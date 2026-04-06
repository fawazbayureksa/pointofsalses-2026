<?php

use App\Http\Controllers\Admin\ActivityLogController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\PermissionController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\TenantController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\SubscriptionController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn() => view('welcome'));

// Subscription expired page (auth required, no subscription check)
Route::middleware('auth')->get('/subscription/expired', fn() => view('subscription.expired'))->name('subscription.expired');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store']);

    Route::get('/register', [RegisteredUserController::class, 'create'])->name('register');
    Route::post('/register', [RegisteredUserController::class, 'store']);

    Route::get('/forgot-password', [PasswordResetLinkController::class, 'create'])->name('password.request');
    Route::post('/forgot-password', [PasswordResetLinkController::class, 'store'])->name('password.email');

    Route::get('/reset-password/{token}', [NewPasswordController::class, 'create'])->name('password.reset');
    Route::post('/reset-password', [NewPasswordController::class, 'store'])->name('password.update');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');

    // Subscription / plan selection (accessible even when trial has expired)
    Route::get('/subscription/plans', [SubscriptionController::class, 'plans'])->name('subscription.plans');
    Route::post('/subscription/select', [SubscriptionController::class, 'select'])->name('subscription.select');
});

Route::middleware(['auth', 'tenant.active', 'subscription.active'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {

        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

        Route::get('/profile', [UserController::class, 'profile'])->name('profile');
        Route::put('/profile', [UserController::class, 'updateProfile'])->name('profile.update');
        Route::put('/password', [UserController::class, 'updatePassword'])->name('password.change');

        Route::middleware('role:super_admin')->group(function () {
            Route::resource('tenants', TenantController::class);
        });

        Route::resource('outlets', \App\Http\Controllers\Admin\OutletController::class);
        Route::resource('categories', \App\Http\Controllers\Admin\CategoryController::class);
        Route::resource('products', \App\Http\Controllers\Admin\ProductController::class);
        Route::post('products/{product}/adjust-stock', [\App\Http\Controllers\Admin\ProductController::class, 'adjustStock'])->name('products.adjust-stock');
        Route::post('products/transfer-stock', [\App\Http\Controllers\Admin\ProductController::class, 'transferStock'])->name('products.transfer-stock');
        Route::resource('customers', \App\Http\Controllers\Admin\CustomerController::class);
        Route::post('customers/{customer}/enroll', [\App\Http\Controllers\Admin\CustomerController::class, 'enroll'])->name('customers.enroll');
        Route::delete('customers/{customer}/unenroll', [\App\Http\Controllers\Admin\CustomerController::class, 'unenroll'])->name('customers.unenroll');
        Route::resource('orders', \App\Http\Controllers\Admin\OrderController::class);
        Route::get('orders/{order}/print', [\App\Http\Controllers\Admin\OrderController::class, 'print'])->name('orders.print');
        Route::get('orders-payments', [\App\Http\Controllers\Admin\OrderController::class, 'payments'])->name('orders.payments');
        Route::post('orders/{order}/process-payment', [\App\Http\Controllers\Admin\OrderController::class, 'processPayment'])->name('orders.process-payment');
        Route::post('orders/{order}/refund-payment', [\App\Http\Controllers\Admin\OrderController::class, 'refundPayment'])->name('orders.refund-payment');
        Route::post('orders/{order}/items', [\App\Http\Controllers\Admin\OrderController::class, 'addItem'])->name('orders.items.store');
        Route::delete('orders/{order}/items/{item}', [\App\Http\Controllers\Admin\OrderController::class, 'removeItem'])->name('orders.items.destroy');

        Route::get('pos', [\App\Http\Controllers\Admin\PosController::class, 'index'])->name('pos.index');

        // Reports
        Route::prefix('reports')->name('reports.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Admin\ReportController::class, 'index'])->name('index');
            Route::get('/sales', [\App\Http\Controllers\Admin\ReportController::class, 'sales'])->name('sales');
            Route::get('/products', [\App\Http\Controllers\Admin\ReportController::class, 'products'])->name('products');
            Route::get('/customers', [\App\Http\Controllers\Admin\ReportController::class, 'customers'])->name('customers');
            Route::get('/inventory', [\App\Http\Controllers\Admin\ReportController::class, 'inventory'])->name('inventory');
        });

        // Inventory
        Route::prefix('inventory')->name('inventory.')->group(function () {
            Route::get('/stock', [\App\Http\Controllers\Admin\ProductController::class, 'stock'])->name('stock');
            Route::get('/movements', [\App\Http\Controllers\Admin\ProductController::class, 'movements'])->name('movements');
        });

        // Settings
        Route::middleware('can:manage settings')->prefix('settings')->name('settings.')->group(function () {
            Route::get('/', [SettingController::class, 'index'])->name('index');
            Route::put('/', [SettingController::class, 'update'])->name('update');
            Route::put('/email', [SettingController::class, 'updateEmail'])->name('email.update');
            Route::put('/backup', [SettingController::class, 'updateBackup'])->name('backup.update');
            Route::get('/tax', [SettingController::class, 'tax'])->name('tax');
            Route::put('/tax', [SettingController::class, 'updateTax'])->name('tax.update');
            Route::get('/currency', [SettingController::class, 'currency'])->name('currency');
            Route::put('/currency', [SettingController::class, 'updateCurrency'])->name('currency.update');
        });

        Route::middleware('can:manage users')->group(function () {
            Route::resource('users', UserController::class);
            Route::resource('roles', RoleController::class);
            Route::resource('permissions', PermissionController::class);
        });

        Route::middleware('can:view activity logs')->prefix('logs')->name('logs.')->group(function () {
            Route::get('/', [ActivityLogController::class, 'index'])->name('index');
            Route::get('/export', [ActivityLogController::class, 'export'])->name('export');
            Route::delete('/clear', [ActivityLogController::class, 'clear'])->name('clear');
        });

        Route::fallback(fn() => response()->view('errors.404', [], 404));
    });
