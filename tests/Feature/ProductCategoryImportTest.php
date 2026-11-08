<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Order;
use App\Models\Outlet;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ProductCategoryImportTest extends TestCase
{
    use RefreshDatabase;

    private Tenant $tenant;

    private User $admin;

    private Outlet $outlet;

    protected function setUp(): void
    {
        parent::setUp();

        // Guard: Ensure test ONLY runs on SQLite :memory:
        $this->assertSame('sqlite', config('database.default'));
        $this->assertSame(':memory:', config('database.connections.sqlite.database'));

        $tenantAdminRole = Role::firstOrCreate(['name' => 'tenant_admin', 'guard_name' => 'web']);

        $perms = [
            'create categories',
            'edit categories',
            'delete categories',
            'create products',
            'edit products',
            'delete products',
            'view products',
            'manage users',
        ];

        foreach ($perms as $p) {
            Permission::firstOrCreate(['name' => $p, 'guard_name' => 'web']);
        }
        $tenantAdminRole->syncPermissions($perms);

        $this->tenant = Tenant::create([
            'name' => 'Kopi Nusantara',
            'slug' => 'kopi-nusantara',
            'business_type' => 'fnb',
            'plan' => 'professional',
            'status' => 'active',
            'subscription_status' => 'active',
            'subscription_expires_at' => now()->addDays(30),
        ]);

        $this->admin = User::factory()->create([
            'tenant_id' => $this->tenant->id,
            'email' => 'admin@kopinusantara.test',
        ]);
        $this->admin->assignRole($tenantAdminRole);

        $this->outlet = Outlet::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Main Outlet',
            'code' => 'OUT-001',
            'is_active' => true,
        ]);
    }

    public function test_can_download_category_import_template(): void
    {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.categories.import-template'));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
        $content = $response->streamedContent();
        $this->assertStringContainsString('name,slug,parent_category,sort_order,is_active', $content);
        $this->assertStringContainsString('Beverages,beverages,,1,1', $content);
    }

    public function test_can_import_categories_from_csv(): void
    {
        $csvContent = implode("\n", [
            'name,slug,parent_category,sort_order,is_active',
            'Beverages,beverages,,1,1',
            'Hot Coffee,hot-coffee,Beverages,2,1',
            'Seasonal Menu,,Beverages,3,0',
        ]);

        $file = UploadedFile::fake()->createWithContent('categories.csv', $csvContent);

        $response = $this->actingAs($this->admin)
            ->post(route('admin.categories.import'), [
                'file' => $file,
            ]);

        $response->assertRedirect(route('admin.categories.index'));
        $response->assertSessionHas('success');

        $beverages = Category::where('slug', 'beverages')->first();
        $this->assertNotNull($beverages);
        $this->assertSame($this->tenant->id, $beverages->tenant_id);
        $this->assertTrue($beverages->is_active);

        $hotCoffee = Category::where('slug', 'hot-coffee')->first();
        $this->assertNotNull($hotCoffee);
        $this->assertSame($beverages->id, $hotCoffee->parent_id);

        $seasonal = Category::where('slug', 'seasonal-menu')->first();
        $this->assertNotNull($seasonal);
        $this->assertFalse($seasonal->is_active);
    }

    public function test_can_download_product_import_template(): void
    {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.products.import-template'));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
        $content = $response->streamedContent();
        $this->assertStringContainsString('name,sku,barcode,category,price,cost_price,unit,is_active,track_stock,outlet,stock,low_stock_threshold,description', $content);
        $this->assertStringContainsString('PRD-001', $content);
    }

    public function test_can_import_products_from_csv_with_category_and_outlet_stock(): void
    {
        $csvContent = implode("\n", [
            'name,sku,barcode,category,price,cost_price,unit,is_active,track_stock,outlet,stock,low_stock_threshold,description',
            'Single Origin Espresso,SKU-ESP-01,8990001,Specialty Coffee,28000,14000,cup,1,1,Main Outlet,120,15,Arabica Gayo',
            'Almond Croissant,SKU-PAS-01,8990002,Pastries,22000,10000,pcs,1,1,OUT-001,45,5,Flaky pastry',
        ]);

        $file = UploadedFile::fake()->createWithContent('products.csv', $csvContent);

        $response = $this->actingAs($this->admin)
            ->post(route('admin.products.import'), [
                'file' => $file,
            ]);

        $response->assertRedirect(route('admin.products.index'));
        $response->assertSessionHas('success');

        $espresso = Product::where('sku', 'SKU-ESP-01')->first();
        $this->assertNotNull($espresso);
        $this->assertSame($this->tenant->id, $espresso->tenant_id);
        $this->assertSame('28000.00', $espresso->price);
        $this->assertSame('14000.00', $espresso->cost_price);
        $this->assertSame('Specialty Coffee', $espresso->category?->name);
        $this->assertSame(120.0, $espresso->getStockForOutlet($this->outlet->id));

        $croissant = Product::where('sku', 'SKU-PAS-01')->first();
        $this->assertNotNull($croissant);
        $this->assertSame('Pastries', $croissant->category?->name);
        $this->assertSame(45.0, $croissant->getStockForOutlet($this->outlet->id));
    }

    public function test_product_import_rejects_invalid_rows_without_partial_writes(): void
    {
        $csvContent = implode("\n", [
            'name,sku,price',
            'Valid Product,SKU-VAL-01,15000',
            'Invalid Price Product,SKU-INV-02,-500',
        ]);

        $file = UploadedFile::fake()->createWithContent('invalid-products.csv', $csvContent);

        $response = $this->actingAs($this->admin)
            ->post(route('admin.products.import'), [
                'file' => $file,
            ]);

        $response->assertRedirect(route('admin.products.index'));
        $response->assertSessionHasErrors();
        $this->assertDatabaseMissing('products', ['sku' => 'SKU-VAL-01']);
    }

    public function test_product_list_formats_price_as_currency_and_hides_id_column(): void
    {
        $category = Category::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Coffee',
            'slug' => 'coffee',
            'is_active' => true,
        ]);

        Product::create([
            'tenant_id' => $this->tenant->id,
            'category_id' => $category->id,
            'name' => 'Cappuccino',
            'sku' => 'CAP-01',
            'price' => 32500,
            'cost_price' => 15000,
            'unit' => 'cup',
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.products.index'));
        $response->assertOk();
        $response->assertSee('Rp 32.500');
        $this->assertDoesNotMatchRegularExpression('/<th[^>]*>\s*ID\s*<\/th>/i', $response->getContent());

        $catResponse = $this->actingAs($this->admin)->get(route('admin.categories.index'));
        $catResponse->assertOk();
        $this->assertDoesNotMatchRegularExpression('/<th[^>]*>\s*ID\s*<\/th>/i', $catResponse->getContent());

        $outletResponse = $this->actingAs($this->admin)->get(route('admin.outlets.index'));
        $outletResponse->assertOk();
        $this->assertDoesNotMatchRegularExpression('/<th[^>]*>\s*ID\s*<\/th>/i', $outletResponse->getContent());

        $customerResponse = $this->actingAs($this->admin)->get(route('admin.customers.index'));
        $customerResponse->assertOk();
        $this->assertDoesNotMatchRegularExpression('/<th[^>]*>\s*ID\s*<\/th>/i', $customerResponse->getContent());

        $userResponse = $this->actingAs($this->admin)->get(route('admin.users.index'));
        $userResponse->assertOk();
        $this->assertDoesNotMatchRegularExpression('/<th[^>]*>\s*ID\s*<\/th>/i', $userResponse->getContent());
    }

    public function test_payments_and_tenants_hide_id_and_format_currency(): void
    {
        $superAdminRole = Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);
        $superAdmin = User::factory()->create(['tenant_id' => null]);
        $superAdmin->assignRole($superAdminRole);

        $tenantResponse = $this->actingAs($superAdmin)->get(route('admin.tenants.index'));
        $tenantResponse->assertOk();
        $this->assertDoesNotMatchRegularExpression('/<th[^>]*>\s*ID\s*<\/th>/i', $tenantResponse->getContent());

        $order = Order::create([
            'tenant_id' => $this->tenant->id,
            'outlet_id' => $this->outlet->id,
            'user_id' => $this->admin->id,
            'order_number' => 'ORD-TEST-001',
            'status' => 'completed',
            'subtotal' => 50000,
            'tax_amount' => 0,
            'total_amount' => 50000,
        ]);

        Payment::create([
            'order_id' => $order->id,
            'payment_method' => 'cash',
            'amount' => 50000,
            'change_amount' => 10000,
            'status' => 'completed',
        ]);

        $paymentsResponse = $this->actingAs($this->admin)->get(route('admin.orders.payments'));
        $paymentsResponse->assertOk();
        $this->assertDoesNotMatchRegularExpression('/<th[^>]*>\s*ID\s*<\/th>/i', $paymentsResponse->getContent());
        $paymentsResponse->assertSee('Rp 50.000');
        $paymentsResponse->assertSee('Rp 10.000');
    }
}
