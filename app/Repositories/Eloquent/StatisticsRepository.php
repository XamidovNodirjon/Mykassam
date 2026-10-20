<?php

namespace App\Repositories\Eloquent;

use App\DTOs\StatisticsFilterData;
use App\Enums\UserRole;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Shop;
use App\Models\User;
use App\Repositories\Contracts\StatisticsRepositoryInterface;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection as BaseCollection;

/**
 * Every method runs a single aggregate query, so statistics never trigger N+1 lookups.
 */
class StatisticsRepository implements StatisticsRepositoryInterface
{
    public function totals(StatisticsFilterData $filter): array
    {
        $row = $this->salesQuery($filter)
            ->toBase()
            ->selectRaw('COALESCE(SUM(total_amount), 0) as revenue')
            ->selectRaw('COALESCE(SUM(total_cost), 0) as cost')
            ->selectRaw('COALESCE(SUM(profit), 0) as profit')
            ->selectRaw('COUNT(*) as sales_count')
            ->selectRaw('COALESCE(SUM(items_count), 0) as items_count')
            ->first();

        return [
            'revenue' => (int) ($row->revenue ?? 0),
            'cost' => (int) ($row->cost ?? 0),
            'profit' => (int) ($row->profit ?? 0),
            'sales_count' => (int) ($row->sales_count ?? 0),
            'items_count' => (int) ($row->items_count ?? 0),
        ];
    }

    public function daily(StatisticsFilterData $filter): BaseCollection
    {
        return $this->salesQuery($filter)
            ->toBase()
            ->selectRaw('DATE(created_at) as day')
            ->selectRaw('SUM(total_amount) as revenue')
            ->selectRaw('SUM(profit) as profit')
            ->selectRaw('COUNT(*) as sales_count')
            ->groupByRaw('DATE(created_at)')
            ->orderBy('day')
            ->get()
            ->mapWithKeys(fn (object $row): array => [
                (string) $row->day => [
                    'revenue' => (int) $row->revenue,
                    'profit' => (int) $row->profit,
                    'sales_count' => (int) $row->sales_count,
                ],
            ]);
    }

    public function bySeller(StatisticsFilterData $filter): Collection
    {
        $inPeriod = $this->periodConstraint($filter);

        return User::query()
            ->ofRole(UserRole::Seller)
            ->whereIn('shop_id', $filter->shopIds)
            ->when($filter->sellerId, fn (Builder $query, int $sellerId) => $query->whereKey($sellerId))
            ->with('shop:id,name')
            ->withCount(['sales as sales_count' => $inPeriod])
            ->withSum(['sales as revenue' => $inPeriod], 'total_amount')
            ->withSum(['sales as profit' => $inPeriod], 'profit')
            ->orderByDesc('revenue')
            ->orderBy('first_name')
            ->get(['id', 'first_name', 'last_name', 'shop_id', 'is_active']);
    }

    public function byShop(StatisticsFilterData $filter): Collection
    {
        $inPeriod = $this->periodConstraint($filter);

        return Shop::query()
            ->whereIn('id', $filter->shopIds)
            ->withCount(['sales as sales_count' => $inPeriod])
            ->withSum(['sales as revenue' => $inPeriod], 'total_amount')
            ->withSum(['sales as profit' => $inPeriod], 'profit')
            ->orderByDesc('revenue')
            ->get(['id', 'name']);
    }

    public function topProducts(StatisticsFilterData $filter, int $limit = 5): BaseCollection
    {
        return SaleItem::query()
            ->toBase()
            ->join('sales', 'sales.id', '=', 'sale_items.sale_id')
            ->whereIn('sales.shop_id', $filter->shopIds)
            ->whereBetween('sales.created_at', [$filter->from, $filter->to])
            ->when($filter->sellerId, fn ($query, int $sellerId) => $query->where('sales.seller_id', $sellerId))
            ->selectRaw('sale_items.product_name as product_name')
            ->selectRaw('SUM(sale_items.quantity) as quantity')
            ->selectRaw('SUM(sale_items.total) as revenue')
            ->groupBy('sale_items.product_name')
            ->orderByDesc('revenue')
            ->limit($limit)
            ->get()
            ->map(fn (object $row): array => [
                'product_name' => (string) $row->product_name,
                'quantity' => (int) $row->quantity,
                'revenue' => (int) $row->revenue,
            ]);
    }

    public function platformTotals(DateTimeInterface $from, DateTimeInterface $to): array
    {
        $row = Sale::query()
            ->toBase()
            ->whereBetween('created_at', [$from, $to])
            ->selectRaw('COALESCE(SUM(total_amount), 0) as revenue')
            ->selectRaw('COALESCE(SUM(profit), 0) as profit')
            ->selectRaw('COUNT(*) as sales_count')
            ->first();

        return [
            'revenue' => (int) ($row->revenue ?? 0),
            'profit' => (int) ($row->profit ?? 0),
            'sales_count' => (int) ($row->sales_count ?? 0),
        ];
    }

    /**
     * @return Builder<Sale>
     */
    private function salesQuery(StatisticsFilterData $filter): Builder
    {
        return Sale::query()
            ->whereIn('shop_id', $filter->shopIds)
            ->whereBetween('created_at', [$filter->from, $filter->to])
            ->when($filter->sellerId, fn (Builder $query, int $sellerId) => $query->where('seller_id', $sellerId));
    }

    /**
     * @return \Closure(Builder<Sale>|HasMany<Sale, *>): void
     */
    private function periodConstraint(StatisticsFilterData $filter): \Closure
    {
        return function ($query) use ($filter): void {
            $query->whereBetween('created_at', [$filter->from, $filter->to]);
        };
    }
}
