<?php

namespace App\Http\Requests\Admin;

use App\Models\ShopType;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Used for both creating and updating a shop type.
 */
class ShopTypeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        /** @var ShopType|null $shopType */
        $shopType = $this->route('shop_type');

        return [
            'name' => ['required', 'string', 'max:100', Rule::unique('shop_types', 'name')->ignore($shopType)],
            'is_active' => ['boolean'],
        ];
    }
}
