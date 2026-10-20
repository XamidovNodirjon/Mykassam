<?php

namespace App\Http\Controllers\Owner;

use App\DTOs\SellerData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Owner\SellerRequest;
use App\Http\Resources\ShopResource;
use App\Http\Resources\UserResource;
use App\Models\Shop;
use App\Models\User;
use App\Services\SellerService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class SellerController extends Controller
{
    public function __construct(private SellerService $sellers) {}

    public function index(Shop $shop): Response
    {
        return Inertia::render('Owner/Sellers/Index', [
            'shop' => ShopResource::make($shop->loadMissing('plan')),
            'sellers' => UserResource::collection($this->sellers->listForShop($shop)),
        ]);
    }

    public function create(Shop $shop): Response
    {
        return Inertia::render('Owner/Sellers/Form', [
            'shop' => ShopResource::make($shop),
            'seller' => null,
        ]);
    }

    public function store(SellerRequest $request, Shop $shop): RedirectResponse
    {
        $credentials = $this->sellers->create($shop, SellerData::fromRequest($request));

        Inertia::flash('credentials', $credentials->toArray());
        $this->toast("Sotuvchi qo'shildi. Login va parolni unga yetkazing.");

        return redirect()->route('owner.shops.sellers.index', $shop);
    }

    public function edit(Shop $shop, User $seller): Response
    {
        return Inertia::render('Owner/Sellers/Form', [
            'shop' => ShopResource::make($shop),
            'seller' => UserResource::make($seller),
        ]);
    }

    public function update(SellerRequest $request, Shop $shop, User $seller): RedirectResponse
    {
        $credentials = $this->sellers->update($seller, SellerData::fromRequest($request));

        if ($credentials !== null) {
            Inertia::flash('credentials', $credentials->toArray());
        }

        $this->toast("Sotuvchi ma'lumotlari saqlandi.");

        return redirect()->route('owner.shops.sellers.index', $shop);
    }

    public function destroy(Shop $shop, User $seller): RedirectResponse
    {
        $this->sellers->delete($seller);

        $this->toast("Sotuvchi o'chirildi.");

        return redirect()->route('owner.shops.sellers.index', $shop);
    }
}
