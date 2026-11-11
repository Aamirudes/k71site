<?php

namespace App\Policies;

use App\Models\ProjectContract;
use App\Models\User;

class ProjectContractPolicy
{
    public function view(User $user, ProjectContract $contract): bool
    {
        $isBuyer = $user->id === $contract->buyer_id;
        $isVendor = $user->vendor && $user->vendor->id === $contract->vendor_id;
        return $isBuyer || $isVendor || $user->hasRole('admin');
    }

    public function submitMilestone(User $user, ProjectContract $contract): bool
    {
        return ($user->vendor && $user->vendor->id === $contract->vendor_id) || $user->hasRole('admin');
    }

    public function reviewMilestone(User $user, ProjectContract $contract): bool
    {
        return $user->id === $contract->buyer_id || $user->hasRole('admin');
    }

    public function complete(User $user, ProjectContract $contract): bool
    {
        return $user->id === $contract->buyer_id || $user->hasRole('admin');
    }
}

