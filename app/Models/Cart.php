<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cart extends Model
{
    protected $fillable = ['user_id', 'session_id'];

    public function items(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public static function mergeGuestCart(int $userId, ?string $sessionId): void
    {
        if (!$sessionId) {
            return;
        }

        $guestCart = self::where('session_id', $sessionId)
            ->whereNull('user_id')
            ->with('items')
            ->first();

        if (!$guestCart || $guestCart->items->isEmpty()) {
            return;
        }

        $userCart = self::where('user_id', $userId)->first();

        if (!$userCart) {
            $guestCart->update([
                'user_id' => $userId,
                'session_id' => $sessionId,
            ]);
            return;
        }

        foreach ($guestCart->items as $item) {
            $existing = $userCart->items()->where('variant_id', $item->variant_id)->first();
            if ($existing) {
                $existing->update([
                    'quantity' => $existing->quantity + $item->quantity,
                ]);
                $item->delete();
            } else {
                $item->update([
                    'cart_id' => $userCart->id,
                ]);
            }
        }

        $guestCart->delete();
    }
}
