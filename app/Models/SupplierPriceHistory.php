<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SupplierPriceHistory extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'supplier_id',
        'product_id',
        'old_price',
        'new_price',
        'purchase_unit',
        'source',
        'changed_by',
        'notes',
        'recorded_at',
    ];

    protected $casts = [
        'old_price' => 'decimal:2',
        'new_price' => 'decimal:2',
        'recorded_at' => 'datetime',
    ];

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class)->withTrashed();
    }

    public function changedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }

    public function getPriceChangePercentAttribute(): ?float
    {
        if ($this->old_price === null || (float) $this->old_price <= 0) {
            return null;
        }

        return round(((((float) $this->new_price) - ((float) $this->old_price)) / ((float) $this->old_price)) * 100, 2);
    }
}
