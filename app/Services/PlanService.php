<?php

namespace App\Services;

use App\DTOs\PlanData;
use App\Exceptions\BusinessException;
use App\Models\Plan;
use App\Repositories\Contracts\PlanRepositoryInterface;
use Illuminate\Container\Attributes\Config;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;
use RuntimeException;

class PlanService
{
    public function __construct(
        private PlanRepositoryInterface $plans,
        #[Config('subscription.trial_plan')] private string $trialPlanSlug,
    ) {}

    /**
     * @return Collection<int, Plan>
     */
    public function list(): Collection
    {
        return $this->plans->allWithShopCount();
    }

    /**
     * @return Collection<int, Plan>
     */
    public function activeOptions(): Collection
    {
        return $this->plans->active();
    }

    public function trialPlan(): Plan
    {
        return $this->plans->findBySlug($this->trialPlanSlug)
            ?? throw new RuntimeException("Sinov tarifi \"{$this->trialPlanSlug}\" topilmadi.");
    }

    public function isTrialPlan(Plan $plan): bool
    {
        return $plan->slug === $this->trialPlanSlug;
    }

    public function create(PlanData $data): Plan
    {
        return $this->plans->create([...$data->toArray(), 'slug' => $this->uniqueSlug($data->name)]);
    }

    /**
     * Shops keep their plan when it is deactivated; it just can no longer be chosen.
     *
     * @throws BusinessException
     */
    public function update(Plan $plan, PlanData $data): Plan
    {
        if (! $data->isActive && $this->isTrialPlan($plan)) {
            throw new BusinessException('Bepul sinov shu tarifda beriladi, uni nofaol qilib bo\'lmaydi.');
        }

        return $this->plans->update($plan, $data->toArray());
    }

    private function uniqueSlug(string $name): string
    {
        $base = Str::slug($name) ?: 'plan';
        $slug = $base;

        for ($suffix = 2; $this->plans->slugExists($slug); $suffix++) {
            $slug = "{$base}-{$suffix}";
        }

        return $slug;
    }
}
