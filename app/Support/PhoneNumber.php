<?php

namespace App\Support;

/**
 * Normalizes Uzbek phone numbers to the canonical "998XXXXXXXXX" form.
 */
final class PhoneNumber
{
    public const PATTERN = '/^998\d{9}$/';

    public static function normalize(?string $phone): ?string
    {
        if ($phone === null) {
            return null;
        }

        $digits = preg_replace('/\D+/', '', $phone) ?? '';

        if (strlen($digits) === 9) {
            $digits = '998'.$digits;
        }

        return $digits === '' ? null : $digits;
    }

    public static function isValid(?string $phone): bool
    {
        return $phone !== null && preg_match(self::PATTERN, $phone) === 1;
    }
}
