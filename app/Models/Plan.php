<?php

namespace App\Models;

use App\Enums\PlanFeature;
use Database\Factories\PlanFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Casts\AsEnumCollection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * Subscription plan managed by the administrator. A null limit means "unlimited".
 *
 * @property int $id
 * @property string $slug
 * @property string $name
 * @property int $price Monthly price per shop, in UZS.
 * @property int|null $max_sellers
 * @property int|null $max_products
 * @property Collection<int, PlanFeature> $features
 * @property bool $is_active
 * @property int $sort_order
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read int|null $shops_count Aggregate selected with withCount().
 */
#[Fillable(['slug', 'name', 'price', 'max_sellers', 'max_products', 'features', 'is_active', 'sort_order'])]
class Plan extends Model
{
    /** @use HasFactory<PlanFactory> */
    use HasFactory;

    /**
     * @return array<string, mixed>
     */
    protected function casts(): array
    {
        return [
            'price' => 'integer',
            'max_sellers' => 'integer',
            'max_products' => 'integer',
            'features' => AsEnumCollection::of(PlanFeature::class),
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function hasFeature(PlanFeature $feature): bool
    {
        return $this->features->contains($feature);
    }

    /**
     * @return HasMany<Shop, $this>
     */
    public function shops(): HasMany
    {
        return $this->hasMany(Shop::class);
    }
}
