<?php

namespace Database\Factories;

use App\Enums\PlanFeature;
use App\Models\Plan;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * Standard plans (start, business, business-plus) are created by their migration,
 * so most tests look them up by slug instead of using this factory.
 *
 * @extends Factory<Plan>
 */
class PlanFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = ucfirst(fake()->unique()->word());

        return [
            'slug' => Str::slug($name).'-'.fake()->unique()->numberBetween(1, 9999),
            'name' => $name,
            'price' => fake()->numberBetween(5, 50) * 10_000,
            'max_sellers' => null,
            'max_products' => null,
            'features' => [],
            'is_active' => true,
            'sort_order' => 10,
        ];
    }

    /**
     * @param  list<PlanFeature>  $features
     */
    public function withFeatures(array $features): static
    {
        return $this->state(fn (): array => ['features' => $features]);
    }
}
