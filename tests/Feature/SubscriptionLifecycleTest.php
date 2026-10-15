<?php

namespace Tests\Feature;

use App\Constants\UserRole;
use App\Models\Company;
use App\Models\DemoRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Mail\SubscriptionExpiryReminderMail;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class SubscriptionLifecycleTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    private function company(array $overrides = []): Company
    {
        return Company::create(array_merge([
            'name' => 'Acme Windows',
            'is_active' => true,
            'subscription_status' => 'Trial',
            'trial_ends_at' => now()->addDays(3),
            'subscription_ends_at' => now()->addDays(3),
            'max_users' => 3,
        ], $overrides));
    }

    private function admin(Company $company): User
    {
        return User::create([
            'company_id' => $company->id,
            'name' => 'Owner',
            'email' => 'owner'.$company->id.'@acme.test',
            'password' => Hash::make('secret-pass-123'),
            'role' => UserRole::ADMIN,
            'status' => 'Active',
            'must_change_password' => false,
        ]);
    }

    private function superAdmin(): User
    {
        $company = $this->company(['name' => 'Platform HQ']);

        return User::create([
            'company_id' => $company->id,
            'name' => 'Root',
            'email' => 'root@platform.test',
            'password' => Hash::make('secret-pass-123'),
            'role' => UserRole::SUPER_ADMIN,
            'status' => 'Active',
            'must_change_password' => false,
        ]);
    }

    public function test_trial_company_can_open_erp_while_trial_is_running(): void
    {
        $user = $this->admin($this->company());

        $this->actingAs($user)->get('/dashboard')->assertOk();
    }

    public function test_expired_trial_is_locked_out_of_erp_and_shown_expiry_screen(): void
    {
        $user = $this->admin($this->company(['trial_ends_at' => now()->subDay(), 'subscription_ends_at' => now()->subDay()]));

        $this->actingAs($user)->get('/dashboard')->assertRedirect(route('subscription.expired'));
    }

    public function test_expired_company_api_request_gets_403_json(): void
    {
        $user = $this->admin($this->company(['trial_ends_at' => now()->subDay(), 'subscription_ends_at' => now()->subDay()]));
        Sanctum::actingAs($user);

        $this->getJson('/api/dashboard/stats')
            ->assertStatus(403)
            ->assertJson(['subscription_expired' => true]);
    }

    public function test_manually_created_company_via_provisioning_is_not_locked_out(): void
    {
        $company = app(\App\Services\AccountProvisioningService::class)
            ->createCompanyWithAdmin(['name' => 'Manual Co'], ['name' => 'Boss', 'email' => 'boss@manual.test'])[0];

        $this->assertTrue($company->hasAccess());
        $this->assertSame('Active', $company->subscription_status);
    }

    public function test_approved_demo_request_cannot_be_rejected(): void
    {
        $super = $this->superAdmin();
        $demo = DemoRequest::create([
            'name' => 'Ravi', 'company_name' => 'Ravi Frames', 'email' => 'ravi@frames.test',
            'phone' => '9000000000', 'status' => 'approved',
        ]);

        $this->actingAs($super)
            ->post(route('platform.demo-requests.reject', $demo))
            ->assertSessionHas('error');

        $this->assertSame('approved', $demo->fresh()->status);
    }

    public function test_extending_trial_does_not_downgrade_a_paid_company(): void
    {
        $super = $this->superAdmin();
        $paid = $this->company([
            'subscription_status' => 'Active',
            'trial_ends_at' => null,
            'subscription_ends_at' => now()->addMonths(6),
        ]);

        $this->actingAs($super)
            ->post(route('platform.companies.subscription', $paid), ['action' => 'extend_3d'])
            ->assertSessionHas('error');

        $this->assertSame('Active', $paid->fresh()->subscription_status);
    }

    public function test_inactive_members_do_not_use_seats(): void
    {
        $company = $this->company(['max_users' => 2]);
        $owner = $this->admin($company);
        User::create([
            'company_id' => $company->id, 'name' => 'Old', 'email' => 'old@acme.test',
            'password' => Hash::make('x'), 'role' => UserRole::STAFF, 'status' => 'Inactive',
        ]);

        Sanctum::actingAs($owner);

        $this->postJson('/api/users', [
            'name' => 'New', 'email' => 'new@acme.test', 'password' => 'password123', 'role' => 'Staff',
        ])->assertStatus(201);
    }

    public function test_grace_period_allows_reading_but_blocks_changes(): void
    {
        $user = $this->admin($this->company([
            'trial_ends_at' => now()->subDay(),
            'subscription_ends_at' => now()->subDay(),
        ]));
        Sanctum::actingAs($user);

        $this->getJson('/api/dashboard/stats')->assertOk();
        $this->postJson('/api/customers', [])
            ->assertStatus(403)
            ->assertJson(['read_only' => true]);
    }

    public function test_access_is_fully_locked_after_grace_period(): void
    {
        $user = $this->admin($this->company([
            'trial_ends_at' => now()->subDays(10),
            'subscription_ends_at' => now()->subDays(10),
        ]));
        Sanctum::actingAs($user);

        $this->getJson('/api/dashboard/stats')
            ->assertStatus(403)
            ->assertJsonMissing(['read_only' => true]);
    }

    public function test_plan_activation_is_written_to_audit_log(): void
    {
        $super = $this->superAdmin();
        $company = $this->company();

        $this->actingAs($super)
            ->post(route('platform.companies.subscription', $company), ['action' => 'activate_paid']);

        $this->assertDatabaseHas('subscription_audit_logs', [
            'company_id' => $company->id,
            'action' => 'plan_activated',
            'actor_id' => $super->id,
        ]);
    }

    public function test_reminder_is_sent_once_per_stage(): void
    {
        Mail::fake();
        $company = $this->company([
            'trial_ends_at' => now()->addHours(10),
            'subscription_ends_at' => now()->addHours(10),
        ]);
        $this->admin($company);

        $this->artisan('subscriptions:send-reminders')->assertSuccessful();
        $this->artisan('subscriptions:send-reminders')->assertSuccessful();

        Mail::assertSent(SubscriptionExpiryReminderMail::class, 1);
        $this->assertSame('1d', $company->fresh()->expiry_reminder_stage);
    }

    public function test_demo_honeypot_drops_bot_submissions_silently(): void
    {
        $this->postJson('/demo-requests', [
            'name' => 'Bot', 'company_name' => 'Spam Co', 'email' => 'bot@spam.test',
            'phone' => '1', 'website' => 'http://spam.test',
        ])->assertOk();

        $this->assertDatabaseMissing('demo_requests', ['email' => 'bot@spam.test']);
    }

    public function test_reactivating_a_member_respects_the_seat_limit(): void
    {
        $company = $this->company(['max_users' => 2]);
        $owner = $this->admin($company);
        User::create([
            'company_id' => $company->id, 'name' => 'Active Staff', 'email' => 'act@acme.test',
            'password' => Hash::make('x'), 'role' => UserRole::STAFF, 'status' => 'Active',
        ]);
        $inactive = User::create([
            'company_id' => $company->id, 'name' => 'Old', 'email' => 'old2@acme.test',
            'password' => Hash::make('x'), 'role' => UserRole::STAFF, 'status' => 'Inactive',
        ]);

        Sanctum::actingAs($owner);

        $this->putJson('/api/users/'.$inactive->id, ['status' => 'Active'])
            ->assertStatus(422);

        $this->assertSame('Inactive', $inactive->fresh()->status);
    }

    public function test_deactivating_a_company_is_written_to_audit_log(): void
    {
        $super = $this->superAdmin();
        $company = $this->company();

        $this->actingAs($super)->patch(route('platform.companies.toggle', $company));

        $this->assertFalse($company->fresh()->is_active);
        $this->assertDatabaseHas('subscription_audit_logs', [
            'company_id' => $company->id,
            'action' => 'deactivated',
            'actor_id' => $super->id,
        ]);
    }

    public function test_reminder_subject_matches_the_actual_days_left(): void
    {
        // A 3-day trial must not be told it ends in 7 days.
        $company = $this->company();

        $mail = new SubscriptionExpiryReminderMail($company, '7d');

        $this->assertSame('Your FrameQuote access ends in 3 days', $mail->build()->subject);
    }

    public function test_expired_paid_company_sees_renewal_wording_not_trial_wording(): void
    {
        $user = $this->admin($this->company([
            'subscription_status' => 'Active',
            'trial_ends_at' => null,
            'subscription_ends_at' => now()->subDays(10),
        ]));

        $this->actingAs($user)
            ->get(route('subscription.expired'))
            ->assertOk()
            ->assertSee('Your Subscription Has Ended')
            ->assertDontSee('Free Trial Has Ended');
    }
}
