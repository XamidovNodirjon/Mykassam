<?php

namespace App\Models;

use App\Enums\DebtStatus;
use Database\Factories\DebtFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * Goods taken on credit (nasiya), paid off in one or more payments.
 *
 * @property int $id
 * @property int $shop_id
 * @property int $customer_id
 * @property int|null $sale_id
 * @property int $amount
 * @property int $paid_amount
 * @property Carbon $due_date
 * @property string|null $note
 * @property Carbon|null $reminded_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Customer $customer
 * @property-read Shop $shop
 */
#[Fillable(['shop_id', 'customer_id', 'sale_id', 'amount', 'paid_amount', 'due_date', 'note', 'reminded_at'])]
class Debt extends Model
{
    /** @use HasFactory<DebtFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'amount' => 'integer',
            'paid_amount' => 'integer',
            'due_date' => 'date',
            'reminded_at' => 'datetime',
        ];
    }

    public function remaining(): int
    {
        return $this->amount - $this->paid_amount;
    }

    public function status(): DebtStatus
    {
        if ($this->remaining() <= 0) {
            return DebtStatus::Paid;
        }

        return $this->due_date->isBefore(today()) ? DebtStatus::Overdue : DebtStatus::Open;
    }

    /**
     * @param  Builder<self>  $query
     */
    #[Scope]
    protected function unpaid(Builder $query): void
    {
        $query->whereColumn('paid_amount', '<', 'amount');
    }

    /**
     * @param  Builder<self>  $query
     */
    #[Scope]
    protected function withStatus(Builder $query, DebtStatus $status): void
    {
        match ($status) {
            DebtStatus::Paid => $query->whereColumn('paid_amount', '>=', 'amount'),
            DebtStatus::Overdue => $query->unpaid()->whereDate('due_date', '<', today()),
            DebtStatus::Open => $query->unpaid()->whereDate('due_date', '>=', today()),
        };
    }

    /**
     * @return BelongsTo<Shop, $this>
     */
    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }

    /**
     * @return BelongsTo<Customer, $this>
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * @return HasMany<DebtPayment, $this>
     */
    public function payments(): HasMany
    {
        return $this->hasMany(DebtPayment::class);
    }
}
