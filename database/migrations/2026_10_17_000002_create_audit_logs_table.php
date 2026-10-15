<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Append-only activity trail for a company (who did what, and when).
 * Rows are never updated or deleted by the application.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id')->nullable()->index();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('user_name', 120)->nullable();   // snapshot: stays readable if the user is removed
            $table->string('user_role', 30)->nullable();
            $table->string('action', 40);
            $table->string('subject_type', 40)->nullable();
            $table->unsignedBigInteger('subject_id')->nullable();
            $table->string('subject_label', 190)->nullable(); // e.g. quotation number
            $table->json('details')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['company_id', 'created_at']);
            $table->index(['subject_type', 'subject_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
