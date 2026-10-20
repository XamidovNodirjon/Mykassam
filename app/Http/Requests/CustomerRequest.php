<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\NormalizesPhone;
use App\Models\Customer;
use App\Models\Shop;
use App\Support\PhoneNumber;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Creating or updating a customer, by the owner (route {shop}) or a seller (their own shop).
 */
class CustomerRequest extends FormRequest
{
    use NormalizesPhone;

    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        /** @var Customer|null $customer */
        $customer = $this->route('customer');

        return [
            'name' => ['required', 'string', 'max:150'],
            'phone' => [
                'nullable',
                'string',
                'regex:'.PhoneNumber::PATTERN,
                Rule::unique('customers', 'phone')->where('shop_id', $this->shop()->id)->ignore($customer),
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'phone.unique' => 'Bu telefon raqamli mijoz allaqachon bor.',
        ];
    }

    public function shop(): Shop
    {
        /** @var Shop|null $shop */
        $shop = $this->route('shop');

        return $shop ?? $this->user()->loadMissing('shop')->shop;
    }
}
