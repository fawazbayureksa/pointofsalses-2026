<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<OrderItem>
 */
class OrderItemFactory extends Factory
{
    protected $model = OrderItem::class;

    public function definition(): array
    {
        $unitPrice      = $this->faker->randomFloat(2, 1000, 100000);
        $quantity       = $this->faker->randomFloat(3, 1, 10);
        $discountAmount = 0;
        $subtotal       = ($unitPrice * $quantity) - $discountAmount;

        return [
            'order_id'        => Order::factory(),
            'product_id'      => Product::factory(),
            'product_name'    => $this->faker->words(3, true),
            'product_sku'     => $this->faker->lexify('SKU-??????'),
            'unit_price'      => $unitPrice,
            'cost_price'      => $this->faker->randomFloat(2, 500, 50000),
            'quantity'        => $quantity,
            'discount_amount' => $discountAmount,
            'tax_amount'      => 0,
            'subtotal'        => $subtotal,
        ];
    }
}
