<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class CommissionRule extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'scope',
        'sector_key',
        'plan_id',
        'vendor_id',
        'category_id',
        'rate_percent',
        'fixed_fee_paise',
        'is_active',
        'effective_from',
        'effective_until',
        'priority',
        'version',
        'notes',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'rate_percent' => 'decimal:2',
        'fixed_fee_paise' => 'integer',
        'is_active' => 'boolean',
        'effective_from' => 'datetime',
        'effective_until' => 'datetime',
        'priority' => 'integer',
        'version' => 'integer',
    ];

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(SubscriptionPlan::class, 'plan_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }
}
