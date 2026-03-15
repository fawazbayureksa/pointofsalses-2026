<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class AdminSeeder extends Seeder
{
    public function run()
    {
        $this->createPermissions();
        $this->createRoles();
        $this->createAdminUser();
    }

    protected function createPermissions()
    {
        $permissions = [
            'view admin panel',
            'manage users',
            'view activity logs',
            'view tenants',
            'create tenants',
            'edit tenants',
            'delete tenants',
            'view outlets',
            'create outlets',
            'edit outlets',
            'delete outlets',
            'view products',
            'create products',
            'edit products',
            'delete products',
            'view categories',
            'create categories',
            'edit categories',
            'delete categories',
            'view customers',
            'create customers',
            'edit customers',
            'delete customers',
            'view orders',
            'create orders',
            'edit orders',
            'delete orders',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'web',
            ]);
        }

        $this->command->info('✓ Permissions created successfully');
    }

    protected function createRoles()
    {
        $superAdmin = Role::firstOrCreate([
            'name' => 'super_admin',
            'guard_name' => 'web',
        ]);

        $tenantAdmin = Role::firstOrCreate([
            'name' => 'tenant_admin',
            'guard_name' => 'web',
        ]);

        $staff = Role::firstOrCreate([
            'name' => 'staff',
            'guard_name' => 'web',
        ]);

        $superAdmin->givePermissionTo(Permission::all());
        $tenantAdmin->givePermissionTo([
            'view admin panel',
            'view outlets',
            'create outlets',
            'edit outlets',
            'view products',
            'create products',
            'edit products',
            'delete products',
            'view categories',
            'create categories',
            'edit categories',
            'delete categories',
            'view customers',
            'create customers',
            'edit customers',
            'delete customers',
            'view orders',
            'create orders',
            'edit orders',
            'delete orders',
        ]);

        $this->command->info('✓ Roles created successfully');
    }

    protected function createAdminUser()
    {
        $admin = User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Super Admin',
                'email' => 'admin@example.com',
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
            ]
        );

        $admin->assignRole('super_admin');

        $this->command->info('✓ Admin user created successfully');
        $this->command->info('  Email: admin@example.com');
        $this->command->info('  Password: password');
    }
}
