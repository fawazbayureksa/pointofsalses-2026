<?php

namespace Database\Seeders;

use App\Models\Outlet;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class TenantSeeder extends Seeder
{
    public function run(): void
    {
        // Create demo tenant
        $tenant = Tenant::firstOrCreate(
            ['slug' => 'demo'],
            [
                'name'          => 'Demo Store',
                'slug'          => 'demo',
                'email'         => 'demo@example.com',
                'business_type' => 'retail',
                'plan'          => 'professional',
                'status'        => 'active',
            ]
        );

        // Create default outlet
        $outlet = Outlet::firstOrCreate(
            ['tenant_id' => $tenant->id, 'code' => 'HO'],
            [
                'tenant_id' => $tenant->id,
                'name'      => 'Main Outlet',
                'code'      => 'HO',
                'phone'     => '+62 21 12345678',
                'address'   => 'Jl. Sudirman No. 1',
                'city'      => 'Jakarta',
                'is_active' => true,
            ]
        );

        // Super admin (no tenant_id)
        $superAdmin = User::firstOrCreate(
            ['email' => 'superadmin@example.com'],
            [
                'tenant_id' => null,
                'name'      => 'Super Admin',
                'password'  => Hash::make('password'),
                'is_active' => true,
            ]
        );
        $superAdmin->syncRoles(['super_admin']);

        // Tenant admin
        $admin = User::firstOrCreate(
            ['email' => 'admin@example.com', 'tenant_id' => $tenant->id],
            [
                'tenant_id' => $tenant->id,
                'name'      => 'Demo Admin',
                'password'  => Hash::make('password'),
                'is_active' => true,
            ]
        );
        $admin->syncRoles(['tenant_admin']);
        $admin->outlets()->syncWithoutDetaching([$outlet->id => ['is_default' => true]]);

        // Manager
        $manager = User::firstOrCreate(
            ['email' => 'manager@example.com', 'tenant_id' => $tenant->id],
            [
                'tenant_id' => $tenant->id,
                'name'      => 'Demo Manager',
                'password'  => Hash::make('password'),
                'is_active' => true,
            ]
        );
        $manager->syncRoles(['manager']);
        $manager->outlets()->syncWithoutDetaching([$outlet->id => ['is_default' => true]]);

        // Cashier
        $cashier = User::firstOrCreate(
            ['email' => 'cashier@example.com', 'tenant_id' => $tenant->id],
            [
                'tenant_id' => $tenant->id,
                'name'      => 'Demo Cashier',
                'password'  => Hash::make('password'),
                'is_active' => true,
            ]
        );
        $cashier->syncRoles(['cashier']);
        $cashier->outlets()->syncWithoutDetaching([$outlet->id => ['is_default' => true]]);

        $this->command->info("Demo tenant '{$tenant->name}' seeded successfully.");
        $this->command->info("Super admin: superadmin@example.com / password");
        $this->command->info("Tenant admin: admin@example.com / password");
    }
}
