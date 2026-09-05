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

        $documents = $user->documents()->get()->keyBy('document_type');
        $types     = AgentDocument::requiredTypes();

        return view('documents.index', compact('documents', 'types', 'appStatus'));
    }

    /**
     * Agent uploads or re-uploads a document.
     * Blocked if admin has not yet enabled uploading.
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
            'file'          => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
        ]);

        $agentId = $user->detail?->agent_id_number ?? ('agent_' . $user->id);
        $type    = $request->document_type;

        // Check if agent already has an approved doc for this type
        $existing = AgentDocument::where('user_id', $user->id)
            ->where('document_type', $type)
            ->first();

        if ($existing && $existing->status === 'approved') {
            return back()->with('error', AgentDocument::typeLabel($type) . ' is already approved and cannot be re-uploaded.');
        }

        // Delete old file if exists
        if ($existing && $existing->file_path) {
            Storage::disk('public')->delete($existing->file_path);
        }

        // Store file in {agent_id}/{type}.{ext}
        $ext      = $request->file->getClientOriginalExtension();
        $filename = $type . '.' . $ext;
        $path     = $request->file('file')->storeAs($agentId, $filename, 'public');

        // Create or update the document record
        $document = AgentDocument::updateOrCreate(
            ['user_id' => $user->id, 'document_type' => $type],
            [
                'file_path'     => $path,
                'original_name' => $request->file->getClientOriginalName(),
                'status'        => 'pending',
                'admin_note'    => null,
                'reviewed_at'   => null,
            ]
        );

        // Notify all admins
        $admins = User::whereHas('role', fn($q) => $q->whereIn('slug', ['admin', 'super-admin']))->get();
        foreach ($admins as $admin) {
            $admin->notify(new DocumentUploadedNotification($document, $user->name));
        }

        return back()->with('success', AgentDocument::typeLabel($type) . ' uploaded successfully!');
    }

    // -------------------------------------------------------
    // ADMIN SIDE
    // -------------------------------------------------------

    /**
     * Admin views all documents for a specific agent.
     */
    public function adminIndex($userId)
    {
        $agent     = User::with(['detail', 'documents'])->findOrFail($userId);
        $documents = $agent->documents()->get()->keyBy('document_type');
        $types     = AgentDocument::requiredTypes();

        return view('admin.documents.index', compact('agent', 'documents', 'types'));
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

