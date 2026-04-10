<?php

namespace App\Services;

use App\Events\OrderCreated;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class OrderService
{
    public function __construct(
        private InventoryService $inventoryService,
    ) {}

    /**
     * Create a new order with its items.
     *
     * $payload = [
     *   'outlet_id'               => int,
     *   'customer_id'             => int|null,
     *   'notes'                   => string|null,
     *   'discount_amount'         => float|null,   // order-level discount
     *   'discount_type'           => string|null,  // 'fixed' | 'percentage'
     *   'loyalty_points_redeemed' => int|null,     // points to redeem (1 pt = Rp 1)
     *   'items'                   => [
     *     ['product_id' => int, 'quantity' => float, 'discount_amount' => float],
     *     ...
     *   ],
     * ]
     */
    public function create(array $payload, int $userId): Order
    {
        return DB::transaction(function () use ($payload, $userId) {
            $this->inventoryService->validateStockForItems(
                $payload['items'],
                $payload['outlet_id']
            );

            $cashier = \App\Models\User::find($userId);

            $order = Order::create([
                'outlet_id'    => $payload['outlet_id'],
                'user_id'      => $userId,
                'cashier_name' => $cashier?->name,
                'customer_id'  => $payload['customer_id'] ?? null,
                'order_number' => $this->generateOrderNumber(),
                'status'       => 'pending',
                'notes'        => $payload['notes'] ?? null,
            ]);

            $this->attachItems($order, $payload['items']);
            $order->recalculateTotals();

            // Apply order-level discount (e.g. supervisor override)
            $orderDiscountAmount = (float) ($payload['discount_amount'] ?? 0);
            $orderDiscountType   = $payload['discount_type'] ?? 'fixed';

            if ($orderDiscountAmount > 0) {
                $this->applyOrderDiscount($order, $orderDiscountAmount, $orderDiscountType);
            }

            // Apply loyalty points redemption (1 point = Rp 1)
            $pointsRedeemed = (int) ($payload['loyalty_points_redeemed'] ?? 0);
            if ($pointsRedeemed > 0 && $order->customer_id) {
                $this->redeemLoyaltyPoints($order, $pointsRedeemed);
            }

            event(new OrderCreated($order));

            return $order->fresh(['items', 'customer', 'outlet']);
        });
    }

    public function cancel(Order $order, string $reason = ''): Order
    {
        DB::transaction(function () use ($order, $reason) {
            $this->inventoryService->restoreStockForOrder($order);

            $order->update([
                'status' => 'cancelled',
                'notes'  => $order->notes . ($reason ? " | Cancelled: {$reason}" : ''),
            ]);
        });

        return $order->refresh();
    }

    public function complete(Order $order): Order
    {
        $order->update([
            'status'       => 'completed',
            'completed_at' => now(),
        ]);

        // Award loyalty points: 1 pt per Rp 1,000 spent
        if ($order->customer_id) {
            $points = (int) floor($order->total_amount / 1000);
            if ($points > 0) {
                $order->customer()->increment('loyalty_points', $points);
            }
        }

        return $order->refresh();
    }

    /**
     * Apply (or replace) an order-level discount on a pending order.
     *
     * For a 'fixed' discount, $amount is subtracted directly from the total.
     * For a 'percentage' discount, $amount is treated as a percentage (0–100).
     * The existing item-level discounts are preserved; only the order-level
     * discount_amount / discount_type fields are overwritten.
     */
    public function applyOrderDiscount(Order $order, float $amount, string $type = 'fixed', ?int $supervisorId = null): Order
    {
        $order->refresh()->loadMissing('items');

        $itemsSubtotal = $order->items->sum('subtotal') + $order->items->sum('tax_amount');
        $itemDiscount  = $order->items->sum('discount_amount');

        if ($type === 'percentage') {
            $orderDiscount = round($itemsSubtotal * ($amount / 100), 2);
        } else {
            $orderDiscount = $amount;
        }

        $total = max(0, $itemsSubtotal - $itemDiscount - $orderDiscount);

        $updateData = [
            'discount_amount' => $itemDiscount + $orderDiscount,
            'discount_type'   => $type,
            'total_amount'    => $total,
        ];

        if ($supervisorId !== null) {
            $updateData['authorized_by'] = $supervisorId;
        }

        $order->update($updateData);

        return $order->refresh();
    }

    /**
     * Redeem loyalty points as a discount on a pending order.
     * 1 point = Rp 1. Points are deducted from the customer immediately.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function redeemLoyaltyPoints(Order $order, int $points): Order
    {
        $customer = $order->customer;

        if (! $customer) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'loyalty_points_redeemed' => 'Order has no associated customer.',
            ]);
        }

        if ($customer->loyalty_points < $points) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'loyalty_points_redeemed' => "Customer only has {$customer->loyalty_points} loyalty points available.",
            ]);
        }

        // Cap redemption at the order total
        $redemptionValue = min((float) $points, (float) $order->total_amount);
        // Only deduct the points that were actually applied as a discount
        $pointsDeducted = (int) ceil($redemptionValue);

        $order->update([
            'discount_amount' => $order->discount_amount + $redemptionValue,
            'total_amount'    => max(0, $order->total_amount - $redemptionValue),
            'notes'           => trim(($order->notes ?? '') . " | Loyalty: {$pointsDeducted} pts redeemed"),
        ]);

        $customer->decrement('loyalty_points', $pointsDeducted);

        return $order->refresh();
    }

    // ─── Private helpers ─────────────────────────────────────────────────────

    private function attachItems(Order $order, array $items): void
    {
        foreach ($items as $item) {
            $product = Product::findOrFail($item['product_id']);

            $unitPrice      = $product->price;
            $quantity       = (float) $item['quantity'];
            $discountAmount = (float) ($item['discount_amount'] ?? 0);
            $subtotal       = ($unitPrice * $quantity) - $discountAmount;

            OrderItem::create([
                'order_id'        => $order->id,
                'product_id'      => $product->id,
                'product_name'    => $product->name,
                'product_sku'     => $product->sku ?? '',
                'unit_price'      => $unitPrice,
                'cost_price'      => $product->cost_price,
                'quantity'        => $quantity,
                'discount_amount' => $discountAmount,
                'tax_amount'      => 0,
                'subtotal'        => $subtotal,
            ]);
        }
    }

    private function generateOrderNumber(): string
    {
        $tenantId = Auth::user()->tenant_id ?? 'SA';
        $prefix   = strtoupper(substr((string) $tenantId, 0, 3));
        $date     = now()->format('Ymd');
        $seq      = str_pad((string) (Order::whereDate('created_at', today())->count() + 1), 4, '0', STR_PAD_LEFT);

        return "{$prefix}-{$date}-{$seq}";
    }
}
