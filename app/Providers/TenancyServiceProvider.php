<?php

declare(strict_types=1);

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

/**
 * Stub retained for compatibility — stancl/tenancy has been removed.
 * Tenancy is now handled via BelongsToTenant trait (single-DB approach).
 */
class TenancyServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void {}
}
