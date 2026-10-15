<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/**
 * Access levels per company:
 *  full      - normal access
 *  read_only - subscription ended, inside the grace window: GET allowed, writes blocked
 *  locked    - no access: redirected to the expiry screen (JSON 403 for API)
 */
class EnsureSubscriptionIsActive
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if (!$user || $user->isSuperAdmin()) {
            return $next($request);
        }

        $company = $user->company;
        $level = $company ? $company->accessLevel() : 'locked';

        if ($level === 'full') {
            return $next($request);
        }

        // Always reachable: expiry screen, password change, logout.
        if ($request->routeIs('subscription.expired', 'account.password.*', 'logout')) {
            return $next($request);
        }

        $isRead = in_array($request->method(), ['GET', 'HEAD', 'OPTIONS'], true);

        if ($level === 'read_only') {
            if ($isRead) {
                return $next($request);
            }

            $message = 'Your subscription has ended. Your data is read-only until you renew.';

            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'subscription_expired' => true,
                    'read_only' => true,
                    'message' => $message,
                ], 403);
            }

            return back()->with('error', $message);
        }

        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'subscription_expired' => true,
                'message' => 'Your workspace subscription or trial has expired. Please contact support to upgrade.',
            ], 403);
        }

        return redirect()->route('subscription.expired');
    }
}
