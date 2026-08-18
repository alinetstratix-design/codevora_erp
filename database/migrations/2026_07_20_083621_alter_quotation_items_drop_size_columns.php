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
        Schema::table('quotation_items', function (Blueprint $table) {
            // $table->dropColumn(['width', 'height', 'unit', 'quantity', 'area']);
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
            $table->decimal('width', 10, 3)->default(0);
            $table->decimal('height', 10, 3)->default(0);
            $table->string('unit', 50)->nullable()->default('mm');
            $table->integer('quantity')->default(1);
            $table->decimal('area', 12, 3)->default(0);
        });
    }
};
