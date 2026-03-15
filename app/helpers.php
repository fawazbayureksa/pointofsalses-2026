<?php

declare(strict_types=1);

use App\Models\Setting;
use App\Services\ConfigService;

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
