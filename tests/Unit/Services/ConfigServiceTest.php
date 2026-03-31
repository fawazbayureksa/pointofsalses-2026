<?php

namespace Tests\Unit\Services;

use App\Models\Setting;
use App\Models\Tenant;
use App\Models\User;
use App\Services\ConfigService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class ConfigServiceTest extends TestCase
{
    use RefreshDatabase;

    private ConfigService $service;
    private Tenant $tenant;
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = app(ConfigService::class);
        $this->tenant  = Tenant::factory()->create();
        $this->user    = User::factory()->create(['tenant_id' => $this->tenant->id]);

        $this->actingAs($this->user);
    }

    // ── get ──────────────────────────────────────────────────────────────────

    public function test_get_returns_default_value_when_no_setting_exists(): void
    {
        Cache::flush();
        $value = $this->service->get('currency');

        $this->assertEquals('IDR', $value);
    }

    public function test_get_returns_stored_setting_value(): void
    {
        Setting::factory()->forTenant($this->tenant)->create([
            'key'   => 'currency',
            'value' => 'USD',
            'type'  => 'string',
        ]);

        Cache::flush();

        $value = $this->service->get('currency');
        $this->assertEquals('USD', $value);
    }

    public function test_get_returns_provided_default_when_key_unknown(): void
    {
        Cache::flush();
        $value = $this->service->get('nonexistent_key', 'my_default');

        $this->assertEquals('my_default', $value);
    }

    public function test_get_returns_null_for_completely_unknown_key(): void
    {
        Cache::flush();
        $value = $this->service->get('no_such_key');

        $this->assertNull($value);
    }

    public function test_get_caches_value_after_first_access(): void
    {
        Cache::flush();

        $this->service->get('currency');

        // Value is now cached; even if DB changes, cached value is returned
        Setting::factory()->forTenant($this->tenant)->create([
            'key'   => 'currency',
            'value' => 'EUR',
        ]);

        $cached = $this->service->get('currency');
        // Should still be default IDR because it's cached
        $this->assertEquals('IDR', $cached);
    }

    // ── set ──────────────────────────────────────────────────────────────────

    public function test_set_creates_new_setting(): void
    {
        Cache::flush();
        $setting = $this->service->set('tax_rate', '15', 'string');

        $this->assertInstanceOf(Setting::class, $setting);
        $this->assertDatabaseHas('settings', [
            'tenant_id' => $this->tenant->id,
            'key'       => 'tax_rate',
            'value'     => '15',
        ]);
    }

    public function test_set_updates_existing_setting(): void
    {
        Setting::factory()->forTenant($this->tenant)->create([
            'key'   => 'currency',
            'value' => 'IDR',
            'type'  => 'string',
        ]);
        Cache::flush();

        $this->service->set('currency', 'USD', 'string');

        $this->assertDatabaseHas('settings', [
            'tenant_id' => $this->tenant->id,
            'key'       => 'currency',
            'value'     => 'USD',
        ]);
        $this->assertDatabaseCount('settings', 1);
    }

    public function test_set_clears_cache_for_key(): void
    {
        // Seed the cache
        Cache::flush();
        $this->service->get('currency'); // caches 'IDR'

        $this->service->set('currency', 'SGD');
        Cache::flush(); // Simulate the forget + re-read

        $fresh = $this->service->get('currency');
        $this->assertEquals('SGD', $fresh);
    }

    // ── all ──────────────────────────────────────────────────────────────────

    public function test_all_returns_default_values_when_no_settings_stored(): void
    {
        $all = $this->service->all();

        $this->assertArrayHasKey('currency', $all);
        $this->assertArrayHasKey('tax_rate', $all);
        $this->assertArrayHasKey('timezone', $all);
        $this->assertEquals('IDR', $all['currency']);
    }

    public function test_all_overrides_defaults_with_stored_settings(): void
    {
        Setting::factory()->forTenant($this->tenant)->create([
            'key'   => 'currency',
            'value' => 'MYR',
            'type'  => 'string',
        ]);

        $all = $this->service->all();

        $this->assertEquals('MYR', $all['currency']);
        // Other defaults should remain
        $this->assertEquals('11', $all['tax_rate']);
    }

    public function test_all_returns_typed_values_for_stored_settings(): void
    {
        Setting::factory()->forTenant($this->tenant)->create([
            'key'   => 'tax_rate',
            'value' => '20',
            'type'  => 'integer',
        ]);

        $all = $this->service->all();

        $this->assertSame(20, $all['tax_rate']);
    }
}
