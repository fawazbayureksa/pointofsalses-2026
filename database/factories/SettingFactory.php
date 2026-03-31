<?php

namespace Database\Factories;

use App\Models\Setting;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Setting>
 */
class SettingFactory extends Factory
{
    protected $model = Setting::class;

    public function definition(): array
    {
        return [
            'tenant_id' => Tenant::factory(),
            'key'       => $this->faker->unique()->word(),
            'value'     => $this->faker->word(),
            'type'      => 'string',
            'is_public' => false,
            'group'     => 'general',
        ];
    }

    public function forTenant(Tenant $tenant): static
    {
        return $this->state(['tenant_id' => $tenant->id]);
    }

    public function asInteger(int $value): static
    {
        return $this->state(['value' => (string) $value, 'type' => 'integer']);
    }

    public function asBoolean(bool $value): static
    {
        return $this->state(['value' => $value ? 'true' : 'false', 'type' => 'boolean']);
    }

    public function asJson(array $value): static
    {
        return $this->state(['value' => json_encode($value), 'type' => 'json']);
    }
}
