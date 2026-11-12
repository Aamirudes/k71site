<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use App\Models\Review;
use App\Services\Food\FoodMarketplaceService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReviewController extends Controller
{
    /**
     * Customer writes or updates review for a product.
     */
    public function store(Request $request, Product $product): RedirectResponse
    {
        $user = auth()->user();

        if ($product->isOwnedBy($user) || ($product->vendor && (int) $product->vendor->user_id === (int) $user->id)) {
            abort(403, 'Sellers cannot review their own listing.');
        }

        if (!app(\App\Services\Monetization\VisibilityService::class)->isProductPubliclyVisible($product)) {
            abort(404);
        }

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
            ->whereHas('items', fn ($q) => $q->where('product_id', $product->id))
            ->exists();

        // Handle uploaded customer photos
        $storedImages = [];
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $imgFile) {
                if ($imgFile && $imgFile->isValid()) {
                    $path = $imgFile->store('reviews', 'public');
                    if (function_exists('k71_sync_public_storage')) {
                        k71_sync_public_storage($path);
                    }
                    $storedImages[] = $path;
                }
            }
        }

        $existingReview = Review::where('product_id', $product->id)->where('user_id', $user->id)->first();
        $imagesToSave = !empty($storedImages)
            ? $storedImages
            : ($existingReview && !empty($existingReview->images) ? $existingReview->images : null);

        try {
            DB::transaction(function () use ($product, $user, $validated, $imagesToSave, $isVerifiedPurchase) {
                Product::withoutGlobalScopes()->whereKey($product->id)->lockForUpdate()->first();

                Review::updateOrCreate(
                    [
                        'product_id' => $product->id,
                        'user_id' => $user->id,
                    ],
                    [
                        'vendor_id' => $product->vendor_id,
                        'review_type' => $product->vertical === 'food' ? 'food_dish' : 'product',
                        'rating' => $validated['rating'],
                        'title' => $validated['title'] ?? null,
                        'body' => $validated['body'],
                        'images' => $imagesToSave,
                        'is_verified_purchase' => $isVerifiedPurchase,
                        'is_approved' => true,
                    ]
                );

                $approvedReviews = Review::where('product_id', $product->id)->where('is_approved', true);
                $reviewCount = $approvedReviews->count();
                $avgRating = $reviewCount > 0 ? round($approvedReviews->avg('rating'), 2) : null;
                $product->update([
                    'review_count' => $reviewCount,
                    'avg_rating' => $avgRating,
                ]);
            });
        } catch (\Throwable $e) {
            foreach ($storedImages as $path) {
                if (function_exists('k71_delete_public_storage')) {
                    k71_delete_public_storage($path);
                }
            }
            return back()->with('error', 'Unable to publish your review right now. Please try again.');
        }

        if ($product->vertical === 'food' && $product->vendor) {
            app(FoodMarketplaceService::class)->refreshRestaurantRatingCache($product->vendor);
        }

        if ($product->vendor?->user_id) {
            app(\App\Services\NotificationCenterService::class)->notifyUser(
                (int) $product->vendor->user_id,
                '⭐',
                'New Product Review Received',
                "{$user->name} rated \"{$product->title}\" {$validated['rating']}/5 stars.",
                route('vendor.reviews.index'),
                'review',
                'product_review_' . $product->id . '_' . $user->id
            );
        }

        return back()->with('success', 'Thank you! Your photo review has been published.');
    }

    /**
     * Customer reviews a delivered Food / Restaurant order (verified delivery review).
     */
    public function storeFoodReview(Request $request, Order $order, FoodMarketplaceService $foodService): RedirectResponse
    {
        $user = auth()->user();

        if ((int) $order->user_id !== (int) $user->id) {
            abort(403, 'You can only review your own orders.');
        }

        if ($order->vendor && (int) $order->vendor->user_id === (int) $user->id) {
            abort(403, 'Restaurant owners cannot review their own kitchen.');
        }

        if (!$order->isFoodOrder()) {
            return back()->with('error', 'Only food delivery or takeaway orders can be reviewed here.');
        }

        if ($order->status !== 'delivered') {
            return back()->with('error', 'You can submit a restaurant review once your order has been delivered.');
        }

        $alreadyReviewed = Review::where('order_id', $order->id)
            ->where('user_id', $user->id)
            ->whereNull('product_id')
            ->exists();

        if ($alreadyReviewed) {
            return back()->with('error', 'You have already submitted a review for this order.');
        }

        if ($request->filled('comment') && !$request->filled('body')) {
            $request->merge(['body' => $request->input('comment')]);
        }

        $validated = $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'title' => 'nullable|string|max:200',
            'body' => 'required|string|max:2000',
            'images' => 'nullable|array|max:4',
            'images.*' => 'image|mimes:jpeg,png,jpg,webp|max:5120',
        ]);

        $storedImages = [];
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $imgFile) {
                if ($imgFile && $imgFile->isValid()) {
                    $path = $imgFile->store('reviews', 'public');
                    if (function_exists('k71_sync_public_storage')) {
                        k71_sync_public_storage($path);
                    }
                    $storedImages[] = $path;
                }
            }
        }

        try {
            DB::transaction(function () use ($order, $user, $validated, $storedImages) {
                $lockedOrder = Order::whereKey($order->id)->lockForUpdate()->firstOrFail();

                if ($lockedOrder->status !== 'delivered') {
                    throw new \RuntimeException('You can submit a restaurant review once your order has been delivered.');
                }

                $duplicate = Review::where('order_id', $lockedOrder->id)
                    ->where('user_id', $user->id)
                    ->whereNull('product_id')
                    ->lockForUpdate()
                    ->exists();

                if ($duplicate) {
                    throw new \RuntimeException('You have already submitted a review for this order.');
                }

                Review::create([
                    'user_id' => $user->id,
                    'vendor_id' => $lockedOrder->vendor_id,
                    'order_id' => $lockedOrder->id,
                    'product_id' => null,
                    'review_type' => 'food_order',
                    'rating' => (int) $validated['rating'],
                    'title' => $validated['title'] ?? null,
                    'body' => trim($validated['body']),
                    'images' => !empty($storedImages) ? $storedImages : null,
                    'is_verified_purchase' => true,
                    'is_approved' => true,
                ]);
            });
        } catch (\Throwable $e) {
            foreach ($storedImages as $path) {
                if (function_exists('k71_delete_public_storage')) {
                    k71_delete_public_storage($path);
                }
            }
            if ($e instanceof \RuntimeException) {
                return back()->with('error', $e->getMessage());
            }
            return back()->with('error', 'Unable to submit your food review right now.');
        }

        if ($order->vendor) {
            $foodService->refreshRestaurantRatingCache($order->vendor);
            if ($order->vendor->user_id) {
                app(\App\Services\NotificationCenterService::class)->notifyUser(
                    (int) $order->vendor->user_id,
                    '⭐',
                    'New Order Review Received',
                    "{$user->name} rated order {$order->order_number} {$validated['rating']}/5 stars.",
                    route('vendor.reviews.index'),
                    'review',
                    'food_order_review_' . $order->id
                );
            }
        }

        return back()->with('success', 'Thank you! Your verified food order review has been published.');
    }

    /**
     * Vendor posts an official response to a customer review.
     */
    public function vendorReply(Request $request, Review $review): RedirectResponse
    {
        $vendor = $request->attributes->get('current_vendor') ?? auth()->user()?->vendor;
        $product = $review->product ?: Product::withoutGlobalScopes()->find($review->product_id);
        $ownsReview = $vendor && (
            ((int) $review->vendor_id === (int) $vendor->id)
            || ($product && (int) $product->vendor_id === (int) $vendor->id)
        );
        if (!$ownsReview) {
            abort(403, 'Unauthorized to reply to this review.');
        }

        $validated = $request->validate([
            'vendor_reply' => 'required|string|max:1000',
        ]);

        $review->update([
            'vendor_reply' => $validated['vendor_reply'],
            'vendor_replied_at' => now(),
        ]);

        $vendor->bumpWebsiteCache();

        return back()->with('success', 'Your official response has been added to the review.');
    }
}
