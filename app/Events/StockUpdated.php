<?php

namespace App\Events;

use App\Models\Product;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class StockUpdated
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly Product $product,
        public readonly float $stockBefore,
        public readonly float $stockAfter,
        public readonly string $reason = 'sale',
        public readonly string $notes = '',
    ) {}
}
