<?php

namespace Tests\Feature;

use App\Enums\PaymentStatus;
use App\Models\{Business, Payment, PaymentGateway, Role, User, VirtualCard};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use paytm\paytmchecksum\PaytmChecksum;
use Tests\TestCase;

class PaymentPaytmCallbackTest extends TestCase
{
    use RefreshDatabase;

    private const TEST_MID = 'TESTMID1234567890';
    private const TEST_KEY = 'TESTMERCHANTKEY';

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
    }

    private function signedStatusResponse(string $reference, string $resultStatus, string $txnId = 'PAYTM_TXN_1'): void
    {
        $body = [
            'mid' => self::TEST_MID,
            'orderId' => $reference,
            'txnId' => $txnId,
            'txnAmount' => '200.00',
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

    private function callbackParams(Payment $payment, string $status = 'TXN_SUCCESS'): array
    {
        $params = [
            'MID' => self::TEST_MID,
            'ORDERID' => $payment->reference,
            'TXNAMOUNT' => number_format((float) $payment->amount, 2, '.', ''),
            'CUST_ID' => 'user_' . $payment->user_id,
            'TXNID' => 'PAYTM_TXN_1',
            'STATUS' => $status,
            'RESPCODE' => $status === 'TXN_SUCCESS' ? '01' : '227',
            'RESPMSG' => $status === 'TXN_SUCCESS' ? 'Success' : 'Failure',
        ];

        $params['CHECKSUMHASH'] = PaytmChecksum::generateSignature($params, self::TEST_KEY);

        return $params;
    }

    private function makeRepaymentPayment(float $usage = 500): array
    {
        $role = Role::create(['name' => 'Agent', 'slug' => 'agent']);
        $agent = User::factory()->create(['role_id' => $role->id]);
        $business = Business::create(['name' => 'Demo', 'created_by' => $agent->id]);
        $agent->update(['business_id' => $business->id]);

        $card = VirtualCard::create([
            'business_id' => $business->id,
            'agent_id' => $agent->id,
            'reference' => 'VC-' . uniqid(),
            'cardholder_name' => 'Agent',
            'status' => 'active',
            'card_limit' => 10000,
            'daily_limit' => 5000,
            'monthly_limit' => 8000,
            'per_transaction_limit' => 1000,
            'current_usage' => $usage,
            'expiry_date' => now()->addYear(),
        ]);

        $payment = Payment::create([
            'business_id' => $business->id,
            'user_id' => $agent->id,
            'card_id' => $card->id,
            'gateway' => 'paytm',
            'amount' => 200,
            'currency' => 'INR',
            'reference' => 'PAY-' . uniqid(),
            'payment_type' => 'repayment',
            'status' => PaymentStatus::PENDING,
        ]);

        return [$card, $payment];
    }

    public function test_callback_marks_payment_successful_and_decrements_usage(): void
    {
        [$card, $payment] = $this->makeRepaymentPayment(500);
        $this->signedStatusResponse($payment->reference, 'TXN_SUCCESS');

        $this->post(route('payments.paytm.callback'), $this->callbackParams($payment))
            ->assertRedirect(route('payments.show', $payment));

        $this->assertSame(PaymentStatus::SUCCESSFUL, $payment->fresh()->status);
        $this->assertEquals(300, (float) $card->fresh()->current_usage);
        $this->assertSame('PAYTM_TXN_1', $payment->fresh()->gateway_payment_id);
        $this->assertDatabaseHas('transactions', ['payment_id' => $payment->id, 'status' => 'successful']);
    }

    public function test_callback_marks_payment_failed_when_status_api_says_failed(): void
    {
        [$card, $payment] = $this->makeRepaymentPayment(500);
        $this->signedStatusResponse($payment->reference, 'TXN_FAILURE', 'PAYTM_TXN_2');

        $this->post(route('payments.paytm.callback'), $this->callbackParams($payment, 'TXN_FAILURE'))
            ->assertRedirect(route('payments.show', $payment));

        $this->assertSame(PaymentStatus::FAILED, $payment->fresh()->status);
        $this->assertEquals(500, (float) $card->fresh()->current_usage);
        $this->assertDatabaseMissing('transactions', ['payment_id' => $payment->id]);
    }

    public function test_callback_marks_payment_pending_when_status_is_unverified(): void
    {
        [$card, $payment] = $this->makeRepaymentPayment(500);
        $this->signedStatusResponse($payment->reference, 'PENDING', 'PAYTM_TXN_3');

        $this->post(route('payments.paytm.callback'), $this->callbackParams($payment))
            ->assertRedirect(route('payments.show', $payment))
            ->assertSessionHas('warning');

        $this->assertSame(PaymentStatus::PENDING, $payment->fresh()->status);
        $this->assertEquals(500, (float) $card->fresh()->current_usage);
    }

    public function test_callback_rejects_invalid_checksum(): void
    {
        [$card, $payment] = $this->makeRepaymentPayment(500);
        $this->signedStatusResponse($payment->reference, 'TXN_SUCCESS');

        $params = $this->callbackParams($payment);
        $params['CHECKSUMHASH'] = 'tampered';

        $this->post(route('payments.paytm.callback'), $params)
            ->assertRedirect(route('payments.show', $payment))
            ->assertSessionHas('warning');

        $this->assertSame(PaymentStatus::PENDING, $payment->fresh()->status);
        $this->assertEquals(500, (float) $card->fresh()->current_usage);
    }

    public function test_wallet_initiate_payment_uses_txn_token_flow(): void
    {
        $role = Role::create(['name' => 'Agent', 'slug' => 'agent']);
        $user = User::factory()->create(['role_id' => $role->id]);

        $body = [
            'resultInfo' => ['resultStatus' => 'S', 'resultCode' => '0000', 'resultMsg' => 'Success'],
            'txnToken' => 'WALLET_TOKEN_1',
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

        $this->actingAs($user)
            ->post(route('paytm.pay'), ['amount' => 500])
            ->assertOk()
            ->assertViewHas('txnToken', 'WALLET_TOKEN_1');

        // No old-style standard-checkout view should be rendered.
        $this->assertDatabaseMissing('wallet_transactions', ['txn_token' => null]);
        $row = DB::table('wallet_transactions')->first();
        $this->assertSame('WALLET_TOKEN_1', $row->txn_token);
    }
}