<?php

namespace App\Http\Controllers;

use App\Models\AgentDocument;
use App\Models\User;
use App\Notifications\DocumentUploadedNotification;
use App\Notifications\DocumentReviewedNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class DocumentController extends Controller
{
    // -------------------------------------------------------
    // AGENT SIDE
    // -------------------------------------------------------

    /**
     * Show the agent's document upload dashboard.
     * Only accessible once admin has set status to form_received or approved.
     */
    public function index()
    {
        $user      = auth()->user();
        $appStatus = $user->detail?->application_status ?? 'pending';

        // Block access until admin has enabled document upload
        if (!in_array($appStatus, ['form_received', 'approved'])) {
            return view('documents.locked', compact('appStatus'));
        }

        // Group by type to support multiple files per type
        // (aadhaar=2 front/back, pan/bank=1, rest=multiple).
        $allDocs = $user->documents()->orderBy('id')->get();
        $documents = $user->documents()->orderBy('id')->get()->keyBy(function ($d) {
            // Backward compat for views expecting single row per type:
            // keep first file per type.
            return $d->document_type . '|' . $d->id;
        });
        // Single-row map (first file per type) for legacy single-file views.
        $firstByType = $allDocs->groupBy('document_type')->map(fn($g) => $g->first());
        // Full multi-file map grouped by type.
        $docsByType = $allDocs->groupBy('document_type');
        $types     = AgentDocument::requiredTypes();
        $docConfig = AgentDocument::types();

        // Legacy $documents variable kept as first-per-type for existing blade helpers.
        $documents = $firstByType;

        return view('documents.index', compact('documents', 'types', 'docConfig', 'appStatus', 'docsByType', 'allDocs'));
    }

    /**
     * Agent uploads or re-uploads a document.
     * Blocked if admin has not yet enabled uploading.
     *
     * Rules:
     * - aadhaar: up to 2 files (front/back)
     * - pan_card, bank_proof: 1 file
     * - rest: multiple files (up to max_files)
     * - allowed: jpg, jpeg, webp, png, pdf
     */
    public function store(Request $request)
    {
        $user      = auth()->user();
        $appStatus = $user->detail?->application_status ?? 'pending';

        // Block uploads until admin has enabled document upload
        if (!in_array($appStatus, ['form_received', 'approved'])) {
            return back()->with('error', 'Document upload is not enabled yet. Please wait for admin to process your application.');
        }

        $request->validate([
            'document_type' => ['required', 'in:' . implode(',', AgentDocument::requiredTypes())],
            'file'          => ['nullable', 'file', 'mimes:jpg,jpeg,webp,png,pdf', 'max:5120'],
            'files'         => ['nullable', 'array', 'max:5'],
            'files.*'       => ['file', 'mimes:jpg,jpeg,webp,png,pdf', 'max:5120'],
            'slot'          => ['nullable', 'string', 'max:30'],
        ]);

        $type = $request->document_type;
        $maxFiles = AgentDocument::maxFiles($type);
        $isSingle = ! AgentDocument::isMultiple($type);

        // Collect incoming files (support both single `file` and multiple `files[]`).
        $incoming = [];
        if ($request->hasFile('files')) {
            foreach ((array) $request->file('files') as $f) {
                if ($f) {
                    $incoming[] = $f;
                }
            }
        }
        if ($request->hasFile('file')) {
            $incoming[] = $request->file('file');
        }

        if (empty($incoming)) {
            return back()->withErrors(['file' => 'Please choose a file to upload (jpg, jpeg, webp, png, pdf).'])->withInput();
        }

        $agentId = $user->detail?->agent_id_number ?? ('agent_' . $user->id);

        $existing = AgentDocument::where('user_id', $user->id)
            ->where('document_type', $type)
            ->orderBy('id')
            ->get();

        // Block if any file already approved and type is single-file.
        // For multi-file types, approved files cannot be replaced — new uploads are added as pending.
        if ($isSingle && $existing->where('status', 'approved')->count() > 0) {
            return back()->with('error', AgentDocument::typeLabel($type) . ' is already approved and cannot be re-uploaded.');
        }

        if ($isSingle) {
            // Single-file types (pan_card, bank_proof): replace existing pending file.
            $file = $incoming[0];
            $old = $existing->first();
            if ($old && $old->file_path) {
                Storage::disk('public')->delete($old->file_path);
            }
            if ($old) {
                $old->delete();
            }

            $ext = strtolower($file->getClientOriginalExtension());
            $filename = $type . '_' . time() . '.' . $ext;
            $path = $file->storeAs($agentId, $filename, 'public');

            $document = AgentDocument::create([
                'user_id'       => $user->id,
                'document_type' => $type,
                'slot'          => 'default',
                'file_path'     => $path,
                'original_name' => $file->getClientOriginalName(),
                'status'        => 'pending',
                'admin_note'    => null,
                'reviewed_at'   => null,
            ]);
        } else {
            // Multi-file types: aadhaar (max 2 front/back), rest (up to max_files).
            $remaining = $maxFiles - $existing->count();
            if ($remaining <= 0) {
                return back()->with('error', AgentDocument::typeLabel($type) . " already has maximum {$maxFiles} file(s). Delete one to upload again.");
            }
            if (count($incoming) > $remaining) {
                return back()->withErrors(['files' => AgentDocument::typeLabel($type) . " accepts max {$maxFiles} file(s). You can add {$remaining} more."])->withInput();
            }

            $document = null;
            foreach ($incoming as $i => $file) {
                // Aadhaar slots: front/back auto-assigned; others: file_1, file_2...
                if ($type === 'aadhaar') {
                    $usedSlots = $existing->pluck('slot')->all();
                    $slot = ! in_array('front', $usedSlots) ? 'front' : (! in_array('back', $usedSlots) ? 'back' : 'extra_' . time() . '_' . $i);
                    // If explicit slot passed and free, honor it.
                    if ($request->filled('slot') && ! in_array($request->slot, $usedSlots) && in_array($request->slot, ['front', 'back'])) {
                        $slot = $request->slot;
                    }
                } else {
                    $slot = 'file_' . time() . '_' . $i . '_' . \Illuminate\Support\Str::random(4);
                    if ($request->filled('slot')) {
                        $slot = preg_replace('/[^a-z0-9_\-]/i', '', $request->slot) . '_' . time() . '_' . $i;
                    }
                }

                $ext = strtolower($file->getClientOriginalExtension());
                $filename = $type . '_' . $slot . '_' . time() . '_' . $i . '.' . $ext;
                $path = $file->storeAs($agentId, $filename, 'public');

                $document = AgentDocument::create([
                    'user_id'       => $user->id,
                    'document_type' => $type,
                    'slot'          => $slot,
                    'file_path'     => $path,
                    'original_name' => $file->getClientOriginalName(),
                    'status'        => 'pending',
                    'admin_note'    => null,
                    'reviewed_at'   => null,
                ]);
                $existing->push($document);
            }
        }

        // Notify all admins
        $admins = User::whereHas('role', fn($q) => $q->whereIn('slug', ['admin', 'super-admin']))->get();
        foreach ($admins as $admin) {
            $admin->notify(new DocumentUploadedNotification($document, $user->name));
        }

        return back()->with('success', AgentDocument::typeLabel($type) . ' uploaded successfully!');
    }

    /**
     * Agent deletes one of their uploaded files (only if not approved).
     */
    public function destroy($docId)
    {
        $user = auth()->user();
        $doc = AgentDocument::where('user_id', $user->id)->findOrFail($docId);

        if ($doc->status === 'approved') {
            return back()->with('error', AgentDocument::typeLabel($doc->document_type) . ' is already approved and cannot be deleted.');
        }

        if ($doc->file_path) {
            Storage::disk('public')->delete($doc->file_path);
        }
        $doc->delete();

        return back()->with('success', AgentDocument::typeLabel($doc->document_type) . ' file removed.');
    }

    // -------------------------------------------------------
    // ADMIN SIDE
    // -------------------------------------------------------

    /**
     * Admin overview: all agents with a live document summary.
     * Single entry point to find documents awaiting review.
     * Multi-file aware: counts distinct types, compliance needs min files per type.
     */
    public function adminOverview()
    {
        $types = AgentDocument::types();
        $total = count($types);

        $agents = User::with(['detail', 'documents'])
            ->whereHas('role', fn($q) => $q->where('slug', 'agent'))
            ->where('status', 'active')
            ->orderBy('name')
            ->get()
            ->map(function (User $agent) use ($total, $types) {
                $docs = $agent->documents;
                $byType = $docs->groupBy('document_type');

                // Distinct types uploaded.
                $uploadedTypes = $byType->keys()->count();
                // Compliance: each type meets its minimum (aadhaar needs 2 approved, others at least 1 approved;
                // single-file types need approved, multi-file need >=1 approved, aadhaar needs 2).
                $compliantTypes = 0;
                foreach ($types as $t => $cfg) {
                    $approved = $docs->where('document_type', $t)->where('status', 'approved')->count();
                    $need = ($t === 'aadhaar') ? 2 : 1;
                    if ($approved >= $need) {
                        $compliantTypes++;
                    }
                }

                return [
                    'agent'            => $agent,
                    'total'            => $total,
                    'uploaded'         => $uploadedTypes,
                    'uploaded_files'   => $docs->count(),
                    'approved'         => $docs->where('status', 'approved')->count(),
                    'pending'          => $docs->where('status', 'pending')->count(),
                    'action_needed'    => $docs->whereIn('status', ['rejected', 're_upload'])->count(),
                    'not_uploaded'     => max(0, $total - $uploadedTypes),
                    'is_compliant'     => $compliantTypes === $total,
                    'app_status'       => $agent->detail?->application_status ?? 'pending',
                ];
            });

        return view('admin.documents.overview', compact('agents', 'types', 'total'));
    }

    /**
     * Admin views all documents for a specific agent.
     * Multi-file aware: passes both single-row map (legacy) and grouped files.
     */
    public function adminIndex($userId)
    {
        $agent     = User::with(['detail', 'documents', 'sanctionLetters'])->findOrFail($userId);
        $allDocs   = $agent->documents()->orderBy('id')->get();
        $documents = $allDocs->groupBy('document_type')->map(fn($g) => $g->first());
        $docsByType = $allDocs->groupBy('document_type');
        $types     = AgentDocument::types();
        $sanctions = $agent->sanctionLetters()->latest()->get();

        return view('admin.documents.index', compact('agent', 'documents', 'types', 'docsByType', 'allDocs', 'sanctions'));
    }

    /**
     * Admin reviews (approve/reject/re_upload) a document.
     */
    public function adminReview(Request $request, $userId, $docId)
    {
        $request->validate([
            'status'     => ['required', 'in:approved,rejected,re_upload'],
            'admin_note' => ['nullable', 'string', 'max:500'],
        ]);

        $document = AgentDocument::where('user_id', $userId)->findOrFail($docId);
        $document->update([
            'status'      => $request->status,
            'admin_note'  => $request->admin_note,
            'reviewed_at' => now(),
        ]);

        // Notify the agent
        $document->user->notify(new DocumentReviewedNotification($document));

        return back()->with('success', AgentDocument::typeLabel($document->document_type) . ' marked as ' . $request->status . '.');
    }
}

