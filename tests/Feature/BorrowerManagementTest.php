<?php

namespace Tests\Feature;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BorrowerManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_authorized_users_can_create_and_search_borrowers(): void
    {
        $this->withoutVite();
        $user = User::factory()->create();
        $this->grant($user, 'borrowers.manage');

        $response = $this->actingAs($user)->post('/borrowers', [
            'first_name' => 'Mary',
            'other_name' => 'Wanjiku',
            'phone_number' => '0711111111',
            'national_id_number' => '12345678',
            'email' => 'mary@example.com',
        ]);

        $response->assertRedirect(route('borrowers.index'));
        $this->assertDatabaseHas('borrowers', ['first_name' => 'Mary', 'phone_number' => '0711111111']);
        $this->assertDatabaseHas('audit_logs', ['event' => 'borrower.created']);

        $this->actingAs($user)->get('/borrowers?search=Wanjiku')->assertOk()->assertSee('Mary');
    }

    public function test_borrower_pages_require_permission(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get('/borrowers')->assertForbidden();
    }

    private function grant(User $user, string $permission): void
    {
        $role = Role::create(['name' => 'test-borrower-role', 'display_name' => 'Test role']);
        $perm = Permission::create(['key' => $permission, 'module' => 'Testing', 'name' => $permission]);
        $role->permissions()->attach($perm);
        $user->roles()->attach($role);
    }
}
