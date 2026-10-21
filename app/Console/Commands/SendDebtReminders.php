<?php

namespace App\Console\Commands;

use App\Services\DebtService;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('debts:remind')]
#[Description('SMS customers whose debt is due today')]
class SendDebtReminders extends Command
{
    public function handle(DebtService $debts): int
    {
        $this->info("Eslatmalar yuborildi: {$debts->sendDueReminders()} ta.");

        return self::SUCCESS;
    }
}
