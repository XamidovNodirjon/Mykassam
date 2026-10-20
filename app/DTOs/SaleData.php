<?php

namespace App\DTOs;

use App\Enums\PaymentMethod;
use App\Http\Requests\Seller\SaleRequest;
use Carbon\CarbonImmutable;

final readonly class SaleData
{
    /**
     * @param  list<SaleItemData>  $items
     * @param  int  $bonusToUse  Customer bonus spent on this sale, in so'm.
     * @param  CarbonImmutable|null  $dueDate  Repayment date of a credit (nasiya) sale.
     */
    public function __construct(
        public PaymentMethod $paymentMethod,
        public array $items,
        public ?int $customerId = null,
        public int $bonusToUse = 0,
        public ?CarbonImmutable $dueDate = null,
    ) {}

    public static function fromRequest(SaleRequest $request): self
    {
        /** @var array<int, array{product_id: int|string, quantity: int|string, unit_price: int|string}> $items */
        $items = $request->validated('items');

        return new self(
            paymentMethod: $request->enum('payment_method', PaymentMethod::class) ?? PaymentMethod::Cash,
            items: array_values(array_map(fn (array $item): SaleItemData => new SaleItemData(
                productId: (int) $item['product_id'],
                quantity: (int) $item['quantity'],
                unitPrice: (int) $item['unit_price'],
            ), $items)),
            customerId: $request->filled('customer_id') ? $request->integer('customer_id') : null,
            bonusToUse: $request->integer('bonus_used'),
            dueDate: $request->filled('due_date') ? CarbonImmutable::parse($request->string('due_date')->toString()) : null,
        );
    }

    /**
     * @return list<int>
     */
    public function productIds(): array
    {
        return array_map(fn (SaleItemData $item): int => $item->productId, $this->items);
    }
}
