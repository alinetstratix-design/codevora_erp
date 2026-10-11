<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Business codes must be unique per company (multi-tenant), not globally.
 * Also protects quotation numbers from duplicates.
 */
return new class extends Migration
{
    private function hasIndex(string $table, string $indexName): bool
    {
        $sm = Schema::getConnection()->getDoctrineSchemaManager();
        $indexes = $sm->listTableIndexes($table);
        return array_key_exists(strtolower($indexName), $indexes);
    }

    public function up()
    {
        // Resolve any duplicate quotation numbers per company before creating unique index
        $duplicates = DB::table('quotations')
            ->select('company_id', 'quotation_number')
            ->groupBy('company_id', 'quotation_number')
            ->havingRaw('COUNT(*) > 1')
            ->get();

        foreach ($duplicates as $duplicate) {
            $records = DB::table('quotations')
                ->where('company_id', $duplicate->company_id)
                ->where('quotation_number', $duplicate->quotation_number)
                ->orderBy('id')
                ->get();

            $i = 1;
            foreach ($records as $index => $record) {
                if ($index === 0) {
                    continue;
                }
                $i++;
                DB::table('quotations')
                    ->where('id', $record->id)
                    ->update(['quotation_number' => $record->quotation_number . '-' . $i]);
            }
        }

        if (! $this->hasIndex('customers', 'customers_company_code_unique')) {
            Schema::table('customers', function (Blueprint $table) {
                if ($this->hasIndex('customers', 'customers_customer_code_unique')) {
                    $table->dropUnique(['customer_code']);
                }
                $table->unique(['company_id', 'customer_code'], 'customers_company_code_unique');
            });
        }

        if (! $this->hasIndex('products', 'products_company_code_unique')) {
            Schema::table('products', function (Blueprint $table) {
                if ($this->hasIndex('products', 'products_product_code_unique')) {
                    $table->dropUnique(['product_code']);
                }
                $table->unique(['company_id', 'product_code'], 'products_company_code_unique');
            });
        }

        if (! $this->hasIndex('materials', 'materials_company_sku_unique')) {
            Schema::table('materials', function (Blueprint $table) {
                if ($this->hasIndex('materials', 'materials_sku_unique')) {
                    $table->dropUnique(['sku']);
                }
                $table->unique(['company_id', 'sku'], 'materials_company_sku_unique');
            });
        }

        if (! $this->hasIndex('quotations', 'quotations_company_number_unique')) {
            Schema::table('quotations', function (Blueprint $table) {
                $table->unique(['company_id', 'quotation_number'], 'quotations_company_number_unique');
            });
        }
    }

    public function down()
    {
        if ($this->hasIndex('quotations', 'quotations_company_number_unique')) {
            Schema::table('quotations', function (Blueprint $table) {
                $table->dropUnique('quotations_company_number_unique');
            });
        }

        if ($this->hasIndex('materials', 'materials_company_sku_unique')) {
            Schema::table('materials', function (Blueprint $table) {
                $table->dropUnique('materials_company_sku_unique');
                $table->unique(['sku']);
            });
        }

        if ($this->hasIndex('products', 'products_company_code_unique')) {
            Schema::table('products', function (Blueprint $table) {
                $table->dropUnique('products_company_code_unique');
                $table->unique(['product_code']);
            });
        }

        if ($this->hasIndex('customers', 'customers_company_code_unique')) {
            Schema::table('customers', function (Blueprint $table) {
                $table->dropUnique('customers_company_code_unique');
                $table->unique(['customer_code']);
            });
        }
    }
};
