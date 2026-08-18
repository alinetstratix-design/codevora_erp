<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Customer;
use App\Models\Product;
use Illuminate\Support\Facades\Hash;

class DevelopmentSeeder extends Seeder
{
    public function run()
    {
        // 1. Seed Users
        $users = [
            [
                'name' => 'Ajeem Ali',
                'email' => 'admin@nexoratech.in',
                'password' => Hash::make('password123'),
                'role' => 'Admin',
                'status' => 'Active',
            ],
            [
                'name' => 'Rahul Singh',
                'email' => 'rahul.sales@fabriworks.in',
                'password' => Hash::make('password123'),
                'role' => 'Manager',
                'status' => 'Active',
            ],
            [
                'name' => 'Guest User',
                'email' => 'guest@fabriworks.in',
                'password' => Hash::make('password123'),
                'role' => 'Staff',
                'status' => 'Inactive',
            ]
        ];

        foreach ($users as $u) {
            User::updateOrCreate(['email' => $u['email']], $u);
        }

        // 2. Seed Customers
        $customers = [
            [
                'customer_code' => 'CUST-0012',
                'name' => 'Shree Balaji Constructions',
                'type' => 'Builder',
                'contact_person' => 'Rakesh Sharma',
                'phone' => '+91 9876543210',
                'email' => 'info@balajiconst.com',
                'location' => 'Haridwar',
                'status' => 'Active',
            ],
            [
                'customer_code' => 'CUST-0011',
                'name' => 'Amit Verma',
                'type' => 'Individual',
                'contact_person' => 'Amit Verma',
                'phone' => '+91 9988776655',
                'email' => 'amit.v@yahoo.com',
                'location' => 'Rishikesh',
                'status' => 'Active',
            ],
            [
                'customer_code' => 'CUST-0010',
                'name' => 'TechPark Solutions',
                'type' => 'Corporate',
                'contact_person' => 'Neha Gupta',
                'phone' => '+91 9123456789',
                'email' => 'purchase@techpark.in',
                'location' => 'Noida',
                'status' => 'Inactive',
            ],
            [
                'customer_code' => 'CUST-0009',
                'name' => 'Design Studio Architects',
                'type' => 'Architect',
                'contact_person' => 'Karan Johar',
                'phone' => '+91 8899001122',
                'email' => 'karan@designstudio.com',
                'location' => 'Dehradun',
                'status' => 'Active',
            ],
            [
                'customer_code' => 'CUST-0008',
                'name' => 'Omega Builders',
                'type' => 'Builder',
                'contact_person' => 'Sunil Tiwari',
                'phone' => '+91 7766554433',
                'email' => 'contact@omega.in',
                'location' => 'Roorkee',
                'status' => 'Active',
            ],
            [
                'customer_code' => 'CUST-0007',
                'name' => 'Rahul Sharma',
                'type' => 'Individual',
                'contact_person' => 'Rahul Sharma',
                'phone' => '+91 9000111222',
                'email' => 'rahul99@gmail.com',
                'location' => 'Haridwar',
                'status' => 'Active',
            ],
        ];

        foreach ($customers as $c) {
            Customer::updateOrCreate(['customer_code' => $c['customer_code']], $c);
        }

        // 3. Seed Products
        $products = [
            [
                'product_code' => 'PRD-1001',
                'name' => 'Aluminium 3-Track Sliding Window',
                'material' => 'Aluminium',
                'category' => 'Windows',
                'uom' => 'Sq.Ft',
                'base_rate' => 350.00,
                'status' => 'Active',
            ],
            [
                'product_code' => 'PRD-1002',
                'name' => 'UPVC Casement Door',
                'material' => 'UPVC',
                'category' => 'Doors',
                'uom' => 'Sq.Ft',
                'base_rate' => 480.00,
                'status' => 'Active',
            ],
            [
                'product_code' => 'PRD-1003',
                'name' => 'Toughened Glass Partition (12mm)',
                'material' => 'Glass',
                'category' => 'Partitions',
                'uom' => 'Sq.Ft',
                'base_rate' => 250.00,
                'status' => 'Active',
            ],
            [
                'product_code' => 'PRD-1004',
                'name' => 'SS 304 Balcony Railing',
                'material' => 'Stainless Steel',
                'category' => 'Railings',
                'uom' => 'Rft',
                'base_rate' => 850.00,
                'status' => 'Active',
            ],
            [
                'product_code' => 'PRD-1005',
                'name' => 'Aluminium Fixed Louvers',
                'material' => 'Aluminium',
                'category' => 'Ventilators',
                'uom' => 'Sq.Ft',
                'base_rate' => 280.00,
                'status' => 'Inactive',
            ],
            [
                'product_code' => 'PRD-1006',
                'name' => 'UPVC Sliding Window (2-Track)',
                'material' => 'UPVC',
                'category' => 'Windows',
                'uom' => 'Sq.Ft',
                'base_rate' => 320.00,
                'status' => 'Active',
            ],
        ];

        foreach ($products as $p) {
            Product::updateOrCreate(['product_code' => $p['product_code']], $p);
        }
    }
}
