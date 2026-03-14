<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Seeds default users inside a tenant database context.
 * Must be called after RolesAndPermissionsSeeder.
 *
 * Usage from TenantSeeder:
 *   tenancy()->initialize($tenant);
 *   (new RolesAndPermissionsSeeder)->run();
 *   (new UserSeeder)->run();
 */
class UserSeeder extends Seeder
{
    public function run(): void
    {
        $tenantId = tenant('id');

        // Tenant Admin
        $admin = User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'tenant_id' => $tenantId,
                'name'      => 'Admin',
                'password'  => bcrypt('password'),
                'is_active' => true,
            ]
        );
        $admin->assignRole('tenant_admin');

        // Manager
        $manager = User::firstOrCreate(
            ['email' => 'manager@example.com'],
            [
                'tenant_id' => $tenantId,
                'name'      => 'Manager',
                'password'  => bcrypt('password'),
                'is_active' => true,
            ]
        );
        $manager->assignRole('manager');

        // Cashier
        $cashier = User::firstOrCreate(
            ['email' => 'cashier@example.com'],
            [
                'tenant_id' => $tenantId,
                'name'      => 'Cashier',
                'password'  => bcrypt('password'),
                'is_active' => true,
            ]
        );
        $cashier->assignRole('cashier');

        $this->command->info("Users seeded for tenant: {$tenantId}");
    }
}
