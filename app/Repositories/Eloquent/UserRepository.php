<?php

namespace App\Repositories\Eloquent;

use App\Enums\UserRole;
use App\Models\Customer;
use App\Models\Debt;
use App\Models\Sale;
use App\Models\Shop;
use App\Models\User;
use App\Repositories\Contracts\UserRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

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

    public function paginateAll(?string $search, ?UserRole $role, int $perPage = 20): LengthAwarePaginator
    {
        return User::query()
            ->with(['shop:id,name,owner_id', 'shop.owner:id,first_name,last_name'])
            ->withCount(['shops', 'sales'])
            ->addSelect([
                'last_seen_at' => DB::table('sessions')
                    ->selectRaw('max(last_activity)')
                    ->whereColumn('sessions.user_id', 'users.id'),
            ])
            ->when($role, fn (Builder $query, UserRole $role) => $query->ofRole($role))
            ->when($search, function (Builder $query, string $search): void {
                $query->where(function (Builder $query) use ($search): void {
                    $query->where('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%")
                        ->orWhere('login', 'like', "%{$search}%");
                });
            })
            ->orderByDesc('id')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function deleteOwnerWithShops(User $owner): void
    {
        DB::transaction(function () use ($owner): void {
            $shopIds = $owner->shops()->pluck('id');
            $userIds = User::query()->whereIn('shop_id', $shopIds)->pluck('id')->push($owner->id);

            // Rows that block deleting a shop go first; the rest cascades with it.
            Sale::query()->whereIn('shop_id', $shopIds)->delete();
            Debt::query()->whereIn('shop_id', $shopIds)->delete();
            Customer::query()->whereIn('shop_id', $shopIds)->delete();
            Shop::query()->whereKey($shopIds)->delete();

            DB::table('sessions')->whereIn('user_id', $userIds)->delete();
            $owner->delete();
        });
    }

    public function deleteSellerWithSales(User $seller): void
    {
        DB::transaction(function () use ($seller): void {
            Sale::query()->where('seller_id', $seller->id)->delete();

            DB::table('sessions')->where('user_id', $seller->id)->delete();
            $seller->delete();
        });
    }

    public function summary(int $onlineWithinSeconds): array
    {
        $byRole = User::query()->toBase()
            ->selectRaw('role, count(*) as aggregate')
            ->groupBy('role')
            ->pluck('aggregate', 'role');

        return [
            'total' => (int) $byRole->sum(),
            'owners' => (int) ($byRole[UserRole::Owner->value] ?? 0),
            'sellers' => (int) ($byRole[UserRole::Seller->value] ?? 0),
            'blocked' => User::query()->where('is_active', false)->count(),
            'online' => DB::table('sessions')
                ->whereNotNull('user_id')
                ->where('last_activity', '>=', now()->subSeconds($onlineWithinSeconds)->getTimestamp())
                ->distinct()
                ->count('user_id'),
        ];
    }

    public function countSellersOfShop(Shop $shop): int
    {
        return $shop->sellers()->count();
    }
}
