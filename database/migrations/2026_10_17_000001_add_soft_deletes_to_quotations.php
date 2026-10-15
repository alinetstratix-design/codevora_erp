<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Quotations are business records: they are never physically removed.
 * "Delete" hides a quotation from every screen and keeps who/when for the audit trail.
 * The quotation number stays reserved, so it is never issued again.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('quotations', function (Blueprint $table) {
            $table->softDeletes();
            $table->unsignedBigInteger('deleted_by')->nullable()->after('deleted_at');
        });
    }

    public function down(): void
    {
        Schema::table('quotations', function (Blueprint $table) {
            $table->dropColumn(['deleted_by', 'deleted_at']);
        });
    }
};
