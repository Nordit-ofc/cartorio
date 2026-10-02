<?php

namespace App\Policies;

use App\Models\Proposal;
use App\Models\User;

/** Propostas são leads da nordit: só o super_admin vê e altera (ADR-006). */
class ProposalPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isSuperAdmin();
    }

    public function view(User $user, Proposal $proposal): bool
    {
        return $user->isSuperAdmin();
    }

    public function update(User $user, Proposal $proposal): bool
    {
        return $user->isSuperAdmin();
    }
}
