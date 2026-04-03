<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Tenant extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    protected $fillable = [
        'name',
        'slug',
        'email',
        'phone',
        'contact_name',
        'business_type',
        'logo',
        'address',
        'plan',
        'status',
        'trial_ends_at',
        'subscription_skipped',
        'settings',
    ];

    protected $casts = [
        'trial_ends_at'        => 'datetime',
        'subscription_skipped' => 'boolean',
        'settings'             => 'array',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->setDescriptionForEvent(fn(string $e) => "Tenant {$this->name} was {$e}");
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function outlets(): HasMany
    {
        return $this->hasMany(Outlet::class);
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isSuspended(): bool
    {
        return $this->status === 'suspended';
    }

    /** Whether this tenant is currently within a free-trial window. */
    public function isOnTrial(): bool
    {
        return $this->trial_ends_at !== null && $this->trial_ends_at->isFuture();
    }

    /** Remaining trial days (0 when trial has ended or was never set). */
    public function trialDaysLeft(): int
    {
        if (! $this->isOnTrial()) {
            return 0;
        }

        return (int) now()->diffInDays($this->trial_ends_at);
    }

    /**
     * Whether the tenant may access the application.
     *
     * Access is granted when any of the following is true:
     *  1. subscription_skipped flag is set (manual override by super-admin)
     *  2. plan is "basic" (always-free starter tier)
     *  3. trial has not yet expired
     */
    public function canAccessSystem(): bool
    {
        if ($this->subscription_skipped) {
            return true;
        }

        if ($this->plan === 'basic') {
            return true;
        }

        return $this->isOnTrial();
    }

    public function getSetting(string $key, mixed $default = null): mixed
    {
        return data_get($this->settings, $key, $default);
    }

    public function setSetting(string $key, mixed $value): void
    {
        $settings = $this->settings ?? [];
        data_set($settings, $key, $value);
        $this->update(['settings' => $settings]);
    }
}
