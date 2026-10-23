<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SupplierReturnItem extends Model
{
    protected $fillable = [
        'supplier_return_id',
        'product_id',
        'quantity',
        'unit_cost',
        'subtotal',
        'deduct_stock',
        'condition_notes',
    ];

    protected $casts = [
        'quantity' => 'float',
        'unit_cost' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'deduct_stock' => 'boolean',
    ];

    public function supplierReturn(): BelongsTo
    {
        return $this->belongsTo(SupplierReturn::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class)->withTrashed();
    }
}
