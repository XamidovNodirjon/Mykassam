<?php

namespace App\Http\Resources;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Full product view for the shop owner, including the cost price.
 *
 * @mixin Product
 */
class ProductResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'shop_id' => $this->shop_id,
            'name' => $this->name,
            'barcode' => $this->barcode,
            'cost_price' => $this->cost_price,
            'min_price' => $this->min_price,
            'price' => $this->price,
            'stock' => $this->stock,
            'min_stock' => $this->min_stock,
            'is_active' => $this->whenHas('is_active'),
            'shop' => ShopResource::make($this->whenLoaded('shop')),
        ];
    }
}
