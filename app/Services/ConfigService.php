<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class ConfigService
{
    private array $defaults = [
        'currency'        => 'IDR',
        'tax_rate'        => '11',
        'receipt_footer'  => 'Thank you for your purchase!',
        'timezone'        => 'Asia/Jakarta',
        'date_format'     => 'd/m/Y',
        'low_stock_alert' => 'true',
    ];

    public function get(string $key, mixed $default = null): mixed
    {
        $tenantId = $this->tenantId();
        $cacheKey = 'config.' . $tenantId . '.' . $key;

        return Cache::remember($cacheKey, now()->addHour(), function () use ($key, $default) {
            $setting = Setting::where('key', $key)->first();

            if (! $setting) {
                return $default ?? ($this->defaults[$key] ?? null);
            }

            return $setting->getTypedValue();
        });
    }

    public function set(string $key, mixed $value, string $type = 'string'): Setting
    {
        $tenantId = $this->tenantId();

        $setting = Setting::updateOrCreate(
            ['tenant_id' => $tenantId, 'key' => $key],
            ['value' => $value, 'type' => $type]
        );

        Cache::forget('config.' . $tenantId . '.' . $key);

        return $setting;
    }

    public function all(): array
    {
        $settings = Setting::all()->keyBy('key');

        return collect($this->defaults)
            ->merge($settings->map(fn($s) => $s->getTypedValue()))
            ->toArray();
    }

    public function forget(string $key): void
    {
        Cache::forget('config.' . $this->tenantId() . '.' . $key);
    }

    private function tenantId(): ?int
    {
        return Auth::check() ? Auth::user()->tenant_id : null;
    }
}
