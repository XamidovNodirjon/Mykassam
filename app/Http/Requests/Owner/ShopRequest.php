<?php

namespace App\Http\Requests\Owner;

use App\Models\Shop;
use App\Support\PhoneNumber;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Used for both creating and updating a shop.
 */
class ShopRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'phone' => PhoneNumber::normalize($this->input('phone')),
        ]);
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        /** @var Shop|null $shop */
        $shop = $this->route('shop');

        $shopTypeRule = Rule::exists('shop_types', 'id');

        // A shop may keep a type the administrator has since deactivated.
        if ($this->integer('shop_type_id') !== $shop?->shop_type_id) {
            $shopTypeRule->where('is_active', true);
        }

        return [
            'name' => ['required', 'string', 'max:150'],
            'shop_type_id' => ['required', 'integer', $shopTypeRule],
            'address' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'regex:'.PhoneNumber::PATTERN],
            'cashback_percent' => ['nullable', 'integer', 'min:0', 'max:50'],
            'is_active' => ['boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'shop_type_id' => "do'kon turi",
        ];
    }
}
