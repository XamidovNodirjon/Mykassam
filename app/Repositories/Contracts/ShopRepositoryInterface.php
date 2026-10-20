<?php

namespace App\Repositories\Contracts;

use App\Enums\SubscriptionStatus;
use App\Models\Shop;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface ShopRepositoryInterface
{
    /**
     * Owner's shops with their type and product/seller counts.
     *
     * @return Collection<int, Shop>
     */
    public function forOwner(User $owner): Collection;

    /**
     * Lightweight id/name list for selects and filters.
     *
     * @return Collection<int, Shop>
     */
    public function optionsForOwner(User $owner): Collection;

    /**
     * @return LengthAwarePaginator<int, Shop>
     */
    public function paginateAll(?string $search, int $perPage = 15): LengthAwarePaginator;

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function create(User $owner, array $attributes): Shop;

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function update(Shop $shop, array $attributes): Shop;

    public function delete(Shop $shop): void;

    public function hasSales(Shop $shop): bool;

    public function count(): int;

    /**
     * Shops with their owner, soonest-ending subscription first.
     *
     * @return LengthAwarePaginator<int, Shop>
     */
    public function paginateBySubscription(?string $search, ?SubscriptionStatus $status, int $perPage = 15): LengthAwarePaginator;

    public function countBySubscriptionStatus(SubscriptionStatus $status): int;

    /**
     * Monthly income from paid, running subscriptions: the sum of their plan prices.
     */
    public function activeSubscriptionRevenue(): int;

    /**
     * Shops whose subscription affects the user: a seller's shop or an owner's active shops.
     *
     * @return Collection<int, Shop>
     */
    public function subscribedShopsOf(User $user): Collection;
}
