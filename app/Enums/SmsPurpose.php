<?php

namespace App\Enums;

enum SmsPurpose: string
{
    case Registration = 'registration';
    case PasswordReset = 'password_reset';
    case DebtReminder = 'debt_reminder';

    /**
     * Messages that carry a one-time code. The code is never stored, so such a message is
     * "resent" by issuing a new code.
     */
    public function isVerificationCode(): bool
    {
        return $this !== self::DebtReminder;
    }

    public function label(): string
    {
        return match ($this) {
            self::Registration => "Ro'yxatdan o'tish",
            self::PasswordReset => 'Parolni tiklash',
            self::DebtReminder => 'Qarz eslatmasi',
        };
    }
}
