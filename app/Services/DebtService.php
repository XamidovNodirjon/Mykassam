<?php

namespace App\Services;

use App\DTOs\DebtData;
use App\Enums\DebtStatus;
use App\Enums\PlanFeature;
use App\Enums\SmsPurpose;
use App\Exceptions\SmsGatewayException;
use App\Models\Customer;
use App\Models\Debt;
use App\Models\Sale;
use App\Models\Shop;
use App\Models\User;
use App\Repositories\Contracts\DebtRepositoryInterface;
use Carbon\CarbonInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Debt book (nasiya): credit sales, repayments and due-date SMS reminders.
 */
class DebtService
{
    public function __construct(
        private DebtRepositoryInterface $debts,
        private SmsService $sms,
    ) {}

    /**
     * @return LengthAwarePaginator<int, Debt>
     */
    public function paginate(Shop $shop, ?DebtStatus $status, ?string $search): LengthAwarePaginator
    {
        return $this->debts->paginateForShop($shop, $status, $search);
    }

    /**
     * @return array{outstanding: int, open: int, overdue: int}
     */
    public function summary(Shop $shop): array
    {
        return $this->debts->summaryForShop($shop);
    }

    public function create(Shop $shop, DebtData $data): Debt
    {
        return $this->debts->create([
            'shop_id' => $shop->id,
            'customer_id' => $data->customerId,
            'amount' => $data->amount,
            'due_date' => $data->dueDate,
            'note' => $data->note,
        ]);
    }

    /**
     * Book a credit sale. Runs inside the sale's transaction.
     */
    public function openForSale(Sale $sale, Customer $customer, CarbonInterface $dueDate): Debt
    {
        return $this->debts->create([
            'shop_id' => $sale->shop_id,
            'customer_id' => $customer->id,
            'sale_id' => $sale->id,
            'amount' => $sale->total_amount,
            'due_date' => $dueDate,
            'note' => "Savdo #{$sale->id}",
        ]);
    }

    /**
     * @throws ValidationException When the payment is larger than what is still owed.
     */
    public function recordPayment(Debt $debt, int $amount, User $user): Debt
    {
        return DB::transaction(function () use ($debt, $amount, $user): Debt {
            $debt = $this->debts->lockForPayment($debt);

            if ($amount > $debt->remaining()) {
                throw ValidationException::withMessages([
                    'amount' => sprintf("Qolgan qarz %s so'm, undan ko'p to'lab bo'lmaydi.", number_format($debt->remaining(), 0, '.', ' ')),
                ]);
            }

            $this->debts->addPayment($debt, $amount, $user->id);

            return $debt;
        });
    }

    /**
     * SMS every customer whose debt is due today, once per debt. Shops whose subscription
     * has ended or whose plan has no debt book are skipped.
     *
     * A reminder the provider rejects is logged as failed and skipped; the rest still go out.
     *
     * @return int Number of reminders sent.
     */
    public function sendDueReminders(): int
    {
        $sent = 0;

        foreach ($this->debts->dueForReminder() as $debt) {
            if ($debt->customer->phone === null
                || ! $debt->shop->hasActiveSubscription()
                || ! $debt->shop->hasFeature(PlanFeature::Debts)) {
                continue;
            }

            try {
                $this->sms->send($debt->customer->phone, SmsPurpose::DebtReminder, [
                    'name' => $debt->customer->name,
                    'shop' => $debt->shop->name,
                    'amount' => number_format($debt->remaining(), 0, '.', ' '),
                ]);
            } catch (SmsGatewayException $exception) {
                report($exception);

                continue;
            }

            $this->debts->markReminded($debt);
            $sent++;
        }

        return $sent;
    }
}
