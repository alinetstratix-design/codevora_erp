<?php

namespace Tests\Feature;

use App\Constants\UserRole;
use App\Models\Company;
use App\Models\CompanySetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class MultiCompanyAccessTest extends TestCase
{
    use RefreshDatabase;

    private const PASSWORD = 'Strong-pass-123';

    private function company(string $name, bool $active = true): Company
    {
        return Company::create(['name' => $name, 'is_active' => $active]);
    }

    private function user(?Company $company, string $role, array $extra = []): User
    {
        return User::create(array_merge([
            'company_id' => $company?->id,
            'name' => ucfirst($role) . ' User',
            'email' => strtolower($role) . '.' . uniqid() . '@example.test',
            'password' => Hash::make(self::PASSWORD),
            'role' => $role,
            'status' => 'Active',
            'must_change_password' => false,
        ], $extra));
    }

    private function signIn(User $user)
    {
        return $this->actingAs($user);
    }

    public function test_super_admin_signs_in_to_platform_console(): void
    {
        $super = $this->user(null, UserRole::SUPER_ADMIN);

        $this->post('/login', ['email' => $super->email, 'password' => self::PASSWORD])
            ->assertRedirect(route('platform.index'));

        $this->get('/platform')->assertOk();
        $this->get('/dashboard')->assertRedirect(route('platform.index'));
    }

    public function test_company_admin_cannot_open_platform_console(): void
    {
        $admin = $this->user($this->company('Acme'), UserRole::ADMIN);

        $this->signIn($admin)->get('/platform')->assertForbidden();
    }

    public function test_super_admin_creates_company_with_temporary_admin(): void
    {
        $super = $this->user(null, UserRole::SUPER_ADMIN);

        $this->signIn($super)->post('/platform/companies', [
            'company_name' => 'Sharma Windows',
            'company_email' => 'info@sharma.test',
            'admin_name' => 'Ravi Sharma',
            'admin_email' => 'ravi@sharma.test',
        ])->assertRedirect(route('platform.index'))
          ->assertSessionHas('temp_password');

        $company = Company::where('name', 'Sharma Windows')->firstOrFail();
        $admin = User::where('email', 'ravi@sharma.test')->firstOrFail();

        $this->assertSame($company->id, $admin->company_id);
        $this->assertSame(UserRole::ADMIN, $admin->role);
        $this->assertTrue($admin->must_change_password);
        $this->assertNotNull(CompanySetting::forCompany($company->id), 'Company settings row must be created');
    }

    public function test_company_admin_adds_team_member_only_in_own_company(): void
    {
        $acme = $this->company('Acme');
        $beta = $this->company('Beta');
        $admin = $this->user($acme, UserRole::ADMIN);
        $betaUser = $this->user($beta, UserRole::STAFF);

        $this->signIn($admin)->post('/team', [
            'name' => 'New Staff',
            'email' => 'staff@acme.test',
            'role' => UserRole::STAFF,
        ])->assertRedirect(route('team.index'));

        $this->assertSame($acme->id, User::where('email', 'staff@acme.test')->value('company_id'));

        // Another company's user is invisible: 404, not 403, so ids cannot be probed.
        $this->signIn($admin)->get('/team/' . $betaUser->id . '/edit')->assertNotFound();
    }

    public function test_company_admin_cannot_create_super_admin(): void
    {
        $admin = $this->user($this->company('Acme'), UserRole::ADMIN);

        $this->signIn($admin)->post('/team', [
            'name' => 'Sneaky',
            'email' => 'sneaky@acme.test',
            'role' => UserRole::SUPER_ADMIN,
        ])->assertSessionHasErrors('role');
    }

    public function test_staff_cannot_manage_team_or_products(): void
    {
        $staff = $this->user($this->company('Acme'), UserRole::STAFF);

        $this->signIn($staff)->get('/team')->assertForbidden();
        $this->signIn($staff)->get('/products')->assertForbidden();
        $this->signIn($staff)->get('/company-settings')->assertForbidden();
        $this->signIn($staff)->get('/quotations')->assertOk();
    }

    public function test_user_of_inactive_company_cannot_sign_in(): void
    {
        $company = $this->company('Dormant Co', false);
        $user = $this->user($company, UserRole::ADMIN);

        $this->post('/login', ['email' => $user->email, 'password' => self::PASSWORD])
            ->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_deactivating_company_signs_out_active_session(): void
    {
        $company = $this->company('Live Co');
        $user = $this->user($company, UserRole::ADMIN);

        $this->signIn($user)->get('/dashboard')->assertOk();

        $company->update(['is_active' => false]);
        $user->unsetRelation('company'); // the guard holds this same instance

        $this->get('/dashboard')->assertRedirect(route('login'));
        $this->assertGuest();
    }

    public function test_temporary_password_must_be_changed_before_using_the_system(): void
    {
        $user = $this->user($this->company('Acme'), UserRole::ADMIN, ['must_change_password' => true]);

        $this->signIn($user)->get('/dashboard')->assertRedirect(route('account.password.edit'));

        $this->signIn($user)->put('/account/password', [
            'current_password' => self::PASSWORD,
            'password' => 'New-strong-pass-456',
            'password_confirmation' => 'New-strong-pass-456',
        ])->assertRedirect(route('dashboard'));

        $user->refresh();
        $this->assertFalse($user->must_change_password);
        $this->assertTrue(Hash::check('New-strong-pass-456', $user->password));
    }

    public function test_admin_cannot_demote_or_deactivate_themselves(): void
    {
        $admin = $this->user($this->company('Solo Co'), UserRole::ADMIN);

        $this->signIn($admin)->put('/team/' . $admin->id, [
            'name' => $admin->name,
            'role' => UserRole::STAFF,
            'status' => 'Active',
        ])->assertSessionHasErrors('role');

        $this->assertSame(UserRole::ADMIN, $admin->fresh()->role);
    }

    public function test_admin_can_demote_another_admin_when_one_remains(): void
    {
        $company = $this->company('Two Admins');
        $admin = $this->user($company, UserRole::ADMIN);
        $other = $this->user($company, UserRole::ADMIN);

        $this->signIn($admin)->put('/team/' . $other->id, [
            'name' => $other->name,
            'role' => UserRole::MANAGER,
            'status' => 'Active',
        ])->assertRedirect(route('team.index'));

        $this->assertSame(UserRole::MANAGER, $other->fresh()->role);
    }

    public function test_password_reset_request_never_reveals_whether_email_exists(): void
    {
        $response = $this->post('/forgot-password', ['email' => 'nobody@nowhere.test']);

        $response->assertSessionHas('success');
        $response->assertSessionDoesntHaveErrors();
    }
}
