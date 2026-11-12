<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Wishlist;
use App\Services\Monetization\VisibilityService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WishlistController extends Controller
{
    public function index()
    {
        $visibility = app(VisibilityService::class);

        if (Auth::check()) {
            // Auto-merge any items saved while browsing as guest
            $guestItems = session()->get('guest_wishlist', []);
            if (!empty($guestItems)) {
                foreach ($guestItems as $pid) {
                    $candidate = Product::withoutGlobalScopes()->find((int) $pid);
                    if ($candidate && $visibility->isProductPubliclyVisible($candidate)) {
                        Wishlist::firstOrCreate([
                            'user_id' => Auth::id(),
                            'product_id' => (int) $pid,
                        ]);
                    }
                }
                session()->forget('guest_wishlist');
            }

            $wishlists = Wishlist::with(['product', 'product.vendor', 'product.images', 'product.variants'])
                ->where('user_id', Auth::id())
                ->latest()
                ->get()
                ->filter(fn ($w) => $w->product && $visibility->isProductPubliclyVisible($w->product))
                ->values();
        } else {
            $guestItems = session()->get('guest_wishlist', []);
            $products = Product::with(['vendor', 'images', 'variants'])
                ->whereIn('id', $guestItems)
                ->where('status', 'active')
                ->get()
                ->filter(fn ($product) => $visibility->isProductPubliclyVisible($product))
                ->values();

            // Transform into wishlist-compatible structure for the view
            $wishlists = $products->map(function ($product) {
                return (object) [
                    'id' => 'guest_' . $product->id,
                    'product_id' => $product->id,
                    'product' => $product,
                ];
            });
        }

        return view('wishlist.index', compact('wishlists'));
    }

    public function toggle(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id'
        ]);

        $productId = (int) $validated['product_id'];
        $product = Product::withoutGlobalScopes()->with('vendor')->find($productId);
        $visibility = app(VisibilityService::class);

        if (Auth::check()) {
            $wishlist = Wishlist::where('user_id', Auth::id())
                ->where('product_id', $productId)
                ->first();

            if ($wishlist) {
                $wishlist->delete();
                $action = 'removed';
            } else {
                if (!$product || !$visibility->isProductPubliclyVisible($product)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'This listing is currently unavailable.',
                    ], 422);
                }
                Wishlist::create([
                    'user_id' => Auth::id(),
                    'product_id' => $productId
                ]);
                $action = 'added';
            }

            $count = Wishlist::where('user_id', Auth::id())
                ->with('product.vendor')
                ->get()
                ->filter(fn ($w) => $w->product && $visibility->isProductPubliclyVisible($w->product))
                ->count();

            $savesCount = null;
            if ($product && $product->isUsedListing()) {
                $savesCount = app(\App\Services\Used\UsedMarketplaceService::class)->syncSavesCount($productId);
            }
        } else {
            // Guest session support
            $guestWishlist = session()->get('guest_wishlist', []);
            if (in_array($productId, $guestWishlist)) {
                $guestWishlist = array_values(array_diff($guestWishlist, [$productId]));
                $action = 'removed';
            } else {
                if (!$product || !$visibility->isProductPubliclyVisible($product)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'This listing is currently unavailable.',
                    ], 422);
                }
                $guestWishlist[] = $productId;
                $action = 'added';
            }
            session()->put('guest_wishlist', $guestWishlist);
            $count = empty($guestWishlist) ? 0 : Product::with('vendor')
                ->whereIn('id', $guestWishlist)
                ->where('status', 'active')
                ->get()
                ->filter(fn ($item) => $visibility->isProductPubliclyVisible($item))
                ->count();
            $savesCount = null;
        }

        $isWishlisted = ($action === 'added');

        return response()->json([
            'success' => true,
            'action' => $action,
            'is_wishlisted' => $isWishlisted,
            'count' => $count,
            'saves_count' => $savesCount,
            'message' => $isWishlisted ? 'Added to your Wishlist ❤️' : 'Removed from Wishlist'
        ]);
    }

    public function getJson()
    {
        $visibility = app(VisibilityService::class);

        if (Auth::check()) {
            $wishlists = Wishlist::with(['product.vendor', 'product.images', 'product.variants'])
                ->where('user_id', Auth::id())
                ->latest()
                ->get();
            $products = $wishlists->map(fn ($w) => $w->product)
                ->filter(fn ($product) => $product && $visibility->isProductPubliclyVisible($product))
                ->values();
        } else {
            $guestItems = session()->get('guest_wishlist', []);
            $products = Product::with(['vendor', 'images', 'variants'])
                ->whereIn('id', $guestItems)
                ->where('status', 'active')
                ->get()
                ->filter(fn ($product) => $visibility->isProductPubliclyVisible($product))
                ->values();
        }

        $items = $products->map(function ($product) {
            $img = $product->images->first();
            $imageUrl = $img ? (str_starts_with($img->path, 'http') ? $img->path : asset('storage/' . $img->path)) : null;

            $url = route('product.show', $product->slug);
            if ($product->isUsedListing()) {
                $url = route('used.show', $product->slug);
            } elseif ($product->vertical === 'services' || in_array($product->listing_type, ['gig', 'service', 'booking'], true)) {
                $url = route('services.show', $product->slug);
            } elseif ($product->vertical === 'food' && $product->vendor) {
                $url = route('food.restaurant', $product->vendor->slug);
            }

            return [
                'id' => $product->id,
                'title' => $product->title,
                'slug' => $product->slug,
                'vendor_name' => $product->vendor?->shop_name ?? ($product->ownerUser()?->name ?? 'K71 Verified Store'),
                'price' => $product->base_price,
                'price_formatted' => '₹' . number_format($product->base_price / 100, 2),
                'image_url' => $imageUrl,
                'url' => $url,
                'in_stock' => $product->isUsedListing()
                    ? (!$product->isSold() && (int) $product->total_stock > 0)
                    : ($product->variants->isNotEmpty()
                        ? (int) $product->variants->sum('stock') > 0
                        : (int) $product->total_stock > 0),
            ];
        })->values();

        return response()->json([
            'items' => $items,
            'count' => $items->count(),
        ]);
    }
}
