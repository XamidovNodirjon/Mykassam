<?php

namespace App\DTOs;

final readonly class SaleItemData
{
    public function __construct(
        public int $productId,
        public int $quantity,
        public int $unitPrice,
    ) {}
}
