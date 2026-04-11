<?php

declare(strict_types=1);

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\CashierReportController;
use App\Http\Controllers\Api\CashierShiftController;
use App\Http\Controllers\Api\ConfigController;
use App\Http\Controllers\Api\CustomerController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\OutletController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\StockController;
use App\Http\Controllers\Api\SupervisorAuthController;
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
    Route::post('login-pin', [AuthController::class, 'loginWithPin']);
    Route::post('switch-cashier', [AuthController::class, 'switchCashier']);
});

// ── Authenticated endpoints ──────────────────────────────────────────────────
Route::middleware(['auth:sanctum', \App\Http\Middleware\AutoLockInactivity::class])->group(function () {

    // Auth
    Route::prefix('auth')->group(function () {
        Route::post('logout', [AuthController::class, 'logout']);
        Route::get('me', [AuthController::class, 'me']);
        Route::put('profile', [AuthController::class, 'updateProfile']);
        Route::put('password', [AuthController::class, 'changePassword']);
        Route::post('set-pin', [AuthController::class, 'setPin']);
        Route::get('cashiers', [AuthController::class, 'listCashiers']);
    });

    // Dashboard
    Route::get('dashboard', [DashboardController::class, 'index']);

    // POS – Products
    // Barcode lookup must be registered before apiResource to avoid being
    // captured by the {product} wildcard route.
    Route::get('products/barcode/{barcode}', [ProductController::class, 'findByBarcode']);
    Route::post('products/{product}/image', [ProductController::class, 'uploadImage']);
    Route::apiResource('products', ProductController::class);

    // POS – Categories
    Route::apiResource('categories', CategoryController::class);

    // POS – Customers
    Route::apiResource('customers', CustomerController::class);

    // POS – Outlets (read-only for mobile clients)
    Route::get('outlets', [OutletController::class, 'index']);
    Route::get('outlets/{outlet}', [OutletController::class, 'show']);

    // POS – Orders
    // `destroy` is excluded because cancellation is handled by POST .../cancel.
    Route::apiResource('orders', OrderController::class)->only(['index', 'store', 'show']);
    Route::post('orders/{order}/pay', [OrderController::class, 'pay']);
    Route::post('orders/{order}/cancel', [OrderController::class, 'cancel']);
    Route::post('orders/{order}/refund', [OrderController::class, 'refund']);
    Route::patch('orders/{order}/discount', [OrderController::class, 'applyDiscount']);

    // Payments
    Route::get('payments', [PaymentController::class, 'index']);
    Route::get('payments/{payment}', [PaymentController::class, 'show']);

    // Cashier Shifts
    Route::prefix('shifts')->group(function () {
        Route::get('/', [CashierShiftController::class, 'index']);
        Route::get('current', [CashierShiftController::class, 'current']);
        Route::post('start', [CashierShiftController::class, 'start']);
        Route::post('end', [CashierShiftController::class, 'end']);
    });

    // Supervisor Authorization
    Route::post('supervisor/authorize', [SupervisorAuthController::class, 'authorize']);

    // Reports
    Route::prefix('reports')->group(function () {
        Route::get('sales-by-cashier', [CashierReportController::class, 'salesByCashier']);
        Route::get('shift-summary', [CashierReportController::class, 'shiftSummary']);
    });

    // Stock – movements history & manual adjustment
    Route::prefix('stock')->group(function () {
        Route::get('movements', [StockController::class, 'movements']);
        Route::post('adjust', [StockController::class, 'adjust']);
    });

    // Tenant Configuration
    Route::prefix('config')->group(function () {
        Route::get('/', [ConfigController::class, 'index']);
        Route::get('{key}', [ConfigController::class, 'show']);
        Route::put('{key}', [ConfigController::class, 'update']);
    });
});
