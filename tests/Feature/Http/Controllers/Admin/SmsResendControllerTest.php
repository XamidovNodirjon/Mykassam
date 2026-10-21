<?php

use App\Enums\SmsPurpose;
use App\Enums\SmsStatus;
use App\Jobs\SendSmsJob;
use App\Models\SmsMessage;
use App\Models\User;
use App\Services\SmsService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use Inertia\Testing\AssertableInertia;

$reminderParams = ['name' => 'Ali', 'shop' => 'Baraka', 'amount' => '150 000'];

it('queues a failed debt reminder again with its stored text', function () use ($reminderParams) {
    Queue::fake();
    $message = SmsMessage::factory()->failed()->create([
        'purpose' => SmsPurpose::DebtReminder,
        'params' => $reminderParams,
    ]);

    $response = $this->actingAs(User::factory()->admin()->create())
        ->post(route('admin.sms.resend', $message));

    $response->assertRedirect();
    expect($message->refresh()->status)->toBe(SmsStatus::Pending)
        ->and($message->error)->toBeNull();
    Queue::assertPushed(SendSmsJob::class, fn (SendSmsJob $job): bool => $job->message->is($message)
        && $job->params === $reminderParams);
});

it('texts a new verification code instead of the one that never arrived', function () {
    Queue::fake();
    $message = SmsMessage::factory()->failed()->create(['phone' => '998901234567']);

    $this->actingAs(User::factory()->admin()->create())
        ->post(route('admin.sms.resend', $message))
        ->assertRedirect();

    $newMessage = SmsMessage::query()->latest('id')->first();
    expect($newMessage->isNot($message))->toBeTrue()
        ->and($newMessage->phone)->toBe('998901234567')
        ->and($newMessage->purpose)->toBe(SmsPurpose::Registration)
        ->and($newMessage->params)->toBeNull();
    Queue::assertPushed(SendSmsJob::class, fn (SendSmsJob $job): bool => $job->message->is($newMessage)
        && strlen($job->params['code']) === 6);
});

it('does not resend a message the queue may still deliver', function () use ($reminderParams) {
    Queue::fake();
    $message = SmsMessage::factory()->create([
        'purpose' => SmsPurpose::DebtReminder,
        'params' => $reminderParams,
        'status' => SmsStatus::Pending,
        'sent_at' => null,
    ]);

    $this->actingAs(User::factory()->admin()->create())
        ->post(route('admin.sms.resend', $message))
        ->assertRedirect();

    Queue::assertNothingPushed();
});

it('offers resending only for undelivered messages', function () use ($reminderParams) {
    SmsMessage::factory()->create();
    SmsMessage::factory()->failed()->create();
    SmsMessage::factory()->create([
        'status' => SmsStatus::Pending,
        'sent_at' => null,
        'created_at' => now()->subMinutes(5),
        'updated_at' => now()->subMinutes(5),
    ]);
    SmsMessage::factory()->failed()->create(['purpose' => SmsPurpose::DebtReminder, 'params' => null]);
    SmsMessage::factory()->failed()->create(['purpose' => SmsPurpose::DebtReminder, 'params' => $reminderParams]);

    $response = $this->actingAs(User::factory()->admin()->create())->get(route('admin.sms.index'));

    $response->assertInertia(fn (AssertableInertia $page) => $page
        ->where('messages.data.0.can_resend', true)
        ->where('messages.data.1.can_resend', false)
        ->where('messages.data.2.can_resend', true)
        ->where('messages.data.3.can_resend', true)
        ->where('messages.data.4.can_resend', false));
});

it('keeps reminder text encrypted until delivery and never stores a verification code', function () use ($reminderParams) {
    Queue::fake();
    $sms = app(SmsService::class);

    $sms->send('998901234567', SmsPurpose::DebtReminder, $reminderParams);
    $sms->send('998901234567', SmsPurpose::Registration, ['code' => '123456']);

    [$reminder, $code] = SmsMessage::query()->orderBy('id')->get()->all();
    expect($reminder->params)->toBe($reminderParams)
        ->and(DB::table('sms_messages')->where('id', $reminder->id)->value('params'))->not->toContain('Baraka')
        ->and($code->params)->toBeNull();

    $reminder->markAsSent('1001');

    expect($reminder->refresh()->params)->toBeNull();
});

it('is not available to owners', function () {
    $message = SmsMessage::factory()->failed()->create();

    $this->actingAs(User::factory()->owner()->create())
        ->post(route('admin.sms.resend', $message))
        ->assertForbidden();
});
