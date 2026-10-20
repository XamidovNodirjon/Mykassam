<?php

use Carbon\CarbonImmutable;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * The free trial grew from 14 days to a month: shops still on the trial get the
     * longer period, counted from the owner's sign-up like new shops.
     */
    public function up(): void
    {
        DB::table('shops')
            ->join('users', 'users.id', '=', 'shops.owner_id')
            ->where('shops.on_trial', true)
            ->select(['shops.id', 'shops.subscription_ends_at', 'users.created_at as owner_created_at'])
            ->orderBy('shops.id')
            ->each(function (object $shop): void {
                $trialEndsAt = CarbonImmutable::parse($shop->owner_created_at)
                    ->addMonthsNoOverflow(config('subscription.trial_months'));

                if ($shop->subscription_ends_at === null || $trialEndsAt->greaterThan($shop->subscription_ends_at)) {
                    DB::table('shops')->where('id', $shop->id)->update(['subscription_ends_at' => $trialEndsAt]);
                }
            });
    }

    /**
     * The longer trial is not taken back.
     */
    public function down(): void
    {
        //
    }
};
