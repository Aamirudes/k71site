<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Booking;
use App\Models\Enquiry;
use App\Models\Bid;
use App\Models\Offer;
use App\Models\Review;
use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AccountController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        return view('account.index', compact('user'));
    }

    /**
     * DPDP Act Compliant Personal Data Package Export (JSON)
     */
    public function export(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();

        // 1. Core Profile
        $profile = [
            'id' => $user->id,
            'name' => $user->name,
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'username' => $user->username,
            'email' => $user->email,
            'phone' => $user->phone,
            'registered_at' => $user->created_at?->toIso8601String(),
            'last_login_at' => $user->last_login_at?->toIso8601String(),
        ];

        // 2. Orders History
        $orders = Order::where('user_id', $user->id)
            ->with(['items.product'])
            ->latest()
            ->get()
            ->map(function ($order) {
                return [
                    'order_number' => $order->order_number,
                    'status' => $order->status,
                    'total_amount' => $order->total_amount,
                    'payment_method' => $order->payment_method,
                    'payment_status' => $order->payment_status,
                    'created_at' => $order->created_at?->toIso8601String(),
                    'items' => $order->items->map(function ($item) {
                        return [
                            'product_title' => $item->product?->title ?? 'Item',
                            'quantity' => $item->quantity,
                            'price' => $item->price,
                            'total' => $item->total,
                        ];
                    }),
                ];
            });

        // 3. Service Bookings
        $bookings = Booking::where('user_id', $user->id)
            ->with('product')
            ->latest()
            ->get()
            ->map(function ($b) {
                return [
                    'booking_number' => $b->booking_number,
                    'service' => $b->product?->title ?? 'Service',
                    'scheduled_at' => $b->scheduled_at?->toIso8601String(),
                    'status' => $b->status,
                    'quoted_price' => $b->quoted_price,
                    'address' => $b->service_address,
                ];
            });

        // 4. Custom Enquiries & Quotes
        $enquiries = Enquiry::where('user_id', $user->id)
            ->with('quotes')
            ->latest()
            ->get()
            ->map(function ($e) {
                return [
                    'enquiry_number' => $e->enquiry_number,
                    'category' => $e->category,
                    'description' => $e->description,
                    'budget_min' => $e->budget_min,
                    'budget_max' => $e->budget_max,
                    'status' => $e->status,
                    'quotes_count' => $e->quotes->count(),
                ];
            });

        // 5. Auction Bids
        $bids = Bid::where('user_id', $user->id)
            ->with('product')
            ->latest()
            ->get()
            ->map(function ($bid) {
                return [
                    'product' => $bid->product?->title ?? 'Product',
                    'amount' => $bid->amount,
                    'status' => $bid->status,
                    'placed_at' => $bid->created_at?->toIso8601String(),
                ];
            });

        // 6. Pre-Owned Offers
        $offers = Offer::where('user_id', $user->id)
            ->with('product')
            ->latest()
            ->get()
            ->map(function ($o) {
                return [
                    'product' => $o->product?->title ?? 'Product',
                    'offer_amount' => $o->offer_amount,
                    'status' => $o->status,
                    'created_at' => $o->created_at?->toIso8601String(),
                ];
            });

        // 7. Product Reviews
        $reviews = Review::where('user_id', $user->id)
            ->with('product')
            ->latest()
            ->get()
            ->map(function ($r) {
                return [
                    'product' => $r->product?->title ?? 'Product',
                    'rating' => $r->rating,
                    'review_text' => $r->review_text,
                    'is_verified_buyer' => $r->is_verified_buyer,
                    'created_at' => $r->created_at?->toIso8601String(),
                ];
            });

        $exportData = [
            'compliance' => 'Digital Personal Data Protection Act (DPDP), 2023 - Republic of India',
            'exported_at' => now()->toIso8601String(),
            'platform' => 'K71 Marketplace (k71.in)',
            'user' => $profile,
            'orders' => $orders,
            'bookings' => $bookings,
            'enquiries' => $enquiries,
            'bids' => $bids,
            'offers' => $offers,
            'reviews' => $reviews,
        ];

        return response()->streamDownload(function () use ($exportData) {
            echo json_encode($exportData, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        }, "k71-data-export-user-{$user->id}.json", [
            'Content-Type' => 'application/json',
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
        ]);
    }

    /**
     * DPDP Act Right to Erasure / Account Deletion
     */
    public function delete(Request $request)
    {
        $request->validate([
            'confirm_delete' => 'required|accepted',
        ]);

        /** @var \App\Models\User $user */
        $user = auth()->user();

        // If user operates a vendor store, mark vendor closed
        if ($user->vendor) {
            $user->vendor->update([
                'status' => 'suspended',
                'vacation_mode' => true,
                'vacation_message' => 'Store closed by account owner.',
            ]);
        }

        // Anonymize personal identifiable information (PII) before soft-deleting
        $anonymizedTag = 'deleted_' . $user->id . '_' . time();
        $user->update([
            'first_name' => 'Deleted',
            'last_name' => 'User',
            'username' => $anonymizedTag,
            'email' => $anonymizedTag . '@erased.k71.in',
            'phone' => null,
            'avatar' => null,
            'two_factor_secret' => null,
            'two_factor_enabled' => false,
        ]);

        // Soft delete the user record
        $user->delete();

        // Terminate session
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home')->with('success', 'Your account and personal identifiers have been permanently anonymized and scheduled for erasure under DPDP Act regulations.');
    }
}

