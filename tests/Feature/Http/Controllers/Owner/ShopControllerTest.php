<?php

use App\Models\Shop;
use App\Models\ShopType;
use App\Models\User;

it('starts a new shop on the one month Business Plus trial counted from the owner sign-up', function () {
    $this->freezeSecond();
    $owner = User::factory()->owner()->create(['created_at' => now()->subDays(10)]);

    $this->actingAs($owner)->post(route('owner.shops.store'), [
        'name' => 'Yunusobod Market',
        'shop_type_id' => ShopType::factory()->create()->id,
        'is_active' => true,
    ]);

    $shop = Shop::query()->sole();
    expect($shop->plan_id)->toBe(plan('business-plus')->id)
        ->and($shop->on_trial)->toBeTrue()
        ->and($shop->subscription_ends_at->equalTo(now()->subDays(10)->addMonthNoOverflow()))->toBeTrue();
});

it('gives no new trial to a shop opened after the trial ended', function () {
    $owner = User::factory()->owner()->create(['created_at' => now()->subMonth()->subDay()]);

    $this->actingAs($owner)->post(route('owner.shops.store'), [
        'name' => 'Chilonzor Market',
        'shop_type_id' => ShopType::factory()->create()->id,
        'is_active' => true,
    ]);

    expect(Shop::query()->sole()->hasActiveSubscription())->toBeFalse();
});
