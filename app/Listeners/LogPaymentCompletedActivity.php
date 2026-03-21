<?php

namespace App\Listeners;

use App\Events\PaymentCompleted;
use Illuminate\Support\Facades\Auth;

class LogPaymentCompletedActivity
{
    public function handle(PaymentCompleted $event): void
    {
        $payment = $event->payment;
        $order   = $event->order;

        activity('finance')
            ->performedOn($payment)
            ->causedBy(Auth::user())
            ->withProperties([
                'order_number'   => $order->order_number,
                'payment_method' => $payment->payment_method,
                'amount'         => $payment->amount,
                'tenant_id'      => Auth::user()?->tenant_id,
            ])
            ->log('payment_completed');
    }
}
