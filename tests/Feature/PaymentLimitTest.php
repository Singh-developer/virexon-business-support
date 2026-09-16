<?php
namespace Tests\Feature;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\{User,Role,Business,VirtualCard};
class PaymentLimitTest extends TestCase {
    use RefreshDatabase;
    public function test_per_transaction_limit_is_enforced(): void {
        $agentRole = Role::create(['name'=>'Agent','slug'=>'agent']);
        $adminRole = Role::create(['name'=>'Admin','slug'=>'admin']);
        $agent = User::factory()->create(['role_id'=>$agentRole->id]);
        $admin = User::factory()->create(['role_id'=>$adminRole->id]);
        $b = Business::create(['name'=>'Demo','created_by'=>$agent->id]);
        $agent->update(['business_id' => $b->id]);
        $c = VirtualCard::create([
            'business_id'=>$b->id,
            'agent_id'=>$agent->id,
            'reference'=>'VC-1',
            'cardholder_name'=>'Demo',
            'status'=>'active',
            'card_limit'=>10000,
            'daily_limit'=>5000,
            'monthly_limit'=>8000,
            'per_transaction_limit'=>1000,
            'expiry_date'=>now()->addYear(),
            'current_usage'=>0
        ]);

        // Agents cannot create payments at all (route is admin/super-admin only).
        $this->actingAs($agent)->post(route('payments.store'), [
            'business_id'=>$b->id,
            'card_id'=>$c->id,
            'amount'=>1001,
            'gateway'=>'mock'
        ])->assertForbidden();

        // Anyone creating a payment above the card's per-transaction limit is rejected.
        $this->actingAs($admin)->post(route('payments.store'), [
            'business_id'=>$b->id,
            'card_id'=>$c->id,
            'amount'=>1001,
            'gateway'=>'mock'
        ])->assertSessionHasErrors('amount');
    }
}