<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Facades\Auth;

class StockMovement extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'product_id',
        'outlet_id',
        'type',
        'quantity_before',
        'quantity_change',
        'quantity_after',
        'reason',
        'reference_type',
        'reference_id',
        'user_id',
    ];

    protected $casts = [
        'quantity_before' => 'float',
        'quantity_change' => 'float',
        'quantity_after'  => 'float',
    ];

    // ── Relationships ──────────────────────────────────────────────────────────

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class)->withTrashed();
    }

    public function outlet(): BelongsTo
    {
        return $this->belongsTo(Outlet::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function reference(): MorphTo
    {
        return $this->morphTo();
    }

    // ── Helpers ────────────────────────────────────────────────────────────────

    public static function record(
        Product $product,
        int $outletId,
        string $type,
        float $before,
        float $change,
        float $after,
        ?string $reason = null,
        ?Model $reference = null,
    ): self {
        return static::create([
            'product_id'      => $product->id,
            'outlet_id'       => $outletId,
            'type'            => $type,
            'quantity_before' => $before,
            'quantity_change' => $change,
            'quantity_after'  => $after,
            'reason'          => $reason,
            'reference_type'  => $reference ? get_class($reference) : null,
            'reference_id'    => $reference?->id,
            'user_id'         => Auth::user()?->id,
        ]);
    }

    // ── Type label helper ──────────────────────────────────────────────────────

    public function getTypeLabelAttribute(): string
    {
        return match ($this->type) {
            'adjustment'    => 'Adjustment',
            'sale'          => 'Sale',
            'return'        => 'Return',
            'transfer_in'   => 'Transfer In',
            'transfer_out'  => 'Transfer Out',
            default         => ucfirst($this->type),
        };
    }

    public function getTypeBadgeClassAttribute(): string
    {
        return match ($this->type) {
            'sale', 'transfer_out' => 'bg-red-100 text-red-800',
            'return', 'transfer_in' => 'bg-green-100 text-green-800',
            default => 'bg-blue-100 text-blue-800',
        };
    }
}
