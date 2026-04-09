<?php

namespace Database\Factories;

use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Tenant>
 */
class TenantFactory extends Factory
{
    protected $model = Tenant::class;

    public function definition(): array
    {
        return [
            'name'          => $this->faker->company(),
            'slug'          => $this->faker->unique()->slug(),
            'email'         => $this->faker->companyEmail(),
            'phone'         => $this->faker->phoneNumber(),
            'contact_name'  => $this->faker->name(),
            'business_type' => 'retail',
            'plan'          => 'basic',
            'status'        => 'active',
        ];
    }

    public function active(): static
    {
        return $this->state(['status' => 'active']);
    }

    public function suspended(): static
    {
        return $this->state(['status' => 'suspended']);
    }
}
