<?php

namespace App\Services;

use App\DTOs\SaleData;
use App\DTOs\SaleFilterData;
use App\Enums\PaymentMethod;
use App\Enums\PlanFeature;
use App\Exceptions\BusinessException;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Sale;
use App\Models\User;
use App\Repositories\Contracts\CustomerRepositoryInterface;
use App\Repositories\Contracts\ProductRepositoryInterface;
use App\Repositories\Contracts\SaleRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SaleService
{
    public function __construct(
        private SaleRepositoryInterface $sales,
        private ProductRepositoryInterface $products,
        private CustomerRepositoryInterface $customers,
        private DebtService $debts,
        private ShopPlanGuard $guard,
    ) {}

    /**
     * @return LengthAwarePaginator<int, Sale>
     */
    public function paginate(SaleFilterData $filter): LengthAwarePaginator
    {
        return $this->sales->paginate($filter);
    }

    /**
     * @return Collection<int, Sale>
     */
    public function latestForSeller(User $seller): Collection
    {
        return $this->sales->latestForSeller($seller->id);
    }

    public function details(Sale $sale): Sale
    {
        return $this->sales->loadDetails($sale);
    }

    /**
     * Register a sale for the seller's shop.
     *
     * Prices and stock are re-checked against locked product rows, so a seller can never
     * sell below the product's minimum price or more than is in stock. With a customer the
     * sale may spend their bonus, earns cashback when paid on the spot, or is booked as a debt.
     *
     * @throws ValidationException
     * @throws BusinessException When the shop's plan has no customers or debt book.
     */
    public function create(User $seller, SaleData $data): Sale
    {
        $shop = $seller->loadMissing('shop.plan')->shop;
        $isCredit = $data->paymentMethod === PaymentMethod::Debt;

        if ($data->customerId !== null || $data->bonusToUse > 0) {
            $this->guard->ensureFeature($shop, PlanFeature::Customers);
        }

        if ($isCredit) {
            $this->guard->ensureFeature($shop, PlanFeature::Debts);
        }

        return DB::transaction(function () use ($seller, $shop, $data, $isCredit): Sale {
            $products = $this->products->lockForSale($shop->id, $data->productIds());

            $items = [];
            $goodsTotal = 0;
            $totalCost = 0;
            $quantity = 0;

            foreach ($data->items as $index => $item) {
                /** @var Product|null $product */
                $product = $products->get($item->productId);

                $this->ensureSellable($index, $product, $item->quantity, $item->unitPrice);

                $lineTotal = $item->unitPrice * $item->quantity;
                $goodsTotal += $lineTotal;
                $totalCost += $product->cost_price * $item->quantity;
                $quantity += $item->quantity;

                $items[] = [
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'quantity' => $item->quantity,
                    'unit_price' => $item->unitPrice,
                    'cost_price' => $product->cost_price,
                    'total' => $lineTotal,
                ];

                $this->products->decrementStock($product, $item->quantity);
            }

            $customer = $this->lockCustomer($shop->id, $data->customerId);
            $bonusUsed = $this->bonusToSpend($customer, $data->bonusToUse, $goodsTotal);
            $totalAmount = $goodsTotal - $bonusUsed;
            $bonusEarned = $customer !== null && ! $isCredit
                ? intdiv($totalAmount * $shop->cashback_percent, 100)
                : 0;

            $sale = $this->sales->create([
                'shop_id' => $shop->id,
                'seller_id' => $seller->id,
                'customer_id' => $customer?->id,
                'total_amount' => $totalAmount,
                'total_cost' => $totalCost,
                'profit' => $totalAmount - $totalCost,
                'items_count' => $quantity,
                'payment_method' => $data->paymentMethod,
                'bonus_used' => $bonusUsed,
                'bonus_earned' => $bonusEarned,
            ], $items);

            if ($customer !== null) {
                $this->customers->changeBonus($customer, $bonusEarned - $bonusUsed);
            }

            if ($isCredit && $customer !== null && $data->dueDate !== null) {
                $this->debts->openForSale($sale, $customer, $data->dueDate);
            }

            return $sale;
        });
    }

    /**
     * @throws ValidationException
     */
    private function lockCustomer(int $shopId, ?int $customerId): ?Customer
    {
        if ($customerId === null) {
            return null;
        }

        return $this->customers->lockForShop($shopId, $customerId)
            ?? throw ValidationException::withMessages(['customer_id' => 'Mijoz topilmadi.']);
    }

    /**
     * Bonus actually spent: never more than the customer has or than the goods cost.
     *
     * @throws ValidationException
     */
    private function bonusToSpend(?Customer $customer, int $requested, int $goodsTotal): int
    {
        if ($requested === 0) {
            return 0;
        }

        if ($customer === null) {
            throw ValidationException::withMessages(['bonus_used' => 'Bonus ishlatish uchun mijozni tanlang.']);
        }

        if ($requested > $customer->bonus_balance) {
            throw ValidationException::withMessages([
                'bonus_used' => sprintf("Mijozda faqat %s so'm bonus bor.", number_format($customer->bonus_balance, 0, '.', ' ')),
            ]);
        }

        return min($requested, $goodsTotal);
    }

    /**
     * @phpstan-assert Product $product
     *
     * @throws ValidationException
     */
    private function ensureSellable(int $index, ?Product $product, int $quantity, int $unitPrice): void
    {
        if ($product === null) {
            throw ValidationException::withMessages([
                "items.{$index}.product_id" => 'Mahsulot topilmadi yoki sotuvda emas.',
            ]);
        }

        if ($unitPrice < $product->min_price) {
            throw ValidationException::withMessages([
                "items.{$index}.unit_price" => sprintf(
                    "\"%s\" uchun narx minimal narxdan (%s so'm) past bo'lishi mumkin emas.",
                    $product->name,
                    number_format($product->min_price, 0, '.', ' '),
                ),
            ]);
        }

        if ($quantity > $product->stock) {
            throw ValidationException::withMessages([
                "items.{$index}.quantity" => sprintf(
                    '"%s" omborda faqat %d dona qolgan.',
                    $product->name,
                    $product->stock,
                ),
            ]);
        }
    }
}
