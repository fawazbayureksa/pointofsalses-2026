<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * Kept for backward compatibility — actual seeding is in TenantSeeder.
 * Run `php artisan db:seed` which calls DatabaseSeeder → TenantSeeder.
 */
class UserSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(TenantSeeder::class);
    }
}
