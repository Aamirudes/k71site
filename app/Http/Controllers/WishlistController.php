<?php

namespace App\Http\Controllers;

use App\Models\Wishlist;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WishlistController extends Controller
{
    public function index()
    {
        if (Auth::check()) {
            // Auto-merge any items saved while browsing as guest
            $guestItems = session()->get('guest_wishlist', []);
            if (!empty($guestItems)) {
                foreach ($guestItems as $pid) {
                    Wishlist::firstOrCreate([
                        'user_id' => Auth::id(),
                        'product_id' => (int) $pid,
                    ]);
                }
                session()->forget('guest_wishlist');
            }

            $wishlists = Wishlist::with(['product', 'product.vendor', 'product.images', 'product.variants'])
                ->where('user_id', Auth::id())
                ->latest()
                ->get();
        } else {
            $guestItems = session()->get('guest_wishlist', []);
            $products = \App\Models\Product::with(['vendor', 'images', 'variants'])
                ->whereIn('id', $guestItems)
                ->get();

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

        if (Auth::check()) {
            $wishlist = Wishlist::where('user_id', Auth::id())
                ->where('product_id', $productId)
                ->first();

            if ($wishlist) {
                $wishlist->delete();
                $action = 'removed';
            } else {
                Wishlist::create([
                    'user_id' => Auth::id(),
                    'product_id' => $productId
                ]);
                $action = 'added';
            }

            $count = Wishlist::where('user_id', Auth::id())->count();
        } else {
            // Guest session support
            $guestWishlist = session()->get('guest_wishlist', []);
            if (in_array($productId, $guestWishlist)) {
                $guestWishlist = array_values(array_diff($guestWishlist, [$productId]));
                $action = 'removed';
            } else {
                $guestWishlist[] = $productId;
                $action = 'added';
            }
            session()->put('guest_wishlist', $guestWishlist);
            $count = count($guestWishlist);
        }

        $isWishlisted = ($action === 'added');

        return response()->json([
            'success' => true,
            'action' => $action,
            'is_wishlisted' => $isWishlisted,
            'count' => $count,
            'message' => $isWishlisted ? 'Added to your Wishlist ❤️' : 'Removed from Wishlist'
        ]);
    }

    public function getJson()
    {
        if (Auth::check()) {
            $wishlists = Wishlist::with(['product.vendor', 'product.images', 'product.variants'])
                ->where('user_id', Auth::id())
                ->latest()
                ->get();
            $products = $wishlists->map(fn($w) => $w->product)->filter();
        } else {
            $guestItems = session()->get('guest_wishlist', []);
            $products = \App\Models\Product::with(['vendor', 'images', 'variants'])
                ->whereIn('id', $guestItems)
                ->get();
        }

        $items = $products->map(function ($product) {
            $img = $product->images->first();
            $imageUrl = $img ? (str_starts_with($img->path, 'http') ? $img->path : asset('storage/' . $img->path)) : null;
            return [
                'id' => $product->id,
                'title' => $product->title,
                'slug' => $product->slug,
                'vendor_name' => $product->vendor?->shop_name ?? 'K71 Verified Store',
                'price' => $product->base_price,
                'price_formatted' => '₹' . number_format($product->base_price / 100, 2),
                'image_url' => $imageUrl,
                'url' => route('product.show', $product->slug),
                'in_stock' => $product->stock_quantity > 0,
            ];
        })->values();

        return response()->json([
            'items' => $items,
            'count' => $items->count(),
        ]);
    }
}
