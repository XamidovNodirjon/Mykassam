<?php

namespace App\DTOs;

use App\Http\Requests\Auth\ResetPasswordRequest;

final readonly class ResetPasswordData
{
    public function __construct(
        public string $phone,
        public string $code,
        public string $password,
    ) {}

    public static function fromRequest(ResetPasswordRequest $request): self
    {
        return new self(
            phone: $request->string('phone')->toString(),
            code: $request->string('code')->toString(),
            password: $request->string('password')->toString(),
        );
    }
}
