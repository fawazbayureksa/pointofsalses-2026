<?php

use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\ProductController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| POS Module – API Routes
|--------------------------------------------------------------------------
*/

Route::prefix('api')->middleware(['auth:sanctum'])->group(function () {
    Route::apiResource('products', ProductController::class);

    Route::apiResource('orders', OrderController::class)->except(['update']);
    Route::post('orders/{order}/pay', [OrderController::class, 'pay']);
    Route::post('orders/{order}/cancel', [OrderController::class, 'cancel']);
});
