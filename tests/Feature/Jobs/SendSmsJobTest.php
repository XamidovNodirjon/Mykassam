<?php

use App\Enums\SmsStatus;
use App\Exceptions\SmsGatewayException;
use App\Jobs\SendSmsJob;
use App\Models\SmsMessage;
use Illuminate\Support\Facades\Http;

it('marks the message as failed with the USMS error when sending is rejected', function () {
    fakeUsms(['usms.uz/api/v1/send' => Http::response(['error' => 'Недостаточно средств'], 402)]);
    $message = SmsMessage::factory()->create(['status' => SmsStatus::Pending, 'sent_at' => null]);

    expect(fn () => SendSmsJob::dispatch($message, ['code' => '123456']))
        ->toThrow(SmsGatewayException::class);

    $message->refresh();
    expect($message->status)->toBe(SmsStatus::Failed)
        ->and($message->error)->toBe('USMS xatosi (HTTP 402): Недостаточно средств')
        ->and($message->sent_at)->toBeNull();
});
