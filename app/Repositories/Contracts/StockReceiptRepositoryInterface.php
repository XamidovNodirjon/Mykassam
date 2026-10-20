<?php

namespace App\Repositories\Contracts;

use App\Models\Shop;
use App\Models\StockReceipt;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface StockReceiptRepositoryInterface
{
    /**
     * Newest receipts first, with the user who recorded them.
     *
     * @return LengthAwarePaginator<int, StockReceipt>
     */
    public function paginateForShop(Shop $shop, ?string $search, int $perPage = 20): LengthAwarePaginator;

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function create(array $attributes): StockReceipt;
}
