<?php

namespace App\Services;

use App\Events\PaymentCompleted;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PaymentService
{
    public function __construct(
        private OrderService $orderService,
    ) {}

    /**
     * Process a payment for an order.
     *
     * $payload = [
     *   'payment_method'   => string,  // cash, card, qris, transfer
     *   'amount'           => float,
     *   'reference_number' => string|null,
     * ]
     */
    public function pay(Order $order, array $payload): Payment
    {
        return DB::transaction(function () use ($order, $payload) {
            $this->ensureOrderIsPayable($order);

            $totalPaid   = $order->payments()->where('status', 'completed')->sum('amount');
            $outstanding = $order->total_amount - $totalPaid;
            $amount      = (float) $payload['amount'];

            if ($amount < $outstanding) {
                throw ValidationException::withMessages([
                    'amount' => "Payment amount ({$amount}) is less than outstanding ({$outstanding}).",
                ]);
            }

            $payment = Payment::create([
                'order_id'         => $order->id,
                'payment_method'   => $payload['payment_method'],
                'amount'           => $amount,
                'change_amount'    => max(0, $amount - $outstanding),
                'status'           => 'completed',
                'reference_number' => $payload['reference_number'] ?? null,
                'paid_at'          => now(),
            ]);

            $order->update(['payment_status' => 'paid']);
            $this->orderService->complete($order);

            event(new PaymentCompleted($payment, $order));

            return $payment->load('order');
        });
    }

    public function refund(Payment $payment, string $reason = ''): Payment
    {
        DB::transaction(function () use ($payment, $reason) {
            $payment->update(['status' => 'refunded']);
            $payment->order->update([
                'payment_status' => 'refunded',
                'status'         => 'refunded',
            ]);

            activity()
                ->on($payment)
                ->withProperties(['reason' => $reason])
                ->log('Payment refunded');
        });

        return $payment->refresh();
    }

    // ─── Private helpers ─────────────────────────────────────────────────────

    private function ensureOrderIsPayable(Order $order): void
    {
        if ($order->isPaid()) {
            throw ValidationException::withMessages(['order' => 'This order has already been paid.']);
        }

        if ($order->status === 'cancelled') {
            throw ValidationException::withMessages(['order' => 'Cannot pay for a cancelled order.']);
        }
    }
}
