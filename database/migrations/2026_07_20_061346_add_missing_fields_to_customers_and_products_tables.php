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
        Schema::table('customers', function (Blueprint $table) {
            $table->string('company_name')->nullable()->after('name');
            $table->string('gst_number')->nullable()->after('email');
            $table->text('address')->nullable()->after('gst_number');
            $table->string('city')->nullable()->after('address');
            $table->string('state')->nullable()->after('city');
            $table->string('pincode')->nullable()->after('state');
            $table->text('notes')->nullable()->after('status');
            $table->softDeletes();
        });

        Schema::table('products', function (Blueprint $table) {
            $table->string('opening_type')->nullable()->after('category'); // Sliding, Casement, Fixed, Tilt & Turn
            $table->string('profile_brand')->nullable()->after('opening_type');
            $table->string('profile_series')->nullable()->after('profile_brand');
            $table->string('glass_type')->nullable()->after('profile_series');
            $table->string('glass_thickness')->nullable()->after('glass_type');
            $table->string('mesh_type')->nullable()->after('glass_thickness');
            $table->string('hardware_brand')->nullable()->after('mesh_type');
            $table->string('default_image')->nullable()->after('hardware_brand');
            $table->text('description')->nullable()->after('default_image');
            $table->string('unit')->default('Sq.Ft.')->after('base_rate'); // Overriding default uom logic
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropSoftDeletes();
            $table->dropColumn(['company_name', 'gst_number', 'address', 'city', 'state', 'pincode', 'notes']);
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropSoftDeletes();
            $table->dropColumn(['opening_type', 'profile_brand', 'profile_series', 'glass_type', 'glass_thickness', 'mesh_type', 'hardware_brand', 'default_image', 'description', 'unit']);
        });
    }
};
