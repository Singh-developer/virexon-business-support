<?php

namespace App\Services\Payments;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use PaytmChecksum;

class PaytmGateway implements PaymentGatewayInterface
{
    private string $mid;
    private string $key;
    private string $website;
    private string $env;

    public function __construct(
        array $credentials = [],
        string $environment = 'sandbox'
    ) {
        $envConfig = config('services.paytm', []);

        if ($environment === 'production') {
            $this->mid = trim((string) ($credentials['production_api_key'] ?? $credentials['live_key_id'] ?? $credentials['mid'] ?? $envConfig['mid'] ?? ''));
            $this->key = trim(urldecode((string) ($credentials['production_api_secret'] ?? $credentials['live_key_secret'] ?? $credentials['merchant_key'] ?? $envConfig['key'] ?? '')));
            $this->website = trim((string) ($credentials['production_website'] ?? $credentials['website'] ?? $envConfig['website'] ?? 'DEFAULT'));
            $this->env = 'production';
        } else {
            $this->mid = trim((string) ($credentials['sandbox_api_key'] ?? $credentials['sandbox_key_id'] ?? $credentials['mid'] ?? $envConfig['mid'] ?? ''));
            $this->key = trim(urldecode((string) ($credentials['sandbox_api_secret'] ?? $credentials['sandbox_key_secret'] ?? $credentials['merchant_key'] ?? $envConfig['key'] ?? '')));
            $this->website = trim((string) ($credentials['sandbox_website'] ?? $credentials['website'] ?? $envConfig['website'] ?? 'WEBSTAGING'));
            $this->env = 'staging';
        }
    }

    private function baseUrl(): string
    {
        return $this->env === 'production'
            ? 'https://secure.paytmpayments.com'
            : 'https://securestage.paytmpayments.com';
    }

    public function createPayment(array $data): array
    {
        Log::info('PaytmGateway: createPayment called', [
            'mid_length' => strlen($this->mid),
            'key_length' => strlen($this->key),
            'website' => $this->website,
            'env' => $this->env,
        ]);

        if (! $this->mid || ! $this->key) {
            Log::error('PaytmGateway: MID or Key is empty', [
                'mid' => $this->mid,
                'env' => $this->env,
            ]);
            throw new RuntimeException('Paytm credentials are not configured. Please check MID and Merchant Key in Platform Settings.');
        }

        $placeholders = ['YOUR_MID', 'YOUR_KEY', 'your_mid', 'your_key', 'xxxxx', ''];
        if (in_array(trim($this->mid), $placeholders, true) || in_array(trim($this->key), $placeholders, true)) {
            Log::error('PaytmGateway: Credentials are placeholder values', [
                'mid' => $this->mid,
                'env' => $this->env,
            ]);
            throw new RuntimeException('Paytm credentials contain placeholder values. Please enter your actual MID and Merchant Key in Platform Settings.');
        }

        $callbackUrl = config('services.paytm.callback_url', url('/payments/paytm/callback'));
        $amount = number_format($data['amount'], 2, '.', '');

        $params = [
            'MID' => $this->mid,
            'ORDER_ID' => $data['reference'],
            'CUST_ID' => 'user_' . $data['user_id'],
            'TXN_AMOUNT' => $amount,
            'CHANNEL_ID' => config('services.paytm.channel', 'WEB'),
            'WEBSITE' => $this->website,
            'CALLBACK_URL' => $callbackUrl,
            'INDUSTRY_TYPE_ID' => config('services.paytm.industry', 'Retail'),
        ];

        $checksum = PaytmChecksum::generateSignature($params, $this->key);

        Log::info('PaytmGateway: Standard Checkout params built', [
            'mid' => $this->mid,
            'orderId' => $data['reference'],
            'amount' => $amount,
            'website' => $this->website,
            'checksum_length' => strlen($checksum),
        ]);

        return [
            'status' => 'created',
            'order_id' => $data['reference'],
            'payment_id' => null,
            'txn_token' => null,
            'checkout_type' => 'standard',
            'checksum' => $checksum,
            'params' => $params,
            'environment' => $this->env === 'production' ? 'production' : 'staging',
        ];
    }

    public function verifyPayment(array $data): array
    {
        $params = $data['params'] ?? [];
        $checksum = $params['CHECKSUMHASH'] ?? '';
        unset($params['CHECKSUMHASH']);

        if (! $checksum || ! PaytmChecksum::verifySignature($params, $this->key, $checksum)) {
            throw new RuntimeException('Invalid Paytm checksum.');
        }

        if (
            isset($data['expected_amount']) &&
            number_format((float) ($params['TXNAMOUNT'] ?? 0), 2, '.', '') !==
            number_format((float) $data['expected_amount'], 2, '.', '')
        ) {
            throw new RuntimeException('Paytm amount mismatch.');
        }

        $status = $params['STATUS'] ?? 'PENDING';

        return [
            'status' => $status === 'TXN_SUCCESS'
                ? 'successful'
                : ($status === 'TXN_FAILURE' ? 'failed' : 'pending'),
            'payment_id' => $params['TXNID'] ?? null,
            'raw' => $params,
        ];
    }

    public function getPaymentStatus(string $orderId): array
    {
        $body = [
            'mid' => $this->mid,
            'orderId' => $orderId,
        ];

        $bodyJson = json_encode($body, JSON_UNESCAPED_SLASHES);

        $signature = PaytmChecksum::generateSignature(
            $bodyJson,
            $this->key
        );

        $fullPayload = json_encode([
            'body' => $body,
            'head' => [
                'signature' => $signature,
            ],
        ], JSON_UNESCAPED_SLASHES);

        $url = $this->baseUrl() . '/merchant/status/api/v1/getPaymentStatus?mid=' .
            urlencode($this->mid) .
            '&orderId=' . urlencode($orderId);

        $response = Http::timeout(30)
            ->withHeaders(['Content-Type' => 'application/json'])
            ->withBody($fullPayload, 'application/json')
            ->post($url);
        $json = $response->json();

        $resultInfo = $json['body']['resultInfo'] ?? [];
        $status = $resultInfo['resultStatus'] ?? 'UNKNOWN';

        return [
            'status' => $status === 'TXN_SUCCESS'
                ? 'successful'
                : ($status === 'TXN_FAILURE' ? 'failed' : 'pending'),
            'payment_id' => $json['body']['txnId'] ?? null,
            'raw' => $json,
        ];
    }

    public function handleWebhook(string $rawBody, array $headers): array
    {
        $payload = json_decode($rawBody, true) ?? [];
        $checksum = $payload['CHECKSUMHASH'] ?? null;
        unset($payload['CHECKSUMHASH']);

        if ($checksum && ! PaytmChecksum::verifySignature($payload, $this->key, $checksum)) {
            throw new RuntimeException('Invalid Paytm checksum.');
        }

        return [
            'event_id' => hash('sha256', $rawBody),
            'event_type' => 'paytm.callback',
            'payload' => array_merge($payload, ['CHECKSUMHASH' => $checksum]),
        ];
    }

    public function refund(array $data): array
    {
        throw new RuntimeException('Paytm refund not implemented.');
    }
}
