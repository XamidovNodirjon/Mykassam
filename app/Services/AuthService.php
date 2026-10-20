<?php

namespace App\Services;

use App\DTOs\LoginData;
use App\DTOs\RegisterOwnerData;
use App\DTOs\ResetPasswordData;
use App\Enums\SmsPurpose;
use App\Enums\UserRole;
use App\Models\User;
use App\Repositories\Contracts\UserRepositoryInterface;
use App\Support\PhoneNumber;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthService
{
    public function __construct(
        private UserRepositoryInterface $users,
        private VerificationCodeService $verificationCodes,
    ) {}

    /**
     * Sign in with a login or phone number. Owners use their phone number as login.
     *
     * @throws ValidationException
     */
    public function login(LoginData $data): User
    {
        $user = $this->users->findByLogin($this->resolveLogin($data->login));

        if ($user === null || ! Hash::check($data->password, $user->password)) {
            throw ValidationException::withMessages([
                'login' => "Login yoki parol noto'g'ri.",
            ]);
        }

        if (! $user->hasActiveAccess()) {
            throw ValidationException::withMessages([
                'login' => 'Hisobingiz faol emas. Administrator yoki do\'kon egasiga murojaat qiling.',
            ]);
        }

        Auth::login($user, $data->remember);

        return $user;
    }

    /**
     * @throws ValidationException When the SMS code is wrong or expired.
     */
    public function registerOwner(RegisterOwnerData $data): User
    {
        $this->verificationCodes->verify($data->phone, SmsPurpose::Registration, $data->code);

        $user = $this->users->create([
            'first_name' => $data->firstName,
            'phone' => $data->phone,
            'login' => $data->phone,
            'password' => $data->password,
            'role' => UserRole::Owner,
            'is_active' => true,
        ]);

        Auth::login($user);

        return $user;
    }

    /**
     * Set a new password for the account whose login is the phone number, once its SMS code is confirmed.
     *
     * @throws ValidationException When the SMS code is wrong or expired.
     */
    public function resetPassword(ResetPasswordData $data): void
    {
        $this->verificationCodes->verify($data->phone, SmsPurpose::PasswordReset, $data->code);

        $user = $this->users->findByLogin($data->phone) ?? throw ValidationException::withMessages([
            'phone' => "Bu raqam bilan ro'yxatdan o'tgan hisob topilmadi.",
        ]);

        $this->users->update($user, ['password' => $data->password]);
    }

    /**
     * Phone-looking input is normalized so "+998 90 123-45-67" matches the stored "998901234567".
     * Input with letters (seller logins such as "s901234567") is used as is.
     */
    private function resolveLogin(string $login): string
    {
        $login = mb_strtolower(trim($login));

        if (preg_match('/^[\d\s()+-]+$/', $login) !== 1) {
            return $login;
        }

        $phone = PhoneNumber::normalize($login);

        return PhoneNumber::isValid($phone) ? (string) $phone : $login;
    }
}
