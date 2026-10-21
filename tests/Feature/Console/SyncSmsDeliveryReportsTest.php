<?php

use App\Enums\SmsStatus;
use App\Models\SmsMessage;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;

function sentSms(string $providerMessageId, array $attributes = []): SmsMessage
{
    return SmsMessage::factory()->create([
        'status' => SmsStatus::Sent,
        'provider_message_id' => $providerMessageId,
        'sent_at' => now()->subMinutes(5),
        ...$attributes,
    ]);
}

it('records which sent messages reached the phone and why the others did not', function () {
    fakeUsms([
        'usms.uz/api/v1/status/11' => Http::response(['id' => '11', 'status' => 'delivered', 'status_note' => 'DELIVRD']),
        'usms.uz/api/v1/status/12' => Http::response(['id' => '12', 'status' => 'failed', 'status_note' => 'UNDELIV']),
        'usms.uz/api/v1/status/13' => Http::response(['id' => '13', 'status' => 'rejected', 'status_note' => null]),
        'usms.uz/api/v1/status/14' => Http::response(['id' => '14', 'status' => 'sent', 'status_note' => null]),
    ]);
    $delivered = sentSms('11');
    $undelivered = sentSms('12');
    $rejected = sentSms('13');
    $waiting = sentSms('14');

    $this->artisan('sms:sync-delivery')->expectsOutputToContain("Holati yangilangan SMS'lar: 3 ta.")->assertSuccessful();

    expect($delivered->refresh()->status)->toBe(SmsStatus::Delivered)
        ->and($undelivered->refresh()->status)->toBe(SmsStatus::Failed)
        ->and($undelivered->error)->toBe('Operator yetkazmadi: UNDELIV')
        ->and($rejected->refresh()->error)->toBe('Operator yetkazmadi.')
        ->and($waiting->refresh()->status)->toBe(SmsStatus::Sent);
    Http::assertSent(fn (Request $request): bool => $request->url() === 'https://usms.uz/api/v1/status/11'
        && $request->hasHeader('Authorization', 'Bearer test-login:test-secret'));
});

it('only checks recent messages that still wait for a report', function () {
    fakeUsms();
    sentSms('21', ['sent_at' => now()->subDays(4)]);
    SmsMessage::factory()->create(['status' => SmsStatus::Delivered, 'provider_message_id' => '22']);
    SmsMessage::factory()->failed()->create();

    $this->artisan('sms:sync-delivery')->assertSuccessful();

    Http::assertNothingSent();
});

it('keeps checking the other messages when one lookup fails', function () {
    fakeUsms([
        'usms.uz/api/v1/status/31' => Http::response(['error' => 'Not found'], 404),
        'usms.uz/api/v1/status/32' => Http::response(['id' => '32', 'status' => 'delivered']),
    ]);
    $missing = sentSms('31');
    $delivered = sentSms('32');

    $this->artisan('sms:sync-delivery')->assertSuccessful();

    expect($missing->refresh()->status)->toBe(SmsStatus::Sent)
        ->and($delivered->refresh()->status)->toBe(SmsStatus::Delivered);
});
