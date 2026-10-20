<?php

use App\Enums\PlanFeature;
use App\Models\Plan;
use App\Models\Shop;
use App\Models\User;
use Inertia\Testing\AssertableInertia;

it('lists the plans with their shop counts', function () {
    Shop::factory()->count(2)->create();

    $response = $this->actingAs(User::factory()->admin()->create())->get(route('admin.plans.index'));

    $response->assertInertia(fn (AssertableInertia $page) => $page
        ->component('Admin/Plans/Index')
        ->has('plans', 3)
        ->where('plans.2.slug', 'business-plus')
        ->where('plans.2.shops_count', 2)
        ->where('trialPlanId', plan('business-plus')->id)
        ->has('features', count(PlanFeature::cases())));
});

it('creates a plan with limits and modules', function () {
    $this->actingAs(User::factory()->admin()->create())->post(route('admin.plans.store'), [
        'name' => 'Mini Market',
        'price' => 149_000,
        'max_sellers' => 2,
        'max_products' => '',
        'features' => ['customers', 'debts'],
        'is_active' => true,
        'sort_order' => 2,
    ])->assertSessionHasNoErrors();

    $plan = Plan::query()->where('slug', 'mini-market')->sole();
    expect($plan->price)->toBe(149_000)
        ->and($plan->max_sellers)->toBe(2)
        ->and($plan->max_products)->toBeNull()
        ->and($plan->hasFeature(PlanFeature::Debts))->toBeTrue()
        ->and($plan->hasFeature(PlanFeature::ProductImport))->toBeFalse();
});

it('applies a changed plan to its shops at once', function () {
    $shop = Shop::factory()->subscribed('start')->create();

    $this->actingAs(User::factory()->admin()->create())->put(route('admin.plans.update', plan('start')), [
        'name' => 'Start',
        'price' => 109_000,
        'max_sellers' => 1,
        'max_products' => 500,
        'features' => ['customers'],
        'is_active' => true,
        'sort_order' => 1,
    ])->assertSessionHasNoErrors();

    expect($shop->fresh()->hasFeature(PlanFeature::Customers))->toBeTrue()
        ->and(plan('start')->price)->toBe(109_000);
});

it('keeps the trial plan active', function () {
    $this->actingAs(User::factory()->admin()->create())->put(route('admin.plans.update', plan('business-plus')), [
        'name' => 'Biznes Plus',
        'price' => 349_000,
        'features' => [],
        'is_active' => false,
        'sort_order' => 3,
    ])->assertRedirect();

    expect(plan('business-plus')->is_active)->toBeTrue();
});

it('is not available to owners', function () {
    $this->actingAs(User::factory()->owner()->create())
        ->get(route('admin.plans.index'))
        ->assertForbidden();
});
