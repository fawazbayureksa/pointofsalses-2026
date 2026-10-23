<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class SupplierInvoice extends Model
{
    use BelongsToTenant, LogsActivity;

    public const STATUS_UNPAID = 'unpaid';

    public const STATUS_PARTIALLY_PAID = 'partially_paid';

    public const STATUS_PAID = 'paid';

    public const STATUS_OVERDUE = 'overdue';

    public const STATUS_CANCELLED = 'cancelled';

    protected $fillable = [
        'tenant_id',
        'supplier_id',
        'purchase_order_id',
        'goods_receipt_id',
        'invoice_number',
        'supplier_invoice_number',
        'invoice_date',
        'due_date',
        'payment_terms',
        'subtotal',
        'tax_amount',
        'discount_amount',
        'shipping_cost',
        'total_amount',
        'amount_paid',
        'return_deduction',
        'outstanding_balance',
        'matching_status',
        'status',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'invoice_date' => 'date',
        'due_date' => 'date',
        'payment_terms' => 'integer',
        'subtotal' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'shipping_cost' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'amount_paid' => 'decimal:2',
        'return_deduction' => 'decimal:2',
        'outstanding_balance' => 'decimal:2',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->setDescriptionForEvent(fn (string $e) => "Supplier Invoice {$this->invoice_number} was {$e}");
    }

    public static function generateInvoiceNumber(?int $tenantId = null): string
    {
        $prefix = 'SINV-'.now()->format('Ym').'-';
        $query = static::withoutGlobalScopes()->where('invoice_number', 'like', $prefix.'%');
        if ($tenantId !== null) {
            $query->where('tenant_id', $tenantId);
        }

        $count = $query->count() + 1;

        do {
            $number = $prefix.str_pad((string) $count, 4, '0', STR_PAD_LEFT);
            $exists = static::withoutGlobalScopes()
                ->where('tenant_id', $tenantId)
                ->where('invoice_number', $number)
                ->exists();
            $count++;
        } while ($exists);

        return $number;
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class)->withTrashed();
    }

    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class)->withTrashed();
    }

    public function goodsReceipt(): BelongsTo
    {
        return $this->belongsTo(GoodsReceipt::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(SupplierPayment::class);
    }

    public function returns(): HasMany
    {
        return $this->hasMany(SupplierReturn::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function recalculateBalance(): void
    {
        if ($this->status === self::STATUS_CANCELLED) {
            return;
        }

        $paid = (float) $this->payments()->sum('amount');
        $deduction = (float) $this->returns()
            ->where('status', 'completed')
            ->where('resolution_type', 'deduct_payable')
            ->sum('total_refund_amount');

        $total = (float) $this->total_amount;
        $outstanding = max(0.0, round($total - $paid - $deduction, 2));

        if ($outstanding <= 0.0) {
            $status = self::STATUS_PAID;
        } elseif ($paid > 0 || $deduction > 0) {
            $status = ($this->due_date && $this->due_date->isPast())
                ? self::STATUS_OVERDUE
                : self::STATUS_PARTIALLY_PAID;
        } else {
            $status = ($this->due_date && $this->due_date->isPast())
                ? self::STATUS_OVERDUE
                : self::STATUS_UNPAID;
        }

        $this->update([
            'amount_paid' => $paid,
            'return_deduction' => $deduction,
            'outstanding_balance' => $outstanding,
            'status' => $status,
        ]);
    }

    public function isOverdue(): bool
    {
        return (float) $this->outstanding_balance > 0
            && $this->due_date !== null
            && $this->due_date->isPast();
    }

    public function getStatusBadgeClassAttribute(): string
    {
        if ($this->isOverdue() && $this->status !== self::STATUS_PAID) {
            return 'bg-red-100 text-red-800';
        }

        return match ($this->status) {
            self::STATUS_PAID => 'bg-emerald-100 text-emerald-800',
            self::STATUS_PARTIALLY_PAID => 'bg-blue-100 text-blue-800',
            self::STATUS_OVERDUE => 'bg-red-100 text-red-800',
            self::STATUS_CANCELLED => 'bg-gray-100 text-gray-800',
            default => 'bg-amber-100 text-amber-800',
        };
    }

    public function getEffectiveStatusLabelAttribute(): string
    {
        if ($this->isOverdue() && $this->status !== self::STATUS_PAID) {
            return 'Overdue';
        }

        return match ($this->status) {
            self::STATUS_PAID => 'Paid',
            self::STATUS_PARTIALLY_PAID => 'Partially Paid',
            self::STATUS_OVERDUE => 'Overdue',
            self::STATUS_CANCELLED => 'Cancelled',
            default => 'Unpaid',
        };
    }
}
