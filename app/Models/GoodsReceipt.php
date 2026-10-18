<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class GoodsReceipt extends Model
{
    use BelongsToTenant, LogsActivity;

    protected $fillable = [
        'tenant_id',
        'purchase_order_id',
        'outlet_id',
        'supplier_id',
        'gr_number',
        'received_date',
        'supplier_delivery_note',
        'status',
        'is_late',
        'notes',
        'received_by',
    ];

    protected $casts = [
        'received_date' => 'date',
        'is_late' => 'boolean',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->setDescriptionForEvent(fn (string $e) => "Goods Receipt {$this->gr_number} was {$e}");
    }

    public static function generateGrNumber(?int $tenantId = null): string
    {
        $prefix = 'GR-'.now()->format('Ym').'-';
        $query = static::withoutGlobalScopes()->where('gr_number', 'like', $prefix.'%');
        if ($tenantId !== null) {
            $query->where('tenant_id', $tenantId);
        }

        $count = $query->count() + 1;

        do {
            $number = $prefix.str_pad((string) $count, 4, '0', STR_PAD_LEFT);
            $exists = static::withoutGlobalScopes()
                ->where('tenant_id', $tenantId)
                ->where('gr_number', $number)
                ->exists();
            $count++;
        } while ($exists);

        return $number;
    }

    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class)->withTrashed();
    }

    public function outlet(): BelongsTo
    {
        return $this->belongsTo(Outlet::class);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class)->withTrashed();
    }

    public function items(): HasMany
    {
        return $this->hasMany(GoodsReceiptItem::class);
    }

    public function receiver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'received_by');
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(SupplierInvoice::class);
    }

    public function getTotalReceivedValueAttribute(): float
    {
        return (float) $this->items->sum(fn ($i) => ((float) $i->quantity_received) * ((float) $i->unit_cost));
    }
}
