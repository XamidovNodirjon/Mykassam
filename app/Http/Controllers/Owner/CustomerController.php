<?php

namespace App\Http\Controllers\Owner;

use App\DTOs\CustomerData;
use App\Http\Controllers\Controller;
use App\Http\Requests\CustomerRequest;
use App\Http\Resources\CustomerResource;
use App\Http\Resources\ShopResource;
use App\Models\Customer;
use App\Models\Shop;
use App\Services\CustomerService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CustomerController extends Controller
{
    public function __construct(private CustomerService $customers) {}

    public function index(Request $request, Shop $shop): Response
    {
        $search = $request->string('search')->trim()->toString() ?: null;

        return Inertia::render('Owner/Customers/Index', [
            'shop' => ShopResource::make($shop->loadMissing('plan')),
            'customers' => CustomerResource::collection($this->customers->paginate($shop, $search)),
            'filters' => ['search' => $search],
        ]);
    }

    public function store(CustomerRequest $request, Shop $shop): RedirectResponse
    {
        $this->customers->create($shop, CustomerData::fromRequest($request));

        $this->toast("Mijoz qo'shildi.");

        return back();
    }

    public function update(CustomerRequest $request, Shop $shop, Customer $customer): RedirectResponse
    {
        $this->customers->update($customer, CustomerData::fromRequest($request));

        $this->toast("Mijoz ma'lumotlari saqlandi.");

        return back();
    }

    public function destroy(Shop $shop, Customer $customer): RedirectResponse
    {
        $this->customers->delete($customer);

        $this->toast("Mijoz o'chirildi.");

        return back();
    }
}
