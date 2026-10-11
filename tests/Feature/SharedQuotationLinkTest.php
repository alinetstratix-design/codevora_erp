<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Quotation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SharedQuotationLinkTest extends TestCase
{
    use RefreshDatabase;

    public function test_shared_pdf_requires_a_valid_signature(): void
    {
        $company = Company::create(['name' => 'Share Co', 'is_active' => true]);
        $quotation = Quotation::create([
            'company_id' => $company->id,
            'quotation_number' => 'QT-20261012-0001',
            'client_name' => 'Client',
            'status' => 'Draft',
        ]);

        // Guests get no access without a signature
        $this->get(route('quotations.shared', ['quotation' => $quotation->id]))->assertStatus(403);

        // Tampering with the id invalidates a signed link
        $signed = \Illuminate\Support\Facades\URL::temporarySignedRoute('quotations.shared', now()->addDay(), ['quotation' => $quotation->id]);
        $tampered = str_replace('/' . $quotation->id . '/', '/' . ($quotation->id + 1) . '/', $signed);
        $this->get($tampered)->assertStatus(403);
    }
}
