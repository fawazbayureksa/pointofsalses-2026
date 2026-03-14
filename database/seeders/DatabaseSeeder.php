<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application – central DB first, then demo tenant.
     *
     * Run all:          php artisan db:seed
     * Tenant only:      php artisan db:seed --class=TenantSeeder
     * Permissions only: php artisan db:seed --class=RolesAndPermissionsSeeder
     */
    public function run(): void
    {
        // Seeds a demo tenant including its DB, roles, settings, users, and outlet.
        // Remove or comment this line in production; provision tenants via the admin panel.
        $this->call(TenantSeeder::class);
    }
}
