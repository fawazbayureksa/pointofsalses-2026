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

if (! function_exists('edc_enabled')) {
    /**
     * Determine whether Bank EDC Terminal integration is enabled for the current user/tenant.
     */
    function edc_enabled(): bool
    {
        if (! Auth::check()) {
            return false;
        }

        $user = Auth::user();

        if ($user->tenant) {
            return $user->tenant->isEdcEnabled();
        }

        return filter_var(setting('edc_enabled', false), FILTER_VALIDATE_BOOLEAN);
    }
}

if (! function_exists('edc_config')) {
    /**
     * Retrieve the full EDC configuration array for the current user/tenant.
     */
    function edc_config(): array
    {
        if (Auth::check() && Auth::user()->tenant) {
            return Auth::user()->tenant->edcConfig();
        }

        $enabled = filter_var(setting('edc_enabled', false), FILTER_VALIDATE_BOOLEAN);
        $mode = (string) setting('edc_connection_mode', 'semi_integrated');
        $banksRaw = (string) setting('edc_banks', 'BCA, Mandiri, BRI, BNI, CIMB Niaga, Permata');
        $banks = array_values(array_filter(array_map('trim', explode(',', $banksRaw))));
        $requireRef = filter_var(setting('edc_require_reference', true), FILTER_VALIDATE_BOOLEAN);
        $baudRate = (int) setting('edc_serial_baud_rate', 9600);

        return [
            'enabled' => $enabled,
            'connection_mode' => in_array($mode, ['semi_integrated', 'ecr_serial', 'both'], true) ? $mode : 'semi_integrated',
            'banks' => ! empty($banks) ? $banks : ['BCA', 'Mandiri', 'BRI', 'BNI'],
            'banks_raw' => implode(', ', ! empty($banks) ? $banks : ['BCA', 'Mandiri', 'BRI', 'BNI']),
            'require_reference' => $requireRef,
            'serial_baud_rate' => $baudRate > 0 ? $baudRate : 9600,
        ];
    }
}

if (! function_exists('format_currency')) {
    /**
     * Format a numeric/nominal value as currency (e.g., "Rp 25.000").
     */
    function format_currency(float|int|string|null $amount): string
    {
        return 'Rp '.number_format((float) ($amount ?? 0), 0, ',', '.');
    }
}
