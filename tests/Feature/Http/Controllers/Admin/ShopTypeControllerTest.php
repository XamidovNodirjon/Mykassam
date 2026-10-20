<?php

use App\Models\Shop;
use App\Models\ShopType;
use App\Models\User;

it('creates a shop type', function () {
    $admin = User::factory()->admin()->create();

    $response = $this->actingAs($admin)
        ->from(route('admin.shop-types.index'))
        ->post(route('admin.shop-types.store'), ['name' => 'Dorixona', 'is_active' => true]);

    $response->assertRedirect(route('admin.shop-types.index'));
    expect(ShopType::query()->where('name', 'Dorixona')->exists())->toBeTrue();
});

it('refuses to delete a shop type that shops use', function () {
    $admin = User::factory()->admin()->create();
    $shop = Shop::factory()->create();

    $this->actingAs($admin)
        ->from(route('admin.shop-types.index'))
        ->delete(route('admin.shop-types.destroy', $shop->type))
        ->assertRedirect(route('admin.shop-types.index'));

    $this->assertModelExists($shop->type);
});

it('forbids owners from managing shop types', function () {
    $owner = User::factory()->owner()->create();

    $this->actingAs($owner)
        ->post(route('admin.shop-types.store'), ['name' => 'Dorixona'])
        ->assertForbidden();
});
