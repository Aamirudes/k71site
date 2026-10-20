<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Bid extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'user_id',
        'bid_amount',
        'status',
        'ip_address',
    ];

    protected $casts = [
        'bid_amount' => 'integer',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isOutbid(): bool
    {
        return $this->status === 'outbid';
    }

    public function isWinning(): bool
    {
        return $this->status === 'winning';
    }

    public function isWon(): bool
    {
        return $this->status === 'won';
    }

    public function formattedBidAmount(): string
    {
        return '₹' . number_format($this->bid_amount / 100, 2);
    }
}
