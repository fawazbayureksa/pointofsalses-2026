<?php

use App\Http\Controllers\Admin\ActivityLogController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\CustomerController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\GoodsReceiptController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\OutletController;
use App\Http\Controllers\Admin\PermissionController;
use App\Http\Controllers\Admin\PosController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\PurchaseOrderController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\SubscriptionManagementController;
use App\Http\Controllers\Admin\SupplierController;
use App\Http\Controllers\Admin\SupplierPayableController;
use App\Http\Controllers\Admin\TenantController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\SubscriptionController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => view('welcome'));

// Subscription expired page (auth required, no subscription check)
Route::middleware('auth')->get('/subscription/expired', fn () => view('subscription.expired'))->name('subscription.expired');

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
        Route::get('/guide', fn () => view('admin.guide.index'))->name('guide');

        Route::get('/profile', [UserController::class, 'profile'])->name('profile');
        Route::put('/profile', [UserController::class, 'updateProfile'])->name('profile.update');
        Route::put('/password', [UserController::class, 'updatePassword'])->name('password.change');

        // Tenant & Super-Admin Subscription Management (accessible even if subscription expired so tenant can renew)
        Route::withoutMiddleware('subscription.active')
            ->prefix('subscription')
            ->name('subscription.')
            ->group(function () {
                Route::get('/', [SubscriptionManagementController::class, 'index'])->name('index');
                Route::post('/plan', [SubscriptionManagementController::class, 'changePlan'])->name('plan.change');
                Route::put('/billing', [SubscriptionManagementController::class, 'updateBilling'])->name('billing.update');
                Route::post('/cancel', [SubscriptionManagementController::class, 'cancel'])->name('cancel');
                Route::post('/tenants/{tenant}/action', [SubscriptionManagementController::class, 'adminAction'])
                    ->middleware('role:super_admin')
                    ->name('admin-action');
            });

        Route::middleware('role:super_admin')->group(function () {
            Route::resource('tenants', TenantController::class);
        });

        Route::resource('outlets', OutletController::class);
        Route::resource('categories', CategoryController::class);
        Route::resource('products', ProductController::class);
        Route::post('products/{product}/adjust-stock', [ProductController::class, 'adjustStock'])->name('products.adjust-stock');
        Route::post('products/transfer-stock', [ProductController::class, 'transferStock'])->name('products.transfer-stock');
        Route::resource('customers', CustomerController::class);
        Route::post('customers/{customer}/enroll', [CustomerController::class, 'enroll'])->name('customers.enroll');
        Route::delete('customers/{customer}/unenroll', [CustomerController::class, 'unenroll'])->name('customers.unenroll');
        Route::resource('orders', OrderController::class);
        Route::get('orders/{order}/print', [OrderController::class, 'print'])->name('orders.print');
        Route::get('orders-payments', [OrderController::class, 'payments'])->name('orders.payments');
        Route::post('orders/{order}/process-payment', [OrderController::class, 'processPayment'])->name('orders.process-payment');
        Route::post('orders/{order}/refund-payment', [OrderController::class, 'refundPayment'])->name('orders.refund-payment');
        Route::post('orders/{order}/items', [OrderController::class, 'addItem'])->name('orders.items.store');
        Route::delete('orders/{order}/items/{item}', [OrderController::class, 'removeItem'])->name('orders.items.destroy');

        Route::get('pos', [PosController::class, 'index'])->name('pos.index');

        // Reports
        Route::prefix('reports')->name('reports.')->group(function () {
            Route::get('/', [ReportController::class, 'index'])->name('index');
            Route::get('/sales', [ReportController::class, 'sales'])->name('sales');
            Route::get('/products', [ReportController::class, 'products'])->name('products');
            Route::get('/customers', [ReportController::class, 'customers'])->name('customers');
            Route::get('/inventory', [ReportController::class, 'inventory'])->name('inventory');
        });

        // Inventory
        Route::prefix('inventory')->name('inventory.')->group(function () {
            Route::get('/stock', [ProductController::class, 'stock'])->name('stock');
            Route::get('/movements', [ProductController::class, 'movements'])->name('movements');
        });

        // Supplier Management (Optional per Tenant)
        Route::middleware('supplier.enabled')->group(function () {
            // 1. Suppliers Directory & 2. Supplier Products Mapping
            Route::resource('suppliers', SupplierController::class)->except(['create', 'edit']);
            Route::get('supplier-products', [SupplierController::class, 'catalog'])->name('supplier-products.index');
            Route::post('supplier-products', [SupplierController::class, 'storeMapping'])->name('supplier-products.store');
            Route::delete('supplier-products/{mapping}', [SupplierController::class, 'destroyMapping'])->name('supplier-products.destroy');

            // 3. Purchase Orders (PO)
            Route::get('purchase-orders', [PurchaseOrderController::class, 'index'])->name('purchase-orders.index');
            Route::post('purchase-orders', [PurchaseOrderController::class, 'store'])->name('purchase-orders.store');
            Route::get('purchase-orders/{purchase_order}', [PurchaseOrderController::class, 'show'])->name('purchase-orders.show');
            Route::get('purchase-orders/{purchase_order}/print', [PurchaseOrderController::class, 'print'])->name('purchase-orders.print');
            Route::post('purchase-orders/{purchase_order}/submit-approval', [PurchaseOrderController::class, 'submitApproval'])->name('purchase-orders.submit-approval');
            Route::post('purchase-orders/{purchase_order}/approve', [PurchaseOrderController::class, 'approve'])->name('purchase-orders.approve');
            Route::post('purchase-orders/{purchase_order}/mark-ordered', [PurchaseOrderController::class, 'markOrdered'])->name('purchase-orders.mark-ordered');
            Route::post('purchase-orders/{purchase_order}/cancel', [PurchaseOrderController::class, 'cancel'])->name('purchase-orders.cancel');

            // 4. Goods Receiving & Supplier Returns
            Route::get('goods-receipts', [GoodsReceiptController::class, 'index'])->name('goods-receipts.index');
            Route::post('goods-receipts', [GoodsReceiptController::class, 'store'])->name('goods-receipts.store');
            Route::post('supplier-returns', [GoodsReceiptController::class, 'storeReturn'])->name('supplier-returns.store');

            // 5. Supplier Invoices & Payables & 6. Payments & 7. Reports
            Route::get('supplier-invoices', [SupplierPayableController::class, 'invoices'])->name('supplier-invoices.index');
            Route::post('supplier-invoices', [SupplierPayableController::class, 'storeInvoice'])->name('supplier-invoices.store');
            Route::get('supplier-payments', [SupplierPayableController::class, 'payments'])->name('supplier-payments.index');
            Route::post('supplier-payments', [SupplierPayableController::class, 'storePayment'])->name('supplier-payments.store');
            Route::get('supplier-reports', [SupplierPayableController::class, 'reports'])->name('supplier-reports.index');
        });

        // Settings
        Route::middleware('can:manage settings')->prefix('settings')->name('settings.')->group(function () {
            Route::get('/', [SettingController::class, 'index'])->name('index');
            Route::put('/', [SettingController::class, 'update'])->name('update');
            Route::put('/modules', [SettingController::class, 'updateModules'])->name('modules.update');
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

        Route::fallback(fn () => response()->view('errors.404', [], 404));
    });
