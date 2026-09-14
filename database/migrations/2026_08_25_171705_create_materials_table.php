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
        Schema::create('materials', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->nullable()->constrained('companies')->cascadeOnDelete();
            $table->string('sku')->unique()->nullable();
            $table->string('name');
            $table->string('category')->default('Accessory'); // Profile, Glass, Hardware, Accessory
            $table->string('uom')->default('Nos'); // Mtr, SqFt, Kg, Pcs, Nos
            $table->decimal('cost', 12, 2)->default(0);
            $table->decimal('waste_percent', 5, 2)->default(0);
            $table->decimal('weight_per_mtr', 10, 3)->nullable();
            $table->decimal('stock_length', 10, 2)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('materials');
    }
};
