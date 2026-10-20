<?php

namespace App\Services\Sms;

use App\Enums\SmsPurpose;
use App\Services\Contracts\SmsGatewayInterface;
use Illuminate\Support\Facades\Log;

/**
 * Development driver: writes messages to the application log instead of sending them.
 */
class LogSmsGateway implements SmsGatewayInterface
{
    public function send(string $phone, SmsPurpose $purpose, array $params, string $idempotencyKey): ?string
    {
        Log::info("[SMS] {$purpose->label()} {$phone}", $params);

        return null;
    }

    public function balance(): ?int
    {
        return null;
    }
}
