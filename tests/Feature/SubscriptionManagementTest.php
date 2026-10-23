<?php

namespace Tests\Feature;

use App\Models\Tenant;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class SubscriptionManagementTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'tenant_admin', 'guard_name' => 'web']);
    }

    public function test_tenant_admin_can_view_subscription_management_portal(): void
    {
        $tenant = Tenant::create([
            'name' => 'Kopi Nusantara',
            'slug' => 'kopi-nusantara',
            'email' => 'owner@kopinusantara.id',
            'plan' => 'professional',
            'status' => 'active',
            'subscription_status' => 'trial',
            'trial_ends_at' => now()->addDays(10),
        ]);

        $user = User::factory()->create([
            'tenant_id' => $tenant->id,
            'is_active' => true,
        ]);
        $user->assignRole('tenant_admin');

        $response = $this->actingAs($user)->get(route('admin.subscription.index'));

        $response->assertStatus(200);
        $response->assertSee('Kopi Nusantara');
        $response->assertSee('Professional Plan');
        $response->assertSee('Resource Usage &amp; Plan Limits', false);
        $response->assertSee('Billing Profile &amp; Payment Settings', false);
    }

    public function test_tenant_can_upgrade_plan_and_generate_invoice(): void
    {
        $tenant = Tenant::create([
            'name' => 'Bistro Jaya',
            'slug' => 'bistro-jaya',
            'email' => 'billing@bistrojaya.id',
            'plan' => 'basic',
            'status' => 'active',
            'subscription_status' => 'trial',
            'trial_ends_at' => now()->addDays(5),
        ]);

        $user = User::factory()->create([
            'tenant_id' => $tenant->id,
            'is_active' => true,
        ]);
        $user->assignRole('tenant_admin');

        $response = $this->actingAs($user)->post(route('admin.subscription.plan.change'), [
            'plan' => 'professional',
            'billing_cycle' => 'annual',
            'payment_method' => 'qris',
            'coupon_code' => 'POSPRO20',
        ]);

        $response->assertRedirect(route('admin.subscription.index'));

        $tenant->refresh();
        $this->assertEquals('professional', $tenant->plan);
        $this->assertEquals('active', $tenant->subscription_status);
        $this->assertNotNull($tenant->subscription_ends_at);
        $this->assertNotEmpty($tenant->billingHistory());
        $this->assertEquals('paid', $tenant->billingHistory()[0]['status']);
        $this->assertEquals(2296000, $tenant->billingHistory()[0]['amount']); // 2,870,000 - 20%
    }

    public function test_tenant_can_update_billing_profile(): void
    {
        $tenant = Tenant::create([
            'name' => 'Martabak Sultan',
            'slug' => 'martabak-sultan',
            'plan' => 'professional',
            'status' => 'active',
            'subscription_status' => 'active',
            'subscription_ends_at' => now()->addMonth(),
        ]);

        $user = User::factory()->create([
            'tenant_id' => $tenant->id,
            'is_active' => true,
        ]);
        $user->assignRole('tenant_admin');

        $response = $this->actingAs($user)->put(route('admin.subscription.billing.update'), [
            'billing_name' => 'PT Martabak Sultan Indonesia',
            'billing_email' => 'finance@martabaksultan.co.id',
            'billing_phone' => '+62811998877',
            'tax_id' => '12.345.678.9-012.000',
            'billing_address' => 'Jl. Sudirman No. 88, Jakarta',
            'default_payment_method' => 'virtual_account',
            'auto_renew' => '1',
        ]);

        $response->assertRedirect(route('admin.subscription.index'));

        $tenant->refresh();
        $this->assertEquals('PT Martabak Sultan Indonesia', $tenant->getSetting('billing.billing_name'));
        $this->assertEquals('12.345.678.9-012.000', $tenant->getSetting('billing.tax_id'));
        $this->assertTrue($tenant->getSetting('billing.auto_renew'));
    }

    public function test_super_admin_can_manage_tenant_subscriptions(): void
    {
        $superAdmin = User::factory()->create([
            'tenant_id' => null,
            'is_active' => true,
        ]);
        $superAdmin->assignRole('super_admin');

        $tenant = Tenant::create([
            'name' => 'Retail Maju',
            'slug' => 'retail-maju',
            'plan' => 'basic',
            'status' => 'active',
            'subscription_status' => 'expired',
        ]);

        $response = $this->actingAs($superAdmin)->post(
            route('admin.subscription.admin-action', $tenant->id),
            [
                'action' => 'activate_paid',
                'plan' => 'enterprise',
                'months' => 6,
            ]
        );

        $response->assertRedirect();

        $tenant->refresh();
        $this->assertEquals('enterprise', $tenant->plan);
        $this->assertEquals('active', $tenant->subscription_status);
        $this->assertTrue($tenant->hasActiveAccess());
    }
}
