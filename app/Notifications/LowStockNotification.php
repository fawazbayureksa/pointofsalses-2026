<?php

namespace App\Notifications;

use App\Models\Product;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class LowStockNotification extends Notification
{
    use Queueable;

    public function __construct(
        public Product $product,
        public float $currentStock,
        public float $threshold = 5,
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
        return [
            'type' => 'low_stock',
            'title' => 'Peringatan Stok Rendah',
            'message' => 'Stok produk "'.$this->product->name.'" tersisa '.$this->currentStock.' unit (batas minimum: '.$this->threshold.').',
            'icon' => 'fa-solid fa-triangle-exclamation',
            'color' => 'amber',
            'action_url' => route('admin.products.index', ['stock_filter' => 'low']),
            'product_id' => $this->product->id,
            'product_name' => $this->product->name,
            'current_stock' => $this->currentStock,
            'threshold' => $this->threshold,
        ];
    }
}
