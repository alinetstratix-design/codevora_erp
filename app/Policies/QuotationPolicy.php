<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Quotation;
use Illuminate\Auth\Access\HandlesAuthorization;

class QuotationPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can approve the quotation.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Quotation  $quotation
     * @return mixed
     */
    public function approve(User $user, Quotation $quotation)
    {
        // Only Manager or Admin can approve
        return in_array($user->role, ['Manager', 'Admin']);
    }
}
