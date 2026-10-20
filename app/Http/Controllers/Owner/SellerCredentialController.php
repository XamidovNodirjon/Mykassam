<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Http\Requests\Owner\SellerCredentialRequest;
use App\Http\Responses\ApiResponse;
use App\Services\SellerService;
use Illuminate\Http\JsonResponse;

class SellerCredentialController extends Controller
{
    /**
     * Suggest a unique login and a fresh password for the typed phone number.
     */
    public function __invoke(SellerCredentialRequest $request, SellerService $sellers): JsonResponse
    {
        $sellerId = $request->integer('seller_id');

        $credentials = $sellers->suggestCredentials(
            $request->string('phone')->toString(),
            $sellerId > 0 ? $sellerId : null,
        );

        return ApiResponse::success($credentials->toArray());
    }
}
