<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application.
     *
     * Run all (central + demo tenant): php artisan db:seed
     * Specific tenant:                php artisan tenants:seed --tenants=<id>
     */
    public function run(): void
    {
        if (tenancy()->initialized) {
            // Called via `php artisan tenants:seed` — tenant DB is already active.
            $this->call([
                RolesAndPermissionsSeeder::class,
                TenantDefaultSettingsSeeder::class,
                UserSeeder::class,
            ]);
        } else {
            // Called via `php artisan db:seed` — seed the demo tenant from scratch.
            $this->call(TenantSeeder::class);
        }
    }
}
