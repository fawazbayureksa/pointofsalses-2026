<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Permissions grouped by domain.
     * All roles and permissions are seeded per-tenant (tenant DB context).
     */
    private array $permissions = [
        // User management
        'manage_users',

        // Product / Inventory
        'manage_products',
        'view_products',

        // Orders
        'manage_orders',
        'view_orders',

        // Reports
        'view_reports',

        // Settings
        'manage_settings',

        // Finance
        'view_payments',
        'manage_payments',

        // Outlet
        'manage_outlets',
    ];

    private array $roles = [
        'super_admin'  => [],  // assigned all permissions programmatically
        'tenant_admin' => [
            'manage_users',
            'manage_products',
            'view_products',
            'manage_orders',
            'view_orders',
            'view_reports',
            'manage_settings',
            'view_payments',
            'manage_payments',
            'manage_outlets',
        ],
        'manager' => [
            'view_products',
            'manage_products',
            'manage_orders',
            'view_orders',
            'view_reports',
            'view_payments',
        ],
        'cashier' => [
            'view_products',
            'manage_orders',
        ],
    ];

    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create all permissions
        foreach ($this->permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        // Create roles and assign permissions
        foreach ($this->roles as $roleName => $permissionNames) {
            $role = Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);

            if ($roleName === 'super_admin') {
                $role->syncPermissions(Permission::all());
            } else {
                $role->syncPermissions($permissionNames);
            }
        }

        $this->command->info('Roles and permissions seeded successfully.');
    }
}
