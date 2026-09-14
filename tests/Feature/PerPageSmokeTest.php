<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\{Role, User};
use Illuminate\Foundation\Testing\RefreshDatabase;

class PerPageSmokeTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $agent;

    protected function setUp(): void
    {
        parent::setUp();
        $agentRole = Role::create(['name' => 'Agent', 'slug' => 'agent']);
        $adminRole = Role::create(['name' => 'Admin', 'slug' => 'admin']);
        $this->agent = User::factory()->create(['role_id' => $agentRole->id]);
        $this->admin = User::factory()->create(['role_id' => $adminRole->id]);
    }

    public function test_server_tables_default_to_20_and_accept_40_100(): void
    {
        foreach (['businesses.index', 'transactions.index', 'payments.index', 'cards.index', 'sanctions.index'] as $route) {
            // Default
            $res = $this->actingAs($this->admin)->get(route($route));
            $res->assertOk();
            $res->assertSee('name="per_page"', false);
            $res->assertSee('value="20" selected', false);

            // Allowed value sticks
            $res = $this->actingAs($this->admin)->get(route($route, ['per_page' => 40]));
            $res->assertOk();
            $res->assertSee('value="40" selected', false);

            // Invalid value falls back to default
            $res = $this->actingAs($this->admin)->get(route($route, ['per_page' => 999]));
            $res->assertOk();
            $res->assertSee('value="20" selected', false);
        }
    }

    public function test_agent_sanction_cards_and_admin_users_dropdown(): void
    {
        $this->actingAs($this->agent)->get(route('agent.sanctions.index'))
            ->assertOk()->assertSee('name="per_page"', false)
            ->assertSee('Important Note')
            ->assertSee('48 hours');

        $this->actingAs($this->admin)->get(route('admin.users.index'))
            ->assertOk()->assertSee('name="per_page"', false);
    }

    public function test_agents_datatable_renders_entries_dropdown(): void
    {
        // The length menu ("Show X entries") only renders when `dom` includes `l`.
        $this->actingAs($this->admin)->get(route('agents.index'))
            ->assertOk()
            ->assertSee("dom: 'Blfrtip'", false)
            ->assertSee('lengthMenu', false);
    }

    public function test_plain_tables_render_with_entries_control(): void
    {
        $this->actingAs($this->agent)->get(route('tickets.index'))->assertOk()
            ->assertSee('agentTicketsTable');
        $this->actingAs($this->agent)->get(route('advances.index'))->assertOk()
            ->assertSee('commissionTable');
        $this->actingAs($this->admin)->get(route('admin.documents.overview'))->assertOk()
            ->assertSee('lengthMenu', false);
    }
}
