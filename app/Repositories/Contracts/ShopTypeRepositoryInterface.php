<?php

namespace App\Repositories\Contracts;

use App\Models\ShopType;
use Illuminate\Database\Eloquent\Collection;

interface ShopTypeRepositoryInterface
{
    /**
     * @return Collection<int, ShopType>
     */
    public function allWithShopCount(): Collection;

    /**
     * @return Collection<int, ShopType>
     */
    public function active(): Collection;

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function create(array $attributes): ShopType;

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function update(ShopType $shopType, array $attributes): ShopType;

    public function delete(ShopType $shopType): void;

    public function hasShops(ShopType $shopType): bool;
}
