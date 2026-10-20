<?php

use App\Models\Customer;
use App\Models\Debt;
use App\Models\Shop;
use Inertia\Testing\AssertableInertia;

it('adds a customer with a normalized phone number', function () {
    $shop = Shop::factory()->create();

    $this->actingAs($shop->owner)->post(route('owner.shops.customers.store', $shop), [
        'name' => 'Dilnoza Karimova',
        'phone' => '+998 93 555 44 33',
    ])->assertSessionHasNoErrors();

    expect($shop->customers()->sole()->phone)->toBe('998935554433');
});

it('lists customers with their unpaid debt', function () {
    $shop = Shop::factory()->create();
    $customer = Customer::factory()->for($shop)->create();
    Debt::factory()->for($customer)->create(['shop_id' => $shop->id, 'amount' => 100_000, 'paid_amount' => 30_000]);
    Debt::factory()->for($customer)->paid()->create(['shop_id' => $shop->id]);

    $response = $this->actingAs($shop->owner)->get(route('owner.shops.customers.index', $shop));

    $response->assertInertia(fn (AssertableInertia $page) => $page
        ->component('Owner/Customers/Index')
        ->where('customers.data.0.debt', 70_000));
});

it('keeps customers readable but frozen after a downgrade to a plan without them', function () {
    $shop = Shop::factory()->subscribed('start')->create();
    Customer::factory()->for($shop)->create();

    $this->actingAs($shop->owner)
        ->get(route('owner.shops.customers.index', $shop))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page->has('customers.data', 1)->where('shop.features', []));

    $this->actingAs($shop->owner)
        ->post(route('owner.shops.customers.store', $shop), ['name' => 'Yangi mijoz'])
        ->assertRedirect();

    expect($shop->customers()->count())->toBe(1);
});

it('refuses to delete a customer with debt history', function () {
    $shop = Shop::factory()->create();
    $customer = Customer::factory()->for($shop)->create();
    Debt::factory()->for($customer)->paid()->create(['shop_id' => $shop->id]);

    $this->actingAs($shop->owner)
        ->delete(route('owner.shops.customers.destroy', [$shop, $customer]))
        ->assertRedirect();

    expect($customer->fresh())->not->toBeNull();
});

it('does not let an owner reach another shop\'s customer', function () {
    $shop = Shop::factory()->create();
    $foreign = Customer::factory()->create();

    $this->actingAs($shop->owner)
        ->put(route('owner.shops.customers.update', [$shop, $foreign]), ['name' => 'X'])
        ->assertNotFound();
});
