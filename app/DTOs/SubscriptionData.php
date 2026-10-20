<?php

namespace App\DTOs;

use App\Http\Requests\Admin\SubscriptionRequest;

final readonly class SubscriptionData
{
    public function __construct(
        public int $planId,
        public int $months,
    ) {}

    public static function fromRequest(SubscriptionRequest $request): self
    {
        return new self(
            planId: $request->integer('plan_id'),
            months: $request->integer('months'),
        );
    }
}
