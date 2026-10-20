<?php

namespace Database\Factories;

use App\Models\Customer;
use App\Models\Debt;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Debt>
 */
class DebtFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'customer_id' => Customer::factory(),
            'shop_id' => fn (array $attributes): int => Customer::query()->whereKey($attributes['customer_id'])->firstOrFail()->shop_id,
            'amount' => fake()->numberBetween(5, 100) * 10_000,
            'paid_amount' => 0,
            'due_date' => today()->addWeek(),
        ];
    }

    public function dueToday(): static
    {
        return $this->state(fn (): array => ['due_date' => today()]);
    }

    public function overdue(): static
    {
        return $this->state(fn (): array => ['due_date' => today()->subDays(3)]);
    }

    public function paid(): static
    {
        return $this->state(fn (array $attributes): array => ['paid_amount' => $attributes['amount']]);
    }
}
