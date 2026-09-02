<?php

namespace App\Services\Payments;

use Illuminate\Support\Facades\Http;
use RuntimeException;
use PaytmChecksum;

class PaytmGateway implements PaymentGatewayInterface
{
    private string $mid;
    private string $key;
    private string $website;
    private string $channel;
    private string $industry;
    private string $env;
    /* public function __construct()
    {
        $this->mid = (string)config('services.paytm.mid');
        $this->key = (string)config('services.paytm.merchant_key');
        $this->website = (string)config('services.paytm.website');
        $this->channel = (string)config('services.paytm.channel_id');
        $this->industry = (string)config('services.paytm.industry_type_id');
        $this->env = (string)config('services.paytm.environment', 'staging');
    } */
    public function __construct(
        \App\Services\PaymentModeService $paymentModeService
    ) {
        $mode = $paymentModeService->current();

        if ($mode === 'live') {

            $this->mid = (string) config(
                'services.paytm.live_mid'
            );

            $this->key = (string) config(
                'services.paytm.live_merchant_key'
            );

            $this->website = (string) config(
                'services.paytm.live_website'
            );
        } else {

            $this->mid = (string) config(
                'services.paytm.test_mid'
            );

            $this->key = (string) config(
                'services.paytm.test_merchant_key'
            );

            $this->website = (string) config(
                'services.paytm.test_website'
            );
        }
    }
    private function base()
    {
        return $this->env === 'production' ? 'https://securegw.paytm.in' : 'https://securegw-stage.paytm.in';
    }
    public function createPayment(array $data): array
    {
        if (!$this->mid || !$this->key) throw new RuntimeException('Paytm credentials are not configured.');
        $body = ['requestType' => 'Payment', 'mid' => $this->mid, 'websiteName' => $this->website, 'orderId' => $data['reference'], 'callbackUrl' => config('services.paytm.callback_url'), 'txnAmount' => ['value' => number_format($data['amount'], 2, '.', ''), 'currency' => $data['currency'] ?? 'INR'], 'userInfo' => ['custId' => 'user_' . $data['user_id']]];
        $params = ['body' => $body, 'head' => ['signature' => PaytmChecksum::generateSignature(json_encode($body, JSON_UNESCAPED_SLASHES), $this->key)]];
        $url = $this->base() . '/theia/api/v1/initiateTransaction?mid=' . urlencode($this->mid) . '&orderId=' . urlencode($data['reference']);
        $r = Http::asJson()->post($url, $params);
        $r->throw();
        $j = $r->json();
        return ['status' => $j['body']['resultInfo']['resultStatus'] === 'S' ? 'created' : 'failed', 'order_id' => $data['reference'], 'payment_id' => null, 'token' => $j['body']['txnToken'] ?? null, 'raw' => $j];
    }
    public function verifyPayment(array $data): array
    {
        $params = $data['params'] ?? [];
        $checksum = $params['CHECKSUMHASH'] ?? '';
        unset($params['CHECKSUMHASH']);
        if (!$checksum || !PaytmChecksum::verifySignature($params, $this->key, $checksum)) throw new RuntimeException('Invalid Paytm checksum.');
        if (isset($data['expected_amount']) && number_format((float)($params['TXNAMOUNT'] ?? 0), 2, '.', '') !== number_format((float)$data['expected_amount'], 2, '.', '')) throw new RuntimeException('Paytm amount mismatch.');
        $status = $params['STATUS'] ?? 'PENDING';
        return ['status' => $status === 'TXN_SUCCESS' ? 'successful' : ($status === 'TXN_FAILURE' ? 'failed' : 'pending'), 'payment_id' => $params['TXNID'] ?? null, 'raw' => $params];
    }
    public function handleWebhook(string $rawBody, array $headers): array
    {
        $payload = json_decode($rawBody, true) ?? [];
        $checksum = $payload['CHECKSUMHASH'] ?? null;
        unset($payload['CHECKSUMHASH']);
        if ($checksum && !PaytmChecksum::verifySignature($payload, $this->key, $checksum)) throw new RuntimeException('Invalid Paytm checksum.');
        return ['event_id' => hash('sha256', $rawBody), 'event_type' => 'paytm.callback', 'payload' => array_merge($payload, ['CHECKSUMHASH' => $checksum])];
    }
    public function refund(array $data): array
    {
        throw new RuntimeException('Paytm refund API wiring should be enabled after merchant refund credentials/contract are confirmed.');
    }
    public function getPaymentStatus(string $paymentId): array
    {
        return ['status' => 'pending', 'payment_id' => $paymentId];
    }
}
