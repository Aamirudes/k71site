<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OnboardingLead extends Model
{
    protected $fillable = [
        'business_name',
        'contact_person',
        'whatsapp_number',
        'phone',
        'email',
        'sector_key',
        'city',
        'status',
        'proposed_plan_id',
        'linked_vendor_id',
        'notes',
        'follow_up_at',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'follow_up_at' => 'datetime',
    ];

    public function linkedVendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class, 'linked_vendor_id');
    }

    public function vendor(): BelongsTo
    {
        return $this->linkedVendor();
    }

    public function proposedPlan(): BelongsTo
    {
        return $this->belongsTo(SubscriptionPlan::class, 'proposed_plan_id');
    }

    public function assignedPlan(): BelongsTo
    {
        return $this->proposedPlan();
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function owner(): BelongsTo
    {
        return $this->creator();
    }
}
