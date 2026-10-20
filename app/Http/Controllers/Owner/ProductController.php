<?php

namespace App\Http\Controllers\Owner;

use App\DTOs\ProductData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Owner\ProductRequest;
use App\Http\Resources\ProductResource;
use App\Http\Resources\ShopResource;
use App\Models\Product;
use App\Models\Shop;
use App\Services\ProductService;
use App\Services\StockService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ProductController extends Controller
{
    public function __construct(private ProductService $products) {}

    public function index(Request $request, Shop $shop, StockService $stock): Response
    {
        $search = $request->string('search')->trim()->toString() ?: null;
        $lowStockOnly = $request->boolean('low_stock');

        return Inertia::render('Owner/Products/Index', [
            'shop' => ShopResource::make($shop->loadMissing('plan')),
            'products' => ProductResource::collection($this->products->paginateForShop($shop, $search, $lowStockOnly)),
            'lowStockCount' => $stock->lowStockCount($shop),
            'filters' => ['search' => $search, 'low_stock' => $lowStockOnly],
        ]);
    }

    public function create(Shop $shop): Response
    {
        return Inertia::render('Owner/Products/Form', [
            'shop' => ShopResource::make($shop),
            'product' => null,
        ]);
    }

    public function store(ProductRequest $request, Shop $shop): RedirectResponse
    {
        $this->products->create($shop, ProductData::fromRequest($request));

        $this->toast("Mahsulot qo'shildi.");

        return redirect()->route('owner.shops.products.index', $shop);
    }

    public function edit(Shop $shop, Product $product): Response
    {
        return Inertia::render('Owner/Products/Form', [
            'shop' => ShopResource::make($shop),
            'product' => ProductResource::make($product),
        ]);
    }

    public function update(ProductRequest $request, Shop $shop, Product $product): RedirectResponse
    {
        $this->products->update($product, ProductData::fromRequest($request));

        $this->toast('Mahsulot yangilandi.');

        return redirect()->route('owner.shops.products.index', $shop);
    }

    public function destroy(Shop $shop, Product $product): RedirectResponse
    {
        $this->products->delete($product);

        $this->toast("Mahsulot o'chirildi.");

        return redirect()->route('owner.shops.products.index', $shop);
    }
}
