<?php

namespace Database\Factories;

use App\Enums\SmsPurpose;
use App\Enums\SmsStatus;
use App\Models\SmsMessage;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SmsMessage>
 */
class SmsMessageFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'phone' => '99890'.fake()->numerify('#######'),
            'purpose' => SmsPurpose::Registration,
            'status' => SmsStatus::Sent,
            'provider_message_id' => (string) fake()->randomNumber(5),
            'sent_at' => now(),
        ];
    }

    public function failed(): static
    {
        return $this->state(fn (): array => [
            'status' => SmsStatus::Failed,
            'provider_message_id' => null,
            'error' => 'USMS xatosi (HTTP 402): Недостаточно средств',
            'sent_at' => null,
        ]);
    }
}
