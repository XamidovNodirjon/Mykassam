<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SaleItem>
 */
class SaleItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $quantity = fake()->numberBetween(1, 5);
        $unitPrice = fake()->numberBetween(10, 100) * 1000;

        return [
            'sale_id' => Sale::factory(),
            'product_id' => Product::factory(),
            'product_name' => ucfirst(fake()->word()).' '.fake()->word(),
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
            'cost_price' => (int) round($unitPrice * 0.75, -2),
            'total' => $quantity * $unitPrice,
        ];
    }
}
