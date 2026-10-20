<?php

namespace Database\Factories;

use App\Models\Plan;
use App\Models\Shop;
use App\Models\ShopType;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Shop>
 */
class ShopFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'owner_id' => User::factory()->owner(),
            'shop_type_id' => ShopType::factory(),
            'name' => fake()->company(),
            'address' => fake()->streetAddress(),
            'phone' => '99871'.fake()->numerify('#######'),
            'cashback_percent' => 0,
            'is_active' => true,
            'plan_id' => fn (): int => self::planId('business-plus'),
            'on_trial' => true,
            'subscription_ends_at' => now()->addMonth(),
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (): array => ['is_active' => false]);
    }

    /**
     * A paid month on one of the standard plans: "start", "business" or "business-plus".
     */
    public function subscribed(string $planSlug = 'business'): static
    {
        return $this->state(fn (): array => [
            'plan_id' => self::planId($planSlug),
            'on_trial' => false,
            'subscription_ends_at' => now()->addMonth(),
        ]);
    }

    public function onPlan(Plan $plan): static
    {
        return $this->state(fn (): array => ['plan_id' => $plan->id]);
    }

    public function subscriptionExpired(): static
    {
        return $this->state(fn (): array => ['subscription_ends_at' => now()->subDay()]);
    }

    private static function planId(string $slug): int
    {
        return Plan::query()->where('slug', $slug)->valueOrFail('id');
    }
}
