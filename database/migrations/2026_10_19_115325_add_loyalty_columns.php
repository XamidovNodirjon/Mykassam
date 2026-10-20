<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Cashback: a shop gives a share of each paid sale back to the customer as bonus.
     */
    public function up(): void
    {
        Schema::table('shops', function (Blueprint $table) {
            $table->unsignedTinyInteger('cashback_percent')->default(0)->after('phone');
        });

        Schema::table('sales', function (Blueprint $table) {
            $table->foreignId('customer_id')->nullable()->after('seller_id')->constrained()->nullOnDelete();
            $table->unsignedBigInteger('bonus_used')->default(0)->after('payment_method');
            $table->unsignedBigInteger('bonus_earned')->default(0)->after('bonus_used');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropConstrainedForeignId('customer_id');
            $table->dropColumn(['bonus_used', 'bonus_earned']);
        });

        Schema::table('shops', function (Blueprint $table) {
            $table->dropColumn('cashback_percent');
        });
    }
};
