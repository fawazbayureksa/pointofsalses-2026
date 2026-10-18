<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class SupplierReturn extends Model
{
    use BelongsToTenant, LogsActivity;

    protected $fillable = [
        'tenant_id',
        'supplier_id',
        'outlet_id',
        'purchase_order_id',
        'goods_receipt_id',
        'supplier_invoice_id',
        'return_number',
        'return_date',
        'total_refund_amount',
        'resolution_type',
        'status',
        'reason',
        'created_by',
    ];

    protected $casts = [
        'return_date' => 'date',
        'total_refund_amount' => 'decimal:2',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->setDescriptionForEvent(fn (string $e) => "Supplier Return {$this->return_number} was {$e}");
    }

    public static function generateReturnNumber(?int $tenantId = null): string
    {
        $prefix = 'SRET-'.now()->format('Ym').'-';
        $query = static::withoutGlobalScopes()->where('return_number', 'like', $prefix.'%');
        if ($tenantId !== null) {
            $query->where('tenant_id', $tenantId);
        }

        $count = $query->count() + 1;

        do {
            $number = $prefix.str_pad((string) $count, 4, '0', STR_PAD_LEFT);
            $exists = static::withoutGlobalScopes()
                ->where('tenant_id', $tenantId)
                ->where('return_number', $number)
                ->exists();
            $count++;
        } while ($exists);

        return $number;
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class)->withTrashed();
    }

    public function outlet(): BelongsTo
    {
        return $this->belongsTo(Outlet::class);
    }

    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class)->withTrashed();
    }

    public function goodsReceipt(): BelongsTo
    {
        return $this->belongsTo(GoodsReceipt::class);
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(SupplierInvoice::class, 'supplier_invoice_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(SupplierReturnItem::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getResolutionLabelAttribute(): string
    {
        return match ($this->resolution_type) {
            'deduct_payable' => 'Deduct Invoice Payable (Credit Note)',
            'replacement' => 'Goods Replacement',
            'cash_refund' => 'Cash / Bank Refund',
            default => ucfirst(str_replace('_', ' ', $this->resolution_type)),
        };
    }
}
