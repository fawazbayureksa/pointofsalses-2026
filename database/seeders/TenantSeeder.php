<?php

namespace Database\Seeders;

use App\Models\Outlet;
use App\Models\Tenant;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

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
        // Always create fresh so TenantCreated fires → CreateDatabase + MigrateDatabase run.
        // If a stale record exists (e.g. from a prior run without migrate:fresh), delete it first.
        Tenant::where('slug', 'demo')->each(fn($t) => $t->delete());

        // migrate:fresh only drops central tables; tenant MySQL databases persist on the server.
        // Drop it explicitly so CreateDatabase doesn't throw TenantDatabaseAlreadyExistsException.
        $dbName = config('tenancy.database.prefix', 'tenant') . 'demo' . config('tenancy.database.suffix', '');
        DB::statement("DROP DATABASE IF EXISTS `{$dbName}`");

        /** @var Tenant $tenant */
        $tenant = Tenant::create([
            'id'            => 'demo',
            'name'          => 'Demo Store',
            'slug'          => 'demo',
            'business_type' => 'retail',
            'plan'          => 'professional',
            'status'        => 'active',
        ]);

        // Attach a domain only if it doesn't already exist.
        if (! $tenant->domains()->where('domain', 'demo.localhost')->exists()) {
            $tenant->createDomain(['domain' => 'demo.localhost']);
        }

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
