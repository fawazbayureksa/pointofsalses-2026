<?php

namespace Tests\Feature\Api;

use App\Models\Setting;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class ConfigTest extends TestCase
{
    use RefreshDatabase;

    private Tenant $tenant;
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = Tenant::factory()->create();
        $this->user   = User::factory()->create(['tenant_id' => $this->tenant->id]);
    }

    public function test_unauthenticated_user_cannot_access_config(): void
    {
        $this->getJson('/api/config')->assertStatus(401);
    }

    public function test_can_get_all_config_defaults(): void
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/config');

        $response->assertStatus(200)
            ->assertJsonStructure(['currency', 'tax_rate', 'receipt_footer', 'timezone'])
            ->assertJsonFragment(['currency' => 'IDR']);
    }

    public function test_can_get_specific_config_key_with_default(): void
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/config/currency');

        $response->assertStatus(200)
            ->assertJsonStructure(['key', 'value'])
            ->assertJsonFragment([
                'key'   => 'currency',
                'value' => 'IDR',
            ]);
    }

    public function test_can_get_stored_config_value(): void
    {
        Setting::factory()->forTenant($this->tenant)->create([
            'key'   => 'currency',
            'value' => 'USD',
            'type'  => 'string',
        ]);

        Cache::flush();

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/config/currency');

        $response->assertStatus(200)
            ->assertJsonFragment(['value' => 'USD']);
    }

    public function test_can_update_config_key(): void
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->putJson('/api/config/currency', [
                'value' => 'USD',
                'type'  => 'string',
            ]);

        $response->assertStatus(200)
            ->assertJsonFragment([
                'key'   => 'currency',
                'value' => 'USD',
            ]);

        $this->assertDatabaseHas('settings', [
            'tenant_id' => $this->tenant->id,
            'key'       => 'currency',
            'value'     => 'USD',
        ]);
    }

    public function test_can_update_config_with_integer_type(): void
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->putJson('/api/config/tax_rate', [
                'value' => '15',
                'type'  => 'integer',
            ]);

        $response->assertStatus(200)
            ->assertJsonFragment(['value' => 15]);
    }

    public function test_update_config_validates_required_value(): void
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->putJson('/api/config/currency', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['value']);
    }

    public function test_update_config_validates_type_field(): void
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->putJson('/api/config/currency', [
                'value' => 'USD',
                'type'  => 'invalid_type',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['type']);
    }

    public function test_config_is_scoped_to_tenant(): void
    {
        // Set config for another tenant
        $otherTenant = Tenant::factory()->create();
        $otherUser   = User::factory()->create(['tenant_id' => $otherTenant->id]);

        $this->actingAs($otherUser, 'sanctum')
            ->putJson('/api/config/currency', ['value' => 'EUR']);

        Cache::flush();

        // Current tenant should still have default
        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/config/currency');

        $response->assertStatus(200)
            ->assertJsonFragment(['value' => 'IDR']);
    }

    public function test_all_config_merges_stored_settings_with_defaults(): void
    {
        Setting::factory()->forTenant($this->tenant)->create([
            'key'   => 'currency',
            'value' => 'SGD',
            'type'  => 'string',
        ]);

        Cache::flush();

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/config');

        $response->assertStatus(200)
            ->assertJsonFragment(['currency' => 'SGD'])
            ->assertJsonStructure(['tax_rate', 'receipt_footer', 'timezone']);
    }
}
