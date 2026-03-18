<?php

namespace Database\Seeders;

use App\Models\Setting;
use App\Models\Tenant;
use Illuminate\Database\Seeder;

class TenantDefaultSettingsSeeder extends Seeder
{
    private array $defaults = [
        'currency'        => ['value' => 'IDR',                         'type' => 'string'],
        'tax_rate'        => ['value' => '11',                          'type' => 'integer'],
        'receipt_footer'  => ['value' => 'Thank you for your purchase!', 'type' => 'string'],
        'timezone'        => ['value' => 'Asia/Jakarta',                 'type' => 'string'],
        'date_format'     => ['value' => 'd/m/Y',                        'type' => 'string'],
        'low_stock_alert' => ['value' => 'true',                         'type' => 'boolean'],
    ];

    public function run(): void
    {
        foreach (Tenant::all() as $tenant) {
            foreach ($this->defaults as $key => $config) {
                Setting::firstOrCreate(
                    ['tenant_id' => $tenant->id, 'key' => $key],
                    [
                        'value'     => $config['value'],
                        'type'      => $config['type'],
                        'is_public' => false,
                        'group'     => 'general',
                    ]
                );
            }
        }

        $this->command->info('Default settings seeded for all tenants.');
    }
}
