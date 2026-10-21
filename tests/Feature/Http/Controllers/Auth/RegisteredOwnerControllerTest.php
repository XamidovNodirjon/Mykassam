<?php

use App\Enums\UserRole;
use App\Models\User;

beforeEach(fn () => fakeUsms());

it('registers an owner after the SMS code is confirmed', function () {
    $this->postJson(route('register.code'), ['phone' => '90 123 45 67'])->assertOk();
    $this->postJson(route('register.verify'), ['phone' => '90 123 45 67', 'code' => lastSmsCode()])->assertOk();

    $response = $this->post(route('register.store'), [
        'phone' => '90 123 45 67',
        'first_name' => 'Jasur',
        'password' => 'qwe123',
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

it('accepts any password of at least 6 characters', function () {
    $this->postJson(route('register.code'), ['phone' => '901234567'])->assertOk();
    $this->postJson(route('register.verify'), ['phone' => '901234567', 'code' => lastSmsCode()])->assertOk();

    $this->post(route('register.store'), ['phone' => '901234567', 'first_name' => 'Jasur', 'password' => '12345'])
        ->assertSessionHasErrors('password');
    $this->post(route('register.store'), ['phone' => '901234567', 'first_name' => 'Jasur', 'password' => '123456'])
        ->assertRedirect(route('owner.shops.create'));
});

it('rejects a wrong SMS code and counts the attempt', function () {
    $this->postJson(route('register.code'), ['phone' => '901234567'])->assertOk();
    $wrongCode = lastSmsCode() === '000000' ? '111111' : '000000';

    $response = $this->postJson(route('register.verify'), ['phone' => '901234567', 'code' => $wrongCode]);

    $response->assertJsonValidationErrors(['code' => "Kod noto'g'ri. Qolgan urinishlar: 4."]);
});

it('does not create an account for a phone that was not confirmed', function () {
    $this->postJson(route('register.code'), ['phone' => '901234567'])->assertOk();

    $response = $this->post(route('register.store'), [
        'phone' => '901234567',
        'first_name' => 'Jasur',
        'password' => 'secret-pass',
    ]);

    $response->assertSessionHasErrors(['phone' => "Telefon raqam tasdiqlanmagan yoki vaqt tugagan. Kodni qaytadan so'rang."]);
    expect(User::query()->count())->toBe(0);
    $this->assertGuest();
});

it('does not let a phone confirmed for one number register another', function () {
    $this->postJson(route('register.code'), ['phone' => '901234567'])->assertOk();
    $this->postJson(route('register.verify'), ['phone' => '901234567', 'code' => lastSmsCode()])->assertOk();

    $response = $this->post(route('register.store'), [
        'phone' => '907654321',
        'first_name' => 'Jasur',
        'password' => 'secret-pass',
    ]);

    $response->assertSessionHasErrors('phone');
    expect(User::query()->count())->toBe(0);
});

it('asks for a new code once the confirmation has expired', function () {
    $this->postJson(route('register.code'), ['phone' => '901234567'])->assertOk();
    $this->postJson(route('register.verify'), ['phone' => '901234567', 'code' => lastSmsCode()])->assertOk();
    $this->travel(16)->minutes();

    $response = $this->post(route('register.store'), [
        'phone' => '901234567',
        'first_name' => 'Jasur',
        'password' => 'secret-pass',
    ]);

    $response->assertSessionHasErrors('phone');
    expect(User::query()->count())->toBe(0);
});

it('rejects a phone number that is already registered', function () {
    User::factory()->owner()->create(['phone' => '998901234567', 'login' => '998901234567']);

    $response = $this->post(route('register.store'), [
        'phone' => '+998901234567',
        'first_name' => 'Ali',
        'password' => 'secret-pass',
    ]);

    $response->assertSessionHasErrors(['phone' => "Bu telefon raqam allaqachon ro'yxatdan o'tgan."]);
});
