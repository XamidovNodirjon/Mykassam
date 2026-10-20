<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\StockReceipt;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<StockReceipt>
 */
class StockReceiptFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'product_id' => Product::factory(),
            'shop_id' => fn (array $attributes): int => Product::query()->whereKey($attributes['product_id'])->firstOrFail()->shop_id,
            'product_name' => fn (array $attributes): string => Product::query()->whereKey($attributes['product_id'])->firstOrFail()->name,
            'quantity' => fake()->numberBetween(1, 100),
            'cost_price' => fake()->numberBetween(5, 500) * 1000,
        ];
    }
}
