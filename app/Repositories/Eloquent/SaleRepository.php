<?php

namespace App\Repositories\Eloquent;

use App\DTOs\SaleFilterData;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Repositories\Contracts\SaleRepositoryInterface;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class SaleRepository implements SaleRepositoryInterface
{
    public function create(array $attributes, array $items): Sale
    {
        $sale = Sale::query()->create($attributes);

        SaleItem::query()->insert(array_map(
            fn (array $item): array => [...$item, 'sale_id' => $sale->id],
            $items,
        ));

        return $sale;
    }

    public function paginate(SaleFilterData $filter, int $perPage = 20): LengthAwarePaginator
    {
        return Sale::query()
            ->with(['seller:id,first_name,last_name', 'shop:id,name'])
            ->whereIn('shop_id', $filter->shopIds)
            ->when($filter->sellerId, fn (Builder $query, int $sellerId) => $query->where('seller_id', $sellerId))
            ->when($filter->from, fn (Builder $query, CarbonImmutable $from) => $query->where('created_at', '>=', $from))
            ->when($filter->to, fn (Builder $query, CarbonImmutable $to) => $query->where('created_at', '<=', $to))
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function latestForSeller(int $sellerId, int $limit = 5): Collection
    {
        return Sale::query()
            ->where('seller_id', $sellerId)
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->limit($limit)
            ->get();
    }

    public function loadDetails(Sale $sale): Sale
    {
        return $sale->load(['items', 'seller:id,first_name,last_name', 'shop:id,name', 'customer:id,name,phone']);
    }
}
