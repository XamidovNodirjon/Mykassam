<?php

namespace App\Models;

use App\Enums\PlanFeature;
use App\Enums\SubscriptionStatus;
use App\Enums\UserRole;
use Database\Factories\ShopFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $owner_id
 * @property int $shop_type_id
 * @property string $name
 * @property string|null $address
 * @property string|null $phone
 * @property int $cashback_percent
 * @property bool $is_active
 * @property int|null $plan_id
 * @property bool $on_trial
 * @property Carbon|null $subscription_ends_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read User $owner
 * @property-read ShopType $type
 * @property-read Plan|null $plan
 * @property-read int|string|null $revenue Aggregate selected with withSum().
 * @property-read int|string|null $profit Aggregate selected with withSum().
 * @property-read int|string|null $sales_count Aggregate selected with withCount().
 */
#[Fillable(['owner_id', 'shop_type_id', 'name', 'address', 'phone', 'cashback_percent', 'is_active', 'plan_id', 'on_trial', 'subscription_ends_at'])]
class Shop extends Model
{
    /** @use HasFactory<ShopFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'cashback_percent' => 'integer',
            'is_active' => 'boolean',
            'on_trial' => 'boolean',
            'subscription_ends_at' => 'datetime',
        ];
    }

    public function subscriptionStatus(): SubscriptionStatus
    {
        if ($this->subscription_ends_at === null || $this->subscription_ends_at->isPast()) {
            return SubscriptionStatus::Expired;
        }

        return $this->on_trial ? SubscriptionStatus::Trial : SubscriptionStatus::Active;
    }

    public function hasActiveSubscription(): bool
    {
        return $this->subscriptionStatus() !== SubscriptionStatus::Expired;
    }

    /**
     * Whether the current plan unlocks the module. Data of a locked module stays readable.
     */
    public function hasFeature(PlanFeature $feature): bool
    {
        return $this->plan?->hasFeature($feature) ?? false;
    }

    /**
     * @return list<string>
     */
    public function featureValues(): array
    {
        return array_values($this->plan?->features->map(fn (PlanFeature $feature): string => $feature->value)->all() ?? []);
    }

    /**
     * @param  Builder<self>  $query
     */
    #[Scope]
    protected function withSubscriptionStatus(Builder $query, SubscriptionStatus $status): void
    {
        if ($status === SubscriptionStatus::Expired) {
            $query->where(fn (Builder $query) => $query
                ->whereNull('subscription_ends_at')
                ->orWhere('subscription_ends_at', '<=', now()));

            return;
        }

        $query->where('subscription_ends_at', '>', now())
            ->where('on_trial', $status === SubscriptionStatus::Trial);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    /**
     * @return BelongsTo<Plan, $this>
     */
    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    /**
     * @return BelongsTo<ShopType, $this>
     */
    public function type(): BelongsTo
    {
        return $this->belongsTo(ShopType::class, 'shop_type_id');
    }

    /**
     * @return HasMany<User, $this>
     */
    public function sellers(): HasMany
    {
        return $this->hasMany(User::class)->where('role', UserRole::Seller);
    }

    /**
     * @return HasMany<Product, $this>
     */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    /**
     * @return HasMany<Sale, $this>
     */
    public function sales(): HasMany
    {
        return $this->hasMany(Sale::class);
    }

    /**
     * @return HasMany<Customer, $this>
     */
    public function customers(): HasMany
    {
        return $this->hasMany(Customer::class);
    }

    /**
     * @return HasMany<Debt, $this>
     */
    public function debts(): HasMany
    {
        return $this->hasMany(Debt::class);
    }

    /**
     * @return HasMany<StockReceipt, $this>
     */
    public function stockReceipts(): HasMany
    {
        return $this->hasMany(StockReceipt::class);
    }
}
