<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use App\Models\Review;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    /**
     * Customer writes or updates review for a product.
     */
    public function store(Request $request, Product $product): RedirectResponse
    {
        $user = auth()->user();

        $validated = $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'title' => 'nullable|string|max:200',
            'body' => 'required|string|max:2000',
        ]);

        // Check verified purchase
        $isVerifiedPurchase = Order::where('user_id', $user->id)
            ->where('status', 'delivered')
            ->whereHas('items', fn($q) => $q->where('product_id', $product->id))
            ->exists();

        Review::updateOrCreate(
            [
                'product_id' => $product->id,
                'user_id' => $user->id,
            ],
            [
                'rating' => $validated['rating'],
                'title' => $validated['title'] ?? null,
                'body' => $validated['body'],
                'is_verified_purchase' => $isVerifiedPurchase,
                'is_approved' => true,
            ]
        );

        return back()->with('success', 'Thank you! Your review has been published.');
    }

    /**
     * Vendor posts an official response to a customer review.
     */
    public function vendorReply(Request $request, Review $review): RedirectResponse
    {
        $vendor = $request->attributes->get('current_vendor') ?? auth()->user()?->vendor;
        $product = $review->product ?: Product::withoutGlobalScopes()->find($review->product_id);
        if (!$vendor || !$product || $product->vendor_id !== $vendor->id) {
            abort(403, 'Unauthorized to reply to this review.');
        }

        $validated = $request->validate([
            'vendor_reply' => 'required|string|max:1000',
        ]);

        $review->update([
            'vendor_reply' => $validated['vendor_reply'],
            'vendor_replied_at' => now(),
        ]);

        return back()->with('success', 'Your official response has been added to the review.');
    }
}
