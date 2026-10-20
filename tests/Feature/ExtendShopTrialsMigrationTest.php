<?php

use App\Models\Shop;
use App\Models\User;

function runExtendShopTrialsMigration(): void
{
    (require database_path('migrations/2026_10_20_115010_extend_shop_trials_to_one_month.php'))->up();
}

it('extends running and recently ended trials to a month from the owner sign-up', function () {
    $this->freezeSecond();
    $owner = User::factory()->owner()->create(['created_at' => now()->subDays(20)]);
    $running = Shop::factory()->for($owner, 'owner')->create(['subscription_ends_at' => now()->addDays(2)]);
    $ended = Shop::factory()->for($owner, 'owner')->create(['subscription_ends_at' => now()->subDays(6)]);

    runExtendShopTrialsMigration();

    $trialEndsAt = now()->subDays(20)->addMonthNoOverflow();
    expect($running->refresh()->subscription_ends_at->equalTo($trialEndsAt))->toBeTrue()
        ->and($ended->refresh()->subscription_ends_at->equalTo($trialEndsAt))->toBeTrue()
        ->and($ended->hasActiveSubscription())->toBeTrue();
});

it('leaves paid subscriptions untouched', function () {
    $this->freezeSecond();
    $owner = User::factory()->owner()->create(['created_at' => now()->subDays(20)]);
    $paid = Shop::factory()->for($owner, 'owner')->subscribed()->create(['subscription_ends_at' => now()->addDay()]);

    runExtendShopTrialsMigration();

    expect($paid->refresh()->subscription_ends_at->equalTo(now()->addDay()))->toBeTrue();
});
