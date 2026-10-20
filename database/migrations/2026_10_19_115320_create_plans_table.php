<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Plans move from a hard-coded enum to a table the administrator manages.
     */
    public function up(): void
    {
        Schema::create('plans', function (Blueprint $table) {
            $table->id();
            $table->string('slug', 50)->unique();
            $table->string('name', 100);
            $table->unsignedBigInteger('price');
            $table->unsignedInteger('max_sellers')->nullable();
            $table->unsignedInteger('max_products')->nullable();
            $table->json('features');
            $table->boolean('is_active')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });

        $now = now();

        DB::table('plans')->insert([
            ['slug' => 'start', 'name' => 'Start', 'price' => 99_000, 'max_sellers' => 1, 'max_products' => 300, 'features' => '[]', 'sort_order' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['slug' => 'business', 'name' => 'Biznes', 'price' => 199_000, 'max_sellers' => 5, 'max_products' => null, 'features' => '["customers","product_import","stock_control"]', 'sort_order' => 2, 'created_at' => $now, 'updated_at' => $now],
            ['slug' => 'business-plus', 'name' => 'Biznes Plus', 'price' => 349_000, 'max_sellers' => null, 'max_products' => null, 'features' => '["customers","debts","product_import","stock_control"]', 'sort_order' => 3, 'created_at' => $now, 'updated_at' => $now],
        ]);

        Schema::table('shops', function (Blueprint $table) {
            $table->foreignId('plan_id')->nullable()->after('is_active')->constrained()->restrictOnDelete();
        });

        $planIds = DB::table('plans')->pluck('id', 'slug');

        foreach (['start' => 'start', 'business' => 'business', 'business_plus' => 'business-plus'] as $oldPlan => $slug) {
            DB::table('shops')->where('plan', $oldPlan)->update(['plan_id' => $planIds[$slug]]);
        }

        Schema::table('shops', function (Blueprint $table) {
            $table->dropColumn('plan');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('shops', function (Blueprint $table) {
            $table->string('plan', 20)->default('business_plus')->after('is_active');
        });

        foreach (DB::table('plans')->pluck('slug', 'id') as $id => $slug) {
            DB::table('shops')->where('plan_id', $id)->update(['plan' => str_replace('-', '_', $slug)]);
        }

        Schema::table('shops', function (Blueprint $table) {
            $table->dropConstrainedForeignId('plan_id');
        });

        Schema::dropIfExists('plans');
    }
};
