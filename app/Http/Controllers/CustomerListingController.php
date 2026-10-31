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

        // Customer's Used items (matched by custom_fields->user_id or vendor owned by user)
        $usedAds = Product::with(['images', 'category'])
            ->where('vertical', 'used')
            ->where(function ($q) use ($user) {
                $q->whereJsonContains('custom_fields->user_id', $user->id)
                  ->orWhereHas('vendor', fn($vq) => $vq->where('user_id', $user->id));
            })
            ->latest()
            ->get();

        // Customer's Freelance Services (matched by vendor owned by user or custom_fields)
        $services = Product::with(['images', 'category', 'gigPackages'])
            ->where('vertical', 'services')
            ->where(function ($q) use ($user) {
                $q->whereHas('vendor', fn($vq) => $vq->where('user_id', $user->id))
                  ->orWhereJsonContains('custom_fields->user_id', $user->id);
            })
            ->latest()
            ->get();

        $freelanceProfile = $user->vendor && $user->vendor->vendor_type === 'service' ? $user->vendor : null;

        return view('account.listings', compact('user', 'activeTab', 'usedAds', 'services', 'freelanceProfile'));
    }
}

