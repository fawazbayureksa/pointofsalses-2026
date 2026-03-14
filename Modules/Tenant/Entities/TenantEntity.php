<?php

namespace Modules\Tenant\Entities;

use App\Models\Tenant as BaseTenant;

/**
 * Tenant entity – extends the central Tenant model with business-level helpers.
 * All domain logic specific to tenant management lives here inside the Tenant module.
 */
class TenantEntity extends BaseTenant
{
    public function getTable(): string
    {
        return 'tenants';
    }

    /**
     * Check whether the tenant is on a premium plan.
     */
    public function isPremium(): bool
    {
        return in_array($this->plan, ['professional', 'enterprise'], true);
    }

    /**
     * Check whether the tenant can create more outlets based on their plan.
     */
    public function canAddOutlet(int $currentCount): bool
    {
        $limits = [
            'basic'        => 1,
            'professional' => 5,
            'enterprise'   => PHP_INT_MAX,
        ];

        return $currentCount < ($limits[$this->plan] ?? 1);
    }
}
