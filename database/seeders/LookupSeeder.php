<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class LookupSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $lookups = [
            // User Roles
            ['type' => 'user_roles', 'value' => 'Admin', 'label' => 'Admin'],
            ['type' => 'user_roles', 'value' => 'Manager', 'label' => 'Manager'],
            ['type' => 'user_roles', 'value' => 'Staff', 'label' => 'Staff'],
            
            // User Status
            ['type' => 'user_status', 'value' => 'Active', 'label' => 'Active'],
            ['type' => 'user_status', 'value' => 'Inactive', 'label' => 'Inactive'],

            // Customer Types
            ['type' => 'customer_types', 'value' => 'Individual', 'label' => 'Individual'],
            ['type' => 'customer_types', 'value' => 'Builder', 'label' => 'Builder'],
            ['type' => 'customer_types', 'value' => 'Architect', 'label' => 'Architect'],
            ['type' => 'customer_types', 'value' => 'Corporate', 'label' => 'Corporate'],

            // Product Categories
            ['type' => 'product_categories', 'value' => 'Windows', 'label' => 'Windows'],
            ['type' => 'product_categories', 'value' => 'Doors', 'label' => 'Doors'],
            ['type' => 'product_categories', 'value' => 'Partitions', 'label' => 'Partitions'],
            ['type' => 'product_categories', 'value' => 'Railings', 'label' => 'Railings'],
            ['type' => 'product_categories', 'value' => 'Ventilators', 'label' => 'Ventilators'],

            // Product Materials
            ['type' => 'product_materials', 'value' => 'Aluminium', 'label' => 'Aluminium'],
            ['type' => 'product_materials', 'value' => 'UPVC', 'label' => 'UPVC'],
            ['type' => 'product_materials', 'value' => 'Glass', 'label' => 'Glass'],
            ['type' => 'product_materials', 'value' => 'Stainless Steel', 'label' => 'Stainless Steel'],

            // Product UOM (Units)
            ['type' => 'uom', 'value' => 'Sq.Ft', 'label' => 'Sq.Ft'],
            ['type' => 'uom', 'value' => 'Rft', 'label' => 'Rft'],
            ['type' => 'uom', 'value' => 'Nos', 'label' => 'Nos'],

            // Work Categories
            ['type' => 'work_categories', 'value' => 'Aluminium', 'label' => 'Aluminium / Glass Work'],
            ['type' => 'work_categories', 'value' => 'UPVC', 'label' => 'UPVC / Glass Work'],
            ['type' => 'work_categories', 'value' => 'SS', 'label' => 'SS / Metal Railings'],

            // Item Types
            ['type' => 'item_types', 'value' => 'fixed', 'label' => 'Fixed Glass Pane'],
            ['type' => 'item_types', 'value' => 'slider_2', 'label' => '2-Pane Sliding Window'],
            ['type' => 'item_types', 'value' => 'slider_3', 'label' => '3-Pane Sliding Window'],
            ['type' => 'item_types', 'value' => 'slider_3_transom', 'label' => '3-Pane Slider with Transom'],
            ['type' => 'item_types', 'value' => 'door', 'label' => 'Heavy Sliding Door'],

            // Quotation Status
            ['type' => 'quotation_status', 'value' => 'Draft', 'label' => 'Draft'],
            ['type' => 'quotation_status', 'value' => 'Sent', 'label' => 'Sent'],
            ['type' => 'quotation_status', 'value' => 'Approved', 'label' => 'Approved'],
            ['type' => 'quotation_status', 'value' => 'Rejected', 'label' => 'Rejected'],

            // GST Slabs
            ['type' => 'gst_slabs', 'value' => '0', 'label' => '0%'],
            ['type' => 'gst_slabs', 'value' => '5', 'label' => '5%'],
            ['type' => 'gst_slabs', 'value' => '12', 'label' => '12%'],
            ['type' => 'gst_slabs', 'value' => '18', 'label' => '18% (Standard GST)'],
            ['type' => 'gst_slabs', 'value' => '28', 'label' => '28%'],

            // Profile Colors
            ['type' => 'profile_colors', 'value' => 'WHITE', 'label' => 'WHITE'],
            ['type' => 'profile_colors', 'value' => 'BLACK', 'label' => 'BLACK'],
            ['type' => 'profile_colors', 'value' => 'WOOD', 'label' => 'WOOD FINISH'],
            ['type' => 'profile_colors', 'value' => 'GREY', 'label' => 'GREY'],

            // Glass Types
            ['type' => 'glass_types', 'value' => '5mm Clear Toughened', 'label' => '5mm Clear Toughened'],
            ['type' => 'glass_types', 'value' => '6mm Clear Toughened', 'label' => '6mm Clear Toughened'],
            ['type' => 'glass_types', 'value' => '8mm Clear Toughened', 'label' => '8mm Clear Toughened'],
            ['type' => 'glass_types', 'value' => 'DGU (5+9A+5)', 'label' => 'DGU (5+9A+5)'],

            // Hardware Brands
            ['type' => 'hardware_brands', 'value' => 'CORA Hardware', 'label' => 'CORA Hardware'],
            ['type' => 'hardware_brands', 'value' => 'Kinlong', 'label' => 'Kinlong'],
            ['type' => 'hardware_brands', 'value' => 'Ozone', 'label' => 'Ozone'],

            // Mesh Types
            ['type' => 'mesh_types', 'value' => 'No', 'label' => 'No Mesh'],
            ['type' => 'mesh_types', 'value' => 'Fiber', 'label' => 'Fiber Mesh'],
            ['type' => 'mesh_types', 'value' => 'SS', 'label' => 'SS Mesh'],
            ['type' => 'mesh_types', 'value' => 'Pleated', 'label' => 'Pleated Mesh'],
        ];

        foreach ($lookups as $lookup) {
            \App\Models\Lookup::firstOrCreate(
                ['type' => $lookup['type'], 'value' => $lookup['value']],
                ['label' => $lookup['label']]
            );
        }
    }
}
