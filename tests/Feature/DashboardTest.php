<?php

namespace Tests\Feature;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_login(): void
    {
        $response = $this->get('/');

        $response->assertRedirect('/login');
    }

    public function test_dashboard_loads_for_authenticated_users_with_security_headers(): void
    {
        $this->withoutVite();

        $user = User::factory()->create();
        $this->grant($user, 'dashboard.view');

        $response = $this->actingAs($user)->get('/');

        $response->assertOk();
        $response->assertSee('Loan Suite');
        $response->assertHeader('X-Frame-Options', 'SAMEORIGIN');
        $response->assertHeader('X-Content-Type-Options', 'nosniff');
    }

    private function grant(User $user, string $permission): void
    {
        $role = Role::create([
            'name' => 'test-'.str_replace('.', '-', $permission),
            'display_name' => 'Test role',
        ]);
        $perm = Permission::create([
            'key' => $permission,
            'module' => 'Testing',
            'name' => $permission,
        ]);
        $role->permissions()->attach($perm);
        $user->roles()->attach($role);
    }
}
