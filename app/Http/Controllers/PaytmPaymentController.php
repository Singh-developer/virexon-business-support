<?php

namespace App\Http\Controllers;

use App\Services\PaymentGatewayManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class PaytmPaymentController extends Controller
{
    private function gateway(): \App\Services\Payments\PaytmGateway
    {
        return app(PaymentGatewayManager::class)->driver('paytm');
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

        try {
            $result = $this->gateway()->createPayment([
                'reference' => $orderId,
                'amount' => $amount,
                'currency' => 'INR',
                'user_id' => $userId,
                'callback_url' => route('paytm.callback'),
            ]);
        } catch (\Exception $e) {
            Log::error('PaytmPaymentController: initiatePayment failed', [
                'order_id' => $orderId,
                'error' => $e->getMessage(),
            ]);

            return back()
                ->withInput()
                ->with('error', $e->getMessage());
        }

        DB::table('wallet_transactions')->updateOrInsert(
            ['order_id' => $orderId],
            [
                'user_id' => $userId,
                'amount' => $amount,
                'type' => 'credit',
                'status' => 'pending',
                'gateway' => 'paytm',
                'txn_token' => $result['txn_token'],
                'gateway_response' => json_encode($result['gateway_response'] ?? []),
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        return view('paytm.redirect', [
            'environment' => $result['environment'] ?? 'staging',
            'mid' => $result['params']['mid'] ?? '',
            'orderId' => $orderId,
            'txnToken' => $result['txn_token'],
            'amount' => $amount,
        ]);
    }

    public function handleCallback(Request $request)
    {
        $params = $request->all();
        $orderId = $params['ORDERID'] ?? null;

        if (! $orderId) {
            return response('Missing order ID', 400);
        }

        $transaction = DB::table('wallet_transactions')->where('order_id', $orderId)->first();

        if (! $transaction) {
            return redirect()->route('paytm.checkout')->with('error', 'Transaction not found.');
        }

        try {
            $gateway = $this->gateway();

            $gateway->verifyPayment([
                'params' => $params,
                'expected_amount' => $transaction->amount,
            ]);

            $status = $gateway->getPaymentStatus($orderId);
        } catch (\Exception $e) {
            Log::error('PaytmPaymentController: callback verification failed', [
                'order_id' => $orderId,
                'error' => $e->getMessage(),
            ]);

            return redirect()->route('paytm.checkout')->with('warning', 'Payment verification is pending. The transaction will be confirmed by a status check.');
        }

        if ($status['status'] === 'successful') {
            return $this->processSuccessfulPayment($orderId, $status, $params);
        }

        if ($status['status'] === 'pending') {
            return $this->markPaymentPending($orderId, $status);
        }

        return $this->markPaymentFailed($orderId, $status);
    }

    protected function processSuccessfulPayment(string $orderId, array $status, array $callbackData)
    {
        $transaction = DB::table('wallet_transactions')->where('order_id', $orderId)->first();

        if (! $transaction) {
            return redirect()->route('paytm.checkout')->with('error', 'Transaction not found.');
        }

        if (abs((float) $transaction->amount - (float) ($status['amount'] ?? 0)) > 0.001) {
            return redirect()->route('paytm.checkout')->with('error', 'Amount mismatch.');
        }

        $rawBody = $status['raw']['body'] ?? $status['raw'] ?? [];

        DB::transaction(function () use ($transaction, $status, $callbackData, $rawBody) {
            $local = DB::table('wallet_transactions')->where('id', $transaction->id)->lockForUpdate()->first();

            if ($local->status === 'success') {
                return;
            }

            DB::table('wallet_transactions')->where('id', $local->id)->update([
                'status' => 'success',
                'txn_id' => $status['payment_id'] ?? null,
                'bank_txn_id' => $rawBody['bankTxnId'] ?? null,
                'payment_mode' => $rawBody['paymentMode'] ?? null,
                'gateway_response' => json_encode(['callback' => $callbackData, 'status_api' => $status['raw'] ?? []]),
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

    protected function markPaymentPending(string $orderId, array $status)
    {
        DB::table('wallet_transactions')
            ->where('order_id', $orderId)
            ->update([
                'status' => 'pending',
                'gateway_response' => json_encode($status['raw'] ?? $status),
                'updated_at' => now(),
            ]);

        return redirect()->route('paytm.checkout')->with('warning', 'Payment is pending.');
    }

    protected function markPaymentFailed(string $orderId, array $status)
    {
        DB::table('wallet_transactions')
            ->where('order_id', $orderId)
            ->update([
                'status' => 'failed',
                'gateway_response' => json_encode($status['raw'] ?? $status),
                'updated_at' => now(),
            ]);

        return redirect()->route('paytm.checkout')->with('error', 'Payment failed.');
    }
}