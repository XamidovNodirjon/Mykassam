<?php

namespace App\Repositories\Contracts;

use App\DTOs\StatisticsFilterData;
use App\Models\Shop;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Collection as BaseCollection;

interface StatisticsRepositoryInterface
{
    /**
     * @return array{revenue: int, cost: int, profit: int, sales_count: int, items_count: int}
     */
    public function totals(StatisticsFilterData $filter): array;

    /**
     * Revenue, profit and sale count grouped by calendar day.
     *
     * @return BaseCollection<string, array{revenue: int, profit: int, sales_count: int}>
     */
    public function daily(StatisticsFilterData $filter): BaseCollection;

    /**
     * Sellers of the filtered shops with aggregated sales for the period.
     *
     * @return Collection<int, User>
     */
    public function bySeller(StatisticsFilterData $filter): Collection;

    /**
     * Filtered shops with aggregated sales for the period.
     *
     * @return Collection<int, Shop>
     */
    public function byShop(StatisticsFilterData $filter): Collection;

    /**
     * @return BaseCollection<int, array{product_name: string, quantity: int, revenue: int}>
     */
    public function topProducts(StatisticsFilterData $filter, int $limit = 5): BaseCollection;

    /**
     * Platform wide revenue and profit for the given date range.
     *
     * @return array{revenue: int, profit: int, sales_count: int}
     */
    public function platformTotals(\DateTimeInterface $from, \DateTimeInterface $to): array;
}
