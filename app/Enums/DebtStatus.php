<?php

namespace App\Enums;

enum DebtStatus: string
{
    case Open = 'open';
    case Overdue = 'overdue';
    case Paid = 'paid';

    public function label(): string
    {
        return match ($this) {
            self::Open => "To'lanmagan",
            self::Overdue => "Muddati o'tgan",
            self::Paid => "To'langan",
        };
    }
}
