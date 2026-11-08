<?php

namespace App\Notifications;

use App\Models\Order;
use App\Models\Payment;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class PaymentReceivedNotification extends Notification
{
    use Queueable;

    public function __construct(
        public Payment $payment,
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
        $formattedAmount = function_exists('format_currency')
            ? format_currency((float) $this->payment->amount)
            : 'Rp '.number_format((float) $this->payment->amount, 0, ',', '.');

        return [
            'type' => 'payment_received',
            'title' => 'Pembayaran Diterima',
            'message' => 'Pembayaran sebesar '.$formattedAmount.' via '.strtoupper($this->payment->payment_method).' untuk pesanan #'.$this->order->order_number.' telah dikonfirmasi.',
            'icon' => 'fa-solid fa-circle-check',
            'color' => 'emerald',
            'action_url' => route('admin.orders.payments'),
            'payment_id' => $this->payment->id,
            'order_id' => $this->order->id,
            'order_number' => $this->order->order_number,
            'amount' => (float) $this->payment->amount,
        ];
    }
}
