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
            'images' => 'nullable|array|max:4',
            'images.*' => 'image|mimes:jpeg,png,jpg,webp|max:5120',
        ]);

        // Check verified purchase
        $isVerifiedPurchase = Order::where('user_id', $user->id)
            ->where('status', 'delivered')
            ->whereHas('items', fn($q) => $q->where('product_id', $product->id))
            ->exists();

        // Handle uploaded customer photos
        $storedImages = [];
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $imgFile) {
                if ($imgFile && $imgFile->isValid()) {
                    $path = $imgFile->store('reviews', 'public');
                    $storedImages[] = $path;
                }
            }
        }

        $existingReview = Review::where('product_id', $product->id)->where('user_id', $user->id)->first();
        if ($existingReview && empty($storedImages) && !empty($existingReview->images)) {
            $storedImages = $existingReview->images;
        }

        Review::updateOrCreate(
            [
                'product_id' => $product->id,
                'user_id' => $user->id,
            ],
            [
                'rating' => $validated['rating'],
                'title' => $validated['title'] ?? null,
                'body' => $validated['body'],
                'images' => !empty($storedImages) ? $storedImages : null,
                'is_verified_purchase' => $isVerifiedPurchase,
                'is_approved' => true,
            ]
        );

        // Update product average rating & review count cache
        $approvedReviews = Review::where('product_id', $product->id)->where('is_approved', true);
        $reviewCount = $approvedReviews->count();
        $avgRating = $reviewCount > 0 ? round($approvedReviews->avg('rating'), 2) : null;
        $product->update([
            'review_count' => $reviewCount,
            'avg_rating' => $avgRating,
        ]);

        return back()->with('success', 'Thank you! Your photo review has been published.');
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
