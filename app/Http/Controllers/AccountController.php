<?php

namespace App\Http\Controllers;

use App\Models\Address;
use App\Models\Order;
use App\Models\Booking;
use App\Models\Enquiry;
use App\Models\Bid;
use App\Models\Offer;
use App\Models\Review;
use App\Models\Message;
use App\Models\ServiceOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

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
                            'product_title' => $item->snapshot['title'] ?? $item->product?->title ?? 'Item',
                            'quantity' => $item->quantity,
                            'unit_price' => $item->unit_price,
                            'total_price' => $item->total_price,
                            'tax_amount' => $item->tax_amount,
                            'gst_rate' => $item->gst_rate,
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
                    'booking_date' => $b->booking_date?->toDateString(),
                    'booking_slot' => $b->booking_slot,
                    'status' => $b->status,
                    'total_amount' => $b->total_amount,
                    'address' => $b->service_address,
                    'created_at' => $b->created_at?->toIso8601String(),
                ];
            });

        // 4. Custom Enquiries & Quotes
        $enquiries = Enquiry::where('user_id', $user->id)
            ->with(['category', 'quotes'])
            ->latest()
            ->get()
            ->map(function ($e) {
                return [
                    'enquiry_number' => $e->enquiry_number,
                    'title' => $e->title,
                    'category' => $e->category?->name,
                    'description' => $e->description,
                    'quantity' => $e->quantity,
                    'target_budget' => $e->target_budget,
                    'city' => $e->city,
                    'urgency' => $e->urgency,
                    'status' => $e->status,
                    'quotes_count' => $e->quotes->count(),
                    'created_at' => $e->created_at?->toIso8601String(),
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
                    'amount' => $bid->bid_amount,
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
                    'title' => $r->title,
                    'body' => $r->body,
                    'is_verified_purchase' => (bool) $r->is_verified_purchase,
                    'created_at' => $r->created_at?->toIso8601String(),
                ];
            });

        // 8. Saved Addresses & Freelance Service Orders
        $addresses = Address::where('user_id', $user->id)
            ->latest()
            ->get()
            ->map(fn ($addr) => [
                'label' => $addr->label,
                'full_name' => $addr->full_name,
                'phone' => $addr->phone,
                'line_1' => $addr->line_1,
                'line_2' => $addr->line_2,
                'city' => $addr->city,
                'state' => $addr->state,
                'pincode' => $addr->pincode,
                'is_default' => (bool) $addr->is_default,
            ]);

        $serviceOrders = ServiceOrder::where('buyer_id', $user->id)
            ->with('product')
            ->latest()
            ->get()
            ->map(fn ($so) => [
                'order_number' => $so->order_number,
                'service' => $so->product?->title ?? 'Service',
                'package_tier' => $so->package_tier,
                'amount' => $so->amount,
                'status' => $so->status,
                'created_at' => $so->created_at?->toIso8601String(),
            ]);

        $exportData = [
            'compliance' => 'Digital Personal Data Protection Act (DPDP), 2023 - Republic of India',
            'exported_at' => now()->toIso8601String(),
            'platform' => 'K71 Marketplace (k71.in)',
            'user' => $profile,
            'addresses' => $addresses,
            'orders' => $orders,
            'bookings' => $bookings,
            'service_orders' => $serviceOrders,
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

        DB::transaction(function () use ($user) {
            // Pull any individual listings (e.g., Used ads) owned directly by this user
            \App\Models\Product::withoutGlobalScopes()
                ->where('user_id', $user->id)
                ->whereIn('status', ['active', 'pending'])
                ->update(['status' => 'inactive']);

            // Take any delivery partner profile offline & unverified
            \App\Models\DeliveryPartner::where('user_id', $user->id)->update([
                'is_online' => false,
                'is_verified' => false,
            ]);

            // If user operates a vendor store, suspend vendor, subscriptions, and pull active public listings
            if ($user->vendor) {
                \App\Models\Product::withoutGlobalScopes()
                    ->where('vendor_id', $user->vendor->id)
                    ->where('status', 'active')
                    ->update(['status' => 'inactive']);

                \App\Models\VendorSubscription::where('vendor_id', $user->vendor->id)
                    ->whereIn('status', [
                        \App\Models\VendorSubscription::STATUS_ACTIVE,
                        \App\Models\VendorSubscription::STATUS_GRACE_PERIOD,
                        \App\Models\VendorSubscription::STATUS_PENDING_ACTIVATION,
                    ])
                    ->update([
                        'status' => \App\Models\VendorSubscription::STATUS_SUSPENDED,
                        'suspended_at' => now(),
                    ]);

                $user->vendor->update([
                    'status' => 'suspended',
                    'vacation_mode' => true,
                    'vacation_message' => 'Store closed by account owner.',
                ]);

                \App\Http\Controllers\SitemapController::clearCache();
            }

            if ($user->avatar && str_starts_with($user->avatar, 'avatars/') && function_exists('k71_delete_public_storage')) {
                k71_delete_public_storage($user->avatar);
            }

            // Anonymize personal identifiable information (PII) before soft-deleting (username <= 20 chars)
            $anonymizedUsername = substr('del_' . $user->id . '_' . base_convert((string) time(), 10, 36), 0, 20);
            $anonymizedEmailTag = 'deleted_' . $user->id . '_' . time();
            $user->update([
                'first_name' => 'Deleted',
                'last_name' => 'User',
                'username' => $anonymizedUsername,
                'email' => $anonymizedEmailTag . '@erased.k71.in',
                'phone' => null,
                'avatar' => null,
                'two_factor_secret' => null,
                'two_factor_enabled' => false,
                'remember_token' => null,
            ]);

            // Soft delete the user record
            $user->delete();
        });

        // Terminate session
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home')->with('success', 'Your account and personal identifiers have been permanently anonymized and scheduled for erasure under DPDP Act regulations.');
    }

    /**
     * Upload or update customer profile avatar.
     */
    public function updateAvatar(Request $request)
    {
        $request->validate([
            'avatar' => 'required|image|mimes:jpeg,png,jpg,webp|max:5120',
        ]);

        /** @var \App\Models\User $user */
        $user = auth()->user();
        if (!$user) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'error' => 'Unauthorized.'], 401);
            }
            return redirect()->route('login');
        }

        $file = $request->file('avatar');
        $avatarMimeMap = [
            'image/jpeg' => 'jpg',
            'image/jpg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
        ];
        $ext = $avatarMimeMap[(string) $file->getMimeType()] ?? 'jpg';
        $filename = 'avatar_' . $user->id . '_' . time() . '.' . $ext;
        
        $storageDir = storage_path('app/public/avatars');
        if (!file_exists($storageDir)) {
            @mkdir($storageDir, 0755, true);
        }
        $publicDir = public_path('storage/avatars');
        if (!file_exists($publicDir)) {
            @mkdir($publicDir, 0755, true);
        }

        $path = $file->storeAs('avatars', $filename, 'public');

        // Copy to public/storage/avatars directly when not symlinked
        try {
            k71_sync_public_storage($path);
        } catch (\Throwable $e) {}
        
        // Remove old local avatar if exists
        if ($user->avatar && str_starts_with($user->avatar, 'avatars/') && $user->avatar !== $path) {
            k71_delete_public_storage($user->avatar);
        }

        $user->avatar = $path;
        $user->save();

        $displayName = trim(($user->first_name ?? '') . ' ' . ($user->last_name ?? ''));
        if ($displayName === '') $displayName = $user->username ?? 'User';
        $avatarUrl = format_user_avatar($path, $displayName);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Profile picture updated successfully!',
                'avatar' => $path,
                'avatar_url' => $avatarUrl,
            ]);
        }

        return redirect()->back()->with('success', 'Profile picture updated successfully!');
    }
}


