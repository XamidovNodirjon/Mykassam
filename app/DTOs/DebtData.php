<?php

namespace App\DTOs;

use App\Http\Requests\Owner\DebtRequest;
use Carbon\CarbonImmutable;

final readonly class DebtData
{
    public function __construct(
        public int $customerId,
        public int $amount,
        public CarbonImmutable $dueDate,
        public ?string $note,
    ) {}

    public static function fromRequest(DebtRequest $request): self
    {
        return new self(
            customerId: $request->integer('customer_id'),
            amount: $request->integer('amount'),
            dueDate: CarbonImmutable::parse($request->string('due_date')->toString()),
            note: $request->filled('note') ? $request->string('note')->trim()->toString() : null,
        );
    }
}
