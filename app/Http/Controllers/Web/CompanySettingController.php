<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\Web\StoreCompanySettingRequest;
use App\Models\CompanySetting;
use App\Services\CompanySettingService;

class CompanySettingController extends Controller
{
    protected $companySettingService;

    public function __construct(CompanySettingService $companySettingService)
    {
        $this->companySettingService = $companySettingService;
    }

    public function edit()
    {
        $setting = CompanySetting::forCompany(auth()->user()->company_id) ?? new CompanySetting();
        return view('company.edit', compact('setting'));
    }

    public function update(StoreCompanySettingRequest $request)
    {
        $this->companySettingService->updateSettings(
            $request->user()->company_id,
            $request->validated(),
            $request->file('logo'),
            $request->file('authorized_signature')
        );

        return redirect()->route('company.edit')->with('success', 'Company settings updated successfully.');
    }
}
