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
        Schema::table('quotations', function (Blueprint $table) {
            $table->decimal('bom_cost_total', 15, 2)->default(0)->after('grand_total');
            $table->decimal('additional_cost_total', 15, 2)->default(0)->after('bom_cost_total');
            $table->decimal('cost_basis_total', 15, 2)->default(0)->after('additional_cost_total');
            $table->decimal('margin_total', 15, 2)->default(0)->after('cost_basis_total');
            $table->decimal('taxable_amount', 15, 2)->default(0)->after('margin_total');
        });

        Schema::table('quotation_items', function (Blueprint $table) {
            $table->decimal('bom_cost', 15, 2)->default(0)->after('amount');
            $table->decimal('additional_cost', 15, 2)->default(0)->after('bom_cost');
            $table->decimal('cost_basis', 15, 2)->default(0)->after('additional_cost');
            $table->decimal('margin_percent', 5, 2)->default(0)->after('cost_basis');
            $table->decimal('margin_amount', 15, 2)->default(0)->after('margin_percent');
            $table->decimal('line_total', 15, 2)->default(0)->after('margin_amount');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('quotation_items', function (Blueprint $table) {
            $table->dropColumn([
                'bom_cost',
                'additional_cost',
                'cost_basis',
                'margin_percent',
                'margin_amount',
                'line_total'
            ]);
        });

        Schema::table('quotations', function (Blueprint $table) {
            $table->dropColumn([
                'bom_cost_total',
                'additional_cost_total',
                'cost_basis_total',
                'margin_total',
                'taxable_amount'
            ]);
        });
    }
};
