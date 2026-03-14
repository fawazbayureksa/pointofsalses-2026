<?php

namespace App\Listeners;

use App\Events\OrderCreated;
use App\Services\InventoryService;

class DeductInventoryOnOrderCreated
{
    public function __construct(
        private InventoryService $inventoryService,
    ) {}

    public function handle(OrderCreated $event): void
    {
        $this->inventoryService->deductStockForOrder($event->order);
    }
}
