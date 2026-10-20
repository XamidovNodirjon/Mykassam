<?php

namespace App\DTOs;

use App\Enums\PlanFeature;
use App\Http\Requests\Admin\PlanRequest;

final readonly class PlanData
{
    /**
     * @param  list<PlanFeature>  $features
     */
    public function __construct(
        public string $name,
        public int $price,
        public ?int $maxSellers,
        public ?int $maxProducts,
        public array $features,
        public bool $isActive,
        public int $sortOrder,
    ) {}

    public static function fromRequest(PlanRequest $request): self
    {
        /** @var list<string> $features */
        $features = $request->validated('features', []);

        return new self(
            name: $request->string('name')->trim()->toString(),
            price: $request->integer('price'),
            maxSellers: $request->filled('max_sellers') ? $request->integer('max_sellers') : null,
            maxProducts: $request->filled('max_products') ? $request->integer('max_products') : null,
            features: array_map(fn (string $feature): PlanFeature => PlanFeature::from($feature), $features),
            isActive: $request->boolean('is_active'),
            sortOrder: $request->integer('sort_order'),
        );
    }

    /**
     * @return array{name: string, price: int, max_sellers: int|null, max_products: int|null, features: list<PlanFeature>, is_active: bool, sort_order: int}
     */
    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'price' => $this->price,
            'max_sellers' => $this->maxSellers,
            'max_products' => $this->maxProducts,
            'features' => $this->features,
            'is_active' => $this->isActive,
            'sort_order' => $this->sortOrder,
        ];
    }
}
