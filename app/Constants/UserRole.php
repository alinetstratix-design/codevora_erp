<?php

namespace App\Constants;

/**
 * SuperAdmin: platform owner (Codevora AI). No company. Creates companies and their first Admin.
 * Admin:      company owner. Full ERP access for their company, manages their team.
 * Manager:    products, master data, approvals.
 * Staff:      quotations and customers only.
 */
class UserRole
{
    const SUPER_ADMIN = 'SuperAdmin';
    const ADMIN = 'Admin';
    const MANAGER = 'Manager';
    const STAFF = 'Staff';

    /** Roles a company admin may assign. SuperAdmin can never be created from inside a company. */
    public static function companyRoles(): array
    {
        return [self::ADMIN, self::MANAGER, self::STAFF];
    }
}
