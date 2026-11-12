<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SubscriptionRenewalRequest extends Model
{
    protected $fillable = [
        'vendor_id',
        'vendor_subscription_id',
        'sector_key',
        'requested_plan_id',
        'message',
        'status',
        'admin_notes',
        'admin_reply',
        'replied_at',
        'replied_by',
        'rejection_reason',
        'renewed_subscription_id',
        'reviewed_by',
        'reviewed_at',
    ];

    protected $casts = [
        'reviewed_at' => 'datetime',
        'replied_at' => 'datetime',
    ];

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(VendorSubscription::class, 'vendor_subscription_id');
    }

    public function currentSubscription(): BelongsTo
    {
        return $this->subscription();
    }

    public function requestedPlan(): BelongsTo
    {
        return $this->belongsTo(SubscriptionPlan::class, 'requested_plan_id');
    }

    public function handledBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}
