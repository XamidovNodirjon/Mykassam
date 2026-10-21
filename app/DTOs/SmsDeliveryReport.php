<?php

namespace App\DTOs;

use App\Enums\SmsStatus;

/**
 * Where a sent SMS stands according to the provider, with its explanation (e.g. "UNDELIV").
 */
final readonly class SmsDeliveryReport
{
    public function __construct(
        public SmsStatus $status,
        public ?string $note = null,
    ) {}
}
