<?php

namespace App\Services;

use App\Events\OrderCreated;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OrderService
{
    public function __construct(
        private readonly InventoryService $inventoryService,
    ) {}

    /**
     * Create a new order with its items.
     *
     * $payload = [
     *   'outlet_id'   => int,
     *   'customer_id' => int|null,
     *   'notes'       => string|null,
     *   'items'       => [
     *     ['product_id' => int, 'quantity' => float, 'discount_amount' => float],
     *     ...
     *   ],
     * ]
     */
    public function create(array $payload, int $userId): Order
    {
        return DB::transaction(function () use ($payload, $userId) {
            // Validate stock before creating
            $this->inventoryService->validateStockForItems($payload['items']);

            $order = Order::create([
                'tenant_id'    => tenant('id'),
                'outlet_id'    => $payload['outlet_id'],
                'user_id'      => $userId,
                'customer_id'  => $payload['customer_id'] ?? null,
                'order_number' => $this->generateOrderNumber(),
                'status'       => 'pending',
                'notes'        => $payload['notes'] ?? null,
            ]);

            $this->attachItems($order, $payload['items']);
            $order->recalculateTotals();

            event(new OrderCreated($order));

            return $order->fresh(['items', 'customer', 'outlet']);
        });
    }

    /**
     * Cancel an existing order and restore stock.
     */
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

    /**
     * Mark order as completed after successful payment.
     */
    public function complete(Order $order): Order
    {
        $order->update([
            'status'       => 'completed',
            'completed_at' => now(),
        ]);

        return $order->refresh();
    }

    // -------------------------------------------------------------------------
    // Private helpers
    // -------------------------------------------------------------------------

    private function attachItems(Order $order, array $items): void
    {
        foreach ($items as $item) {
            $product = Product::findOrFail($item['product_id']);

            $unitPrice      = $product->price;
            $quantity       = (float) $item['quantity'];
            $discountAmount = (float) ($item['discount_amount'] ?? 0);

            $subtotal = ($unitPrice * $quantity) - $discountAmount;

            OrderItem::create([
                'tenant_id'       => tenant('id'),
                'order_id'        => $order->id,
                'product_id'      => $product->id,
                'product_name'    => $product->name,
                'unit_price'      => $unitPrice,
                'quantity'        => $quantity,
                'discount_amount' => $discountAmount,
                'tax_amount'      => 0, // tax is calculated at order level
                'subtotal'        => $subtotal,
                'metadata'        => ['sku' => $product->sku, 'category' => $product->category],
            ]);
        }
    }

    private function generateOrderNumber(): string
    {
        $prefix = strtoupper(substr(tenant('id'), 0, 3));
        $date   = now()->format('Ymd');
        $seq    = str_pad((string) (Order::whereDate('created_at', today())->count() + 1), 4, '0', STR_PAD_LEFT);

        return "{$prefix}-{$date}-{$seq}";
    }
}
