<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;

class ConfigService
{
    /**
     * Default values for tenant settings.
     */
    private array $defaults = [
        'currency'        => 'IDR',
        'tax_rate'        => '11',
        'receipt_footer'  => 'Thank you for your purchase!',
        'timezone'        => 'Asia/Jakarta',
        'date_format'     => 'd/m/Y',
        'low_stock_alert' => 'true',
    ];

    /**
     * Retrieve a tenant config value by key.
     *
     * Example: $configService->get('tax_rate')
     */
    public function get(string $key, mixed $default = null): mixed
    {
        $cacheKey = $this->cacheKey($key);

        return Cache::remember($cacheKey, now()->addHour(), function () use ($key, $default) {
            $setting = Setting::where('key', $key)->first();

            if (! $setting) {
                return $default ?? ($this->defaults[$key] ?? null);
            }

            return $setting->getTypedValue();
        });
    }

    /**
     * Set a tenant config value.
     */
    public function set(string $key, mixed $value, string $type = 'string'): Setting
    {
        $setting = Setting::updateOrCreate(
            ['tenant_id' => tenant('id'), 'key' => $key],
            ['value' => $value, 'type' => $type, 'tenant_id' => tenant('id')],
        );

        Cache::forget($this->cacheKey($key));

        return $setting;
    }

    /**
     * Get all settings for the current tenant as a key->value array.
     */
    public function all(): array
    {
        $settings = Setting::all()->keyBy('key');

        return collect($this->defaults)
            ->merge($settings->map(fn($s) => $s->getTypedValue()))
            ->toArray();
    }

    /**
     * Bust cache for a specific key.
     */
    public function forget(string $key): void
    {
        Cache::forget($this->cacheKey($key));
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    private function cacheKey(string $key): string
    {
        return 'config.' . tenant('id') . '.' . $key;
    }
}
