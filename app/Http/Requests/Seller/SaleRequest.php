<?php

namespace App\Http\Requests\Seller;

use App\Enums\PaymentMethod;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Validates the cart shape. Price floors and stock are enforced by SaleService on locked rows.
 */
class SaleRequest extends FormRequest
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
            'payment_method' => ['required', Rule::enum(PaymentMethod::class)],
            'items' => ['required', 'array', 'min:1', 'max:100'],
            'items.*.product_id' => ['required', 'integer', 'distinct'],
            'items.*.quantity' => ['required', 'integer', 'min:1', 'max:100000'],
            'items.*.unit_price' => ['required', 'integer', 'min:0', 'max:1000000000000'],
            'customer_id' => ['nullable', 'integer', 'required_if:payment_method,'.PaymentMethod::Debt->value],
            'bonus_used' => ['nullable', 'integer', 'min:0', 'max:1000000000000'],
            'due_date' => ['nullable', 'date', 'after_or_equal:today', 'required_if:payment_method,'.PaymentMethod::Debt->value],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'items.required' => "Savatcha bo'sh.",
            'items.min' => "Savatcha bo'sh.",
            'items.*.product_id.distinct' => 'Bir mahsulot savatchada ikki marta bo\'lishi mumkin emas.',
        ];
    }
}
