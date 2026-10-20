<?php

namespace App\Services\Contracts;

use App\DTOs\CredentialsData;

interface SellerCredentialGeneratorInterface
{
    /**
     * Generate a unique login and a random password for a seller with the given phone.
     */
    public function generate(string $phone, ?int $exceptUserId = null): CredentialsData;

    public function generatePassword(): string;
}
