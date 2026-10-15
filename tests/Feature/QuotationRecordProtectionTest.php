<?php

namespace Tests\Feature;

use App\Constants\QuotationStatus;
use App\Models\AuditLog;
use App\Models\Company;
use App\Models\Quotation;
use App\Models\User;
use App\Services\QuotationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

/**
 * Business rules: an Approved quotation can never be edited (by anyone, including Admin),
 * and a deleted quotation is never physically removed.
 */
class QuotationRecordProtectionTest extends TestCase
{
    use RefreshDatabase;

    private function setupCompany(string $role = 'Admin'): array
    {
        $company = Company::create(['name' => 'Acme', 'is_active' => true]);
        $user = User::factory()->create(['company_id' => $company->id, 'role' => $role, 'status' => 'Active']);
        $quotation = Quotation::factory()->create([
            'company_id' => $company->id,
            'status' => QuotationStatus::DRAFT,
            'quotation_number' => 'QT-20261017-0001',
        ]);

        return [$company, $user, $quotation];
    }

    public function test_admin_cannot_edit_an_approved_quotation(): void
    {
        [, $admin, $quotation] = $this->setupCompany('Admin');
        $quotation->update(['status' => QuotationStatus::APPROVED]);

        $this->actingAs($admin)->get(route('quotations.edit', $quotation->id))
            ->assertRedirect(route('quotations.show', $quotation->id));

        $this->actingAs($admin)->put(route('quotations.update', $quotation->id), [])
            ->assertRedirect(route('quotations.show', $quotation->id));

        $this->assertSame(QuotationStatus::APPROVED, $quotation->fresh()->status);
    }

    public function test_service_refuses_to_update_an_approved_quotation(): void
    {
        [, , $quotation] = $this->setupCompany();
        $quotation->update(['status' => QuotationStatus::APPROVED]);

        $this->expectException(\Illuminate\Validation\ValidationException::class);
        app(QuotationService::class)->updateQuotation($quotation, []);
    }

    public function test_delete_is_a_soft_delete_and_keeps_the_record(): void
    {
        [, $admin, $quotation] = $this->setupCompany('Admin');

        $this->actingAs($admin)->delete(route('quotations.destroy', $quotation->id))
            ->assertRedirect(route('quotations.index'));

        // Hidden from normal queries...
        $this->assertNull(Quotation::find($quotation->id));
        // ...but still in the database, with who deleted it.
        $row = Quotation::withTrashed()->find($quotation->id);
        $this->assertNotNull($row);
        $this->assertNotNull($row->deleted_at);
        $this->assertSame($admin->id, (int) $row->deleted_by);
        $this->assertDatabaseHas('quotations', ['id' => $quotation->id]);
    }

    public function test_delete_writes_an_audit_log_entry(): void
    {
        [$company, $admin, $quotation] = $this->setupCompany('Admin');

        $this->actingAs($admin)->delete(route('quotations.destroy', $quotation->id));

        $this->assertDatabaseHas('audit_logs', [
            'company_id' => $company->id,
            'user_id' => $admin->id,
            'action' => 'quotation.deleted',
            'subject_type' => 'quotation',
            'subject_id' => $quotation->id,
            'subject_label' => 'QT-20261017-0001',
        ]);
    }

    public function test_approved_quotation_cannot_be_deleted(): void
    {
        [, $admin, $quotation] = $this->setupCompany('Admin');
        $quotation->update(['status' => QuotationStatus::APPROVED]);

        $this->actingAs($admin)->delete(route('quotations.destroy', $quotation->id))->assertForbidden();
        $this->assertNotNull(Quotation::find($quotation->id));
    }

    public function test_deleted_quotation_number_is_never_reused(): void
    {
        [$company, $admin, $quotation] = $this->setupCompany('Admin');
        $quotation->forceFill(['quotation_number' => 'QT-' . now()->format('Ymd') . '-0001'])->save();

        $this->actingAs($admin)->delete(route('quotations.destroy', $quotation->id));

        $next = app(QuotationService::class)->nextQuotationNumber($company->id);
        $this->assertSame('QT-' . now()->format('Ymd') . '-0002', $next);
    }

    public function test_shared_link_of_a_deleted_quotation_returns_404(): void
    {
        [, $admin, $quotation] = $this->setupCompany('Admin');
        $link = URL::temporarySignedRoute('quotations.shared', now()->addDay(), ['quotation' => $quotation->id]);

        $this->actingAs($admin)->delete(route('quotations.destroy', $quotation->id));
        auth()->logout();

        $this->get($link)->assertNotFound();
    }

    public function test_deleted_quotation_disappears_from_the_list(): void
    {
        [, $admin, $quotation] = $this->setupCompany('Admin');
        $this->actingAs($admin)->delete(route('quotations.destroy', $quotation->id));

        $this->actingAs($admin)->get(route('quotations.index'))
            ->assertOk()
            ->assertDontSee('QT-20261017-0001');
        $this->actingAs($admin)->get(route('quotations.show', $quotation->id))->assertNotFound();
    }

    public function test_audit_log_rows_cannot_be_changed_or_removed(): void
    {
        $log = AuditLog::create(['action' => 'x', 'company_id' => 1]);

        $log->update(['action' => 'y']);
        $this->assertSame('x', $log->fresh()->action);

        $log->delete();
        $this->assertNotNull(AuditLog::find($log->id));
    }

    public function test_activity_page_is_admin_only_and_company_scoped(): void
    {
        [$company, $admin, $quotation] = $this->setupCompany('Admin');
        $manager = User::factory()->create(['company_id' => $company->id, 'role' => 'Manager', 'status' => 'Active']);

        $other = Company::create(['name' => 'Other', 'is_active' => true]);
        AuditLog::create(['company_id' => $other->id, 'action' => 'quotation.deleted', 'subject_label' => 'OTHER-SECRET-1']);

        $this->actingAs($admin)->delete(route('quotations.destroy', $quotation->id));

        $this->actingAs($admin)->get(route('activity.index'))
            ->assertOk()
            ->assertSee('QT-20261017-0001')
            ->assertDontSee('OTHER-SECRET-1');

        $this->actingAs($manager)->get(route('activity.index'))->assertForbidden();
    }
}
