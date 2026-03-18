<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Payment extends Model
{
    use LogsActivity;

    protected $fillable = [
        'tenant_id',
        'order_id',
        'payment_method',
        'amount',
        'change_amount',
        'status',
        'reference_number',
        'metadata',
        'notes',
        'paid_at',
    ];

    protected $casts = [
        'amount'        => 'decimal:2',
        'change_amount' => 'decimal:2',
        'metadata'      => 'array',
        'paid_at'       => 'datetime',
    ];

    // -------------------------------------------------------------------------
    // Activity Log
    // -------------------------------------------------------------------------

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['status', 'amount', 'payment_method'])
            ->logOnlyDirty()
            ->setDescriptionForEvent(fn(string $e) => "Payment for order was {$e}");
    }

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function markAsPaid(): void
    {
        $this->update([
            'status'  => 'completed',
            'paid_at' => now(),
        ]);
    }
}
