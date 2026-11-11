<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class CustomerListingController extends Controller
{
    /**
     * Unified Customer Listings Management Hub (/account/listings)
     * For managing customer-posted Used Items & Services Gigs without requiring vendor subdomains
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        if (!$user) {
            return redirect()->route('login');
        }

        $activeTab = $request->query('tab', 'used');

        // Customer's Used items (matched by products.user_id, legacy custom_fields->user_id, or vendor owned by user)
        $vendorId = $user->vendor?->id;
        $usedAds = Product::withoutGlobalScopes()
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
            })
            ->latest()
            ->get();

        // Customer's Freelance Services (matched by vendor owned by user or custom_fields)
        $services = Product::withoutGlobalScopes()
            ->with(['images', 'category', 'gigPackages'])
            ->where('vertical', 'services')
            ->where(function ($q) use ($user, $vendorId) {
                if ($vendorId) {
                    $q->where('vendor_id', $vendorId)
                      ->orWhere('custom_fields->user_id', $user->id);
                } else {
                    $q->where('custom_fields->user_id', $user->id);
                }
            })
            ->latest()
            ->get();

        $freelanceProfile = $user->vendor && in_array($user->vendor->vendor_type, ['service', 'services', 'local_services', 'digital_services'], true) ? $user->vendor : null;

        return view('account.listings', compact('user', 'activeTab', 'usedAds', 'services', 'freelanceProfile'));
    }
}

