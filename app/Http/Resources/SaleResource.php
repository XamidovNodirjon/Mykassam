<?php

namespace App\Http\Resources;

use App\Models\Sale;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Cost and profit are only exposed to the shop owner.
 *
 * @mixin Sale
 */
class SaleResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $canSeeProfit = $request->user()?->isOwner() ?? false;

        return [
            'id' => $this->id,
            'total_amount' => $this->total_amount,
            'total_cost' => $this->when($canSeeProfit, $this->total_cost),
            'profit' => $this->when($canSeeProfit, $this->profit),
            'items_count' => $this->items_count,
            'payment_method' => $this->payment_method->value,
            'payment_method_label' => $this->payment_method->label(),
            'bonus_used' => $this->bonus_used,
            'bonus_earned' => $this->bonus_earned,
            'customer' => CustomerResource::make($this->whenLoaded('customer')),
            'created_at' => $this->created_at->toIso8601String(),
            'seller' => UserResource::make($this->whenLoaded('seller')),
            'shop' => ShopResource::make($this->whenLoaded('shop')),
            'items' => SaleItemResource::collection($this->whenLoaded('items')),
        ];
    }
}
