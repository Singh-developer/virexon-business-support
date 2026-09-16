<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAgentPopupTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $agent;

    protected function setUp(): void
    {
        parent::setUp();

        $adminRole = Role::firstOrCreate(['slug' => 'admin', 'name' => 'Admin']);
        $agentRole = Role::firstOrCreate(['slug' => 'agent', 'name' => 'Agent']);

        $this->admin = User::factory()->create(['role_id' => $adminRole->id]);
        $this->agent = User::factory()->create(['role_id' => $agentRole->id, 'status' => 'active']);
    }

    // ------------------------------------------------------------------
    // Login status (explicit + toggle) -- used by the popup
    // ------------------------------------------------------------------

    public function test_toggle_status_sets_active_via_select(): void
    {
        $this->agent->update(['status' => 'inactive']);

        $this->actingAs($this->admin)
            ->patch(route('agents.toggle-status', $this->agent), ['status' => 'active'])
            ->assertRedirect();

        $this->assertDatabaseHas('users', [
            'id' => $this->agent->id,
            'status' => 'active',
        ]);
    }

    public function test_toggle_status_sets_inactive_via_select(): void
    {
        $this->actingAs($this->admin)
            ->patch(route('agents.toggle-status', $this->agent), ['status' => 'inactive'])
            ->assertRedirect();

        $this->assertDatabaseHas('users', [
            'id' => $this->agent->id,
            'status' => 'inactive',
        ]);
    }

    public function test_toggle_status_without_param_still_toggles(): void
    {
        $this->actingAs($this->admin)
            ->patch(route('agents.toggle-status', $this->agent))
            ->assertRedirect();

        $this->assertDatabaseHas('users', [
            'id' => $this->agent->id,
            'status' => 'inactive',
        ]);
    }

    public function test_toggle_status_invalid_value_rejected(): void
    {
        $this->actingAs($this->admin)
            ->patch(route('agents.toggle-status', $this->agent), ['status' => 'banned'])
            ->assertSessionHasErrors('status');

        $this->assertDatabaseHas('users', [
            'id' => $this->agent->id,
            'status' => 'active',
        ]);
    }

    // ------------------------------------------------------------------
    // Popup data embedded in the agents index page
    // ------------------------------------------------------------------

    public function test_agents_index_embeds_document_manifest(): void
    {
        $agentRole = Role::firstOrCreate(['slug' => 'agent', 'name' => 'Agent']);
        $agent = User::factory()->create(['role_id' => $agentRole->id]);
        $agent->documents()->create([
            'document_type' => 'pan_card',
            'file_path'     => 'AGT-1/pan_card.pdf',
            'original_name' => 'pan_card.pdf',
            'status'        => 'pending',
        ]);

        $response = $this->actingAs($this->admin)->get(route('agents.index'));

        $response->assertOk()
            ->assertSee('pan_card')
            ->assertSee('AGT-1\\/pan_card.pdf');
    }
}