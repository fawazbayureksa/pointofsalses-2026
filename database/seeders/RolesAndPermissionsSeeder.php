<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolesAndPermissionsSeeder extends Seeder
{
    private array $permissions = [
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
        'manage_tenants',
    ];

    private array $roles = [
        'super_admin'  => [],  // gets all permissions via Gate::before
        'tenant_admin' => [
            'manage_users',
            'manage_products', 'view_products',
            'manage_orders', 'view_orders',
            'view_reports',
            'manage_settings',
            'view_payments', 'manage_payments',
            'manage_outlets',
        ],
        'manager' => [
            'view_products', 'manage_products',
            'manage_orders', 'view_orders',
            'view_reports',
            'view_payments',
        ],
        'cashier' => [
            'view_products',
            'manage_orders', 'view_orders',
            'view_payments',
        ],
    ];

    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        foreach ($this->permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        foreach ($this->roles as $roleName => $rolePermissions) {
            $role = Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);

            if (! empty($rolePermissions)) {
                $role->syncPermissions($rolePermissions);
            }
        }

        $this->command->info('Roles and permissions seeded.');
    }
}
