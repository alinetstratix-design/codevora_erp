<?php

namespace App\Http\Controllers\Web\Platform;

use App\Constants\UserRole;
use App\Http\Controllers\Controller;
use App\Mail\TrialApprovedMail;
use App\Models\Company;
use App\Models\CompanySetting;
use App\Models\DemoRequest;
use App\Models\User;
use App\Services\SubscriptionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class DemoRequestManagementController extends Controller
{
    /**
     * List all demo requests for the Super Admin.
     */
    public function index(Request $request)
    {
        $status = $request->query('status', 'all');

        $query = DemoRequest::with(['company', 'approver'])->latest();

        if (in_array($status, ['pending', 'approved', 'rejected'])) {
            $query->where('status', $status);
        }

        $requests = $query->paginate(20)->withQueryString();
        $pendingCount = DemoRequest::where('status', 'pending')->count();

        return view('platform.demo_requests.index', compact('requests', 'status', 'pendingCount'));
    }

    /**
     * Approve a demo request: provision company, admin user, default settings, and email credentials.
     */
    public function approve(Request $request, DemoRequest $demoRequest)
    {
        if ($demoRequest->status !== 'pending') {
            return back()->with('error', 'This request has already been processed.');
        }

        if (User::where('email', $demoRequest->email)->exists()) {
            return back()->with('error', "A user with email '{$demoRequest->email}' already exists.");
        }

        $plainPassword = 'FQ' . Str::upper(Str::random(8));

        try {
            $company = DB::transaction(function () use ($demoRequest, $plainPassword) {
                // 1. Create company with 3-day trial
                $company = Company::create([
                    'name' => $demoRequest->company_name,
                    'is_active' => true,
                    'subscription_status' => 'Trial',
                    'trial_ends_at' => now()->addDays(3),
                    'subscription_ends_at' => now()->addDays(3),
                    'max_users' => 3,
                ]);

                // Audit: the trial starts here
                app(SubscriptionService::class)->record(
                    $company,
                    'trial_started',
                    [],
                    app(SubscriptionService::class)->snapshot($company),
                    auth()->user(),
                    'Approved demo request'
                );

                // 2. Initialize default company settings
                CompanySetting::create([
                    'company_id' => $company->id,
                    'company_name' => $company->name,
                    'email' => $demoRequest->email,
                    'phone' => $demoRequest->phone,
                    'default_tax_percent' => 18.00,
                    'terms_conditions' => config('quotation_defaults.terms', []),
                    'installation_prerequisites' => config('quotation_defaults.installation_prerequisites', []),
                ]);

                // 3. Create Company Admin user
                $user = User::create([
                    'company_id' => $company->id,
                    'name' => $demoRequest->name,
                    'email' => $demoRequest->email,
                    'password' => Hash::make($plainPassword),
                    'role' => UserRole::ADMIN,
                    'status' => 'Active',
                    'must_change_password' => true,
                ]);

                // 4. Mark request as approved
                $demoRequest->update([
                    'status' => 'approved',
                    'company_id' => $company->id,
                    'approved_by' => auth()->id(),
                    'approved_at' => now(),
                    'admin_notes' => request('admin_notes', 'Approved by platform administrator'),
                ]);

                return $company;
            });

            // 5. Attempt sending credentials via email
            $mailSent = false;
            try {
                $user = User::where('email', $demoRequest->email)->first();
                if ($user) {
                    Mail::to($user->email)->send(new TrialApprovedMail($demoRequest, $company, $user, $plainPassword));
                    $mailSent = true;
                }
            } catch (\Throwable $e) {
                Log::warning('Trial welcome email could not be delivered: ' . $e->getMessage());
            }

            $msg = $mailSent
                ? "Trial approved for {$demoRequest->company_name}. Credentials were emailed to {$demoRequest->email}."
                : "Trial approved for {$demoRequest->company_name}, but the email could not be sent. " .
                  "Share these credentials securely now: Email {$demoRequest->email} | Temp Password {$plainPassword}";

            return redirect()->route('platform.demo-requests.index')->with('success', $msg);

        } catch (\Throwable $e) {
            Log::error('Demo approval failed: ' . $e->getMessage(), ['exception' => $e]);
            return back()->with('error', 'Error approving request: ' . $e->getMessage());
        }
    }

    /**
     * Reject a demo request.
     */
    public function reject(Request $request, DemoRequest $demoRequest)
    {
        if ($demoRequest->status !== 'pending') {
            return back()->with('error', 'Only pending requests can be rejected.');
        }

        $demoRequest->update([
            'status' => 'rejected',
            'admin_notes' => $request->input('admin_notes', 'Rejected by administrator'),
        ]);

        return redirect()->route('platform.demo-requests.index')->with('success', "Request for {$demoRequest->company_name} rejected.");
    }

    /**
     * Extend or activate a company subscription from platform console.
     */
    public function updateSubscription(Request $request, Company $company)
    {
        $action = $request->input('action'); // extend_3d, extend_30d, activate_paid, suspend
        $service = app(SubscriptionService::class);
        $actor = $request->user();

        try {
            switch ($action) {
                case 'extend_3d':
                    $service->extendTrial($company, $actor);
                    $msg = "Trial extended by 3 days for {$company->name}.";
                    break;

                case 'extend_30d':
                case 'activate_paid':
                    $service->activatePaid($company, $actor);
                    $msg = "Subscription activated/extended for 30 days for {$company->name}.";
                    break;

                case 'suspend':
                    $service->suspend($company, $actor);
                    $msg = "Company {$company->name} suspended.";
                    break;

                default:
                    return back()->with('error', 'Invalid action specified.');
            }
        } catch (\DomainException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', $msg);
    }
}
