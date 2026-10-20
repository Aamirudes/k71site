<?php

namespace App\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

/**
 * Global scope that filters queries by vendor_id when in a vendor context.
 * Does NOT apply in admin context or when no vendor is authenticated.
 * This is the primary mechanism for tenant data isolation.
 */
class VendorScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        // Only apply in vendor dashboard context (not public pages, not admin)
        if ($this->shouldApply()) {
            $vendor = auth()->user()?->vendor;
            if ($vendor) {
                $builder->where($model->getTable() . '.vendor_id', $vendor->id);
            }
        }
    }

    protected function shouldApply(): bool
    {
        // Don't apply if user is not logged in
        if (!auth()->check()) {
            return false;
        }

        $user = auth()->user();

        // Don't apply for admin users (they see everything)
        if ($user->hasRole('admin') || $user->hasRole('staff')) {
            return false;
        }

        // Only apply when in vendor dashboard context
        // This is set by the vendor dashboard middleware
        return (bool) request()->attributes->get('vendor_context', false);
    }
}
