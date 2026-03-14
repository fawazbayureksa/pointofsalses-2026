<?php

namespace App\Services;

use App\Events\StockUpdated;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Validation\ValidationException;

class InventoryService
{
    /**
     * Validate that all items in a cart have sufficient stock.
     *
     * @throws ValidationException
     */
    public function validateStockForItems(array $items): void
    {
        $errors = [];

        foreach ($items as $item) {
            $product = Product::find($item['product_id']);

            if (! $product) {
                $errors["items.{$item['product_id']}"] = "Product ID {$item['product_id']} not found.";
                continue;
            }

            if ($product->track_stock && $product->stock < $item['quantity']) {
                $errors["items.{$item['product_id']}"] = "Insufficient stock for '{$product->name}'. Available: {$product->stock}";
            }
        }

        if (! empty($errors)) {
            throw ValidationException::withMessages($errors);
        }
    }

    /**
     * Deduct stock for all items in an order.
     */
    public function deductStockForOrder(Order $order): void
    {
        foreach ($order->items as $item) {
            $product = $item->product;

            if ($product && $product->track_stock) {
                $before = $product->stock;
                $product->decrementStock($item->quantity);

                event(new StockUpdated($product, $before, $product->fresh()->stock));
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
                $before = $product->stock;
                $product->incrementStock($item->quantity);

                event(new StockUpdated($product, $before, $product->fresh()->stock, 'restored'));
            }
        }
    }

    /**
     * Manually adjust stock (e.g., stock-take, receiving).
     */
    public function adjustStock(Product $product, float $newQuantity, string $reason = ''): void
    {
        $before = $product->stock;

        $product->update(['stock' => $newQuantity]);

        event(new StockUpdated($product, $before, $newQuantity, 'adjustment', $reason));
    }

    /**
     * Get products that are at or below their low-stock threshold.
     */
    public function getLowStockProducts(): \Illuminate\Database\Eloquent\Collection
    {
        return Product::active()->lowStock()->get();
    }
}
