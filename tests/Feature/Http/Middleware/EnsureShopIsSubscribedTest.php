<?php

use App\Models\Product;
use App\Models\Sale;
use App\Models\Shop;
use App\Models\User;
use Inertia\Testing\AssertableInertia;

it('stops a seller from selling once the shop subscription expires', function () {
    $seller = User::factory()->seller(Shop::factory()->subscriptionExpired()->create())->create();
    $product = Product::factory()->for($seller->shop)->create(['min_price' => 1_000, 'price' => 1_000, 'stock' => 5]);

    $response = $this->actingAs($seller)->post(route('seller.sales.store'), [
        'payment_method' => 'cash',
        'items' => [['product_id' => $product->id, 'quantity' => 1, 'unit_price' => 1_000]],
    ]);

    $response->assertRedirect();
    expect(Sale::query()->count())->toBe(0)
        ->and($product->fresh()->stock)->toBe(5);
});

it('locks the seller pages behind the payment notice', function () {
    $seller = User::factory()->seller(Shop::factory()->subscriptionExpired()->create())->create();

    $response = $this->actingAs($seller)->get(route('seller.sales.create'));

    $response->assertOk()->assertInertia(fn (AssertableInertia $page) => $page
        ->where('subscription.locked', true)
        ->where('subscription.shops.0.id', $seller->shop_id)
        ->where('support.telegram', 'Nodirjon10_01')
        ->has('subscription.plans', 3));
});

it('stops an owner from changing an expired shop but keeps it viewable', function () {
    $shop = Shop::factory()->subscriptionExpired()->create();

    $this->actingAs($shop->owner)
        ->post(route('owner.shops.products.store', $shop), [
            'name' => 'Non',
            'cost_price' => 3_000,
            'min_price' => 3_500,
            'price' => 4_000,
            'stock' => 50,
        ])
        ->assertRedirect();

    expect($shop->products()->count())->toBe(0);

    $this->actingAs($shop->owner)
        ->get(route('owner.shops.products.index', $shop))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page->where('subscription.locked', true));
});

it('only warns an owner whose other shops are still subscribed', function () {
    $owner = User::factory()->owner()->create();
    $expired = Shop::factory()->for($owner, 'owner')->subscriptionExpired()->create();
    Shop::factory()->for($owner, 'owner')->create();

    $response = $this->actingAs($owner)->get(route('owner.dashboard'));

    $response->assertInertia(fn (AssertableInertia $page) => $page
        ->where('subscription.locked', false)
        ->has('subscription.shops', 1)
        ->where('subscription.shops.0.id', $expired->id));
});

it('shares no notice while the trial is running', function () {
    $seller = User::factory()->seller()->create();

    $this->actingAs($seller)
        ->get(route('seller.sales.create'))
        ->assertInertia(fn (AssertableInertia $page) => $page->where('subscription', null));
});
