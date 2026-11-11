<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class UsedTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'transaction_number',
        'product_id',
        'seller_id',
        'buyer_id',
        'offer_id',
        'agreed_price',
        'quantity',
        'fulfillment_method',
        'status',
        'private_pickup_location',
        'seller_notes',
        'buyer_notes',
        'reserved_at',
        'completed_at',
        'cancelled_at',
    ];

    protected $casts = [
        'agreed_price' => 'integer',
        'quantity' => 'integer',
        'reserved_at' => 'datetime',
        'completed_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class)->withoutGlobalScopes();
    }

    public function seller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    public function buyer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }

    public function offer(): BelongsTo
    {
        return $this->belongsTo(Offer::class);
    }

    public function review(): HasOne
    {
        return $this->hasOne(Review::class, 'used_transaction_id');
    }

    public function isReserved(): bool
    {
        return $this->status === 'reserved';
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }

    public function getFormattedPriceAttribute(): string
    {
        return '₹' . number_format($this->agreed_price / 100, 2);
    }
}

