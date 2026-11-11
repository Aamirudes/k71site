<?php

namespace App\Policies;

use App\Models\PortfolioProject;
use App\Models\User;

class PortfolioProjectPolicy
{
    public function viewAny(User $user): bool
    {
        return (bool) $user->vendor;
    }

    public function view(User $user, PortfolioProject $project): bool
    {
        return $project->status === 'published'
            || ($user->vendor && $user->vendor->id === $project->vendor_id)
            || $user->hasRole('admin');
    }

    public function create(User $user): bool
    {
        return (bool) $user->vendor && $user->vendor->isServices();
    }

    public function update(User $user, PortfolioProject $project): bool
    {
        return ($user->vendor && $user->vendor->id === $project->vendor_id) || $user->hasRole('admin');
    }

    public function delete(User $user, PortfolioProject $project): bool
    {
        return ($user->vendor && $user->vendor->id === $project->vendor_id) || $user->hasRole('admin');
    }
}

