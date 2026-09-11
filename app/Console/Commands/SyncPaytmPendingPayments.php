<?php

namespace App\Console\Commands;

use App\Enums\PaymentStatus;
use App\Models\Payment;
use App\Services\PaymentGatewayManager;
use App\Services\PaymentService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SyncPaytmPendingPayments extends Command
{
    protected $signature = 'payments:sync-paytm-pending';

    protected $description = 'Reconcile pending Paytm payments against the gateway order status API.';

    public function handle(PaymentGatewayManager $manager, PaymentService $service): int
    {
        $payments = Payment::query()
            ->where('gateway', 'paytm')
            ->where('status', PaymentStatus::PENDING->value)
            ->where('created_at', '>=', now()->subHours(48))
            ->get();

        if ($payments->isEmpty()) {
            $this->info('No pending Paytm payments to reconcile.');
            return self::SUCCESS;
        }

        $checked = 0;
        $settled = 0;

        foreach ($payments as $payment) {
            try {
                $orderId = $payment->gateway_order_id ?: $payment->reference;
                $status = $manager->driver('paytm')->getPaymentStatus($orderId);

                if ($status['status'] === 'successful') {
                    $service->markSuccessful($payment, $status['payment_id'], $status['raw']);
                    $settled++;
                    $this->line("  [OK] {$payment->reference} settled as successful.");
                } elseif ($status['status'] === 'failed') {
                    $service->markFailed($payment, $status['raw']);
                    $settled++;
                    $this->line("  [OK] {$payment->reference} settled as failed.");
                } else {
                    $this->line("  [..] {$payment->reference} still pending ({$status['result_status']}).");
                }
            } catch (\Exception $e) {
                Log::warning('payments:sync-paytm-pending could not reconcile', [
                    'reference' => $payment->reference,
                    'error' => $e->getMessage(),
                ]);
                $this->error("  [!!] {$payment->reference}: {$e->getMessage()}");
            }

            $checked++;
        }

        $this->info("Reconciled {$checked} pending payment(s); settled {$settled}.");

        return self::SUCCESS;
    }
}