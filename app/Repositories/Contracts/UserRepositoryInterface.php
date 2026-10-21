<?php

namespace App\Repositories\Contracts;

use App\Enums\UserRole;
use App\Models\Shop;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface UserRepositoryInterface
{
    public function findByLogin(string $login): ?User;

    public function loginExists(string $login, ?int $exceptUserId = null): bool;

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function create(array $attributes): User;

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function update(User $user, array $attributes): User;

    public function delete(User $user): void;

    public function hasSales(User $user): bool;

    /**
     * @return LengthAwarePaginator<int, User>
     */
    public function paginateOwners(?string $search, int $perPage = 15): LengthAwarePaginator;

    /**
     * @return Collection<int, User>
     */
    public function sellersOfShop(Shop $shop): Collection;

    /**
     * @param  list<int>  $shopIds
     * @return Collection<int, User>
     */
    public function sellersOfShops(array $shopIds): Collection;

    public function countByRole(UserRole $role): int;

    /**
     * Every account, newest first, with its shop, counts and the time of its latest request.
     *
     * @return LengthAwarePaginator<int, User>
     */
    public function paginateAll(?string $search, ?UserRole $role, int $perPage = 20): LengthAwarePaginator;

    /**
     * @return array{total: int, owners: int, sellers: int, blocked: int, online: int}
     */
    public function summary(int $onlineWithinSeconds): array;

    public function countSellersOfShop(Shop $shop): int;
}
