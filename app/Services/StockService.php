<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Shop;
use App\Models\StockReceipt;
use App\Models\User;
use App\Repositories\Contracts\ProductRepositoryInterface;
use App\Repositories\Contracts\StockReceiptRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Stock receipts (kirim) and low-stock alerts.
 */
class StockService
{
    public function __construct(
        private StockReceiptRepositoryInterface $receipts,
        private ProductRepositoryInterface $products,
    ) {}

    /**
     * @return LengthAwarePaginator<int, StockReceipt>
     */
    public function history(Shop $shop, ?string $search): LengthAwarePaginator
    {
        return $this->receipts->paginateForShop($shop, $search);
    }

    /**
     * Add the goods to stock. A new purchase price becomes the product's cost price.
     */
    public function receive(Product $product, int $quantity, ?int $costPrice, User $user): StockReceipt
    {
        return DB::transaction(function () use ($product, $quantity, $costPrice, $user): StockReceipt {
            $this->products->incrementStock($product, $quantity);

            if ($costPrice !== null && $costPrice !== $product->cost_price) {
                $this->products->update($product, ['cost_price' => $costPrice]);
            }

            return $this->receipts->create([
                'shop_id' => $product->shop_id,
                'product_id' => $product->id,
                'product_name' => $product->name,
                'user_id' => $user->id,
                'quantity' => $quantity,
                'cost_price' => $product->cost_price,
            ]);
        });
    }

    public function lowStockCount(Shop $shop): int
    {
        return $this->products->countLowStock($shop);
    }

    /**
     * @param  list<int>  $shopIds
     * @return Collection<int, Product>
     */
    public function lowStockForShops(array $shopIds): Collection
    {
        return $shopIds === [] ? new Collection : $this->products->lowStockForShops($shopIds);
    }
}
