<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class SubscriptionPlan extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'code',
        'name',
        'slug',
        'description',
        'sector_key',
        'applicable_sectors',
        'is_cross_sector',
        'is_free',
        'price_amount',
        'price_monthly',
        'price_yearly',
        'currency',
        'duration_days',
        'custom_duration_allowed',
        'max_products',
        'max_images_per_product',
        'features',
        'default_commission_rule_id',
        'status',
        'is_active',
        'is_public_display',
        'sort_order',
        'trial_days',
        'current_version',
        'admin_notes',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'applicable_sectors' => 'array',
        'is_cross_sector' => 'boolean',
        'is_free' => 'boolean',
        'price_amount' => 'integer',
        'price_monthly' => 'integer',
        'price_yearly' => 'integer',
        'duration_days' => 'integer',
        'custom_duration_allowed' => 'boolean',
        'max_products' => 'integer',
        'max_images_per_product' => 'integer',
        'features' => 'array',
        'is_active' => 'boolean',
        'is_public_display' => 'boolean',
        'sort_order' => 'integer',
        'trial_days' => 'integer',
        'current_version' => 'integer',
    ];

    public function versions(): HasMany
    {
        return $this->hasMany(SubscriptionPlanVersion::class, 'plan_id')->orderByDesc('version_number');
    }

    public function latestVersion(): ?SubscriptionPlanVersion
    {
        return $this->versions()->where('version_number', $this->current_version)->first()
            ?? $this->versions()->first();
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(VendorSubscription::class, 'plan_id');
    }

    public function defaultCommissionRule(): BelongsTo
    {
        return $this->belongsTo(CommissionRule::class, 'default_commission_rule_id');
    }

    public function commissionRules(): HasMany
    {
        return $this->hasMany(CommissionRule::class, 'plan_id');
    }

    public function coversSector(string $sectorKey): bool
    {
        if ($this->sector_key === $sectorKey || $this->sector_key === 'all') {
            return true;
        }
        $applicable = $this->applicable_sectors ?? [];
        if ($this->is_cross_sector && (in_array($sectorKey, $applicable, true) || in_array('all', $applicable, true))) {
            return true;
        }
        return in_array($sectorKey, $applicable, true);
    }

    public function getFormattedPriceAttribute(): string
    {
        if ($this->is_free || (int) $this->price_amount <= 0) {
            return 'Free';
        }
        return '₹' . number_format($this->price_amount / 100, 2);
    }
}
