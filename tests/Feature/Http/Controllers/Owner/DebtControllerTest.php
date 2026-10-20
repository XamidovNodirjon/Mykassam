<?php

use App\Models\Customer;
use App\Models\Debt;
use App\Models\Shop;
use Inertia\Testing\AssertableInertia;

it('writes a debt into the book by hand', function () {
    $shop = Shop::factory()->create();
    $customer = Customer::factory()->for($shop)->create();

    $this->actingAs($shop->owner)->post(route('owner.shops.debts.store', $shop), [
        'customer_id' => $customer->id,
        'amount' => 250_000,
        'due_date' => today()->addDays(10)->toDateString(),
        'note' => 'Un 2 qop',
    ])->assertSessionHasNoErrors();

    $debt = $shop->debts()->sole();
    expect($debt->remaining())->toBe(250_000)
        ->and($debt->customer_id)->toBe($customer->id);
});

it('records partial and final repayments', function () {
    $shop = Shop::factory()->create();
    $debt = Debt::factory()->for(Customer::factory()->for($shop))->create(['shop_id' => $shop->id, 'amount' => 100_000]);

    $this->actingAs($shop->owner)->post(route('owner.shops.debts.pay', [$shop, $debt]), ['amount' => 40_000]);
    expect($debt->fresh()->remaining())->toBe(60_000);

    $this->actingAs($shop->owner)->post(route('owner.shops.debts.pay', [$shop, $debt]), ['amount' => 60_000]);
    expect($debt->fresh()->remaining())->toBe(0)
        ->and($debt->payments()->count())->toBe(2);
});

it('refuses a payment larger than what is owed', function () {
    $shop = Shop::factory()->create();
    $debt = Debt::factory()->for(Customer::factory()->for($shop))->create(['shop_id' => $shop->id, 'amount' => 50_000]);

    $this->actingAs($shop->owner)
        ->post(route('owner.shops.debts.pay', [$shop, $debt]), ['amount' => 50_001])
        ->assertSessionHasErrors('amount');

    expect($debt->fresh()->paid_amount)->toBe(0);
});

it('summarizes the outstanding and overdue debts', function () {
    $shop = Shop::factory()->create();
    $customer = Customer::factory()->for($shop)->create();
    Debt::factory()->for($customer)->create(['shop_id' => $shop->id, 'amount' => 100_000]);
    Debt::factory()->for($customer)->overdue()->create(['shop_id' => $shop->id, 'amount' => 50_000, 'paid_amount' => 20_000]);
    Debt::factory()->for($customer)->paid()->create(['shop_id' => $shop->id]);

    $response = $this->actingAs($shop->owner)->get(route('owner.shops.debts.index', [$shop, 'status' => 'overdue']));

    $response->assertInertia(fn (AssertableInertia $page) => $page
        ->component('Owner/Debts/Index')
        ->where('summary', ['outstanding' => 130_000, 'open' => 1, 'overdue' => 1])
        ->has('debts.data', 1)
        ->where('debts.data.0.status', 'overdue'));
});

it('needs a plan with the debt book to write debts', function () {
    $shop = Shop::factory()->subscribed('business')->create();
    $customer = Customer::factory()->for($shop)->create();

    $this->actingAs($shop->owner)->post(route('owner.shops.debts.store', $shop), [
        'customer_id' => $customer->id,
        'amount' => 10_000,
        'due_date' => today()->toDateString(),
    ])->assertRedirect();

    expect($shop->debts()->count())->toBe(0);
});
