<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the central database.
     *
     * Tenant-specific seeders (RolesAndPermissionsSeeder, TenantDefaultSettingsSeeder)
     * must be executed inside a tenant context via:
     *   tenancy()->initialize($tenant);
     *   (new RolesAndPermissionsSeeder)->run();
     */
    public function run(): void
    {
        // Nothing to seed in the central DB by default.
        // Add central super-admin seeding here if needed.
    }
}
