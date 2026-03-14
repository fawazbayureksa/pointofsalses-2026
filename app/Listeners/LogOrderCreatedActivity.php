<?php

namespace App\Listeners;

use App\Events\OrderCreated;

class LogOrderCreatedActivity
{
    public function handle(OrderCreated $event): void
    {
        $order = $event->order;

        activity('pos')
            ->performedOn($order)
            ->causedBy(auth()->user())
            ->withProperties([
                'order_number' => $order->order_number,
                'total_amount' => $order->total_amount,
                'items_count'  => $order->items->count(),
                'tenant_id'    => tenant('id'),
            ])
            ->log('order_created');
    }
}
