<?php

namespace Tests\Feature;

use App\Constants\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class LoginWindowTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    private function loginUser(): User
    {
        $user = User::factory()->create([
            'role' => UserRole::SUPER_ADMIN,
            'status' => 'Active',
            'password' => Hash::make('secret-pass-123'),
        ]);

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'secret-pass-123',
        ])->assertRedirect();

        $this->assertAuthenticatedAs($user);

        return $user;
    }

    public function test_session_stays_valid_within_24_hours(): void
    {
        $this->loginUser();

        Carbon::setTestNow(now()->addHours(23));

        $this->get('/platform')->assertOk();
        $this->assertAuthenticated();
    }

    public function test_session_ends_after_24_hours_and_user_must_login_again(): void
    {
        $this->loginUser();

        Carbon::setTestNow(now()->addHours(25));

        $this->get('/platform')->assertRedirect(route('login'));
        $this->assertGuest();
    }

    public function test_activity_does_not_extend_the_24_hour_window(): void
    {
        $this->loginUser();

        Carbon::setTestNow(now()->addHours(12));
        $this->get('/platform');
        $this->assertAuthenticated();

        Carbon::setTestNow(now()->addHours(13)); // 25h after login, 13h after last request
        $this->get('/platform')->assertRedirect(route('login'));
        $this->assertGuest();
    }

    public function test_expired_api_request_gets_401_json(): void
    {
        $this->loginUser();

        Carbon::setTestNow(now()->addHours(25));

        $this->getJson('/platform')->assertStatus(401);
    }

    public function test_login_form_has_no_remember_me_checkbox(): void
    {
        $this->get('/login')->assertDontSee('name="remember"', false);
    }
}
