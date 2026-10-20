<?php

namespace App\Http\Controllers\Seller;

use App\DTOs\CustomerData;
use App\Http\Controllers\Controller;
use App\Http\Requests\CustomerRequest;
use App\Http\Resources\CustomerResource;
use App\Http\Responses\ApiResponse;
use App\Services\CustomerService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Customer lookup and quick sign-up from the point of sale.
 */
class CustomerController extends Controller
{
    public function __construct(private CustomerService $customers) {}

    public function index(Request $request): JsonResponse
    {
        $search = $request->string('search')->trim()->toString();
        $shop = $request->user()->loadMissing('shop')->shop;

        return ApiResponse::success(
            mb_strlen($search) >= 2 ? CustomerResource::collection($this->customers->search($shop, $search))->resolve() : [],
        );
    }

    public function store(CustomerRequest $request): JsonResponse
    {
        $customer = $this->customers->create($request->shop(), CustomerData::fromRequest($request));

        return ApiResponse::success(CustomerResource::make($customer->refresh()), "Mijoz qo'shildi.", Response::HTTP_CREATED);
    }
}
