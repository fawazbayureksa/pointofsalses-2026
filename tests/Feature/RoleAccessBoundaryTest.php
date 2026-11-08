<?php

namespace Tests\Feature;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class RoleAccessBoundaryTest extends TestCase
{
    use RefreshDatabase;

    private User $superAdmin;

    private User $tenantOwner;

    private Tenant $tenant;

    protected function setUp(): void
    {
        parent::setUp();

        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // Create standard roles & permissions
        $perms = [
            'manage users',
            'create users',
            'edit users',
            'delete users',
        ];

        foreach ($perms as $p) {
            Permission::firstOrCreate(['name' => $p, 'guard_name' => 'web']);
        }

        $superAdminRole = Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);
        $tenantAdminRole = Role::firstOrCreate(['name' => 'tenant_admin', 'guard_name' => 'web']);
        $managerRole = Role::firstOrCreate(['name' => 'manager', 'guard_name' => 'web']);
        $cashierRole = Role::firstOrCreate(['name' => 'cashier', 'guard_name' => 'web']);

        $tenantAdminRole->syncPermissions($perms);

        $this->superAdmin = User::create([
            'tenant_id' => null,
            'name' => 'Super Admin',
            'email' => 'super@system.local',
            'password' => bcrypt('password123'),
            'is_active' => true,
        ]);
        $this->superAdmin->assignRole($superAdminRole);

        $this->tenant = Tenant::create([
            'name' => 'Kopi Nusantara',
            'slug' => 'kopi-nusantara',
            'business_type' => 'cafe',
            'plan' => 'professional',
            'status' => 'active',
            'subscription_status' => 'active',
        ]);

        $this->tenantOwner = User::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Budi Owner',
            'email' => 'owner@kopinusantara.id',
            'password' => bcrypt('password123'),
            'is_active' => true,
        ]);
        $this->tenantOwner->assignRole($tenantAdminRole);
    }

    public function test_tenant_owner_cannot_access_roles_and_permissions_management(): void
    {
        // Tenant owner should be forbidden from accessing /admin/roles and /admin/permissions
        $this->actingAs($this->tenantOwner)
            ->get(route('admin.roles.index'))
            ->assertForbidden();

        $this->actingAs($this->tenantOwner)
            ->get(route('admin.permissions.index'))
            ->assertForbidden();

        // But super admin can access them
        $this->actingAs($this->superAdmin)
            ->get(route('admin.roles.index'))
            ->assertOk();

        $this->actingAs($this->superAdmin)
            ->get(route('admin.permissions.index'))
            ->assertOk();
    }

    public function test_tenant_owner_does_not_see_super_admin_role_option_in_user_management(): void
    {
        // When tenant owner visits user management, super_admin role should not be listed as an option
        $response = $this->actingAs($this->tenantOwner)
            ->get(route('admin.users.index'));

        $response->assertOk();
        $roles = $response->viewData('roles');
        $this->assertFalse($roles->contains('name', 'super_admin'));
        $this->assertTrue($roles->contains('name', 'cashier'));
        $this->assertTrue($roles->contains('name', 'manager'));
        $this->assertTrue($roles->contains('name', 'tenant_admin'));
    }

    public function test_super_admin_can_see_super_admin_role_option_in_user_management(): void
    {
        $response = $this->actingAs($this->superAdmin)
            ->get(route('admin.users.index'));

        $response->assertOk();
        $roles = $response->viewData('roles');
        $this->assertTrue($roles->contains('name', 'super_admin'));
    }

    public function test_tenant_owner_cannot_assign_super_admin_role_when_creating_or_updating_user(): void
    {
        // Attempting to assign super_admin role should fail validation for tenant owner
        $response = $this->actingAs($this->tenantOwner)
            ->post(route('admin.users.store'), [
                'name' => 'Hacker User',
                'email' => 'hacker@tenant.test',
                'password' => 'secret1234',
                'role' => 'super_admin',
                'is_active' => 1,
            ]);

        $response->assertSessionHasErrors('role');
        $this->assertDatabaseMissing('users', ['email' => 'hacker@tenant.test']);

        // Attempting to update existing user to super_admin should also fail validation
        $staff = User::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Staff Regular',
            'email' => 'staff@tenant.test',
            'password' => bcrypt('password123'),
            'is_active' => true,
        ]);
        $staff->assignRole('cashier');

        $updateResponse = $this->actingAs($this->tenantOwner)
            ->put(route('admin.users.update', $staff->id), [
                'name' => 'Staff Regular',
                'email' => 'staff@tenant.test',
                'role' => 'super_admin',
                'is_active' => 1,
            ]);

        $updateResponse->assertSessionHasErrors('role');
        $this->assertFalse($staff->fresh()->hasRole('super_admin'));
    }

    public function test_tenant_owner_can_assign_valid_tenant_roles(): void
    {
        $response = $this->actingAs($this->tenantOwner)
            ->post(route('admin.users.store'), [
                'name' => 'Kasir Baru',
                'email' => 'kasirbaru@tenant.test',
                'password' => 'secret1234',
                'role' => 'cashier',
                'is_active' => 1,
            ]);

        $response->assertRedirect(route('admin.users.index'));
        $created = User::where('email', 'kasirbaru@tenant.test')->firstOrFail();
        $this->assertTrue($created->hasRole('cashier'));
        $this->assertSame($this->tenant->id, $created->tenant_id);
    }

    public function test_sidebar_renders_search_bar_and_shortcuts(): void
    {
        $response = $this->actingAs($this->tenantOwner)
            ->get(route('admin.dashboard'));

        $response->assertOk();
        $response->assertSee('Search menu... (Ctrl+K)');
        $response->assertSee('menuSearch');
        $response->assertSee('No matching menu');
        $response->assertSee('sidebar_scroll_top');
        $response->assertSee('active-menu-item');
    }
}
