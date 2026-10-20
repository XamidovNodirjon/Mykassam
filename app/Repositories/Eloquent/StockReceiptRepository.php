<?php

namespace App\Repositories\Eloquent;

use App\Models\Shop;
use App\Models\StockReceipt;
use App\Repositories\Contracts\StockReceiptRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class StockReceiptRepository implements StockReceiptRepositoryInterface
{
    public function paginateForShop(Shop $shop, ?string $search, int $perPage = 20): LengthAwarePaginator
    {
        return $shop->stockReceipts()
            ->with('user:id,first_name,last_name')
            ->when($search, fn (Builder $query, string $search) => $query->where('product_name', 'like', "%{$search}%"))
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function create(array $attributes): StockReceipt
    {
        return StockReceipt::query()->create($attributes);
    }
}
