<?php

namespace App\Policies;

use App\Models\ProjectProposal;
use App\Models\User;

class ProjectProposalPolicy
{
    public function view(User $user, ProjectProposal $proposal): bool
    {
        $isOwner = $user->vendor && $user->vendor->id === $proposal->vendor_id;
        $isProjectBuyer = $proposal->project && $proposal->project->buyer_id === $user->id;
        return $isOwner || $isProjectBuyer || $user->hasRole('admin');
    }

    public function withdraw(User $user, ProjectProposal $proposal): bool
    {
        return $user->vendor && $user->vendor->id === $proposal->vendor_id && in_array($proposal->status, ['pending', 'shortlisted'], true);
    }

    public function decide(User $user, ProjectProposal $proposal): bool
    {
        return ($proposal->project && $proposal->project->buyer_id === $user->id) || $user->hasRole('admin');
    }
}

