<?php

namespace App\Listeners;

use App\Events\StockUpdated;
use Illuminate\Support\Facades\Auth;

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
                'tenant_id'    => Auth::user()?->tenant_id,
            ])
            ->log('stock_updated');
    }
}
