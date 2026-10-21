<?php

namespace App\Http\Requests\Admin;

use App\Enums\UserRole;
use App\Models\User;
use App\Support\PhoneNumber;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * An administrator editing any account. Owners sign in with their phone, so for them the phone
 * is the login; everyone else keeps a separate login.
 */
class UserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'phone' => PhoneNumber::normalize($this->input('phone')),
            'login' => mb_strtolower(trim((string) $this->input('login'))),
        ]);
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        /** @var User $user */
        $user = $this->route('user');
        $isOwner = $user->role === UserRole::Owner;

        return [
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['nullable', 'string', 'max:100'],
            'phone' => $isOwner
                ? ['required', 'string', 'regex:'.PhoneNumber::PATTERN, Rule::unique('users', 'login')->ignore($user)]
                : [$user->role === UserRole::Seller ? 'required' : 'nullable', 'string', 'regex:'.PhoneNumber::PATTERN],
            'login' => $isOwner
                ? ['nullable']
                : ['required', 'string', 'min:3', 'max:50', 'regex:/^[a-z0-9_]+$/', Rule::unique('users', 'login')->ignore($user)],
            'password' => ['nullable', 'string', 'min:6', 'max:64'],
            'is_active' => ['boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'phone.unique' => "Bu telefon raqam bilan boshqa hisob ro'yxatdan o'tgan.",
            'login.regex' => "Login faqat lotin harflari, raqamlar va _ belgisidan iborat bo'lishi kerak.",
            'login.unique' => 'Bu login band. Boshqasini tanlang.',
        ];
    }
}
