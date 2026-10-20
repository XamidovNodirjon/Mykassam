<?php

namespace App\Http\Requests\Owner;

use App\Models\Shop;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * A debt written into the book by hand (goods given on credit outside the POS).
 */
class DebtRequest extends FormRequest
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
        /** @var Shop $shop */
        $shop = $this->route('shop');

        return [
            'customer_id' => ['required', 'integer', Rule::exists('customers', 'id')->where('shop_id', $shop->id)],
            'amount' => ['required', 'integer', 'min:1', 'max:1000000000000'],
            'due_date' => ['required', 'date', 'after_or_equal:today'],
            'note' => ['nullable', 'string', 'max:255'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'customer_id' => 'mijoz',
            'amount' => 'summa',
            'due_date' => "to'lov sanasi",
            'note' => 'izoh',
        ];
    }
}
