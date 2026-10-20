<?php

namespace App\Http\Controllers\Admin;

use App\DTOs\PlanData;
use App\Enums\PlanFeature;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\PlanRequest;
use App\Http\Resources\PlanResource;
use App\Models\Plan;
use App\Services\PlanService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class PlanController extends Controller
{
    public function __construct(private PlanService $plans) {}

    public function index(): Response
    {
        return Inertia::render('Admin/Plans/Index', [
            'plans' => PlanResource::collection($this->plans->list()),
            'features' => PlanFeature::options(),
            'trialPlanId' => $this->plans->trialPlan()->id,
        ]);
    }

    public function store(PlanRequest $request): RedirectResponse
    {
        $this->plans->create(PlanData::fromRequest($request));

        $this->toast("Tarif qo'shildi.");

        return back();
    }

    public function update(PlanRequest $request, Plan $plan): RedirectResponse
    {
        $this->plans->update($plan, PlanData::fromRequest($request));

        $this->toast('Tarif saqlandi. O\'zgarish shu tarifdagi barcha do\'konlarga darhol tegishli.');

        return back();
    }
}
