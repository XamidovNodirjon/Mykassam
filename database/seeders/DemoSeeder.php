<?php

namespace Database\Seeders;

use App\Enums\PaymentMethod;
use App\Enums\UserRole;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Shop;
use App\Models\ShopType;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;

/**
 * Local demo data: owner 998901234567 / password, sellers s901112233 and s901112244 / password.
 */
class DemoSeeder extends Seeder
{
    public function run(): void
    {
        if (User::query()->where('login', '998901234567')->exists()) {
            return;
        }

        $owner = User::factory()->owner()->create([
            'first_name' => 'Jasur',
            'last_name' => 'Karimov',
            'phone' => '998901234567',
            'login' => '998901234567',
        ]);

        $type = ShopType::query()->where('name', 'Oziq-ovqat')->firstOrFail();

        $shops = collect(['Chilonzor Market', 'Yunusobod Market'])->map(
            fn (string $name): Shop => Shop::factory()->for($owner, 'owner')->for($type, 'type')->create(['name' => $name]),
        );

        $sellerPhones = [['998901112233', '998901112244'], ['998901112255']];

        foreach ($shops as $index => $shop) {
            $sellers = collect($sellerPhones[$index])->map(fn (string $phone): User => User::factory()->create([
                'phone' => $phone,
                'login' => 's'.substr($phone, -9),
                'role' => UserRole::Seller,
                'shop_id' => $shop->id,
            ]));

            $products = Product::factory()->count(15)->for($shop)->create();

            $this->seedSales($shop, $sellers, $products);
        }
    }

    /**
     * @param  Collection<int, User>  $sellers
     * @param  Collection<int, Product>  $products
     */
    private function seedSales(Shop $shop, Collection $sellers, Collection $products): void
    {
        $today = CarbonImmutable::today();

        for ($daysAgo = 29; $daysAgo >= 0; $daysAgo--) {
            $salesPerDay = random_int(2, 7);

            for ($i = 0; $i < $salesPerDay; $i++) {
                $createdAt = $today->subDays($daysAgo)->setTime(random_int(9, 20), random_int(0, 59));
                $lines = $products->random(random_int(1, 3))->map(function (Product $product): array {
                    $quantity = random_int(1, 3);
                    $unitPrice = random_int($product->min_price, $product->price);

                    return [
                        'product_id' => $product->id,
                        'product_name' => $product->name,
                        'quantity' => $quantity,
                        'unit_price' => $unitPrice,
                        'cost_price' => $product->cost_price,
                        'total' => $quantity * $unitPrice,
                    ];
                });

                $totalAmount = $lines->sum('total');
                $totalCost = $lines->sum(fn (array $line): int => $line['cost_price'] * $line['quantity']);

                $sale = Sale::query()->forceCreate([
                    'shop_id' => $shop->id,
                    'seller_id' => $sellers->random()->id,
                    'total_amount' => $totalAmount,
                    'total_cost' => $totalCost,
                    'profit' => $totalAmount - $totalCost,
                    'items_count' => $lines->sum('quantity'),
                    'payment_method' => collect(PaymentMethod::immediate())->random(),
                    'created_at' => $createdAt,
                    'updated_at' => $createdAt,
                ]);

                SaleItem::query()->insert(
                    $lines->map(fn (array $line): array => [...$line, 'sale_id' => $sale->id])->all(),
                );
            }
        }
    }
}
