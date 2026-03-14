<?php

namespace Modules\Finance\Entities;

use App\Models\Payment;

/**
 * Finance-domain Payment entity.
 *
 * Adds financial reporting and formatting methods
 * on top of the base Payment model.
 */
class FinancePayment extends Payment
{
    public function getTable(): string
    {
        return 'payments';
    }

    /**
     * Human-readable payment method label.
     */
    public function methodLabel(): string
    {
        return match ($this->payment_method) {
            'cash'     => 'Cash',
            'card'     => 'Debit / Credit Card',
            'qris'     => 'QRIS',
            'transfer' => 'Bank Transfer',
            default    => ucfirst($this->payment_method),
        };
    }

    /**
     * Formatted amount with currency symbol.
     */
    public function formattedAmount(): string
    {
        $currency = app(\App\Services\ConfigService::class)->get('currency', 'IDR');

        return "{$currency} " . number_format($this->amount, 2, '.', ',');
    }
}
