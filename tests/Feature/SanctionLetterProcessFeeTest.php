<?php

namespace Tests\Feature;

use App\Models\{Role, SanctionLetter, SanctionLetterUpload, User, PaymentGateway};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use paytm\paytmchecksum\PaytmChecksum;
use Tests\TestCase;

class SanctionLetterProcessFeeTest extends TestCase
{
    use RefreshDatabase;

    private const TEST_MID = 'TESTMID1234567890';
    private const TEST_KEY = 'TESTMERCHANTKEY';

    private Role $agentRole;
    private User $agent;

    protected function setUp(): void
    {
        parent::setUp();

        PaymentGateway::create([
            'name' => 'Paytm',
            'slug' => 'paytm',
            'status' => true,
            'environment' => 'sandbox',
            'credentials' => [
                'sandbox_api_key' => self::TEST_MID,
                'sandbox_api_secret' => self::TEST_KEY,
            ],
        ]);

        $this->agentRole = Role::create(['name' => 'Agent', 'slug' => 'agent']);
        $this->agent = User::factory()->create(['role_id' => $this->agentRole->id]);
    }

    private function makeLetter(float $fee = 500): SanctionLetter
    {
        Storage::fake('public');
        Storage::fake('local');

        $pdf = UploadedFile::fake()->create('letter.pdf', 100, 'application/pdf');
        $path = Storage::disk('public')->putFile('sanction-letters', $pdf);

        return SanctionLetter::create([
            'user_id'         => $this->agent->id,
            'sanction_number' => 101,
            'title'           => 'Sanction Letter',
            'subject'         => 'Notice of sanction',
            'greeting'        => 'Dear Agent,',
            'body'            => 'Body text',
            'closing'         => 'Sincerely,',
            'pdf_path'        => $path,
            'status'          => 'sent',
            'sent_at'         => now(),
            'upload_deadline_at' => now()->addHours(48),
            'process_fee'     => $fee,
        ]);
    }

    private function signedStatusResponse(string $reference, string $resultStatus = 'TXN_SUCCESS'): void
    {
        $body = [
            'mid' => self::TEST_MID,
            'orderId' => $reference,
            'txnId' => 'PAYTM_TXN_1',
            'txnAmount' => '500.00',
            'resultInfo' => [
                'resultStatus' => $resultStatus,
                'resultCode' => $resultStatus === 'TXN_SUCCESS' ? '01' : '227',
                'resultMsg' => $resultStatus === 'TXN_SUCCESS' ? 'Txn Success' : 'Txn Failed',
            ],
        ];

        $bodyJson = json_encode($body, JSON_UNESCAPED_SLASHES);
        $signature = PaytmChecksum::generateSignature($bodyJson, self::TEST_KEY);

        Http::fake([
            'https://securestage.paytmpayments.com/v3/order/status' => Http::response(
                json_encode([
                    'body' => $body,
                    'head' => ['signature' => $signature],
                ], JSON_UNESCAPED_SLASHES),
                200
            ),
        ]);
    }

    private function fakeInitiateTransaction(string $token = 'FEE_TOKEN_1'): void
    {
        $body = [
            'resultInfo' => ['resultStatus' => 'S', 'resultCode' => '0000', 'resultMsg' => 'Success'],
            'txnToken' => $token,
        ];
        $bodyJson = json_encode($body, JSON_UNESCAPED_SLASHES);
        $signature = PaytmChecksum::generateSignature($bodyJson, self::TEST_KEY);

        Http::fake([
            'https://securestage.paytmpayments.com/theia/api/v1/initiateTransaction*' => Http::response(
                json_encode([
                    'body' => $body,
                    'head' => ['signature' => $signature],
                ], JSON_UNESCAPED_SLASHES),
                200
            ),
        ]);
    }

    private function callbackParams(SanctionLetter $letter, string $status = 'TXN_SUCCESS'): array
    {
        $params = [
            'MID' => self::TEST_MID,
            'ORDERID' => $letter->process_fee_reference,
            'TXNAMOUNT' => number_format($letter->processFeeAmount(), 2, '.', ''),
            'CUST_ID' => 'user_' . $this->agent->id,
            'TXNID' => 'PAYTM_TXN_1',
            'STATUS' => $status,
            'RESPCODE' => $status === 'TXN_SUCCESS' ? '01' : '227',
            'RESPMSG' => $status === 'TXN_SUCCESS' ? 'Success' : 'Failure',
        ];

        $params['CHECKSUMHASH'] = PaytmChecksum::generateSignature($params, self::TEST_KEY);

        return $params;
    }

    public function test_upload_is_blocked_until_processing_fee_is_paid(): void
    {
        $letter = $this->makeLetter(500);

        $signed = UploadedFile::fake()->create('signed.pdf', 500, 'application/pdf');

        $this->actingAs($this->agent)
            ->post(route('agent.sanctions.upload', $letter), ['signed_pdf' => $signed])
            ->assertSessionHas('error');

        $this->assertSame(0, SanctionLetterUpload::where('sanction_letter_id', $letter->id)->count());
        $this->assertNull($letter->fresh()->signed_pdf_path);
    }

    public function test_letter_without_fee_allows_upload(): void
    {
        $letter = $this->makeLetter(0);

        $signed = UploadedFile::fake()->create('signed.pdf', 500, 'application/pdf');

        $this->actingAs($this->agent)
            ->post(route('agent.sanctions.upload', $letter), ['signed_pdf' => $signed])
            ->assertSessionHas('success');

        $this->assertSame('under_review', $letter->fresh()->review_status);
    }

    public function test_pay_fee_initiates_paytm_payment(): void
    {
        $letter = $this->makeLetter(500);
        $this->fakeInitiateTransaction('FEE_TOKEN_1');

        $this->actingAs($this->agent)
            ->post(route('agent.sanctions.pay-fee', $letter))
            ->assertOk()
            ->assertViewHas('txnToken', 'FEE_TOKEN_1');

        $fresh = $letter->fresh();
        $this->assertSame(SanctionLetter::PROCESS_FEE_PENDING, $fresh->process_fee_status);
        $this->assertNotNull($fresh->process_fee_reference);
        $this->assertStringStartsWith('SLPF-', $fresh->process_fee_reference);
    }

    public function test_paytm_callback_marks_fee_paid_and_unlocks_upload(): void
    {
        $letter = $this->makeLetter(500);
        $letter->update([
            'process_fee_status' => SanctionLetter::PROCESS_FEE_PENDING,
            'process_fee_reference' => 'SLPF-' . $letter->id . '-ABCDEF',
            'process_fee_gateway' => 'paytm',
        ]);

        $this->signedStatusResponse('SLPF-' . $letter->id . '-ABCDEF', 'TXN_SUCCESS');

        $this->actingAs($this->agent)
            ->post(route('agent.sanctions.fee-callback'), $this->callbackParams($letter))
            ->assertRedirect(route('agent.sanctions.show', $letter))
            ->assertSessionHas('success');

        $fresh = $letter->fresh();
        $this->assertSame(SanctionLetter::PROCESS_FEE_PAID, $fresh->process_fee_status);
        $this->assertSame('PAYTM_TXN_1', $fresh->process_fee_payment_id);
        $this->assertNotNull($fresh->process_fee_paid_at);

        $signed = UploadedFile::fake()->create('signed.pdf', 500, 'application/pdf');
        $this->actingAs($this->agent)
            ->post(route('agent.sanctions.upload', $letter), ['signed_pdf' => $signed])
            ->assertSessionHas('success');

        $this->assertSame('under_review', $letter->fresh()->review_status);
    }

    public function test_paytm_callback_failure_resets_fee_to_unpaid(): void
    {
        $letter = $this->makeLetter(500);
        $letter->update([
            'process_fee_status' => SanctionLetter::PROCESS_FEE_PENDING,
            'process_fee_reference' => 'SLPF-' . $letter->id . '-XYZ999',
            'process_fee_gateway' => 'paytm',
        ]);

        $this->signedStatusResponse('SLPF-' . $letter->id . '-XYZ999', 'TXN_FAILURE');

        $this->actingAs($this->agent)
            ->post(route('agent.sanctions.fee-callback'), $this->callbackParams($letter, 'TXN_FAILURE'))
            ->assertRedirect(route('agent.sanctions.show', $letter))
            ->assertSessionHas('error');

        $fresh = $letter->fresh();
        $this->assertSame(SanctionLetter::PROCESS_FEE_UNPAID, $fresh->process_fee_status);
        $this->assertNull($fresh->process_fee_reference);
    }

    public function test_pay_fee_allows_re_initiation_while_pending(): void
    {
        $letter = $this->makeLetter(500);
        $oldRef = 'SLPF-' . $letter->id . '-OLDREF';
        $letter->update([
            'process_fee_status' => SanctionLetter::PROCESS_FEE_PENDING,
            'process_fee_reference' => $oldRef,
            'process_fee_gateway' => 'paytm',
        ]);

        $statusBody = [
            'mid' => self::TEST_MID,
            'orderId' => $oldRef,
            'txnId' => 'PAYTM_TXN_X',
            'txnAmount' => '500.00',
            'resultInfo' => [
                'resultStatus' => 'TXN_FAILURE',
                'resultCode' => '227',
                'resultMsg' => 'Txn Failed',
            ],
        ];
        $statusJson = json_encode($statusBody, JSON_UNESCAPED_SLASHES);
        $statusSignature = PaytmChecksum::generateSignature($statusJson, self::TEST_KEY);

        $initBody = [
            'resultInfo' => ['resultStatus' => 'S', 'resultCode' => '0000', 'resultMsg' => 'Success'],
            'txnToken' => 'FEE_TOKEN_NEW',
        ];
        $initJson = json_encode($initBody, JSON_UNESCAPED_SLASHES);
        $initSignature = PaytmChecksum::generateSignature($initJson, self::TEST_KEY);

        Http::fake([
            'https://securestage.paytmpayments.com/v3/order/status' => Http::response(
                json_encode([
                    'body' => $statusBody,
                    'head' => ['signature' => $statusSignature],
                ], JSON_UNESCAPED_SLASHES),
                200
            ),
            'https://securestage.paytmpayments.com/theia/api/v1/initiateTransaction*' => Http::response(
                json_encode([
                    'body' => $initBody,
                    'head' => ['signature' => $initSignature],
                ], JSON_UNESCAPED_SLASHES),
                200
            ),
        ]);

        $this->actingAs($this->agent)
            ->post(route('agent.sanctions.pay-fee', $letter))
            ->assertOk()
            ->assertViewHas('txnToken', 'FEE_TOKEN_NEW');

        $fresh = $letter->fresh();
        $this->assertNotSame($oldRef, $fresh->process_fee_reference);
        $this->assertStringStartsWith('SLPF-', $fresh->process_fee_reference);
        $this->assertSame(SanctionLetter::PROCESS_FEE_PENDING, $fresh->process_fee_status);

        $states = collect($fresh->process_fee_response['attempts'] ?? [])->pluck('state')->all();
        $this->assertContains('failed', $states);
        $this->assertContains('initiated', $states);
    }

    public function test_show_reconciles_stuck_pending_to_paid_when_paytm_confirms(): void
    {
        $letter = $this->makeLetter(500);
        $reference = 'SLPF-' . $letter->id . '-PAYOK';
        $letter->update([
            'process_fee_status' => SanctionLetter::PROCESS_FEE_PENDING,
            'process_fee_reference' => $reference,
            'process_fee_gateway' => 'paytm',
        ]);

        $this->signedStatusResponse($reference, 'TXN_SUCCESS');

        $this->actingAs($this->agent)
            ->get(route('agent.sanctions.show', $letter))
            ->assertOk();

        $fresh = $letter->fresh();
        $this->assertSame(SanctionLetter::PROCESS_FEE_PAID, $fresh->process_fee_status);
        $this->assertSame('PAYTM_TXN_1', $fresh->process_fee_payment_id);
        $this->assertNotNull($fresh->process_fee_paid_at);
    }

    public function test_show_keeps_pending_when_paytm_status_is_unavailable(): void
    {
        $letter = $this->makeLetter(500);
        $reference = 'SLPF-' . $letter->id . '-UNKNOWN';
        $letter->update([
            'process_fee_status' => SanctionLetter::PROCESS_FEE_PENDING,
            'process_fee_reference' => $reference,
            'process_fee_gateway' => 'paytm',
        ]);

        Http::fake([
            'https://securestage.paytmpayments.com/v3/order/status' => Http::response(null, 500),
        ]);

        $this->actingAs($this->agent)
            ->get(route('agent.sanctions.show', $letter))
            ->assertOk();

        $fresh = $letter->fresh();
        $this->assertSame(SanctionLetter::PROCESS_FEE_PENDING, $fresh->process_fee_status);
        $this->assertSame($reference, $fresh->process_fee_reference);
    }

    public function test_pay_fee_marks_paid_and_skips_new_payment_when_pending_confirmed(): void
    {
        $letter = $this->makeLetter(500);
        $reference = 'SLPF-' . $letter->id . '-PAIDOLD';
        $letter->update([
            'process_fee_status' => SanctionLetter::PROCESS_FEE_PENDING,
            'process_fee_reference' => $reference,
            'process_fee_gateway' => 'paytm',
        ]);

        $this->signedStatusResponse($reference, 'TXN_SUCCESS');

        $this->actingAs($this->agent)
            ->post(route('agent.sanctions.pay-fee', $letter))
            ->assertSessionHas('success');

        Http::assertNotSent(fn ($request) => str_contains($request->url(), 'theia/api/v1/initiateTransaction'));

        $fresh = $letter->fresh();
        $this->assertSame(SanctionLetter::PROCESS_FEE_PAID, $fresh->process_fee_status);
        $this->assertSame($reference, $fresh->process_fee_reference);
        $this->assertNotNull($fresh->process_fee_paid_at);
    }

    public function test_callback_silently_logs_agent_back_in_via_signed_url(): void
    {
        $letter = $this->makeLetter(500);
        $reference = 'SLPF-' . $letter->id . '-SIGNED';
        $letter->update([
            'process_fee_status' => SanctionLetter::PROCESS_FEE_PENDING,
            'process_fee_reference' => $reference,
            'process_fee_gateway' => 'paytm',
        ]);

        $this->signedStatusResponse($reference, 'TXN_SUCCESS');

        // Build a signed callback URL as payFee does, then POST to it WITHOUT
        // actingAs to simulate the real cross-site Paytm redirect.
        $callbackUrl = URL::temporarySignedRoute(
            'agent.sanctions.fee-callback',
            now()->addMinutes(10),
            ['u' => $this->agent->id]
        );

        $this->post($callbackUrl, $this->callbackParams($letter))
            ->assertRedirect(route('agent.sanctions.show', $letter));

        $this->assertAuthenticatedAs($this->agent);
        $this->assertSame(SanctionLetter::PROCESS_FEE_PAID, $letter->fresh()->process_fee_status);
    }

    public function test_callback_without_signed_url_does_not_authenticate(): void
    {
        $letter = $this->makeLetter(500);
        $reference = 'SLPF-' . $letter->id . '-NOSIGNED';
        $letter->update([
            'process_fee_status' => SanctionLetter::PROCESS_FEE_PENDING,
            'process_fee_reference' => $reference,
            'process_fee_gateway' => 'paytm',
        ]);

        $this->signedStatusResponse($reference, 'TXN_SUCCESS');

        $this->post(route('agent.sanctions.fee-callback'), $this->callbackParams($letter))
            ->assertRedirect(route('agent.sanctions.show', $letter));

        $this->assertGuest();
        $this->assertSame(SanctionLetter::PROCESS_FEE_PAID, $letter->fresh()->process_fee_status);
    }
}