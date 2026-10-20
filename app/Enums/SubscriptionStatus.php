<?php

namespace App\Enums;

enum SubscriptionStatus: string
{
    case Trial = 'trial';
    case Active = 'active';
    case Expired = 'expired';

    public function label(): string
    {
        return match ($this) {
            self::Trial => 'Sinov',
            self::Active => 'Faol',
            self::Expired => 'Muddati tugagan',
        };
    }
}
