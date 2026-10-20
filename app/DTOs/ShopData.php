<?php

namespace App\DTOs;

use App\Http\Requests\Owner\ShopRequest;

final readonly class ShopData
{
    public function __construct(
        public string $name,
        public int $shopTypeId,
        public ?string $address,
        public ?string $phone,
        public int $cashbackPercent,
        public bool $isActive,
    ) {}

    public static function fromRequest(ShopRequest $request): self
    {
        return new self(
            name: $request->string('name')->trim()->toString(),
            shopTypeId: $request->integer('shop_type_id'),
            address: $request->filled('address') ? $request->string('address')->trim()->toString() : null,
            phone: $request->filled('phone') ? $request->string('phone')->toString() : null,
            cashbackPercent: $request->integer('cashback_percent'),
            isActive: $request->boolean('is_active'),
        );
    }

    /**
     * @return array{name: string, shop_type_id: int, address: string|null, phone: string|null, cashback_percent: int, is_active: bool}
     */
    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'shop_type_id' => $this->shopTypeId,
            'address' => $this->address,
            'phone' => $this->phone,
            'cashback_percent' => $this->cashbackPercent,
            'is_active' => $this->isActive,
        ];
    }
}
