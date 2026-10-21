<?php

use App\Models\Plan;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Factory;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "pest()" function to bind different classes or traits.
|
*/

pest()->extend(TestCase::class)
    ->use(RefreshDatabase::class)
    ->in('Feature');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
|
| When you're writing tests, you often need to check that values meet certain conditions. The
| "expect()" function gives you access to a set of "expectations" methods that you can use
| to assert different things. Of course, you may extend the Expectation API at any time.
|
*/

expect()->extend('toBeOne', function () {
    return $this->toBe(1);
});

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
|
| While Pest is very powerful out-of-the-box, you may have some testing code specific to your
| project that you don't want to repeat in every file. Here you can also expose helpers as
| global functions to help you to reduce the number of lines of code in your test files.
|
*/

/**
 * One of the standard plans created by the plans migration: "start", "business" or "business-plus".
 */
function plan(string $slug): Plan
{
    return Plan::query()->where('slug', $slug)->firstOrFail();
}

/**
 * Send SMS through a faked USMS API. Each call starts a fresh fake, so a test can replace the
 * successful defaults that a beforeEach() already registered.
 *
 * @param  array<string, mixed>  $stubs  Responses that replace the successful defaults, keyed by URL.
 */
function fakeUsms(array $stubs = []): void
{
    config([
        'sms.driver' => 'usms',
        'sms.usms.login' => 'test-login',
        'sms.usms.secret' => 'test-secret',
        'sms.usms.templates' => ['registration' => 43, 'password_reset' => 43, 'debt_reminder' => 44],
    ]);

    Http::swap(new Factory(app(Dispatcher::class)));
    Http::preventStrayRequests();
    // Stubs come first so a specific URL wins over the wildcard defaults below.
    Http::fake($stubs + [
        'usms.uz/api/v1/send' => Http::response(['id' => '1001', 'parts' => 1, 'price' => 160, 'status' => 'sent']),
        'usms.uz/api/v1/balance' => Http::response(['balance' => 100000]),
        'usms.uz/api/v1/status/*' => Http::response(['id' => '1001', 'status' => 'delivered', 'status_note' => 'DELIVRD']),
    ]);
}

/**
 * The verification code of the most recent SMS sent through the faked USMS API.
 */
function lastSmsCode(): string
{
    [$request] = Http::recorded(fn (Request $request): bool => str_ends_with($request->url(), '/send'))->last();

    return $request['params']['code'];
}
