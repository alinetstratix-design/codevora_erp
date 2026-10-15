<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            if (!Schema::hasColumn('companies', 'subscription_status')) {
                $table->string('subscription_status', 32)->default('Trial')->after('is_active');
            }
            if (!Schema::hasColumn('companies', 'trial_ends_at')) {
                $table->timestamp('trial_ends_at')->nullable()->after('subscription_status');
            }
            if (!Schema::hasColumn('companies', 'subscription_ends_at')) {
                $table->timestamp('subscription_ends_at')->nullable()->after('trial_ends_at');
            }
            if (!Schema::hasColumn('companies', 'max_users')) {
                $table->integer('max_users')->default(3)->after('subscription_ends_at');
            }
        });

        Schema::create('demo_requests', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('company_name');
            $table->string('email');
            $table->string('phone', 32);
            $table->string('location')->nullable();
            $table->string('fabrication_type', 64)->nullable(); // uPVC, Aluminium, Both, Other
            $table->string('status', 32)->default('pending'); // pending, approved, rejected
            $table->foreignId('company_id')->nullable()->constrained('companies')->nullOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('admin_notes')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();

            $table->index('status');
            $table->index('email');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('demo_requests');

        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn(['subscription_status', 'trial_ends_at', 'subscription_ends_at', 'max_users']);
        });
    }
};
