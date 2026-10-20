<?php

namespace App\Repositories\Contracts;

use App\Models\Product;
use App\Models\Shop;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface ProductRepositoryInterface
{
    /**
     * @param  bool  $lowStockOnly  Only products whose stock dropped to their min_stock.
     * @return LengthAwarePaginator<int, Product>
     */
    public function paginateForShop(Shop $shop, ?string $search, bool $lowStockOnly = false, int $perPage = 20): LengthAwarePaginator;

    /**
     * Active products running low in the given shops, the emptiest first.
     *
     * @param  list<int>  $shopIds
     * @return Collection<int, Product>
     */
    public function lowStockForShops(array $shopIds, int $limit = 10): Collection;

    public function countLowStock(Shop $shop): int;

    public function countForShop(Shop $shop): int;

    /**
     * Product an import row refers to: same barcode, or same name when the row has no barcode.
     */
    public function findForImport(Shop $shop, ?string $barcode, string $name): ?Product;

    public function incrementStock(Product $product, int $quantity): void;

    /**
     * Active, in-stock products a seller may put into a sale.
     *
     * @return Collection<int, Product>
     */
    public function sellableForShop(int $shopId, ?string $search, int $limit = 40): Collection;

    /**
     * Lock the given products of a shop for the duration of the current transaction.
     *
     * @param  list<int>  $productIds
     * @return Collection<int, Product>
     */
    public function lockForSale(int $shopId, array $productIds): Collection;

    public function decrementStock(Product $product, int $quantity): void;

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function create(Shop $shop, array $attributes): Product;

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function update(Product $product, array $attributes): Product;

    public function delete(Product $product): void;

    public function count(): int;
}
