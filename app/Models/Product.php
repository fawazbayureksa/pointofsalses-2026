<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Product extends Model
{
    use BelongsToTenant, HasFactory, SoftDeletes, LogsActivity;

    protected $fillable = [
        'tenant_id',
        'category_id',
        'name',
        'sku',
        'barcode',
        'description',
        'price',
        'cost_price',
        'unit',
        'is_active',
        'track_stock',
        'image',
    ];

    protected $casts = [
        'price'       => 'decimal:2',
        'cost_price'  => 'decimal:2',
        'is_active'   => 'boolean',
        'track_stock' => 'boolean',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->setDescriptionForEvent(fn(string $e) => "Product {$this->name} was {$e}");
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function outlets(): BelongsToMany
    {
        return $this->belongsToMany(Outlet::class, 'product_outlet')
                    ->withPivot(['stock', 'low_stock_threshold'])
                    ->withTimestamps();
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function getStockForOutlet(int $outletId): float
    {
        $pivot = $this->outlets()->wherePivot('outlet_id', $outletId)->first();
        return $pivot ? (float) $pivot->pivot->stock : 0.0;
    }
}
