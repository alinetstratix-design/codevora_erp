<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SubscriptionController extends Controller
{
    /**
     * Display the locked / expired subscription view when a 3-day trial or plan ends.
     */
    public function expired(Request $request)
    {
        $user = $request->user();

        // If user already has active access or is SuperAdmin, redirect to dashboard
        if (!$user || $user->isSuperAdmin() || ($user->company && $user->company->accessLevel() !== 'locked')) {
            return redirect()->route('dashboard');
        }

        $company = $user->company;

        return view('subscription.expired', compact('user', 'company'));
    }
}
