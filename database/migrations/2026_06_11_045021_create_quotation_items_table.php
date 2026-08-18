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
        Schema::create('quotation_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quotation_id')->constrained('quotations')->cascadeOnDelete();
            $table->string('position');
            $table->integer('qty');
            $table->string('system_name');
            $table->decimal('dimension_w', 10, 2);
            $table->decimal('dimension_h', 10, 2);
            $table->decimal('area', 12, 3);
            $table->string('profile_color');
            $table->string('handle_type');
            $table->string('hardware_color');
            $table->string('mesh_type');
            $table->string('glass_type');
            $table->text('remarks')->nullable();
            $table->string('drawing_type')->default('fixed'); // fixed, slider_2, slider_3, slider_3_transom, door, custom
            $table->string('drawing_url')->nullable();
            
            // Core cost and pricing fields
            $table->string('unit')->default('Nos');
            $table->decimal('rate', 15, 2)->default(0);
            $table->decimal('material_cost', 15, 2)->default(0);
            $table->decimal('glass_cost', 15, 2)->default(0);
            $table->decimal('total_cost', 15, 2);
            
            // Expanded technical specifications as JSON text
            $table->text('technical_specs')->nullable();
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
        Schema::dropIfExists('quotation_items');
    }
};
