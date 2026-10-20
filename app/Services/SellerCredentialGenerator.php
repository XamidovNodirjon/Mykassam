<?php

namespace App\Services;

use App\DTOs\CredentialsData;
use App\Repositories\Contracts\UserRepositoryInterface;
use App\Services\Contracts\SellerCredentialGeneratorInterface;
use App\Support\PhoneNumber;

/**
 * Builds seller logins like "s901234567" from the phone number and adds a numeric suffix on collision.
 */
class SellerCredentialGenerator implements SellerCredentialGeneratorInterface
{
    private const PASSWORD_LENGTH = 8;

    /**
     * Characters that are easy to read and type on a phone keyboard (no 0/O, 1/l/I).
     */
    private const PASSWORD_ALPHABET = 'abcdefghjkmnpqrstuvwxyz23456789';

    public function __construct(private UserRepositoryInterface $users) {}

    public function generate(string $phone, ?int $exceptUserId = null): CredentialsData
    {
        return new CredentialsData(
            login: $this->uniqueLogin($phone, $exceptUserId),
            password: $this->generatePassword(),
        );
    }

    public function generatePassword(): string
    {
        $alphabetLength = strlen(self::PASSWORD_ALPHABET);
        $password = '';

        for ($i = 0; $i < self::PASSWORD_LENGTH; $i++) {
            $password .= self::PASSWORD_ALPHABET[random_int(0, $alphabetLength - 1)];
        }

        return $password;
    }

    private function uniqueLogin(string $phone, ?int $exceptUserId): string
    {
        $digits = PhoneNumber::normalize($phone) ?? '';
        $base = 's'.substr($digits, -9);
        $login = $base;
        $suffix = 1;

        while ($this->users->loginExists($login, $exceptUserId)) {
            $suffix++;
            $login = "{$base}_{$suffix}";
        }

        return $login;
    }
}
