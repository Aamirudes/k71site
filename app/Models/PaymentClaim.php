<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentClaim extends Model
{
    protected $fillable = [
        'order_id',
        'user_id',
        'utr_number',
        'screenshot',
        'amount_claimed',
        'status',
        'verified_by',
        'verified_at',
        'rejection_reason',
        'admin_override',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
