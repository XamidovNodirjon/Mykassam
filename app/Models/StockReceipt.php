<?php

namespace App\Models;

use Database\Factories\StockReceiptFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Goods received into a shop's stock. The product name is copied so history survives deletion.
 *
 * @property int $id
 * @property int $shop_id
 * @property int|null $product_id
 * @property string $product_name
 * @property int|null $user_id
 * @property int $quantity
 * @property int $cost_price
 * @property Carbon $created_at
 * @property Carbon|null $updated_at
 * @property-read User|null $user
 */
#[Fillable(['shop_id', 'product_id', 'product_name', 'user_id', 'quantity', 'cost_price'])]
class StockReceipt extends Model
{
    /** @use HasFactory<StockReceiptFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'cost_price' => 'integer',
        ];
    }

    /**
     * @return BelongsTo<Product, $this>
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
