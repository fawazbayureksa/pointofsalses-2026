<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class OrderCreatedNotification extends Notification
{
    use Queueable;

    public function __construct(
        public Order $order,
    ) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $formattedTotal = function_exists('format_currency')
            ? format_currency((float) $this->order->total_amount)
            : 'Rp '.number_format((float) $this->order->total_amount, 0, ',', '.');

        return [
            'type' => 'order_created',
            'title' => 'Pesanan Baru Diterima',
            'message' => 'Pesanan #'.$this->order->order_number.' dengan total '.$formattedTotal.' berhasil dibuat.',
            'icon' => 'fa-solid fa-cart-shopping',
            'color' => 'blue',
            'action_url' => route('admin.orders.index', ['search' => $this->order->order_number]),
            'order_id' => $this->order->id,
            'order_number' => $this->order->order_number,
            'total_amount' => (float) $this->order->total_amount,
        ];
    }
}
