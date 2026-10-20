<?php

use App\Enums\UserRole;
use App\Models\Sale;
use App\Models\Shop;
use App\Models\User;

describe('store', function () {
    it('creates a seller in the shop and flashes the plain credentials once', function () {
        $shop = Shop::factory()->create();

        $response = $this->actingAs($shop->owner)->post(route('owner.shops.sellers.store', $shop), [
            'first_name' => 'Aziz',
            'last_name' => 'Rahimov',
            'phone' => '+998 93 111 22 33',
            'login' => 's931112233',
            'password' => 'k7m2p9xa',
            'is_active' => true,
        ]);

        $response->assertRedirect(route('owner.shops.sellers.index', $shop))
            ->assertInertiaFlash('credentials', ['login' => 's931112233', 'password' => 'k7m2p9xa']);

        $seller = User::query()->where('login', 's931112233')->sole();
        expect($seller->role)->toBe(UserRole::Seller)
            ->and($seller->shop_id)->toBe($shop->id)
            ->and($seller->phone)->toBe('998931112233');
    });

    it('lets the created seller sign in with the generated credentials', function () {
        $shop = Shop::factory()->create();

        $this->actingAs($shop->owner)->post(route('owner.shops.sellers.store', $shop), [
            'first_name' => 'Aziz',
            'last_name' => 'Rahimov',
            'phone' => '998931112233',
            'login' => 's931112233',
            'password' => 'k7m2p9xa',
            'is_active' => true,
        ]);
        $this->post(route('logout'));

        $response = $this->post(route('login.store'), ['login' => 's931112233', 'password' => 'k7m2p9xa']);

        $response->assertRedirect(route('seller.dashboard'));
    });

    it('rejects a login that is already taken', function () {
        $shop = Shop::factory()->create();
        User::factory()->seller($shop)->create(['login' => 's931112233']);

        $response = $this->actingAs($shop->owner)->post(route('owner.shops.sellers.store', $shop), [
            'first_name' => 'Aziz',
            'last_name' => 'Rahimov',
            'phone' => '998931112233',
            'login' => 's931112233',
            'password' => 'k7m2p9xa',
        ]);

        $response->assertSessionHasErrors(['login' => 'Bu login band. Boshqasini yarating.']);
    });

    it('forbids adding a seller to another owner\'s shop', function () {
        $shop = Shop::factory()->create();
        $otherOwner = User::factory()->owner()->create();

        $response = $this->actingAs($otherOwner)->post(route('owner.shops.sellers.store', $shop), [
            'first_name' => 'Aziz',
            'last_name' => 'Rahimov',
            'phone' => '998931112233',
            'login' => 's931112233',
            'password' => 'k7m2p9xa',
        ]);

        $response->assertForbidden();
        expect(User::query()->where('login', 's931112233')->exists())->toBeFalse();
    });
});

describe('update', function () {
    it('keeps the current password when none is sent', function () {
        $seller = User::factory()->seller()->create();
        $passwordHash = $seller->password;

        $response = $this->actingAs($seller->shop->owner)->put(route('owner.shops.sellers.update', [$seller->shop, $seller]), [
            'first_name' => 'Yangi',
            'last_name' => $seller->last_name,
            'phone' => $seller->phone,
            'login' => $seller->login,
            'password' => '',
            'is_active' => true,
        ]);

        $response->assertRedirect(route('owner.shops.sellers.index', $seller->shop));
        expect($seller->fresh()->first_name)->toBe('Yangi')
            ->and($seller->fresh()->password)->toBe($passwordHash);
    });

    it('returns 404 for a seller that belongs to a different shop of the same owner', function () {
        $owner = User::factory()->owner()->create();
        [$shopA, $shopB] = Shop::factory()->count(2)->for($owner, 'owner')->create();
        $seller = User::factory()->seller($shopB)->create();

        $response = $this->actingAs($owner)->get(route('owner.shops.sellers.edit', [$shopA, $seller]));

        $response->assertNotFound();
    });
});

describe('destroy', function () {
    it('refuses to delete a seller who has sales', function () {
        $seller = User::factory()->seller()->create();
        Sale::factory()->for($seller->shop)->for($seller, 'seller')->create();

        $response = $this->actingAs($seller->shop->owner)
            ->from(route('owner.shops.sellers.edit', [$seller->shop, $seller]))
            ->delete(route('owner.shops.sellers.destroy', [$seller->shop, $seller]));

        $response->assertRedirect(route('owner.shops.sellers.edit', [$seller->shop, $seller]));
        $this->assertModelExists($seller);
    });
});
