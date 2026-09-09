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
        if ($environment === 'production') {
            $this->mid = (string) ($credentials['production_api_key'] ?? $credentials['mid'] ?? '');
            $this->key = (string) ($credentials['production_api_secret'] ?? $credentials['merchant_key'] ?? '');
            $this->website = (string) ($credentials['production_website'] ?? $credentials['website'] ?? 'DEFAULT');
            $this->env = 'production';
        } else {
            $this->mid = (string) ($credentials['sandbox_api_key'] ?? $credentials['mid'] ?? '');
            $this->key = (string) ($credentials['sandbox_api_secret'] ?? $credentials['merchant_key'] ?? '');
            $this->website = (string) ($credentials['sandbox_website'] ?? $credentials['website'] ?? 'WEBSTAGING');
            $this->env = 'staging';
        }
    }

    private function baseUrl(): string
    {
        return $this->env === 'production'
            ? 'https://securegw.paytm.in'
            : 'https://securegw-stage.paytm.in';
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

        $callbackUrl = config('services.paytm.callback_url', url('/payments/paytm/callback'));

        $body = [
            'requestType' => 'Payment',
            'mid' => $this->mid,
            'websiteName' => $this->website,
            'orderId' => $data['reference'],
            'txnAmount' => [
                'value' => number_format($data['amount'], 2, '.', ''),
                'currency' => $data['currency'] ?? 'INR',
            ],
            'userInfo' => [
                'custId' => 'user_' . $data['user_id'],
            ],
            'callbackUrl' => $callbackUrl,
        ];

        Log::info('PaytmGateway: Request body built', [
            'mid' => $this->mid,
            'websiteName' => $this->website,
            'orderId' => $data['reference'],
            'amount' => $data['amount'],
            'callbackUrl' => $callbackUrl,
        ]);

        $bodyJson = json_encode($body, JSON_UNESCAPED_SLASHES);

        $signature = PaytmChecksum::generateSignature($bodyJson, $this->key);

        $params = [
            'body' => $body,
            'head' => [
                'signature' => $signature,
            ],
        ];

        $url = $this->baseUrl() . '/theia/api/v1/initiateTransaction?mid=' .
            urlencode($this->mid) .
            '&orderId=' . urlencode($data['reference']);

        Log::info('PaytmGateway: Sending request', [
            'url' => $url,
            'body_json' => $bodyJson,
        ]);

        $response = Http::timeout(30)->asJson()->post($url, $params);

        $json = $response->json();

        Log::info('PaytmGateway: Response', [
            'status' => $response->status(),
            'body' => $json,
        ]);

        if ($response->failed()) {
            throw new RuntimeException(
                'Paytm API error: ' . ($json['body']['resultInfo']['resultMsg'] ?? 'HTTP ' . $response->status())
            );
        }

        $resultInfo = $json['body']['resultInfo'] ?? [];
        $resultCode = $resultInfo['resultCode'] ?? '';
        $resultStatus = $resultInfo['resultStatus'] ?? '';

        if ($resultCode !== '01' && $resultStatus !== 'S') {
            throw new RuntimeException(
                'Paytm error: ' . ($resultInfo['resultMsg'] ?? 'Unknown error') .
                ' [Code: ' . $resultCode . ']'
            );
        }

        return [
            'status' => 'created',
            'order_id' => $data['reference'],
            'payment_id' => null,
            'txn_token' => $json['body']['txnToken'] ?? null,
            'raw' => $json,
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

        $signature = PaytmChecksum::generateSignature(
            json_encode($body, JSON_UNESCAPED_SLASHES),
            $this->key
        );

        $params = [
            'body' => $body,
            'head' => [
                'signature' => $signature,
            ],
        ];

        $url = $this->baseUrl() . '/merchant/status/api/v1/getPaymentStatus?mid=' .
            urlencode($this->mid) .
            '&orderId=' . urlencode($orderId);

        $response = Http::timeout(30)->asJson()->post($url, $params);
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
