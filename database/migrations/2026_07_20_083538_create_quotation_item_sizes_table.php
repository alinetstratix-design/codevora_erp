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
        Schema::create('quotation_item_sizes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quotation_item_id')->constrained('quotation_items')->onDelete('cascade');
            $table->decimal('width', 10, 3)->default(0);
            $table->decimal('height', 10, 3)->default(0);
            $table->string('unit', 50)->nullable()->default('mm');
            $table->integer('quantity')->default(1);
            $table->decimal('area', 12, 3)->default(0);
            $table->decimal('amount', 15, 2)->default(0);
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
        Schema::dropIfExists('quotation_item_sizes');
    }
};
