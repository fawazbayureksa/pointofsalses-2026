<?php

namespace Database\Seeders;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Seeds default users inside a tenant database context.
 * Must be called after RolesAndPermissionsSeeder.
 *
 * Usage from TenantSeeder (recommended):
 *   tenancy()->initialize($tenant);
 *   $this->call([RolesAndPermissionsSeeder::class, UserSeeder::class]);
 *
 * Or standalone (initializes the first/only tenant automatically):
 *   php artisan db:seed --class=UserSeeder
 */
class UserSeeder extends Seeder
{
    public function run(): void
    {
        $endAfter = false;

        // If called outside a tenant context (e.g. php artisan db:seed --class=UserSeeder),
        // auto-initialize the first available tenant.
        if (!tenancy()->initialized) {
            $tenant = Tenant::latest()->first();
            if (! $tenant) {
                $this->command->error('No tenants found. Run TenantSeeder first.');
                return;
            }

            tenancy()->initialize($tenant);
            $endAfter = true;
        }

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

        if ($endAfter) {
            tenancy()->end();
        }
    }
}
