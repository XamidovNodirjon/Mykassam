<?php

namespace App\DTOs;

use App\Http\Requests\CustomerRequest;

final readonly class CustomerData
{
    public function __construct(
        public string $name,
        public ?string $phone,
    ) {}

    public static function fromRequest(CustomerRequest $request): self
    {
        return new self(
            name: $request->string('name')->trim()->toString(),
            phone: $request->filled('phone') ? $request->string('phone')->toString() : null,
        );
    }

    /**
     * @return array{name: string, phone: string|null}
     */
    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'phone' => $this->phone,
        ];
    }
}
