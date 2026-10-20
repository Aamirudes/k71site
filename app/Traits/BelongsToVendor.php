<?php

namespace App\Traits;

use App\Models\Vendor;
use App\Scopes\VendorScope;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Trait for models that belong to a vendor.
 * Automatically applies a global scope to filter by the current vendor,
 * and auto-fills vendor_id on creation.
 */
trait BelongsToVendor
{
    public static function bootBelongsToVendor(): void
    {
        // Apply global scope only in vendor context (not admin)
        static::addGlobalScope(new VendorScope());

        // Auto-fill vendor_id on creating
        static::creating(function ($model) {
            if (empty($model->vendor_id) && auth()->check()) {
                $vendor = auth()->user()->vendor;
                if ($vendor) {
                    $model->vendor_id = $vendor->id;
                }
            }
        });
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }
}
