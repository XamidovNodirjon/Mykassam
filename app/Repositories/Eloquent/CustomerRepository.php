<?php

namespace App\Repositories\Eloquent;

use App\Models\Customer;
use App\Models\Shop;
use App\Repositories\Contracts\CustomerRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class CustomerRepository implements CustomerRepositoryInterface
{
    public function paginateForShop(Shop $shop, ?string $search, int $perPage = 20): LengthAwarePaginator
    {
        return $shop->customers()
            ->withSum(['debts as debt_amount' => fn (Builder $query) => $query->whereColumn('paid_amount', '<', 'amount')], 'amount')
            ->withSum(['debts as debt_paid' => fn (Builder $query) => $query->whereColumn('paid_amount', '<', 'amount')], 'paid_amount')
            ->when($search, fn (Builder $query, string $search) => $this->applySearch($query, $search))
            ->orderBy('name')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function searchForShop(int $shopId, string $search, int $limit = 8): Collection
    {
        return Customer::query()
            ->where('shop_id', $shopId)
            ->tap(fn (Builder $query) => $this->applySearch($query, $search))
            ->orderBy('name')
            ->limit($limit)
            ->get(['id', 'name', 'phone', 'bonus_balance']);
    }

    public function optionsForShop(Shop $shop): Collection
    {
        return $shop->customers()->orderBy('name')->get(['id', 'name', 'phone']);
    }

    public function lockForShop(int $shopId, int $customerId): ?Customer
    {
        return Customer::query()
            ->where('shop_id', $shopId)
            ->whereKey($customerId)
            ->lockForUpdate()
            ->first();
    }

    public function create(Shop $shop, array $attributes): Customer
    {
        return $shop->customers()->create($attributes);
    }

    public function update(Customer $customer, array $attributes): Customer
    {
        $customer->update($attributes);

        return $customer;
    }

    public function delete(Customer $customer): void
    {
        $customer->delete();
    }

    public function hasDebts(Customer $customer): bool
    {
        return $customer->debts()->exists();
    }

    public function changeBonus(Customer $customer, int $delta): void
    {
        if ($delta !== 0) {
            $customer->increment('bonus_balance', $delta);
        }
    }

    public function count(): int
    {
        return Customer::query()->count();
    }

    /**
     * @param  Builder<Customer>  $query
     */
    private function applySearch(Builder $query, string $search): void
    {
        $digits = preg_replace('/\D+/', '', $search) ?? '';

        $query->where(function (Builder $query) use ($search, $digits): void {
            $query->where('name', 'like', "%{$search}%")
                ->when(strlen($digits) >= 3, fn (Builder $query) => $query->orWhere('phone', 'like', "%{$digits}%"));
        });
    }
}
