<?php

namespace App\Listeners;

use App\Events\OrderCreated;
use App\Notifications\OrderCreatedNotification;
use App\Services\NotificationService;

class SendOrderNotification
{
    public function __construct(
        protected NotificationService $notificationService,
    ) {}

    public function handle(OrderCreated $event): void
    {
        $order = $event->order;

        if ($order->tenant_id) {
            $this->notificationService->sendToTenant(
                $order->tenant_id,
                new OrderCreatedNotification($order),
                ['tenant_admin', 'manager']
            );
        } else {
            $this->notificationService->sendToSuperAdmins(
                new OrderCreatedNotification($order)
            );
        }
    }
}
