<?php

declare(strict_types=1);

namespace App\Models;

use Stancl\Tenancy\Database\Models\Tenant as BaseTenant;
use Stancl\Tenancy\Contracts\TenantWithDatabase;
use Stancl\Tenancy\Database\Concerns\HasDatabase;
use Stancl\Tenancy\Database\Concerns\HasDomains;

class Tenant extends BaseTenant implements TenantWithDatabase
{
    use HasDatabase, HasDomains;

    /**
     * Columns stored directly on the `tenants` table.
     * Everything else goes into the `data` JSON column via stancl magic.
     */
    protected $fillable = [
        'id',
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
    ];

    protected $casts = [
        'data'          => 'array',
        'trial_ends_at' => 'datetime',
    ];

    // -------------------------------------------------------------------------
    // stancl/tenancy: declare which custom columns live on the table
    // -------------------------------------------------------------------------
    public static function getCustomColumns(): array
    {
        return [
            'id',
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
        ];
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isSuspended(): bool
    {
        return $this->status === 'suspended';
    }
}
