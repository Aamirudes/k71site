<?php

namespace App\Http\Controllers;

use App\Models\Offer;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OfferController extends Controller
{
    /**
     * Submit a buyer offer for a used / pre-owned listing.
     */
    public function store(Request $request, Product $product): JsonResponse|RedirectResponse
    {
        if ($product->isSold()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'This item has already been marked as sold and cannot receive new offers.',
                ], 422);
            }
            return back()->with('error', 'This item has already been marked as sold.');
        }

        $validated = $request->validate([
            'buyer_name' => 'required|string|max:120',
            'buyer_phone' => 'required|string|min:10|max:15',
            'buyer_email' => 'nullable|email|max:150',
            'offer_amount' => 'required|numeric|min:1',
            'message' => 'nullable|string|max:500',
        ]);

        $offer = Offer::create([
            'product_id' => $product->id,
            'vendor_id' => $product->vendor_id,
            'user_id' => auth()->id(),
            'buyer_name' => $validated['buyer_name'],
            'buyer_phone' => $validated['buyer_phone'],
            'buyer_email' => $validated['buyer_email'] ?? auth()->user()?->email,
            'offer_amount' => (int) round($validated['offer_amount'] * 100),
            'message' => $validated['message'] ?? null,
            'status' => 'pending',
        ]);

        $msg = "Your offer of ₹" . number_format($validated['offer_amount'], 2) . " has been submitted to the seller!";

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => $msg,
                'offer_id' => $offer->id,
            ]);
        }

        return back()->with('success', $msg);
    }

    /**
     * Vendor inbox for received offers.
     */
    public function vendorIndex(Request $request): View
    {
        $vendor = auth()->user()->vendor;
        if (!$vendor) abort(403);

        $status = $request->query('status', 'all');

        $counts = [
            'all' => Offer::where('vendor_id', $vendor->id)->count(),
            'pending' => Offer::where('vendor_id', $vendor->id)->where('status', 'pending')->count(),
            'accepted' => Offer::where('vendor_id', $vendor->id)->where('status', 'accepted')->count(),
            'countered' => Offer::where('vendor_id', $vendor->id)->where('status', 'countered')->count(),
            'rejected' => Offer::where('vendor_id', $vendor->id)->where('status', 'rejected')->count(),
        ];

        $query = Offer::with(['product.images', 'user'])
            ->where('vendor_id', $vendor->id);

        if ($status !== 'all' && array_key_exists($status, $counts)) {
            $query->where('status', $status);
        }

        $offers = $query->latest()->paginate(20)->withQueryString();

        return view('vendor.offers.index', compact('offers', 'counts', 'status'));
    }

    /**
     * Vendor responds to an offer (accept, reject, or counter).
     */
    public function vendorRespond(Request $request, Offer $offer): RedirectResponse
    {
        $vendor = auth()->user()->vendor;
        if (!$vendor || $offer->vendor_id !== $vendor->id) {
            abort(403, 'Unauthorized access to offer.');
        }

        $validated = $request->validate([
            'action' => 'required|in:accept,reject,counter',
            'counter_amount' => 'required_if:action,counter|nullable|numeric|min:1',
            'vendor_notes' => 'nullable|string|max:500',
        ]);

        $status = match ($validated['action']) {
            'accept' => 'accepted',
            'reject' => 'rejected',
            'counter' => 'countered',
        };

        $offer->update([
            'status' => $status,
            'counter_amount' => $validated['action'] === 'counter' ? (int) round($validated['counter_amount'] * 100) : null,
            'vendor_notes' => $validated['vendor_notes'] ?? null,
            'responded_at' => now(),
        ]);

        $actionWord = match ($status) {
            'accepted' => 'accepted',
            'rejected' => 'rejected',
            'countered' => 'countered with ₹' . number_format($validated['counter_amount'] ?? 0, 2),
        };

        return back()->with('success', "Offer has been {$actionWord} successfully.");
    }

    /**
     * Mark a used item as Sold.
     */
    public function markSold(Request $request, Product $product): RedirectResponse
    {
        $vendor = auth()->user()->vendor;
        if (!$vendor || $product->vendor_id !== $vendor->id) {
            abort(403, 'Unauthorized action.');
        }

        $product->markAsSold();

        return back()->with('success', "Product '{$product->title}' has been marked as SOLD.");
    }
}
