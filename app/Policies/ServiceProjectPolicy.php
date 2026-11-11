<?php

namespace App\Policies;

use App\Models\ServiceProject;
use App\Models\User;

class ServiceProjectPolicy
{
    public function view(User $user, ServiceProject $project): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, ServiceProject $project): bool
    {
        return $user->id === $project->buyer_id || $user->hasRole('admin');
    }

    public function manageProposals(User $user, ServiceProject $project): bool
    {
        return $user->id === $project->buyer_id || $user->hasRole('admin');
    }

    public function submitProposal(User $user, ServiceProject $project): bool
    {
        $vendor = $user->vendor;
        if (!$vendor || !$vendor->isServices()) {
            return false;
        }
        return $project->buyer_id !== $user->id && in_array($project->status, ['open', 'shortlisting'], true);
    }
}

