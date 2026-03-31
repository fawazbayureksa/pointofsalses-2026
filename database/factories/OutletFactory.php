<?php

namespace Database\Factories;

use App\Models\Outlet;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Outlet>
 */
class OutletFactory extends Factory
{
    protected $model = Outlet::class;

    public function definition(): array
    {
        return [
            'tenant_id' => Tenant::factory(),
            'name'      => $this->faker->company(),
            'code'      => $this->faker->unique()->lexify('???'),
            'phone'     => $this->faker->phoneNumber(),
            'email'     => $this->faker->companyEmail(),
            'address'   => $this->faker->address(),
            'city'      => $this->faker->city(),
            'is_active' => true,
        ];
    }

    public function forTenant(Tenant $tenant): static
    {
        return $this->state(['tenant_id' => $tenant->id]);
    }
}
