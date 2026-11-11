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
        'seller_id',
        'user_id',
        'buyer_name',
        'buyer_email',
        'buyer_phone',
        'offer_amount',
        'message',
        'handoff_preference',
        'status',
        'last_action_by',
        'counter_amount',
        'vendor_notes',
        'responded_at',
        'expires_at',
        'accepted_at',
        'rejected_at',
        'withdrawn_at',
    ];

    protected $casts = [
        'offer_amount' => 'integer',
        'counter_amount' => 'integer',
        'responded_at' => 'datetime',
        'expires_at' => 'datetime',
        'accepted_at' => 'datetime',
        'rejected_at' => 'datetime',
        'withdrawn_at' => 'datetime',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class)->withoutGlobalScopes();
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function seller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'seller_id');
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

    public function isWithdrawn(): bool
    {
        return $this->status === 'withdrawn';
    }

    public function isExpired(): bool
    {
        return $this->status === 'expired'
            || (in_array($this->status, ['pending', 'countered'], true) && $this->expires_at && $this->expires_at->isPast());
    }

    public function isOwnedBySeller(?User $user): bool
    {
        if (!$user) {
            return false;
        }
        if ($this->seller_id && (int) $this->seller_id === (int) $user->id) {
            return true;
        }
        if ($user->vendor && $this->vendor_id && (int) $this->vendor_id === (int) $user->vendor->id) {
            return true;
        }
        return (bool) $this->product?->isOwnedBy($user);
    }

    public function isOwnedByBuyer(?User $user): bool
    {
        return $user && $this->user_id && (int) $this->user_id === (int) $user->id;
    }

    public function effectiveAgreedAmount(): int
    {
        return ($this->counter_amount && $this->last_action_by === 'buyer_accepted_counter')
            ? (int) $this->counter_amount
            : (int) ($this->status === 'countered' && $this->counter_amount ? $this->counter_amount : $this->offer_amount);
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
