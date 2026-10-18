<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class SupplierPayment extends Model
{
    use BelongsToTenant, LogsActivity;

    protected $fillable = [
        'tenant_id',
        'supplier_id',
        'supplier_invoice_id',
        'payment_number',
        'payment_date',
        'amount',
        'payment_method',
        'reference_number',
        'proof_file',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'payment_date' => 'date',
        'amount' => 'decimal:2',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->setDescriptionForEvent(fn (string $e) => "Supplier Payment {$this->payment_number} was {$e}");
    }

    public static function generatePaymentNumber(?int $tenantId = null): string
    {
        $prefix = 'SPAY-'.now()->format('Ym').'-';
        $query = static::withoutGlobalScopes()->where('payment_number', 'like', $prefix.'%');
        if ($tenantId !== null) {
            $query->where('tenant_id', $tenantId);
        }

        $count = $query->count() + 1;

        do {
            $number = $prefix.str_pad((string) $count, 4, '0', STR_PAD_LEFT);
            $exists = static::withoutGlobalScopes()
                ->where('tenant_id', $tenantId)
                ->where('payment_number', $number)
                ->exists();
            $count++;
        } while ($exists);

        return $number;
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class)->withTrashed();
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(SupplierInvoice::class, 'supplier_invoice_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getPaymentMethodLabelAttribute(): string
    {
        return match ($this->payment_method) {
            'bank_transfer' => 'Bank Transfer',
            'cash' => 'Cash',
            'giro' => 'Giro / Check',
            'e_wallet' => 'E-Wallet / QRIS',
            default => ucfirst(str_replace('_', ' ', $this->payment_method)),
        };
    }
}
