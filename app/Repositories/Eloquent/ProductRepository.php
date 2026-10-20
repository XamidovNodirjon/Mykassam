<?php

namespace App\Repositories\Eloquent;

use App\Models\Product;
use App\Models\Shop;
use App\Repositories\Contracts\ProductRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class ProductRepository implements ProductRepositoryInterface
{
    public function paginateForShop(Shop $shop, ?string $search, bool $lowStockOnly = false, int $perPage = 20): LengthAwarePaginator
    {
        return $shop->products()
            ->when($search, fn (Builder $query, string $search) => $this->applySearch($query, $search))
            ->when($lowStockOnly, fn (Builder $query) => $query->whereColumn('stock', '<=', 'min_stock'))
            ->orderBy('name')
            ->orderBy('id')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function sellableForShop(int $shopId, ?string $search, int $limit = 40): Collection
    {
        return Product::query()
            ->where('shop_id', $shopId)
            ->where('is_active', true)
            ->where('stock', '>', 0)
            ->when($search, fn (Builder $query, string $search) => $this->applySearch($query, $search))
            ->orderBy('name')
            ->limit($limit)
            ->get(['id', 'name', 'barcode', 'min_price', 'price', 'stock']);
    }

    public function lockForSale(int $shopId, array $productIds): Collection
    {
        return Product::query()
            ->where('shop_id', $shopId)
            ->where('is_active', true)
            ->whereIn('id', $productIds)
            ->lockForUpdate()
            ->get()
            ->keyBy('id');
    }

    public function decrementStock(Product $product, int $quantity): void
    {
        $product->decrement('stock', $quantity);
    }

    public function incrementStock(Product $product, int $quantity): void
    {
        $product->increment('stock', $quantity);
    }

    public function lowStockForShops(array $shopIds, int $limit = 10): Collection
    {
        return Product::query()
            ->with('shop:id,name')
            ->whereIn('shop_id', $shopIds)
            ->where('is_active', true)
            ->whereColumn('stock', '<=', 'min_stock')
            ->orderBy('stock')
            ->orderBy('name')
            ->limit($limit)
            ->get(['id', 'shop_id', 'name', 'stock', 'min_stock']);
    }

    public function countLowStock(Shop $shop): int
    {
        return $shop->products()->where('is_active', true)->whereColumn('stock', '<=', 'min_stock')->count();
    }

    public function countForShop(Shop $shop): int
    {
        return $shop->products()->count();
    }

    public function findForImport(Shop $shop, ?string $barcode, string $name): ?Product
    {
        return $barcode !== null
            ? $shop->products()->where('barcode', $barcode)->first()
            : $shop->products()->whereNull('barcode')->where('name', $name)->first();
    }

    public function create(Shop $shop, array $attributes): Product
    {
        return $shop->products()->create($attributes);
    }

    public function update(Product $product, array $attributes): Product
    {
        $product->update($attributes);

        return $product;
    }

    public function delete(Product $product): void
    {
        $product->delete();
    }

    public function count(): int
    {
        return Product::query()->count();
    }

    /**
     * @param  Builder<Product>  $query
     */
    private function applySearch(Builder $query, string $search): void
    {
        $query->where(function (Builder $query) use ($search): void {
            $query->where('name', 'like', "%{$search}%")
                ->orWhere('barcode', $search);
        });
    }
}
