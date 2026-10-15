<?php

namespace App\Models;

use App\Constants\UserRole;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'company_id',
        'name',
        'email',
        'password',
        'role',
        'status',
        'must_change_password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'last_login_at' => 'datetime',
        'must_change_password' => 'boolean',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    public function isSuperAdmin(): bool
    {
        return $this->role === UserRole::SUPER_ADMIN;
    }

    public function isCompanyAdmin(): bool
    {
        return $this->role === UserRole::ADMIN;
    }

    /**
     * A user may use the system only when the account is Active and,
     * for company users, the company itself is active.
     */
    public function canSignIn(): bool
    {
        if ($this->status !== 'Active') {
            return false;
        }

        if ($this->isSuperAdmin()) {
            return true;
        }

        return (bool) optional($this->company)->is_active;
    }
}
