<?php

namespace App\Console\Commands;

use App\Constants\UserRole;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

/**
 * Bootstraps the platform owner. Run once per installation.
 * Company admins are then created from the Platform console.
 */
class CreateSuperAdmin extends Command
{
    protected $signature = 'users:create-super-admin {--email=} {--name=}';

    protected $description = 'Create the platform Super Admin (no company)';

    public function handle(): int
    {
        if (User::where('role', UserRole::SUPER_ADMIN)->exists()) {
            $this->error('A Super Admin already exists. Use the Platform console to manage companies.');
            return self::FAILURE;
        }

        $email = $this->option('email') ?: $this->ask('Super Admin email');
        $name = $this->option('name') ?: $this->ask('Name', 'Super Admin');
        $password = $this->secret('Password (min 12 characters)');
        $confirm = $this->secret('Confirm password');

        $validator = Validator::make(
            ['email' => $email, 'name' => $name, 'password' => $password, 'password_confirmation' => $confirm],
            [
                'email' => 'required|email|unique:users,email',
                'name' => 'required|string|max:255',
                'password' => 'required|string|min:12|confirmed',
            ]
        );

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }
            return self::FAILURE;
        }

        User::create([
            'company_id' => null,
            'name' => $name,
            'email' => $email,
            'password' => Hash::make($password),
            'role' => UserRole::SUPER_ADMIN,
            'status' => 'Active',
            'must_change_password' => false,
        ]);

        $this->info("Super Admin {$email} created.");

        return self::SUCCESS;
    }
}
