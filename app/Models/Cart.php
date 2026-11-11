<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cart extends Model
{
    protected $fillable = ['user_id', 'session_id', 'coupon_codes'];

    protected $casts = [
        'coupon_codes' => 'array',
    ];

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
            ->with(['items.product', 'items.vendor.settings'])
            ->first();

        if (!$guestCart || $guestCart->items->isEmpty()) {
            return;
        }

        $userCart = self::where('user_id', $userId)
            ->with(['items.product', 'items.vendor.settings'])
            ->first();

        if (!$userCart) {
            $guestCart->update([
                'user_id' => $userId,
                'session_id' => $sessionId,
            ]);
            return;
        }

        // Preserve single-restaurant food invariant if guest cart holds food from another kitchen
        $guestFoodVendorIds = $guestCart->items
            ->filter(fn ($i) => ($i->product?->vertical === 'food') || ($i->vendor?->isRestaurant()))
            ->pluck('vendor_id')
            ->unique()
            ->values();

        if ($guestFoodVendorIds->isNotEmpty()) {
            $primaryGuestFoodVendorId = (int) $guestFoodVendorIds->first();
            $staleUserFoodItems = $userCart->items->filter(function ($uItem) use ($primaryGuestFoodVendorId) {
                $isFood = ($uItem->product?->vertical === 'food') || ($uItem->vendor?->isRestaurant());
                return $isFood && (int) $uItem->vendor_id !== $primaryGuestFoodVendorId;
            });
            foreach ($staleUserFoodItems as $staleItem) {
                $staleItem->delete();
            }
            $userCart->load('items');
        }

        foreach ($guestCart->items as $item) {
            $incomingModIds = collect($item->selected_modifiers_json ?? [])->pluck('id')->sort()->values()->all();
            $incomingInstr = trim((string) ($item->special_instructions ?? ''));
            $incomingOpts = json_encode($item->option_selections_json ?? []);
            $incomingBooking = $item->booking_date_time?->toIso8601String();

            $existing = $userCart->items
                ->where('product_id', $item->product_id)
                ->first(function ($candidate) use ($item, $incomingModIds, $incomingInstr, $incomingOpts, $incomingBooking) {
                    if ((int) ($candidate->variant_id ?? 0) !== (int) ($item->variant_id ?? 0)) {
                        return false;
                    }
                    if ((int) ($candidate->gig_package_id ?? 0) !== (int) ($item->gig_package_id ?? 0)) {
                        return false;
                    }
                    $candModIds = collect($candidate->selected_modifiers_json ?? [])->pluck('id')->sort()->values()->all();
                    if ($candModIds !== $incomingModIds) {
                        return false;
                    }
                    if (trim((string) ($candidate->special_instructions ?? '')) !== $incomingInstr) {
                        return false;
                    }
                    if (json_encode($candidate->option_selections_json ?? []) !== $incomingOpts) {
                        return false;
                    }
                    if ($candidate->booking_date_time?->toIso8601String() !== $incomingBooking) {
                        return false;
                    }
                    return true;
                });

            if ($existing) {
                $existing->update([
                    'quantity' => $existing->quantity + $item->quantity,
                ]);
                $item->delete();
            } else {
                $item->update([
                    'cart_id' => $userCart->id,
                ]);
                $userCart->items->push($item);
            }
        }

        $guestCart->delete();
    }
}
