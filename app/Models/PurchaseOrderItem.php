<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PurchaseOrderItem extends Model
{
    protected $fillable = [
        'purchase_order_id',
        'product_id',
        'product_name',
        'product_sku',
        'purchase_unit',
        'conversion_factor',
        'quantity_ordered',
        'quantity_received',
        'unit_price',
        'discount_amount',
        'tax_amount',
        'subtotal',
        'notes',
    ];

    protected $casts = [
        'conversion_factor' => 'float',
        'quantity_ordered' => 'float',
        'quantity_received' => 'float',
        'unit_price' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'subtotal' => 'decimal:2',
    ];

    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class)->withTrashed();
    }

    public function goodsReceiptItems(): HasMany
    {
        return $this->hasMany(GoodsReceiptItem::class);
    }

    public function getRemainingQuantityAttribute(): float
    {
        return max(0.0, (float) $this->quantity_ordered - (float) $this->quantity_received);
    }
}
