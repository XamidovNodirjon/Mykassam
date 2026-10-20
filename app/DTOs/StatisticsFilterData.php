<?php

namespace App\DTOs;

use App\Enums\StatisticsPeriod;
use App\Http\Requests\StatisticsRequest;
use Carbon\CarbonImmutable;

final readonly class StatisticsFilterData
{
    /**
     * @param  list<int>  $shopIds  Shops the statistics are limited to.
     */
    public function __construct(
        public StatisticsPeriod $period,
        public CarbonImmutable $from,
        public CarbonImmutable $to,
        public array $shopIds,
        public ?int $sellerId = null,
    ) {}

    /**
     * @param  list<int>  $allowedShopIds
     */
    public static function fromRequest(StatisticsRequest $request, array $allowedShopIds, ?int $sellerId = null): self
    {
        $period = $request->enum('period', StatisticsPeriod::class) ?? StatisticsPeriod::Week;
        [$from, $to] = $period->range($request->input('from'), $request->input('to'));

        $shopId = $request->integer('shop_id');

        return new self(
            period: $period,
            from: $from,
            to: $to,
            shopIds: $shopId > 0 && in_array($shopId, $allowedShopIds, true) ? [$shopId] : $allowedShopIds,
            sellerId: $sellerId,
        );
    }

    /**
     * Filter values echoed back to the page.
     *
     * @return array{period: string, from: string, to: string, shop_id: int|null}
     */
    public function toArray(): array
    {
        return [
            'period' => $this->period->value,
            'from' => $this->from->toDateString(),
            'to' => $this->to->toDateString(),
            'shop_id' => count($this->shopIds) === 1 ? $this->shopIds[0] : null,
        ];
    }
}
