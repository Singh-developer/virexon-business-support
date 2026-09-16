<?php

namespace App\Http\Controllers;

use App\Models\SanctionLetter;
use App\Models\SanctionLetterUpload;
use App\Models\User;
use App\Notifications\SanctionLetterUploadedNotification;
use App\Services\AuditService;
use App\Services\PaymentGatewayManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
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

        // Reconcile a payment stuck in "pending" (agent left checkout before the
        // Paytm callback arrived) so the UI never dead-ends.
        if ($sanction->processFeePending()) {
            $this->reconcileProcessFee($sanction);
            $sanction->refresh();
        }

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

        if (! $sanction->processFeePaid()) {
            $message = $sanction->processFeePending()
                ? 'Your processing fee payment is still being confirmed. Please wait a moment and try again.'
                : 'You must pay the processing fee before uploading the signed PDF.';

            return back()->with('error', $message);
        }

        if (! $sanction->canUpload()) {
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

    /**
     * Initiate the Paytm payment for this letter's processing fee so the
     * agent can unlock the signed-PDF upload.
     */
    public function payFee(Request $request, SanctionLetter $sanction, AuditService $audit)
    {
        $this->authorizeAccess($sanction);

        if (! $sanction->hasProcessFee()) {
            return back()->with('error', 'No processing fee is required for this letter.');
        }

        if ($sanction->processFeePaid()) {
            return back()->with('info', 'The processing fee for this letter is already paid.');
        }

        // A payment left in "pending" may actually have completed at Paytm (the
        // callback can be lost). Check before charging the agent again.
        if ($sanction->processFeePending()) {
            $resolved = $this->reconcileProcessFee($sanction, $request);

            if ($resolved === SanctionLetter::PROCESS_FEE_PAID) {
                return back()->with('success', 'Your processing fee is confirmed as paid. You can now upload your signed PDF.');
            }

            $sanction->refresh();
        }

        $amount = number_format($sanction->processFeeAmount(), 2, '.', '');
        $orderId = 'SLPF-' . $sanction->id . '-' . Str::upper(Str::random(6));

        // Paytm's cross-site POST callback arrives without the session cookie
        // (SameSite=Lax). Embed a short-lived signed verifier so the callback
        // can silently re-authenticate the agent and avoid an involuntary logout.
        $callbackUrl = URL::temporarySignedRoute(
            'agent.sanctions.fee-callback',
            now()->addMinutes(30),
            ['u' => $request->user()->id]
        );

        try {
            $result = app(PaymentGatewayManager::class)
                ->driver('paytm')
                ->createPayment([
                    'reference' => $orderId,
                    'amount' => $amount,
                    'currency' => 'INR',
                    'user_id' => $request->user()->id,
                    'callback_url' => $callbackUrl,
                ]);
        } catch (\Exception $e) {
            Log::error('AgentSanctionLetterController: process fee initiation failed', [
                'sanction_letter_id' => $sanction->id,
                'order_id' => $orderId,
                'error' => $e->getMessage(),
            ]);

            return back()->with('error', 'Could not start the Paytm payment: ' . $e->getMessage());
        }

        $sanction->update([
            'process_fee_status' => SanctionLetter::PROCESS_FEE_PENDING,
            'process_fee_reference' => $orderId,
            'process_fee_gateway' => 'paytm',
            'process_fee_response' => [
                'attempts' => $this->rememberAttempt($sanction, $orderId, 'initiated'),
            ],
        ]);

        $audit->record($request, 'sanction_letter.process_fee_initiated', $sanction, [
            'order_id' => $orderId,
            'amount' => $amount,
        ]);

        return view('paytm.redirect', [
            'environment' => $result['environment'] ?? 'staging',
            'mid' => $result['params']['mid'] ?? '',
            'orderId' => $orderId,
            'txnToken' => $result['txn_token'],
            'amount' => $amount,
        ]);
    }

    /**
     * Paytm redirects the agent's browser here after the processing-fee
     * payment attempt. The checksum is verified and the letter is marked
     * paid so the signed-PDF upload unlocks.
     */
    public function paytmCallback(Request $request, AuditService $audit)
    {
        // Strip the signed login verifier params so they don't corrupt
        // Paytm's own checksum verification (they aren't part of the signed payload).
        $params = $request->except(['u', 'expires', 'signature']);
        $orderId = $params['ORDERID'] ?? null;

        if (! $orderId) {
            return response('Missing order ID', 400);
        }

        $letter = SanctionLetter::where('process_fee_reference', $orderId)->first();

        if (! $letter) {
            return response('Transaction not found', 404);
        }

        // Paytm's cross-site POST callback loses the Lax session cookie.
        // When the callback URL carries a valid signed verifier we silently
        // re-authenticate the agent so they don't get logged out.
        if ($request->hasValidSignature()) {
            $userId = (int) $request->query('u', 0);

            if ($userId === (int) $letter->user_id) {
                auth()->loginUsingId($userId);
                $request->session()->regenerate(true);
            }
        }

        try {
            $gateway = app(PaymentGatewayManager::class)->driver('paytm');

            $gateway->verifyPayment([
                'params' => $params,
                'expected_amount' => $letter->processFeeAmount(),
            ]);

            $status = $gateway->getPaymentStatus($orderId);
        } catch (\Exception $e) {
            Log::error('AgentSanctionLetterController: process fee callback verification failed', [
                'sanction_letter_id' => $letter->id,
                'order_id' => $orderId,
                'error' => $e->getMessage(),
            ]);

            return redirect()->route('agent.sanctions.show', $letter)->with(
                'warning',
                'Fee payment verification is pending. It will be confirmed shortly.'
            );
        }

        if ($status['status'] === 'successful') {
            $letter->update([
                'process_fee_status' => SanctionLetter::PROCESS_FEE_PAID,
                'process_fee_payment_id' => $status['payment_id'] ?? null,
                'process_fee_paid_at' => now(),
                'process_fee_gateway' => 'paytm',
                'process_fee_response' => [
                    'callback' => $params,
                    'status_api' => $status['raw'] ?? null,
                ],
            ]);

            $audit->record($request, 'sanction_letter.process_fee_paid', $letter, [
                'order_id' => $orderId,
                'payment_id' => $status['payment_id'] ?? null,
                'amount' => $letter->processFeeAmount(),
            ]);

            return redirect()->route('agent.sanctions.show', $letter)->with(
                'success',
                'Processing fee paid successfully. You can now upload your signed PDF.'
            );
        }

        if ($status['status'] === 'pending') {
            $letter->update([
                'process_fee_status' => SanctionLetter::PROCESS_FEE_PENDING,
                'process_fee_gateway' => 'paytm',
                'process_fee_response' => [
                    'callback' => $params,
                    'status_api' => $status['raw'] ?? null,
                ],
            ]);

            return redirect()->route('agent.sanctions.show', $letter)->with(
                'warning',
                'Fee payment is pending confirmation.'
            );
        }

        $letter->update([
            'process_fee_status' => SanctionLetter::PROCESS_FEE_UNPAID,
            'process_fee_reference' => null,
            'process_fee_gateway' => 'paytm',
            'process_fee_response' => [
                'callback' => $params,
                'status_api' => $status['raw'] ?? null,
            ],
        ]);

        return redirect()->route('agent.sanctions.show', $letter)->with(
            'error',
            'Fee payment failed. Please try again.'
        );
    }

    /**
     * Query Paytm to resolve a letter stuck in "pending" (e.g. the agent left
     * the checkout before the callback arrived). Confirmed transactions are
     * marked paid; definitively failed/abandoned attempts are reset to unpaid;
     * anything else stays pending so the UI keeps offering a retry.
     */
    private function reconcileProcessFee(SanctionLetter $sanction, ?Request $request = null): ?string
    {
        $current = $sanction->process_fee_reference;

        if (! $current) {
            return null;
        }

        $attempts = $sanction->process_fee_response['attempts'] ?? [];
        $attempts = is_array($attempts) ? $attempts : [];

        $references = array_values(array_unique(array_filter(array_merge(
            array_map(
                fn ($a) => (string) ($a['order_id'] ?? ''),
                $attempts
            ),
            [$current]
        ))));

        foreach ($references as $reference) {
            try {
                $status = app(PaymentGatewayManager::class)
                    ->driver('paytm')
                    ->getPaymentStatus($reference);
            } catch (\Exception $e) {
                Log::warning('AgentSanctionLetterController: process fee status lookup failed', [
                    'sanction_letter_id' => $sanction->id,
                    'order_id' => $reference,
                    'error' => $e->getMessage(),
                ]);
                continue;
            }

            $statusKey = $status['status'] ?? 'pending';

            if ($statusKey === 'successful') {
                $this->markProcessFeePaid($sanction, $reference, $status['payment_id'] ?? null, $request);

                return SanctionLetter::PROCESS_FEE_PAID;
            }

            if ($statusKey === 'failed' && $reference === $current) {
                $sanction->update([
                    'process_fee_status' => SanctionLetter::PROCESS_FEE_UNPAID,
                    'process_fee_reference' => null,
                    'process_fee_gateway' => 'paytm',
                    'process_fee_response' => [
                        'attempts' => $this->rememberAttempt($sanction, $reference, 'failed'),
                        'status_api' => $status['raw'] ?? null,
                    ],
                ]);

                return SanctionLetter::PROCESS_FEE_UNPAID;
            }
        }

        return SanctionLetter::PROCESS_FEE_PENDING;
    }

    /** Flip a letter to paid and record the confirmation. */
    private function markProcessFeePaid(
        SanctionLetter $sanction,
        string $reference,
        ?string $paymentId,
        ?Request $request
    ): void {
        $sanction->update([
            'process_fee_status' => SanctionLetter::PROCESS_FEE_PAID,
            'process_fee_reference' => $reference,
            'process_fee_payment_id' => $paymentId,
            'process_fee_paid_at' => now(),
            'process_fee_gateway' => 'paytm',
            'process_fee_response' => [
                'attempts' => $this->rememberAttempt($sanction, $reference, 'paid'),
            ],
        ]);

        if ($request) {
            app(AuditService::class)->record($request, 'sanction_letter.process_fee_paid', $sanction, [
                'order_id' => $reference,
                'payment_id' => $paymentId,
                'amount' => $sanction->processFeeAmount(),
            ]);
        }
    }

    /** Keep a short history of Paytm attempts for the letter (latest 5). */
    private function rememberAttempt(SanctionLetter $sanction, string $orderId, string $state): array
    {
        $attempts = $sanction->process_fee_response['attempts'] ?? [];
        $attempts = is_array($attempts) ? $attempts : [];

        $attempts[] = [
            'order_id' => $orderId,
            'state' => $state,
            'at' => now()->toDateTimeString(),
        ];

        return array_slice($attempts, -5);
    }

    private function authorizeAccess(SanctionLetter $sanction): void
    {
        abort_unless($sanction->user_id === auth()->id(), 403, 'You are not authorised for this letter.');
    }
}