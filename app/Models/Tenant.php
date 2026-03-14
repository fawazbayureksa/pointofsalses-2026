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
        'business_type',
        'plan',
        'status',
    ];

    protected $casts = [
        'data' => 'array',
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
            'business_type',
            'plan',
            'status',
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
