<?php

namespace App\Console\Commands;

use App\Models\Company;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

/**
 * Creates the first administrator. Replaces the old seeder that set a known default password.
 */
class CreateAdminUser extends Command
{
    protected $signature = 'users:create-admin {--email=} {--name=} {--company=}';

    protected $description = 'Create an Admin user (and the company, if none exists yet)';

    public function handle(): int
    {
        $email = $this->option('email') ?: $this->ask('Admin email');
        $name = $this->option('name') ?: $this->ask('Admin name', 'Administrator');
        $password = $this->secret('Admin password (min 8 characters)');
        $confirm = $this->secret('Confirm password');

        $validator = Validator::make(
            ['email' => $email, 'name' => $name, 'password' => $password, 'password_confirmation' => $confirm],
            ['email' => 'required|email|unique:users,email', 'name' => 'required|string|max:255', 'password' => 'required|string|min:8|confirmed']
        );

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }
            return self::FAILURE;
        }

        $company = Company::first();
        if (!$company) {
            $companyName = $this->option('company') ?: $this->ask('Company name');
            $company = Company::create(['name' => $companyName, 'is_active' => true]);
        }

        User::create([
            'company_id' => $company->id,
            'name' => $name,
            'email' => $email,
            'password' => Hash::make($password),
            'role' => 'Admin',
            'status' => 'Active',
        ]);

        $this->info("Admin user {$email} created for company '{$company->name}'.");

        return self::SUCCESS;
    }
}
