<?php

namespace App\Http\Requests\Admin;

use App\Enums\PlanFeature;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Used for both creating and updating a plan. Empty limits mean "unlimited".
 */
class PlanRequest extends FormRequest
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
        return [
            'name' => ['required', 'string', 'max:100'],
            'price' => ['required', 'integer', 'min:0', 'max:100000000'],
            'max_sellers' => ['nullable', 'integer', 'min:1', 'max:10000'],
            'max_products' => ['nullable', 'integer', 'min:1', 'max:10000000'],
            'features' => ['array'],
            'features.*' => ['distinct', Rule::enum(PlanFeature::class)],
            'is_active' => ['boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:1000'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'name' => 'nomi',
            'price' => 'narx',
            'max_sellers' => 'sotuvchilar limiti',
            'max_products' => 'mahsulotlar limiti',
        ];
    }
}
