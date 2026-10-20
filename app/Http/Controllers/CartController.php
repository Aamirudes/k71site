<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\ProductVariant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class CartController extends Controller
{
    public function index()
    {
        $cart = $this->getCart();
        
        if (!$cart) {
            $items = collect();
        } else {
            $items = $cart->items()->with(['variant.product', 'vendor'])->get();
        }

        // Group by vendor because checkout generates separate orders
        $groupedItems = $items->groupBy('vendor_id');
        
        return view('cart.index', compact('groupedItems'));
    }

    public function add(Request $request)
    {
        $validated = $request->validate([
            'variant_id' => 'required|exists:product_variants,id',
            'quantity' => 'required|integer|min:1'
        ]);

        // When adding via PDP, VendorScope is not active for the public, so we must query without it if necessary, 
        // or just rely on the variant's product.
        $variant = ProductVariant::withoutGlobalScopes()->with('product')->findOrFail($validated['variant_id']);

        // Listing type enforcement (Decision D9 & Spec Sec 14)
        if ($variant->product && !$variant->product->isPurchase()) {
            $typeLabel = ucfirst(str_replace('_', ' ', $variant->product->listing_type));
            return response()->json([
                'success' => false,
                'message' => "This is an {$typeLabel} listing and cannot be added to the shopping cart for direct purchase."
            ], 422);
        }

        if ($variant->stock <= 0) {
            return response()->json(['success' => false, 'message' => 'This size/variant is completely out of stock.'], 400);
        }

        if ($variant->stock < $validated['quantity']) {
            return response()->json(['success' => false, 'message' => "Only {$variant->stock} pieces available in stock."], 400);
        }

        $cart = $this->getOrCreateCart();

        $cartItem = $cart->items()->where('variant_id', $variant->id)->first();

        if ($cartItem) {
            if ($cartItem->quantity + $validated['quantity'] > $variant->stock) {
                return response()->json(['success' => false, 'message' => 'Cannot add more than available stock.'], 400);
            }
            $cartItem->increment('quantity', $validated['quantity']);
        } else {
            $cart->items()->create([
                'variant_id' => $variant->id,
                'product_id' => $variant->product_id,
                'vendor_id' => $variant->product->vendor_id,
                'quantity' => $validated['quantity'],
                'price_at_add' => $variant->price_override ?? $variant->product->base_price,
            ]);
        }

        return response()->json(['success' => true, 'message' => 'Added to cart.', 'cart_count' => $cart->items()->sum('quantity')]);
    }

    public function remove(Request $request, CartItem $item)
    {
        // Security check
        if ($item->cart_id !== $this->getCart()?->id) {
            abort(403);
        }

        $item->delete();

        if ($request->expectsJson() || $request->wantsJson()) {
            return $this->data();
        }

        return back()->with('success', 'Item removed from cart.');
    }

    public function update(Request $request, CartItem $item)
    {
        if ($item->cart_id !== $this->getCart()?->id) {
            abort(403);
        }

        $validated = $request->validate([
            'quantity' => 'required|integer|min:1'
        ]);

        $variant = ProductVariant::withoutGlobalScopes()->findOrFail($item->variant_id);
        
        if ($variant->stock <= 0) {
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'This item is now out of stock.'], 400);
            }
            return back()->with('error', 'This item is now out of stock.');
        }

        if ($validated['quantity'] > $variant->stock) {
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => "Only {$variant->stock} items available in stock."], 400);
            }
            return back()->with('error', "Only {$variant->stock} items available in stock.");
        }

        $item->update(['quantity' => $validated['quantity']]);

        if ($request->expectsJson() || $request->wantsJson()) {
            return $this->data();
        }

        return back()->with('success', 'Cart updated.');
    }

    public function data()
    {
        $cart = $this->getCart();
        if (!$cart) {
            return response()->json([
                'items' => [],
                'count' => 0,
                'subtotal' => 0,
                'subtotal_formatted' => '₹0.00',
            ]);
        }

        $items = $cart->items()->with(['variant.product.images', 'variant', 'vendor'])->get();

        $subtotal = 0;
        $mappedItems = $items->map(function ($item) use (&$subtotal) {
            $price = $item->price_at_add;
            $lineTotal = $price * $item->quantity;
            $subtotal += $lineTotal;

            $product = $item->variant?->product;
            $image = $product?->images?->first();
            $imageUrl = $image ? asset('storage/' . $image->path) : null;

            return [
                'id' => $item->id,
                'product_title' => $product?->title ?? 'Product',
                'variant_label' => $item->variant?->name ?? ($item->variant?->size ?? 'Standard'),
                'vendor_name' => $item->vendor?->shop_name ?? 'K71 Store',
                'vendor_id' => $item->vendor_id,
                'quantity' => $item->quantity,
                'unit_price' => $price,
                'unit_price_formatted' => '₹' . number_format($price / 100, 2),
                'line_total' => $lineTotal,
                'line_total_formatted' => '₹' . number_format($lineTotal / 100, 2),
                'image_url' => $imageUrl,
                'checkout_url' => $item->vendor ? route('checkout.index', $item->vendor) : route('cart.index'),
            ];
        });

        return response()->json([
            'items' => $mappedItems,
            'count' => $items->sum('quantity'),
            'subtotal' => $subtotal,
            'subtotal_formatted' => '₹' . number_format($subtotal / 100, 2),
        ]);
    }

    public function getCount()
    {
        $cart = $this->getCart();
        return response()->json(['count' => $cart ? $cart->items()->sum('quantity') : 0]);
    }

    private function getCart()
    {
        if (Auth::check()) {
            Cart::mergeGuestCart(Auth::id(), Session::getId());
            return Cart::where('user_id', Auth::id())->first();
        }
        
        return Cart::where('session_id', Session::getId())->first();
    }

    private function getOrCreateCart()
    {
        if (Auth::check()) {
            Cart::mergeGuestCart(Auth::id(), Session::getId());
            return Cart::firstOrCreate(['user_id' => Auth::id()]);
        }
        
        Session::put('cart_initialized', true); // Force Laravel to persist the session cookie for guests
        return Cart::firstOrCreate(['session_id' => Session::getId()]);
    }
}
