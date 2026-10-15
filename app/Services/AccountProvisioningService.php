<?php

namespace App\Services;

use App\Constants\UserRole;
use App\Models\Company;
use App\Models\CompanySetting;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Creates companies and users. Every generated password is temporary and must be changed at first sign-in.
 */
class AccountProvisioningService
{
    public function generateTempPassword(): string
    {
        // 12 characters from a URL-safe alphabet: easy to read and type once.
        return Str::random(12);
    }

    /**
     * Creates a company, its settings row and its first Admin, in one transaction.
     *
     * @return array{0: Company, 1: User, 2: string} company, admin, temporary password
     */
    public function createCompanyWithAdmin(array $company, array $admin): array
    {
        return DB::transaction(function () use ($company, $admin) {
            $companyModel = Company::create([
                'name' => $company['name'],
                'is_active' => true,
            ]);

            CompanySetting::create([
                'company_id' => $companyModel->id,
                'company_name' => $company['name'],
                'email' => $company['email'] ?? null,
                'phone' => $company['phone'] ?? null,
                'gstin' => $company['gstin'] ?? null,
                'default_tax_percent' => 18,
                'terms_conditions' => config('quotation_defaults.terms'),
                'installation_prerequisites' => config('quotation_defaults.installation_prerequisites'),
            ]);

            $temp = $this->generateTempPassword();
            $adminUser = $this->createUser($companyModel->id, $admin['name'], $admin['email'], UserRole::ADMIN, $temp);

            return [$companyModel, $adminUser, $temp];
        });
    }

    public function createUser(int $companyId, string $name, string $email, string $role, string $password): User
    {
        return User::create([
            'company_id' => $companyId,
            'name' => $name,
            'email' => $email,
            'password' => Hash::make($password),
            'role' => $role,
            'status' => 'Active',
            'must_change_password' => true,
        ]);
    }

    /**
     * Replaces the password with a new temporary one and forces a change at next sign-in.
     */
    public function resetPassword(User $user): string
    {
        $temp = $this->generateTempPassword();

        $user->forceFill([
            'password' => Hash::make($temp),
            'must_change_password' => true,
            'remember_token' => Str::random(60),
        ])->save();

        return $temp;
    }
}
