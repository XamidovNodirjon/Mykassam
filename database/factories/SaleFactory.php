<?php

namespace Database\Factories;

use App\Enums\PaymentMethod;
use App\Models\Sale;
use App\Models\Shop;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Sale>
 */
class SaleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $totalCost = fake()->numberBetween(10, 500) * 1000;
        $totalAmount = (int) round($totalCost * fake()->randomFloat(2, 1.1, 1.4), -2);

        return [
            'shop_id' => Shop::factory(),
            'seller_id' => fn (array $attributes) => User::factory()->seller()->state(['shop_id' => $attributes['shop_id']]),
            'total_amount' => $totalAmount,
            'total_cost' => $totalCost,
            'profit' => $totalAmount - $totalCost,
            'items_count' => fake()->numberBetween(1, 5),
            'payment_method' => fake()->randomElement(PaymentMethod::immediate()),
        ];
    }
}
