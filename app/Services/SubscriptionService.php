<?php

namespace App\Services;

use App\DTOs\SubscriptionData;
use App\Enums\SubscriptionStatus;
use App\Models\Plan;
use App\Models\Shop;
use App\Models\User;
use App\Repositories\Contracts\ShopRepositoryInterface;
use Illuminate\Container\Attributes\Config;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class SubscriptionService
{
    public function __construct(
        private ShopRepositoryInterface $shops,
        private PlanService $plans,
        #[Config('subscription.trial_months')] private int $trialMonths,
    ) {}

    /**
     * Subscription of a new shop: the trial plan counted from the owner's sign-up,
     * so opening more shops never restarts it.
     *
     * @return array{plan_id: int, on_trial: bool, subscription_ends_at: \DateTimeInterface}
     */
    public function trialFor(User $owner): array
    {
        return [
            'plan_id' => $this->plans->trialPlan()->id,
            'on_trial' => true,
            'subscription_ends_at' => ($owner->created_at ?? now())->addMonthsNoOverflow($this->trialMonths),
        ];
    }

    /**
     * @return LengthAwarePaginator<int, Shop>
     */
    public function paginate(?string $search, ?SubscriptionStatus $status): LengthAwarePaginator
    {
        return $this->shops->paginateBySubscription($search, $status);
    }

    /**
     * @return array{trial: int, active: int, expired: int, monthly_revenue: int}
     */
    public function summary(): array
    {
        return [
            'trial' => $this->shops->countBySubscriptionStatus(SubscriptionStatus::Trial),
            'active' => $this->shops->countBySubscriptionStatus(SubscriptionStatus::Active),
            'expired' => $this->shops->countBySubscriptionStatus(SubscriptionStatus::Expired),
            'monthly_revenue' => $this->shops->activeSubscriptionRevenue(),
        ];
    }

    /**
     * Switch the plan and, for a paid period, extend from the current end date while it is
     * still running (unused trial days are kept) or from now once it has ended.
     */
    public function change(Shop $shop, SubscriptionData $data): Shop
    {
        $attributes = ['plan_id' => $data->planId];

        if ($data->months > 0) {
            $from = $shop->subscription_ends_at?->isFuture() ? $shop->subscription_ends_at : now();

            $attributes['on_trial'] = false;
            $attributes['subscription_ends_at'] = $from->addMonths($data->months);
        }

        return $this->shops->update($shop, $attributes)->load('plan');
    }

    /**
     * Expired subscriptions the user has to pay for. "locked" means nothing is left to work with:
     * a seller's shop, or every active shop of an owner, has expired.
     *
     * @return array{shops: list<array{id: int, name: string}>, locked: bool, plans: list<array{id: int, name: string, price: int, features: list<string>}>}|null
     */
    public function noticeFor(User $user): ?array
    {
        if ($user->isAdmin()) {
            return null;
        }

        $shops = $this->shops->subscribedShopsOf($user);
        $expired = $shops->reject(fn (Shop $shop): bool => $shop->hasActiveSubscription());

        if ($expired->isEmpty()) {
            return null;
        }

        return [
            'shops' => array_values($expired->map(fn (Shop $shop): array => ['id' => $shop->id, 'name' => $shop->name])->all()),
            'locked' => $expired->count() === $shops->count(),
            'plans' => array_values($this->plans->activeOptions()->map(fn (Plan $plan): array => [
                'id' => $plan->id,
                'name' => $plan->name,
                'price' => $plan->price,
                'features' => array_values($plan->features->map->label()->all()),
            ])->all()),
        ];
    }
}
