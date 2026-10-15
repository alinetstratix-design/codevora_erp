<?php

namespace App\Http\Controllers\Web\Platform;

use App\Constants\UserRole;
use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\User;
use App\Services\AccountProvisioningService;
use App\Services\SubscriptionService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * Super Admin view of a single company: its users, seats and limits.
 * Only account details and counts are shown here. Quotation and customer content is never opened.
 */
class PlatformCompanyController extends Controller
{
    protected AccountProvisioningService $accounts;

    protected SubscriptionService $subscriptions;

    public function __construct(AccountProvisioningService $accounts, SubscriptionService $subscriptions)
    {
        $this->accounts = $accounts;
        $this->subscriptions = $subscriptions;
    }

    public function show(Company $company)
    {
        $company->loadCount(['users', 'quotations', 'customers']);

        $users = User::where('company_id', $company->id)->orderBy('name')->get();

        return view('platform.company', [
            'company' => $company,
            'users' => $users,
            'roles' => UserRole::companyRoles(),
        ]);
    }

    public function storeUser(Request $request, Company $company)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'role' => ['required', Rule::in(UserRole::companyRoles())],
        ]);

        if ($company->seatsFull()) {
            return back()->withInput()->with('error', "Seat limit reached ({$company->activeSeatCount()}/{$company->seatLimit()}). Increase the seat limit before adding a user.");
        }

        $temp = $this->accounts->generateTempPassword();
        $user = $this->accounts->createUser($company->id, $data['name'], $data['email'], $data['role'], $temp);

        $this->subscriptions->record(
            $company,
            'user_added',
            [],
            ['email' => $user->email, 'role' => $user->role, 'status' => $user->status],
            $request->user(),
            'Added by platform'
        );

        return redirect()->route('platform.companies.show', $company)
            ->with('success', 'User added. Share the temporary password securely.')
            ->with('temp_password', $temp)
            ->with('temp_email', $user->email);
    }

    public function updateUser(Request $request, Company $company, User $user)
    {
        abort_unless($user->company_id === $company->id, 404);

        if ($user->id === $request->user()->id) {
            return back()->with('error', 'You cannot change your own account from here.');
        }

        $data = $request->validate([
            'role' => ['required', Rule::in(UserRole::companyRoles())],
            'status' => ['required', Rule::in(['Active', 'Inactive'])],
        ]);

        $before = ['role' => $user->role, 'status' => $user->status];

        $wasActiveAdmin = $user->role === UserRole::ADMIN && $user->status === 'Active';
        $losesAdmin = $wasActiveAdmin && ($data['role'] !== UserRole::ADMIN || $data['status'] !== 'Active');

        if ($losesAdmin && $this->activeAdminCount($company) <= 1) {
            return back()->withInput()->with('error', 'A company must keep at least one active admin.');
        }

        $reactivating = $user->status !== 'Active' && $data['status'] === 'Active';

        if ($reactivating && $company->seatsFull()) {
            return back()->withInput()->with('error', "Seat limit reached ({$company->activeSeatCount()}/{$company->seatLimit()}). Increase the seat limit before reactivating this user.");
        }

        $user->update($data);

        $this->subscriptions->record(
            $company,
            'user_updated',
            $before,
            ['role' => $user->role, 'status' => $user->status],
            $request->user(),
            "User {$user->email}"
        );

        return back()->with('success', "Updated {$user->email}.");
    }

    public function updateSeats(Request $request, Company $company)
    {
        $data = $request->validate([
            'max_users' => 'required|integer|min:1|max:1000',
        ]);

        $before = ['max_users' => (int) $company->max_users];

        $company->update(['max_users' => $data['max_users']]);

        $this->subscriptions->record(
            $company,
            'seats_changed',
            $before,
            ['max_users' => (int) $company->max_users],
            $request->user()
        );

        return back()->with('success', "Seat limit set to {$company->max_users}.");
    }

    private function activeAdminCount(Company $company): int
    {
        return User::where('company_id', $company->id)
            ->where('role', UserRole::ADMIN)
            ->where('status', 'Active')
            ->count();
    }
}
