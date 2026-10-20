<?php

namespace App\Services;

use App\DTOs\ProductData;
use App\Exceptions\BusinessException;
use App\Models\Product;
use App\Models\Shop;
use App\Repositories\Contracts\ProductRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class ProductService
{
    public function __construct(
        private ProductRepositoryInterface $products,
        private ShopPlanGuard $guard,
    ) {}

    /**
     * @return LengthAwarePaginator<int, Product>
     */
    public function paginateForShop(Shop $shop, ?string $search, bool $lowStockOnly = false): LengthAwarePaginator
    {
        return $this->products->paginateForShop($shop, $search, $lowStockOnly);
    }

    /**
     * @return Collection<int, Product>
     */
    public function sellableForShop(int $shopId, ?string $search): Collection
    {
        return $this->products->sellableForShop($shopId, $search);
    }

    /**
     * @throws BusinessException When the plan's product limit is reached.
     */
    public function create(Shop $shop, ProductData $data): Product
    {
        $this->guard->ensureCanAddProducts($shop);

        return $this->products->create($shop, $data->toArray());
    }

    public function update(Product $product, ProductData $data): Product
    {
        return $this->products->update($product, $data->toArray());
    }

    public function delete(Product $product): void
    {
        $this->products->delete($product);
    }
}
