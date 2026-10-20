<?php

namespace App\Enums;

enum PaymentMethod: string
{
    case Cash = 'cash';
    case Card = 'card';
    case Transfer = 'transfer';
    case Debt = 'debt';

    public function label(): string
    {
        return match ($this) {
            self::Cash => 'Naqd',
            self::Card => 'Karta',
            self::Transfer => "O'tkazma",
            self::Debt => 'Nasiya',
        };
    }

    /**
     * Methods paid on the spot, i.e. everything except selling on credit.
     *
     * @return list<self>
     */
    public static function immediate(): array
    {
        return [self::Cash, self::Card, self::Transfer];
    }

    /**
     * @param  list<self>|null  $methods  Defaults to every method.
     * @return list<array{value: string, label: string}>
     */
    public static function options(?array $methods = null): array
    {
        return array_map(
            fn (self $method): array => ['value' => $method->value, 'label' => $method->label()],
            $methods ?? self::cases(),
        );
    }
}
