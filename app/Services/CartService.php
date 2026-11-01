<?php

namespace App\Services;

use App\Models\Cart;

class CartService
{
    /**
     * Get total item count in active user/guest cart
     */
    public static function getCount(): int
    {
        try {
            if (auth()->check()) {
                $cart = Cart::where('user_id', auth()->id())->first();
                if ($cart) {
                    return (int) $cart->items()->sum('quantity');
                }
            }
            $sessionId = session()->getId();
            if ($sessionId) {
                $cart = Cart::where('session_id', $sessionId)->first();
                if ($cart) {
                    return (int) $cart->items()->sum('quantity');
                }
            }
            $guestCart = session('guest_cart', []);
            if (is_array($guestCart)) {
                return (int) array_sum(array_column($guestCart, 'quantity'));
            }
            return 0;
        } catch (\Throwable $e) {
            return 0;
        }
    }
}

