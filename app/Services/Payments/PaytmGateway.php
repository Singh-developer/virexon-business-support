<?php

namespace App\Services\Payments;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use paytm\paytmchecksum\PaytmChecksum;

class PaytmGateway implements PaymentGatewayInterface
{
    private string $mid;
    private string $key;
    private string $website;
    private string $env;
    private bool $signed = false;

    public function __construct(
        array $credentials = [],
        string $environment = 'sandbox'
    ) {
        $envConfig = config('services.paytm', []);

        if ($environment === 'production') {
            $this->mid = $this->resolve($credentials, ['production_api_key', 'live_key_id', 'mid'], $envConfig['mid'] ?? '');
            $this->key = $this->resolve($credentials, ['production_api_secret', 'live_key_secret', 'merchant_key'], $envConfig['key'] ?? '');
            $this->website = $this->resolve($credentials, ['production_website', 'website'], $envConfig['website'] ?? 'DEFAULT');
            $this->env = 'production';
        } else {
            $this->mid = $this->resolve($credentials, ['sandbox_api_key', 'sandbox_key_id', 'mid'], $envConfig['mid'] ?? '');
            $this->key = $this->resolve($credentials, ['sandbox_api_secret', 'sandbox_key_secret', 'merchant_key'], $envConfig['key'] ?? '');
            $this->website = $this->resolve($credentials, ['sandbox_website', 'website'], $envConfig['website'] ?? 'WEBSTAGING');
            $this->env = 'staging';
        }
    }

    private function resolve(array $credentials, array $keys, string $fallback): string
    {
        foreach ($keys as $key) {
            if (! empty($credentials[$key]) && is_string($credentials[$key])) {
                return trim($credentials[$key]);
            }
        }

        return trim($fallback);
    }

    private function merchantKey(): string
    {
        return urldecode($this->key);
    }

    private function baseUrl(): string
    {
        return $this->env === 'production'
            ? 'https://secure.paytmpayments.com'
            : 'https://securestage.paytmpayments.com';
    }

    private function assertConfigured(): void
    {
        if (! $this->mid || ! $this->key) {
            Log::error('PaytmGateway: MID or Key is empty', [
                'mid' => $this->mid,
                'env' => $this->env,
            ]);
            throw new RuntimeException('Paytm credentials are not configured. Please check MID and Merchant Key in Platform Settings.');
        }

        $placeholders = ['YOUR_MID', 'YOUR_KEY', 'your_mid', 'your_key', 'xxxxx'];
        if (in_array(trim($this->mid), $placeholders, true) || in_array(trim($this->key), $placeholders, true)) {
            Log::error('PaytmGateway: Credentials are placeholder values', [
                'mid' => $this->mid,
                'env' => $this->env,
            ]);
            throw new RuntimeException('Paytm credentials contain placeholder values. Please enter your actual MID and Merchant Key in Platform Settings.');
        }
    }

    public function createPayment(array $data): array
    {
        Log::info('PaytmGateway: createPayment called', [
            'mid_length' => strlen($this->mid),
            'key_length' => strlen($this->key),
            'website' => $this->website,
            'env' => $this->env,
        ]);

        $this->assertConfigured();

        $orderId = (string) ($data['reference'] ?? ('ORDER_' . time()));
        $amount = number_format((float) ($data['amount'] ?? 0), 2, '.', '');
        $currency = (string) ($data['currency'] ?? 'INR');

        $callbackUrl = $data['callback_url']
            ?? config('services.paytm.callback_url')
            ?? url('/payments/paytm/callback');

        $body = [
            'requestType' => 'Payment',
            'mid' => $this->mid,
            'websiteName' => $this->website,
            'orderId' => $orderId,
            'txnAmount' => [
                'value' => $amount,
                'currency' => $currency,
            ],
            'userInfo' => [
                'custId' => 'user_' . ($data['user_id'] ?? 'guest'),
            ],
            'callbackUrl' => $callbackUrl,
        ];

        $url = $this->baseUrl() . '/theia/api/v1/initiateTransaction?mid='
            . urlencode($this->mid) . '&orderId=' . urlencode($orderId);

        Log::info('PaytmGateway: initiateTransaction called', [
            'mid' => $this->mid,
            'orderId' => $orderId,
            'amount' => $amount,
            'callbackUrl' => $callbackUrl,
            'website' => $this->website,
        ]);

        $response = Http::timeout(30)
            ->withHeaders(['Content-Type' => 'application/json'])
            ->withBody($this->signedPayload($body), 'application/json')
            ->post($url);

        $payload = $response->json() ?? [];
        $this->verifyResponseSignature($response, $payload);

        $resultInfo = $payload['body']['resultInfo'] ?? [];
        $resultStatus = $resultInfo['resultStatus'] ?? 'F';
        $resultCode = $resultInfo['resultCode'] ?? '';
        $resultMsg = $resultInfo['resultMsg'] ?? 'Unknown Paytm error';

        if ($resultStatus !== 'S' || ! in_array($resultCode, ['0000', '0002'], true)) {
            Log::error('PaytmGateway: initiateTransaction failed', [
                'mid' => $this->mid,
                'orderId' => $orderId,
                'resultStatus' => $resultStatus,
                'resultCode' => $resultCode,
                'resultMsg' => $resultMsg,
                'env' => $this->env,
            ]);

            $hint = ($resultCode === '501' || ! $this->signed)
                ? ' This usually means the MID/Key is not accepted on the ' . strtoupper($this->env) . ' Paytm environment (public sample/tutorial MIDs do not work).'
                : '';

            throw new RuntimeException('Paytm could not start the transaction: ' . $resultMsg . $hint);
        }

        $txnToken = $payload['body']['txnToken'] ?? null;
        if (! $txnToken) {
            Log::error('PaytmGateway: txnToken missing from initiateTransaction', [
                'mid' => $this->mid,
                'orderId' => $orderId,
            ]);
            throw new RuntimeException('Paytm did not return a transaction token.');
        }

        return [
            'status' => 'created',
            'order_id' => $orderId,
            'payment_id' => null,
            'txn_token' => $txnToken,
            'checkout_type' => 'token',
            'checksum' => null,
            'params' => [
                'mid' => $this->mid,
                'orderId' => $orderId,
            ],
            'environment' => $this->env,
            'gateway_response' => $payload,
        ];
    }

    public function verifyPayment(array $data): array
    {
        $params = $data['params'] ?? [];
        $checksum = $params['CHECKSUMHASH'] ?? '';
        unset($params['CHECKSUMHASH']);

        if (! $checksum || ! PaytmChecksum::verifySignature($params, $this->merchantKey(), $checksum)) {
            throw new RuntimeException('Invalid Paytm checksum.');
        }

        if (
            isset($data['expected_amount']) &&
            $data['expected_amount'] !== null &&
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
        $url = $this->baseUrl() . '/v3/order/status';

        $response = Http::timeout(30)
            ->withHeaders(['Content-Type' => 'application/json'])
            ->withBody($this->signedPayload([
                'mid' => $this->mid,
                'orderId' => $orderId,
            ]), 'application/json')
            ->post($url);

        $json = $response->json() ?? [];
        $this->verifyResponseSignature($response, $json);

        $resultInfo = $json['body']['resultInfo'] ?? [];
        $status = $resultInfo['resultStatus'] ?? 'UNKNOWN';

        return [
            'status' => $status === 'TXN_SUCCESS'
                ? 'successful'
                : ($status === 'TXN_FAILURE' ? 'failed' : 'pending'),
            'payment_id' => $json['body']['txnId'] ?? null,
            'transaction_id' => $json['body']['txnId'] ?? null,
            'amount' => $json['body']['txnAmount'] ?? null,
            'result_status' => $status,
            'raw' => $json,
        ];
    }

    public function handleWebhook(string $rawBody, array $headers): array
    {
        $payload = json_decode($rawBody, true) ?? [];
        $checksum = $payload['CHECKSUMHASH'] ?? null;
        unset($payload['CHECKSUMHASH']);

        if ($checksum && ! PaytmChecksum::verifySignature($payload, $this->merchantKey(), $checksum)) {
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

    private function signedPayload(array $body): string
    {
        $bodyJson = json_encode($body, JSON_UNESCAPED_SLASHES);
        $signature = PaytmChecksum::generateSignature($bodyJson, $this->merchantKey());

        return json_encode([
            'body' => $body,
            'head' => ['signature' => $signature],
        ], JSON_UNESCAPED_SLASHES);
    }

    private function verifyResponseSignature(Response $response, array $payload): void
    {
        $signature = $payload['head']['signature'] ?? null;

        if (! $signature) {
            Log::warning('PaytmGateway: response has no signature to verify', [
                'env' => $this->env,
            ]);

            return;
        }

        $rawBody = self::extractRawBodyJson((string) $response->body());

        if ($rawBody === null || ! PaytmChecksum::verifySignature($rawBody, $this->merchantKey(), $signature)) {
            Log::error('PaytmGateway: response signature verification failed', [
                'env' => $this->env,
            ]);
            throw new RuntimeException('Paytm response signature verification failed.');
        }

        $this->signed = true;
    }

    /**
     * Extract the raw JSON substring of the "body" object so the exact
     * serialized payload Paytm signed can be verified.
     */
    private static function extractRawBodyJson(string $raw): ?string
    {
        $start = strpos($raw, '"body"');
        if ($start === false) {
            return null;
        }

        $open = strpos($raw, '{', $start);
        if ($open === false) {
            return null;
        }

        $depth = 0;
        $inString = false;
        $escaped = false;
        $length = strlen($raw);

        for ($i = $open; $i < $length; $i++) {
            $char = $raw[$i];

            if ($inString) {
                if ($escaped) {
                    $escaped = false;
                    continue;
                }
                if ($char === '\\') {
                    $escaped = true;
                    continue;
                }
                if ($char === '"') {
                    $inString = false;
                }
                continue;
            }

            if ($char === '"') {
                $inString = true;
                continue;
            }

            if ($char === '{') {
                $depth++;
            } elseif ($char === '}') {
                $depth--;
                if ($depth === 0) {
                    return substr($raw, $open, $i - $open + 1);
                }
            }
        }

        return null;
    }
}