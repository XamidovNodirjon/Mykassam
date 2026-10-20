<?php

namespace App\Repositories\Contracts;

use App\DTOs\SaleFilterData;
use App\Models\Sale;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface SaleRepositoryInterface
{
    /**
     * @param  array<string, mixed>  $attributes
     * @param  list<array<string, mixed>>  $items
     */
    public function create(array $attributes, array $items): Sale;

    /**
     * @return LengthAwarePaginator<int, Sale>
     */
    public function paginate(SaleFilterData $filter, int $perPage = 20): LengthAwarePaginator;

    /**
     * @return Collection<int, Sale>
     */
    public function latestForSeller(int $sellerId, int $limit = 5): Collection;

    public function loadDetails(Sale $sale): Sale;
}
