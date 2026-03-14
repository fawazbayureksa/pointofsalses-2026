<?php

namespace Modules\Inventory\Entities;

use App\Models\Product;

/**
 * Inventory-domain Product entity.
 *
 * Extends the base Product model with stock management and
 * inventory-specific reporting logic.
 */
class InventoryProduct extends Product
{
    public function getTable(): string
    {
        return 'products';
    }

    /**
     * Calculate potential revenue based on current stock.
     */
    public function stockValue(): float
    {
        return round($this->stock * $this->cost_price, 2);
    }

    /**
     * Potential retail revenue of current stock.
     */
    public function retailStockValue(): float
    {
        return round($this->stock * $this->price, 2);
    }

    /**
     * Gross margin percentage.
     */
    public function marginPercent(): float
    {
        if ($this->price <= 0) {
            return 0.0;
        }

        return round((($this->price - $this->cost_price) / $this->price) * 100, 2);
    }
}
