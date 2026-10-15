<?php

namespace App\Http\Controllers;

use App\Models\DemoRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;

class DemoRequestController extends Controller
{
    /**
     * Handle public demo / trial requests submitted from the landing page.
     */
    public function store(Request $request)
    {
        // Honeypot: real visitors never fill this hidden field. Bots get a fake success and nothing is stored.
        if ($request->filled('website')) {
            return response()->json([
                'success' => true,
                'message' => 'Your 3-day free trial request has been submitted successfully!',
            ]);
        }

        // Turnstile verification, only when the site has configured keys.
        $secret = config('services.turnstile.secret');
        if ($secret) {
            $token = (string) $request->input('cf-turnstile-response', '');
            $verified = $token !== '' && Http::asForm()->timeout(5)
                ->post('https://challenges.cloudflare.com/turnstile/v0/siteverify', [
                    'secret' => $secret,
                    'response' => $token,
                    'remoteip' => $request->ip(),
                ])->json('success') === true;

            if (!$verified) {
                return response()->json([
                    'success' => false,
                    'message' => 'Security check failed. Please complete the verification and try again.',
                ], 422);
            }
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:150',
            'company_name' => 'required|string|max:150',
            'email' => 'required|email|max:150',
            'phone' => 'required|string|max:30',
            'location' => 'nullable|string|max:150',
            'fabrication_type' => 'nullable|string|max:64',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $email = strtolower(trim($request->input('email')));

        // Check if user already exists
        if (User::where('email', $email)->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'An account with this email address already exists. Please sign in or use password recovery.',
            ], 422);
        }

        // Check if there is already an active pending request
        $existing = DemoRequest::where('email', $email)
            ->where('status', 'pending')
            ->first();

        if ($existing) {
            return response()->json([
                'success' => true,
                'message' => 'A trial request for this email is already under review. Our team will email your credentials shortly.',
            ]);
        }

        DemoRequest::create([
            'name' => trim($request->input('name')),
            'company_name' => trim($request->input('company_name')),
            'email' => $email,
            'phone' => trim($request->input('phone')),
            'location' => trim((string)$request->input('location')),
            'fabrication_type' => $request->input('fabrication_type', 'Both'),
            'status' => 'pending',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Your 3-day free trial request has been submitted successfully! Our team will review and email your credentials shortly.',
        ]);
    }
}
