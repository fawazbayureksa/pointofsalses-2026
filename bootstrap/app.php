<?php

use App\Http\Middleware\CheckPermission;
use App\Http\Middleware\EnsureSubscriptionActive;
use App\Http\Middleware\EnsureSupplierManagementEnabled;
use App\Http\Middleware\EnsureTenantActive;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Spatie\Permission\Middleware\RoleMiddleware;
use Spatie\Permission\Middleware\RoleOrPermissionMiddleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withEvents(discover: false)
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'tenant.active' => EnsureTenantActive::class,
            'subscription.active' => EnsureSubscriptionActive::class,
            'supplier.enabled' => EnsureSupplierManagementEnabled::class,
            'permission' => CheckPermission::class,
            'role' => RoleMiddleware::class,
            'role_or_permission' => RoleOrPermissionMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
