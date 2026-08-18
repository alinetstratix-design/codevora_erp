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
            ['type' => 'quotation_status', 'value' => 'draft', 'label' => 'Draft'],
            ['type' => 'quotation_status', 'value' => 'sent', 'label' => 'Sent'],
            ['type' => 'quotation_status', 'value' => 'approved', 'label' => 'Approved'],
            ['type' => 'quotation_status', 'value' => 'rejected', 'label' => 'Rejected'],
        ];

        foreach ($lookups as $lookup) {
            \App\Models\Lookup::firstOrCreate(
                ['type' => $lookup['type'], 'value' => $lookup['value']],
                ['label' => $lookup['label']]
            );
        }
    }
}
