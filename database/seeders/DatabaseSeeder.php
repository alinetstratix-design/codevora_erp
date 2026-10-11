<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * Safe to run in any environment: only reference lookups are seeded.
 *
 * - Admin users: php artisan users:create-admin
 * - Master product/material data (one-time, empty company only):
 *   php artisan db:seed --class=ProductionMasterSeeder
 * - Demo data (local only): php artisan db:seed --class=DevelopmentSeeder
 */
class DatabaseSeeder extends Seeder
{
    public function run()
    {
        $this->call(LookupSeeder::class);
    }
}
