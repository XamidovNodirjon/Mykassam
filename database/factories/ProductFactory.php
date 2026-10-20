<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\Shop;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $costPrice = fake()->numberBetween(5, 500) * 1000;
        $minPrice = (int) round($costPrice * 1.1, -2);

        return [
            'shop_id' => Shop::factory(),
            'name' => ucfirst(fake()->word()).' '.fake()->word(),
            'barcode' => fake()->unique()->ean13(),
            'cost_price' => $costPrice,
            'min_price' => $minPrice,
            'price' => (int) round($costPrice * 1.3, -2),
            'stock' => fake()->numberBetween(10, 200),
            'is_active' => true,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (): array => ['is_active' => false]);
    }

    public function outOfStock(): static
    {
        return $this->state(fn (): array => ['stock' => 0]);
    }
}
