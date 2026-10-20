<?php

namespace App\Http\Controllers\Owner;

use App\DTOs\DebtData;
use App\Enums\DebtStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Owner\DebtPaymentRequest;
use App\Http\Requests\Owner\DebtRequest;
use App\Http\Resources\CustomerResource;
use App\Http\Resources\DebtResource;
use App\Http\Resources\ShopResource;
use App\Models\Debt;
use App\Models\Shop;
use App\Services\CustomerService;
use App\Services\DebtService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DebtController extends Controller
{
    public function __construct(private DebtService $debts) {}

    public function index(Request $request, Shop $shop, CustomerService $customers): Response
    {
        $search = $request->string('search')->trim()->toString() ?: null;
        $status = $request->enum('status', DebtStatus::class);

        return Inertia::render('Owner/Debts/Index', [
            'shop' => ShopResource::make($shop->loadMissing('plan')),
            'debts' => DebtResource::collection($this->debts->paginate($shop, $status, $search)),
            'summary' => $this->debts->summary($shop),
            'customers' => Inertia::optional(fn () => CustomerResource::collection($customers->options($shop))),
            'filters' => ['search' => $search, 'status' => $status?->value],
        ]);
    }

    public function store(DebtRequest $request, Shop $shop): RedirectResponse
    {
        $this->debts->create($shop, DebtData::fromRequest($request));

        $this->toast('Qarz yozildi.');

        return back();
    }

    /**
     * Record a repayment of the debt.
     */
    public function pay(DebtPaymentRequest $request, Shop $shop, Debt $debt): RedirectResponse
    {
        $debt = $this->debts->recordPayment($debt, $request->integer('amount'), $request->user());

        $this->toast($debt->remaining() > 0
            ? sprintf("To'lov qabul qilindi. Qolgan qarz: %s so'm.", number_format($debt->remaining(), 0, '.', ' '))
            : "Qarz to'liq yopildi.");

        return back();
    }
}
