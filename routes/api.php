<?php

declare(strict_types=1);

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ConfigController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\ProductController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes – Tenant Context
|--------------------------------------------------------------------------
|
| All routes here run inside a tenant context (resolved via subdomain or
| path-based tenancy middleware).  Authentication is handled via Sanctum.
|
*/

// ── Public endpoints (no auth required) ─────────────────────────────────────
Route::prefix('auth')->group(function () {
    Route::post('login', [AuthController::class, 'login']);
});

// ── Authenticated endpoints ──────────────────────────────────────────────────
Route::middleware('auth:sanctum')->group(function () {

    // Auth
    Route::prefix('auth')->group(function () {
        Route::post('logout', [AuthController::class, 'logout']);
        Route::get('me', [AuthController::class, 'me']);
    });

    // POS – Products
    Route::apiResource('products', ProductController::class);

    // POS – Orders
    Route::apiResource('orders', OrderController::class)->except(['update']);
    Route::post('orders/{order}/pay', [OrderController::class, 'pay']);
    Route::post('orders/{order}/cancel', [OrderController::class, 'cancel']);

    // Tenant Configuration
    Route::prefix('config')->group(function () {
        Route::get('/', [ConfigController::class, 'index']);
        Route::get('{key}', [ConfigController::class, 'show']);
        Route::put('{key}', [ConfigController::class, 'update']);
    });
});
