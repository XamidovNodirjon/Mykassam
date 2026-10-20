<?php

namespace App\Services;

use App\Enums\PlanFeature;
use App\Exceptions\BusinessException;
use App\Models\Shop;
use App\Repositories\Contracts\ProductRepositoryInterface;
use App\Repositories\Contracts\UserRepositoryInterface;

/**
 * Enforces what the shop's plan allows. Nothing is ever deleted on a downgrade: data above a
 * limit or of a locked module stays, only adding new records is refused.
 */
class ShopPlanGuard
{
    public function __construct(
        private ProductRepositoryInterface $products,
        private UserRepositoryInterface $users,
    ) {}

    /**
     * @throws BusinessException
     */
    public function ensureFeature(Shop $shop, PlanFeature $feature): void
    {
        if (! $shop->loadMissing('plan')->hasFeature($feature)) {
            throw new BusinessException(sprintf(
                '"%s" %s tarifiga kirmaydi. Tarifni yangilash uchun admin bilan bog\'laning.',
                $feature->label(),
                $shop->plan->name ?? 'joriy',
            ));
        }
    }

    /**
     * @throws BusinessException
     */
    public function ensureCanAddProducts(Shop $shop, int $count = 1): void
    {
        $limit = $shop->loadMissing('plan')->plan?->max_products;

        if ($limit !== null && $this->products->countForShop($shop) + $count > $limit) {
            throw new BusinessException("{$shop->plan->name} tarifida do'konga {$limit} tagacha mahsulot qo'shish mumkin. Ko'proq uchun tarifni yangilang.");
        }
    }

    /**
     * @throws BusinessException
     */
    public function ensureCanAddSeller(Shop $shop): void
    {
        $limit = $shop->loadMissing('plan')->plan?->max_sellers;

        if ($limit !== null && $this->users->countSellersOfShop($shop) >= $limit) {
            throw new BusinessException("{$shop->plan->name} tarifida do'konga {$limit} tagacha sotuvchi qo'shish mumkin. Ko'proq uchun tarifni yangilang.");
        }
    }
}
