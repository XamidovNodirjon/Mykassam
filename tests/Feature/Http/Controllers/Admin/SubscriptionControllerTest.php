<?php

use App\Models\Plan;
use App\Models\Shop;
use App\Models\User;
use Inertia\Testing\AssertableInertia;

beforeEach(fn () => $this->freezeSecond());

it('lists shops by subscription with a revenue summary', function () {
    Shop::factory()->create();
    Shop::factory()->subscribed('business')->create();
    Shop::factory()->subscriptionExpired()->create();

    $response = $this->actingAs(User::factory()->admin()->create())->get(route('admin.subscriptions.index'));

    $response->assertInertia(fn (AssertableInertia $page) => $page
        ->component('Admin/Subscriptions/Index')
        ->has('shops.data', 3)
        ->where('summary', ['trial' => 1, 'active' => 1, 'expired' => 1, 'monthly_revenue' => 199_000])
        ->has('plans', 3));
});

it('filters shops whose subscription has expired', function () {
    Shop::factory()->create();
    $expired = Shop::factory()->subscriptionExpired()->create();

    $response = $this->actingAs(User::factory()->admin()->create())
        ->get(route('admin.subscriptions.index', ['status' => 'expired']));

    $response->assertInertia(fn (AssertableInertia $page) => $page
        ->has('shops.data', 1)
        ->where('shops.data.0.id', $expired->id)
        ->where('shops.data.0.subscription_status', 'expired'));
});

it('activates an expired shop for the paid period starting now', function () {
    $shop = Shop::factory()->subscriptionExpired()->create();

    $response = $this->actingAs(User::factory()->admin()->create())
        ->patch(route('admin.subscriptions.update', $shop), ['plan_id' => plan('business')->id, 'months' => 1]);

    $response->assertRedirect()->assertSessionHasNoErrors();
    $shop->refresh();
    expect($shop->plan_id)->toBe(plan('business')->id)
        ->and($shop->on_trial)->toBeFalse()
        ->and($shop->subscription_ends_at->equalTo(now()->addMonth()))->toBeTrue()
        ->and($shop->hasActiveSubscription())->toBeTrue();
});

it('adds paid months to the end of a running period', function () {
    $shop = Shop::factory()->create(['subscription_ends_at' => now()->addDays(5)]);

    $this->actingAs(User::factory()->admin()->create())
        ->patch(route('admin.subscriptions.update', $shop), ['plan_id' => plan('start')->id, 'months' => 3]);

    expect($shop->refresh()->subscription_ends_at->equalTo(now()->addDays(5)->addMonths(3)))->toBeTrue()
        ->and($shop->on_trial)->toBeFalse();
});

it('changes only the plan when no period is given', function () {
    $shop = Shop::factory()->subscribed('start')->create();
    $endsAt = $shop->subscription_ends_at;

    $this->actingAs(User::factory()->admin()->create())
        ->patch(route('admin.subscriptions.update', $shop), ['plan_id' => plan('business-plus')->id, 'months' => 0]);

    $shop->refresh();
    expect($shop->plan_id)->toBe(plan('business-plus')->id)
        ->and($shop->subscription_ends_at->equalTo($endsAt))->toBeTrue();
});

it('does not offer a deactivated plan', function () {
    $shop = Shop::factory()->create();
    $retired = Plan::factory()->create(['is_active' => false]);

    $this->actingAs(User::factory()->admin()->create())
        ->patch(route('admin.subscriptions.update', $shop), ['plan_id' => $retired->id, 'months' => 1])
        ->assertSessionHasErrors('plan_id');
});

it('is not available to owners', function () {
    $shop = Shop::factory()->subscriptionExpired()->create();

    $this->actingAs($shop->owner)
        ->patch(route('admin.subscriptions.update', $shop), ['plan_id' => plan('business')->id, 'months' => 12])
        ->assertForbidden();

    expect($shop->refresh()->hasActiveSubscription())->toBeFalse();
});
