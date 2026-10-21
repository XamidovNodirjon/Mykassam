<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Encrypted template variables, so an undelivered message can be sent again.
     */
    public function up(): void
    {
        Schema::table('sms_messages', function (Blueprint $table) {
            $table->text('params')->nullable()->after('purpose');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sms_messages', function (Blueprint $table) {
            $table->dropColumn('params');
        });
    }
};
