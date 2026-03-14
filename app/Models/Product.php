<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Product extends Model
{
    use SoftDeletes, LogsActivity;

    protected $fillable = [
        'tenant_id',
        'outlet_id',
        'name',
        'sku',
        'barcode',
        'description',
        'category',
        'price',
        'cost_price',
        'stock',
        'low_stock_threshold',
        'unit',
        'is_active',
        'track_stock',
        'image',
    ];

    protected $casts = [
        'price'               => 'decimal:2',
        'cost_price'          => 'decimal:2',
        'stock'               => 'decimal:3',
        'low_stock_threshold' => 'decimal:3',
        'is_active'           => 'boolean',
        'track_stock'         => 'boolean',
    ];

    // -------------------------------------------------------------------------
    // Activity Log
    // -------------------------------------------------------------------------

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->setDescriptionForEvent(fn(string $e) => "Product {$this->name} was {$e}");
    }

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    public function outlet(): BelongsTo
    {
        return $this->belongsTo(Outlet::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    // -------------------------------------------------------------------------
    // Scopes
    // -------------------------------------------------------------------------

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeLowStock($query)
    {
        return $query->where('track_stock', true)
            ->whereColumn('stock', '<=', 'low_stock_threshold');
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    public function isLowStock(): bool
    {
        return $this->track_stock && $this->stock <= $this->low_stock_threshold;
    }

    public function decrementStock(float $quantity): void
    {
        $this->decrement('stock', $quantity);
    }

    public function incrementStock(float $quantity): void
    {
        $this->increment('stock', $quantity);
    }
}
