<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\TenantController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RegisteredUserController;
use Illuminate\Support\Facades\Route;

// -------------------------------------------------------------------------
// Central domain routes — wrapped per-domain so they have unique route keys
// and are never overwritten by tenant routes registered in tenant.php.
// -------------------------------------------------------------------------
foreach (config('tenancy.central_domains') as $domain) {
    Route::domain($domain)->group(function () {

        Route::get('/', function () {
            return view('welcome');
        });

        // -----------------------------------------------------------------
        // Central Auth (super admins & landlord access)
        // -----------------------------------------------------------------

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

        // -----------------------------------------------------------------
        // Central Admin (super admin only — tenant management)
        // -----------------------------------------------------------------

        Route::middleware(['auth'])
            ->prefix('admin')
            ->name('admin.')
            ->group(function () {

                Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

                // Route::middleware(['role:super_admin'])->group(function () {
                    Route::resource('tenants', TenantController::class);
                // });

                Route::get('/profile', [UserController::class, 'profile'])->name('profile');
                Route::put('/profile', [UserController::class, 'updateProfile'])->name('profile.update');
                Route::put('/password', [UserController::class, 'updatePassword'])->name('password.change');

                Route::fallback(function () {
                    return response()->view('errors.404', [], 404);
                });
            });
    });
}
