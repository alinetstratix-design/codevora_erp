<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('bom_rules', function (Blueprint $table) {
            $table->unsignedBigInteger('design_id')->nullable()->after('product_id');
            $table->foreign('design_id')->references('id')->on('designs')->onDelete('cascade');
        });

        Schema::table('quotation_items', function (Blueprint $table) {
            $table->unsignedBigInteger('design_id')->nullable()->after('product_id');
            $table->foreign('design_id')->references('id')->on('designs')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::table('bom_rules', function (Blueprint $table) {
            $table->dropForeign(['design_id']);
            $table->dropColumn('design_id');
        });

        Schema::table('quotation_items', function (Blueprint $table) {
            $table->dropForeign(['design_id']);
            $table->dropColumn('design_id');
        });
    }
};
