<?php

namespace App\DTOs;

use App\Http\Requests\Admin\ShopTypeRequest;

final readonly class ShopTypeData
{
    public function __construct(
        public string $name,
        public bool $isActive,
    ) {}

    public static function fromRequest(ShopTypeRequest $request): self
    {
        return new self(
            name: $request->string('name')->trim()->toString(),
            isActive: $request->boolean('is_active'),
        );
    }

    /**
     * @return array{name: string, is_active: bool}
     */
    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'is_active' => $this->isActive,
        ];
    }
}
