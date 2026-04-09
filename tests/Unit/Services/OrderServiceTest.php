<?php

namespace Tests\Unit\Services;

use App\Models\Order;
use App\Models\Outlet;
use App\Models\Product;
use App\Models\Tenant;
use App\Models\User;
use App\Services\OrderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class OrderServiceTest extends TestCase
{
    use RefreshDatabase;

    private OrderService $service;
    private Tenant $tenant;
    private User $user;
    private Outlet $outlet;
    private Product $product;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = app(OrderService::class);
        $this->tenant  = Tenant::factory()->create();
        $this->user    = User::factory()->create(['tenant_id' => $this->tenant->id]);
        $this->outlet  = Outlet::factory()->create(['tenant_id' => $this->tenant->id]);
        $this->product = Product::factory()->create([
            'tenant_id'   => $this->tenant->id,
            'price'       => 20000,
            'cost_price'  => 10000,
            'track_stock' => true,
        ]);
        $this->product->outlets()->attach($this->outlet->id, [
            'stock'               => 50,
            'low_stock_threshold' => 5,
        ]);

        $this->actingAs($this->user);
    }

    // ── create ───────────────────────────────────────────────────────────────

    public function test_create_order_persists_order_and_items(): void
    {
        $order = $this->service->create([
            'outlet_id' => $this->outlet->id,
            'items'     => [
                ['product_id' => $this->product->id, 'quantity' => 2],
            ],
        ], $this->user->id);

        $this->assertDatabaseHas('orders', [
            'outlet_id'  => $this->outlet->id,
            'user_id'    => $this->user->id,
            'tenant_id'  => $this->tenant->id,
            'status'     => 'pending',
        ]);

        $this->assertDatabaseHas('order_items', [
            'order_id'   => $order->id,
            'product_id' => $this->product->id,
            'quantity'   => 2,
        ]);
    }

    public function test_create_order_calculates_total_amount(): void
    {
        $order = $this->service->create([
            'outlet_id' => $this->outlet->id,
            'items'     => [
                [
                    'product_id'      => $this->product->id,
                    'quantity'        => 3,
                    'discount_amount' => 5000,
                ],
            ],
        ], $this->user->id);

        // item subtotal = (3 × 20000) - 5000 = 55000
        // order total   = item_subtotal - order_discount = 55000 - 5000 = 50000
        $this->assertEquals('50000.00', $order->total_amount);
    }

    public function test_create_order_generates_unique_order_number(): void
    {
        $order1 = $this->service->create([
            'outlet_id' => $this->outlet->id,
            'items'     => [['product_id' => $this->product->id, 'quantity' => 1]],
        ], $this->user->id);

        $order2 = $this->service->create([
            'outlet_id' => $this->outlet->id,
            'items'     => [['product_id' => $this->product->id, 'quantity' => 1]],
        ], $this->user->id);

        $this->assertNotEquals($order1->order_number, $order2->order_number);
    }

    public function test_create_order_deducts_stock(): void
    {
        $this->service->create([
            'outlet_id' => $this->outlet->id,
            'items'     => [['product_id' => $this->product->id, 'quantity' => 4]],
        ], $this->user->id);

        $stockAfter = $this->product->fresh()->getStockForOutlet($this->outlet->id);
        $this->assertEquals(46.0, $stockAfter);
    }

    public function test_create_order_throws_when_insufficient_stock(): void
    {
        $this->expectException(ValidationException::class);

        $this->service->create([
            'outlet_id' => $this->outlet->id,
            'items'     => [['product_id' => $this->product->id, 'quantity' => 999]],
        ], $this->user->id);
    }

    public function test_create_order_accepts_optional_notes(): void
    {
        $order = $this->service->create([
            'outlet_id' => $this->outlet->id,
            'notes'     => 'No chilli please',
            'items'     => [['product_id' => $this->product->id, 'quantity' => 1]],
        ], $this->user->id);

        $this->assertEquals('No chilli please', $order->notes);
    }

    // ── cancel ───────────────────────────────────────────────────────────────

    public function test_cancel_order_sets_status_to_cancelled(): void
    {
        $order = Order::factory()->create([
            'tenant_id' => $this->tenant->id,
            'outlet_id' => $this->outlet->id,
            'user_id'   => $this->user->id,
            'status'    => 'pending',
        ]);

        $cancelled = $this->service->cancel($order, 'Test reason');

        $this->assertEquals('cancelled', $cancelled->status);
        $this->assertDatabaseHas('orders', ['id' => $order->id, 'status' => 'cancelled']);
    }

    public function test_cancel_order_appends_reason_to_notes(): void
    {
        $order = Order::factory()->create([
            'tenant_id' => $this->tenant->id,
            'outlet_id' => $this->outlet->id,
            'user_id'   => $this->user->id,
            'notes'     => 'Original note',
        ]);

        $cancelled = $this->service->cancel($order, 'Customer request');

        $this->assertStringContainsString('Customer request', $cancelled->notes);
    }

    public function test_cancel_order_without_reason_does_not_append_pipe(): void
    {
        $order = Order::factory()->create([
            'tenant_id' => $this->tenant->id,
            'outlet_id' => $this->outlet->id,
            'user_id'   => $this->user->id,
            'notes'     => null,
        ]);

        $cancelled = $this->service->cancel($order);

        $this->assertStringNotContainsString('|', $cancelled->notes ?? '');
    }

    // ── complete ─────────────────────────────────────────────────────────────

    public function test_complete_order_sets_status_to_completed(): void
    {
        $order = Order::factory()->create([
            'tenant_id' => $this->tenant->id,
            'outlet_id' => $this->outlet->id,
            'user_id'   => $this->user->id,
            'status'    => 'pending',
        ]);

        $completed = $this->service->complete($order);

        $this->assertEquals('completed', $completed->status);
        $this->assertNotNull($completed->completed_at);
    }
}
