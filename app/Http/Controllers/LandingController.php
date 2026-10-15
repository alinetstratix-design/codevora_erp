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
            return redirect()->route('dashboard');
        }

        return view('landing');
    }
}
