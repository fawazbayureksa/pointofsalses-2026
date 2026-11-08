<?php

namespace App\Providers;

use App\Events\OrderCreated;
use App\Events\PaymentCompleted;
use App\Events\StockUpdated;
use App\Listeners\DeductInventoryOnOrderCreated;
use App\Listeners\LogOrderCreatedActivity;
use App\Listeners\LogPaymentCompletedActivity;
use App\Listeners\LogStockUpdatedActivity;
use App\Listeners\SendLowStockNotification;
use App\Listeners\SendOrderNotification;
use App\Listeners\SendPaymentNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        // ── Order flow ──────────────────────────────────────────────────────
        OrderCreated::class => [
            DeductInventoryOnOrderCreated::class, // deduct stock
            LogOrderCreatedActivity::class,       // write audit log
            SendOrderNotification::class,         // send notification
        ],

        // ── Payment flow ─────────────────────────────────────────────────────
        PaymentCompleted::class => [
            LogPaymentCompletedActivity::class,
            SendPaymentNotification::class,       // send notification
        ],

        // ── Inventory ────────────────────────────────────────────────────────
        StockUpdated::class => [
            LogStockUpdatedActivity::class,
            SendLowStockNotification::class,      // send notification
        ],
    ];

    public function boot(): void
    {
        //
    }
}
