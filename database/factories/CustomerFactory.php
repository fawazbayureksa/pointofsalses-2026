<?php

namespace Database\Factories;

use App\Models\Customer;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Customer>
 */
class CustomerFactory extends Factory
{
    protected $model = Customer::class;

    public function definition(): array
    {
        return [
            'tenant_id'      => Tenant::factory(),
            'name'           => $this->faker->name(),
            'email'          => $this->faker->unique()->safeEmail(),
            'phone'          => $this->faker->phoneNumber(),
            'address'        => $this->faker->optional()->address(),
            'loyalty_points' => 0,
            'is_active'      => true,
        ];
    }

    public function forTenant(Tenant $tenant): static
    {
        return $this->state(['tenant_id' => $tenant->id]);
    }
}
