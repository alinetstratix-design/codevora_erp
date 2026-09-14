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
        Schema::table('materials', function (Blueprint $table) {
            $table->json('properties')->nullable()->after('is_active');
        });

        Schema::table('bom_rules', function (Blueprint $table) {
            $table->string('component_role')->nullable()->after('material_id');
            $table->json('rule_definition')->nullable()->after('component_role');
            $table->json('condition_definition')->nullable()->after('rule_definition');
            $table->string('unit')->nullable()->after('condition_definition');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('materials', function (Blueprint $table) {
            $table->dropColumn('properties');
        });

        Schema::table('bom_rules', function (Blueprint $table) {
            $table->dropColumn('component_role');
        });
        Schema::table('bom_rules', function (Blueprint $table) {
            $table->dropColumn('rule_definition');
        });
        Schema::table('bom_rules', function (Blueprint $table) {
            $table->dropColumn('condition_definition');
        });
        Schema::table('bom_rules', function (Blueprint $table) {
            $table->dropColumn('unit');
        });
    }
};
