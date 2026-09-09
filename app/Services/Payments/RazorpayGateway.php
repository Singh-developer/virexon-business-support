<?php

namespace App\Services\Payments;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class RazorpayGateway implements PaymentGatewayInterface
{
    private string $key;
    private string $secret;

    public function __construct(
        array $credentials = [],
        string $environment = 'sandbox'
    ) {
        if ($environment === 'production') {
            $this->key = (string) ($credentials['production_api_key'] ?? $credentials['key_id'] ?? '');
            $this->secret = (string) ($credentials['production_api_secret'] ?? $credentials['key_secret'] ?? '');
        } else {
            $this->key = (string) ($credentials['sandbox_api_key'] ?? $credentials['key_id'] ?? '');
            $this->secret = (string) ($credentials['sandbox_api_secret'] ?? $credentials['key_secret'] ?? '');
        }
    }

    private function api()
    {
        return Http::withBasicAuth($this->key, $this->secret)->acceptJson();
    }

    public function createPayment(array $data): array
    {
        if (! $this->key || ! $this->secret) {
            throw new RuntimeException('Razorpay credentials are not configured.');
        }

        $r = $this->api()->post('https://api.razorpay.com/v1/orders', [
            'amount' => (int) round($data['amount'] * 100),
            'currency' => $data['currency'] ?? 'INR',
            'receipt' => $data['reference'],
        ]);

        $r->throw();

        $j = $r->json();

        return [
            'status' => 'created',
            'order_id' => $j['id'],
            'payment_id' => null,
            'raw' => $j,
        ];
    }

    public function verifyPayment(array $data): array
    {
        $order = (string) ($data['order_id'] ?? '');
        $payment = (string) ($data['payment_id'] ?? '');
        $signature = (string) ($data['signature'] ?? '');

        $expected = hash_hmac('sha256', $order . '|' . $payment, $this->secret);

        if (! $signature || ! hash_equals($expected, $signature)) {
            throw new RuntimeException('Invalid Razorpay payment signature.');
        }

        return ['status' => 'successful', 'payment_id' => $payment];
    }

    public function handleWebhook(string $rawBody, array $headers): array
    {
        $sig = $headers['x-razorpay-signature'] ??
            $headers['X-Razorpay-Signature'] ??
            null;

        if (! $sig || ! $this->secret) {
            throw new RuntimeException('Missing Razorpay webhook signature or secret.');
        }

        $expected = hash_hmac('sha256', $rawBody, $this->secret);

        if (! hash_equals($expected, $sig)) {
            throw new RuntimeException('Invalid Razorpay webhook signature.');
        }

        $payload = json_decode($rawBody, true, 512, JSON_THROW_ON_ERROR);

        return [
            'event_id' => hash('sha256', $rawBody),
            'event_type' => $payload['event'] ?? 'unknown',
            'payload' => $payload,
        ];
    }

    public function refund(array $data): array
    {
        $id = (string) $data['payment_id'];

        $r = $this->api()->post(
            "https://api.razorpay.com/v1/payments/{$id}/refund",
            ['amount' => (int) round(($data['amount'] ?? 0) * 100)]
        );

        $r->throw();

        $j = $r->json();

        return [
            'status' => 'refunded',
            'refund_id' => $j['id'] ?? null,
            'raw' => $j,
        ];
    }

    public function getPaymentStatus(string $paymentId): array
    {
        $r = $this->api()->get("https://api.razorpay.com/v1/payments/{$paymentId}");

        $r->throw();

        $j = $r->json();

        return [
            'status' => $j['status'] ?? 'unknown',
            'payment_id' => $paymentId,
            'raw' => $j,
        ];
    }
}
