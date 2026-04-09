<?php

namespace Tests\Unit\Services;

use App\Models\Order;
use App\Models\Outlet;
use App\Models\Payment;
use App\Models\Tenant;
use App\Models\User;
use App\Services\PaymentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class PaymentServiceTest extends TestCase
{
    use RefreshDatabase;

    private PaymentService $service;
    private Tenant $tenant;
    private User $user;
    private Outlet $outlet;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = app(PaymentService::class);
        $this->tenant  = Tenant::factory()->create();
        $this->user    = User::factory()->create(['tenant_id' => $this->tenant->id]);
        $this->outlet  = Outlet::factory()->create(['tenant_id' => $this->tenant->id]);

        $this->actingAs($this->user);
    }

    private function makeOrder(array $attributes = []): Order
    {
        return Order::factory()->create(array_merge([
            'tenant_id'      => $this->tenant->id,
            'outlet_id'      => $this->outlet->id,
            'user_id'        => $this->user->id,
            'total_amount'   => 100000,
            'status'         => 'pending',
            'payment_status' => 'unpaid',
        ], $attributes));
    }

    // ── pay ──────────────────────────────────────────────────────────────────

    public function test_pay_creates_payment_record(): void
    {
        $order   = $this->makeOrder();
        $payment = $this->service->pay($order, [
            'payment_method' => 'cash',
            'amount'         => 100000,
        ]);

        $this->assertDatabaseHas('payments', [
            'order_id'       => $order->id,
            'payment_method' => 'cash',
            'amount'         => 100000,
            'status'         => 'completed',
        ]);

        $this->assertInstanceOf(Payment::class, $payment);
    }

    public function test_pay_marks_order_as_paid_and_completed(): void
    {
        $order = $this->makeOrder();

        $this->service->pay($order, [
            'payment_method' => 'cash',
            'amount'         => 100000,
        ]);

        $order->refresh();
        $this->assertEquals('paid', $order->payment_status);
        $this->assertEquals('completed', $order->status);
    }

    public function test_pay_calculates_change_when_overpaid(): void
    {
        $order   = $this->makeOrder(['total_amount' => 75000]);
        $payment = $this->service->pay($order, [
            'payment_method' => 'cash',
            'amount'         => 100000,
        ]);

        $this->assertEquals('25000.00', $payment->change_amount);
    }

    public function test_pay_calculates_zero_change_when_exact_amount(): void
    {
        $order   = $this->makeOrder(['total_amount' => 50000]);
        $payment = $this->service->pay($order, [
            'payment_method' => 'cash',
            'amount'         => 50000,
        ]);

        $this->assertEquals('0.00', $payment->change_amount);
    }

    public function test_pay_stores_reference_number(): void
    {
        $order   = $this->makeOrder();
        $payment = $this->service->pay($order, [
            'payment_method'   => 'card',
            'amount'           => 100000,
            'reference_number' => 'REF-12345',
        ]);

        $this->assertEquals('REF-12345', $payment->reference_number);
    }

    public function test_pay_throws_when_order_is_already_paid(): void
    {
        $this->expectException(ValidationException::class);

        $order = $this->makeOrder(['payment_status' => 'paid']);
        $this->service->pay($order, [
            'payment_method' => 'cash',
            'amount'         => 100000,
        ]);
    }

    public function test_pay_throws_when_order_is_cancelled(): void
    {
        $this->expectException(ValidationException::class);

        $order = $this->makeOrder(['status' => 'cancelled']);
        $this->service->pay($order, [
            'payment_method' => 'cash',
            'amount'         => 100000,
        ]);
    }

    public function test_pay_throws_when_amount_is_less_than_outstanding(): void
    {
        $this->expectException(ValidationException::class);

        $order = $this->makeOrder(['total_amount' => 100000]);
        $this->service->pay($order, [
            'payment_method' => 'cash',
            'amount'         => 50000,
        ]);
    }

    // ── refund ───────────────────────────────────────────────────────────────

    public function test_refund_marks_payment_as_refunded(): void
    {
        $order   = $this->makeOrder(['payment_status' => 'paid', 'status' => 'completed']);
        $payment = Payment::factory()->completed()->create([
            'order_id' => $order->id,
            'amount'   => 100000,
        ]);

        $refunded = $this->service->refund($payment, 'Customer returned item');

        $this->assertEquals('refunded', $refunded->status);
    }

    public function test_refund_updates_order_status(): void
    {
        $order   = $this->makeOrder(['payment_status' => 'paid', 'status' => 'completed']);
        $payment = Payment::factory()->completed()->create([
            'order_id' => $order->id,
            'amount'   => 100000,
        ]);

        $this->service->refund($payment);

        $order->refresh();
        $this->assertEquals('refunded', $order->payment_status);
        $this->assertEquals('refunded', $order->status);
    }
}
