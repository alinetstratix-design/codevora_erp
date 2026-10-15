<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/**
 * Users created with a temporary password must set their own password before using the system.
 */
class EnsurePasswordChanged
{
    private const ALLOWED_ROUTES = ['account.password.edit', 'account.password.update', 'logout'];

    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if ($user && $user->must_change_password && !$request->routeIs(self::ALLOWED_ROUTES)) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'You must change your password before continuing.'], 403);
            }

            return redirect()->route('account.password.edit')
                ->with('error', 'Please set a new password to continue.');
        }

        return $next($request);
    }
}
