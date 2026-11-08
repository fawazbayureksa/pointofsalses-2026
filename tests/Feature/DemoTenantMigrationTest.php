<?php

namespace Tests\Feature;

use App\Models\CashierShift;
use App\Models\Category;
use App\Models\Customer;
use App\Models\GoodsReceipt;
use App\Models\Order;
use App\Models\Outlet;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\Setting;
use App\Models\StockMovement;
use App\Models\Supplier;
use App\Models\SupplierInvoice;
use App\Models\SupplierPayment;
use App\Models\SupplierReturn;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DemoTenantMigrationTest extends TestCase
{
    use RefreshDatabase;

    private string $migrationPath = 'database/migrations/2026_11_07_000001_seed_demo_tenant_sample_data.php';

    public function test_demo_tenant_migration_seeds_complete_business_data_and_rolls_back_cleanly(): void
    {
        config(['database.seed_demo_in_tests' => true]);

        $migration = require base_path($this->migrationPath);
        $migration->up();

        // 1. Verify Tenant
        $tenant = Tenant::where('slug', 'kopi-nusantara-demo')->first();
        $this->assertNotNull($tenant);
        $this->assertSame('Kopi & Roastery Nusantara', $tenant->name);
        $this->assertSame('professional', $tenant->plan);
        $this->assertTrue($tenant->isActive());
        $this->assertTrue($tenant->hasActiveAccess());
        $this->assertTrue($tenant->isSupplierManagementEnabled());

        // 2. Verify Settings, Outlets & Users
        $this->assertGreaterThanOrEqual(15, Setting::withoutGlobalScopes()->where('tenant_id', $tenant->id)->count());
        $this->assertSame(3, Outlet::withoutGlobalScopes()->where('tenant_id', $tenant->id)->count());

        $owner = User::where('email', 'owner@kopinusantara.id')->first();
        $manager = User::where('email', 'manager@kopinusantara.id')->first();
        $cashier = User::where('email', 'kasir.senopati@kopinusantara.id')->first();

        $this->assertNotNull($owner);
        $this->assertNotNull($manager);
        $this->assertNotNull($cashier);
        $this->assertTrue($owner->hasRole('tenant_admin'));
        $this->assertTrue($manager->hasRole('manager'));
        $this->assertTrue($cashier->hasRole('cashier'));
        $this->assertTrue($cashier->verifyPin('123456'));
        $this->assertSame(3, $owner->outlets()->count());

        // 3. Verify Categories, Products, Multi-Outlet Stock & Customers
        $this->assertSame(6, Category::withoutGlobalScopes()->where('tenant_id', $tenant->id)->count());
        $this->assertSame(18, Product::withoutGlobalScopes()->where('tenant_id', $tenant->id)->count());
        $this->assertSame(7, Customer::withoutGlobalScopes()->where('tenant_id', $tenant->id)->count());

        // 4. Verify Suppliers, POs, Goods Receipts, Payables & Returns
        $this->assertSame(3, Supplier::withoutGlobalScopes()->where('tenant_id', $tenant->id)->count());
        $this->assertSame(3, PurchaseOrder::withoutGlobalScopes()->where('tenant_id', $tenant->id)->count());
        $this->assertSame(2, GoodsReceipt::withoutGlobalScopes()->where('tenant_id', $tenant->id)->count());
        $this->assertSame(2, SupplierInvoice::withoutGlobalScopes()->where('tenant_id', $tenant->id)->count());
        $this->assertSame(2, SupplierPayment::withoutGlobalScopes()->where('tenant_id', $tenant->id)->count());
        $this->assertSame(1, SupplierReturn::withoutGlobalScopes()->where('tenant_id', $tenant->id)->count());

        // 5. Verify Cashier Shifts, Orders & Stock Movements
        $this->assertSame(2, CashierShift::withoutGlobalScopes()->where('tenant_id', $tenant->id)->count());
        $this->assertNotNull($cashier->activeShift);
        $this->assertSame(17, Order::withoutGlobalScopes()->where('tenant_id', $tenant->id)->count());
        $this->assertSame(7, StockMovement::withoutGlobalScopes()->where('tenant_id', $tenant->id)->count());

        // 6. Verify key pages render for the Demo Owner
        $this->actingAs($owner)->get(route('admin.dashboard'))->assertOk();
        $this->actingAs($owner)->get(route('admin.pos.index'))->assertOk();
        $this->actingAs($owner)->get(route('admin.guide'))->assertOk();

        // 7. Verify idempotency (running up() a second time does not duplicate data)
        $migration->up();
        $this->assertSame(18, Product::withoutGlobalScopes()->where('tenant_id', $tenant->id)->count());
        $this->assertSame(17, Order::withoutGlobalScopes()->where('tenant_id', $tenant->id)->count());

        // 8. Verify down() cleans up the demo tenant
        $migration->down();
        $this->assertNull(Tenant::withTrashed()->where('slug', 'kopi-nusantara-demo')->first());
        $this->assertNull(User::withTrashed()->where('email', 'owner@kopinusantara.id')->first());
    }
}
