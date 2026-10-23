<?php

namespace Tests\Feature;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class AdminUserSubscriptionTest extends TestCase
{
    use RefreshDatabase;

    protected User $superAdmin;

    protected function setUp(): void
    {
        parent::setUp();

        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        foreach (['manage users', 'create users', 'edit users', 'delete users'] as $perm) {
            Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
        }

        $superAdminRole = Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);
        $superAdminRole->syncPermissions(Permission::all());

        $tenantAdminRole = Role::firstOrCreate(['name' => 'tenant_admin', 'guard_name' => 'web']);
        $tenantAdminRole->syncPermissions(Permission::all());

        $this->superAdmin = User::create([
            'tenant_id' => null,
            'name' => 'Super Admin',
            'email' => 'super@admin.com',
            'password' => bcrypt('password123'),
            'is_active' => true,
        ]);
        $this->superAdmin->assignRole($superAdminRole);
    }

    public function test_registered_trial_user_shows_trial_and_expired_date_in_admin_users_list(): void
    {
        $response = $this->post('/register', [
            'name' => 'Budi Santoso',
            'business_name' => 'Kopi Nusantara',
            'email' => 'budi@kopi.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertRedirect('/admin/dashboard');

        $trialUser = User::where('email', 'budi@kopi.com')->firstOrFail();
        $this->assertNotNull($trialUser->tenant);
        $this->assertEquals('trial', $trialUser->tenant->subscription_status);
        $this->assertNotNull($trialUser->tenant->trial_ends_at);

        $indexResponse = $this->actingAs($this->superAdmin)->get(route('admin.users.index'));

        $indexResponse->assertStatus(200);
        $indexResponse->assertSee('Budi Santoso');
        $indexResponse->assertSee('budi@kopi.com');
        $indexResponse->assertSee('Kopi Nusantara');
        $indexResponse->assertSee('Subscribed: Free Trial');
        $indexResponse->assertSee($trialUser->tenant->trial_ends_at->format('d M Y, H:i'));
        $indexResponse->assertSee('Set / Edit Date');
    }

    public function test_admin_can_edit_user_and_set_trial_or_subscription_expired_date_by_self(): void
    {
        $tenant = Tenant::create([
            'name' => 'Warung Kita',
            'slug' => 'warung-kita',
            'email' => 'owner@warung.com',
            'plan' => 'professional',
            'status' => 'active',
            'subscription_status' => 'trial',
            'trial_ends_at' => now()->addDays(14),
        ]);

        $user = User::create([
            'tenant_id' => $tenant->id,
            'name' => 'Siti Aminah',
            'email' => 'owner@warung.com',
            'phone' => '08123456789',
            'password' => bcrypt('password123'),
            'is_active' => true,
        ]);
        $user->assignRole('tenant_admin');

        $customExpiredDate = now()->addDays(30)->format('Y-m-d\TH:i');

        $updateResponse = $this->actingAs($this->superAdmin)->put(route('admin.users.update', $user->id), [
            'name' => 'Siti Aminah Updated',
            'email' => 'owner@warung.com',
            'phone' => '08999999999',
            'is_active' => 1,
            'role' => 'tenant_admin',
            'business_name' => 'Warung Kita Jaya',
            'plan' => 'enterprise',
            'subscription_status' => 'trial',
            'trial_ends_at' => $customExpiredDate,
            'subscription_ends_at' => '',
            'is_subscription_exempt' => 0,
        ]);

        $updateResponse->assertRedirect(route('admin.users.index'));

        $user->refresh();
        $tenant->refresh();

        $this->assertEquals('Siti Aminah Updated', $user->name);
        $this->assertEquals('08999999999', $user->phone);
        $this->assertEquals('Warung Kita Jaya', $tenant->name);
        $this->assertEquals('enterprise', $tenant->plan);
        $this->assertEquals('trial', $tenant->subscription_status);
        $this->assertEquals($customExpiredDate, $tenant->trial_ends_at->format('Y-m-d\TH:i'));

        // Now set expired date in the past and verify trial is expired
        $pastDate = now()->subDays(2)->format('Y-m-d\TH:i');

        $this->actingAs($this->superAdmin)->put(route('admin.users.update', $user->id), [
            'name' => 'Siti Aminah Updated',
            'email' => 'owner@warung.com',
            'is_active' => 1,
            'subscription_status' => 'trial',
            'trial_ends_at' => $pastDate,
            'is_subscription_exempt' => 0,
        ])->assertRedirect(route('admin.users.index'));

        $tenant->refresh();
        $this->assertFalse($tenant->isOnTrial());
        $this->assertFalse($tenant->hasActiveAccess());
    }

    public function test_admin_can_create_user_with_custom_trial_expired_date_and_filter_list(): void
    {
        $customTrialEnd = now()->addDays(21)->format('Y-m-d\TH:i');

        $storeResponse = $this->actingAs($this->superAdmin)->post(route('admin.users.store'), [
            'name' => 'Andi Wijaya',
            'email' => 'andi@toko.com',
            'phone' => '0811223344',
            'password' => 'secret1234',
            'role' => 'tenant_admin',
            'is_active' => 1,
            'business_name' => 'Toko Andi',
            'plan' => 'professional',
            'subscription_status' => 'trial',
            'trial_ends_at' => $customTrialEnd,
            'is_subscription_exempt' => 0,
        ]);

        $storeResponse->assertRedirect(route('admin.users.index'));

        $createdUser = User::where('email', 'andi@toko.com')->firstOrFail();
        $this->assertNotNull($createdUser->tenant);
        $this->assertEquals('Toko Andi', $createdUser->tenant->name);
        $this->assertEquals('trial', $createdUser->tenant->subscription_status);
        $this->assertEquals($customTrialEnd, $createdUser->tenant->trial_ends_at->format('Y-m-d\TH:i'));

        // Filter by active trial
        $filterResponse = $this->actingAs($this->superAdmin)->get(route('admin.users.index', [
            'subscription_status' => 'trial_active',
            'search' => 'Toko Andi',
        ]));

        $filterResponse->assertStatus(200);
        $filterResponse->assertSee('Andi Wijaya');
        $filterResponse->assertSee('Toko Andi');
    }
}
