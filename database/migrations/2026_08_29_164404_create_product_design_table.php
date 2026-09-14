<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('product_design', function (Blueprint $table) {
            $table->unsignedBigInteger('product_id');
            $table->unsignedBigInteger('design_id');

            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
            $table->foreign('design_id')->references('id')->on('designs')->onDelete('cascade');
            
            $table->primary(['product_id', 'design_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('product_design');
    }
};
