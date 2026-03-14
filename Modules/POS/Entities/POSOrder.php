<?php

namespace Modules\POS\Entities;

use App\Models\Order;

/**
 * POS-domain Order entity.
 *
 * Extends the base Order model with POS-specific business logic and
 * presentation helpers.  Business logic belongs here – NOT in controllers.
 */
class POSOrder extends Order
{
    public function getTable(): string
    {
        return 'orders';
    }

    /**
     * Whether the order can still be cancelled.
     */
    public function canBeCancelled(): bool
    {
        return in_array($this->status, ['pending', 'processing'], true);
    }

    /**
     * Formatted total amount including currency.
     */
    public function formattedTotal(): string
    {
        $currency = app(\App\Services\ConfigService::class)->get('currency', 'IDR');

        return "{$currency} " . number_format($this->total_amount, 2, '.', ',');
    }
}
