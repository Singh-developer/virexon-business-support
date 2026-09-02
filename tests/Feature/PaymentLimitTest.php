<?php
namespace Tests\Feature;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\{User,Role,Business,VirtualCard};
class PaymentLimitTest extends TestCase {
    use RefreshDatabase;
    public function test_agent_cannot_exceed_card_per_transaction_limit(): void {
        $role = Role::create(['name'=>'Agent','slug'=>'agent']);
        $u = User::factory()->create(['role_id'=>$role->id]);
        $b = Business::create(['name'=>'Demo','created_by'=>$u->id]);
        $u->update(['business_id' => $b->id]);
        $c = VirtualCard::create([
            'business_id'=>$b->id,
            'agent_id'=>$u->id,
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
        $this->actingAs($u)->post(route('payments.store'),[
            'business_id'=>$b->id,
            'card_id'=>$c->id,
            'amount'=>1001,
            'gateway'=>'mock'
        ])->assertSessionHasErrors('amount');
    }
}
