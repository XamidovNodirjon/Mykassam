<?php

namespace App\DTOs;

use App\Http\Requests\Admin\UserRequest;

/**
 * Account changes made by an administrator. A null password keeps the current one.
 */
final readonly class AdminUserData
{
    public function __construct(
        public string $firstName,
        public ?string $lastName,
        public ?string $phone,
        public ?string $login,
        public ?string $password,
        public bool $isActive,
    ) {}

    public static function fromRequest(UserRequest $request): self
    {
        return new self(
            firstName: $request->string('first_name')->trim()->toString(),
            lastName: $request->string('last_name')->trim()->toString() ?: null,
            phone: $request->string('phone')->toString() ?: null,
            login: $request->string('login')->toString() ?: null,
            password: $request->filled('password') ? $request->string('password')->toString() : null,
            isActive: $request->boolean('is_active'),
        );
    }
}
