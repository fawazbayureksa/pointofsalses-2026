<?php

namespace App\Services;

use App\Events\StockUpdated;
use App\Models\Order;
use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Validation\ValidationException;

class InventoryService
{
    /**
     * Validate that all items have sufficient stock at the given outlet.
     *
     * @throws ValidationException
     */
    public function validateStockForItems(array $items, int $outletId): void
    {
        $errors = [];

        foreach ($items as $item) {
            $product = Product::find($item['product_id']);

            if (! $product) {
                $errors["items.{$item['product_id']}"] = "Product ID {$item['product_id']} not found.";
                continue;
            }

            if ($product->track_stock) {
                $pivot = $product->outlets()->wherePivot('outlet_id', $outletId)->first();
                $stock = $pivot ? (float) $pivot->pivot->stock : 0.0;

                if ($stock < $item['quantity']) {
                    $errors["items.{$item['product_id']}"] = "Insufficient stock for '{$product->name}'. Available: {$stock}";
                }
            }
        }

        if (! empty($errors)) {
            throw ValidationException::withMessages($errors);
        }
    }

    /**
     * Deduct stock for all items in an order from the order's outlet.
     */
    public function deductStockForOrder(Order $order): void
    {
        foreach ($order->items as $item) {
            $product = $item->product;

            if ($product && $product->track_stock) {
                $pivot = $product->outlets()->wherePivot('outlet_id', $order->outlet_id)->first();

                if ($pivot) {
                    $before = (float) $pivot->pivot->stock;
                    $after  = max(0, $before - (float) $item->quantity);

                    $product->outlets()->updateExistingPivot($order->outlet_id, ['stock' => $after]);

                    StockMovement::record($product, $order->outlet_id, 'sale', $before, $before - $after, $after, null, $order);

                    event(new StockUpdated($product, $before, $after));
                }
            }
        }
    }

    /**
     * Restore stock when an order is cancelled.
     */
    public function restoreStockForOrder(Order $order): void
    {
        foreach ($order->items as $item) {
            $product = $item->product;

            if ($product && $product->track_stock) {
                $pivot = $product->outlets()->wherePivot('outlet_id', $order->outlet_id)->first();

                if ($pivot) {
                    $before = (float) $pivot->pivot->stock;
                    $after  = $before + (float) $item->quantity;

                    $product->outlets()->updateExistingPivot($order->outlet_id, ['stock' => $after]);

                    StockMovement::record($product, $order->outlet_id, 'return', $before, $after - $before, $after, null, $order);

                    event(new StockUpdated($product, $before, $after, 'restored'));
                }
            }
        }
    }

    /**
     * Get products at/below low-stock threshold for an outlet.
     */
    public function getLowStockProducts(int $outletId): \Illuminate\Support\Collection
    {
        return Product::where('track_stock', true)
            ->where('is_active', true)
            ->with(['outlets' => fn($q) => $q->wherePivot('outlet_id', $outletId)])
            ->get()
            ->filter(function (Product $product) use ($outletId) {
                $pivot = $product->outlets->firstWhere('id', $outletId);
                if (! $pivot) return false;
                return $pivot->pivot->stock <= $pivot->pivot->low_stock_threshold;
            });
    }
}
