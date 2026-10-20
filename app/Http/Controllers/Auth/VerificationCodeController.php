<?php

namespace App\Http\Controllers\Auth;

use App\Enums\SmsPurpose;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\PasswordResetCodeRequest;
use App\Http\Requests\Auth\RegistrationCodeRequest;
use App\Http\Responses\ApiResponse;
use App\Services\VerificationCodeService;
use Illuminate\Http\JsonResponse;

/**
 * JSON endpoints that text a one-time code to the phone number being verified.
 */
class VerificationCodeController extends Controller
{
    public function __construct(private VerificationCodeService $codes) {}

    public function registration(RegistrationCodeRequest $request): JsonResponse
    {
        return $this->send($request->string('phone')->toString(), SmsPurpose::Registration);
    }

    public function passwordReset(PasswordResetCodeRequest $request): JsonResponse
    {
        return $this->send($request->string('phone')->toString(), SmsPurpose::PasswordReset);
    }

    private function send(string $phone, SmsPurpose $purpose): JsonResponse
    {
        $resendAfter = $this->codes->send($phone, $purpose);

        return ApiResponse::success(['resend_after' => $resendAfter], 'Tasdiqlash kodi SMS orqali yuborildi.');
    }
}
