<?php

namespace App\Http\Controllers\Owner;

use App\DTOs\ShopData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Owner\ShopRequest;
use App\Http\Resources\ShopResource;
use App\Http\Resources\ShopTypeResource;
use App\Models\Shop;
use App\Services\ShopService;
use App\Services\ShopTypeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ShopController extends Controller
{
    public function __construct(
        private ShopService $shops,
        private ShopTypeService $shopTypes,
    ) {}

    public function index(Request $request): Response
    {
        return Inertia::render('Owner/Shops/Index', [
            'shops' => ShopResource::collection($this->shops->listForOwner($request->user())),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Owner/Shops/Form', [
            'shop' => null,
            'shopTypes' => ShopTypeResource::collection($this->shopTypes->activeOptions()),
        ]);
    }

    public function store(ShopRequest $request): RedirectResponse
    {
        $shop = $this->shops->create($request->user(), ShopData::fromRequest($request));

        $this->toast("Do'kon yaratildi. Endi mahsulot va sotuvchilarni qo'shing.");

        return redirect()->route('owner.shops.products.index', $shop);
    }

    public function edit(Shop $shop): Response
    {
        $shop->load('type:id,name');

        return Inertia::render('Owner/Shops/Form', [
            'shop' => ShopResource::make($shop),
            'shopTypes' => ShopTypeResource::collection($this->shopTypes->activeOptions()),
        ]);
    }

    public function update(ShopRequest $request, Shop $shop): RedirectResponse
    {
        $this->shops->update($shop, ShopData::fromRequest($request));

        $this->toast("Do'kon ma'lumotlari saqlandi.");

        return redirect()->route('owner.shops.index');
    }

    public function destroy(Shop $shop): RedirectResponse
    {
        $this->shops->delete($shop);

        $this->toast("Do'kon o'chirildi.");

        return redirect()->route('owner.shops.index');
    }
}
