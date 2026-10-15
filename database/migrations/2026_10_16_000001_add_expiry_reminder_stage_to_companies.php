<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            // Last reminder sent for the current expiry: 7d, 1d, expired. Cleared when the expiry changes.
            $table->string('expiry_reminder_stage', 16)->nullable()->after('max_users');
        });
    }

    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn('expiry_reminder_stage');
        });
    }
};
