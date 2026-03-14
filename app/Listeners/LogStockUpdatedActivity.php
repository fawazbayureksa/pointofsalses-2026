<?php

namespace App\Listeners;

use App\Events\StockUpdated;

class LogStockUpdatedActivity
{
    public function handle(StockUpdated $event): void
    {
        activity('inventory')
            ->performedOn($event->product)
            ->withProperties([
                'stock_before' => $event->stockBefore,
                'stock_after'  => $event->stockAfter,
                'change'       => $event->stockAfter - $event->stockBefore,
                'reason'       => $event->reason,
                'notes'        => $event->notes,
                'tenant_id'    => tenant('id'),
            ])
            ->log('stock_updated');
    }
}
