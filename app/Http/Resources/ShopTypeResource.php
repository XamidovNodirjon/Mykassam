<?php

namespace App\Http\Resources;

use App\Models\ShopType;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin ShopType
 */
class ShopTypeResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'is_active' => $this->whenHas('is_active'),
            'shops_count' => $this->whenCounted('shops'),
        ];
    }
}
