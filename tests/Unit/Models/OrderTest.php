<?php

namespace Tests\Unit\Models;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Outlet;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderTest extends TestCase
{
    use RefreshDatabase;

    public function test_is_paid_returns_true_when_payment_status_is_paid(): void
    {
        $order                 = new Order();
        $order->payment_status = 'paid';

        $this->assertTrue($order->isPaid());
    }

    public function test_is_paid_returns_false_when_payment_status_is_unpaid(): void
    {
        $order                 = new Order();
        $order->payment_status = 'unpaid';

        $this->assertFalse($order->isPaid());
    }

    public function test_is_completed_returns_true_when_status_is_completed(): void
    {
        $order         = new Order();
        $order->status = 'completed';

        $this->assertTrue($order->isCompleted());
    }

    public function test_is_completed_returns_false_when_status_is_pending(): void
    {
        $order         = new Order();
        $order->status = 'pending';

        $this->assertFalse($order->isCompleted());
    }

    public function test_recalculate_totals_sums_item_subtotals(): void
    {
        $tenant = Tenant::factory()->create();
        $user   = User::factory()->create(['tenant_id' => $tenant->id]);
        $outlet = Outlet::factory()->create(['tenant_id' => $tenant->id]);

        $this->actingAs($user);

        $order = Order::factory()->create([
            'tenant_id' => $tenant->id,
            'outlet_id' => $outlet->id,
            'user_id'   => $user->id,
        ]);

        // Create two items
        OrderItem::factory()->create([
            'order_id'        => $order->id,
            'subtotal'        => 30000,
            'tax_amount'      => 0,
            'discount_amount' => 0,
        ]);
        OrderItem::factory()->create([
            'order_id'        => $order->id,
            'subtotal'        => 20000,
            'tax_amount'      => 0,
            'discount_amount' => 0,
        ]);

        $order->recalculateTotals();
        $order->refresh();

        $this->assertEquals('50000.00', $order->total_amount);
        $this->assertEquals('50000.00', $order->subtotal);
    }

    public function test_recalculate_totals_accounts_for_discounts(): void
    {
        $tenant = Tenant::factory()->create();
        $user   = User::factory()->create(['tenant_id' => $tenant->id]);
        $outlet = Outlet::factory()->create(['tenant_id' => $tenant->id]);

        $this->actingAs($user);

        $order = Order::factory()->create([
            'tenant_id' => $tenant->id,
            'outlet_id' => $outlet->id,
            'user_id'   => $user->id,
        ]);

        // subtotal 50000 − discount 5000 = 45000 net
        OrderItem::factory()->create([
            'order_id'        => $order->id,
            'subtotal'        => 45000, // already reflects the discount
            'tax_amount'      => 0,
            'discount_amount' => 5000,
        ]);

        $order->recalculateTotals();
        $order->refresh();

        // total = subtotal + tax - discount = 45000 + 0 - 5000 = 40000
        $this->assertEquals('40000.00', $order->total_amount);
        $this->assertEquals('5000.00', $order->discount_amount);
    }
}
