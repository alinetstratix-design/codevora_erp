<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use App\Models\User;
use App\Models\Customer;
use App\Models\Product;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // 1. Run LookupSeeder
        $this->call(LookupSeeder::class);

        // 2. Run ProductionMasterSeeder
        $this->call(ProductionMasterSeeder::class);

        // 3. Seed Default Admin & System Users
        $company = \App\Models\Company::first();
        $companyId = $company ? $company->id : null;

        User::updateOrCreate(
            ['email' => 'admin@codevora.com'],
            [
                'company_id' => $companyId,
                'name' => 'System Administrator',
                'password' => Hash::make('password123'),
                'role' => 'Admin',
                'status' => 'Active',
            ]
        );

        User::updateOrCreate(
            ['email' => 'admin@nexoratech.in'],
            [
                'company_id' => $companyId,
                'name' => 'Ajeem Ali',
                'password' => Hash::make('password123'),
                'role' => 'Admin',
                'status' => 'Active',
            ]
        );
    }
}
