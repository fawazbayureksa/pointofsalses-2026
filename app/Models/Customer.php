<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'customer_code',
        'name',
        'email',
        'phone',
        'address',
        'date_of_birth',
        'gender',
        'loyalty_points',
        'status',
    ];

    protected $casts = [
        'date_of_birth'  => 'date',
        'loyalty_points' => 'integer',
    ];

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    public function addLoyaltyPoints(int $points): void
    {
        $this->increment('loyalty_points', $points);
    }
}
