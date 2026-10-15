<?php

namespace App\Http\Controllers\Web;

use App\Constants\UserRole;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\AccountProvisioningService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * Company admin: manages users of their own company only.
 */
class TeamController extends Controller
{
    protected AccountProvisioningService $accounts;

    public function __construct(AccountProvisioningService $accounts)
    {
        $this->accounts = $accounts;
    }

    public function index(Request $request)
    {
        $users = User::where('company_id', $request->user()->company_id)
            ->orderBy('name')
            ->get();

        return view('team.index', compact('users'));
    }

    public function create()
    {
        $roles = UserRole::companyRoles();

        return view('team.create', compact('roles'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'role' => ['required', Rule::in(UserRole::companyRoles())],
        ]);

        $company = $request->user()->company;

        if ($company->seatsFull()) {
            return back()->withInput()->with('error', "User limit reached ({$company->activeSeatCount()}/{$company->seatLimit()}). Please upgrade your subscription to add more team members.");
        }

        $temp = $this->accounts->generateTempPassword();

        $this->accounts->createUser(
            $request->user()->company_id,
            $data['name'],
            $data['email'],
            $data['role'],
            $temp
        );

        return redirect()->route('team.index')
            ->with('success', 'User created. Share the temporary password securely.')
            ->with('temp_password', $temp)
            ->with('temp_email', $data['email']);
    }

    public function edit(Request $request, User $user)
    {
        $this->ensureSameCompany($request, $user);

        $roles = UserRole::companyRoles();

        return view('team.edit', ['member' => $user, 'roles' => $roles]);
    }

    public function update(Request $request, User $user)
    {
        $this->ensureSameCompany($request, $user);

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'role' => ['required', Rule::in(UserRole::companyRoles())],
            'status' => ['required', Rule::in(['Active', 'Inactive'])],
        ]);

        $reactivating = $user->status !== 'Active' && $data['status'] === 'Active';

        if ($reactivating && $request->user()->company->seatsFull()) {
            return back()->withErrors(['status' => 'User limit reached. Deactivate another member or upgrade your plan before reactivating this user.'])->withInput();
        }

        $losesAdminAccess = $data['role'] !== UserRole::ADMIN || $data['status'] !== 'Active';

        if ($user->id === $request->user()->id && $losesAdminAccess) {
            return back()->withErrors(['role' => 'You cannot remove your own admin access.'])->withInput();
        }

        if ($user->role === UserRole::ADMIN && $losesAdminAccess && $this->activeAdminCount($request) <= 1) {
            return back()->withErrors(['role' => 'Your company must keep at least one active admin.'])->withInput();
        }

        $user->update($data);

        return redirect()->route('team.index')->with('success', 'User updated.');
    }

    public function resetPassword(Request $request, User $user)
    {
        $this->ensureSameCompany($request, $user);

        $temp = $this->accounts->resetPassword($user);

        return redirect()->route('team.index')
            ->with('success', 'A new temporary password was generated.')
            ->with('temp_password', $temp)
            ->with('temp_email', $user->email);
    }

    private function ensureSameCompany(Request $request, User $user): void
    {
        // 404 rather than 403, so other companies' user ids cannot be probed.
        abort_unless($user->company_id !== null && $user->company_id === $request->user()->company_id, 404);
    }

    private function activeAdminCount(Request $request): int
    {
        return User::where('company_id', $request->user()->company_id)
            ->where('role', UserRole::ADMIN)
            ->where('status', 'Active')
            ->count();
    }
}
