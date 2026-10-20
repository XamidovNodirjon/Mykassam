<?php

namespace App\Services\Contracts;

use App\Enums\SmsPurpose;
use App\Exceptions\SmsGatewayException;

interface SmsGatewayInterface
{
    /**
     * Send the message for the purpose, filled with the template variables.
     * Retrying with the same idempotency key never sends a duplicate.
     *
     * @param  array<string, string>  $params
     * @return string|null Message id assigned by the provider.
     *
     * @throws SmsGatewayException
     */
    public function send(string $phone, SmsPurpose $purpose, array $params, string $idempotencyKey): ?string;

    /**
     * Account balance in UZS, or null when the driver has no balance.
     *
     * @throws SmsGatewayException
     */
    public function balance(): ?int;
}
