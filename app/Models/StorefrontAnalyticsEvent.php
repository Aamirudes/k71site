<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StorefrontAnalyticsEvent extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'vendor_id',
        'event_type',
        'entity_type',
        'entity_id',
        'visitor_hash',
        'referrer',
        'created_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }
}

