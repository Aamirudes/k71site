<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Wishlist extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'product_id',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get list of wishlisted product IDs for current user or guest (memoized per request).
     */
    public static function getUserWishlistIds(): array
    {
        static $cached = null;
        if ($cached !== null) {
            return $cached;
        }

        if (auth()->check()) {
            return $cached = static::where('user_id', auth()->id())->pluck('product_id')->toArray();
        }

        return $cached = session()->get('guest_wishlist', []);
    }
}
