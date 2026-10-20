<?php

use App\Models\Sale;
use App\Models\Shop;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Inertia\Testing\AssertableInertia;

it('sums revenue and profit of the owner\'s shops only', function () {
    $shop = Shop::factory()->create();
    $seller = User::factory()->seller($shop)->create();
    Sale::factory()->for($shop)->for($seller, 'seller')->create(['total_amount' => 100_000, 'total_cost' => 70_000, 'profit' => 30_000]);
    Sale::factory()->for($shop)->for($seller, 'seller')->create(['total_amount' => 50_000, 'total_cost' => 40_000, 'profit' => 10_000]);
    Sale::factory()->create(['total_amount' => 999_000]);

    $response = $this->actingAs($shop->owner)->get(route('owner.dashboard', ['period' => 'today']));

    $response->assertInertia(fn (AssertableInertia $page) => $page
        ->component('Owner/Dashboard')
        ->where('totals.revenue', 150_000)
        ->where('totals.profit', 40_000)
        ->where('totals.sales_count', 2)
        ->where('totals.average_check', 75_000)
        ->has('sellers', 1)
        ->where('sellers.0.revenue', 150_000)
        ->where('sellers.0.profit', 40_000));
});

it('excludes sales outside the selected period', function () {
    $shop = Shop::factory()->create();
    $seller = User::factory()->seller($shop)->create();
    Sale::factory()->for($shop)->for($seller, 'seller')->create(['total_amount' => 10_000]);
    Sale::factory()->for($shop)->for($seller, 'seller')->create([
        'total_amount' => 20_000,
        'created_at' => now()->subDays(10),
    ]);

    $response = $this->actingAs($shop->owner)->get(route('owner.dashboard', ['period' => 'week']));

    $response->assertInertia(fn (AssertableInertia $page) => $page
        ->where('totals.revenue', 10_000)
        ->has('daily', 7));
});

it('runs the same number of queries regardless of how many sellers there are', function () {
    $shop = Shop::factory()->create();

    $countQueries = function () use ($shop): int {
        DB::flushQueryLog();
        DB::enableQueryLog();
        $this->actingAs($shop->owner)->get(route('owner.dashboard'))->assertOk();

        return count(DB::getQueryLog());
    };

    User::factory()->seller($shop)->create();
    $countQueries(); // Warm up one-time boot queries.
    $queriesWithOneSeller = $countQueries();

    User::factory()->count(5)->seller($shop)->create();
    $queriesWithSixSellers = $countQueries();

    expect($queriesWithSixSellers)->toBe($queriesWithOneSeller);
});

it('forbids sellers from opening the owner dashboard', function () {
    $seller = User::factory()->seller()->create();

    $this->actingAs($seller)->get(route('owner.dashboard'))->assertForbidden();
});
