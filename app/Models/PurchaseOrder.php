<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class PurchaseOrder extends Model
{
    use BelongsToTenant, HasFactory, LogsActivity, SoftDeletes;

    public const STATUS_DRAFT = 'draft';

    public const STATUS_PENDING_APPROVAL = 'pending_approval';

    public const STATUS_APPROVED = 'approved';

    public const STATUS_ORDERED = 'ordered';

    public const STATUS_PARTIALLY_RECEIVED = 'partially_received';

    public const STATUS_RECEIVED = 'received';

    public const STATUS_CANCELLED = 'cancelled';

    protected $fillable = [
        'tenant_id',
        'outlet_id',
        'supplier_id',
        'po_number',
        'order_date',
        'expected_date',
        'status',
        'payment_terms',
        'subtotal',
        'discount_amount',
        'tax_rate',
        'tax_amount',
        'shipping_cost',
        'total_amount',
        'notes',
        'created_by',
        'approved_by',
        'approved_at',
        'ordered_at',
    ];

    protected $casts = [
        'order_date' => 'date',
        'expected_date' => 'date',
        'payment_terms' => 'integer',
        'subtotal' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'tax_rate' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'shipping_cost' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'approved_at' => 'datetime',
        'ordered_at' => 'datetime',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->setDescriptionForEvent(fn (string $e) => "Purchase Order {$this->po_number} was {$e}");
    }

    public static function generatePoNumber(?int $tenantId = null): string
    {
        $prefix = 'PO-'.now()->format('Ym').'-';
        $query = static::withoutGlobalScopes()->withTrashed()->where('po_number', 'like', $prefix.'%');
        if ($tenantId !== null) {
            $query->where('tenant_id', $tenantId);
        }

        $count = $query->count() + 1;

        do {
            $number = $prefix.str_pad((string) $count, 4, '0', STR_PAD_LEFT);
            $exists = static::withoutGlobalScopes()
                ->where('tenant_id', $tenantId)
                ->where('po_number', $number)
                ->exists();
            $count++;
        } while ($exists);

        return $number;
    }

    // ── Relationships ──────────────────────────────────────────────────────────

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
        return $this->hasMany(PurchaseOrderItem::class);
    }

    public function goodsReceipts(): HasMany
    {
        return $this->hasMany(GoodsReceipt::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(SupplierInvoice::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    // ── Helpers ────────────────────────────────────────────────────────────────

    public function recalculateTotals(): void
    {
        $subtotal = (float) $this->items()->sum('subtotal');
        $discount = (float) $this->discount_amount;
        $taxRate = (float) $this->tax_rate;
        $shipping = (float) $this->shipping_cost;

        $taxableAmount = max(0, $subtotal - $discount);
        $taxAmount = round($taxableAmount * ($taxRate / 100), 2);
        $totalAmount = round($taxableAmount + $taxAmount + $shipping, 2);

        $this->update([
            'subtotal' => $subtotal,
            'tax_amount' => $taxAmount,
            'total_amount' => $totalAmount,
        ]);
    }

    public function canReceiveGoods(): bool
    {
        return in_array($this->status, [
            self::STATUS_APPROVED,
            self::STATUS_ORDERED,
            self::STATUS_PARTIALLY_RECEIVED,
        ], true);
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_DRAFT => 'Draft',
            self::STATUS_PENDING_APPROVAL => 'Pending Approval',
            self::STATUS_APPROVED => 'Approved',
            self::STATUS_ORDERED => 'Ordered',
            self::STATUS_PARTIALLY_RECEIVED => 'Partially Received',
            self::STATUS_RECEIVED => 'Received',
            self::STATUS_CANCELLED => 'Cancelled',
            default => ucfirst($this->status),
        };
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_DRAFT => 'bg-gray-100 text-gray-800',
            self::STATUS_PENDING_APPROVAL => 'bg-amber-100 text-amber-800',
            self::STATUS_APPROVED => 'bg-indigo-100 text-indigo-800',
            self::STATUS_ORDERED => 'bg-blue-100 text-blue-800',
            self::STATUS_PARTIALLY_RECEIVED => 'bg-purple-100 text-purple-800',
            self::STATUS_RECEIVED => 'bg-emerald-100 text-emerald-800',
            self::STATUS_CANCELLED => 'bg-red-100 text-red-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }
}
