<?php

use App\Models\SmsMessage;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Inertia\Testing\AssertableInertia;

it('shows sent SMS and loads the USMS balance after the page renders', function () {
    fakeUsms();
    SmsMessage::factory()->count(2)->create();
    SmsMessage::factory()->failed()->create();

    $response = $this->actingAs(User::factory()->admin()->create())->get(route('admin.sms.index'));

    $response->assertOk()->assertInertia(fn (AssertableInertia $page) => $page
        ->component('Admin/Sms/Index')
        ->has('messages.data', 3)
        ->where('summary', ['total' => 3, 'today' => 3, 'failed' => 1])
        ->where('isLive', true)
        ->missing('balance')
        ->loadDeferredProps(fn (AssertableInertia $reload) => $reload->where('balance', ['amount' => 100000, 'error' => null])));

    Http::assertSent(fn ($request): bool => $request->url() === 'https://usms.uz/api/v1/balance');
});

it('filters SMS by phone digits', function () {
    fakeUsms();
    SmsMessage::factory()->create(['phone' => '998901234567']);
    SmsMessage::factory()->create(['phone' => '998935554433']);

    $response = $this->actingAs(User::factory()->admin()->create())
        ->get(route('admin.sms.index', ['search' => '+998 93 555']));

    $response->assertInertia(fn (AssertableInertia $page) => $page
        ->has('messages.data', 1)
        ->where('messages.data.0.phone', '998935554433')
        ->where('filters.search', '99893555'));
});

it('shows an unknown balance instead of failing when USMS is unreachable', function () {
    fakeUsms(['usms.uz/api/v1/balance' => Http::failedConnection()]);

    $response = $this->actingAs(User::factory()->admin()->create())->get(route('admin.sms.index'));

    $response->assertInertia(fn (AssertableInertia $page) => $page
        ->loadDeferredProps(fn (AssertableInertia $reload) => $reload
            ->where('balance', ['amount' => null, 'error' => "USMS serveriga ulanib bo'lmadi."])));
});

it('tells the admin when USMS rejects the login and secret', function () {
    fakeUsms(['usms.uz/api/v1/balance' => Http::response(['error' => 'Authorization: Bearer login:secret'], 401)]);

    $response = $this->actingAs(User::factory()->admin()->create())->get(route('admin.sms.index'));

    $response->assertInertia(fn (AssertableInertia $page) => $page
        ->loadDeferredProps(fn (AssertableInertia $reload) => $reload
            ->where('balance.amount', null)
            ->where('balance.error', fn (string $error): bool => str_contains($error, 'HTTP 401'))));
});

it('is not available to owners', function () {
    $this->actingAs(User::factory()->owner()->create())
        ->get(route('admin.sms.index'))
        ->assertForbidden();
});
