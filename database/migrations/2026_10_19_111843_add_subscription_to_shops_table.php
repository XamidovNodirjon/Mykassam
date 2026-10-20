<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('shops', function (Blueprint $table) {
            $table->string('plan', 20)->default('business_plus')->after('is_active');
            $table->boolean('on_trial')->default(true)->after('plan');
            $table->timestamp('subscription_ends_at')->nullable()->index()->after('on_trial');
        });

        // Shops that existed before subscriptions start a fresh trial.
        DB::table('shops')->update(['subscription_ends_at' => now()->addMonthsNoOverflow(config('subscription.trial_months'))]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('shops', function (Blueprint $table) {
            $table->dropIndex(['subscription_ends_at']);
            $table->dropColumn(['plan', 'on_trial', 'subscription_ends_at']);
        });
    }
};
