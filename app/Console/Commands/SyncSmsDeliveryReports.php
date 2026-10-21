<?php

namespace App\Console\Commands;

use App\Services\SmsService;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('sms:sync-delivery')]
#[Description('Record whether recently sent SMS reached the phone')]
class SyncSmsDeliveryReports extends Command
{
    public function handle(SmsService $sms): int
    {
        $this->info("Holati yangilangan SMS'lar: {$sms->syncDeliveryReports()} ta.");

        return self::SUCCESS;
    }
}
