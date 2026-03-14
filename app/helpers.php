<?php

declare(strict_types=1);

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
