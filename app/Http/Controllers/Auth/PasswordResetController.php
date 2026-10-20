<?php

namespace App\Http\Controllers\Auth;

use App\DTOs\ResetPasswordData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ResetPasswordRequest;
use App\Services\AuthService;
use Illuminate\Http\RedirectResponse;

class PasswordResetController extends Controller
{
    public function store(ResetPasswordRequest $request, AuthService $auth): RedirectResponse
    {
        $auth->resetPassword(ResetPasswordData::fromRequest($request));

        $this->toast('Parol yangilandi. Endi yangi parol bilan kiring.');

        return back();
    }
}
