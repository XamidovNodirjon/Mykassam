<?php

namespace App\Repositories\Contracts;

use App\Models\Plan;
use Illuminate\Database\Eloquent\Collection;

interface PlanRepositoryInterface
{
    /**
     * Every plan with its shop count, in display order.
     *
     * @return Collection<int, Plan>
     */
    public function allWithShopCount(): Collection;

    /**
     * @return Collection<int, Plan>
     */
    public function active(): Collection;

    public function findBySlug(string $slug): ?Plan;

    public function slugExists(string $slug): bool;

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function create(array $attributes): Plan;

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function update(Plan $plan, array $attributes): Plan;
}
