<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\Payment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Payment>
 */
class PaymentFactory extends Factory
{
    protected $model = Payment::class;

    public function definition(): array
    {
        $amount = $this->faker->randomFloat(2, 10000, 500000);

        return [
            'order_id'         => Order::factory(),
            'payment_method'   => $this->faker->randomElement(['cash', 'card', 'qris', 'transfer']),
            'amount'           => $amount,
            'change_amount'    => 0,
            'status'           => 'completed',
            'reference_number' => null,
            'paid_at'          => now(),
        ];
    }

    public function cash(): static
    {
        return $this->state(['payment_method' => 'cash']);
    }

    public function completed(): static
    {
        return $this->state(['status' => 'completed', 'paid_at' => now()]);
    }

    public function refunded(): static
    {
        return $this->state(['status' => 'refunded']);
    }
}
