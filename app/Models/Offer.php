<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Offer extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'vendor_id',
        'user_id',
        'buyer_name',
        'buyer_email',
        'buyer_phone',
        'offer_amount',
        'message',
        'status',
        'counter_amount',
        'vendor_notes',
        'responded_at',
    ];

    protected $casts = [
        'offer_amount' => 'integer',
        'counter_amount' => 'integer',
        'responded_at' => 'datetime',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isAccepted(): bool
    {
        return $this->status === 'accepted';
    }

    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    public function isCountered(): bool
    {
        return $this->status === 'countered';
    }

    public function formattedOfferAmount(): string
    {
        return '₹' . number_format($this->offer_amount / 100, 2);
    }

    public function formattedCounterAmount(): ?string
    {
        return $this->counter_amount ? ('₹' . number_format($this->counter_amount / 100, 2)) : null;
    }
}
