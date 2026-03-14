<?php

namespace App\Events;

use App\Models\Product;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class StockUpdated
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public Product $product,
        public float $stockBefore,
        public float $stockAfter,
        public string $reason = 'sale',
        public string $notes = '',
    ) {}
}
