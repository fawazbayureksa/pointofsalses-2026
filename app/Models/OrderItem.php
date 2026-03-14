<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    protected $fillable = [
        'tenant_id',
        'order_id',
        'product_id',
        'product_name',
        'unit_price',
        'quantity',
        'discount_amount',
        'tax_amount',
        'subtotal',
        'metadata',
    ];

    protected $casts = [
        'unit_price'      => 'decimal:2',
        'quantity'        => 'decimal:3',
        'discount_amount' => 'decimal:2',
        'tax_amount'      => 'decimal:2',
        'subtotal'        => 'decimal:2',
        'metadata'        => 'array',
    ];

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    public function calculateSubtotal(): float
    {
        return ($this->unit_price * $this->quantity) - $this->discount_amount + $this->tax_amount;
    }
}
