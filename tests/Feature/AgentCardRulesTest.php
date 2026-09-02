<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\Role;
use App\Models\User;
use App\Models\VirtualCard;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AgentCardRulesTest extends TestCase
{
    use RefreshDatabase;

    public function test_new_agent_has_no_virtual_card(): void
    {
        $adminRole = Role::create([
            'name' => 'Admin',
            'slug' => 'admin',
        ]);

        Role::create([
            'name' => 'Agent',
            'slug' => 'agent',
        ]);

        $admin = User::factory()->create([
            'role_id' => $adminRole->id,
        ]);

        $business = Business::create([
            'name' => 'Demo',
            'created_by' => $admin->id,
        ]);

        $this->actingAs($admin)
            ->post(route('agents.store'), [
                'name' => 'New Agent',
                'email' => 'new-agent@example.test',
                'phone' => '+91 90000 11111',
                'business_id' => $business->id,
                'status' => 'active',
                'password' => 'Password123!',
                'password_confirmation' => 'Password123!',
            ])
            ->assertRedirect(route('agents.index'));

        $agent = User::where(
            'email',
            'new-agent@example.test'
        )->firstOrFail();

        $this->assertNull(
            $agent->virtualCard
        );

        $this->assertSame(
            0,
            VirtualCard::where(
                'agent_id',
                $agent->id
            )->count()
        );
    }

    public function test_admin_can_create_card_for_agent(): void
    {
        $adminRole = Role::create([
            'name' => 'Admin',
            'slug' => 'admin',
        ]);

        $agentRole = Role::create([
            'name' => 'Agent',
            'slug' => 'agent',
        ]);

        $admin = User::factory()->create([
            'role_id' => $adminRole->id,
        ]);

        $business = Business::create([
            'name' => 'Demo',
            'created_by' => $admin->id,
        ]);

        $agent = User::factory()->create([
            'role_id' => $agentRole->id,
            'business_id' => $business->id,
            'status' => 'active',
        ]);

        $this->actingAs($admin)
            ->post(route('cards.store'), [
                'business_id' => $business->id,
                'agent_id' => $agent->id,
                'cardholder_name' => $agent->name,
                'card_limit' => 100000,
                'daily_limit' => 20000,
                'monthly_limit' => 80000,
                'per_transaction_limit' => 20000,
                'status' => 'active',
            ])
            ->assertRedirect();

        $card = VirtualCard::where(
            'agent_id',
            $agent->id
        )->firstOrFail();

        $this->assertNotNull(
            $card->encrypted_pan
        );

        $this->assertSame(
            4,
            strlen($card->last4)
        );

        $this->assertNotNull(
            $card->provider_card_id
        );
    }

    public function test_agent_has_only_one_card(): void
    {
        $role = Role::create([
            'name' => 'Agent',
            'slug' => 'agent',
        ]);

        $agent = User::factory()->create([
            'role_id' => $role->id,
        ]);

        $business = Business::create([
            'name' => 'Demo',
            'created_by' => $agent->id,
        ]);

        VirtualCard::create([
            'business_id' => $business->id,
            'agent_id' => $agent->id,
            'reference' => 'VC-1',
            'encrypted_pan' => encrypt(
                '4000001234567890'
            ),
            'last4' => '7890',
            'provider_card_id' => 'mock_1',
            'cardholder_name' => 'Agent',
            'status' => 'active',
            'card_limit' => 10000,
            'daily_limit' => 5000,
            'monthly_limit' => 8000,
            'per_transaction_limit' => 1000,
            'current_usage' => 0,
            'expiry_date' => now()->addYear(),
        ]);

        $this->expectException(
            UniqueConstraintViolationException::class
        );

        VirtualCard::create([
            'business_id' => $business->id,
            'agent_id' => $agent->id,
            'reference' => 'VC-2',
            'encrypted_pan' => encrypt(
                '4000009876543210'
            ),
            'last4' => '3210',
            'provider_card_id' => 'mock_2',
            'cardholder_name' => 'Agent',
            'status' => 'active',
            'card_limit' => 10000,
            'daily_limit' => 5000,
            'monthly_limit' => 8000,
            'per_transaction_limit' => 1000,
            'current_usage' => 0,
            'expiry_date' => now()->addYear(),
        ]);
    }

    public function test_agent_cannot_open_another_agents_card(): void
    {
        $role = Role::create([
            'name' => 'Agent',
            'slug' => 'agent',
        ]);

        $business = Business::create([
            'name' => 'Demo',
        ]);

        $agent1 = User::factory()->create([
            'role_id' => $role->id,
            'business_id' => $business->id,
        ]);

        $agent2 = User::factory()->create([
            'role_id' => $role->id,
            'business_id' => $business->id,
        ]);

        $card = VirtualCard::create([
            'business_id' => $business->id,
            'agent_id' => $agent2->id,
            'reference' => 'VC-2',
            'encrypted_pan' => encrypt(
                '4000001234567890'
            ),
            'last4' => '7890',
            'provider_card_id' => 'mock_2',
            'cardholder_name' => $agent2->name,
            'status' => 'active',
            'card_limit' => 10000,
            'daily_limit' => 5000,
            'monthly_limit' => 8000,
            'per_transaction_limit' => 1000,
            'current_usage' => 0,
            'expiry_date' => now()->addYear(),
        ]);

        $this->actingAs($agent1)
            ->get(route('cards.show', $card))
            ->assertForbidden();
    }

    public function test_agent_is_redirected_directly_to_own_card(): void
    {
        $role = Role::create([
            'name' => 'Agent',
            'slug' => 'agent',
        ]);

        $business = Business::create([
            'name' => 'Demo',
        ]);

        $agent = User::factory()->create([
            'role_id' => $role->id,
            'business_id' => $business->id,
        ]);

        $card = VirtualCard::create([
            'business_id' => $business->id,
            'agent_id' => $agent->id,
            'reference' => 'VC-1',
            'encrypted_pan' => encrypt(
                '4000001234567890'
            ),
            'last4' => '7890',
            'provider_card_id' => 'mock_1',
            'cardholder_name' => $agent->name,
            'status' => 'active',
            'card_limit' => 10000,
            'daily_limit' => 5000,
            'monthly_limit' => 8000,
            'per_transaction_limit' => 1000,
            'current_usage' => 0,
            'expiry_date' => now()->addYear(),
        ]);

        $this->actingAs($agent)
            ->get(route('cards.index'))
            ->assertRedirect(
                route('cards.show', $card)
            );
    }

    public function test_agent_can_reveal_only_own_pan(): void
    {
        $role = Role::create([
            'name' => 'Agent',
            'slug' => 'agent',
        ]);

        $business = Business::create([
            'name' => 'Demo',
        ]);

        $agent = User::factory()->create([
            'role_id' => $role->id,
            'business_id' => $business->id,
        ]);

        $card = VirtualCard::create([
            'business_id' => $business->id,
            'agent_id' => $agent->id,
            'reference' => 'VC-1',
            'encrypted_pan' => encrypt(
                '4000001234567890'
            ),
            'last4' => '7890',
            'provider_card_id' => 'mock_1',
            'cardholder_name' => $agent->name,
            'status' => 'active',
            'card_limit' => 10000,
            'daily_limit' => 5000,
            'monthly_limit' => 8000,
            'per_transaction_limit' => 1000,
            'current_usage' => 0,
            'expiry_date' => now()->addYear(),
        ]);

        $this->actingAs($agent)
            ->post(route('cards.reveal-pan', $card))
            ->assertOk()
            ->assertJson([
                'pan' => '4000001234567890',
                'formatted' => '4000 0012 3456 7890',
            ]);
    }
}