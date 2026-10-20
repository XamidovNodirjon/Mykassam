<?php

use App\Models\Product;
use App\Models\Shop;
use App\Models\User;

it('stops adding sellers above the plan limit but keeps the existing ones', function () {
    $shop = Shop::factory()->subscribed('start')->create();
    $existing = User::factory()->seller($shop)->create();

    $this->actingAs($shop->owner)->post(route('owner.shops.sellers.store', $shop), [
        'first_name' => 'Aziz',
        'last_name' => 'Rahimov',
        'phone' => '+998 93 111 22 33',
        'login' => 's931112233',
        'password' => 'k7m2p9xa',
        'is_active' => true,
    ])->assertRedirect();

    expect($shop->sellers()->pluck('id')->all())->toBe([$existing->id]);
});

it('stops adding products above the plan limit', function () {
    $shop = Shop::factory()->subscribed('start')->create();
    Product::factory()->count(300)->for($shop)->create();

    $this->actingAs($shop->owner)->post(route('owner.shops.products.store', $shop), [
        'name' => 'Non',
        'cost_price' => 3_000,
        'min_price' => 3_500,
        'price' => 4_000,
        'stock' => 50,
    ])->assertRedirect();

    expect($shop->products()->count())->toBe(300);
});

it('lets a downgraded shop keep selling what it already has', function () {
    $shop = Shop::factory()->subscribed('start')->create();
    $seller = User::factory()->seller($shop)->create();
    User::factory()->seller($shop)->create();
    $product = Product::factory()->for($shop)->create(['min_price' => 1_000, 'price' => 1_000, 'stock' => 5]);

    $this->actingAs($seller)->post(route('seller.sales.store'), [
        'payment_method' => 'cash',
        'items' => [['product_id' => $product->id, 'quantity' => 1, 'unit_price' => 1_000]],
    ])->assertSessionHasNoErrors();

    expect($product->fresh()->stock)->toBe(4);
});
