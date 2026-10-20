<?php

namespace App\Http\Controllers\Owner;

use App\DTOs\SaleFilterData;
use App\Http\Controllers\Controller;
use App\Http\Requests\SaleFilterRequest;
use App\Http\Resources\SaleResource;
use App\Http\Resources\ShopResource;
use App\Http\Resources\UserResource;
use App\Models\Sale;
use App\Services\SaleService;
use App\Services\SellerService;
use App\Services\ShopService;
use Inertia\Inertia;
use Inertia\Response;

class SaleController extends Controller
{
    public function __construct(private SaleService $sales) {}

    public function index(SaleFilterRequest $request, ShopService $shops, SellerService $sellers): Response
    {
        $shopOptions = $shops->optionsForOwner($request->user());
        $shopIds = $shops->idsOf($shopOptions);
        $filter = SaleFilterData::fromRequest($request, $shopIds);

        return Inertia::render('Owner/Sales/Index', [
            'sales' => SaleResource::collection($this->sales->paginate($filter)),
            'filters' => $filter->toArray(),
            'shopOptions' => ShopResource::collection($shopOptions),
            'sellerOptions' => UserResource::collection($sellers->optionsForShops($shopIds)),
        ]);
    }

    public function show(Sale $sale): Response
    {
        return Inertia::render('Owner/Sales/Show', [
            'sale' => SaleResource::make($this->sales->details($sale)),
        ]);
    }
}
