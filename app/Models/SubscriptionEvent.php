<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SubscriptionEvent extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'vendor_subscription_id',
        'vendor_id',
        'sector_key',
        'event_type',
        'from_status',
        'to_status',
        'actor_id',
        'actor_type',
        'reason',
        'summary',
        'payload_json',
        'created_at',
    ];

    public function getSummaryAttribute(): ?string
    {
        return $this->attributes['summary'] ?? ($this->attributes['reason'] ?? null);
    }

    protected $casts = [
        'payload_json' => 'array',
        'created_at' => 'datetime',
    ];

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(VendorSubscription::class, 'vendor_subscription_id');
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_id');
    }
}
