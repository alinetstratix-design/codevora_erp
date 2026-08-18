<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('quotations', function (Blueprint $table) {
            $table->renameColumn('quote_no', 'quotation_number');
            $table->renameColumn('date', 'quotation_date');
            $table->renameColumn('valid_till', 'valid_until');
            
            $table->foreignId('company_setting_id')->nullable()->constrained('company_settings')->nullOnDelete();
            $table->string('project_location')->nullable();
            $table->string('sales_person')->nullable();
            $table->text('remarks')->nullable();
            
            $table->decimal('discount', 15, 2)->default(0)->after('subtotal');
            $table->decimal('transportation', 15, 2)->default(0);
            $table->decimal('installation', 15, 2)->default(0);
            $table->decimal('gst', 15, 2)->default(0);
            $table->string('amount_in_words')->nullable();
            $table->string('pdf_path')->nullable();
        });

        Schema::table('quotation_items', function (Blueprint $table) {
            $table->renameColumn('dimension_w', 'width');
            $table->renameColumn('dimension_h', 'height');
            $table->renameColumn('qty', 'quantity');
            $table->renameColumn('total_cost', 'amount');
            $table->renameColumn('remarks', 'notes');

            $table->string('product_name')->nullable();
            $table->string('profile_brand')->nullable();
            $table->string('profile_series')->nullable();
            $table->string('opening_type')->nullable();
            $table->string('glass_thickness')->nullable();
            $table->string('hardware_brand')->nullable();
            $table->string('color')->nullable();
            $table->integer('sort_order')->default(0);
        });
    }

    public function down()
    {
        Schema::table('quotations', function (Blueprint $table) {
            $table->renameColumn('quotation_number', 'quote_no');
            $table->renameColumn('quotation_date', 'date');
            $table->renameColumn('valid_until', 'valid_till');
            
            $table->dropForeign(['company_setting_id']);
            $table->dropColumn(['company_setting_id', 'project_location', 'sales_person', 'remarks', 'discount', 'transportation', 'installation', 'gst', 'amount_in_words', 'pdf_path']);
        });

        Schema::table('quotation_items', function (Blueprint $table) {
            $table->renameColumn('width', 'dimension_w');
            $table->renameColumn('height', 'dimension_h');
            $table->renameColumn('quantity', 'qty');
            $table->renameColumn('amount', 'total_cost');
            $table->renameColumn('notes', 'remarks');

            $table->dropColumn(['product_name', 'profile_brand', 'profile_series', 'opening_type', 'glass_thickness', 'hardware_brand', 'color', 'sort_order']);
        });
    }
};
