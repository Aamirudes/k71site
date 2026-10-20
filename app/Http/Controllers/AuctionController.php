<?php

namespace App\Http\Controllers;

use App\Models\Bid;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

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

        if (!$product->isAuction()) {
            return back()->with('error', 'This listing is not an auction.');
        }

        if (!$product->isAuctionLive()) {
            return back()->with('error', 'This auction has ended or is not currently active.');
        }

        $user = auth()->user();

        // Highest bidder cannot outbid themselves
        if ($product->winning_bidder_id === $user->id) {
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

        if ($bidAmountPaise < $minNextBidPaise) {
            return back()->with('error', "Your bid must be at least ₹" . number_format($minNextBidRupees, 2) . ".");
        }

        // Outbid previous winning bids
        Bid::where('product_id', $product->id)
            ->where('status', 'winning')
            ->update(['status' => 'outbid']);

        // Create new winning bid
        $bid = Bid::create([
            'product_id' => $product->id,
            'user_id' => $user->id,
            'bid_amount' => $bidAmountPaise,
            'status' => 'winning',
            'ip_address' => $request->ip(),
        ]);

        $updates = [
            'current_bid' => $bidAmountPaise,
            'winning_bidder_id' => $user->id,
            'bids_count' => ($product->bids_count ?? 0) + 1,
            'auction_status' => 'live',
        ];

        // Anti-sniping rule: if bid placed in final 5 minutes, auto-extend auction by 5 minutes
        if ($product->auction_ends_at) {
            $now = Carbon::now();
            $secondsRemaining = $now->diffInSeconds($product->auction_ends_at, false);

            if ($secondsRemaining > 0 && $secondsRemaining <= 300) {
                $updates['auction_ends_at'] = $product->auction_ends_at->copy()->addMinutes(5);
            }
        }

        $product->update($updates);

        return back()->with('success', 'Your bid of ₹' . number_format($validated['bid_amount'], 2) . ' was placed successfully!');
    }
}
