<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Coupon extends Model
{
    protected $fillable = [
        'vendor_id',
        'code',
        'type',
        'value',
        'max_discount',
        'min_cart_value',
        'starts_at',
        'expires_at',
        'total_limit',
        'per_customer_limit',
        'first_order_only',
        'is_active',
        'usage_count',
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }
}
