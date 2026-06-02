<?php

namespace Tests\Feature;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoanProductManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_authorized_users_can_create_loan_products(): void
    {
        $this->withoutVite();
        $user = User::factory()->create();
        $this->grant($user, 'loan-products.manage');

        $response = $this->actingAs($user)->post('/loan-products', [
            'name' => 'Biashara 30 Day',
            'min_principal' => 1000,
            'max_principal' => 50000,
            'interest_rate' => 12.5,
            'repayment_period' => 1,
            'repayment_period_type' => 'month',
        ]);

        $response->assertRedirect(route('loan-products.index'));
        $this->assertDatabaseHas('loan_products', ['name' => 'Biashara 30 Day']);
        $this->assertDatabaseHas('audit_logs', ['event' => 'loan_product.created']);
    }

    public function test_loan_product_pages_require_permission(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get('/loan-products')->assertForbidden();
    }

    private function grant(User $user, string $permission): void
    {
        $role = Role::create(['name' => 'test-product-role', 'display_name' => 'Test role']);
        $perm = Permission::create(['key' => $permission, 'module' => 'Testing', 'name' => $permission]);
        $role->permissions()->attach($perm);
        $user->roles()->attach($role);
    }
}
