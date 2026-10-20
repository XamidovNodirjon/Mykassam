<?php

use App\Enums\SmsPurpose;
use App\Enums\SmsStatus;
use App\Models\SmsMessage;
use App\Models\User;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;

beforeEach(fn () => fakeUsms());

it('texts a registration code through USMS and logs the delivery', function () {
    $response = $this->postJson(route('register.code'), ['phone' => '+998 (90) 123-45-67']);

    $response->assertOk()->assertJsonPath('data.resend_after', 60);

    Http::assertSent(fn (Request $request): bool => $request->url() === 'https://usms.uz/api/v1/send'
        && $request->hasHeader('Authorization', 'Bearer test-login:test-secret')
        && $request['phone'] === '998901234567'
        && $request['template_id'] === 43
        && preg_match('/^\d{6}$/', $request['params']['code']) === 1
        && filled($request['idem_key']));

    $message = SmsMessage::query()->sole();
    expect($message->phone)->toBe('998901234567')
        ->and($message->purpose)->toBe(SmsPurpose::Registration)
        ->and($message->status)->toBe(SmsStatus::Sent)
        ->and($message->provider_message_id)->toBe('1001');
});

it('does not text a registration code to a registered phone', function () {
    User::factory()->owner()->create(['phone' => '998901234567', 'login' => '998901234567']);

    $response = $this->postJson(route('register.code'), ['phone' => '901234567']);

    $response->assertJsonValidationErrors(['phone' => "Bu telefon raqam allaqachon ro'yxatdan o'tgan."]);
    Http::assertNothingSent();
});

it('asks to wait before another code is sent to the same phone', function () {
    $this->postJson(route('register.code'), ['phone' => '901234567'])->assertOk();

    $response = $this->postJson(route('register.code'), ['phone' => '901234567']);

    $response->assertJsonValidationErrors('phone');
    Http::assertSentCount(1);
});

it('does not text a password reset code to an unknown phone', function () {
    $response = $this->postJson(route('password.code'), ['phone' => '901234567']);

    $response->assertJsonValidationErrors(['phone' => "Bu raqam bilan ro'yxatdan o'tgan hisob topilmadi."]);
    Http::assertNothingSent();
});
