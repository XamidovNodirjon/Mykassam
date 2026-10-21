<?php

namespace App\Jobs;

use App\Models\SmsMessage;
use App\Services\Contracts\SmsGatewayInterface;
use Illuminate\Contracts\Queue\ShouldBeEncrypted;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Str;
use Throwable;

/**
 * Delivers a logged SMS. SmsService runs it synchronously; if it is ever queued, the payload is
 * encrypted because the variables may carry a verification code.
 */
class SendSmsJob implements ShouldBeEncrypted, ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    /**
     * @var list<int>
     */
    public array $backoff = [5, 15];

    /**
     * Kept across retries so the provider never sends the same message twice.
     */
    public string $idempotencyKey;

    /**
     * @param  array<string, string>  $params  Template variables.
     */
    public function __construct(public SmsMessage $message, public array $params)
    {
        $this->idempotencyKey = (string) Str::uuid();
    }

    public function handle(SmsGatewayInterface $gateway): void
    {
        $providerMessageId = $gateway->send($this->message->phone, $this->message->purpose, $this->params, $this->idempotencyKey);

        $this->message->markAsSent($providerMessageId);
    }

    public function failed(?Throwable $exception): void
    {
        $this->message->markAsFailed($exception?->getMessage() ?? "Noma'lum xatolik");
    }
}
