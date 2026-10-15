<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

/**
 * Admin-only user management, strictly limited to the admin's own company.
 */
class UserController extends Controller
{
    public const ROLES = ['Admin', 'Manager', 'Staff'];
    public const STATUSES = ['Active', 'Inactive'];

    protected function companyUsers(Request $request)
    {
        return User::query()->where('company_id', $request->user()->company_id);
    }

    public function index(Request $request)
    {
        return response()->json($this->companyUsers($request)->get(['id', 'name', 'email', 'role', 'status', 'company_id']));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email',
            'password' => 'required|string|min:8',
            'role' => ['required', Rule::in(self::ROLES)],
            'status' => ['nullable', Rule::in(self::STATUSES)],
        ]);

        $company = $request->user()->company;
        $creatingActive = ($data['status'] ?? 'Active') === 'Active';

        if ($creatingActive && $company->seatsFull()) {
            return response()->json([
                'message' => "User limit reached ({$company->activeSeatCount()}/{$company->seatLimit()}). Please upgrade your plan.",
            ], 422);
        }

        $user = User::create([
            'company_id' => $request->user()->company_id,
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role' => $data['role'],
            'status' => $data['status'] ?? 'Active',
        ]);

        return response()->json($user->only(['id', 'name', 'email', 'role', 'status']), 201);
    }

    public function show(Request $request, $id)
    {
        $user = $this->companyUsers($request)->findOrFail($id);
        return response()->json($user->only(['id', 'name', 'email', 'role', 'status']));
    }

    public function update(Request $request, $id)
    {
        $user = $this->companyUsers($request)->findOrFail($id);

        $data = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'email' => ['sometimes', 'required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'password' => 'nullable|string|min:8',
            'role' => ['sometimes', 'required', Rule::in(self::ROLES)],
            'status' => ['sometimes', 'required', Rule::in(self::STATUSES)],
        ]);

        if (!empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        $company = $request->user()->company;
        $reactivating = $user->status !== 'Active' && ($data['status'] ?? $user->status) === 'Active';

        if ($reactivating && $company->seatsFull()) {
            return response()->json([
                'message' => "User limit reached ({$company->activeSeatCount()}/{$company->seatLimit()}). Deactivate another member or upgrade your plan first.",
            ], 422);
        }

        // An admin must not be able to demote or deactivate themselves (lock-out).
        if ($user->id === $request->user()->id && (($data['role'] ?? 'Admin') !== 'Admin' || ($data['status'] ?? 'Active') !== 'Active')) {
            return response()->json(['message' => 'You cannot remove your own admin access.'], 422);
        }

        $user->update($data);

        return response()->json($user->only(['id', 'name', 'email', 'role', 'status']));
    }

    public function destroy(Request $request, $id)
    {
        $user = $this->companyUsers($request)->findOrFail($id);

        if ($user->id === $request->user()->id) {
            return response()->json(['message' => 'You cannot delete your own account.'], 422);
        }

        $user->delete();

        return response()->json(null, 204);
    }
}
