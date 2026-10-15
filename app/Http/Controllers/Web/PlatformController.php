<?php

namespace App\Http\Controllers\Web;

use App\Constants\UserRole;
use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\DemoRequest;
use App\Models\User;
use App\Services\AccountProvisioningService;
use App\Services\SubscriptionService;
use Illuminate\Http\Request;

/**
 * Super admin console: creates companies, each with its own first Admin account.
 */
class PlatformController extends Controller
{
    protected AccountProvisioningService $accounts;

    public function __construct(AccountProvisioningService $accounts)
    {
        $this->accounts = $accounts;
    }

    public function index()
    {
        $companies = Company::withCount('users')
            ->with(['users' => fn ($q) => $q->where('role', UserRole::ADMIN)->orderBy('id')])
            ->orderBy('name')
            ->get();

        $onTrial = 0;
        $onPaid = 0;
        $ended = 0;
        $expiringSoon = collect();

        foreach ($companies as $company) {
            if ($company->isTrialActive()) {
                $onTrial++;
            }

            if ($company->isSubscriptionActive()) {
                $onPaid++;
            }

            $onPlan = in_array($company->subscription_status, ['Trial', 'Active'], true);

            if ($company->is_active && $onPlan && $company->accessLevel() === 'locked') {
                $ended++;
            }

            $expiry = $company->expiryDate();

            if ($company->is_active && $onPlan && $expiry && $expiry->isFuture() && $expiry->lte(now()->addDays(7))) {
                $expiringSoon->push($company);
            }
        }

        $stats = [
            'companies' => $companies->count(),
            'active_companies' => $companies->where('is_active', true)->count(),
            'on_trial' => $onTrial,
            'on_paid' => $onPaid,
            'expiring_soon' => $expiringSoon->count(),
            'ended' => $ended,
            'company_users' => User::whereNotNull('company_id')->count(),
            'active_users' => User::whereNotNull('company_id')->where('status', 'Active')->count(),
            'pending_requests' => DemoRequest::where('status', 'pending')->count(),
        ];

        $pendingRequests = DemoRequest::where('status', 'pending')->latest()->limit(5)->get();

        return view('platform.index', compact('companies', 'stats', 'pendingRequests', 'expiringSoon'));
    }

    public function create()
    {
        return view('platform.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'company_name' => 'required|string|max:255|unique:companies,name',
            'company_email' => 'nullable|email|max:255',
            'company_phone' => 'nullable|string|max:30',
            'company_gstin' => 'nullable|string|max:20',
            'admin_name' => 'required|string|max:255',
            'admin_email' => 'required|email|max:255|unique:users,email',
        ]);

        [, $admin, $temp] = $this->accounts->createCompanyWithAdmin(
            [
                'name' => $data['company_name'],
                'email' => $data['company_email'] ?? null,
                'phone' => $data['company_phone'] ?? null,
                'gstin' => $data['company_gstin'] ?? null,
            ],
            ['name' => $data['admin_name'], 'email' => $data['admin_email']]
        );

        return redirect()->route('platform.index')
            ->with('success', "Company \"{$data['company_name']}\" created with admin {$admin->email}.")
            ->with('temp_password', $temp)
            ->with('temp_email', $admin->email);
    }

    public function toggleActive(Request $request, Company $company)
    {
        app(SubscriptionService::class)->setActive($company, !$company->is_active, $request->user());

        $message = $company->is_active
            ? 'Company activated. Its users can sign in again.'
            : 'Company deactivated. Its users are signed out and cannot sign in.';

        return back()->with('success', $message);
    }

    public function resetAdminPassword(Company $company, User $user)
    {
        abort_unless($user->company_id === $company->id, 404);

        $temp = $this->accounts->resetPassword($user);

        return back()
            ->with('success', "Temporary password generated for {$user->email}.")
            ->with('temp_password', $temp)
            ->with('temp_email', $user->email);
    }
}
