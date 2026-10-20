<?php

namespace Database\Factories;

use App\Enums\UserRole;
use App\Models\Shop;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $phone = '99890'.fake()->unique()->numerify('#######');

        return [
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'phone' => $phone,
            'login' => $phone,
            'password' => static::$password ??= Hash::make('password'),
            'role' => UserRole::Owner,
            'is_active' => true,
            'remember_token' => Str::random(10),
        ];
    }

    public function admin(): static
    {
        return $this->state(fn (): array => [
            'role' => UserRole::Admin,
            'login' => 'admin_'.fake()->unique()->numerify('####'),
            'phone' => null,
        ]);
    }

    public function owner(): static
    {
        return $this->state(fn (): array => ['role' => UserRole::Owner]);
    }

    public function seller(?Shop $shop = null): static
    {
        return $this->state(fn (array $attributes): array => [
            'role' => UserRole::Seller,
            'login' => 's'.substr((string) $attributes['phone'], -9),
            'shop_id' => $shop ?? Shop::factory(),
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (): array => ['is_active' => false]);
    }
}
