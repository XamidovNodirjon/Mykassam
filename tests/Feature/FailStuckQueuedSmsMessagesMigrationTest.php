<?php

use App\Enums\SmsStatus;
use App\Models\SmsMessage;
use Illuminate\Support\Facades\DB;

function runFailStuckQueuedSmsMessagesMigration(): void
{
    (require database_path('migrations/2026_10_21_173308_fail_stuck_queued_sms_messages.php'))->up();
}

it('marks SMS stuck in the queue as failed and drops their queued jobs', function () {
    $stuck = SmsMessage::factory()->create(['status' => SmsStatus::Pending, 'sent_at' => null]);
    $sent = SmsMessage::factory()->create();
    DB::table('jobs')->insert([
        ['queue' => 'default', 'payload' => '{"displayName":"App\\\\Jobs\\\\SendSmsJob"}', 'attempts' => 0, 'available_at' => 0, 'created_at' => 0],
        ['queue' => 'default', 'payload' => '{"displayName":"App\\\\Jobs\\\\OtherJob"}', 'attempts' => 0, 'available_at' => 0, 'created_at' => 0],
    ]);

    runFailStuckQueuedSmsMessagesMigration();

    expect($stuck->refresh()->status)->toBe(SmsStatus::Failed)
        ->and($stuck->error)->toBe('Navbat ishlamagani sababli yuborilmagan.')
        ->and($sent->refresh()->status)->toBe(SmsStatus::Sent)
        ->and(DB::table('jobs')->pluck('payload')->all())->toBe(['{"displayName":"App\\\\Jobs\\\\OtherJob"}']);
});
