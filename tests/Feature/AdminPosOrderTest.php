<?php

namespace Tests\Feature;

use App\Models\Outlet;
use App\Models\Product;
use App\Models\Setting;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class AdminPosOrderTest extends TestCase
{
    use RefreshDatabase;

    private Tenant $tenant;

    private User $owner;

    protected function setUp(): void
    {
        parent::setUp();

        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        foreach (['create orders', 'view orders', 'manage orders', 'manage settings'] as $perm) {
            Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
        }

        $ownerRole = Role::firstOrCreate(['name' => 'tenant_admin', 'guard_name' => 'web']);
        $ownerRole->syncPermissions(Permission::all());

        $this->tenant = Tenant::create([
            'name' => 'Kopi Test Owner',
            'slug' => 'kopi-test-owner',
            'plan' => 'professional',
            'status' => 'active',
            'subscription_status' => 'active',
        ]);

        $this->owner = User::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Owner User',
            'email' => 'owner@kopitest.id',
            'password' => bcrypt('password123'),
            'is_active' => true,
        ]);
        $this->owner->assignRole($ownerRole);
    }

    public function test_pos_shows_warning_when_owner_has_no_active_outlet_and_displays_error_on_failed_order_submit(): void
    {
        $product = Product::factory()->create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Americano',
            'price' => 25000,
            'track_stock' => false,
            'is_active' => true,
        ]);

        // Visit POS when tenant has 0 outlets
        $posResponse = $this->actingAs($this->owner)->get(route('admin.pos.index'));
        $posResponse->assertOk();
        $posResponse->assertSee('No active outlet found.');
        $posResponse->assertSee('No outlet available');

        // Attempt to submit an order without outlet_id from POS
        $storeResponse = $this->actingAs($this->owner)
            ->from(route('admin.pos.index'))
            ->post(route('admin.orders.store'), [
                'outlet_id' => '',
                'payment_method' => 'cash',
                'amount_tendered' => 25000,
                'items' => [
                    [
                        'product_id' => $product->id,
                        'quantity' => 1,
                        'discount_amount' => 0,
                    ],
                ],
            ]);

        $storeResponse->assertRedirect(route('admin.pos.index'));
        $storeResponse->assertSessionHasErrors('outlet_id');

        // Follow redirect to POS and verify the error banner is rendered
        $redirectedPos = $this->actingAs($this->owner)->get(route('admin.pos.index'));
        $redirectedPos->assertOk();
        $redirectedPos->assertSee('Failed to create order');
        $redirectedPos->assertSee('Order failed to create: No outlet selected or you do not have an active outlet yet.');
    }

    public function test_pos_preselects_first_active_outlet_for_owner_without_outlet_user_pivot(): void
    {
        $outlet1 = Outlet::factory()->create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Branch Alpha',
            'is_active' => true,
        ]);
        Outlet::factory()->create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Branch Beta',
            'is_active' => true,
        ]);

        // Owner is not attached in outlet_user pivot, but tenant has 2 outlets
        $this->assertSame(0, $this->owner->outlets()->count());

        $response = $this->actingAs($this->owner)->get(route('admin.pos.index'));
        $response->assertOk();
        $this->assertEquals($outlet1->id, $response->viewData('defaultOutletId'));
    }

    public function test_pos_order_creation_with_percentage_discount_and_tax_calculates_exact_total(): void
    {
        $outlet = Outlet::factory()->create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Main Outlet',
            'is_active' => true,
        ]);

        $product = Product::factory()->create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Cold Brew',
            'price' => 50000,
            'track_stock' => true,
            'is_active' => true,
        ]);
        $product->outlets()->attach($outlet->id, ['stock' => 20, 'low_stock_threshold' => 2]);

        Setting::create([
            'tenant_id' => $this->tenant->id,
            'key' => 'tax_enabled',
            'value' => '1',
            'type' => 'boolean',
        ]);
        Setting::create([
            'tenant_id' => $this->tenant->id,
            'key' => 'tax_rate',
            'value' => '10',
            'type' => 'integer',
        ]);

        // 2 x 50,000 = 100,000 subtotal
        // 10% discount = 10,000 -> after discount = 90,000
        // 10% tax on 90,000 = 9,000 -> total = 99,000
        $response = $this->actingAs($this->owner)->post(route('admin.orders.store'), [
            'outlet_id' => $outlet->id,
            'discount_amount' => 10,
            'discount_type' => 'percentage',
            'tax_amount' => 9000,
            'payment_method' => 'cash',
            'amount_tendered' => 100000,
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 2,
                    'discount_amount' => 0,
                ],
            ],
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('orders', [
            'tenant_id' => $this->tenant->id,
            'outlet_id' => $outlet->id,
            'subtotal' => 100000,
            'discount_amount' => 10000,
            'discount_type' => 'percentage',
            'tax_amount' => 9000,
            'total_amount' => 99000,
            'status' => 'completed',
            'payment_status' => 'paid',
        ]);
    }

    public function test_pos_order_rolls_back_when_payment_is_insufficient(): void
    {
        $outlet = Outlet::factory()->create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Main Outlet',
            'is_active' => true,
        ]);

        $product = Product::factory()->create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Matcha Latte',
            'price' => 40000,
            'track_stock' => true,
            'is_active' => true,
        ]);
        $product->outlets()->attach($outlet->id, ['stock' => 10, 'low_stock_threshold' => 2]);

        $response = $this->actingAs($this->owner)
            ->from(route('admin.pos.index'))
            ->post(route('admin.orders.store'), [
                'outlet_id' => $outlet->id,
                'payment_method' => 'cash',
                'amount_tendered' => 10000, // Less than 40,000 total
                'items' => [
                    [
                        'product_id' => $product->id,
                        'quantity' => 1,
                        'discount_amount' => 0,
                    ],
                ],
            ]);

        $response->assertRedirect(route('admin.pos.index'));
        $response->assertSessionHasErrors('amount');

        // Order and stock deduction should be rolled back
        $this->assertDatabaseCount('orders', 0);
        $this->assertEquals(10.0, $product->fresh()->getStockForOutlet($outlet->id));
    }

    public function test_card_order_skips_edc_flow_when_edc_is_disabled_in_tenant_settings(): void
    {
        $outlet = Outlet::factory()->create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Main Outlet',
            'is_active' => true,
        ]);

        $product = Product::factory()->create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Cappuccino',
            'price' => 35000,
            'track_stock' => false,
            'is_active' => true,
        ]);

        $this->assertFalse($this->tenant->fresh()->isEdcEnabled());

        // Card payment without reference_number succeeds when EDC module is inactive
        $response = $this->actingAs($this->owner)->post(route('admin.orders.store'), [
            'outlet_id' => $outlet->id,
            'payment_method' => 'card',
            'amount_tendered' => 35000,
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 1,
                    'discount_amount' => 0,
                ],
            ],
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('orders', [
            'tenant_id' => $this->tenant->id,
            'outlet_id' => $outlet->id,
            'total_amount' => 35000,
            'status' => 'completed',
            'payment_status' => 'paid',
        ]);
    }

    public function test_tenant_can_enable_and_configure_edc_in_settings_and_pos_enforces_and_stores_reference_number(): void
    {
        $outlet = Outlet::factory()->create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Main Outlet',
            'is_active' => true,
        ]);

        $product = Product::factory()->create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Flat White',
            'price' => 42000,
            'track_stock' => false,
            'is_active' => true,
        ]);

        // 1. Enable & configure EDC in tenant settings
        $settingResponse = $this->actingAs($this->owner)->put(route('admin.settings.edc.update'), [
            'edc_enabled' => '1',
            'edc_connection_mode' => 'both',
            'edc_banks' => 'BCA, Mandiri, BNI',
            'edc_require_reference' => '1',
            'edc_serial_baud_rate' => 115200,
        ]);

        $settingResponse->assertRedirect();
        $this->assertTrue($this->tenant->fresh()->isEdcEnabled());
        $edcConfig = $this->tenant->fresh()->edcConfig();
        $this->assertTrue($edcConfig['enabled']);
        $this->assertSame('both', $edcConfig['connection_mode']);
        $this->assertSame(['BCA', 'Mandiri', 'BNI'], $edcConfig['banks']);
        $this->assertTrue($edcConfig['require_reference']);
        $this->assertSame(115200, $edcConfig['serial_baud_rate']);

        // 2. Submitting a card order without reference_number fails when edc_enabled && edc_require_reference
        $failedOrder = $this->actingAs($this->owner)
            ->from(route('admin.pos.index'))
            ->post(route('admin.orders.store'), [
                'outlet_id' => $outlet->id,
                'payment_method' => 'card',
                'amount_tendered' => 42000,
                'reference_number' => '',
                'items' => [
                    [
                        'product_id' => $product->id,
                        'quantity' => 1,
                        'discount_amount' => 0,
                    ],
                ],
            ]);

        $failedOrder->assertRedirect(route('admin.pos.index'));
        $failedOrder->assertSessionHasErrors('reference_number');
        $this->assertDatabaseCount('orders', 0);

        // 3. Submitting a card order with EDC reference_number succeeds and stores it in payments
        $successOrder = $this->actingAs($this->owner)->post(route('admin.orders.store'), [
            'outlet_id' => $outlet->id,
            'payment_method' => 'card',
            'amount_tendered' => 42000,
            'reference_number' => 'BCA (Debit) - APPR:789123',
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 1,
                    'discount_amount' => 0,
                ],
            ],
        ]);

        $successOrder->assertRedirect();
        $this->assertDatabaseHas('payments', [
            'payment_method' => 'card',
            'amount' => 42000,
            'reference_number' => 'BCA (Debit) - APPR:789123',
            'status' => 'completed',
        ]);
    }

    public function test_customer_display_screen_renders_for_authenticated_pos_user(): void
    {
        $response = $this->actingAs($this->owner)->get(route('admin.pos.customer-display'));
        $response->assertOk();
        $response->assertSee('Customer Display');
        $response->assertSee('Daftar Pesanan Anda');
    }
}
