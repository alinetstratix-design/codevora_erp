<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Every sign-in lasts exactly 24 hours from the moment of login.
 * Activity does not extend it. When the window is over, or the login
 * record is missing from the session store, all local login state is
 * removed and the user must sign in again.
 */
class EnforceLoginWindow
{
    public const LOGIN_AT_KEY = 'auth.login_at';

    public const MAX_AGE_SECONDS = 86400; // 24 hours

    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check()) {
            return $next($request);
        }

        $loginAt = $request->session()->get(self::LOGIN_AT_KEY);

        if (!is_int($loginAt) || (now()->timestamp - $loginAt) >= self::MAX_AGE_SECONDS) {
            return $this->endSession($request);
        }

        return $next($request);
    }

    private function endSession(Request $request): Response
    {
        // logout() also clears the "remember me" cookie if one exists.
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Your session has expired. Please sign in again.'], 401);
        }

        return redirect()->route('login')->withErrors([
            'email' => 'Your 24-hour session has ended. Please sign in again.',
        ]);
    }
}
