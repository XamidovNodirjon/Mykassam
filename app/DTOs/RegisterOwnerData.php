<?php

namespace App\DTOs;

use App\Http\Requests\Auth\RegisterOwnerRequest;

final readonly class RegisterOwnerData
{
    public function __construct(
        public string $firstName,
        public string $phone,
        public string $code,
        public string $password,
    ) {}

    public static function fromRequest(RegisterOwnerRequest $request): self
    {
        return new self(
            firstName: $request->string('first_name')->trim()->toString(),
            phone: $request->string('phone')->toString(),
            code: $request->string('code')->toString(),
            password: $request->string('password')->toString(),
        );
    }
}
