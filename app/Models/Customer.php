<?php

namespace App\Models;

use Database\Factories\CustomerFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * A shop's regular customer: collects cashback bonus and may buy on credit.
 *
 * @property int $id
 * @property int $shop_id
 * @property string $name
 * @property string|null $phone
 * @property int $bonus_balance
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read int|string|null $debt_amount Aggregate selected with withSum().
 * @property-read int|string|null $debt_paid Aggregate selected with withSum().
 */
#[Fillable(['shop_id', 'name', 'phone', 'bonus_balance'])]
class Customer extends Model
{
    /** @use HasFactory<CustomerFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'bonus_balance' => 'integer',
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
     * @return HasMany<Debt, $this>
     */
    public function debts(): HasMany
    {
        return $this->hasMany(Debt::class);
    }
}
