<?php

namespace App\Services;

use App\Constants\QuotationStatus;
use App\Models\CompanySetting;
use App\Models\Quotation;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class CompanySettingService
{
    /** Textarea fields that the PDF renders as lists (one entry per line). */
    private const LIST_FIELDS = ['bank_details', 'terms_conditions', 'installation_prerequisites', 'inclusions', 'exclusions'];

    public function updateSettings(int $companyId, array $data, $logoFile = null, $signatureFile = null)
    {
        return DB::transaction(function () use ($companyId, $data, $logoFile, $signatureFile) {
            $setting = CompanySetting::forCompany($companyId) ?? new CompanySetting(['company_id' => $companyId]);

            // Files are handled below; never mass-assign uploaded file objects.
            unset($data['logo'], $data['authorized_signature']);

            foreach (self::LIST_FIELDS as $field) {
                if (array_key_exists($field, $data)) {
                    $data[$field] = $this->textToLines($data[$field]);
                }
            }

            if (array_key_exists('item_specs', $data)) {
                $data['item_specs'] = array_map(fn ($value) => trim((string) ($value ?? '')), (array) $data['item_specs']);
            }

            $setting->fill($data);
            $setting->company_id = $companyId;

            if ($logoFile) {
                if ($setting->logo) {
                    Storage::delete(str_replace('storage/', 'public/', $setting->logo));
                }
                $path = $logoFile->store('public/logos');
                $setting->logo = str_replace('public/', 'storage/', $path);
            }

            if ($signatureFile) {
                if ($setting->authorized_signature) {
                    Storage::delete(str_replace('storage/', 'public/', $setting->authorized_signature));
                }
                $path = $signatureFile->store('public/signatures');
                $setting->authorized_signature = str_replace('public/', 'storage/', $path);
            }

            $setting->save();

            // Draft PDFs are rebuilt with the new settings on their next download.
            // Approved, sent, accepted and rejected quotations keep their snapshot: their terms were agreed.
            Quotation::withoutGlobalScopes()
                ->where('company_id', $companyId)
                ->where('status', QuotationStatus::DRAFT)
                ->update(['pdf_stale' => true]);

            return $setting;
        });
    }

    /**
     * Turns "line 1\nline 2" into ['line 1', 'line 2'], dropping empty lines. Null stays null.
     */
    private function textToLines($value): ?array
    {
        if ($value === null) {
            return null;
        }
        if (is_array($value)) {
            return array_values(array_filter(array_map('trim', $value), fn ($line) => $line !== ''));
        }

        $lines = preg_split('/\r\n|\r|\n/', (string) $value);
        return array_values(array_filter(array_map('trim', $lines), fn ($line) => $line !== ''));
    }
}
