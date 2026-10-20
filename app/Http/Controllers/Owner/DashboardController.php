<?php

namespace App\Http\Controllers\Owner;

use App\DTOs\StatisticsFilterData;
use App\Enums\PlanFeature;
use App\Enums\StatisticsPeriod;
use App\Http\Controllers\Controller;
use App\Http\Requests\StatisticsRequest;
use App\Http\Resources\ProductResource;
use App\Http\Resources\ShopResource;
use App\Http\Resources\UserResource;
use App\Services\ShopService;
use App\Services\StatisticsService;
use App\Services\StockService;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(StatisticsRequest $request, StatisticsService $statistics, ShopService $shops, StockService $stock): Response
    {
        $owner = $request->user();
        $shopOptions = $shops->optionsForOwner($owner);
        $filter = StatisticsFilterData::fromRequest($request, $shops->idsOf($shopOptions));

        return Inertia::render('Owner/Dashboard', [
            'filters' => $filter->toArray(),
            'periods' => StatisticsPeriod::options(),
            'shopOptions' => ShopResource::collection($shopOptions),
            'totals' => $statistics->totals($filter),
            'daily' => $statistics->dailySeries($filter),
            'sellers' => UserResource::collection($statistics->sellers($filter)),
            'shops' => ShopResource::collection($statistics->shops($filter)),
            'topProducts' => $statistics->topProducts($filter),
            'lowStock' => ProductResource::collection($stock->lowStockForShops($shops->idsWithFeature($shopOptions, PlanFeature::StockControl))),
        ]);
    }
}
