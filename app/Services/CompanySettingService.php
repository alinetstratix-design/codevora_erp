<?php

namespace App\Services;

use App\Models\CompanySetting;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class CompanySettingService
{
    public function updateSettings(array $data, $logoFile = null, $signatureFile = null)
    {
        return DB::transaction(function () use ($data, $logoFile, $signatureFile) {
            $setting = CompanySetting::first() ?? new CompanySetting();
            $setting->fill($data);

            if ($logoFile) {
                if ($setting->logo) {
                    $oldPath = str_replace('storage/', 'public/', $setting->logo);
                    Storage::delete($oldPath);
                }
                $path = $logoFile->store('public/logos');
                $setting->logo = str_replace('public/', 'storage/', $path);
            }

            if ($signatureFile) {
                if ($setting->authorized_signature) {
                    $oldPath = str_replace('storage/', 'public/', $setting->authorized_signature);
                    Storage::delete($oldPath);
                }
                $path = $signatureFile->store('public/signatures');
                $setting->authorized_signature = str_replace('public/', 'storage/', $path);
            }

            $setting->save();
            return $setting;
        });
    }
}
