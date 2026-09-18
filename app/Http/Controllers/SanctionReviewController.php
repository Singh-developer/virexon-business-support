<?php

namespace App\Http\Controllers;

use App\Models\SanctionLetter;
use App\Models\SanctionLetterUpload;
use App\Models\Advance;
use App\Models\VirtualCard;
use App\Notifications\SanctionLetterReviewedNotification;
use App\Services\AuditService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SanctionReviewController extends Controller
{
    /** Admin review page for a letter's signed PDF. */
    public function show(SanctionLetter $sanction)
    {
        $sanction->load([
            'user.business',
            'user.detail',
            'business',
            'reviewedBy',
            'uploads.uploader',
            'latestUpload',
        ]);

        return view('sanctions.review', ['letter' => $sanction]);
    }

    /** Admin approves the signed PDF - completes the workflow. */
    public function approve(Request $request, SanctionLetter $sanction, AuditService $audit)
    {
        $request->validate([
            'review_comment' => ['nullable', 'string', 'max:1000'],
        ]);

        $sanction->update([
            'review_status' => 'approved',
            'reviewed_at' => now(),
            'reviewed_by' => auth()->id(),
            'review_comment' => $request->input('review_comment'),
        ]);

        $this->syncApprovedLoanData($sanction);

        $sanction->user->notify(new SanctionLetterReviewedNotification($sanction));

        $audit->record($request, 'sanction_letter.approved', $sanction, [
            'reviewed_by' => auth()->id(),
            'comment' => $request->input('review_comment'),
        ]);

        return back()->with('success', 'Sanction letter ' . $sanction->sanction_letter_no . ' marked as Approved.');
    }

    /** Admin requests the agent to upload a new signed PDF. */
    public function requestReupload(Request $request, SanctionLetter $sanction, AuditService $audit)
    {
        $request->validate([
            'review_comment' => ['nullable', 'string', 'max:1000'],
        ]);

        // Keep the latest upload visible to the agent, but clear admin decision state
        // and open a fresh upload slot (another 48 hours to re-upload).
        $sanction->update([
            'review_status' => 'reupload_required',
            'reviewed_at' => now(),
            'reviewed_by' => auth()->id(),
            'review_comment' => $request->input('review_comment'),
            'upload_deadline_at' => now()->addHours(SanctionLetter::UPLOAD_WINDOW_HOURS),
        ]);

        $sanction->user->notify(new SanctionLetterReviewedNotification($sanction));

        $audit->record($request, 'sanction_letter.reupload_required', $sanction, [
            'reviewed_by' => auth()->id(),
            'comment' => $request->input('review_comment'),
        ]);

        return back()->with('success', 'Agent notified: signed PDF re-upload required for letter ' . $sanction->sanction_letter_no . '.');
    }

    /**
     * Admin changes the review status at any time — including after approval.
     * This makes it easy to reopen an approved letter (e.g. back to
     * reupload_required / under_review / pending) without being locked.
     */
    public function updateStatus(Request $request, SanctionLetter $sanction, AuditService $audit)
    {
        $request->validate([
            'review_status' => ['required', 'in:pending,under_review,reupload_required,approved'],
            'review_comment' => ['nullable', 'string', 'max:1000'],
        ]);

        $status = $request->input('review_status');
        $comment = $request->input('review_comment');

        $data = [
            'review_status' => $status,
            'reviewed_at' => now(),
            'reviewed_by' => auth()->id(),
            'review_comment' => $comment,
        ];

        // Resetting to pending revokes any prior review, so the workflow
        // progress (e.g. the "Reviewed" step) goes back to incomplete too.
        if ($status === 'pending') {
            $data['reviewed_at'] = null;
            $data['reviewed_by'] = null;
        }

        // Reopening for re-upload always opens a fresh 48h upload slot.
        if ($status === 'reupload_required') {
            $data['upload_deadline_at'] = now()->addHours(SanctionLetter::UPLOAD_WINDOW_HOURS);
        }

        $sanction->update($data);

        // Approving (including re-approving) syncs loan data so limits stay correct.
        if ($status === 'approved') {
            $this->syncApprovedLoanData($sanction);
        }

        $sanction->user->notify(new SanctionLetterReviewedNotification($sanction));

        $audit->record($request, 'sanction_letter.status_changed', $sanction, [
            'reviewed_by' => auth()->id(),
            'status' => $status,
            'comment' => $comment,
        ]);

        return back()->with('success', 'Sanction letter ' . $sanction->sanction_letter_no . ' status changed to ' . $status . '.');
    }

    /** Inline preview of a specific signed upload (PDF or image, used in the review preview). */
    public function signedPdf(SanctionLetter $sanction, SanctionLetterUpload $upload)
    {
        $this->authorizeUpload($sanction, $upload);

        if (!Storage::disk('local')->exists($upload->file_path)) {
            abort(404, 'Signed file not found.');
        }

        return Storage::disk('local')->response($upload->file_path, $upload->original_name ?? 'signed-sanction-letter');
    }

    /** Download a specific signed upload (PDF or image). */
    public function signedDownload(SanctionLetter $sanction, SanctionLetterUpload $upload)
    {
        $this->authorizeUpload($sanction, $upload);

        if (!Storage::disk('local')->exists($upload->file_path)) {
            abort(404, 'Signed file not found.');
        }

        return Storage::disk('local')->download(
            $upload->file_path,
            $upload->original_name ?? 'signed-sanction-letter'
        );
    }

    private function authorizeUpload(SanctionLetter $sanction, SanctionLetterUpload $upload): void
    {
        abort_unless($upload->sanction_letter_id === $sanction->id, 404, 'Upload does not belong to this letter.');
        abort_unless(auth()->user()->isAdmin(), 403);
    }

    /**
     * When a sanction letter is approved, write the sanctioned amount into
     * the agent's loan records so dashboards / advance pages reflect it:
     *  - user_details.max_limit  => approved amount (Total Approved)
     *  - user_details.application_status => approved
     *  - advances record so repayment flows can attach to it
     *  - existing virtual card limit synced so "Remaining Limit" stays consistent
     */
    private function syncApprovedLoanData(SanctionLetter $sanction): void
    {
        $amount = (float) ($sanction->getDynamicValue('approved_amount') ?? 0);

        if ($amount <= 0) {
            return;
        }

        $user = $sanction->user;

        $user->detail()->updateOrCreate(
            ['user_id' => $user->id],
            [
                'max_limit'          => $amount,
                'application_status' => 'approved',
            ]
        );

        $activeAdvance = $user->advances()->where('status', 'active')->first();
        if ($activeAdvance) {
            $activeAdvance->update([
                'total_amount'       => $amount,
                'outstanding_amount' => $amount,
            ]);
        } else {
            Advance::create([
                'user_id'            => $user->id,
                'total_amount'       => $amount,
                'outstanding_amount' => $amount,
                'repayment_type'     => 'unselected',
                'status'             => 'active',
            ]);
        }

        if ($user->virtualCard) {
            $user->virtualCard->update([
                'card_limit'   => $amount,
                'daily_limit'  => $amount,
                'monthly_limit' => $amount * 10,
                'per_transaction_limit' => $amount,
                'status'       => 'active',
            ]);
        }
    }
}