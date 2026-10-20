<?php

namespace App\DTOs;

use App\Http\Requests\Auth\LoginRequest;

final readonly class LoginData
{
    public function __construct(
        public string $login,
        public string $password,
        public bool $remember,
    ) {}

    public static function fromRequest(LoginRequest $request): self
    {
        return new self(
            login: $request->string('login')->toString(),
            password: $request->string('password')->toString(),
            remember: $request->boolean('remember'),
        );
    }
}
