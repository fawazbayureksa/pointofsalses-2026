<?php

namespace Modules\POS\app\Providers;

use App\Services\InventoryService;
use App\Services\OrderService;
use App\Services\PaymentService;
use Illuminate\Support\ServiceProvider;

class POSServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Bind services with their dependencies
        $this->app->singleton(InventoryService::class);

        $this->app->singleton(OrderService::class, function ($app) {
            return new OrderService($app->make(InventoryService::class));
        });

        $this->app->singleton(PaymentService::class, function ($app) {
            return new PaymentService($app->make(OrderService::class));
        });
    }

    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__ . '/../../routes/api.php');
    }
}
