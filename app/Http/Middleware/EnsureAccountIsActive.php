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
