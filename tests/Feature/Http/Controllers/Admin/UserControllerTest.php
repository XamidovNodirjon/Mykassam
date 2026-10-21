<?php

use App\Models\Customer;
use App\Models\Debt;
use App\Models\Product;
use App\Models\Sale;
use App\Models\Shop;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Inertia\Testing\AssertableInertia;

function recordSession(User $user, int $lastActivity): void
{
    DB::table('sessions')->insert([
        'id' => fake()->uuid(),
        'user_id' => $user->id,
        'ip_address' => '127.0.0.1',
        'user_agent' => 'Pest',
        'payload' => '',
        'last_activity' => $lastActivity,
    ]);
}

it('lists every account with its role, shop and latest activity', function () {
    $this->freezeSecond();
    $admin = User::factory()->admin()->create();
    $owner = User::factory()->owner()->create(['first_name' => 'Aziz']);
    $shop = Shop::factory()->for($owner, 'owner')->create(['name' => 'Baraka']);
    $seller = User::factory()->seller($shop)->create();
    User::factory()->owner()->create(['is_active' => false]);
    recordSession($seller, now()->subMinutes(2)->getTimestamp());
    recordSession($owner, now()->subDay()->getTimestamp());

    $response = $this->actingAs($admin)->get(route('admin.users.index'));

    $response->assertOk()->assertInertia(fn (AssertableInertia $page) => $page
        ->component('Admin/Users/Index')
        ->has('users.data', 4)
        ->where('summary', ['total' => 4, 'owners' => 2, 'sellers' => 1, 'blocked' => 1, 'online' => 1])
        ->where('users.data.1.role', 'seller')
        ->where('users.data.1.shop.name', 'Baraka')
        ->where('users.data.1.shop.owner.first_name', 'Aziz')
        ->where('users.data.1.sales_count', 0)
        ->where('users.data.1.last_seen_at', now()->subMinutes(2)->toIso8601String())
        ->where('users.data.2.shops_count', 1)
        ->where('users.data.3.last_seen_at', null));
});

it('filters accounts by role and search term', function () {
    $admin = User::factory()->admin()->create();
    User::factory()->owner()->create(['first_name' => 'Aziz']);
    $bobur = User::factory()->owner()->create(['first_name' => 'Bobur']);
    User::factory()->seller(Shop::factory()->for($bobur, 'owner')->create())->create(['first_name' => 'Azamat']);

    $response = $this->actingAs($admin)->get(route('admin.users.index', ['role' => 'owner', 'search' => 'Az']));

    $response->assertInertia(fn (AssertableInertia $page) => $page
        ->has('users.data', 1)
        ->where('users.data.0.first_name', 'Aziz')
        ->where('filters', ['search' => 'Az', 'role' => 'owner']));
});

it('is not available to owners', function () {
    $this->actingAs(User::factory()->owner()->create())
        ->get(route('admin.users.index'))
        ->assertForbidden();
});

it('updates any account and keeps an owner signing in with the phone', function () {
    $owner = User::factory()->owner()->create(['phone' => '998901234567', 'login' => '998901234567']);

    $response = $this->actingAs(User::factory()->admin()->create())->put(route('admin.users.update', $owner), [
        'first_name' => 'Aziz',
        'last_name' => 'Karimov',
        'phone' => '+998 93 111 22 33',
        'login' => 'ignored',
        'password' => 'yangi1',
        'is_active' => false,
    ]);

    $response->assertRedirect()->assertSessionHasNoErrors();
    $owner->refresh();
    expect($owner->full_name)->toBe('Aziz Karimov')
        ->and($owner->phone)->toBe('998931112233')
        ->and($owner->login)->toBe('998931112233')
        ->and($owner->is_active)->toBeFalse()
        ->and(Hash::check('yangi1', $owner->password))->toBeTrue();
});

it('keeps the password when none is given and rejects a taken login', function () {
    $seller = User::factory()->seller()->create(['login' => 's1']);
    User::factory()->seller()->create(['login' => 'band']);
    $password = $seller->password;
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)->put(route('admin.users.update', $seller), [
        'first_name' => 'Ali', 'phone' => '998901234567', 'login' => 'band', 'is_active' => true,
    ])->assertSessionHasErrors(['login' => 'Bu login band. Boshqasini tanlang.']);

    $this->actingAs($admin)->put(route('admin.users.update', $seller), [
        'first_name' => 'Ali', 'phone' => '998901234567', 'login' => 'ali_1', 'password' => '', 'is_active' => true,
    ])->assertSessionHasNoErrors();

    expect($seller->refresh()->login)->toBe('ali_1')
        ->and($seller->password)->toBe($password);
});

it('deletes an owner with all shop data and their sellers', function () {
    $owner = User::factory()->owner()->create();
    $shop = Shop::factory()->for($owner, 'owner')->create();
    $seller = User::factory()->seller($shop)->create();
    Product::factory()->for($shop)->create();
    Sale::factory()->create(['shop_id' => $shop->id, 'seller_id' => $seller->id]);
    $customer = Customer::factory()->for($shop)->create();
    Debt::factory()->for($customer)->create(['shop_id' => $shop->id]);
    $otherSale = Sale::factory()->create();
    recordSession($seller, now()->getTimestamp());

    $this->actingAs(User::factory()->admin()->create())
        ->delete(route('admin.users.destroy', $owner))
        ->assertRedirect();

    expect(User::query()->whereKey([$owner->id, $seller->id])->exists())->toBeFalse()
        ->and(Shop::query()->whereKey($shop->id)->exists())->toBeFalse()
        ->and(Sale::query()->pluck('id')->all())->toBe([$otherSale->id])
        ->and(Customer::query()->where('shop_id', $shop->id)->exists())->toBeFalse()
        ->and(DB::table('sessions')->where('user_id', $seller->id)->exists())->toBeFalse();
});

it('deletes a seller together with their sales', function () {
    $seller = User::factory()->seller()->create();
    Sale::factory()->create(['shop_id' => $seller->shop_id, 'seller_id' => $seller->id]);

    $this->actingAs(User::factory()->admin()->create())
        ->delete(route('admin.users.destroy', $seller))
        ->assertRedirect();

    expect(User::query()->whereKey($seller->id)->exists())->toBeFalse()
        ->and(Sale::query()->count())->toBe(0)
        ->and(Shop::query()->whereKey($seller->shop_id)->exists())->toBeTrue();
});

it('never deletes an administrator', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)->delete(route('admin.users.destroy', $admin))->assertRedirect();

    expect(User::query()->whereKey($admin->id)->exists())->toBeTrue();
});

it('does not let owners edit or delete accounts', function () {
    $owner = User::factory()->owner()->create();
    $other = User::factory()->owner()->create();

    $this->actingAs($owner)->put(route('admin.users.update', $other), ['first_name' => 'X'])->assertForbidden();
    $this->actingAs($owner)->delete(route('admin.users.destroy', $other))->assertForbidden();
});
