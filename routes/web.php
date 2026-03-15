<?php

use App\Http\Controllers\Admin\ActivityLogController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\CustomerController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\OutletController;
use App\Http\Controllers\Admin\PermissionController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\TenantController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RegisteredUserController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware(['guest'])->group(function () {
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])
        ->name('login');

    Route::post('/login', [AuthenticatedSessionController::class, 'store']);

    Route::get('/register', [RegisteredUserController::class, 'create'])
        ->name('register');

    Route::post('/register', [RegisteredUserController::class, 'store']);

    Route::get('/forgot-password', [PasswordResetLinkController::class, 'create'])
        ->name('password.request');

    Route::post('/forgot-password', [PasswordResetLinkController::class, 'store'])
        ->name('password.email');

    Route::get('/reset-password/{token}', [NewPasswordController::class, 'create'])
        ->name('password.reset');

    Route::post('/reset-password', [NewPasswordController::class, 'store'])
        ->name('password.update');
});

Route::middleware(['auth'])->group(function () {
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])
        ->name('logout');
});

Route::middleware(['auth'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {

        // Route::middleware(['can:view admin panel'])->group(function () {

        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

        Route::middleware(['role:super_admin'])->group(function () {
            Route::resource('tenants', TenantController::class);
        });

        Route::resource('outlets', OutletController::class);

        Route::resource('products', ProductController::class);

        Route::resource('categories', CategoryController::class);

        Route::resource('customers', CustomerController::class);

        Route::prefix('orders')->name('orders.')->group(function () {
            Route::get('/', [OrderController::class, 'index'])->name('index');
            Route::post('/', [OrderController::class, 'store'])->name('store');
            Route::get('/{order}', [OrderController::class, 'show'])->name('show');
            Route::get('/{order}/edit', [OrderController::class, 'edit'])->name('edit');
            Route::put('/{order}', [OrderController::class, 'update'])->name('update');
            Route::delete('/{order}', [OrderController::class, 'destroy'])->name('destroy');
            Route::get('/{order}/print', [OrderController::class, 'print'])->name('print');
            Route::post('/{order}/items', [OrderController::class, 'addItem'])->name('add-item');
            Route::delete('/orders/{order}/items/{item}', [OrderController::class, 'removeItem'])->name('remove-item');
        });

        Route::middleware(['can:manage users'])->group(function () {
            Route::resource('users', UserController::class);
            Route::resource('roles', RoleController::class);
            Route::resource('permissions', PermissionController::class);
        });

        Route::prefix('settings')->name('settings.')->group(function () {
            Route::get('/', [SettingController::class, 'index'])->name('index');
            Route::put('/', [SettingController::class, 'update'])->name('update');
            Route::prefix('email')->group(function () {
                Route::put('/', [SettingController::class, 'updateEmail'])->name('email.update');
                Route::post('/test', [SettingController::class, 'testEmail'])->name('email.test');
            });
            Route::prefix('backup')->group(function () {
                Route::put('/', [SettingController::class, 'updateBackup'])->name('backup.update');
                Route::post('/create', [SettingController::class, 'createBackup'])->name('backup.create');
                Route::get('/download/{file}', [SettingController::class, 'downloadBackup'])->name('backup.download');
                Route::delete('/{file}', [SettingController::class, 'deleteBackup'])->name('backup.delete');
            });
            Route::prefix('tax')->group(function () {
                Route::get('/', [SettingController::class, 'tax'])->name('tax');
                Route::put('/', [SettingController::class, 'updateTax'])->name('tax.update');
            });
            Route::prefix('currency')->group(function () {
                Route::get('/', [SettingController::class, 'currency'])->name('currency');
                Route::put('/', [SettingController::class, 'updateCurrency'])->name('currency.update');
            });
        });

        Route::middleware(['can:view activity logs'])->prefix('logs')->name('logs.')->group(function () {
            Route::get('/', [ActivityLogController::class, 'index'])->name('index');
            Route::get('/export', [ActivityLogController::class, 'export'])->name('export');
            Route::delete('/clear', [ActivityLogController::class, 'clear'])->name('clear');
        });

        Route::prefix('inventory')->name('inventory.')->group(function () {
            Route::get('/stock', [ProductController::class, 'stock'])->name('stock');
            Route::get('/movements', [ProductController::class, 'movements'])->name('movements');
            Route::post('/adjust-stock/{product}', [ProductController::class, 'adjustStock'])->name('adjust-stock');
            Route::post('/transfer-stock', [ProductController::class, 'transferStock'])->name('transfer-stock');
        });

        Route::prefix('payments')->name('payments.')->group(function () {
            Route::get('/', [OrderController::class, 'payments'])->name('index');
            Route::post('/{order}', [OrderController::class, 'processPayment'])->name('process');
            Route::post('/{order}/refund', [OrderController::class, 'refundPayment'])->name('refund');
        });

        Route::get('/profile', [UserController::class, 'profile'])->name('profile');
        Route::put('/profile', [UserController::class, 'updateProfile'])->name('profile.update');
        Route::put('/password', [UserController::class, 'updatePassword'])->name('password.update');
        // });

        Route::fallback(function () {
            return response()->view('errors.404', [], 404);
        });
    });
