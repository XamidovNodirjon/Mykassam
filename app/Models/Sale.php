<?php

namespace App\Models;

use App\Enums\PaymentMethod;
use Database\Factories\SaleFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $shop_id
 * @property int $seller_id
 * @property int|null $customer_id
 * @property int $total_amount Amount charged: goods total minus the bonus used.
 * @property int $total_cost
 * @property int $profit
 * @property int $items_count
 * @property PaymentMethod $payment_method
 * @property int $bonus_used
 * @property int $bonus_earned
 * @property Carbon $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['shop_id', 'seller_id', 'customer_id', 'total_amount', 'total_cost', 'profit', 'items_count', 'payment_method', 'bonus_used', 'bonus_earned'])]
class Sale extends Model
{
    /** @use HasFactory<SaleFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'total_amount' => 'integer',
            'total_cost' => 'integer',
            'profit' => 'integer',
            'items_count' => 'integer',
            'payment_method' => PaymentMethod::class,
            'bonus_used' => 'integer',
            'bonus_earned' => 'integer',
        ];
    }

    /**
     * @return BelongsTo<Shop, $this>
     */
    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function seller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    /**
     * @return BelongsTo<Customer, $this>
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * @return HasMany<SaleItem, $this>
     */
    public function items(): HasMany
    {
        return $this->hasMany(SaleItem::class);
    }
}
