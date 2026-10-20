<?php

namespace App\Services;

use App\DTOs\ShopTypeData;
use App\Exceptions\BusinessException;
use App\Models\ShopType;
use App\Repositories\Contracts\ShopTypeRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class ShopTypeService
{
    public function __construct(private ShopTypeRepositoryInterface $shopTypes) {}

    /**
     * @return Collection<int, ShopType>
     */
    public function list(): Collection
    {
        return $this->shopTypes->allWithShopCount();
    }

    /**
     * @return Collection<int, ShopType>
     */
    public function activeOptions(): Collection
    {
        return $this->shopTypes->active();
    }

    public function create(ShopTypeData $data): ShopType
    {
        return $this->shopTypes->create($data->toArray());
    }

    public function update(ShopType $shopType, ShopTypeData $data): ShopType
    {
        return $this->shopTypes->update($shopType, $data->toArray());
    }

    /**
     * @throws BusinessException
     */
    public function delete(ShopType $shopType): void
    {
        if ($this->shopTypes->hasShops($shopType)) {
            throw new BusinessException("Bu turdagi do'konlar mavjud. O'chirish o'rniga nofaol qiling.");
        }

        $this->shopTypes->delete($shopType);
    }
}
