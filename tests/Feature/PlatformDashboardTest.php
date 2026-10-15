<?php

namespace Tests\Feature;

use App\Constants\UserRole;
use App\Models\Company;
use App\Models\DemoRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PlatformDashboardTest extends TestCase
{
    use RefreshDatabase;

    private function company(string $name = 'Acme Windows', array $overrides = []): Company
    {
        return Company::create(array_merge([
            'name' => $name,
            'is_active' => true,
            'subscription_status' => 'Active',
            'trial_ends_at' => null,
            'subscription_ends_at' => now()->addMonths(3),
            'max_users' => 3,
        ], $overrides));
    }

    private function member(Company $company, string $role = UserRole::ADMIN, string $status = 'Active', ?string $email = null): User
    {
        return User::create([
            'company_id' => $company->id,
            'name' => 'Member',
            'email' => $email ?? 'member'.uniqid().'@acme.test',
            'password' => Hash::make('secret-pass-123'),
            'role' => $role,
            'status' => $status,
            'must_change_password' => false,
        ]);
    }

    private function superAdmin(): User
    {
        return User::create([
            'company_id' => null,
            'name' => 'Root',
            'email' => 'root@platform.test',
            'password' => Hash::make('secret-pass-123'),
            'role' => UserRole::SUPER_ADMIN,
            'status' => 'Active',
            'must_change_password' => false,
        ]);
    }

    public function test_dashboard_counts_companies_and_pending_trial_requests(): void
    {
        $company = $this->company();
        $this->member($company);

        DemoRequest::create([
            'name' => 'Ravi', 'company_name' => 'Ravi Frames', 'email' => 'ravi@frames.test',
            'phone' => '9000000000', 'status' => 'pending',
        ]);

        $response = $this->actingAs($this->superAdmin())->get(route('platform.index'));

        $response->assertOk();
        $stats = $response->viewData('stats');
        $this->assertSame(1, $stats['companies']);
        $this->assertSame(1, $stats['on_paid']);
        $this->assertSame(1, $stats['pending_requests']);
        $this->assertSame(1, $stats['active_users']);
    }

    public function test_super_admin_adds_user_with_temporary_password_and_audit_entry(): void
    {
        $company = $this->company();
        $super = $this->superAdmin();

        $this->actingAs($super)
            ->post(route('platform.companies.users.store', $company), [
                'name' => 'New Staff', 'email' => 'staff@acme.test', 'role' => 'Staff',
            ])
            ->assertRedirect(route('platform.companies.show', $company))
            ->assertSessionHas('temp_email', 'staff@acme.test');

        $this->assertDatabaseHas('users', [
            'email' => 'staff@acme.test',
            'company_id' => $company->id,
            'status' => 'Active',
            'must_change_password' => true,
        ]);
        $this->assertDatabaseHas('subscription_audit_logs', [
            'company_id' => $company->id,
            'action' => 'user_added',
            'actor_id' => $super->id,
        ]);
    }

    public function test_adding_a_user_is_blocked_when_seats_are_full(): void
    {
        $company = $this->company('Full Co', ['max_users' => 1]);
        $this->member($company);

        $this->actingAs($this->superAdmin())
            ->post(route('platform.companies.users.store', $company), [
                'name' => 'Extra', 'email' => 'extra@full.test', 'role' => 'Staff',
            ])
            ->assertSessionHas('error');

        $this->assertDatabaseMissing('users', ['email' => 'extra@full.test']);
    }

    public function test_super_admin_cannot_edit_a_user_through_the_wrong_company(): void
    {
        $companyA = $this->company('Company A');
        $companyB = $this->company('Company B');
        $userOfA = $this->member($companyA, UserRole::STAFF);

        $this->actingAs($this->superAdmin())
            ->put(route('platform.companies.users.update', [$companyB, $userOfA]), [
                'role' => 'Manager', 'status' => 'Active',
            ])
            ->assertNotFound();

        $this->assertSame(UserRole::STAFF, $userOfA->fresh()->role);
    }

    public function test_last_active_admin_cannot_be_demoted(): void
    {
        $company = $this->company();
        $onlyAdmin = $this->member($company, UserRole::ADMIN);

        $this->actingAs($this->superAdmin())
            ->put(route('platform.companies.users.update', [$company, $onlyAdmin]), [
                'role' => 'Staff', 'status' => 'Active',
            ])
            ->assertSessionHas('error');

        $this->assertSame(UserRole::ADMIN, $onlyAdmin->fresh()->role);
    }

    public function test_reactivating_a_user_is_blocked_at_the_seat_limit(): void
    {
        $company = $this->company('Two Seats', ['max_users' => 2]);
        $this->member($company, UserRole::ADMIN);
        $this->member($company, UserRole::STAFF, 'Active');
        $inactive = $this->member($company, UserRole::STAFF, 'Inactive');

        $this->actingAs($this->superAdmin())
            ->put(route('platform.companies.users.update', [$company, $inactive]), [
                'role' => 'Staff', 'status' => 'Active',
            ])
            ->assertSessionHas('error');

        $this->assertSame('Inactive', $inactive->fresh()->status);
    }

    public function test_seat_limit_change_is_saved_and_audited(): void
    {
        $company = $this->company();
        $super = $this->superAdmin();

        $this->actingAs($super)
            ->patch(route('platform.companies.seats', $company), ['max_users' => 10])
            ->assertSessionHas('success');

        $this->assertSame(10, $company->fresh()->max_users);
        $this->assertDatabaseHas('subscription_audit_logs', [
            'company_id' => $company->id,
            'action' => 'seats_changed',
            'actor_id' => $super->id,
        ]);
    }

    public function test_company_admin_cannot_open_the_platform_company_page(): void
    {
        $company = $this->company();
        $admin = $this->member($company);

        $this->actingAs($admin)
            ->get(route('platform.companies.show', $company))
            ->assertForbidden();
    }
}
