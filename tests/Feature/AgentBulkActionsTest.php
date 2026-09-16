<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use App\Models\UserDetail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AgentBulkActionsTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private Role $agentRole;

    protected function setUp(): void
    {
        parent::setUp();

        $adminRole = Role::create(['name' => 'Super Admin', 'slug' => 'super-admin']);
        $this->admin = User::factory()->create(['role_id' => $adminRole->id]);

        $this->agentRole = Role::create(['name' => 'Agent', 'slug' => 'agent']);
    }

    private function makeAgent(int $status = 1): User
    {
        return User::factory()->create([
            'role_id' => $this->agentRole->id,
            'status'  => $status === 1 ? 'active' : 'inactive',
        ]);
    }

    private function bulk(string $action, array $ids)
    {
        return $this->actingAs($this->admin)
            ->post(route('agents.bulk'), [
                'action' => $action,
                'ids'    => implode(',', $ids),
            ]);
    }

    public function test_bulk_trash_soft_deletes_selected_agents(): void
    {
        $a = $this->makeAgent();
        $b = $this->makeAgent();

        $this->bulk('trash', [$a->id, $b->id])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertSoftDeleted('users', ['id' => $a->id]);
        $this->assertSoftDeleted('users', ['id' => $b->id]);
    }

    public function test_bulk_activate_and_deactivate_toggle_login_status(): void
    {
        $a = $this->makeAgent(0);
        $b = $this->makeAgent(1);

        $this->bulk('activate', [$a->id])
            ->assertSessionHas('success');
        $this->assertSame('active', $a->fresh()->status);

        $this->bulk('deactivate', [$b->id])
            ->assertSessionHas('success');
        $this->assertSame('inactive', $b->fresh()->status);
    }

    public function test_bulk_only_trashes_agents_not_other_roles(): void
    {
        $admin = User::factory()->create([
            'role_id' => Role::where('slug', 'super-admin')->first()->id,
        ]);
        $agent = $this->makeAgent();

        $this->bulk('trash', [$admin->id, $agent->id])->assertSessionHas('success');

        $this->assertDatabaseHas('users', ['id' => $admin->id, 'deleted_at' => null]);
        $this->assertSoftDeleted('users', ['id' => $agent->id]);
    }

    public function test_bulk_with_only_non_agents_reports_error(): void
    {
        $admin = User::factory()->create([
            'role_id' => Role::where('slug', 'super-admin')->first()->id,
        ]);

        $this->bulk('trash', [$admin->id])->assertSessionHas('error');
    }

    public function test_bulk_requires_valid_action(): void
    {
        $a = $this->makeAgent();

        $this->bulk('explode', [$a->id])
            ->assertSessionHasErrors('action');
    }
}