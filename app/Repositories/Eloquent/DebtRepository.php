<?php

namespace App\Repositories\Eloquent;

use App\Enums\DebtStatus;
use App\Models\Debt;
use App\Models\Shop;
use App\Repositories\Contracts\DebtRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class DebtRepository implements DebtRepositoryInterface
{
    public function paginateForShop(Shop $shop, ?DebtStatus $status, ?string $search, int $perPage = 20): LengthAwarePaginator
    {
        return $shop->debts()
            ->with('customer:id,name,phone')
            ->when($status, fn (Builder $query, DebtStatus $status) => $query->withStatus($status))
            ->when($search, fn (Builder $query, string $search) => $query->whereHas(
                'customer',
                fn (Builder $query) => $query->where(fn (Builder $query) => $query
                    ->where('name', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")),
            ))
            ->orderByRaw('paid_amount >= amount')
            ->orderBy('due_date')
            ->orderBy('id')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function summaryForShop(Shop $shop): array
    {
        return [
            'outstanding' => (int) $shop->debts()->unpaid()->sum(DB::raw('amount - paid_amount')),
            'open' => $shop->debts()->withStatus(DebtStatus::Open)->count(),
            'overdue' => $shop->debts()->withStatus(DebtStatus::Overdue)->count(),
        ];
    }

    public function create(array $attributes): Debt
    {
        return Debt::query()->create($attributes);
    }

    public function lockForPayment(Debt $debt): Debt
    {
        return Debt::query()->whereKey($debt->id)->lockForUpdate()->firstOrFail();
    }

    public function addPayment(Debt $debt, int $amount, ?int $userId): void
    {
        $debt->payments()->create(['amount' => $amount, 'user_id' => $userId]);
        $debt->increment('paid_amount', $amount);
    }

    public function dueForReminder(): Collection
    {
        return Debt::query()
            ->with(['customer:id,name,phone', 'shop:id,name,plan_id,on_trial,subscription_ends_at', 'shop.plan'])
            ->unpaid()
            ->whereDate('due_date', today())
            ->whereNull('reminded_at')
            ->get();
    }

    public function markReminded(Debt $debt): void
    {
        $debt->update(['reminded_at' => now()]);
    }

    public function outstandingTotal(): int
    {
        return (int) Debt::query()->unpaid()->sum(DB::raw('amount - paid_amount'));
    }
}
