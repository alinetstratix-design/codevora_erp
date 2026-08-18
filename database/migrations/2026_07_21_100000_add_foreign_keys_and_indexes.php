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
        // Add indexes to quotations table for faster search
        Schema::table('quotations', function (Blueprint $table) {
            $quoteColumn = Schema::hasColumn('quotations', 'quotation_number') ? 'quotation_number' : 'quote_no';
            $table->index($quoteColumn);
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('quotations', function (Blueprint $table) {
            $quoteColumn = Schema::hasColumn('quotations', 'quotation_number') ? 'quotation_number' : 'quote_no';
            $table->dropIndex([$quoteColumn]);
            $table->dropIndex(['status']);
        });
    }
};
