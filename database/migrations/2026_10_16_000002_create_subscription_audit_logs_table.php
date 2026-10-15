<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subscription_audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('action', 40);
            $table->json('before')->nullable();
            $table->json('after')->nullable();
            $table->string('note')->nullable();
            $table->timestamps();

            $table->index(['company_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscription_audit_logs');
    }
};
