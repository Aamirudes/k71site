<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Order;
use App\Models\Product;
use App\Services\Vendor\VendorWorkspaceService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $vendor = Auth::user()->vendor;
        if (!$vendor) abort(403);

        $sectors = app(\App\Services\Monetization\SectorRegistryService::class);
        $entitlements = app(\App\Services\Monetization\EntitlementService::class);

        if ($entitlements->isStrictEnforcementEnabled() && $vendor->isVerifiedMerchant()) {
            $explicitSector = $request->filled('sector') ? $sectors->normalizeSectorKey((string) $request->query('sector')) : null;
            $targetSector = $explicitSector ?: $sectors->resolveVendorPrimarySector($vendor);
            $lockState = $entitlements->resolveSectorLockState($vendor, $targetSector);

            if ($lockState['locked'] && !$explicitSector && !empty($lockState['active_other_sectors'])) {
                $targetSector = $lockState['active_other_sectors'][0];
                $lockState = $entitlements->resolveSectorLockState($vendor, $targetSector);
            }

            if ($lockState['locked']) {
                $renewalRequests = \App\Models\SubscriptionRenewalRequest::with(['requestedPlan', 'subscription'])
                    ->where('vendor_id', $vendor->id)
                    ->orderByDesc('id')
                    ->take(10)
                    ->get();

                return response()->view('vendor.partials.expired-sector-lock', [
                    'vendor' => $vendor,
                    'lockState' => $lockState,
                    'renewalRequests' => $renewalRequests,
                ], 200);
            }
        }

        $period = $request->query('period', 'all');
        $startDateInput = $request->query('start_date');
        $endDateInput = $request->query('end_date');

        $startDate = null;
        $endDate = null;
        $periodLabel = 'All Time (Lifetime)';

        switch ($period) {
            case 'today':
                $startDate = Carbon::today()->startOfDay();
                $endDate = Carbon::today()->endOfDay();
                $periodLabel = 'Today (' . Carbon::today()->format('d M Y') . ')';
                break;

            case 'yesterday':
                $startDate = Carbon::yesterday()->startOfDay();
                $endDate = Carbon::yesterday()->endOfDay();
                $periodLabel = 'Yesterday (' . Carbon::yesterday()->format('d M Y') . ')';
                break;

            case '7_days':
                $startDate = Carbon::today()->subDays(6)->startOfDay();
                $endDate = Carbon::now()->endOfDay();
                $periodLabel = 'Last 7 Days';
                break;

            case 'this_month':
                $startDate = Carbon::now()->startOfMonth()->startOfDay();
                $endDate = Carbon::now()->endOfDay();
                $periodLabel = 'This Month (' . Carbon::now()->format('F Y') . ')';
                break;

            case 'last_month':
                $startDate = Carbon::now()->subMonth()->startOfMonth()->startOfDay();
                $endDate = Carbon::now()->subMonth()->endOfMonth()->endOfDay();
                $periodLabel = 'Last Month (' . Carbon::now()->subMonth()->format('F Y') . ')';
                break;

            case 'this_year':
                $startDate = Carbon::now()->startOfYear()->startOfDay();
                $endDate = Carbon::now()->endOfDay();
                $periodLabel = 'This Year (' . Carbon::now()->format('Y') . ')';
                break;

            case 'custom':
                if ($startDateInput && $endDateInput) {
                    try {
                        $parsedStart = Carbon::parse($startDateInput)->startOfDay();
                        $parsedEnd = Carbon::parse($endDateInput)->endOfDay();
                        if ($parsedStart->gt($parsedEnd)) {
                            $temp = $parsedStart;
                            $parsedStart = $parsedEnd;
                            $parsedEnd = $temp;
                        }
                        $startDate = $parsedStart;
                        $endDate = $parsedEnd;
                        $periodLabel = $startDate->format('d M Y') . ' - ' . $endDate->format('d M Y');
                    } catch (\Exception $e) {
                        $period = 'all';
                        $periodLabel = 'All Time (Lifetime)';
                    }
                } else {
                    $period = 'all';
                    $periodLabel = 'All Time (Lifetime)';
                }
                break;

            case 'all':
            default:
                $period = 'all';
                $startDate = null;
                $endDate = null;
                $periodLabel = 'All Time (Lifetime)';
                break;
        }

        // Base scoped query for vendor's orders in this period
        $periodOrderQuery = Order::where('vendor_id', $vendor->id);
        if ($startDate && $endDate) {
            $periodOrderQuery->whereBetween('created_at', [$startDate, $endDate]);
        }

        // 1. Realized Revenue: STRICTLY payment_status === 'paid' AND status !== 'cancelled'
        // Product Gross Sales = subtotal - discount_amount (platform shipping is retained for logistics)
        $realizedQuery = (clone $periodOrderQuery)
            ->where('payment_status', 'paid')
            ->where('status', '!=', 'cancelled');

        $realizedOrders = (clone $realizedQuery)->with('orderCommission')->get(['id', 'vendor_id', 'subtotal', 'discount_amount', 'order_type']);
        $commissionService = app(\App\Services\Monetization\CommissionService::class);
        $totalRevenue = (int) $realizedOrders->sum(fn($o) => max(0, (int) $o->subtotal - (int) $o->discount_amount)); // in paise
        $paidOrdersCount = $realizedOrders->count();

        // Compute dynamic platform commission & vendor net payout from stored snapshots or CommissionService rules
        $platformFee = (int) $realizedOrders->sum(function ($o) use ($commissionService) {
            return (int) ($o->orderCommission?->calculated_commission_paise ?? $commissionService->calculateBreakdownForOrder($o)['calculated_commission_paise']);
        });
        $netPayout = max(0, $totalRevenue - $platformFee);
        $avgOrderValue = $paidOrdersCount > 0 ? (int) round($totalRevenue / $paidOrdersCount) : 0;

        // 2. Pending / Unpaid Orders in this period
        $pendingQuery = (clone $periodOrderQuery)
            ->where('payment_status', '!=', 'paid')
            ->where('status', '!=', 'cancelled');
        $pendingRevenue = (int) $pendingQuery->sum(DB::raw('subtotal - discount_amount'));
        $pendingOrdersInPeriod = $pendingQuery->count();

        // 3. Cancelled Orders in this period (explicitly excluded from revenue)
        $cancelledQuery = (clone $periodOrderQuery)
            ->where('status', 'cancelled');
        $cancelledRevenue = (int) $cancelledQuery->sum(DB::raw('subtotal - discount_amount'));
        $cancelledOrdersCount = $cancelledQuery->count();

        // 4. Overall operational numbers
        $actionableOrders = Order::where('vendor_id', $vendor->id)
            ->whereIn('status', ['pending', 'processing'])
            ->count();
        $totalProducts = Product::where('vendor_id', $vendor->id)->count();
        $totalOrdersInPeriod = (clone $periodOrderQuery)->count();

        // 5. Recent Orders in period (or latest overall if all)
        $recentOrders = (clone $periodOrderQuery)
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->take(8)
            ->get();

        // 6. Dynamic Chart Data based on period (STRICTLY realized paid revenue)
        $effectiveRateRatio = $totalRevenue > 0
            ? ($netPayout / $totalRevenue)
            : ((100 - (float) $commissionService->resolveEffectiveRule($vendor, $vendor->getCanonicalSectorKey())['rate_percent']) / 100);

        $workspace = app(VendorWorkspaceService::class);
        $chartData = $workspace->buildRealizedRevenueChart($vendor, $period, $startDate, $endDate, $effectiveRateRatio);

        // Fetch customer payment submissions (UTR / Screenshots) waiting for vendor approval
        $pendingPaymentClaims = \App\Models\PaymentClaim::with(['order.user', 'order.items.product'])
            ->whereHas('order', function($q) use ($vendor) {
                $q->where('vendor_id', $vendor->id)
                  ->where('payment_status', '!=', 'paid')
                  ->where('status', '!=', 'cancelled');
            })
            ->whereIn('status', ['submitted', 'pending', 'processing'])
            ->latest()
            ->get();

        // Also ensure any active UPI orders awaiting payment review appear in the vendor queue
        // without mutating payment_claims or colliding on the unique utr_number constraint.
        $unclaimedUpiOrders = Order::with('user')
            ->where('vendor_id', $vendor->id)
            ->where('payment_method', 'upi')
            ->whereIn('payment_status', ['processing', 'pending'])
            ->where('status', '!=', 'cancelled')
            ->whereDoesntHave('paymentClaim')
            ->latest()
            ->get();

        foreach ($unclaimedUpiOrders as $uOrder) {
            if ($pendingPaymentClaims->contains('order_id', $uOrder->id)) {
                continue;
            }

            $claim = new \App\Models\PaymentClaim([
                'order_id' => $uOrder->id,
                'user_id' => $uOrder->user_id,
                'utr_number' => null,
                'amount_claimed' => $uOrder->total_amount,
                'status' => 'pending',
            ]);
            $claim->created_at = $uOrder->created_at ?? now();
            $claim->setRelation('order', $uOrder);
            $pendingPaymentClaims->push($claim);
        }

        // 7. Delivery Partners & Boys Listing for Food, Feasto, Kirana & Express Vendors
        $isFoodOrFeasto = in_array($vendor->vendor_type, ['food', 'feasto', 'groceries', 'kirana']) 
            || (method_exists($vendor, 'isRestaurant') && $vendor->isRestaurant());

        $vLat = null;
        $vLng = null;
        if (is_array($vendor->pickup_address) && !empty($vendor->pickup_address['lat']) && !empty($vendor->pickup_address['lng'])) {
            $vLat = (float) $vendor->pickup_address['lat'];
            $vLng = (float) $vendor->pickup_address['lng'];
        }

        $deliveryPartners = \App\Models\DeliveryPartner::realFleet()->with(['assignments' => function($q) {
            $q->whereIn('status', ['assigned', 'accepted', 'picked_up'])->with('order');
        }])->get()->map(function($dp) use ($vLat, $vLng) {
            $activeAssignment = $dp->assignments->first(function($a) {
                return in_array($a->status, ['assigned', 'accepted', 'picked_up']);
            });
            $dp->active_assignment = $activeAssignment;
            $dp->is_busy = !is_null($activeAssignment);
            $dist = $dp->distanceTo($vLat, $vLng);
            $dp->distance_km = $dist !== null ? round(abs($dist), 1) : null;
            $dp->eta_minutes = $dp->distance_km !== null ? max(3, (int) round($dp->distance_km * 3.5)) : null;
            return $dp;
        })->sortBy(fn ($dp) => [$dp->is_online ? 0 : 1, $dp->distance_km ?? 999999])->values();

        $pendingDispatchOrders = Order::where('vendor_id', $vendor->id)
            ->whereIn('status', ['pending', 'processing'])
            ->where(function($q) {
                $q->whereDoesntHave('deliveryAssignment')
                  ->orWhereHas('deliveryAssignment', function($aq) {
                      $aq->whereIn('status', ['cancelled', 'failed']);
                  });
            })
            ->latest()
            ->get();

        $serviceMetrics = null;
        if ($vendor->isServices()) {
            $serviceMetrics = [
                'active_services' => Product::withoutGlobalScopes()->where('vendor_id', $vendor->id)->where('vertical', 'services')->where('status', 'active')->count(),
                'published_portfolio' => \App\Models\PortfolioProject::where('vendor_id', $vendor->id)->where('status', 'published')->count(),
                'active_orders' => \App\Models\ServiceOrder::where('vendor_id', $vendor->id)->whereIn('status', ['in_progress', 'delivered', 'revision_requested'])->count(),
                'completed_orders' => \App\Models\ServiceOrder::where('vendor_id', $vendor->id)->where('status', 'completed')->count(),
                'open_proposals' => \App\Models\ProjectProposal::where('vendor_id', $vendor->id)->whereIn('status', ['pending', 'shortlisted'])->count(),
                'active_contracts' => \App\Models\ProjectContract::where('vendor_id', $vendor->id)->where('status', 'active')->count(),
                'direct_enquiries' => \App\Models\Enquiry::where('vendor_id', $vendor->id)->where('status', 'open')->count(),
                'website_views_30d' => \App\Models\StorefrontAnalyticsEvent::where('vendor_id', $vendor->id)->where('event_type', 'website_view')->where('created_at', '>=', Carbon::now()->subDays(30))->count(),
                'readiness' => app(\App\Services\Services\ServiceWebsitePresenter::class)->evaluatePublishReadiness($vendor),
            ];
        }

        $foodKitchenMetrics = null;
        if ($isFoodOrFeasto && ($vendor->isFood() || ($vendor->settings?->is_restaurant ?? false))) {
            $foodKitchenMetrics = [
                'operational_status' => app(\App\Services\Food\FoodMarketplaceService::class)->getRestaurantOperationalStatus($vendor),
                'new_orders' => Order::where('vendor_id', $vendor->id)->whereIn('order_type', ['food_delivery', 'food_takeaway'])->where('status', 'pending')->count(),
                'preparing_orders' => Order::where('vendor_id', $vendor->id)->whereIn('order_type', ['food_delivery', 'food_takeaway'])->where('status', 'processing')->count(),
                'ready_orders' => Order::where('vendor_id', $vendor->id)->whereIn('order_type', ['food_delivery', 'food_takeaway'])->where('status', 'ready_for_pickup')->count(),
                'out_for_delivery' => Order::where('vendor_id', $vendor->id)->whereIn('order_type', ['food_delivery', 'food_takeaway'])->where('status', 'shipped')->count(),
                'active_dishes' => Product::withoutGlobalScopes()->where('vendor_id', $vendor->id)->where('vertical', 'food')->where('status', 'active')->where('is_dish_available', true)->count(),
                'paused_dishes' => Product::withoutGlobalScopes()->where('vendor_id', $vendor->id)->where('vertical', 'food')->where('status', 'active')->where('is_dish_available', false)->count(),
                'menu_categories_count' => \App\Models\FoodMenuCategory::where('vendor_id', $vendor->id)->count(),
            ];
        }

        $actionQueue = $workspace->buildActionQueue($vendor, $pendingPaymentClaims->count());
        $activityTimeline = $workspace->buildActivityTimeline($vendor, 10);

        return view('vendor.dashboard', compact(
            'vendor',
            'period',
            'periodLabel',
            'startDateInput',
            'endDateInput',
            'totalRevenue',
            'netPayout',
            'platformFee',
            'avgOrderValue',
            'paidOrdersCount',
            'pendingRevenue',
            'pendingOrdersInPeriod',
            'cancelledRevenue',
            'cancelledOrdersCount',
            'actionableOrders',
            'totalProducts',
            'totalOrdersInPeriod',
            'recentOrders',
            'chartData',
            'pendingPaymentClaims',
            'deliveryPartners',
            'isFoodOrFeasto',
            'pendingDispatchOrders',
            'serviceMetrics',
            'foodKitchenMetrics',
            'actionQueue',
            'activityTimeline'
        ));
    }
}
