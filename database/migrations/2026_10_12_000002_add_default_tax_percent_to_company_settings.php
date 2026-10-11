<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('company_settings', function (Blueprint $table) {
            $table->decimal('default_tax_percent', 5, 2)->default(18)->after('gstin');
        });
    }

    public function down()
    {
        Schema::table('company_settings', function (Blueprint $table) {
            $table->dropColumn('default_tax_percent');
        });
    }
};
