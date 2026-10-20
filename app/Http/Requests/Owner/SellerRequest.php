<?php

namespace App\Http\Requests\Owner;

use App\Models\User;
use App\Support\PhoneNumber;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Used for both creating and updating a seller. Password is required only on create.
 */
class SellerRequest extends FormRequest
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
        /** @var User|null $seller */
        $seller = $this->route('seller');

        return [
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'phone' => ['required', 'string', 'regex:'.PhoneNumber::PATTERN],
            'login' => ['required', 'string', 'min:3', 'max:50', 'regex:/^[a-z0-9_]+$/', Rule::unique('users', 'login')->ignore($seller)],
            'password' => [$seller === null ? 'required' : 'nullable', 'string', 'min:6', 'max:64'],
            'is_active' => ['boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'login.regex' => "Login faqat lotin harflari, raqamlar va _ belgisidan iborat bo'lishi kerak.",
            'login.unique' => 'Bu login band. Boshqasini yarating.',
        ];
    }
}
