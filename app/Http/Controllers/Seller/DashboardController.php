<?php

namespace App\Http\Controllers\Seller;

use App\DTOs\StatisticsFilterData;
use App\Enums\StatisticsPeriod;
use App\Http\Controllers\Controller;
use App\Http\Requests\StatisticsRequest;
use App\Http\Resources\SaleResource;
use App\Services\SaleService;
use App\Services\StatisticsService;
use Illuminate\Support\Arr;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    /**
     * Personal statistics of the signed-in seller. Cost and profit figures are not exposed.
     */
    public function __invoke(StatisticsRequest $request, StatisticsService $statistics, SaleService $sales): Response
    {
        $seller = $request->user();
        $filter = StatisticsFilterData::fromRequest($request, [(int) $seller->shop_id], $seller->id);

        return Inertia::render('Seller/Dashboard', [
            'filters' => $filter->toArray(),
            'periods' => StatisticsPeriod::options(),
            'totals' => Arr::only($statistics->totals($filter), ['revenue', 'sales_count', 'items_count', 'average_check']),
            'daily' => array_map(
                fn (array $day): array => Arr::except($day, 'profit'),
                $statistics->dailySeries($filter),
            ),
            'topProducts' => $statistics->topProducts($filter),
            'latestSales' => SaleResource::collection($sales->latestForSeller($seller)),
        ]);
    }
}
