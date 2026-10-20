<?php

namespace App\DTOs;

use App\Http\Requests\Owner\SellerRequest;

/**
 * Shared by seller create and update. On update a null password keeps the current one.
 */
final readonly class SellerData
{
    public function __construct(
        public string $firstName,
        public string $lastName,
        public string $phone,
        public string $login,
        public ?string $password,
        public bool $isActive,
    ) {}

    public static function fromRequest(SellerRequest $request): self
    {
        return new self(
            firstName: $request->string('first_name')->trim()->toString(),
            lastName: $request->string('last_name')->trim()->toString(),
            phone: $request->string('phone')->toString(),
            login: $request->string('login')->trim()->lower()->toString(),
            password: $request->filled('password') ? $request->string('password')->toString() : null,
            isActive: $request->boolean('is_active'),
        );
    }

    /**
     * @return array<string, string|bool>
     */
    public function toArray(): array
    {
        $attributes = [
            'first_name' => $this->firstName,
            'last_name' => $this->lastName,
            'phone' => $this->phone,
            'login' => $this->login,
            'is_active' => $this->isActive,
        ];

        if ($this->password !== null) {
            $attributes['password'] = $this->password;
        }

        return $attributes;
    }
}
