<?php

namespace App\Http\Controllers\Seller;

use App\DTOs\SaleData;
use App\DTOs\SaleFilterData;
use App\Enums\PaymentMethod;
use App\Enums\PlanFeature;
use App\Http\Controllers\Controller;
use App\Http\Requests\SaleFilterRequest;
use App\Http\Requests\Seller\SaleRequest;
use App\Http\Resources\SaleResource;
use App\Http\Resources\SellableProductResource;
use App\Models\Sale;
use App\Services\ProductService;
use App\Services\SaleService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SaleController extends Controller
{
    public function __construct(private SaleService $sales) {}

    public function index(SaleFilterRequest $request): Response
    {
        $seller = $request->user();
        $filter = SaleFilterData::fromRequest($request, [(int) $seller->shop_id], $seller->id);

        return Inertia::render('Seller/Sales/Index', [
            'sales' => SaleResource::collection($this->sales->paginate($filter)),
            'filters' => $filter->toArray(),
        ]);
    }

    /**
     * Point of sale screen. The product list is searched through partial reloads.
     */
    public function create(Request $request, ProductService $products): Response
    {
        $search = $request->string('search')->trim()->toString() ?: null;
        $shop = $request->user()->loadMissing('shop.plan')->shop;
        $sellsOnCredit = $shop->hasFeature(PlanFeature::Debts);

        return Inertia::render('Seller/Sales/Create', [
            'products' => SellableProductResource::collection(
                $products->sellableForShop($shop->id, $search),
            ),
            'search' => $search,
            'paymentMethods' => PaymentMethod::options($sellsOnCredit ? null : PaymentMethod::immediate()),
            'customersEnabled' => $shop->hasFeature(PlanFeature::Customers),
            'cashbackPercent' => $shop->cashback_percent,
        ]);
    }

    public function store(SaleRequest $request): RedirectResponse
    {
        $sale = $this->sales->create($request->user(), SaleData::fromRequest($request));

        Inertia::flash('lastSaleId', $sale->id);
        $this->toast(sprintf("Savdo #%d saqlandi: %s so'm.", $sale->id, number_format($sale->total_amount, 0, '.', ' ')));

        return redirect()->route('seller.sales.create');
    }

    public function show(Sale $sale): Response
    {
        return Inertia::render('Seller/Sales/Show', [
            'sale' => SaleResource::make($this->sales->details($sale)),
        ]);
    }
}
