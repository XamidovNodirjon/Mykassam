<?php

use App\Models\Shop;
use App\Models\User;
use Database\Seeders\AdminSeeder;
use Inertia\Testing\AssertableInertia;

it('signs the seeded administrator in with admin / admin123', function () {
    $this->seed(AdminSeeder::class);

    $response = $this->post(route('login.store'), [
        'login' => 'admin',
        'password' => 'admin123',
    ]);

    $response->assertRedirect(route('admin.dashboard'));
    $this->assertAuthenticated();
});

it('signs an owner in with a formatted phone number', function () {
    $owner = User::factory()->owner()->create([
        'phone' => '998901234567',
        'login' => '998901234567',
    ]);

    $response = $this->post(route('login.store'), [
        'login' => '+998 (90) 123-45-67',
        'password' => 'password',
    ]);

    $response->assertRedirect(route('owner.dashboard'));
    $this->assertAuthenticatedAs($owner);
});

it('ignores a remembered page that belongs to another role', function () {
    $owner = User::factory()->owner()->create();
    $this->get(route('admin.dashboard'))->assertRedirect(route('login'));

    $response = $this->post(route('login.store'), [
        'login' => $owner->login,
        'password' => 'password',
    ]);

    $response->assertRedirect(route('owner.dashboard'));
});

it('returns to a remembered page of the same role', function () {
    $owner = User::factory()->owner()->create();
    $this->get(route('owner.sales.index'))->assertRedirect(route('login'));

    $response = $this->post(route('login.store'), [
        'login' => $owner->login,
        'password' => 'password',
    ]);

    $response->assertRedirect(route('owner.sales.index'));
});

it('renders the friendly error page when a role opens another role\'s page', function () {
    $owner = User::factory()->owner()->create();

    $response = $this->actingAs($owner)->get(route('admin.dashboard'));

    $response->assertForbidden()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('Error')
            ->where('status', 403));
});

it('rejects a wrong password', function () {
    $owner = User::factory()->owner()->create();

    $response = $this->post(route('login.store'), [
        'login' => $owner->login,
        'password' => 'wrong-password',
    ]);

    $response->assertSessionHasErrors(['login' => "Login yoki parol noto'g'ri."]);
    $this->assertGuest();
});

it('rejects a seller whose shop is deactivated', function () {
    $seller = User::factory()->seller(Shop::factory()->inactive()->create())->create();

    $response = $this->post(route('login.store'), [
        'login' => $seller->login,
        'password' => 'password',
    ]);

    $response->assertSessionHasErrors('login');
    $this->assertGuest();
});

it('signs out a seller on the next request after their owner is blocked', function () {
    $seller = User::factory()->seller()->create();
    $seller->shop->owner->update(['is_active' => false]);

    $response = $this->actingAs($seller)->get(route('seller.dashboard'));

    $response->assertRedirect(route('login'));
    $this->assertGuest();
});
