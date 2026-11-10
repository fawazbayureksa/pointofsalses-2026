<?php

namespace App\Listeners;

use App\Events\PaymentCompleted;
use App\Notifications\PaymentReceivedNotification;
use App\Services\NotificationService;

class SendPaymentNotification
{
    public function __construct(
        protected NotificationService $notificationService,
    ) {}

    public function handle(PaymentCompleted $event): void
    {
        $order = $event->order;
        $payment = $event->payment;

        if ($order->tenant_id) {
            $this->notificationService->sendToTenant(
                $order->tenant_id,
                new PaymentReceivedNotification($payment, $order),
                ['tenant_admin', 'manager']
            );
        } else {
            $this->notificationService->sendToSuperAdmins(
                new PaymentReceivedNotification($payment, $order)
            );
        }
    }
}
