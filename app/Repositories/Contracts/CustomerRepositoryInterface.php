<?php

namespace App\Repositories\Contracts;

use App\Models\Customer;
use App\Models\Shop;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface CustomerRepositoryInterface
{
    /**
     * Customers with their unpaid debt sums (debt_amount, debt_paid).
     *
     * @return LengthAwarePaginator<int, Customer>
     */
    public function paginateForShop(Shop $shop, ?string $search, int $perPage = 20): LengthAwarePaginator;

    /**
     * Quick lookup by name or phone at the point of sale.
     *
     * @return Collection<int, Customer>
     */
    public function searchForShop(int $shopId, string $search, int $limit = 8): Collection;

    /**
     * @return Collection<int, Customer>
     */
    public function optionsForShop(Shop $shop): Collection;

    /**
     * Lock the customer row for the current transaction (bonus balance changes).
     */
    public function lockForShop(int $shopId, int $customerId): ?Customer;

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function create(Shop $shop, array $attributes): Customer;

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function update(Customer $customer, array $attributes): Customer;

    public function delete(Customer $customer): void;

    public function hasDebts(Customer $customer): bool;

    public function changeBonus(Customer $customer, int $delta): void;

    public function count(): int;
}
