<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('products', function (Blueprint $table) {
            $table->decimal('profile_weight_per_mtr', 10, 3)->nullable()->default(0)->after('base_rate');
            $table->decimal('profile_rate_per_kg', 10, 2)->nullable()->default(0)->after('profile_weight_per_mtr');
            $table->decimal('glass_rate_per_sqft', 10, 2)->nullable()->default(0)->after('profile_rate_per_kg');
            $table->decimal('hardware_kit_cost', 10, 2)->nullable()->default(0)->after('glass_rate_per_sqft');
            $table->decimal('wastage_percent', 5, 2)->nullable()->default(10)->after('hardware_kit_cost');
            $table->decimal('profit_margin_percent', 5, 2)->nullable()->default(20)->after('wastage_percent');
            $table->decimal('labor_rate_per_sqft', 10, 2)->nullable()->default(0)->after('profit_margin_percent');
            $table->string('profile_calc_formula')->nullable()->default('PERIMETER')->after('labor_rate_per_sqft');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn([
                'profile_weight_per_mtr',
                'profile_rate_per_kg',
                'glass_rate_per_sqft',
                'hardware_kit_cost',
                'wastage_percent',
                'profit_margin_percent',
                'labor_rate_per_sqft',
                'profile_calc_formula'
            ]);
        });
    }
};
