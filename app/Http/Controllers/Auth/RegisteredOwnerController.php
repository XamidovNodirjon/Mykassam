<?php

namespace App\Http\Controllers\Auth;

use App\DTOs\RegisterOwnerData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterOwnerRequest;
use App\Services\AuthService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class RegisteredOwnerController extends Controller
{
    public function create(): Response
    {
        return Inertia::render('Auth/Register');
    }

    public function store(RegisterOwnerRequest $request, AuthService $auth): RedirectResponse
    {
        $auth->registerOwner(RegisterOwnerData::fromRequest($request));

        $request->session()->regenerate();

        $this->toast("Xush kelibsiz! Endi birinchi do'koningizni yarating.");

        return redirect()->route('owner.shops.create');
    }
}
