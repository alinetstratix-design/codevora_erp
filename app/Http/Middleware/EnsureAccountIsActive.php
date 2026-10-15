<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Signs the user out immediately when the account or its company is deactivated,
 * so a disabled user loses access on the next request, not at the next login.
 */
class EnsureAccountIsActive
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if ($user && !$user->canSignIn()) {
            if ($request->expectsJson() || $request->is('api/*')) {
                $request->user()->currentAccessToken()?->delete();

                return response()->json([
                    'message' => 'Your account or company is inactive. Please contact your administrator.',
                    'account_inactive' => true,
                ], 403);
            }

            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->withErrors([
                'email' => 'Your account or company is inactive. Please contact your administrator.',
            ]);
        }

        return $next($request);
    }
}
