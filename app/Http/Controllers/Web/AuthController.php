<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Middleware\EnforceLoginWindow;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $attempt = [
            'email' => $credentials['email'],
            'password' => $credentials['password'],
            'status' => 'Active',
        ];

        if (!Auth::attempt($attempt)) {
            return back()->withErrors([
                'email' => 'The provided credentials do not match our records or the account is inactive.',
            ])->onlyInput('email');
        }

        /** @var User $user */
        $user = Auth::user();

        if (!$user->canSignIn()) {
            Auth::logout();

            return back()->withErrors([
                'email' => 'Your company account is inactive. Please contact Codevora AI support.',
            ])->onlyInput('email');
        }

        $request->session()->regenerate();
        $request->session()->put(EnforceLoginWindow::LOGIN_AT_KEY, now()->timestamp);
        $user->forceFill(['last_login_at' => now()])->save();

        return $user->isSuperAdmin()
            ? redirect()->route('platform.index')
            : redirect()->intended(route('dashboard'));
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'You have been logged out.');
    }

    public function showForgotForm()
    {
        return view('auth.forgot-password');
    }

    /**
     * Always answers the same way, so the form cannot be used to discover which emails have accounts.
     */
    public function sendResetLink(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        try {
            Password::sendResetLink($request->only('email'));
        } catch (\Throwable $e) {
            Log::error('Password reset e-mail failed', ['error' => $e->getMessage()]);

            return back()->withErrors(['email' => 'We could not send the reset e-mail right now. Please try again later.'])
                ->onlyInput('email');
        }

        return back()->with('success', 'If an account exists for this email, a password reset link has been sent.');
    }

    public function showResetForm(Request $request, string $token)
    {
        return view('auth.reset-password', [
            'token' => $token,
            'email' => $request->query('email'),
        ]);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password) {
                $user->forceFill([
                    'password' => Hash::make($password),
                    'must_change_password' => false,
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
            }
        );

        return $status === Password::PASSWORD_RESET
            ? redirect()->route('login')->with('success', 'Your password has been reset. Please sign in.')
            : back()->withErrors(['email' => __($status)])->onlyInput('email');
    }
}
