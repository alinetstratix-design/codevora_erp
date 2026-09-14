<?php

namespace App\Services\BOM;

use App\Models\QuotationItem;
use App\Models\QuotationItemBom;
use Illuminate\Support\Facades\DB;

class BomSnapshotService
{
    /**
     * Snapshots the calculated BOM into the quotation_item_boms table.
     * This makes the BOM stable historically even if rules or material costs change.
     */
    public function snapshotForQuotationItem(QuotationItem $item, array $bomResultLines): void
    {
        DB::transaction(function () use ($item, $bomResultLines) {
            // Clear existing snapshots for this item if any
            QuotationItemBom::where('quotation_item_id', $item->id)->delete();

            foreach ($bomResultLines as $line) {
                QuotationItemBom::create([
                    'quotation_item_id' => $item->id,
                    'material_id' => $line['material_id'],
                    'rule_type' => $line['rule_type'] ?? 'STRUCTURED',
                    'material_sku' => $line['material_sku'],
                    'material_name' => $line['material_name'],
                    'unit_cost' => $line['unit_cost'],
                    'calculated_qty' => $line['total_qty'],
                    'total_cost' => $line['total_cost'],
                    'calculation_snapshot' => [
                        'component_role' => $line['component_role'],
                        'material_uom' => $line['material_uom'],
                        'theoretical_qty' => $line['theoretical_qty'],
                        'normalized_qty' => $line['normalized_qty'],
                        'waste_percent' => $line['waste_percent'],
                        'context' => $line['snapshot_context']
                    ]
                ]);
            }
        });
    }
}
