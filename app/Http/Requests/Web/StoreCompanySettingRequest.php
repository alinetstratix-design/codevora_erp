<?php

namespace App\Http\Requests\Web;

use Illuminate\Foundation\Http\FormRequest;

class StoreCompanySettingRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    protected function prepareForValidation()
    {
        if ($this->filled('website')) {
            $website = trim($this->website);
            if ($website !== '' && !preg_match('~^https?://~i', $website)) {
                $this->merge([
                    'website' => 'https://' . $website,
                ]);
            }
        }
    }

    public function rules()
    {
        return [
            'company_name' => 'nullable|string|max:255',
            'gst_number' => 'nullable|string|max:50',
            'address' => 'nullable|string',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'website' => 'nullable|string|max:255',
            'bank_details' => 'nullable|string',
            'terms_conditions' => 'nullable|string',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'authorized_signature' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ];
    }
}
