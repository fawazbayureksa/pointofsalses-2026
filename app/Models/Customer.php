<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends Model
{
    use BelongsToTenant, SoftDeletes;

    const TIER_REGULAR  = 'regular';
    const TIER_SILVER   = 'silver';
    const TIER_GOLD     = 'gold';
    const TIER_PLATINUM = 'platinum';

    protected $fillable = [
        'tenant_id',
        'name',
        'email',
        'phone',
        'address',
        'customer_code',
        'gender',
        'date_of_birth',
        'notes',
        'loyalty_points',
        'is_active',
        'member_since',
        'membership_tier',
    ];

    protected $casts = [
        'loyalty_points' => 'integer',
        'is_active'      => 'boolean',
        'date_of_birth'  => 'date',
        'member_since'   => 'date',
    ];

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeMembers($query)
    {
        return $query->whereNotNull('member_since');
    }

    public function getIsMemberAttribute(): bool
    {
        return $this->member_since !== null;
    }

    public function getStatusAttribute(): string
    {
        return $this->is_active ? 'active' : 'inactive';
    }

    public static function tierLabels(): array
    {
        return [
            self::TIER_REGULAR  => 'Regular',
            self::TIER_SILVER   => 'Silver',
            self::TIER_GOLD     => 'Gold',
            self::TIER_PLATINUM => 'Platinum',
        ];
    }

    public static function tierColors(): array
    {
        return [
            self::TIER_REGULAR  => 'gray',
            self::TIER_SILVER   => 'slate',
            self::TIER_GOLD     => 'yellow',
            self::TIER_PLATINUM => 'purple',
        ];
    }
}
