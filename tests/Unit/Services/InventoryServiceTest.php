<?php

namespace Tests\Unit\Services;

use App\Models\Outlet;
use App\Models\Product;
use App\Models\Tenant;
use App\Models\User;
use App\Services\InventoryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class InventoryServiceTest extends TestCase
{
    use RefreshDatabase;

    private InventoryService $service;
    private Tenant $tenant;
    private User $user;
    private Outlet $outlet;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = app(InventoryService::class);
        $this->tenant  = Tenant::factory()->create();
        $this->user    = User::factory()->create(['tenant_id' => $this->tenant->id]);
        $this->outlet  = Outlet::factory()->create(['tenant_id' => $this->tenant->id]);

        $this->actingAs($this->user);
    }

    // ── validateStockForItems ────────────────────────────────────────────────

    public function test_validate_stock_passes_when_sufficient_stock(): void
    {
        $product = Product::factory()->create([
            'tenant_id'   => $this->tenant->id,
            'track_stock' => true,
        ]);
        $product->outlets()->attach($this->outlet->id, [
            'stock'               => 10,
            'low_stock_threshold' => 2,
        ]);

        // Should not throw
        $this->service->validateStockForItems(
            [['product_id' => $product->id, 'quantity' => 5]],
            $this->outlet->id
        );

        $this->assertTrue(true); // reached without exception
    }

    public function test_validate_stock_throws_when_insufficient_stock(): void
    {
        $this->expectException(ValidationException::class);

        $product = Product::factory()->create([
            'tenant_id'   => $this->tenant->id,
            'track_stock' => true,
        ]);
        $product->outlets()->attach($this->outlet->id, [
            'stock'               => 3,
            'low_stock_threshold' => 1,
        ]);

        $this->service->validateStockForItems(
            [['product_id' => $product->id, 'quantity' => 10]],
            $this->outlet->id
        );
    }

    public function test_validate_stock_throws_when_product_not_found(): void
    {
        $this->expectException(ValidationException::class);

        $this->service->validateStockForItems(
            [['product_id' => 999999, 'quantity' => 1]],
            $this->outlet->id
        );
    }

    public function test_validate_stock_skips_non_tracked_products(): void
    {
        $product = Product::factory()->withoutStockTracking()->create([
            'tenant_id' => $this->tenant->id,
        ]);
        // No pivot entry — if stock-tracking applied it would fail

        // Should not throw
        $this->service->validateStockForItems(
            [['product_id' => $product->id, 'quantity' => 1000]],
            $this->outlet->id
        );

        $this->assertTrue(true);
    }

    public function test_validate_stock_throws_for_multiple_errors(): void
    {
        $product1 = Product::factory()->create([
            'tenant_id'   => $this->tenant->id,
            'track_stock' => true,
        ]);
        $product2 = Product::factory()->create([
            'tenant_id'   => $this->tenant->id,
            'track_stock' => true,
        ]);

        $product1->outlets()->attach($this->outlet->id, ['stock' => 1, 'low_stock_threshold' => 0]);
        $product2->outlets()->attach($this->outlet->id, ['stock' => 2, 'low_stock_threshold' => 0]);

        try {
            $this->service->validateStockForItems(
                [
                    ['product_id' => $product1->id, 'quantity' => 5],
                    ['product_id' => $product2->id, 'quantity' => 5],
                ],
                $this->outlet->id
            );

            $this->fail('Expected ValidationException not thrown.');
        } catch (ValidationException $e) {
            $this->assertCount(2, $e->errors());
        }
    }

    // ── deductStockForOrder ──────────────────────────────────────────────────

    public function test_deduct_stock_reduces_pivot_stock(): void
    {
        $product = Product::factory()->create([
            'tenant_id'   => $this->tenant->id,
            'track_stock' => true,
        ]);
        $product->outlets()->attach($this->outlet->id, ['stock' => 20, 'low_stock_threshold' => 2]);

        $order = \App\Models\Order::factory()->create([
            'tenant_id' => $this->tenant->id,
            'outlet_id' => $this->outlet->id,
            'user_id'   => $this->user->id,
        ]);
        \App\Models\OrderItem::factory()->create([
            'order_id'   => $order->id,
            'product_id' => $product->id,
            'quantity'   => 5,
        ]);

        $this->service->deductStockForOrder($order->load('items'));

        $stockAfter = $product->fresh()->getStockForOutlet($this->outlet->id);
        $this->assertEquals(15.0, $stockAfter);
    }

    public function test_deduct_stock_records_stock_movement(): void
    {
        $product = Product::factory()->create([
            'tenant_id'   => $this->tenant->id,
            'track_stock' => true,
        ]);
        $product->outlets()->attach($this->outlet->id, ['stock' => 10, 'low_stock_threshold' => 2]);

        $order = \App\Models\Order::factory()->create([
            'tenant_id' => $this->tenant->id,
            'outlet_id' => $this->outlet->id,
            'user_id'   => $this->user->id,
        ]);
        \App\Models\OrderItem::factory()->create([
            'order_id'   => $order->id,
            'product_id' => $product->id,
            'quantity'   => 3,
        ]);

        $this->service->deductStockForOrder($order->load('items'));

        $this->assertDatabaseHas('stock_movements', [
            'product_id'      => $product->id,
            'outlet_id'       => $this->outlet->id,
            'type'            => 'sale',
            'quantity_before' => 10,
            'quantity_change' => 3,
            'quantity_after'  => 7,
        ]);
    }

    // ── restoreStockForOrder ─────────────────────────────────────────────────

    public function test_restore_stock_increases_pivot_stock(): void
    {
        $product = Product::factory()->create([
            'tenant_id'   => $this->tenant->id,
            'track_stock' => true,
        ]);
        $product->outlets()->attach($this->outlet->id, ['stock' => 5, 'low_stock_threshold' => 2]);

        $order = \App\Models\Order::factory()->create([
            'tenant_id' => $this->tenant->id,
            'outlet_id' => $this->outlet->id,
            'user_id'   => $this->user->id,
        ]);
        \App\Models\OrderItem::factory()->create([
            'order_id'   => $order->id,
            'product_id' => $product->id,
            'quantity'   => 3,
        ]);

        $this->service->restoreStockForOrder($order->load('items'));

        $stockAfter = $product->fresh()->getStockForOutlet($this->outlet->id);
        $this->assertEquals(8.0, $stockAfter);
    }

    public function test_restore_stock_records_return_movement(): void
    {
        $product = Product::factory()->create([
            'tenant_id'   => $this->tenant->id,
            'track_stock' => true,
        ]);
        $product->outlets()->attach($this->outlet->id, ['stock' => 5, 'low_stock_threshold' => 2]);

        $order = \App\Models\Order::factory()->create([
            'tenant_id' => $this->tenant->id,
            'outlet_id' => $this->outlet->id,
            'user_id'   => $this->user->id,
        ]);
        \App\Models\OrderItem::factory()->create([
            'order_id'   => $order->id,
            'product_id' => $product->id,
            'quantity'   => 2,
        ]);

        $this->service->restoreStockForOrder($order->load('items'));

        $this->assertDatabaseHas('stock_movements', [
            'product_id'      => $product->id,
            'type'            => 'return',
            'quantity_before' => 5,
            'quantity_change' => 2,
            'quantity_after'  => 7,
        ]);
    }

    // ── getLowStockProducts ──────────────────────────────────────────────────

    public function test_get_low_stock_products_returns_products_at_or_below_threshold(): void
    {
        $lowProduct = Product::factory()->create([
            'tenant_id'   => $this->tenant->id,
            'track_stock' => true,
            'is_active'   => true,
        ]);
        $lowProduct->outlets()->attach($this->outlet->id, [
            'stock'               => 2,
            'low_stock_threshold' => 5,
        ]);

        $okProduct = Product::factory()->create([
            'tenant_id'   => $this->tenant->id,
            'track_stock' => true,
            'is_active'   => true,
        ]);
        $okProduct->outlets()->attach($this->outlet->id, [
            'stock'               => 20,
            'low_stock_threshold' => 5,
        ]);

        $results = $this->service->getLowStockProducts($this->outlet->id);

        $this->assertCount(1, $results);
        $this->assertEquals($lowProduct->id, $results->first()->id);
    }

    public function test_get_low_stock_products_excludes_non_tracked(): void
    {
        $untracked = Product::factory()->withoutStockTracking()->create([
            'tenant_id' => $this->tenant->id,
            'is_active' => true,
        ]);
        $untracked->outlets()->attach($this->outlet->id, [
            'stock'               => 0,
            'low_stock_threshold' => 5,
        ]);

        $results = $this->service->getLowStockProducts($this->outlet->id);

        $this->assertCount(0, $results);
    }
}
