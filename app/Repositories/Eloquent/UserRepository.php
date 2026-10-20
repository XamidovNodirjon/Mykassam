<?php

namespace App\Repositories\Eloquent;

use App\Enums\UserRole;
use App\Models\Shop;
use App\Models\User;
use App\Repositories\Contracts\UserRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class UserRepository implements UserRepositoryInterface
{
    public function findByLogin(string $login): ?User
    {
        return User::query()->where('login', $login)->first();
    }

    public function loginExists(string $login, ?int $exceptUserId = null): bool
    {
        return User::query()
            ->where('login', $login)
            ->when($exceptUserId, fn (Builder $query, int $id) => $query->whereKeyNot($id))
            ->exists();
    }

    public function create(array $attributes): User
    {
        return User::query()->create($attributes);
    }

    public function update(User $user, array $attributes): User
    {
        $user->update($attributes);

        return $user;
    }

    public function delete(User $user): void
    {
        $user->delete();
    }

    public function hasSales(User $user): bool
    {
        return $user->sales()->exists();
    }

    public function paginateOwners(?string $search, int $perPage = 15): LengthAwarePaginator
    {
        return User::query()
            ->ofRole(UserRole::Owner)
            ->withCount('shops')
            ->when($search, function (Builder $query, string $search): void {
                $query->where(function (Builder $query) use ($search): void {
                    $query->where('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
                });
            })
            ->orderByDesc('id')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function sellersOfShop(Shop $shop): Collection
    {
        return $shop->sellers()
            ->withCount('sales')
            ->withSum('sales as revenue', 'total_amount')
            ->orderBy('first_name')
            ->get();
    }

    public function sellersOfShops(array $shopIds): Collection
    {
        return User::query()
            ->ofRole(UserRole::Seller)
            ->whereIn('shop_id', $shopIds)
            ->orderBy('first_name')
            ->get(['id', 'first_name', 'last_name', 'shop_id']);
    }

    public function countByRole(UserRole $role): int
    {
        return User::query()->ofRole($role)->count();
    }

    public function countSellersOfShop(Shop $shop): int
    {
        return $shop->sellers()->count();
    }
}
