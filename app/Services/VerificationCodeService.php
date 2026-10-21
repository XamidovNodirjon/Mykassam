<?php

namespace App\Services;

use App\Enums\SmsPurpose;
use App\Exceptions\SmsGatewayException;
use Illuminate\Container\Attributes\Config;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

/**
 * One-time SMS codes that confirm the user owns a phone number.
 * Only a hash of the code is kept, in the cache, until it is used or expires.
 */
class VerificationCodeService
{
    /**
     * Seconds a confirmed phone stays usable for finishing the sign-up.
     */
    public const CONFIRMATION_TTL = 900;

    public function __construct(
        private SmsService $sms,
        #[Config('sms.verification.length')] private int $length,
        #[Config('sms.verification.ttl')] private int $ttl,
        #[Config('sms.verification.resend_after')] private int $resendAfter,
        #[Config('sms.verification.max_attempts')] private int $maxAttempts,
    ) {}

    /**
     * @return int Seconds until another code may be requested.
     *
     * @throws ValidationException
     */
    public function send(string $phone, SmsPurpose $purpose): int
    {
        $key = $this->cacheKey($phone, $purpose);
        $wait = (Cache::get($key)['resend_at'] ?? 0) - now()->getTimestamp();

        if ($wait > 0) {
            throw ValidationException::withMessages([
                'phone' => "Kod yuborilgan. Qayta so'rash uchun {$wait} soniya kuting.",
            ]);
        }

        $code = $this->generateCode();

        Cache::put($key, [
            'hash' => Hash::make($code),
            'attempts' => 0,
            'resend_at' => now()->addSeconds($this->resendAfter)->getTimestamp(),
            'expires_at' => now()->addSeconds($this->ttl)->getTimestamp(),
        ], $this->ttl);

        try {
            $this->sms->send($phone, $purpose, ['code' => $code]);
        } catch (SmsGatewayException $exception) {
            // The code never arrived, so it must not block an immediate retry.
            Cache::forget($key);
            report($exception);

            throw ValidationException::withMessages([
                'phone' => "SMS yuborib bo'lmadi. Birozdan so'ng qayta urinib ko'ring.",
            ]);
        }

        return $this->resendAfter;
    }

    /**
     * Consume the code. Each wrong guess counts toward the attempt limit.
     *
     * @throws ValidationException
     */
    public function verify(string $phone, SmsPurpose $purpose, string $code): void
    {
        $key = $this->cacheKey($phone, $purpose);
        $pending = Cache::get($key);

        if ($pending === null) {
            throw ValidationException::withMessages([
                'code' => "Kodning muddati tugagan. Yangi kod so'rang.",
            ]);
        }

        if (Hash::check($code, $pending['hash'])) {
            Cache::forget($key);

            return;
        }

        $attemptsLeft = $this->maxAttempts - ++$pending['attempts'];

        if ($attemptsLeft <= 0) {
            Cache::forget($key);
        } else {
            Cache::put($key, $pending, Date::createFromTimestamp($pending['expires_at']));
        }

        throw ValidationException::withMessages([
            'code' => $attemptsLeft > 0
                ? "Kod noto'g'ri. Qolgan urinishlar: {$attemptsLeft}."
                : "Urinishlar tugadi. Yangi kod so'rang.",
        ]);
    }

    /**
     * Check the code and remember in the session that this visitor owns the phone, so the next
     * step (e.g. name and password) can follow without the code.
     *
     * @throws ValidationException
     */
    public function confirm(string $phone, SmsPurpose $purpose, string $code): void
    {
        $this->verify($phone, $purpose, $code);

        session()->put($this->confirmationKey($purpose), [
            'phone' => $phone,
            'expires_at' => now()->addSeconds(self::CONFIRMATION_TTL)->getTimestamp(),
        ]);
    }

    /**
     * Use up the confirmation made by confirm().
     *
     * @throws ValidationException When the phone was not confirmed in this session or it expired.
     */
    public function consumeConfirmation(string $phone, SmsPurpose $purpose): void
    {
        $confirmation = session()->pull($this->confirmationKey($purpose));

        if (($confirmation['phone'] ?? null) !== $phone || ($confirmation['expires_at'] ?? 0) < now()->getTimestamp()) {
            throw ValidationException::withMessages([
                'phone' => "Telefon raqam tasdiqlanmagan yoki vaqt tugagan. Kodni qaytadan so'rang.",
            ]);
        }
    }

    private function confirmationKey(SmsPurpose $purpose): string
    {
        return "verified-phone.{$purpose->value}";
    }

    private function generateCode(): string
    {
        return str_pad((string) random_int(0, 10 ** $this->length - 1), $this->length, '0', STR_PAD_LEFT);
    }

    private function cacheKey(string $phone, SmsPurpose $purpose): string
    {
        return "verification-code:{$purpose->value}:{$phone}";
    }
}
