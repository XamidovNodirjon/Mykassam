<?php

namespace App\Http\Resources;

use App\Models\Shop;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Shop
 */
class ShopResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'address' => $this->whenHas('address'),
            'phone' => $this->whenHas('phone'),
            'is_active' => $this->whenHas('is_active'),
            'shop_type_id' => $this->whenHas('shop_type_id'),
            'type' => ShopTypeResource::make($this->whenLoaded('type')),
            'owner' => UserResource::make($this->whenLoaded('owner')),
            'products_count' => $this->whenCounted('products'),
            'sellers_count' => $this->whenCounted('sellers'),
            'sales_count' => $this->whenHas('sales_count', fn () => (int) $this->sales_count),
            'revenue' => $this->whenHas('revenue', fn () => (int) $this->revenue),
            'profit' => $this->whenHas('profit', fn () => (int) $this->profit),
            'cashback_percent' => $this->whenHas('cashback_percent'),
            'plan' => PlanResource::make($this->whenLoaded('plan')),
            'features' => $this->whenLoaded('plan', fn () => $this->featureValues()),
            'subscription_status' => $this->whenHas('subscription_ends_at', fn () => $this->subscriptionStatus()->value),
            'subscription_status_label' => $this->whenHas('subscription_ends_at', fn () => $this->subscriptionStatus()->label()),
            'subscription_ends_at' => $this->whenHas('subscription_ends_at', fn () => $this->subscription_ends_at?->toDateString()),
        ];
    }
}
