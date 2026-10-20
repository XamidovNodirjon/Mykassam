<?php

namespace App\DTOs;

use App\Http\Requests\SaleFilterRequest;
use Carbon\CarbonImmutable;

final readonly class SaleFilterData
{
    /**
     * @param  list<int>  $shopIds
     */
    public function __construct(
        public array $shopIds,
        public ?int $sellerId = null,
        public ?CarbonImmutable $from = null,
        public ?CarbonImmutable $to = null,
    ) {}

    /**
     * @param  list<int>  $allowedShopIds
     * @param  int|null  $forcedSellerId  Restricts results to one seller regardless of input (seller role).
     */
    public static function fromRequest(SaleFilterRequest $request, array $allowedShopIds, ?int $forcedSellerId = null): self
    {
        $shopId = $request->integer('shop_id');
        $sellerId = $request->integer('seller_id');

        return new self(
            shopIds: $shopId > 0 && in_array($shopId, $allowedShopIds, true) ? [$shopId] : $allowedShopIds,
            sellerId: $forcedSellerId ?? ($sellerId > 0 ? $sellerId : null),
            from: $request->date('from')?->toImmutable()->startOfDay(),
            to: $request->date('to')?->toImmutable()->endOfDay(),
        );
    }

    /**
     * Filter values echoed back to the page.
     *
     * @return array{shop_id: int|null, seller_id: int|null, from: string|null, to: string|null}
     */
    public function toArray(): array
    {
        return [
            'shop_id' => count($this->shopIds) === 1 ? $this->shopIds[0] : null,
            'seller_id' => $this->sellerId,
            'from' => $this->from?->toDateString(),
            'to' => $this->to?->toDateString(),
        ];
    }
}
