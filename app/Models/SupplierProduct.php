<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SupplierProduct extends Model
{
    use BelongsToTenant, HasFactory;

    protected $fillable = [
        'tenant_id',
        'supplier_id',
        'product_id',
        'supplier_sku',
        'purchase_price',
        'is_primary',
        'min_order_qty',
        'lead_time_days',
        'purchase_unit',
        'conversion_factor',
        'notes',
    ];

    protected $casts = [
        'purchase_price' => 'decimal:2',
        'is_primary' => 'boolean',
        'min_order_qty' => 'float',
        'lead_time_days' => 'integer',
        'conversion_factor' => 'float',
    ];

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class)->withTrashed();
    }

    public function getUnitCostPerStockUnitAttribute(): float
    {
        $factor = max(0.001, (float) $this->conversion_factor);

        return round(((float) $this->purchase_price) / $factor, 2);
    }
}
