<?php

namespace App\Http\Controllers;

use App\Models\Offer;
use App\Models\Product;
use App\Models\Review;
use App\Models\UsedSavedSearch;
use App\Models\UsedSellerProfile;
use App\Models\UsedThread;
use App\Models\UsedTransaction;
use App\Models\Wishlist;
use App\Services\Used\UsedMarketplaceService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class UsedSellerController extends Controller
{
    public function __construct(private UsedMarketplaceService $usedService)
    {
    }

    /**
     * Public Used Seller Profile storefront (/used/seller/{handle}).
     */
    public function profile(Request $request, string $handle): View
    {
        $profile = UsedSellerProfile::with('user.vendor')
            ->where('handle', $handle)
            ->firstOrFail();

        $sellerUser = $profile->user;
        if (!$sellerUser || ($sellerUser->is_blocked && !auth()->user()?->hasRole('admin'))) {
            abort(404);
        }
        $vendorId = $sellerUser->vendor?->id;

        $activeListings = Product::withoutGlobalScopes()
            ->with(['images', 'category'])
            ->where('vertical', 'used')
            ->where('status', 'active')
            ->where('is_sold', false)
            ->where(function ($q) use ($sellerUser, $vendorId) {
                $q->where('user_id', $sellerUser->id);
                if ($vendorId) {
                    $q->orWhere('vendor_id', $vendorId);
                }
            })
            ->where(function ($q) {
                $q->whereNull('used_status')->orWhereIn('used_status', ['active', 'reserved']);
            })
            ->latest('refreshed_at')
            ->latest()
            ->get();

        $soldListings = collect();
        if ($profile->show_sold_history) {
            $soldListings = Product::withoutGlobalScopes()
                ->with(['images', 'category'])
                ->where('vertical', 'used')
                ->where(function ($q) use ($sellerUser, $vendorId) {
                    $q->where('user_id', $sellerUser->id);
                    if ($vendorId) {
                        $q->orWhere('vendor_id', $vendorId);
                    }
                })
                ->where(function ($q) {
                    $q->where('is_sold', true)->orWhere('used_status', 'sold');
                })
                ->latest('sold_at')
                ->take(12)
                ->get();
        }

        $reviews = Review::with(['user', 'product'])
            ->where('reviewed_user_id', $sellerUser->id)
            ->where('is_approved', true)
            ->latest()
            ->take(20)
            ->get();

        return view('used.seller-profile', compact(
            'profile',
            'sellerUser',
            'activeListings',
            'soldListings',
            'reviews'
        ));
    }

    /**
     * Private Used Seller Dashboard & Buyer Activity Hub (/used/dashboard).
     */
    public function dashboard(Request $request): View
    {
        $user = auth()->user();
        $profile = $this->usedService->getOrCreateSellerProfile($user);
        $this->usedService->recalculateSellerStats($user->id);
        $profile->refresh();

        $tab = $request->query('tab', 'overview');
        $statusFilter = $request->query('status', 'all');
        $vendorId = $user->vendor?->id;

        $myListingsQuery = Product::withoutGlobalScopes()
            ->with(['images', 'category'])
            ->withCount([
                'offers as pending_offers_count' => fn($q) => $q->whereIn('status', ['pending', 'countered']),
                'usedThreads as enquiries_count',
            ])
            ->where('vertical', 'used')
            ->where(function ($q) use ($user, $vendorId) {
                $q->where('user_id', $user->id)
                  ->orWhere('custom_fields->user_id', $user->id);
                if ($vendorId) {
                    $q->orWhere('vendor_id', $vendorId);
                }
            });

        $allMyListings = (clone $myListingsQuery)->latest()->get();

        $listings = match ($statusFilter) {
            'active' => $allMyListings->filter(fn($p) => $p->effectiveUsedStatus() === 'active'),
            'draft' => $allMyListings->filter(fn($p) => $p->effectiveUsedStatus() === 'draft'),
            'paused' => $allMyListings->filter(fn($p) => $p->effectiveUsedStatus() === 'paused'),
            'reserved' => $allMyListings->filter(fn($p) => $p->effectiveUsedStatus() === 'reserved'),
            'sold' => $allMyListings->filter(fn($p) => $p->effectiveUsedStatus() === 'sold'),
            default => $allMyListings,
        };

        $receivedOffers = Offer::with(['product.images', 'user'])
            ->where(function ($q) use ($user, $vendorId) {
                $q->where('seller_id', $user->id);
                if ($vendorId) {
                    $q->orWhere('vendor_id', $vendorId);
                }
            })
            ->latest()
            ->get();

        $sentOffers = Offer::with(['product.images', 'seller'])
            ->where('user_id', $user->id)
            ->latest()
            ->get();

        $sellerThreads = UsedThread::with(['product.images', 'buyer', 'messages'])
            ->withCount(['messages as unread_count' => fn($q) => $q->where('sender_id', '!=', $user->id)->where('is_read', false)])
            ->where('seller_id', $user->id)
            ->latest('last_message_at')
            ->get();

        $buyerThreads = UsedThread::with(['product.images', 'seller', 'messages'])
            ->withCount(['messages as unread_count' => fn($q) => $q->where('sender_id', '!=', $user->id)->where('is_read', false)])
            ->where('buyer_id', $user->id)
            ->latest('last_message_at')
            ->get();

        $sellerTransactions = UsedTransaction::with(['product.images', 'buyer', 'review'])
            ->where('seller_id', $user->id)
            ->latest()
            ->get();

        $buyerTransactions = UsedTransaction::with(['product.images', 'seller.usedSellerProfile', 'review'])
            ->where('buyer_id', $user->id)
            ->latest()
            ->get();

        $savedListings = Wishlist::with(['product.images', 'product.category'])
            ->where('user_id', $user->id)
            ->whereHas('product', fn($q) => $q->where('vertical', 'used'))
            ->latest()
            ->get();

        $savedSearches = UsedSavedSearch::where('user_id', $user->id)->latest()->get();

        $stats = [
            'total_listings' => $allMyListings->count(),
            'active_listings' => $allMyListings->filter(fn($p) => $p->effectiveUsedStatus() === 'active')->count(),
            'draft_listings' => $allMyListings->filter(fn($p) => $p->effectiveUsedStatus() === 'draft')->count(),
            'reserved_listings' => $allMyListings->filter(fn($p) => $p->effectiveUsedStatus() === 'reserved')->count(),
            'sold_listings' => $allMyListings->filter(fn($p) => $p->effectiveUsedStatus() === 'sold')->count(),
            'total_views' => (int) $allMyListings->sum('views_count'),
            'total_saves' => (int) $allMyListings->sum('saves_count'),
            'total_enquiries' => $sellerThreads->count(),
            'pending_offers' => $receivedOffers->whereIn('status', ['pending', 'countered'])->count(),
            'unread_messages' => (int) $sellerThreads->sum('unread_count') + (int) $buyerThreads->sum('unread_count'),
        ];

        return view('used.dashboard', compact(
            'user',
            'profile',
            'tab',
            'statusFilter',
            'listings',
            'allMyListings',
            'receivedOffers',
            'sentOffers',
            'sellerThreads',
            'buyerThreads',
            'sellerTransactions',
            'buyerTransactions',
            'savedListings',
            'savedSearches',
            'stats'
        ));
    }

    /**
     * Update Used seller public profile & privacy controls.
     */
    public function updateProfile(Request $request): RedirectResponse
    {
        $user = auth()->user();
        $profile = $this->usedService->getOrCreateSellerProfile($user);

        $validated = $request->validate([
            'display_name' => 'required|string|min:2|max:120',
            'handle' => 'required|alpha_dash|min:3|max:60|unique:used_seller_profiles,handle,' . $profile->id,
            'bio' => 'nullable|string|max:1000',
            'city' => 'nullable|string|max:100',
            'locality' => 'nullable|string|max:120',
            'preferred_contact_methods' => 'nullable|array',
            'preferred_handoff_methods' => 'nullable|array',
            'show_sold_history' => 'nullable|boolean',
            'show_phone_publicly' => 'nullable|boolean',
        ]);

        $profile->update([
            'display_name' => trim($validated['display_name']),
            'handle' => Str::lower(trim($validated['handle'])),
            'bio' => $validated['bio'] ?? null,
            'city' => $validated['city'] ?? null,
            'locality' => $validated['locality'] ?? null,
            'preferred_contact_methods' => $validated['preferred_contact_methods'] ?? ['k71_chat'],
            'preferred_handoff_methods' => $validated['preferred_handoff_methods'] ?? ['local_pickup'],
            'show_sold_history' => (bool) ($validated['show_sold_history'] ?? false),
            'show_phone_publicly' => (bool) ($validated['show_phone_publicly'] ?? false),
        ]);

        return back()->with('success', 'Your Used seller profile and privacy settings were updated.');
    }
}
