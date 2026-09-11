<?php

namespace Tests\Feature;

use App\Services\Payments\PaytmGateway;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use paytm\paytmchecksum\PaytmChecksum;
use Tests\TestCase;

class PaytmGatewayTest extends TestCase
{
    private const TEST_MID = 'TESTMID1234567890';
    private const TEST_KEY = 'TESTMERCHANTKEY';

    protected function setUp(): void
    {
        parent::setUp();
        config([
            'services.paytm.callback_url' => 'http://127.0.0.1:8000/payments/paytm/callback',
        ]);
    }

    private function gateway(array $creds = [], string $env = 'sandbox'): PaytmGateway
    {
        return new PaytmGateway(array_merge([
            'sandbox_api_key' => self::TEST_MID,
            'sandbox_api_secret' => self::TEST_KEY,
        ], $creds), $env);
    }

    private function signedResponse(array $body): string
    {
        $bodyJson = json_encode($body, JSON_UNESCAPED_SLASHES);
        $signature = PaytmChecksum::generateSignature($bodyJson, self::TEST_KEY);

        return json_encode([
            'body' => $body,
            'head' => ['signature' => $signature],
        ], JSON_UNESCAPED_SLASHES);
    }

    public function test_create_payment_returns_txn_token_via_initiate_transaction(): void
    {
        Http::fake([
            'https://securestage.paytmpayments.com/theia/api/v1/initiateTransaction*' => Http::response(
                $this->signedResponse([
                    'resultInfo' => ['resultStatus' => 'S', 'resultCode' => '0000', 'resultMsg' => 'Success'],
                    'txnToken' => 'TOKEN_123',
                    'txnAmount' => '100.00',
                ]),
                200
            ),
        ]);

        $result = $this->gateway()->createPayment([
            'reference' => 'PAY-1',
            'amount' => 100,
            'currency' => 'INR',
            'user_id' => 5,
        ]);

        $this->assertSame('token', $result['checkout_type']);
        $this->assertSame('TOKEN_123', $result['txn_token']);
        $this->assertSame('PAY-1', $result['order_id']);
        $this->assertSame('staging', $result['environment']);
        $this->assertSame(['mid' => self::TEST_MID, 'orderId' => 'PAY-1'], $result['params']);

        Http::assertSent(function ($request) {
            $payload = json_decode($request->body(), true);

            return $payload['body']['orderId'] === 'PAY-1'
                && $payload['body']['txnAmount']['value'] === '100.00'
                && $payload['body']['txnAmount']['currency'] === 'INR'
                && $payload['body']['websiteName'] === 'WEBSTAGING'
                && $payload['body']['callbackUrl'] === 'http://127.0.0.1:8000/payments/paytm/callback';
        });
    }

    public function test_create_payment_honors_explicit_callback_url(): void
    {
        Http::fake([
            'https://securestage.paytmpayments.com/theia/api/v1/initiateTransaction*' => Http::response(
                $this->signedResponse([
                    'resultInfo' => ['resultStatus' => 'S', 'resultCode' => '0002', 'resultMsg' => 'Success'],
                    'txnToken' => 'TOKEN_999',
                ]),
                200
            ),
        ]);

        $this->gateway()->createPayment([
            'reference' => 'PAY-2',
            'amount' => 50,
            'user_id' => 5,
            'callback_url' => 'https://app.example.com/paytm-callback',
        ]);

        Http::assertSent(function ($request) {
            $payload = json_decode($request->body(), true);

            return $payload['body']['callbackUrl'] === 'https://app.example.com/paytm-callback';
        });
    }

    public function test_create_payment_throws_when_gateway_rejects(): void
    {
        Http::fake([
            'https://securestage.paytmpayments.com/theia/api/v1/initiateTransaction*' => Http::response(
                $this->signedResponse([
                    'resultInfo' => ['resultStatus' => 'F', 'resultCode' => '501', 'resultMsg' => 'System Error'],
                ]),
                200
            ),
        ]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('System Error');

        $this->gateway()->createPayment([
            'reference' => 'PAY-3',
            'amount' => 100,
            'user_id' => 5,
        ]);
    }

    public function test_create_payment_throws_when_txn_token_missing(): void
    {
        Http::fake([
            'https://securestage.paytmpayments.com/theia/api/v1/initiateTransaction*' => Http::response(
                $this->signedResponse([
                    'resultInfo' => ['resultStatus' => 'S', 'resultCode' => '0000', 'resultMsg' => 'Success'],
                ]),
                200
            ),
        ]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('transaction token');

        $this->gateway()->createPayment([
            'reference' => 'PAY-4',
            'amount' => 100,
            'user_id' => 5,
        ]);
    }

    public function test_create_payment_throws_when_credentials_are_placeholders(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessageMatches('/placeholder/');

        $this->gateway(['sandbox_api_key' => 'YOUR_MID'])->createPayment([
            'reference' => 'PAY-5',
            'amount' => 100,
            'user_id' => 5,
        ]);
    }

    public function test_get_payment_status_returns_successful_with_signed_response(): void
    {
        Http::fake([
            'https://securestage.paytmpayments.com/v3/order/status' => Http::response(
                $this->signedResponse([
                    'mid' => self::TEST_MID,
                    'orderId' => 'PAY-10',
                    'txnId' => 'PAYTM_TXN_1',
                    'txnAmount' => '100.00',
                    'resultInfo' => ['resultStatus' => 'TXN_SUCCESS', 'resultCode' => '01', 'resultMsg' => 'Txn Success'],
                ]),
                200
            ),
        ]);

        $result = $this->gateway()->getPaymentStatus('PAY-10');

        $this->assertSame('successful', $result['status']);
        $this->assertSame('PAYTM_TXN_1', $result['payment_id']);
        $this->assertSame('100.00', $result['amount']);

        Http::assertSent(function ($request) {
            $payload = json_decode($request->body(), true);

            return $payload['body']['orderId'] === 'PAY-10' && $payload['body']['mid'] === self::TEST_MID;
        });
    }

    public function test_get_payment_status_maps_failure(): void
    {
        Http::fake([
            'https://securestage.paytmpayments.com/v3/order/status' => Http::response(
                $this->signedResponse([
                    'mid' => self::TEST_MID,
                    'orderId' => 'PAY-11',
                    'resultInfo' => ['resultStatus' => 'TXN_FAILURE', 'resultCode' => '227', 'resultMsg' => 'Failure'],
                ]),
                200
            ),
        ]);

        $result = $this->gateway()->getPaymentStatus('PAY-11');

        $this->assertSame('failed', $result['status']);
    }

    public function test_get_payment_status_throws_on_invalid_signature(): void
    {
        $body = [
            'mid' => self::TEST_MID,
            'orderId' => 'PAY-12',
            'resultInfo' => ['resultStatus' => 'TXN_SUCCESS'],
        ];

        // Build payload, then tamper with the resultStatus after signing.
        $payload = json_decode($this->signedResponse($body), true);
        $payload['body']['resultInfo']['resultStatus'] = 'TXN_FAILURE';

        Http::fake([
            'https://securestage.paytmpayments.com/v3/order/status' => Http::response(
                json_encode($payload, JSON_UNESCAPED_SLASHES),
                200
            ),
        ]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessageMatches('/signature/');

        $this->gateway()->getPaymentStatus('PAY-12');
    }
}