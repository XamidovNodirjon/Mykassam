<?php

namespace App\Services;

use App\DTOs\StatisticsFilterData;
use App\Enums\UserRole;
use App\Models\Shop;
use App\Models\User;
use App\Repositories\Contracts\CustomerRepositoryInterface;
use App\Repositories\Contracts\DebtRepositoryInterface;
use App\Repositories\Contracts\ProductRepositoryInterface;
use App\Repositories\Contracts\ShopRepositoryInterface;
use App\Repositories\Contracts\StatisticsRepositoryInterface;
use App\Repositories\Contracts\UserRepositoryInterface;
use Carbon\CarbonImmutable;
use Carbon\CarbonPeriod;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Collection as BaseCollection;

class StatisticsService
{
    public function __construct(
        private StatisticsRepositoryInterface $statistics,
        private UserRepositoryInterface $users,
        private ShopRepositoryInterface $shops,
        private ProductRepositoryInterface $products,
        private CustomerRepositoryInterface $customers,
        private DebtRepositoryInterface $debts,
    ) {}

    /**
     * @return array{revenue: int, cost: int, profit: int, sales_count: int, items_count: int, average_check: int}
     */
    public function totals(StatisticsFilterData $filter): array
    {
        $totals = $this->statistics->totals($filter);

        return [
            ...$totals,
            'average_check' => $totals['sales_count'] > 0
                ? intdiv($totals['revenue'], $totals['sales_count'])
                : 0,
        ];
    }

    /**
     * Daily revenue series with zero-filled gaps so charts show every day of the period.
     *
     * @return list<array{date: string, revenue: int, profit: int, sales_count: int}>
     */
    public function dailySeries(StatisticsFilterData $filter): array
    {
        $rows = $this->statistics->daily($filter);
        $series = [];

        foreach (CarbonPeriod::create($filter->from->startOfDay(), $filter->to->startOfDay()) as $day) {
            $key = $day->toDateString();
            $row = $rows->get($key, ['revenue' => 0, 'profit' => 0, 'sales_count' => 0]);

            $series[] = ['date' => $key, ...$row];
        }

        return $series;
    }

    /**
     * @return Collection<int, User>
     */
    public function sellers(StatisticsFilterData $filter): Collection
    {
        return $this->statistics->bySeller($filter);
    }

    /**
     * @return Collection<int, Shop>
     */
    public function shops(StatisticsFilterData $filter): Collection
    {
        return $this->statistics->byShop($filter);
    }

    /**
     * @return BaseCollection<int, array{product_name: string, quantity: int, revenue: int}>
     */
    public function topProducts(StatisticsFilterData $filter): BaseCollection
    {
        return $this->statistics->topProducts($filter);
    }

    /**
     * Platform overview for the administrator.
     *
     * @return array<string, int>
     */
    public function platformOverview(): array
    {
        $today = CarbonImmutable::today();
        $todayTotals = $this->statistics->platformTotals($today, $today->endOfDay());
        $monthTotals = $this->statistics->platformTotals($today->subDays(29), $today->endOfDay());

        return [
            'owners' => $this->users->countByRole(UserRole::Owner),
            'sellers' => $this->users->countByRole(UserRole::Seller),
            'shops' => $this->shops->count(),
            'products' => $this->products->count(),
            'customers' => $this->customers->count(),
            'outstanding_debts' => $this->debts->outstandingTotal(),
            'today_revenue' => $todayTotals['revenue'],
            'today_sales' => $todayTotals['sales_count'],
            'month_revenue' => $monthTotals['revenue'],
            'month_profit' => $monthTotals['profit'],
        ];
    }
}
