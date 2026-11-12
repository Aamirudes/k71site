<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\Enquiry;
use App\Models\PortfolioProject;
use App\Models\ProjectContract;
use App\Models\ServiceOrder;
use App\Models\StorefrontAnalyticsEvent;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AnalyticsController extends Controller
{
    public function index(Request $request): View
    {
        $vendor = $request->attributes->get('current_vendor') ?? auth()->user()?->vendor;
        if (!$vendor) {
            abort(403, 'Vendor account required.');
        }

        $entitlements = app(\App\Services\Monetization\EntitlementService::class);
        $sectorKey = $request->attributes->get('current_sector_key') ?? $vendor->getCanonicalSectorKey();
        $authCheck = $entitlements->authorizeSectorWrite($vendor, $sectorKey, 'allow_analytics');
        if (!$authCheck['allowed']) {
            return view('vendor.partials.feature-locked', [
                'vendor' => $vendor,
                'featureTitle' => 'Analytics Not Included in Current Plan',
                'reason' => $authCheck['reason'],
                'sectorKey' => $sectorKey,
            ]);
        }

        $days = in_array((int) $request->query('days', 30), [7, 14, 30], true) ? (int) $request->query('days', 30) : 30;
        $since = Carbon::now()->subDays($days - 1)->startOfDay();

        $eventsInWindow = StorefrontAnalyticsEvent::where('vendor_id', $vendor->id)
            ->where('created_at', '>=', $since);

        $websiteViews = (clone $eventsInWindow)->where('event_type', 'website_view')->count();
        $uniqueVisitors = (clone $eventsInWindow)->where('event_type', 'website_view')->distinct('visitor_hash')->count('visitor_hash');
        $portfolioViews = (clone $eventsInWindow)->where('event_type', 'portfolio_view')->count();
        $enquirySubmits = (clone $eventsInWindow)->where('event_type', 'enquiry_submit')->count();

        $directEnquiriesCount = Enquiry::where('vendor_id', $vendor->id)->count();
        $serviceOrdersCompleted = ServiceOrder::where('vendor_id', $vendor->id)->where('status', 'completed')->count();
        $contractsCompleted = ProjectContract::where('vendor_id', $vendor->id)->where('status', 'completed')->count();

        $topPortfolioIds = StorefrontAnalyticsEvent::where('vendor_id', $vendor->id)
            ->where('event_type', 'portfolio_view')
            ->where('entity_type', 'portfolio_project')
            ->whereNotNull('entity_id')
            ->select('entity_id', DB::raw('COUNT(*) as views'))
            ->groupBy('entity_id')
            ->orderByDesc('views')
            ->take(5)
            ->get();

        $portfolioLookup = PortfolioProject::whereIn('id', $topPortfolioIds->pluck('entity_id'))->get()->keyBy('id');
        $topPortfolio = $topPortfolioIds->map(fn ($row) => [
            'project' => $portfolioLookup->get($row->entity_id),
            'views' => (int) $row->views,
        ])->filter(fn ($row) => $row['project'] !== null)->values();

        $dailyRows = [];
        for ($i = $days - 1; $i >= 0; $i--) {
            $d = Carbon::today()->subDays($i);
            $dayEvents = StorefrontAnalyticsEvent::where('vendor_id', $vendor->id)
                ->whereDate('created_at', $d->toDateString());
            $dailyRows[] = [
                'date' => $d->format('d M'),
                'views' => (clone $dayEvents)->where('event_type', 'website_view')->count(),
                'portfolio' => (clone $dayEvents)->where('event_type', 'portfolio_view')->count(),
                'enquiries' => (clone $dayEvents)->where('event_type', 'enquiry_submit')->count(),
            ];
        }

        return view('vendor.analytics.index', compact(
            'vendor',
            'days',
            'websiteViews',
            'uniqueVisitors',
            'portfolioViews',
            'enquirySubmits',
            'directEnquiriesCount',
            'serviceOrdersCompleted',
            'contractsCompleted',
            'topPortfolio',
            'dailyRows'
        ));
    }
}

