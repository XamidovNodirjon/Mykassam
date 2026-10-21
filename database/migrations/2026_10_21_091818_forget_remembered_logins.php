<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * "Remember me" was dropped: sign-ins now end with the session. Clearing the tokens voids the
     * long-lived remember cookies browsers still hold, so those users are asked to sign in again.
     */
    public function up(): void
    {
        DB::table('users')->whereNotNull('remember_token')->update(['remember_token' => null]);
    }

    /**
     * Voided tokens cannot be restored.
     */
    public function down(): void
    {
        //
    }
};
