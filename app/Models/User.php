<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Activitylog\Traits\CausesActivity;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens, HasRoles, CausesActivity, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'name',
        'email',
        'password',
        'pin',
        'phone',
        'avatar',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'pin',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
            'is_active'         => 'boolean',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function outlets(): BelongsToMany
    {
        return $this->belongsToMany(Outlet::class, 'outlet_user')
                    ->withPivot('is_default')
                    ->withTimestamps();
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function shifts(): HasMany
    {
        return $this->hasMany(CashierShift::class);
    }

    public function activeShift(): HasOne
    {
        return $this->hasOne(CashierShift::class)->whereNull('ended_at')->latestOfMany('started_at');
    }

    public function isSuperAdmin(): bool
    {
        return $this->tenant_id === null;
    }

    public function setPin(string $pin): void
    {
        $this->update(['pin' => Hash::make($pin)]);
    }

    public function verifyPin(string $pin): bool
    {
        if ($this->pin === null) {
            return false;
        }

        return Hash::check($pin, $this->pin);
    }

    public function hasPin(): bool
    {
        return $this->pin !== null;
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
