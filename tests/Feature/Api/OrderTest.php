<?php

namespace Tests\Feature\Api;

use App\Models\Order;
use App\Models\Outlet;
use App\Models\Product;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderTest extends TestCase
{
    use RefreshDatabase;

    private Tenant $tenant;
    private User $user;
    private Outlet $outlet;
    private Product $product;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = Tenant::factory()->create();
        $this->user   = User::factory()->create(['tenant_id' => $this->tenant->id]);
        $this->outlet = Outlet::factory()->create(['tenant_id' => $this->tenant->id]);

        $this->product = Product::factory()->create([
            'tenant_id'   => $this->tenant->id,
            'price'       => 50000,
            'track_stock' => true,
        ]);

        // Attach product to outlet with sufficient stock
        $this->product->outlets()->attach($this->outlet->id, [
            'stock'               => 100,
            'low_stock_threshold' => 5,
        ]);
    }

    public function test_unauthenticated_user_cannot_list_orders(): void
    {
        $this->getJson('/api/orders')->assertStatus(401);
    }

    public function test_can_list_orders(): void
    {
        Order::factory()->count(3)->create([
            'tenant_id' => $this->tenant->id,
            'outlet_id' => $this->outlet->id,
            'user_id'   => $this->user->id,
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/orders');

        $response->assertStatus(200)
            ->assertJsonStructure(['data', 'total', 'per_page', 'current_page']);
    }

    public function test_can_filter_orders_by_status(): void
    {
        Order::factory()->create([
            'tenant_id' => $this->tenant->id,
            'outlet_id' => $this->outlet->id,
            'user_id'   => $this->user->id,
            'status'    => 'pending',
        ]);
        Order::factory()->create([
            'tenant_id' => $this->tenant->id,
            'outlet_id' => $this->outlet->id,
            'user_id'   => $this->user->id,
            'status'    => 'completed',
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/orders?status=pending');

        $response->assertStatus(200)
            ->assertJsonPath('total', 1)
            ->assertJsonPath('data.0.status', 'pending');
    }

    public function test_can_filter_orders_by_outlet(): void
    {
        $otherOutlet = Outlet::factory()->create(['tenant_id' => $this->tenant->id]);

        Order::factory()->create([
            'tenant_id' => $this->tenant->id,
            'outlet_id' => $this->outlet->id,
            'user_id'   => $this->user->id,
        ]);
        Order::factory()->create([
            'tenant_id' => $this->tenant->id,
            'outlet_id' => $otherOutlet->id,
            'user_id'   => $this->user->id,
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson("/api/orders?outlet_id={$this->outlet->id}");

        $response->assertStatus(200)
            ->assertJsonPath('total', 1)
            ->assertJsonPath('data.0.outlet_id', $this->outlet->id);
    }

    public function test_can_create_order(): void
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/orders', [
                'outlet_id' => $this->outlet->id,
                'items'     => [
                    [
                        'product_id'      => $this->product->id,
                        'quantity'        => 2,
                        'discount_amount' => 0,
                    ],
                ],
            ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'id', 'order_number', 'status', 'total_amount', 'items',
            ])
            ->assertJsonFragment(['status' => 'pending']);

        $this->assertDatabaseHas('orders', [
            'outlet_id'  => $this->outlet->id,
            'user_id'    => $this->user->id,
            'tenant_id'  => $this->tenant->id,
            'status'     => 'pending',
        ]);
    }

    public function test_create_order_calculates_totals_correctly(): void
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/orders', [
                'outlet_id' => $this->outlet->id,
                'items'     => [
                    [
                        'product_id'      => $this->product->id,
                        'quantity'        => 2,
                        'discount_amount' => 5000,
                    ],
                ],
            ]);

        $response->assertStatus(201);
        // item subtotal = (2 × 50000) - 5000 = 95000
        // order total   = item_subtotal - order_discount = 95000 - 5000 = 90000
        $this->assertEquals('90000.00', $response->json('total_amount'));
    }

    public function test_create_order_deducts_stock(): void
    {
        $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/orders', [
                'outlet_id' => $this->outlet->id,
                'items'     => [
                    [
                        'product_id' => $this->product->id,
                        'quantity'   => 3,
                    ],
                ],
            ]);

        $this->product->refresh();
        $stockAfter = $this->product->getStockForOutlet($this->outlet->id);
        $this->assertEquals(97.0, $stockAfter);
    }

    public function test_create_order_validates_required_fields(): void
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/orders', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['outlet_id', 'items']);
    }

    public function test_create_order_validates_items_not_empty(): void
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/orders', [
                'outlet_id' => $this->outlet->id,
                'items'     => [],
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['items']);
    }

    public function test_create_order_fails_when_insufficient_stock(): void
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/orders', [
                'outlet_id' => $this->outlet->id,
                'items'     => [
                    [
                        'product_id' => $this->product->id,
                        'quantity'   => 999,
                    ],
                ],
            ]);

        $response->assertStatus(422);
    }

    public function test_can_get_order_details(): void
    {
        $order = Order::factory()->create([
            'tenant_id' => $this->tenant->id,
            'outlet_id' => $this->outlet->id,
            'user_id'   => $this->user->id,
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson("/api/orders/{$order->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'id', 'order_number', 'status', 'items', 'payments',
            ])
            ->assertJsonFragment(['id' => $order->id]);
    }

    public function test_can_pay_for_an_order(): void
    {
        $order = Order::factory()->create([
            'tenant_id'    => $this->tenant->id,
            'outlet_id'    => $this->outlet->id,
            'user_id'      => $this->user->id,
            'total_amount' => 100000,
            'status'       => 'pending',
            'payment_status' => 'unpaid',
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson("/api/orders/{$order->id}/pay", [
                'payment_method' => 'cash',
                'amount'         => 100000,
            ]);

        $response->assertStatus(200)
            ->assertJsonStructure(['id', 'payment_method', 'amount', 'status'])
            ->assertJsonFragment([
                'payment_method' => 'cash',
                'status'         => 'completed',
            ]);

        $this->assertDatabaseHas('orders', [
            'id'             => $order->id,
            'status'         => 'completed',
            'payment_status' => 'paid',
        ]);
    }

    public function test_pay_calculates_change_correctly(): void
    {
        $order = Order::factory()->create([
            'tenant_id'    => $this->tenant->id,
            'outlet_id'    => $this->outlet->id,
            'user_id'      => $this->user->id,
            'total_amount' => 75000,
            'status'       => 'pending',
            'payment_status' => 'unpaid',
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson("/api/orders/{$order->id}/pay", [
                'payment_method' => 'cash',
                'amount'         => 100000,
            ]);

        $response->assertStatus(200)
            ->assertJsonFragment(['change_amount' => '25000.00']);
    }

    public function test_cannot_pay_for_already_paid_order(): void
    {
        $order = Order::factory()->completed()->create([
            'tenant_id' => $this->tenant->id,
            'outlet_id' => $this->outlet->id,
            'user_id'   => $this->user->id,
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson("/api/orders/{$order->id}/pay", [
                'payment_method' => 'cash',
                'amount'         => 100000,
            ]);

        $response->assertStatus(422);
    }

    public function test_cannot_pay_for_cancelled_order(): void
    {
        $order = Order::factory()->cancelled()->create([
            'tenant_id' => $this->tenant->id,
            'outlet_id' => $this->outlet->id,
            'user_id'   => $this->user->id,
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson("/api/orders/{$order->id}/pay", [
                'payment_method' => 'cash',
                'amount'         => 100000,
            ]);

        $response->assertStatus(422);
    }

    public function test_pay_validates_payment_method(): void
    {
        $order = Order::factory()->create([
            'tenant_id' => $this->tenant->id,
            'outlet_id' => $this->outlet->id,
            'user_id'   => $this->user->id,
            'status'    => 'pending',
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson("/api/orders/{$order->id}/pay", [
                'payment_method' => 'invalid_method',
                'amount'         => 100000,
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['payment_method']);
    }

    public function test_can_cancel_an_order(): void
    {
        $order = Order::factory()->create([
            'tenant_id' => $this->tenant->id,
            'outlet_id' => $this->outlet->id,
            'user_id'   => $this->user->id,
            'status'    => 'pending',
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson("/api/orders/{$order->id}/cancel", [
                'reason' => 'Customer changed mind',
            ]);

        $response->assertStatus(200)
            ->assertJsonFragment(['status' => 'cancelled']);

        $this->assertDatabaseHas('orders', [
            'id'     => $order->id,
            'status' => 'cancelled',
        ]);
    }

    public function test_orders_are_scoped_to_authenticated_tenant(): void
    {
        $otherTenant = Tenant::factory()->create();
        $otherUser   = User::factory()->create(['tenant_id' => $otherTenant->id]);
        $otherOutlet = Outlet::factory()->create(['tenant_id' => $otherTenant->id]);

        // Another tenant's order
        Order::factory()->create([
            'tenant_id' => $otherTenant->id,
            'outlet_id' => $otherOutlet->id,
            'user_id'   => $otherUser->id,
        ]);

        // Current tenant's order
        Order::factory()->create([
            'tenant_id' => $this->tenant->id,
            'outlet_id' => $this->outlet->id,
            'user_id'   => $this->user->id,
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/orders');

        $response->assertStatus(200)
            ->assertJsonPath('total', 1);
    }
}
