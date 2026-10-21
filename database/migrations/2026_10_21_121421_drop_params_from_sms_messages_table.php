<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Resending moved out of the admin panel (the person registering asks for a new code), so
     * message variables are no longer kept.
     */
    public function up(): void
    {
        Schema::table('sms_messages', function (Blueprint $table) {
            $table->dropColumn('params');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sms_messages', function (Blueprint $table) {
            $table->text('params')->nullable()->after('purpose');
        });
    }
};
