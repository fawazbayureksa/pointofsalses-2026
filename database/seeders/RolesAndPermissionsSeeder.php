<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolesAndPermissionsSeeder extends Seeder
{
    private array $permissions = [
        // Outlets
        'create outlets',
        'edit outlets',
        'delete outlets',
        'manage outlets',
        // Products
        'create products',
        'edit products',
        'delete products',
        'manage products',
        'view products',
        // Categories
        'create categories',
        'edit categories',
        'delete categories',
        // Customers
        'create customers',
        'edit customers',
        'delete customers',
        // Orders
        'create orders',
        'edit orders',
        'delete orders',
        'manage orders',
        'view orders',
        // Payments
        'view payments',
        'manage payments',
        // Users
        'create users',
        'edit users',
        'delete users',
        'manage users',
        // Supervisor authorizations
        'authorize refund',
        'authorize void',
        // Reports
        'view reports',
        'view cashier reports',
        // Other
        'manage settings',
        'manage tenants',
        'view activity logs',
    ];

    private array $roles = [
        'super_admin'  => [],  // gets all permissions via Gate::before
        'tenant_admin' => [
            // Outlets
            'create outlets',
            'edit outlets',
            'delete outlets',
            'manage outlets',
            // Products
            'create products',
            'edit products',
            'delete products',
            'manage products',
            'view products',
            // Categories
            'create categories',
            'edit categories',
            'delete categories',
            // Customers
            'create customers',
            'edit customers',
            'delete customers',
            // Orders
            'create orders',
            'edit orders',
            'delete orders',
            'manage orders',
            'view orders',
            // Payments
            'view payments',
            'manage payments',
            // Users
            'create users',
            'edit users',
            'delete users',
            'manage users',
            // Supervisor authorizations
            'authorize refund',
            'authorize void',
            // Reports
            'view reports',
            'view cashier reports',
            // Other
            'manage settings',
            'view activity logs',
        ],
        'manager' => [
            'create products',
            'edit products',
            'view products',
            'manage products',
            'create categories',
            'edit categories',
            'create customers',
            'edit customers',
            'create orders',
            'edit orders',
            'manage orders',
            'view orders',
            'view payments',
            'authorize refund',
            'authorize void',
            'view reports',
            'view cashier reports',
            'view activity logs',
        ],
        'cashier' => [
            'view products',
            'create orders',
            'view orders',
            'view payments',
            'create customers',
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
