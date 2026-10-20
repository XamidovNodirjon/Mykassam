<?php

use App\Models\Product;
use App\Models\Shop;
use Inertia\Testing\AssertableInertia;

it('adds received goods to stock and keeps the new purchase price', function () {
    $shop = Shop::factory()->create();
    $product = Product::factory()->for($shop)->create(['stock' => 2, 'cost_price' => 5_000]);

    $this->actingAs($shop->owner)->post(route('owner.shops.products.receipts.store', [$shop, $product]), [
        'quantity' => 30,
        'cost_price' => 5_500,
    ])->assertSessionHasNoErrors();

    expect($product->fresh())->stock->toBe(32)->cost_price->toBe(5_500);

    $this->actingAs($shop->owner)
        ->get(route('owner.shops.stock-receipts.index', $shop))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('Owner/StockReceipts/Index')
            ->where('receipts.data.0.quantity', 30)
            ->where('receipts.data.0.total', 165_000)
            ->where('receipts.data.0.user.id', $shop->owner_id));
});

it('lists only the products running low when asked', function () {
    $shop = Shop::factory()->create();
    $low = Product::factory()->for($shop)->create(['stock' => 3, 'min_stock' => 5]);
    Product::factory()->for($shop)->create(['stock' => 50, 'min_stock' => 5]);

    $this->actingAs($shop->owner)
        ->get(route('owner.shops.products.index', [$shop, 'low_stock' => 1]))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->where('lowStockCount', 1)
            ->has('products.data', 1)
            ->where('products.data.0.id', $low->id));
});

it('warns the owner about low stock on the dashboard only for plans with stock control', function () {
    $shop = Shop::factory()->create();
    Product::factory()->for($shop)->create(['stock' => 1, 'min_stock' => 5]);
    $basic = Shop::factory()->for($shop->owner, 'owner')->subscribed('start')->create();
    Product::factory()->for($basic)->create(['stock' => 0, 'min_stock' => 5]);

    $this->actingAs($shop->owner)
        ->get(route('owner.dashboard'))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->has('lowStock', 1)
            ->where('lowStock.0.shop_id', $shop->id));
});

it('needs a plan with stock control to receive goods', function () {
    $shop = Shop::factory()->subscribed('start')->create();
    $product = Product::factory()->for($shop)->create(['stock' => 2]);

    $this->actingAs($shop->owner)
        ->post(route('owner.shops.products.receipts.store', [$shop, $product]), ['quantity' => 5])
        ->assertRedirect();

    expect($product->fresh()->stock)->toBe(2);
});
