<?php

namespace App\Enums;

enum SmsPurpose: string
{
    case Registration = 'registration';
    case PasswordReset = 'password_reset';
    case DebtReminder = 'debt_reminder';

    public function label(): string
    {
        return match ($this) {
            self::Registration => "Ro'yxatdan o'tish",
            self::PasswordReset => 'Parolni tiklash',
            self::DebtReminder => 'Qarz eslatmasi',
        };
    }
}
