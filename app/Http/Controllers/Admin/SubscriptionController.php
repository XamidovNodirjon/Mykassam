<?php

namespace App\Http\Controllers\Admin;

use App\DTOs\SubscriptionData;
use App\Enums\SubscriptionStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SubscriptionRequest;
use App\Http\Resources\PlanResource;
use App\Http\Resources\ShopResource;
use App\Models\Shop;
use App\Services\PlanService;
use App\Services\SubscriptionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SubscriptionController extends Controller
{
    public function __construct(private SubscriptionService $subscriptions) {}

    public function index(Request $request, PlanService $plans): Response
    {
        $search = $request->string('search')->trim()->toString() ?: null;
        $status = $request->enum('status', SubscriptionStatus::class);

        return Inertia::render('Admin/Subscriptions/Index', [
            'shops' => ShopResource::collection($this->subscriptions->paginate($search, $status)),
            'summary' => $this->subscriptions->summary(),
            'plans' => PlanResource::collection($plans->activeOptions()),
            'filters' => ['search' => $search, 'status' => $status?->value],
        ]);
    }

    public function update(SubscriptionRequest $request, Shop $shop): RedirectResponse
    {
        $shop = $this->subscriptions->change($shop, SubscriptionData::fromRequest($request));

        $this->toast(sprintf(
            '"%s": %s tarifi, %s gacha.',
            $shop->name,
            $shop->plan->name ?? '—',
            $shop->subscription_ends_at?->format('d.m.Y') ?? '—',
        ));

        return back();
    }
}
