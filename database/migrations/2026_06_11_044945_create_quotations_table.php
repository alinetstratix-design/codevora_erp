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
        Schema::create('quotations', function (Blueprint $table) {
            $table->id();
            $table->string('client_name');
            $table->string('project_name')->nullable();
            $table->string('quote_no');
            $table->string('status')->default('Draft');
            $table->date('date');
            $table->date('valid_till')->nullable();
            $table->string('opportunity_no')->nullable();
            $table->text('address')->nullable();
            
            // Cost breakdowns
            $table->decimal('material_value', 15, 2)->default(0);
            $table->decimal('net_value', 15, 2)->default(0);
            $table->decimal('total_glass_cost', 15, 2)->default(0);
            $table->decimal('installation_cost_rate', 15, 2)->default(0);
            $table->decimal('freight_charges_rate', 15, 2)->default(0);
            $table->decimal('installation_cost', 15, 2)->default(0);
            $table->decimal('freight_charges', 15, 2)->default(0);
            
            // Tax and additional fields
            $table->decimal('subtotal', 15, 2)->default(0);
            $table->decimal('discount_percent', 5, 2)->default(0);
            $table->decimal('tax_percent', 5, 2)->default(18);
            $table->decimal('tax_amount', 15, 2)->default(0);
            $table->decimal('additional_charges', 15, 2)->default(0);
            $table->decimal('grand_total', 15, 2)->default(0);
            
            // Metrics
            $table->decimal('total_sqmt', 12, 3)->default(0);
            $table->integer('total_units')->default(0);
            
            // Text JSONs
            $table->text('terms_conditions')->nullable();
            $table->text('bank_details')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('quotations');
    }
};
