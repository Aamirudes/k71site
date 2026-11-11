<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProjectContract;
use App\Models\Review;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReviewHubController extends Controller
{
    protected function resolveVendor(Request $request)
    {
        $vendor = $request->attributes->get('current_vendor') ?? auth()->user()?->vendor;
        if (!$vendor) {
            abort(403, 'Vendor account required.');
        }

        return $vendor;
    }

    public function index(Request $request): View
    {
        $vendor = $this->resolveVendor($request);
        $productIds = Product::withoutGlobalScopes()->where('vendor_id', $vendor->id)->pluck('id');

        $serviceReviews = Review::with(['user', 'product', 'serviceOrder'])
            ->where('is_approved', true)
            ->where(function ($q) use ($vendor, $productIds) {
                $q->where('vendor_id', $vendor->id);
                if ($productIds->isNotEmpty()) {
                    $q->orWhereIn('product_id', $productIds);
                }
            })
            ->latest('id')
            ->get();

        $contractReviews = ProjectContract::with(['buyer', 'project'])
            ->where('vendor_id', $vendor->id)
            ->whereNotNull('buyer_rating')
            ->latest('reviewed_at')
            ->get();

        $totalCount = $serviceReviews->count() + $contractReviews->count();
        $avgRating = $totalCount > 0
            ? round(($serviceReviews->sum('rating') + $contractReviews->sum('buyer_rating')) / $totalCount, 1)
            : null;

        return view('vendor.reviews.index', compact(
            'vendor',
            'serviceReviews',
            'contractReviews',
            'totalCount',
            'avgRating'
        ));
    }

    public function replyContractReview(Request $request, ProjectContract $contract): RedirectResponse
    {
        $vendor = $this->resolveVendor($request);
        if ((int) $contract->vendor_id !== (int) $vendor->id) {
            abort(403, 'Unauthorized.');
        }

        $validated = $request->validate([
            'vendor_reply' => 'required|string|max:1000',
        ]);

        $contract->update([
            'vendor_reply' => $validated['vendor_reply'],
            'vendor_replied_at' => now(),
        ]);

        $vendor->bumpWebsiteCache();

        return back()->with('success', 'Official response posted on your website and project record.');
    }
}

