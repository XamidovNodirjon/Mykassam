<?php

namespace App\Services;

use App\DTOs\ShopData;
use App\Enums\PlanFeature;
use App\Exceptions\BusinessException;
use App\Models\Shop;
use App\Models\User;
use App\Repositories\Contracts\ShopRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class ShopService
{
    public function __construct(
        private ShopRepositoryInterface $shops,
        private SubscriptionService $subscriptions,
    ) {}

    /**
     * @return Collection<int, Shop>
     */
    public function listForOwner(User $owner): Collection
    {
        return $this->shops->forOwner($owner);
    }

    /**
     * @return Collection<int, Shop>
     */
    public function optionsForOwner(User $owner): Collection
    {
        return $this->shops->optionsForOwner($owner);
    }

    /**
     * Ids of an already loaded shop list, normalized to integers.
     *
     * @param  Collection<int, Shop>  $shops
     * @return list<int>
     */
    public function idsOf(Collection $shops): array
    {
        return array_values($shops->map(fn (Shop $shop): int => $shop->id)->all());
    }

    /**
     * Ids of the shops whose plan unlocks the module.
     *
     * @param  Collection<int, Shop>  $shops  Loaded with their plan.
     * @return list<int>
     */
    public function idsWithFeature(Collection $shops, PlanFeature $feature): array
    {
        return $this->idsOf($shops->filter(fn (Shop $shop): bool => $shop->hasFeature($feature)));
    }

    /**
     * @return LengthAwarePaginator<int, Shop>
     */
    public function paginateAll(?string $search): LengthAwarePaginator
    {
        return $this->shops->paginateAll($search);
    }

    public function create(User $owner, ShopData $data): Shop
    {
        return $this->shops->create($owner, [...$data->toArray(), ...$this->subscriptions->trialFor($owner)]);
    }

    public function update(Shop $shop, ShopData $data): Shop
    {
        return $this->shops->update($shop, $data->toArray());
    }

    /**
     * @throws BusinessException
     */
    public function delete(Shop $shop): void
    {
        if ($this->shops->hasSales($shop)) {
            throw new BusinessException("Do'konda savdolar tarixi bor. O'chirish o'rniga nofaol qiling.");
        }

        $this->shops->delete($shop);
    }

    public function toggleStatus(Shop $shop): Shop
    {
        return $this->shops->update($shop, ['is_active' => ! $shop->is_active]);
    }
}
