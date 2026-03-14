<?php

namespace App\Providers;

use App\Events\OrderCreated;
use App\Events\PaymentCompleted;
use App\Events\StockUpdated;
use App\Listeners\DeductInventoryOnOrderCreated;
use App\Listeners\LogOrderCreatedActivity;
use App\Listeners\LogPaymentCompletedActivity;
use App\Listeners\LogStockUpdatedActivity;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        // ── Order flow ──────────────────────────────────────────────────────
        OrderCreated::class => [
            DeductInventoryOnOrderCreated::class, // deduct stock
            LogOrderCreatedActivity::class,       // write audit log
        ],

        // ── Payment flow ─────────────────────────────────────────────────────
        PaymentCompleted::class => [
            LogPaymentCompletedActivity::class,
        ],

        // ── Inventory ────────────────────────────────────────────────────────
        StockUpdated::class => [
            LogStockUpdatedActivity::class,
        ],
    ];

    public function boot(): void
    {
        //
    }
}
