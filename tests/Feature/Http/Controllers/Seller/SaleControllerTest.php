<?php

use App\Enums\PaymentMethod;
use App\Models\Customer;
use App\Models\Debt;
use App\Models\Product;
use App\Models\Sale;
use App\Models\Shop;
use App\Models\User;
use Inertia\Testing\AssertableInertia;

describe('store', function () {
    it('records the sale with totals and profit and decrements stock', function () {
        $seller = User::factory()->seller()->create();
        $product = Product::factory()->for($seller->shop)->create([
            'cost_price' => 8_000,
            'min_price' => 9_000,
            'price' => 10_000,
            'stock' => 10,
        ]);

        $response = $this->actingAs($seller)->post(route('seller.sales.store'), [
            'payment_method' => 'card',
            'items' => [
                ['product_id' => $product->id, 'quantity' => 3, 'unit_price' => 9_500],
            ],
        ]);

        $response->assertRedirect(route('seller.sales.create'));

        $sale = Sale::query()->with('items')->sole();
        expect($sale->seller_id)->toBe($seller->id)
            ->and($sale->shop_id)->toBe($seller->shop_id)
            ->and($sale->payment_method)->toBe(PaymentMethod::Card)
            ->and($sale->total_amount)->toBe(28_500)
            ->and($sale->total_cost)->toBe(24_000)
            ->and($sale->profit)->toBe(4_500)
            ->and($sale->items_count)->toBe(3)
            ->and($sale->items->sole()->product_name)->toBe($product->name);
        expect($product->fresh()->stock)->toBe(7);
    });

    it('rejects a price below the product minimum price', function () {
        $seller = User::factory()->seller()->create();
        $product = Product::factory()->for($seller->shop)->create([
            'name' => 'Choy',
            'min_price' => 9_000,
            'price' => 10_000,
            'stock' => 10,
        ]);

        $response = $this->actingAs($seller)->post(route('seller.sales.store'), [
            'payment_method' => 'cash',
            'items' => [
                ['product_id' => $product->id, 'quantity' => 1, 'unit_price' => 8_999],
            ],
        ]);

        $response->assertSessionHasErrors([
            'items.0.unit_price' => "\"Choy\" uchun narx minimal narxdan (9 000 so'm) past bo'lishi mumkin emas.",
        ]);
        expect(Sale::query()->count())->toBe(0)
            ->and($product->fresh()->stock)->toBe(10);
    });

    it('rejects a quantity larger than the stock', function () {
        $seller = User::factory()->seller()->create();
        $product = Product::factory()->for($seller->shop)->create(['stock' => 2]);

        $response = $this->actingAs($seller)->post(route('seller.sales.store'), [
            'payment_method' => 'cash',
            'items' => [
                ['product_id' => $product->id, 'quantity' => 3, 'unit_price' => $product->price],
            ],
        ]);

        $response->assertSessionHasErrors('items.0.quantity');
        expect(Sale::query()->count())->toBe(0);
    });

    it('rejects a product from another shop', function () {
        $seller = User::factory()->seller()->create();
        $foreignProduct = Product::factory()->create();

        $response = $this->actingAs($seller)->post(route('seller.sales.store'), [
            'payment_method' => 'cash',
            'items' => [
                ['product_id' => $foreignProduct->id, 'quantity' => 1, 'unit_price' => $foreignProduct->price],
            ],
        ]);

        $response->assertSessionHasErrors('items.0.product_id');
        expect($foreignProduct->fresh()->stock)->toBe($foreignProduct->stock);
    });

    it('rejects an empty cart', function () {
        $seller = User::factory()->seller()->create();

        $response = $this->actingAs($seller)->post(route('seller.sales.store'), [
            'payment_method' => 'cash',
            'items' => [],
        ]);

        $response->assertSessionHasErrors(['items' => "Savatcha bo'sh."]);
    });

    it('forbids owners from using the point of sale', function () {
        $owner = User::factory()->owner()->create();

        $this->actingAs($owner)->post(route('seller.sales.store'), [])->assertForbidden();
    });
});

describe('create', function () {
    it('lists only active in-stock products of the seller shop without cost prices', function () {
        $seller = User::factory()->seller()->create();
        $sellable = Product::factory()->for($seller->shop)->create();
        Product::factory()->for($seller->shop)->outOfStock()->create();
        Product::factory()->for($seller->shop)->inactive()->create();
        Product::factory()->create();

        $response = $this->actingAs($seller)->get(route('seller.sales.create'));

        $response->assertInertia(fn (AssertableInertia $page) => $page
            ->component('Seller/Sales/Create')
            ->has('products', 1)
            ->where('products.0.id', $sellable->id)
            ->missing('products.0.cost_price'));
    });
});

describe('show', function () {
    it('forbids a seller from viewing another seller\'s sale', function () {
        $shop = Shop::factory()->create();
        $seller = User::factory()->seller($shop)->create();
        $otherSale = Sale::factory()->for($shop)->create();

        $this->actingAs($seller)->get(route('seller.sales.show', $otherSale))->assertForbidden();
    });

    it('hides cost and profit from the seller', function () {
        $seller = User::factory()->seller()->create();
        $sale = Sale::factory()->for($seller->shop)->for($seller, 'seller')->create();

        $response = $this->actingAs($seller)->get(route('seller.sales.show', $sale));

        $response->assertInertia(fn (AssertableInertia $page) => $page
            ->where('sale.id', $sale->id)
            ->missing('sale.profit')
            ->missing('sale.total_cost'));
    });
});

/**
 * A seller of a shop with 5% cashback and one product priced 100 000 so'm.
 *
 * @return array{0: User, 1: Product}
 */
function loyaltyShop(string $planSlug = 'business-plus'): array
{
    $shop = Shop::factory()->subscribed($planSlug)->create(['cashback_percent' => 5]);
    $seller = User::factory()->seller($shop)->create();
    $product = Product::factory()->for($shop)->create(['cost_price' => 60_000, 'min_price' => 90_000, 'price' => 100_000, 'stock' => 10]);

    return [$seller, $product];
}

describe('customers', function () {
    it('gives the customer cashback on a paid sale', function () {
        [$seller, $product] = loyaltyShop();
        $customer = Customer::factory()->for($seller->shop)->create();

        $this->actingAs($seller)->post(route('seller.sales.store'), [
            'payment_method' => 'cash',
            'customer_id' => $customer->id,
            'items' => [['product_id' => $product->id, 'quantity' => 2, 'unit_price' => 100_000]],
        ])->assertSessionHasNoErrors();

        $sale = Sale::query()->sole();
        expect($sale->customer_id)->toBe($customer->id)
            ->and($sale->bonus_earned)->toBe(10_000)
            ->and($customer->fresh()->bonus_balance)->toBe(10_000);
    });

    it('takes the spent bonus off the amount charged', function () {
        [$seller, $product] = loyaltyShop();
        $customer = Customer::factory()->for($seller->shop)->create(['bonus_balance' => 20_000]);

        $this->actingAs($seller)->post(route('seller.sales.store'), [
            'payment_method' => 'card',
            'customer_id' => $customer->id,
            'bonus_used' => 20_000,
            'items' => [['product_id' => $product->id, 'quantity' => 1, 'unit_price' => 100_000]],
        ])->assertSessionHasNoErrors();

        $sale = Sale::query()->sole();
        expect($sale->total_amount)->toBe(80_000)
            ->and($sale->profit)->toBe(20_000)
            ->and($sale->bonus_earned)->toBe(4_000)
            ->and($customer->fresh()->bonus_balance)->toBe(4_000);
    });

    it('refuses to spend more bonus than the customer has', function () {
        [$seller, $product] = loyaltyShop();
        $customer = Customer::factory()->for($seller->shop)->create(['bonus_balance' => 5_000]);

        $this->actingAs($seller)->post(route('seller.sales.store'), [
            'payment_method' => 'cash',
            'customer_id' => $customer->id,
            'bonus_used' => 6_000,
            'items' => [['product_id' => $product->id, 'quantity' => 1, 'unit_price' => 100_000]],
        ])->assertSessionHasErrors('bonus_used');

        expect(Sale::query()->count())->toBe(0)
            ->and($product->fresh()->stock)->toBe(10);
    });

    it('books a credit sale into the debt book without cashback', function () {
        [$seller, $product] = loyaltyShop();
        $customer = Customer::factory()->for($seller->shop)->create();

        $this->actingAs($seller)->post(route('seller.sales.store'), [
            'payment_method' => 'debt',
            'customer_id' => $customer->id,
            'due_date' => today()->addWeek()->toDateString(),
            'items' => [['product_id' => $product->id, 'quantity' => 1, 'unit_price' => 100_000]],
        ])->assertSessionHasNoErrors();

        $sale = Sale::query()->sole();
        $debt = Debt::query()->sole();
        expect($sale->payment_method)->toBe(PaymentMethod::Debt)
            ->and($sale->bonus_earned)->toBe(0)
            ->and($debt->sale_id)->toBe($sale->id)
            ->and($debt->remaining())->toBe(100_000)
            ->and($debt->due_date->toDateString())->toBe(today()->addWeek()->toDateString());
    });

    it('needs a customer and a due date to sell on credit', function () {
        [$seller, $product] = loyaltyShop();

        $this->actingAs($seller)->post(route('seller.sales.store'), [
            'payment_method' => 'debt',
            'items' => [['product_id' => $product->id, 'quantity' => 1, 'unit_price' => 100_000]],
        ])->assertSessionHasErrors(['customer_id', 'due_date']);
    });

    it('does not sell on credit on a plan without the debt book', function () {
        [$seller, $product] = loyaltyShop('business');
        $customer = Customer::factory()->for($seller->shop)->create();

        $this->actingAs($seller)->post(route('seller.sales.store'), [
            'payment_method' => 'debt',
            'customer_id' => $customer->id,
            'due_date' => today()->addWeek()->toDateString(),
            'items' => [['product_id' => $product->id, 'quantity' => 1, 'unit_price' => 100_000]],
        ])->assertRedirect();

        expect(Sale::query()->count())->toBe(0)
            ->and($product->fresh()->stock)->toBe(10);
    });

    it('offers credit and customers on the POS only when the plan has them', function () {
        [$seller] = loyaltyShop('start');

        $this->actingAs($seller)->get(route('seller.sales.create'))->assertInertia(fn (AssertableInertia $page) => $page
            ->where('customersEnabled', false)
            ->where('paymentMethods', PaymentMethod::options(PaymentMethod::immediate())));
    });

    it('finds and signs up customers from the POS', function () {
        [$seller] = loyaltyShop();
        Customer::factory()->for($seller->shop)->create(['name' => 'Akmal Toirov', 'phone' => '998901112233']);
        Customer::factory()->create(['name' => 'Akmal Boshqa']);

        $this->actingAs($seller)->getJson(route('seller.customers.index', ['search' => 'akmal']))
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.phone', '998901112233');

        $this->actingAs($seller)->postJson(route('seller.customers.store'), ['name' => 'Malika', 'phone' => '90 765 43 21'])
            ->assertCreated()
            ->assertJsonPath('data.phone', '998907654321')
            ->assertJsonPath('data.bonus_balance', 0);
    });
});
