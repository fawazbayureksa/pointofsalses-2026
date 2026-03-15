<?php

namespace App\Providers;

use App\Services\ConfigService;
use App\Services\InventoryService;
use App\Services\OrderService;
use App\Services\PaymentService;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Spatie\Permission\Models\Permission;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Bind services as singletons so the DI container resolves them correctly.
        // POSServiceProvider also binds these; this ensures they're available
        // everywhere (including artisan commands outside the POS module).

        $this->app->singleton(ConfigService::class);
        $this->app->singleton(InventoryService::class);

        $this->app->singleton(
            OrderService::class,
            fn ($app) => new OrderService($app->make(InventoryService::class))
        );

        $this->app->singleton(
            PaymentService::class,
            fn ($app) => new PaymentService($app->make(OrderService::class))
        );
    }

    public function boot(): void
    {
        // Spatie Permission registers a Gate for every permission automatically
        // via its own ServiceProvider. This "before" callback grants super_admin
        // access to everything without needing individual permission checks.
        Gate::before(function ($user, $ability) {
            if ($user->hasRole('super_admin')) {
                return true;
            }
        });
    }
}
