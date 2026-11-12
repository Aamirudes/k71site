<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class UsedThread extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'seller_id',
        'buyer_id',
        'chat_conversation_id',
        'shared_pickup_location',
        'pickup_shared_at',
        'status',
        'last_message_at',
    ];

    protected $casts = [
        'pickup_shared_at' => 'datetime',
        'last_message_at' => 'datetime',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class)->withoutGlobalScopes()->withTrashed();
    }

    public function seller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    public function buyer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(UsedThreadMessage::class, 'used_thread_id')->orderBy('id');
    }

    public function isParticipant(?User $user): bool
    {
        if (!$user) {
            return false;
        }
        return (int) $this->buyer_id === (int) $user->id || (int) $this->seller_id === (int) $user->id;
    }

    public function isLocked(): bool
    {
        return $this->status === 'locked' || (bool) $this->product?->isSold();
    }
}

