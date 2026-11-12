<?php

namespace App\Http\Controllers;

use App\Models\Bid;
use App\Models\Product;
use App\Services\NotificationCenterService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class AuctionController extends Controller
{
    /**
     * Display auction product details and bid history.
     */
    public function show(Product $product)
    {
        if (!$product->isAuction()) {
            return redirect()->route('catalog.product', $product->slug);
        }

        return app(ProductViewController::class)->show($product->slug);
    }

    /**
     * Place a live bid on an auction product.
     */
    public function placeBid(Request $request, Product $product)
    {
        if (!auth()->check()) {
            return redirect()->guest(route('login'))->with('error', 'Please login to place a bid.');
        }

        $user = auth()->user();

        if (!$product->isAuction()) {
            return back()->with('error', 'This listing is not an auction.');
        }

        if ($product->isOwnedBy($user) || ((int) ($product->vendor?->user_id ?? 0) === (int) $user->id)) {
            return back()->with('error', 'Sellers cannot place bids on their own auction.');
        }

        if (!$product->isAuctionLive()) {
            return back()->with('error', 'This auction has ended or is not currently active.');
        }

        // Highest bidder cannot outbid themselves
        if ((int) $product->winning_bidder_id === (int) $user->id) {
            return back()->with('error', 'You are already the highest bidder on this auction!');
        }

        $minNextBidPaise = $product->minNextBid();
        $minNextBidRupees = $minNextBidPaise / 100;

        $validated = $request->validate([
            'bid_amount' => ['required', 'numeric', 'min:' . $minNextBidRupees],
        ], [
            'bid_amount.min' => "Your bid must be at least ₹" . number_format($minNextBidRupees, 2) . ".",
        ]);

        $bidAmountPaise = (int) round($validated['bid_amount'] * 100);

        $outcome = DB::transaction(function () use ($product, $user, $bidAmountPaise, $request) {
            $lockedProduct = Product::withoutGlobalScopes()
                ->whereKey($product->id)
                ->lockForUpdate()
                ->firstOrFail();

            if (!$lockedProduct->isAuctionLive()) {
                return ['ok' => false, 'error' => 'This auction has ended or is not currently active.'];
            }

            if ((int) $lockedProduct->winning_bidder_id === (int) $user->id) {
                return ['ok' => false, 'error' => 'You are already the highest bidder on this auction!'];
            }

            $lockedMinPaise = $lockedProduct->minNextBid();
            if ($bidAmountPaise < $lockedMinPaise) {
                return [
                    'ok' => false,
                    'error' => "Your bid must be at least ₹" . number_format($lockedMinPaise / 100, 2) . ".",
                ];
            }

            $previousWinnerId = $lockedProduct->winning_bidder_id ? (int) $lockedProduct->winning_bidder_id : null;

            // Outbid previous winning bids
            Bid::where('product_id', $lockedProduct->id)
                ->where('status', 'winning')
                ->update(['status' => 'outbid']);

            // Create new winning bid
            Bid::create([
                'product_id' => $lockedProduct->id,
                'user_id' => $user->id,
                'bid_amount' => $bidAmountPaise,
                'status' => 'winning',
                'ip_address' => $request->ip(),
            ]);

            $updates = [
                'current_bid' => $bidAmountPaise,
                'winning_bidder_id' => $user->id,
                'bids_count' => ($lockedProduct->bids_count ?? 0) + 1,
                'auction_status' => 'live',
            ];

            // Anti-sniping rule: if bid placed in final 5 minutes, auto-extend auction by 5 minutes
            if ($lockedProduct->auction_ends_at) {
                $now = Carbon::now();
                $secondsRemaining = $now->diffInSeconds($lockedProduct->auction_ends_at, false);

                if ($secondsRemaining > 0 && $secondsRemaining <= 300) {
                    $updates['auction_ends_at'] = $lockedProduct->auction_ends_at->copy()->addMinutes(5);
                }
            }

            $lockedProduct->update($updates);

            return [
                'ok' => true,
                'previous_winner_id' => $previousWinnerId,
                'product_title' => $lockedProduct->title,
                'product_slug' => $lockedProduct->slug,
            ];
        });

        if (!$outcome['ok']) {
            return back()->with('error', $outcome['error']);
        }

        if (!empty($outcome['previous_winner_id']) && $outcome['previous_winner_id'] !== (int) $user->id) {
            app(NotificationCenterService::class)->notifyUser(
                $outcome['previous_winner_id'],
                '🔨',
                'You Have Been Outbid!',
                'A higher bid of ₹' . number_format($validated['bid_amount'], 2) . ' was placed on "' . $outcome['product_title'] . '".',
                route('auctions.show', $outcome['product_slug']),
                'auction',
                'auction_outbid_' . $product->id . '_' . $bidAmountPaise
            );
        }

        return back()->with('success', 'Your bid of ₹' . number_format($validated['bid_amount'], 2) . ' was placed successfully!');
    }
}
