<?php

namespace App\Listeners;

use App\Events\PaymentCompleted;

class LogPaymentCompletedActivity
{
    public function handle(PaymentCompleted $event): void
    {
        $payment = $event->payment;
        $order   = $event->order;

        activity('finance')
            ->performedOn($payment)
            ->causedBy(auth()->user())
            ->withProperties([
                'order_number'   => $order->order_number,
                'payment_method' => $payment->payment_method,
                'amount'         => $payment->amount,
                'tenant_id'      => tenant('id'),
            ])
            ->log('payment_completed');
    }
}
