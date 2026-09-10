<?php

namespace App\Http\Controllers;

use App\Models\PaymentGateway;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class PaytmPaymentController extends Controller
{
    private string $environment;
    private string $merchantKey;
    private string $mid;
    private string $apiUrl;

    public function __construct()
    {
        $gateway = PaymentGateway::where('slug', 'paytm')->first();
        if (!$gateway) {
            throw new \Exception('Paytm gateway not configured.');
        }

        $credentials = is_array($gateway->credentials) ? $gateway->credentials : [];
        $env = $gateway->environment ?? ($gateway->mode === 'live' ? 'production' : 'sandbox');

        $this->environment = $env === 'production' ? 'production' : 'staging';
        $this->merchantKey = trim(urldecode((string) ($credentials['production_api_secret'] ?? $credentials['live_key_secret'] ?? $credentials['sandbox_key_secret'] ?? $gateway->live_key_secret ?? $gateway->sandbox_key_secret ?? '')));
        $this->mid = trim((string) ($credentials['production_api_key'] ?? $credentials['live_key_id'] ?? $credentials['sandbox_key_id'] ?? $gateway->live_key_id ?? $gateway->sandbox_key_id ?? ''));
        $this->apiUrl = $this->environment === 'production'
            ? 'https://secure.paytmpayments.com'
            : 'https://securestage.paytmpayments.com';
    }

    public function showCheckoutForm()
    {
        return view('paytm.checkout');
    }

    public function initiatePayment(Request $request)
    {
        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'min:1', 'max:5000000'],
        ]);

        $amount = number_format((float) $validated['amount'], 2, '.', '');
        $orderId = 'ORDER_' . time() . '_' . Str::upper(Str::random(6));
        $userId = auth()->id();
        $customerId = $userId ? 'USER_' . $userId : 'CUST_' . Str::upper(Str::random(10));

        $params = [
            'MID' => $this->mid,
            'ORDER_ID' => $orderId,
            'CUST_ID' => $customerId,
            'TXN_AMOUNT' => $amount,
            'CHANNEL_ID' => 'WEB',
            'WEBSITE' => 'DEFAULT',
            'CALLBACK_URL' => route('paytm.callback'),
            'INDUSTRY_TYPE_ID' => 'Retail',
        ];

        $checksum = \PaytmChecksum::generateSignature($params, $this->merchantKey);

        DB::table('wallet_transactions')->updateOrInsert(
            ['order_id' => $orderId],
            [
                'user_id'    => $userId,
                'amount'     => $amount,
                'type'       => 'credit',
                'status'     => 'pending',
                'gateway'    => 'paytm',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        return view('paytm.redirect-standard', compact('mid', 'orderId', 'amount', 'environment', 'params', 'checksum'));
    }

    public function handleCallback(Request $request)
    {
        $params = $request->all();
        $paytmChecksum = $params['CHECKSUMHASH'] ?? '';
        unset($params['CHECKSUMHASH']);
        $orderId = $params['ORDERID'] ?? null;

        if (!\PaytmChecksum::verifySignature($params, $this->merchantKey, $paytmChecksum)) {
            return response('Invalid payment signature.', 400);
        }

        $statusResult = $this->checkTransactionStatus($orderId);

        if ($statusResult['resultStatus'] === 'TXN_SUCCESS') {
            return $this->processSuccessfulPayment($orderId, $statusResult, $params);
        }

        if (in_array($statusResult['resultStatus'], ['PENDING', 'TXN_PENDING'])) {
            return $this->markPaymentPending($orderId, $statusResult);
        }

        return $this->markPaymentFailed($orderId, $statusResult);
    }

    protected function checkTransactionStatus(string $orderId): array
    {
        $body = ['mid' => $this->mid, 'orderId' => $orderId];
        $bodyJson = json_encode($body, JSON_UNESCAPED_SLASHES);
        $checksum = \PaytmChecksum::generateSignature($bodyJson, $this->merchantKey);

        $fullPayload = json_encode(['body' => $body, 'head' => ['signature' => $checksum]], JSON_UNESCAPED_SLASHES);

        $response = Http::withHeaders(['Content-Type' => 'application/json'])
            ->timeout(30)
            ->withBody($fullPayload, 'application/json')
            ->post($this->apiUrl . '/v3/order/status');

        $resultInfo = data_get($response->json(), 'body.resultInfo', []);

        return [
            'resultStatus' => $resultInfo['resultStatus'] ?? 'UNKNOWN',
            'txnId' => data_get($response->json(), 'body.txnId'),
            'txnAmount' => data_get($response->json(), 'body.txnAmount'),
            'bankTxnId' => data_get($response->json(), 'body.bankTxnId'),
            'paymentMode' => data_get($response->json(), 'body.paymentMode'),
            'gatewayName' => data_get($response->json(), 'body.gatewayName'),
            'raw' => $response->json(),
        ];
    }

    protected function processSuccessfulPayment(string $orderId, array $statusResult, array $callbackData)
    {
        $transaction = DB::table('wallet_transactions')->where('order_id', $orderId)->first();
        if (!$transaction) {
            return redirect()->route('paytm.checkout')->with('error', 'Transaction not found.');
        }

        if (abs((float) $transaction->amount - (float) ($statusResult['txnAmount'] ?? 0)) > 0.001) {
            return redirect()->route('paytm.checkout')->with('error', 'Amount mismatch.');
        }

        DB::transaction(function () use ($transaction, $statusResult, $callbackData) {
            $local = DB::table('wallet_transactions')->where('id', $transaction->id)->lockForUpdate()->first();
            if ($local->status === 'success') {
                return;
            }

            DB::table('wallet_transactions')->where('id', $local->id)->update([
                'status' => 'success',
                'txn_id' => $statusResult['txnId'] ?? null,
                'bank_txn_id' => $statusResult['bankTxnId'] ?? null,
                'payment_mode' => $statusResult['paymentMode'] ?? null,
                'gateway_response' => json_encode(['callback' => $callbackData, 'status_api' => $statusResult['raw'] ?? null]),
                'paid_at' => now(),
                'updated_at' => now(),
            ]);

            $wallet = DB::table('wallets')->where('user_id', $local->user_id)->lockForUpdate()->first();
            if ($wallet) {
                DB::table('wallets')->where('id', $wallet->id)->update([
                    'balance' => (float) $wallet->balance + (float) $local->amount,
                    'updated_at' => now(),
                ]);
            } else {
                DB::table('wallets')->insert([
                    'user_id' => $local->user_id,
                    'balance' => $local->amount,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        });

        return redirect()->route('paytm.checkout')->with('success', 'Payment successful!');
    }

    protected function markPaymentPending(string $orderId, array $statusResult)
    {
        DB::table('wallet_transactions')
            ->where('order_id', $orderId)
            ->update([
                'status' => 'pending',
                'gateway_response' => json_encode($statusResult),
                'updated_at' => now(),
            ]);

        return redirect()->route('paytm.checkout')->with('warning', 'Payment is pending.');
    }

    protected function markPaymentFailed(string $orderId, array $statusResult)
    {
        DB::table('wallet_transactions')
            ->where('order_id', $orderId)
            ->update([
                'status' => 'failed',
                'gateway_response' => json_encode($statusResult),
                'updated_at' => now(),
            ]);

        return redirect()->route('paytm.checkout')->with('error', 'Payment failed.');
    }
}
