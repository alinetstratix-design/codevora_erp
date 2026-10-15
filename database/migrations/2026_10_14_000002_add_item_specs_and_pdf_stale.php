<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('company_settings', function (Blueprint $table) {
            $table->json('item_specs')->nullable();
            $table->json('inclusions')->nullable();
            $table->json('exclusions')->nullable();
        });

        Schema::table('quotations', function (Blueprint $table) {
            $table->boolean('pdf_stale')->default(false);
        });
    }

    public function down()
    {
        Schema::table('quotations', function (Blueprint $table) {
            $table->dropColumn('pdf_stale');
        });

        Schema::table('company_settings', function (Blueprint $table) {
            $table->dropColumn(['item_specs', 'inclusions', 'exclusions']);
        });
    }
};
