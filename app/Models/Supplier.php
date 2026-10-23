<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Supplier extends Model
{
    use BelongsToTenant, HasFactory, LogsActivity, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'code',
        'name',
        'company_name',
        'contact_person',
        'phone',
        'email',
        'address',
        'city',
        'npwp',
        'tax_info',
        'bank_name',
        'bank_account_number',
        'bank_account_holder',
        'default_payment_terms',
        'is_active',
        'notes',
    ];

    protected $casts = [
        'default_payment_terms' => 'integer',
        'is_active' => 'boolean',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->setDescriptionForEvent(fn (string $e) => "Supplier {$this->name} ({$this->code}) was {$e}");
    }

    public static function generateCode(?int $tenantId = null): string
    {
        $query = static::withoutGlobalScopes()->withTrashed();
        if ($tenantId !== null) {
            $query->where('tenant_id', $tenantId);
        }

        $latest = $query->orderByDesc('id')->first();
        $nextNumber = 1;

        if ($latest && preg_match('/SUP-(\d+)/', $latest->code, $matches)) {
            $nextNumber = ((int) $matches[1]) + 1;
        } else {
            $nextNumber = $query->count() + 1;
        }

        do {
            $code = 'SUP-'.str_pad((string) $nextNumber, 4, '0', STR_PAD_LEFT);
            $exists = static::withoutGlobalScopes()
                ->where('tenant_id', $tenantId)
                ->where('code', $code)
                ->exists();
            $nextNumber++;
        } while ($exists);

        return $code;
    }

    // ── Relationships ──────────────────────────────────────────────────────────

    public function supplierProducts(): HasMany
    {
        return $this->hasMany(SupplierProduct::class);
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'supplier_products')
            ->withPivot([
                'id',
                'supplier_sku',
                'purchase_price',
                'is_primary',
                'min_order_qty',
                'lead_time_days',
                'purchase_unit',
                'conversion_factor',
                'notes',
            ])
            ->withTimestamps();
    }

    public function purchaseOrders(): HasMany
    {
        return $this->hasMany(PurchaseOrder::class);
    }

    public function goodsReceipts(): HasMany
    {
        return $this->hasMany(GoodsReceipt::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(SupplierInvoice::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(SupplierPayment::class);
    }

    public function returns(): HasMany
    {
        return $this->hasMany(SupplierReturn::class);
    }

    public function priceHistories(): HasMany
    {
        return $this->hasMany(SupplierPriceHistory::class);
    }

    // ── Scopes & Helpers ───────────────────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function totalPayableBalance(): float
    {
        return (float) $this->invoices()
            ->whereIn('status', ['unpaid', 'partially_paid', 'overdue'])
            ->sum('outstanding_balance');
    }
}
