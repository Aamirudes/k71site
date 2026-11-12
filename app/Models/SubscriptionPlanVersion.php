<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SubscriptionPlanVersion extends Model
{
    protected $fillable = [
        'plan_id',
        'version_number',
        'name',
        'description',
        'is_free',
        'price_amount',
        'currency',
        'duration_days',
        'default_commission_rule_id',
        'entitlements_json',
        'expiry_policy_json',
        'promotional_benefits_json',
        'change_notes',
        'created_by',
    ];

    protected $casts = [
        'version_number' => 'integer',
        'is_free' => 'boolean',
        'price_amount' => 'integer',
        'duration_days' => 'integer',
        'entitlements_json' => 'array',
        'expiry_policy_json' => 'array',
        'promotional_benefits_json' => 'array',
    ];

    public function plan(): BelongsTo
    {
        return $this->belongsTo(SubscriptionPlan::class, 'plan_id');
    }

    public function defaultCommissionRule(): BelongsTo
    {
        return $this->belongsTo(CommissionRule::class, 'default_commission_rule_id');
    }
}
