<?php

namespace App\DTOs;

use App\Http\Requests\Owner\ProductRequest;

final readonly class ProductData
{
    public function __construct(
        public string $name,
        public ?string $barcode,
        public int $costPrice,
        public int $minPrice,
        public int $price,
        public int $stock,
        public int $minStock,
        public bool $isActive,
    ) {}

    public static function fromRequest(ProductRequest $request): self
    {
        return new self(
            name: $request->string('name')->trim()->toString(),
            barcode: $request->filled('barcode') ? $request->string('barcode')->trim()->toString() : null,
            costPrice: $request->integer('cost_price'),
            minPrice: $request->integer('min_price'),
            price: $request->integer('price'),
            stock: $request->integer('stock'),
            minStock: $request->filled('min_stock') ? $request->integer('min_stock') : 5,
            isActive: $request->boolean('is_active'),
        );
    }

    /**
     * @return array{name: string, barcode: string|null, cost_price: int, min_price: int, price: int, stock: int, min_stock: int, is_active: bool}
     */
    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'barcode' => $this->barcode,
            'cost_price' => $this->costPrice,
            'min_price' => $this->minPrice,
            'price' => $this->price,
            'stock' => $this->stock,
            'min_stock' => $this->minStock,
            'is_active' => $this->isActive,
        ];
    }
}
