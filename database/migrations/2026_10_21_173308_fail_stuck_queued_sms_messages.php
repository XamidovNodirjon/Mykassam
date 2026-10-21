<?php

use App\Enums\SmsStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * SMS used to wait in a queue that nothing processed, so some never left "pending". They are
     * sent right away now: the stuck ones are marked failed, and their queued jobs are dropped so a
     * stray worker can never deliver an outdated code.
     */
    public function up(): void
    {
        DB::table('sms_messages')
            ->where('status', SmsStatus::Pending->value)
            ->update([
                'status' => SmsStatus::Failed->value,
                'error' => 'Navbat ishlamagani sababli yuborilmagan.',
                'updated_at' => now(),
            ]);

        DB::table('jobs')->where('payload', 'like', '%SendSmsJob%')->delete();
    }

    /**
     * Messages that were never delivered cannot be put back in the queue.
     */
    public function down(): void
    {
        //
    }
};
