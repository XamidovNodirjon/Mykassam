<?php

namespace App\Http\Requests\Owner;

use App\Models\Product;
use App\Models\Shop;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Used for both creating and updating a product.
 */
class ProductRequest extends FormRequest
{
    private const MAX_PRICE = 1_000_000_000_000;

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
        /** @var Product|null $product */
        $product = $this->route('product');

        return [
            'name' => ['required', 'string', 'max:255'],
            'barcode' => [
                'nullable',
                'string',
                'max:64',
                Rule::unique('products', 'barcode')->where('shop_id', $shop->id)->ignore($product),
            ],
            'cost_price' => ['required', 'integer', 'min:0', 'max:'.self::MAX_PRICE],
            'min_price' => ['required', 'integer', 'min:0', 'max:'.self::MAX_PRICE],
            'price' => ['required', 'integer', 'min:0', 'max:'.self::MAX_PRICE, 'gte:min_price'],
            'stock' => ['required', 'integer', 'min:0', 'max:10000000'],
            'min_stock' => ['nullable', 'integer', 'min:0', 'max:10000000'],
            'is_active' => ['boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'price.gte' => "Sotuv narxi minimal narxdan kam bo'lishi mumkin emas.",
            'barcode.unique' => "Bu shtrix-kod do'konda allaqachon mavjud.",
        ];
    }
}
