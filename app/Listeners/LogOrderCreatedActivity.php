<?php

namespace App\Listeners;

use Illuminate\Support\Facades\Auth;

class LogOrderCreatedActivity
{
    public function handle($event): void
    {
        $order = $event->order;
        $user = Auth::user();
        activity('pos')
            ->performedOn($order)
            ->causedBy($user)
            ->withProperties([
                'order_number' => $order->order_number,
                'total_amount' => $order->total_amount,
                'items_count'  => $order->items->count(),
                'tenant_id'    => $user?->tenant_id,
            ])
            ->log('order_created');
    }
}
