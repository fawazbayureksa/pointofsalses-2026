<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        return [
            'tenant_id'   => Tenant::factory(),
            'name'        => $this->faker->words(3, true),
            'sku'         => $this->faker->unique()->lexify('SKU-??????'),
            'barcode'     => $this->faker->optional()->ean13(),
            'description' => $this->faker->optional()->sentence(),
            'price'       => $this->faker->randomFloat(2, 1000, 100000),
            'cost_price'  => $this->faker->randomFloat(2, 500, 50000),
            'unit'        => 'pcs',
            'is_active'   => true,
            'track_stock' => true,
        ];
    }

    public function forTenant(Tenant $tenant): static
    {
        return $this->state(['tenant_id' => $tenant->id]);
    }

    public function inactive(): static
    {
        return $this->state(['is_active' => false]);
    }

    public function withoutStockTracking(): static
    {
        return $this->state(['track_stock' => false]);
    }
}
