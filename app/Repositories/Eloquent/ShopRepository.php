<?php

namespace App\Repositories\Eloquent;

use App\Enums\SubscriptionStatus;
use App\Models\Shop;
use App\Models\User;
use App\Repositories\Contracts\ShopRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class ShopRepository implements ShopRepositoryInterface
{
    public function forOwner(User $owner): Collection
    {
        return $owner->shops()
            ->with(['type:id,name', 'plan'])
            ->withCount(['products', 'sellers'])
            ->orderBy('name')
            ->get();
    }

    public function optionsForOwner(User $owner): Collection
    {
        return $owner->shops()
            ->with('plan')
            ->orderBy('name')
            ->get(['id', 'name', 'owner_id', 'is_active', 'plan_id']);
    }

    public function paginateAll(?string $search, int $perPage = 15): LengthAwarePaginator
    {
        return Shop::query()
            ->with(['owner:id,first_name,last_name,phone', 'type:id,name'])
            ->withCount(['products', 'sellers'])
            ->withSum('sales as revenue', 'total_amount')
            ->when($search, fn (Builder $query, string $search) => $query->where('name', 'like', "%{$search}%"))
            ->orderByDesc('id')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function create(User $owner, array $attributes): Shop
    {
        return $owner->shops()->create($attributes);
    }

    public function update(Shop $shop, array $attributes): Shop
    {
        $shop->update($attributes);

        return $shop;
    }

    public function delete(Shop $shop): void
    {
        $shop->delete();
    }

    public function hasSales(Shop $shop): bool
    {
        return $shop->sales()->exists();
    }

    public function count(): int
    {
        return Shop::query()->count();
    }

    public function paginateBySubscription(?string $search, ?SubscriptionStatus $status, int $perPage = 15): LengthAwarePaginator
    {
        return Shop::query()
            ->with(['owner:id,first_name,last_name,phone', 'plan'])
            ->when($search, fn (Builder $query, string $search) => $query->where('name', 'like', "%{$search}%"))
            ->when($status, fn (Builder $query, SubscriptionStatus $status) => $query->withSubscriptionStatus($status))
            ->orderBy('subscription_ends_at')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function countBySubscriptionStatus(SubscriptionStatus $status): int
    {
        return Shop::query()->withSubscriptionStatus($status)->count();
    }

    public function activeSubscriptionRevenue(): int
    {
        return (int) Shop::query()
            ->withSubscriptionStatus(SubscriptionStatus::Active)
            ->join('plans', 'plans.id', '=', 'shops.plan_id')
            ->sum('plans.price');
    }

    public function subscribedShopsOf(User $user): Collection
    {
        $query = $user->isSeller()
            ? Shop::query()->whereKey($user->shop_id)
            : $user->shops()->where('is_active', true);

        return $query->get(['id', 'name', 'on_trial', 'subscription_ends_at']);
    }
}
