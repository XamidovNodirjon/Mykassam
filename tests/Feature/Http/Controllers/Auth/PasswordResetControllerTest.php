<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;

beforeEach(fn () => fakeUsms());

it('sets a new password once the SMS code is confirmed', function () {
    $owner = User::factory()->owner()->create(['phone' => '998901234567', 'login' => '998901234567']);
    $this->postJson(route('password.code'), ['phone' => '901234567'])->assertOk();

    $response = $this->post(route('password.update'), [
        'phone' => '901234567',
        'code' => lastSmsCode(),
        'password' => 'new-secret-pass',
    ]);

    $response->assertRedirect()->assertSessionHasNoErrors();
    expect(Hash::check('new-secret-pass', $owner->fresh()->password))->toBeTrue();

    $this->post(route('login.store'), ['login' => '901234567', 'password' => 'new-secret-pass'])
        ->assertRedirect(route('owner.dashboard'));
});

it('keeps the old password when the SMS code is wrong', function () {
    $owner = User::factory()->owner()->create(['phone' => '998901234567', 'login' => '998901234567']);
    $this->postJson(route('password.code'), ['phone' => '901234567'])->assertOk();
    $wrongCode = lastSmsCode() === '000000' ? '111111' : '000000';

    $response = $this->post(route('password.update'), [
        'phone' => '901234567',
        'code' => $wrongCode,
        'password' => 'new-secret-pass',
    ]);

    $response->assertSessionHasErrors('code');
    expect(Hash::check('password', $owner->fresh()->password))->toBeTrue();
});
