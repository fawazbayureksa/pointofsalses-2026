<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\Outlet;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Order>
 */
class OrderFactory extends Factory
{
    protected $model = Order::class;

    public function definition(): array
    {
        $subtotal = $this->faker->randomFloat(2, 10000, 1000000);

        return [
            'tenant_id'      => Tenant::factory(),
            'outlet_id'      => Outlet::factory(),
            'user_id'        => User::factory(),
            'order_number'   => 'ORD-' . $this->faker->unique()->numerify('########'),
            'status'         => 'pending',
            'subtotal'       => $subtotal,
            'tax_amount'     => 0,
            'discount_amount'=> 0,
            'total_amount'   => $subtotal,
            'payment_status' => 'unpaid',
        ];
    }

    public function pending(): static
    {
        return $this->state(['status' => 'pending', 'payment_status' => 'unpaid']);
    }

    public function completed(): static
    {
        return $this->state([
            'status'         => 'completed',
            'payment_status' => 'paid',
            'completed_at'   => now(),
        ]);
    }

    public function cancelled(): static
    {
        return $this->state(['status' => 'cancelled']);
    }
}
