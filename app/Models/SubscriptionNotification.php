<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SubscriptionNotification extends Model
{
    protected $fillable = [
        'vendor_subscription_id',
        'vendor_id',
        'sector_key',
        'recipient_type',
        'event_key',
        'reminder_day',
        'title',
        'message',
        'whatsapp_message',
        'channel',
        'dedupe_key',
        'is_read',
        'sent_at',
        'read_at',
    ];

    protected $casts = [
        'reminder_day' => 'integer',
        'is_read' => 'boolean',
        'sent_at' => 'datetime',
        'read_at' => 'datetime',
    ];

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(VendorSubscription::class, 'vendor_subscription_id');
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }
}
