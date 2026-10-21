<?php

use App\Models\Shop;
use App\Models\User;
use Illuminate\Support\Facades\DB;
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
