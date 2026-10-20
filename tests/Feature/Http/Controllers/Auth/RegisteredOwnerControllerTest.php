<?php

use App\Enums\UserRole;
use App\Models\User;

beforeEach(fn () => fakeUsms());

it('registers an owner whose login is the phone number confirmed by SMS', function () {
    $this->postJson(route('register.code'), ['phone' => '90 123 45 67'])->assertOk();

    $response = $this->post(route('register.store'), [
        'phone' => '90 123 45 67',
        'code' => lastSmsCode(),
        'first_name' => 'Jasur',
        'password' => 'secret-pass',
    ]);

    $response->assertRedirect(route('owner.shops.create'));

    $owner = User::query()->sole();
    expect($owner->role)->toBe(UserRole::Owner)
        ->and($owner->first_name)->toBe('Jasur')
        ->and($owner->last_name)->toBeNull()
        ->and($owner->phone)->toBe('998901234567')
        ->and($owner->login)->toBe('998901234567');
    $this->assertAuthenticatedAs($owner);
});

it('rejects a wrong SMS code and counts the attempt', function () {
    $this->postJson(route('register.code'), ['phone' => '901234567'])->assertOk();
    $wrongCode = lastSmsCode() === '000000' ? '111111' : '000000';

    $response = $this->post(route('register.store'), [
        'phone' => '901234567',
        'code' => $wrongCode,
        'first_name' => 'Jasur',
        'password' => 'secret-pass',
    ]);

    $response->assertSessionHasErrors(['code' => "Kod noto'g'ri. Qolgan urinishlar: 4."]);
    expect(User::query()->count())->toBe(0);
    $this->assertGuest();
});

it('rejects registration without a requested code', function () {
    $response = $this->post(route('register.store'), [
        'phone' => '901234567',
        'code' => '123456',
        'first_name' => 'Jasur',
        'password' => 'secret-pass',
    ]);

    $response->assertSessionHasErrors(['code' => "Kodning muddati tugagan. Yangi kod so'rang."]);
    expect(User::query()->count())->toBe(0);
});

it('rejects a phone number that is already registered', function () {
    User::factory()->owner()->create(['phone' => '998901234567', 'login' => '998901234567']);

    $response = $this->post(route('register.store'), [
        'phone' => '+998901234567',
        'code' => '123456',
        'first_name' => 'Ali',
        'password' => 'secret-pass',
    ]);

    $response->assertSessionHasErrors(['phone' => "Bu telefon raqam allaqachon ro'yxatdan o'tgan."]);
});
