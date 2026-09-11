<?php

namespace Tests\Feature;

use App\Enums\PaymentStatus;
use App\Models\{Business, Payment, Role, Transaction, User, VirtualCard};
use App\Services\PaymentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentServiceRepaymentTest extends TestCase
{
    use RefreshDatabase;

    private function makeRepayment(float $usage, float $amount): array
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
            'amount' => $amount,
            'currency' => 'INR',
            'reference' => 'PAY-' . uniqid(),
            'payment_type' => 'repayment',
            'status' => PaymentStatus::PENDING,
        ]);

        return [$card, $payment];
    }

    public function test_mark_successful_repayment_decrements_usage(): void
    {
        [$card, $payment] = $this->makeRepayment(500, 200);

        app(PaymentService::class)->markSuccessful($payment, 'PAYTM_TXN_1', []);

        $this->assertSame(PaymentStatus::SUCCESSFUL, $payment->fresh()->status);
        $this->assertEquals(300, (float) $card->fresh()->current_usage);
        $this->assertDatabaseHas('transactions', ['payment_id' => $payment->id, 'status' => 'successful']);
    }

    public function test_mark_successful_repayment_clamps_usage_at_zero(): void
    {
        [$card, $payment] = $this->makeRepayment(500, 700);

        app(PaymentService::class)->markSuccessful($payment, 'PAYTM_TXN_2', []);

        $this->assertSame(0.0, (float) $card->fresh()->current_usage);
    }

    public function test_mark_successful_is_idempotent(): void
    {
        [$card, $payment] = $this->makeRepayment(500, 200);

        $service = app(PaymentService::class);
        $service->markSuccessful($payment, 'PAYTM_TXN_3', []);
        $service->markSuccessful($payment->fresh(), 'PAYTM_TXN_3', []);

        $this->assertEquals(300, (float) $card->fresh()->current_usage);
        $this->assertSame(1, Transaction::where('payment_id', $payment->id)->count());
    }

    public function test_mark_failed_does_not_downgrade_successful_payment(): void
    {
        [$card, $payment] = $this->makeRepayment(500, 200);

        $service = app(PaymentService::class);
        $service->markSuccessful($payment, 'PAYTM_TXN_4', []);
        $service->markFailed($payment->fresh(), []);

        $this->assertSame(PaymentStatus::SUCCESSFUL, $payment->fresh()->status);
        $this->assertEquals(300, (float) $card->fresh()->current_usage);
    }

    public function test_mark_pending_does_not_downgrade_successful_payment(): void
    {
        [$card, $payment] = $this->makeRepayment(500, 200);

        $service = app(PaymentService::class);
        $service->markSuccessful($payment, 'PAYTM_TXN_5', []);
        $service->markPending($payment->fresh(), []);

        $this->assertSame(PaymentStatus::SUCCESSFUL, $payment->fresh()->status);
    }

    public function test_create_rejects_repayment_over_outstanding_balance(): void
    {
        $this->withoutVite();

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
            'current_usage' => 500,
            'expiry_date' => now()->addYear(),
        ]);

        $this->expectException(\Illuminate\Validation\ValidationException::class);

        app(PaymentService::class)->create(
            $agent->id,
            $business->id,
            $card->id,
            600,
            'mock',
            'repayment'
        );
    }
}