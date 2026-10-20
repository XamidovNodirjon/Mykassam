<?php

namespace App\Repositories\Eloquent;

use App\Models\ShopType;
use App\Repositories\Contracts\ShopTypeRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class ShopTypeRepository implements ShopTypeRepositoryInterface
{
    public function allWithShopCount(): Collection
    {
        return ShopType::query()
            ->withCount('shops')
            ->orderBy('name')
            ->get();
    }

    public function active(): Collection
    {
        return ShopType::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name']);
    }

    public function create(array $attributes): ShopType
    {
        return ShopType::query()->create($attributes);
    }

    public function update(ShopType $shopType, array $attributes): ShopType
    {
        $shopType->update($attributes);

        return $shopType;
    }

    public function delete(ShopType $shopType): void
    {
        $shopType->delete();
    }

    public function hasShops(ShopType $shopType): bool
    {
        return $shopType->shops()->exists();
    }
}
