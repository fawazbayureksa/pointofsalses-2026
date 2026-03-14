<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class TenantDefaultSettingsSeeder extends Seeder
{
    private array $defaults = [
        ['key' => 'currency',        'value' => 'IDR',                          'type' => 'string',  'is_public' => true],
        ['key' => 'tax_rate',        'value' => '11',                           'type' => 'integer', 'is_public' => false],
        ['key' => 'receipt_footer',  'value' => 'Thank you for your purchase!', 'type' => 'string',  'is_public' => true],
        ['key' => 'timezone',        'value' => 'Asia/Jakarta',                 'type' => 'string',  'is_public' => true],
        ['key' => 'date_format',     'value' => 'd/m/Y',                        'type' => 'string',  'is_public' => true],
        ['key' => 'low_stock_alert', 'value' => 'true',                         'type' => 'boolean', 'is_public' => false],
    ];

    public function run(): void
    {
        $tenantId = tenant('id');

        foreach ($this->defaults as $setting) {
            Setting::firstOrCreate(
                ['tenant_id' => $tenantId, 'key' => $setting['key']],
                array_merge($setting, ['tenant_id' => $tenantId]),
            );
        }

        $this->command->info('Default tenant settings seeded.');
    }
}
