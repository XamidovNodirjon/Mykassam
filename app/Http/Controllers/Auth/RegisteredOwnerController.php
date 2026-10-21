<?php

namespace App\Http\Controllers\Auth;

use App\DTOs\RegisterOwnerData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterOwnerRequest;
use App\Http\Requests\Auth\VerifyRegistrationCodeRequest;
use App\Http\Responses\ApiResponse;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class RegisteredOwnerController extends Controller
{
    public function create(): Response
    {
        return Inertia::render('Auth/Register');
    }

    /**
     * Check the SMS code before asking for the name and password.
     */
    public function verify(VerifyRegistrationCodeRequest $request, AuthService $auth): JsonResponse
    {
        $auth->confirmRegistrationPhone(
            $request->string('phone')->toString(),
            $request->string('code')->toString(),
        );

        return ApiResponse::success(null, 'Telefon raqam tasdiqlandi.');
    }

    public function store(RegisterOwnerRequest $request, AuthService $auth): RedirectResponse
    {
        $auth->registerOwner(RegisterOwnerData::fromRequest($request));

        $request->session()->regenerate();

        $this->toast("Xush kelibsiz! Endi birinchi do'koningizni yarating.");

        return redirect()->route('owner.shops.create');
    }
}
