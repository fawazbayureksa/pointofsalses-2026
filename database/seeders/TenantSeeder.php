<?php

namespace Database\Seeders;

use App\Models\Outlet;
use App\Models\Tenant;
use Illuminate\Database\Seeder;

/**
 * Seeds a demo tenant with its entire stack:
 *   1. Tenant record in the central DB
 *   2. Tenant DB is auto-created via stancl/tenancy TenantCreated pipeline
 *   3. Switch to tenant context → run tenant-level seeders
 *
 * Run with:
 *   php artisan db:seed --class=TenantSeeder
 */
class TenantSeeder extends Seeder
{
    public function run(): void
    {
        // ── 1. Create the tenant in the central DB ────────────────────────────
        /** @var Tenant $tenant */
        $tenant = Tenant::firstOrCreate(
            ['slug' => 'demo'],
            [
                'name'          => 'Demo Store',
                'slug'          => 'demo',
                'business_type' => 'retail',
                'plan'          => 'professional',
                'status'        => 'active',
            ]
        );

        // Attach a domain (used for subdomain tenancy resolution)
        $tenant->createDomain(['domain' => 'demo.localhost']);

        // ── 2. Switch to tenant DB context ───────────────────────────────────
        tenancy()->initialize($tenant);

        // ── 3. Seed tenant-level data ─────────────────────────────────────────
        $this->call([
            RolesAndPermissionsSeeder::class,   // roles + permissions
            TenantDefaultSettingsSeeder::class, // currency, tax_rate, etc.
            UserSeeder::class,                  // default admin/manager/cashier
        ]);

        // Seed a default outlet
        Outlet::firstOrCreate(
            ['name' => 'Main Outlet'],
            [
                'tenant_id' => $tenant->id,
                'name'      => 'Main Outlet',
                'code'      => 'HO',
                'phone'     => '+62 21 12345678',
                'address'   => 'Jl. Sudirman No. 1',
                'city'      => 'Jakarta',
                'country'   => 'ID',
                'is_active' => true,
            ]
        );

        // ── 4. End tenancy context ────────────────────────────────────────────
        tenancy()->end();

        $this->command->info("Tenant '{$tenant->name}' seeded successfully.");
    }
}
