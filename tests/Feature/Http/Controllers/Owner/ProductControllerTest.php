<?php

use App\Models\Product;
use App\Models\Shop;
use App\Models\User;

it('creates a product with cost, minimum and standard prices', function () {
    $shop = Shop::factory()->create();

    $response = $this->actingAs($shop->owner)->post(route('owner.shops.products.store', $shop), [
        'name' => 'Non',
        'barcode' => '4780000000011',
        'cost_price' => 3_000,
        'min_price' => 3_500,
        'price' => 4_000,
        'stock' => 50,
        'is_active' => true,
    ]);

    $response->assertRedirect(route('owner.shops.products.index', $shop));
    $product = Product::query()->sole();
    expect($product->shop_id)->toBe($shop->id)
        ->and($product->cost_price)->toBe(3_000)
        ->and($product->min_price)->toBe(3_500)
        ->and($product->price)->toBe(4_000);
});

it('rejects a standard price below the minimum price', function () {
    $shop = Shop::factory()->create();

    $response = $this->actingAs($shop->owner)->post(route('owner.shops.products.store', $shop), [
        'name' => 'Non',
        'cost_price' => 3_000,
        'min_price' => 4_000,
        'price' => 3_500,
        'stock' => 1,
    ]);

    $response->assertSessionHasErrors(['price' => "Sotuv narxi minimal narxdan kam bo'lishi mumkin emas."]);
});

it('returns 404 when the product belongs to another shop', function () {
    $shop = Shop::factory()->create();
    $foreignProduct = Product::factory()->create();

    $response = $this->actingAs($shop->owner)->get(route('owner.shops.products.edit', [$shop, $foreignProduct]));

    $response->assertNotFound();
});

it('forbids listing products of another owner\'s shop', function () {
    $shop = Shop::factory()->create();

    $this->actingAs(User::factory()->owner()->create())
        ->get(route('owner.shops.products.index', $shop))
        ->assertForbidden();
});
