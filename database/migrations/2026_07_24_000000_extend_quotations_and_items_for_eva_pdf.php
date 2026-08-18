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
        Schema::table('quotations', function (Blueprint $table) {
            if (!Schema::hasColumn('quotations', 'no_of_components')) {
                $table->integer('no_of_components')->default(0)->nullable();
            }
            if (!Schema::hasColumn('quotations', 'total_area_sqft')) {
                $table->decimal('total_area_sqft', 10, 3)->default(0)->nullable();
            }
            if (!Schema::hasColumn('quotations', 'basic_value')) {
                $table->decimal('basic_value', 12, 2)->default(0)->nullable();
            }
            if (!Schema::hasColumn('quotations', 'total_project_cost')) {
                $table->decimal('total_project_cost', 12, 2)->default(0)->nullable();
            }
            if (!Schema::hasColumn('quotations', 'avg_price_sqft_ex_gst')) {
                $table->decimal('avg_price_sqft_ex_gst', 10, 2)->default(0)->nullable();
            }
            if (!Schema::hasColumn('quotations', 'avg_price_sqft_inc_gst')) {
                $table->decimal('avg_price_sqft_inc_gst', 10, 2)->default(0)->nullable();
            }
            if (!Schema::hasColumn('quotations', 'cover_letter_enclosures')) {
                $table->json('cover_letter_enclosures')->nullable();
            }
        });

        Schema::table('quotation_items', function (Blueprint $table) {
            if (!Schema::hasColumn('quotation_items', 'item_code')) {
                $table->string('item_code', 50)->nullable();
            }
            if (!Schema::hasColumn('quotation_items', 'profile_system')) {
                $table->string('profile_system', 255)->nullable();
            }
            if (!Schema::hasColumn('quotation_items', 'weight_kg')) {
                $table->decimal('weight_kg', 10, 3)->default(0)->nullable();
            }
            if (!Schema::hasColumn('quotation_items', 'value_per_sqft')) {
                $table->decimal('value_per_sqft', 10, 2)->default(0)->nullable();
            }
            if (!Schema::hasColumn('quotation_items', 'unit_price')) {
                $table->decimal('unit_price', 12, 2)->default(0)->nullable();
            }
            if (!Schema::hasColumn('quotation_items', 'profile_details')) {
                $table->json('profile_details')->nullable();
            }
            if (!Schema::hasColumn('quotation_items', 'accessories_details')) {
                $table->json('accessories_details')->nullable();
            }
            if (!Schema::hasColumn('quotation_items', 'drawing_metadata')) {
                $table->json('drawing_metadata')->nullable();
            }
        });

        Schema::table('company_settings', function (Blueprint $table) {
            if (!Schema::hasColumn('company_settings', 'website')) {
                $table->string('website', 255)->nullable();
            }
            if (!Schema::hasColumn('company_settings', 'gstin')) {
                $table->string('gstin', 50)->nullable();
            }
            if (!Schema::hasColumn('company_settings', 'installation_prerequisites')) {
                $table->json('installation_prerequisites')->nullable();
            }
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
            $table->dropColumn([
                'no_of_components',
                'total_area_sqft',
                'basic_value',
                'total_project_cost',
                'avg_price_sqft_ex_gst',
                'avg_price_sqft_inc_gst',
                'cover_letter_enclosures'
            ]);
        });

        Schema::table('quotation_items', function (Blueprint $table) {
            $table->dropColumn([
                'item_code',
                'profile_system',
                'weight_kg',
                'value_per_sqft',
                'unit_price',
                'profile_details',
                'accessories_details',
                'drawing_metadata'
            ]);
        });

        Schema::table('company_settings', function (Blueprint $table) {
            $table->dropColumn([
                'website',
                'gstin',
                'installation_prerequisites'
            ]);
        });
    }
};
