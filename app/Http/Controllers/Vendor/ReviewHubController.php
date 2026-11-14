<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProjectContract;
use App\Models\Review;
use App\Services\Monetization\EntitlementService;
use App\Services\NotificationCenterService;
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

        app(EntitlementService::class)->authorizeSectorWrite($vendor, 'services');

        if (empty($contract->buyer_rating)) {
            return back()->with('error', 'Cannot reply to a contract without an existing client review.');
        }

        $replyText = trim((string) ($request->input('vendor_reply') ?? $request->input('reply') ?? ''));
        if ($replyText === '') {
            return back()->with('error', 'Reply text is required.');
        }

        $contract->update([
            'vendor_reply' => $replyText,
            'vendor_replied_at' => now(),
        ]);

        $vendor->bumpWebsiteCache();

        if ($contract->buyer_id) {
            app(NotificationCenterService::class)->notifyUser(
                (int) $contract->buyer_id,
                '💬',
                'Provider Responded to Your Review',
                "{$vendor->shop_name} posted a response to your review on contract #{$contract->contract_number}.",
                route('services.contracts.show', $contract),
                'services_contract'
            );
        }

        return back()->with('success', 'Official response posted on your website and project record.');
    }
}

