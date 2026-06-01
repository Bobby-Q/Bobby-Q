<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $permissions = collect([
            ['key' => 'dashboard.view', 'module' => 'Dashboard', 'name' => 'View dashboard'],
            ['key' => 'borrowers.manage', 'module' => 'Borrowers', 'name' => 'Manage borrowers'],
            ['key' => 'loan-products.manage', 'module' => 'Loan Products', 'name' => 'Manage loan products'],
            ['key' => 'loans.initiate', 'module' => 'Loans', 'name' => 'Initiate loan applications'],
            ['key' => 'loans.approve', 'module' => 'Approvals', 'name' => 'Approve loan applications'],
            ['key' => 'collections.manage', 'module' => 'Collections', 'name' => 'Record repayments and collections'],
            ['key' => 'reports.view', 'module' => 'Reports', 'name' => 'View reports'],
            ['key' => 'users.manage', 'module' => 'Users', 'name' => 'Manage users and roles'],
            ['key' => 'settings.manage', 'module' => 'Settings', 'name' => 'Manage system settings'],
        ])->mapWithKeys(fn (array $permission): array => [
            $permission['key'] => Permission::updateOrCreate(
                ['key' => $permission['key']],
                $permission,
            ),
        ]);

        $adminRole = Role::updateOrCreate(
            ['name' => 'administrator'],
            [
                'display_name' => 'Administrator',
                'description' => 'Full system access for trusted business administrators.',
                'is_system' => true,
            ],
        );

        $adminRole->permissions()->sync($permissions->pluck('id')->all());

        $admin = User::updateOrCreate(
            ['email' => env('ADMIN_EMAIL', 'admin@loansuite.local')],
            [
                'name' => env('ADMIN_NAME', 'Loan Suite Admin'),
                'password' => env('ADMIN_PASSWORD', 'ChangeMe#2026'),
                'can_initiate' => true,
                'can_authorize' => true,
                'can_validate' => true,
                'is_locked' => false,
            ],
        );

        $admin->roles()->syncWithoutDetaching([$adminRole->id]);
    }
}
