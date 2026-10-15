<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;

/**
 * Public marketing page for guests. Signed-in users go straight to the dashboard.
 */
class LandingController extends Controller
{
    public function index()
    {
        if (Auth::check()) {
            return Auth::user()->isSuperAdmin()
                ? redirect()->route('platform.index')
                : redirect()->route('dashboard');
        }

        return view('landing');
    }
}
