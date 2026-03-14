<?php

namespace Modules\Tenant\app\Providers;

use Illuminate\Support\ServiceProvider;

class TenantModuleServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__ . '/../../routes/api.php');
        $this->loadMigrationsFrom(__DIR__ . '/../../database/migrations');
    }
}
