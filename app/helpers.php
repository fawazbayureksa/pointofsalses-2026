<?php

declare(strict_types=1);

use App\Models\Setting;
use App\Services\ConfigService;
use Illuminate\Support\Facades\Auth;

if (! function_exists('configService')) {
    /**
     * Get the ConfigService instance (or retrieve a value directly).
     *
     * Usage:
     *   configService()               → ConfigService instance
     *   configService()->get('tax_rate')  → '11'
     *   configService('tax_rate')     → '11'
     */
    function configService(?string $key = null, mixed $default = null): mixed
    {
        $service = app(ConfigService::class);

        if ($key !== null) {
            return $service->get($key, $default);
        }

        return $service;
    }
}

if (! function_exists('setting')) {
    /**
     * Get a setting value from the database, with an optional default.
     *
     * Usage:
     *   setting('app_name')           → 'My POS'
     *   setting('timezone', 'UTC')    → 'UTC'
     */
    function setting(string $key, mixed $default = null): mixed
    {
        $record = Setting::where('key', $key)->first();

        if (! $record) {
            return $default;
        }

        return $record->getTypedValue() ?? $default;
    }
}

if (! function_exists('supplier_management_enabled')) {
    /**
     * Determine whether the optional Supplier Management module is enabled for the current user/tenant.
     */
    function supplier_management_enabled(): bool
    {
        if (! Auth::check()) {
            return false;
        }

        $user = Auth::user();

        if ($user->hasRole('super_admin') && ! $user->tenant_id) {
            return true;
        }

        if ($user->tenant) {
            return $user->tenant->isSupplierManagementEnabled();
        }

        return (bool) setting('supplier_management_enabled', true);
    }
}
