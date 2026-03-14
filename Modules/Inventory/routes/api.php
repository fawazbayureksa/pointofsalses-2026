<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Inventory Module – API Routes
|--------------------------------------------------------------------------
| Low-stock alerts, stock adjustments, and product CRUD are registered here.
*/

Route::prefix('api/inventory')->middleware(['auth:sanctum'])->group(function () {
    Route::get('low-stock', fn() => response()->json(
        app(\App\Services\InventoryService::class)->getLowStockProducts()
    ));

    Route::post('products/{product}/adjust', function (
        \Illuminate\Http\Request $request,
        \App\Models\Product $product
    ) {
        $request->validate(['quantity' => 'required|numeric|min:0', 'reason' => 'nullable|string']);
        app(\App\Services\InventoryService::class)->adjustStock($product, $request->quantity, $request->reason ?? '');

        return response()->json($product->fresh());
    });
});
