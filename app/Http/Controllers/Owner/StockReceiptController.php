<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Http\Requests\Owner\StockReceiptRequest;
use App\Http\Resources\ShopResource;
use App\Http\Resources\StockReceiptResource;
use App\Models\Product;
use App\Models\Shop;
use App\Services\StockService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class StockReceiptController extends Controller
{
    public function __construct(private StockService $stock) {}

    public function index(Request $request, Shop $shop): Response
    {
        $search = $request->string('search')->trim()->toString() ?: null;

        return Inertia::render('Owner/StockReceipts/Index', [
            'shop' => ShopResource::make($shop->loadMissing('plan')),
            'receipts' => StockReceiptResource::collection($this->stock->history($shop, $search)),
            'filters' => ['search' => $search],
        ]);
    }

    public function store(StockReceiptRequest $request, Shop $shop, Product $product): RedirectResponse
    {
        $costPrice = $request->filled('cost_price') ? $request->integer('cost_price') : null;
        $receipt = $this->stock->receive($product, $request->integer('quantity'), $costPrice, $request->user());

        $this->toast("\"{$receipt->product_name}\": +{$receipt->quantity} dona omborga qo'shildi.");

        return back();
    }
}
