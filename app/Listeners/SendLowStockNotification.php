<?php

namespace App\Listeners;

use App\Events\StockUpdated;
use App\Notifications\LowStockNotification;
use App\Services\NotificationService;

class SendLowStockNotification
{
    public function __construct(
        protected NotificationService $notificationService,
    ) {}

    public function handle(StockUpdated $event): void
    {
        $product = $event->product;

        if (! $product->track_stock) {
            return;
        }

        $threshold = 5.0;
        if ($product->relationLoaded('outlets') && $product->outlets->isNotEmpty()) {
            $pivot = $product->outlets->first();
            if ($pivot && isset($pivot->pivot->low_stock_threshold)) {
                $threshold = (float) $pivot->pivot->low_stock_threshold;
            }
        } elseif ($product->outlets()->exists()) {
            $pivotThreshold = $product->outlets()->first()?->pivot?->low_stock_threshold;
            if ($pivotThreshold !== null && (float) $pivotThreshold > 0) {
                $threshold = (float) $pivotThreshold;
            }
        }

        if ($event->stockAfter <= $threshold) {
            $notification = new LowStockNotification($product, (float) $event->stockAfter, $threshold);

            if ($product->tenant_id) {
                $this->notificationService->sendToTenant(
                    $product->tenant_id,
                    $notification,
                    ['tenant_admin', 'manager']
                );
            } else {
                $this->notificationService->sendToSuperAdmins($notification);
            }
        }
    }
}
