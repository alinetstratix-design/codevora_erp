<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Quotation;
use Illuminate\Auth\Access\HandlesAuthorization;

class QuotationPolicy
{
    use HandlesAuthorization;

    public function view(User $user, Quotation $quotation)
    {
        return $user->company_id === $quotation->company_id;
    }

    public function update(User $user, Quotation $quotation)
    {
        if ($quotation->status !== 'Draft') {
            return false;
        }
        return $user->company_id === $quotation->company_id;
    }

    public function delete(User $user, Quotation $quotation)
    {
        if ($quotation->status !== 'Draft') {
            return false;
        }
        return $user->company_id === $quotation->company_id;
    }

    public function approve(User $user, Quotation $quotation)
    {
        // Only Manager or Admin can approve, and they must belong to the company
        if ($user->company_id !== $quotation->company_id) {
            return false;
        }
        return in_array($user->role, ['Manager', 'Admin']);
    }
}
