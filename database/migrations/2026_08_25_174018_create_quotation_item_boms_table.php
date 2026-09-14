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
        Schema::create('quotation_item_boms', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('quotation_item_id');
            $table->unsignedBigInteger('material_id');
            $table->string('rule_type'); // e.g. PERIMETER_MULTIPLIER
            $table->string('material_sku')->nullable();
            $table->string('material_name');
            $table->decimal('unit_cost', 10, 2);
            $table->decimal('calculated_qty', 10, 4);
            $table->decimal('total_cost', 10, 2);
            $table->json('calculation_snapshot')->nullable(); // Freeze of the exact formula/variables used
            $table->timestamps();

            $table->foreign('quotation_item_id')->references('id')->on('quotation_items')->onDelete('cascade');
            $table->foreign('material_id')->references('id')->on('materials')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('quotation_item_boms');
    }
};
