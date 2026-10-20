<?php

namespace App\Http\Resources;

use App\Enums\PlanFeature;
use App\Models\Plan;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Plan
 */
class PlanResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'slug' => $this->slug,
            'name' => $this->name,
            'price' => $this->price,
            'max_sellers' => $this->max_sellers,
            'max_products' => $this->max_products,
            'features' => $this->features->map(fn (PlanFeature $feature): string => $feature->value)->values(),
            'feature_labels' => $this->features->map(fn (PlanFeature $feature): string => $feature->label())->values(),
            'is_active' => $this->is_active,
            'sort_order' => $this->sort_order,
            'shops_count' => $this->whenCounted('shops'),
        ];
    }
}
