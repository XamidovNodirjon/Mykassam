<?php

namespace App\Http\Resources;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Date;

/**
 * @mixin User
 */
class UserResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'full_name' => $this->full_name,
            'phone' => $this->whenHas('phone'),
            'login' => $this->whenHas('login'),
            'role' => $this->whenHas('role', fn () => $this->role->value),
            'role_label' => $this->whenHas('role', fn () => $this->role->label()),
            'is_active' => $this->whenHas('is_active'),
            'shop_id' => $this->whenHas('shop_id'),
            'shop' => ShopResource::make($this->whenLoaded('shop')),
            'shops_count' => $this->whenCounted('shops'),
            'sales_count' => $this->whenHas('sales_count', fn () => (int) $this->sales_count),
            'revenue' => $this->whenHas('revenue', fn () => (int) $this->revenue),
            'profit' => $this->whenHas('profit', fn () => (int) $this->profit),
            'created_at' => $this->whenHas('created_at', fn () => $this->created_at?->toDateString()),
            'last_seen_at' => $this->whenHas('last_seen_at', fn () => $this->last_seen_at === null
                ? null
                : Date::createFromTimestamp((int) $this->last_seen_at, config('app.timezone'))->toIso8601String()),
        ];
    }
}
