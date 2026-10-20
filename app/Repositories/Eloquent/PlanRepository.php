<?php

namespace App\Repositories\Eloquent;

use App\Models\Plan;
use App\Repositories\Contracts\PlanRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class PlanRepository implements PlanRepositoryInterface
{
    public function allWithShopCount(): Collection
    {
        return Plan::query()
            ->withCount('shops')
            ->orderBy('sort_order')
            ->orderBy('price')
            ->get();
    }

    public function active(): Collection
    {
        return Plan::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('price')
            ->get();
    }

    public function findBySlug(string $slug): ?Plan
    {
        return Plan::query()->where('slug', $slug)->first();
    }

    public function slugExists(string $slug): bool
    {
        return Plan::query()->where('slug', $slug)->exists();
    }

    public function create(array $attributes): Plan
    {
        return Plan::query()->create($attributes);
    }

    public function update(Plan $plan, array $attributes): Plan
    {
        $plan->update($attributes);

        return $plan;
    }
}
