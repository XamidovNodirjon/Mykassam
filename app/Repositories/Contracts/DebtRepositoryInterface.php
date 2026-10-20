<?php

namespace App\Repositories\Contracts;

use App\Enums\DebtStatus;
use App\Models\Debt;
use App\Models\Shop;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface DebtRepositoryInterface
{
    /**
     * Debts with their customer, the soonest due first.
     *
     * @return LengthAwarePaginator<int, Debt>
     */
    public function paginateForShop(Shop $shop, ?DebtStatus $status, ?string $search, int $perPage = 20): LengthAwarePaginator;

    /**
     * @return array{outstanding: int, open: int, overdue: int}
     */
    public function summaryForShop(Shop $shop): array;

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function create(array $attributes): Debt;

    /**
     * Re-read the debt with a row lock so concurrent payments cannot overpay it.
     */
    public function lockForPayment(Debt $debt): Debt;

    public function addPayment(Debt $debt, int $amount, ?int $userId): void;

    /**
     * Unpaid debts due today that have not been reminded yet, with their customer and shop plan.
     *
     * @return Collection<int, Debt>
     */
    public function dueForReminder(): Collection;

    public function markReminded(Debt $debt): void;

    /**
     * Unpaid amount over all shops.
     */
    public function outstandingTotal(): int;
}
