<?php

namespace App\Http\Controllers\Admin;

use App\DTOs\ShopTypeData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ShopTypeRequest;
use App\Http\Resources\ShopTypeResource;
use App\Models\ShopType;
use App\Services\ShopTypeService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class ShopTypeController extends Controller
{
    public function __construct(private ShopTypeService $shopTypes) {}

    public function index(): Response
    {
        return Inertia::render('Admin/ShopTypes/Index', [
            'shopTypes' => ShopTypeResource::collection($this->shopTypes->list()),
        ]);
    }

    public function store(ShopTypeRequest $request): RedirectResponse
    {
        $this->shopTypes->create(ShopTypeData::fromRequest($request));

        $this->toast("Do'kon turi qo'shildi.");

        return back();
    }

    public function update(ShopTypeRequest $request, ShopType $shopType): RedirectResponse
    {
        $this->shopTypes->update($shopType, ShopTypeData::fromRequest($request));

        $this->toast("Do'kon turi yangilandi.");

        return back();
    }

    public function destroy(ShopType $shopType): RedirectResponse
    {
        $this->shopTypes->delete($shopType);

        $this->toast("Do'kon turi o'chirildi.");

        return back();
    }
}
