<?php

namespace Tests\Feature;

use App\Models\Outlet;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use App\Models\SupplierInvoice;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class SupplierManagementTest extends TestCase
{
    use RefreshDatabase;

    private Tenant $retailTenant;

    private User $retailAdmin;

    private Outlet $outlet;

    private Product $product;

    protected function setUp(): void
    {
        parent::setUp();

        // Guard: Ensure test ONLY runs on SQLite :memory:
        $this->assertSame('sqlite', config('database.default'));
        $this->assertSame(':memory:', config('database.connections.sqlite.database'));

        Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);
        $tenantAdminRole = Role::firstOrCreate(['name' => 'tenant_admin', 'guard_name' => 'web']);

        $perms = [
            'manage settings',
            'view suppliers',
            'manage suppliers',
            'manage purchase orders',
            'approve purchase orders',
            'receive goods',
            'manage supplier invoices',
            'manage supplier payments',
        ];
        foreach ($perms as $p) {
            Permission::firstOrCreate(['name' => $p, 'guard_name' => 'web']);
        }
        $tenantAdminRole->syncPermissions($perms);

        $this->retailTenant = Tenant::create([
            'name' => 'Toko Retail Sejahtera',
            'slug' => 'toko-retail-sejahtera',
            'business_type' => 'retail',
            'plan' => 'professional',
            'status' => 'active',
            'subscription_status' => 'active',
        ]);

        $this->outlet = Outlet::create([
            'tenant_id' => $this->retailTenant->id,
            'name' => 'Cabang Utama',
            'code' => 'MAIN',
            'is_active' => true,
        ]);

        $this->retailAdmin = User::factory()->create([
            'tenant_id' => $this->retailTenant->id,
            'is_active' => true,
        ]);
        $this->retailAdmin->assignRole('tenant_admin');

        $this->product = Product::create([
            'tenant_id' => $this->retailTenant->id,
            'name' => 'Kopi Arabika 250g',
            'sku' => 'KOP-250',
            'price' => 18000,
            'cost_price' => 9500,
            'unit' => 'pcs',
            'is_active' => true,
            'track_stock' => true,
        ]);

        $this->product->outlets()->attach($this->outlet->id, [
            'stock' => 5,
            'low_stock_threshold' => 20,
        ]);
    }

    public function test_supplier_management_is_optional_per_tenant_and_respects_business_type_and_super_admin_override(): void
    {
        // 1. Retail tenant defaults to enabled
        $this->assertTrue($this->retailTenant->isSupplierManagementEnabled());

        // 2. Service-only tenant (e.g. Barbershop) defaults to disabled
        $barbershopTenant = Tenant::create([
            'name' => 'Gentlemen Barbershop',
            'slug' => 'gentlemen-barbershop',
            'business_type' => 'Barbershop & Salon Jasa',
            'plan' => 'basic',
            'status' => 'active',
            'subscription_status' => 'active',
        ]);
        $this->assertFalse($barbershopTenant->isSupplierManagementEnabled());

        $barberAdmin = User::factory()->create([
            'tenant_id' => $barbershopTenant->id,
            'is_active' => true,
        ]);
        $barberAdmin->assignRole('tenant_admin');

        // Accessing supplier routes while disabled redirects to settings
        $this->actingAs($barberAdmin)
            ->get(route('admin.suppliers.index'))
            ->assertRedirect(route('admin.settings.index'));

        // 3. Tenant Admin can self-enable Supplier Management via Settings
        $this->actingAs($barberAdmin)
            ->put(route('admin.settings.modules.update'), [
                'supplier_management_enabled' => '1',
                'po_approval_threshold' => 3000000,
            ])
            ->assertRedirect(route('admin.settings.index'));

        $barbershopTenant->refresh();
        $this->assertTrue($barbershopTenant->isSupplierManagementEnabled());

        // Now accessible
        $this->actingAs($barberAdmin)
            ->get(route('admin.suppliers.index'))
            ->assertStatus(200);

        // 4. Super Admin can force-disable via override
        $barbershopTenant->setSetting('supplier_management_override', 'disabled');
        $this->assertFalse($barbershopTenant->fresh()->isSupplierManagementEnabled());
    }

    public function test_complete_procurement_workflow_100_ordered_90_received_updates_stock_by_90_and_processes_payables(): void
    {
        $this->actingAs($this->retailAdmin);

        // Step 01: Create Supplier A (Auto-code SUP-0001) & Map Product
        $this->post(route('admin.suppliers.store'), [
            'name' => 'Supplier A',
            'company_name' => 'PT Supplier A Distribusi',
            'contact_person' => 'Andi',
            'phone' => '0811223344',
            'npwp' => '01.111.222.3-444.000',
            'default_payment_terms' => 30,
            'is_active' => '1',
        ])->assertRedirect(route('admin.suppliers.index'));

        $supplier = Supplier::firstOrFail();
        $this->assertSame('SUP-0001', $supplier->code);

        // Map product at Rp 10,000 per pcs
        $this->post(route('admin.supplier-products.store'), [
            'supplier_id' => $supplier->id,
            'product_id' => $this->product->id,
            'purchase_price' => 10000,
            'is_primary' => '1',
            'min_order_qty' => 10,
            'lead_time_days' => 2,
            'purchase_unit' => 'pcs',
            'conversion_factor' => 1,
        ])->assertRedirect();

        $this->assertDatabaseHas('supplier_products', [
            'supplier_id' => $supplier->id,
            'product_id' => $this->product->id,
            'purchase_price' => 10000,
            'is_primary' => true,
        ]);

        // Step 02 & 03: Create Purchase Order for 100 pcs @ Rp 10,000 (Rp 1,000,000) & Approve/Order
        $this->post(route('admin.purchase-orders.store'), [
            'outlet_id' => $this->outlet->id,
            'supplier_id' => $supplier->id,
            'order_date' => now()->toDateString(),
            'expected_date' => now()->addDays(2)->toDateString(),
            'payment_terms' => 30,
            'discount_amount' => 0,
            'tax_rate' => 0,
            'shipping_cost' => 0,
            'submit_action' => 'submit',
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'quantity_ordered' => 100,
                    'unit_price' => 10000,
                    'discount_amount' => 0,
                    'purchase_unit' => 'pcs',
                    'conversion_factor' => 1,
                ],
            ],
        ])->assertRedirect();

        $po = PurchaseOrder::with('items')->firstOrFail();
        $this->assertEquals(1000000.00, (float) $po->total_amount);
        $this->assertSame(PurchaseOrder::STATUS_APPROVED, $po->status);

        $this->post(route('admin.purchase-orders.mark-ordered', $po))->assertRedirect();
        $this->assertSame(PurchaseOrder::STATUS_ORDERED, $po->fresh()->status);

        // Step 04 & 05: Receive Goods — Only 90 pcs valid received, 10 pcs damaged
        // Initial stock was 5 pcs. After receiving 90 pcs valid, stock MUST be 95 pcs (increased by 90, NOT 100).
        $poItem = $po->items->first();

        $this->post(route('admin.goods-receipts.store'), [
            'purchase_order_id' => $po->id,
            'received_date' => now()->toDateString(),
            'supplier_delivery_note' => 'SJ-SUPA-001',
            'items' => [
                [
                    'purchase_order_item_id' => $poItem->id,
                    'quantity_received' => 90,
                    'quantity_damaged' => 10,
                    'batch_number' => 'BATCH-2026-10',
                    'expiry_date' => now()->addYear()->toDateString(),
                ],
            ],
        ])->assertRedirect(route('admin.goods-receipts.index'));

        $this->assertSame(PurchaseOrder::STATUS_PARTIALLY_RECEIVED, $po->fresh()->status);
        $this->assertEquals(90.0, (float) $poItem->fresh()->quantity_received);

        // Verify Inventory Stock increased by 90 (from 5 to 95), NOT 100!
        $currentStock = $this->product->fresh()->getStockForOutlet($this->outlet->id);
        $this->assertEquals(95.0, $currentStock);

        // Step 06: Record Supplier Invoice for 90 pcs received (Rp 900,000) with Three-Way Matching
        $gr = $po->goodsReceipts()->firstOrFail();

        $this->post(route('admin.supplier-invoices.store'), [
            'supplier_id' => $supplier->id,
            'purchase_order_id' => $po->id,
            'goods_receipt_id' => $gr->id,
            'supplier_invoice_number' => 'INV-SUPA-90',
            'invoice_date' => now()->toDateString(),
            'payment_terms' => 30,
            'subtotal' => 900000,
            'tax_amount' => 0,
            'discount_amount' => 0,
            'shipping_cost' => 0,
        ])->assertRedirect(route('admin.supplier-invoices.index'));

        $invoice = SupplierInvoice::firstOrFail();
        $this->assertEquals(900000.00, (float) $invoice->total_amount);
        $this->assertEquals(900000.00, (float) $invoice->outstanding_balance);
        $this->assertSame('matched', $invoice->matching_status);
        $this->assertSame(SupplierInvoice::STATUS_UNPAID, $invoice->status);

        // Step 07: Process Partial Payment (Rp 400,000) then Remaining Payment (Rp 500,000)
        $this->post(route('admin.supplier-payments.store'), [
            'supplier_invoice_id' => $invoice->id,
            'payment_date' => now()->toDateString(),
            'amount' => 400000,
            'payment_method' => 'bank_transfer',
            'reference_number' => 'TRF-001',
        ])->assertRedirect();

        $invoice->refresh();
        $this->assertSame(SupplierInvoice::STATUS_PARTIALLY_PAID, $invoice->status);
        $this->assertEquals(500000.00, (float) $invoice->outstanding_balance);

        // Pay remaining Rp 500,000
        $this->post(route('admin.supplier-payments.store'), [
            'supplier_invoice_id' => $invoice->id,
            'payment_date' => now()->toDateString(),
            'amount' => 500000,
            'payment_method' => 'bank_transfer',
            'reference_number' => 'TRF-002',
        ])->assertRedirect();

        $invoice->refresh();
        $this->assertSame(SupplierInvoice::STATUS_PAID, $invoice->status);
        $this->assertEquals(0.00, (float) $invoice->outstanding_balance);

        // Verify Reports page loads with Supplier Performance Score
        $this->get(route('admin.supplier-reports.index'))
            ->assertStatus(200)
            ->assertSee('Supplier A');
    }
}
