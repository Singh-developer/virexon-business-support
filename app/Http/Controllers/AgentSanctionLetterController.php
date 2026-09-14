<?php

namespace App\Http\Controllers;

use App\Models\SanctionLetter;
use App\Models\SanctionLetterUpload;
use App\Models\User;
use App\Notifications\SanctionLetterUploadedNotification;
use App\Services\AuditService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AgentSanctionLetterController extends Controller
{
    /** List all sanction letters issued to the logged-in agent. */
    public function index(Request $request)
    {
        $letters = auth()->user()->sanctionLetters()
            ->with(['user', 'business'])
            ->latest()
            ->paginate($this->perPage($request))
            ->withQueryString();

        return view('agent.sanctions.index', ['letters' => $letters]);
    }

    /** Agent detail page: status, preview, download and signed-PDF upload. */
    public function show(SanctionLetter $sanction)
    {
        $this->authorizeAccess($sanction);

        $sanction->load(['user.business', 'business', 'uploads' => fn ($q) => $q->latest()]);

        return view('agent.sanctions.show', ['letter' => $sanction]);
    }

    /** Inline PDF preview of the original (unsigned) sanction letter. */
    public function pdf(SanctionLetter $sanction)
    {
        $this->authorizeAccess($sanction);

        if (!$sanction->pdf_path || !Storage::disk('public')->exists($sanction->pdf_path)) {
            abort(404, 'Sanction letter PDF not found.');
        }

        return Storage::disk('public')->response($sanction->pdf_path, 'sanction-letter.pdf');
    }

    /** Agent downloads the sanction letter PDF; records the first download. */
    public function download(SanctionLetter $sanction, AuditService $audit)
    {
        $this->authorizeAccess($sanction);

        if (!$sanction->pdf_path || !Storage::disk('public')->exists($sanction->pdf_path)) {
            abort(404, 'Sanction letter PDF not found.');
        }

        if (!$sanction->downloaded_at) {
            $sanction->forceFill(['downloaded_at' => now()])->save();
            $audit->record(request(), 'sanction_letter.agent_downloaded', $sanction, [
                'agent_id' => auth()->id(),
            ]);
        }

        return Storage::disk('public')->download(
            $sanction->pdf_path,
            'sanction-letter-' . ($sanction->sanction_number ?: $sanction->id) . '.pdf'
        );
    }

    /** Inline preview of the agent's latest uploaded signed PDF. */
    public function signedPdf(SanctionLetter $sanction)
    {
        $this->authorizeAccess($sanction);

        if (!$sanction->signed_pdf_path || !Storage::disk('local')->exists($sanction->signed_pdf_path)) {
            abort(404, 'Signed PDF not found.');
        }

        return Storage::disk('local')->response($sanction->signed_pdf_path, 'signed-sanction-letter.pdf');
    }

    /** Agent uploads (or re-uploads) the signed sanction letter PDF. */
    public function upload(Request $request, SanctionLetter $sanction, AuditService $audit)
    {
        $this->authorizeAccess($sanction);

        if (!$sanction->canUpload()) {
            return back()->with('error', 'Upload is not available for this letter right now.');
        }

        $request->validate([
            'signed_pdf' => ['required', 'file', 'mimes:pdf', 'max:10240'],
        ]);

        $file = $request->file('signed_pdf');
        $extension = $file->getClientOriginalExtension();

        $directory = 'signed-sanction-letters';
        $filename = 'letter-' . ($sanction->sanction_number ?: $sanction->id)
            . '-' . now()->format('Ymd-His')
            . '-' . Str::lower(Str::random(6)) . '.' . $extension;

        $path = Storage::disk('local')->putFileAs($directory, $file, $filename);

        if (!$path) {
            return back()->with('error', 'Could not store the uploaded PDF. Please try again.');
        }

        SanctionLetterUpload::create([
            'sanction_letter_id' => $sanction->id,
            'uploaded_by' => auth()->id(),
            'file_path' => $path,
            'original_name' => $file->getClientOriginalName(),
            'file_size' => $file->getSize(),
            'uploaded_at' => now(),
        ]);

        $sanction->update([
            'signed_pdf_path' => $path,
            'signed_pdf_uploaded_at' => now(),
            'signed_pdf_upload_count' => ($sanction->signed_pdf_upload_count ?? 0) + 1,
            'review_status' => 'under_review',
            'reviewed_at' => null,
            'reviewed_by' => null,
            'review_comment' => null,
        ]);

        // Notify all admins that a signed PDF awaits review.
        $admins = User::whereHas('role', fn ($q) => $q->whereIn('slug', ['admin', 'super-admin']))->get();
        foreach ($admins as $admin) {
            $admin->notify(new SanctionLetterUploadedNotification($sanction, auth()->user()->name));
        }

        $audit->record($request, 'sanction_letter.signed_uploaded', $sanction, [
            'upload_count' => $sanction->signed_pdf_upload_count,
            'file' => $path,
        ]);

        return back()->with('success', 'Signed Sanction Letter uploaded successfully. It is now pending admin review.');
    }

    private function authorizeAccess(SanctionLetter $sanction): void
    {
        abort_unless($sanction->user_id === auth()->id(), 403, 'You are not authorised for this letter.');
    }
}