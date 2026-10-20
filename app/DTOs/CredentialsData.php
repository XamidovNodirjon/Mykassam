<?php

namespace App\DTOs;

final readonly class CredentialsData
{
    public function __construct(
        public string $login,
        public string $password,
    ) {}

    /**
     * @return array{login: string, password: string}
     */
    public function toArray(): array
    {
        return [
            'login' => $this->login,
            'password' => $this->password,
        ];
    }
}
