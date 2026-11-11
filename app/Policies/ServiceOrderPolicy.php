<?php

namespace App\Policies;

use App\Models\ServiceOrder;
use App\Models\User;

class ServiceOrderPolicy
{
    public function view(User $user, ServiceOrder $order): bool
    {
        $isBuyer = $user->id === $order->buyer_id;
        $isVendor = $user->vendor && $user->vendor->id === $order->vendor_id;
        return $isBuyer || $isVendor || $user->hasRole('admin');
    }

    public function deliver(User $user, ServiceOrder $order): bool
    {
        return ($user->vendor && $user->vendor->id === $order->vendor_id) || $user->hasRole('admin');
    }

    public function approve(User $user, ServiceOrder $order): bool
    {
        return $user->id === $order->buyer_id || $user->hasRole('admin');
    }
}

