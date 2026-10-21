<?php

namespace App\Http\Controllers\Admin;

use App\Exceptions\BusinessException;
use App\Http\Controllers\Controller;
use App\Models\SmsMessage;
use App\Services\SmsService;
use App\Services\VerificationCodeService;
use Illuminate\Http\RedirectResponse;

class SmsResendController extends Controller
{
    /**
     * Send an undelivered SMS again. A verification code is never stored, so the phone gets a
     * new code instead, which replaces the one it never received.
     *
     * @throws BusinessException
     */
    public function __invoke(SmsMessage $smsMessage, SmsService $sms, VerificationCodeService $codes): RedirectResponse
    {
        if (! $smsMessage->purpose->isVerificationCode()) {
            $sms->resend($smsMessage);
            $this->toast("SMS qayta navbatga qo'yildi.");

            return back();
        }

        $reason = $smsMessage->resendBlockedReason();

        if ($reason !== null) {
            throw new BusinessException($reason);
        }

        $codes->send($smsMessage->phone, $smsMessage->purpose);
        $this->toast("Yangi tasdiqlash kodi navbatga qo'yildi.");

        return back();
    }
}
