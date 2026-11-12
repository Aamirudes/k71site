<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderCommission extends Model
{
    protected $fillable = [
        'transaction_type',
        'transaction_id',
        'order_number',
        'vendor_id',
        'sector_key',
        'subscription_id',
        'plan_id',
        'plan_version_number',
        'commission_rule_id',
        'commission_rule_version',
        'rule_source',
        'commission_base_paise',
        'tax_paise',
        'shipping_paise',
        'discount_paise',
        'rate_percent',
        'fixed_fee_paise',
        'calculated_commission_paise',
        'vendor_net_paise',
        'event_type',
        'refund_state',
        'settlement_status',
        'is_manual_override',
        'override_reason',
        'notes',
        'rule_snapshot_json',
        'calculated_at',
        'created_by',
    ];

    protected $casts = [
        'commission_base_paise' => 'integer',
        'tax_paise' => 'integer',
        'shipping_paise' => 'integer',
        'discount_paise' => 'integer',
        'rate_percent' => 'decimal:2',
        'fixed_fee_paise' => 'integer',
        'calculated_commission_paise' => 'integer',
        'vendor_net_paise' => 'integer',
        'plan_version_number' => 'integer',
        'commission_rule_version' => 'integer',
        'is_manual_override' => 'boolean',
        'rule_snapshot_json' => 'array',
        'calculated_at' => 'datetime',
    ];

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(VendorSubscription::class, 'subscription_id');
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(SubscriptionPlan::class, 'plan_id');
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'transaction_id');
    }

    public function commissionRule(): BelongsTo
    {
        return $this->belongsTo(CommissionRule::class, 'commission_rule_id');
    }

    public function rule(): BelongsTo
    {
        return $this->commissionRule();
    }
}
