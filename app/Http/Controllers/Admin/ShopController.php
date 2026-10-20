<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\ShopResource;
use App\Models\Shop;
use App\Services\ShopService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ShopController extends Controller
{
    public function __construct(private ShopService $shops) {}

    public function index(Request $request): Response
    {
        $search = $request->string('search')->trim()->toString() ?: null;

        return Inertia::render('Admin/Shops/Index', [
            'shops' => ShopResource::collection($this->shops->paginateAll($search)),
            'filters' => ['search' => $search],
        ]);
    }

    /**
     * Block or unblock a shop.
     */
    public function toggleStatus(Shop $shop): RedirectResponse
    {
        $shop = $this->shops->toggleStatus($shop);

        $this->toast($shop->is_active ? "Do'kon faollashtirildi." : "Do'kon bloklandi.");

        return back();
    }
}
