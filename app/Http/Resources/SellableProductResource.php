<?php

namespace App\Http\Resources;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Product as seen by a seller at the point of sale. The cost price is never exposed.
 *
 * @mixin Product
 */
class SellableProductResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'barcode' => $this->barcode,
            'min_price' => $this->min_price,
            'price' => $this->price,
            'stock' => $this->stock,
        ];
    }
}
