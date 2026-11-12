<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class VendorSubscription extends Model
{
    public const STATUS_PENDING_ACTIVATION = 'pending_activation';
    public const STATUS_ACTIVE = 'active';
    public const STATUS_GRACE_PERIOD = 'grace_period';
    public const STATUS_EXPIRED = 'expired';
    public const STATUS_CANCELLED = 'cancelled';
    public const STATUS_SUSPENDED = 'suspended';

    protected $fillable = [
        'vendor_id',
        'plan_id',
        'sector_key',
        'applicable_sectors_snapshot',
        'plan_version_id',
        'plan_version_number',
        'plan_name_snapshot',
        'price_amount_snapshot',
        'is_free_snapshot',
        'currency',
        'duration_days',
        'entitlements_snapshot',
        'expiry_policy_snapshot',
        'status',
        'starts_at',
        'ends_at',
        'trial_ends_at',
        'grace_ends_at',
        'grace_keep_visible',
        'grace_keep_features',
        'cancelled_at',
        'expired_at',
        'suspended_at',
        'commission_rule_id',
        'commission_rate_override',
        'commission_fixed_override',
        'reminder_days_before',
        'auto_reminder_enabled',
        'external_payment_received',
        'external_payment_date',
        'external_payment_amount',
        'external_payment_method',
        'external_payment_ref',
        'external_receipt_path',
        'internal_notes',
        'admin_reason',
        'assigned_by',
        'activated_by',
        'activated_at',
        'renewed_from_id',
    ];

    protected $casts = [
        'applicable_sectors_snapshot' => 'array',
        'plan_version_number' => 'integer',
        'price_amount_snapshot' => 'integer',
        'is_free_snapshot' => 'boolean',
        'duration_days' => 'integer',
        'entitlements_snapshot' => 'array',
        'expiry_policy_snapshot' => 'array',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'trial_ends_at' => 'datetime',
        'grace_ends_at' => 'datetime',
        'grace_keep_visible' => 'boolean',
        'grace_keep_features' => 'boolean',
        'cancelled_at' => 'datetime',
        'expired_at' => 'datetime',
        'suspended_at' => 'datetime',
        'commission_rate_override' => 'decimal:2',
        'commission_fixed_override' => 'integer',
        'reminder_days_before' => 'array',
        'auto_reminder_enabled' => 'boolean',
        'external_payment_received' => 'boolean',
        'external_payment_date' => 'datetime',
        'external_payment_amount' => 'integer',
        'activated_at' => 'datetime',
    ];

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(SubscriptionPlan::class, 'plan_id');
    }

    public function planVersion(): BelongsTo
    {
        return $this->belongsTo(SubscriptionPlanVersion::class, 'plan_version_id');
    }

    public function commissionRule(): BelongsTo
    {
        return $this->belongsTo(CommissionRule::class, 'commission_rule_id');
    }

    public function assignedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    public function activatedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'activated_by');
    }

    public function events(): HasMany
    {
        return $this->hasMany(SubscriptionEvent::class, 'vendor_subscription_id')->latest('id');
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(SubscriptionNotification::class, 'vendor_subscription_id')->latest('id');
    }

    public function isEffectivelyActive(): bool
    {
        if ($this->status === self::STATUS_ACTIVE) {
            return $this->ends_at === null || $this->ends_at->isFuture();
        }
        if ($this->status === self::STATUS_GRACE_PERIOD) {
            return $this->grace_keep_features && ($this->grace_ends_at === null || $this->grace_ends_at->isFuture());
        }
        return false;
    }

    public function isPubliclyVisible(): bool
    {
        if ($this->status === self::STATUS_ACTIVE) {
            return $this->ends_at === null || $this->ends_at->isFuture();
        }
        if ($this->status === self::STATUS_GRACE_PERIOD) {
            return (bool) $this->grace_keep_visible && ($this->grace_ends_at === null || $this->grace_ends_at->isFuture());
        }
        if ($this->status === self::STATUS_EXPIRED) {
            $policy = $this->expiry_policy_snapshot ?? [];
            $hideStorefront = $policy['hide_storefront'] ?? true;
            $hideListings = $policy['hide_listings'] ?? true;
            return !$hideStorefront && !$hideListings;
        }
        return false;
    }

    public function coversSector(string $sectorKey): bool
    {
        if ($this->sector_key === $sectorKey || $this->sector_key === 'all') {
            return true;
        }
        $applicable = $this->applicable_sectors_snapshot ?? [];
        return in_array($sectorKey, $applicable, true) || in_array('all', $applicable, true);
    }

    public function getDaysRemainingAttribute(): ?int
    {
        if (!$this->ends_at) {
            return null;
        }
        if ($this->ends_at->isPast()) {
            return 0;
        }
        return (int) now()->startOfDay()->diffInDays($this->ends_at->copy()->startOfDay(), false);
    }
}
