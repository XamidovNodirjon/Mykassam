<?php

namespace App\Enums;

use Carbon\CarbonImmutable;

enum StatisticsPeriod: string
{
    case Today = 'today';
    case Week = 'week';
    case Month = 'month';
    case Custom = 'custom';

    public function label(): string
    {
        return match ($this) {
            self::Today => 'Bugun',
            self::Week => '7 kun',
            self::Month => '30 kun',
            self::Custom => 'Oraliq',
        };
    }

    /**
     * Resolve the inclusive date range covered by the period.
     *
     * @return array{0: CarbonImmutable, 1: CarbonImmutable}
     */
    public function range(?string $from = null, ?string $to = null): array
    {
        $today = CarbonImmutable::today();

        return match ($this) {
            self::Today => [$today, $today->endOfDay()],
            self::Week => [$today->subDays(6), $today->endOfDay()],
            self::Month => [$today->subDays(29), $today->endOfDay()],
            self::Custom => [
                CarbonImmutable::parse($from ?? $today)->startOfDay(),
                CarbonImmutable::parse($to ?? $today)->endOfDay(),
            ],
        };
    }

    /**
     * @return list<array{value: string, label: string}>
     */
    public static function options(): array
    {
        return array_map(
            fn (self $period): array => ['value' => $period->value, 'label' => $period->label()],
            self::cases(),
        );
    }
}
