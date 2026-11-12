<?php

namespace App\Services\Vendor;

use App\Models\Booking;
use App\Models\Enquiry;
use App\Models\Order;
use App\Models\Product;
use App\Models\ServiceOrder;
use App\Models\SubscriptionEvent;
use App\Models\Vendor;
use App\Models\VendorSubscription;
use App\Services\Monetization\CommissionService;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class VendorWorkspaceService
{
    public function __construct(
        protected CommissionService $commissions
    ) {}

    /**
     * Build realized revenue chart buckets in 1 single database query instead of per-bucket queries.
     *
     * @return array<int, array{label:string,revenue:float,net:float}>
     */
    public function buildRealizedRevenueChart(
        Vendor $vendor,
        string $period,
        ?Carbon $startDate,
        ?Carbon $endDate,
        float $effectiveRateRatio
    ): array {
        $chartData = [];
        $rangeDays = ($startDate && $endDate)
            ? (int) $startDate->copy()->startOfDay()->diffInDays($endDate->copy()->startOfDay())
            : null;

        if ($period === 'today' || $period === 'yesterday') {
            $rows = Order::where('vendor_id', $vendor->id)
                ->where('payment_status', 'paid')
                ->where('status', '!=', 'cancelled')
                ->whereBetween('created_at', [$startDate, $endDate])
                ->get(['created_at', 'subtotal', 'discount_amount']);

            $buckets = array_fill(0, 6, 0);
            foreach ($rows as $row) {
                $hour = (int) optional($row->created_at)->format('G');
                $bucketIdx = min(5, max(0, intdiv($hour, 4)));
                $buckets[$bucketIdx] += max(0, (int) $row->subtotal - (int) $row->discount_amount);
            }

            for ($idx = 0; $idx < 6; $idx++) {
                $bStart = $startDate->copy()->addHours($idx * 4);
                $sum = $buckets[$idx];
                $chartData[] = [
                    'label' => $bStart->format('g A'),
                    'revenue' => round($sum / 100, 2),
                    'net' => round(($sum * $effectiveRateRatio) / 100, 2),
                ];
            }

            return $chartData;
        }

        if ($period === '7_days' || $period === 'this_month' || $period === 'last_month' || ($rangeDays !== null && $rangeDays <= 31)) {
            $rows = Order::where('vendor_id', $vendor->id)
                ->where('payment_status', 'paid')
                ->where('status', '!=', 'cancelled')
                ->whereBetween('created_at', [$startDate, $endDate])
                ->get(['created_at', 'subtotal', 'discount_amount']);

            $dailyTotals = [];
            foreach ($rows as $row) {
                $dayKey = optional($row->created_at)->toDateString();
                if ($dayKey) {
                    $dailyTotals[$dayKey] = ($dailyTotals[$dayKey] ?? 0) + max(0, (int) $row->subtotal - (int) $row->discount_amount);
                }
            }

            $days = ($rangeDays ?? 6) + 1;
            for ($d = 0; $d < $days; $d++) {
                $currentDay = $startDate->copy()->addDays($d);
                $sum = $dailyTotals[$currentDay->toDateString()] ?? 0;
                $chartData[] = [
                    'label' => $currentDay->format('d M'),
                    'revenue' => round($sum / 100, 2),
                    'net' => round(($sum * $effectiveRateRatio) / 100, 2),
                ];
            }

            return $chartData;
        }

        $monthsCount = ($period === 'this_year') ? max(1, Carbon::now()->month) : 6;
        $windowStart = Carbon::now()->subMonths($monthsCount - 1)->startOfMonth()->startOfDay();
        $rows = Order::where('vendor_id', $vendor->id)
            ->where('payment_status', 'paid')
            ->where('status', '!=', 'cancelled')
            ->where('created_at', '>=', $windowStart)
            ->get(['created_at', 'subtotal', 'discount_amount']);

        $monthlyTotals = [];
        foreach ($rows as $row) {
            $monthKey = optional($row->created_at)->format('Y-m');
            if ($monthKey) {
                $monthlyTotals[$monthKey] = ($monthlyTotals[$monthKey] ?? 0) + max(0, (int) $row->subtotal - (int) $row->discount_amount);
            }
        }

        for ($i = $monthsCount - 1; $i >= 0; $i--) {
            $month = Carbon::now()->subMonths($i);
            $sum = $monthlyTotals[$month->format('Y-m')] ?? 0;
            $chartData[] = [
                'label' => $month->format('M Y'),
                'revenue' => round($sum / 100, 2),
                'net' => round(($sum * $effectiveRateRatio) / 100, 2),
            ];
        }

        return $chartData;
    }

    /**
     * Build a genuine prioritized vendor operational task queue from real database conditions.
     *
     * @return array<int, array{priority:string,icon:string,title:string,detail:string,count:int,url:string,cta:string}>
     */
    public function buildActionQueue(Vendor $vendor, int $pendingClaimsCount = 0): array
    {
        $tasks = [];

        if ($pendingClaimsCount > 0) {
            $tasks[] = [
                'priority' => 'high',
                'icon' => '💳',
                'title' => 'Verify UPI Payment Submissions',
                'detail' => "{$pendingClaimsCount} customer payment verification(s) waiting for confirmation.",
                'count' => $pendingClaimsCount,
                'url' => route('vendor.orders.index'),
                'cta' => 'Review Payments',
            ];
        }

        $ordersToProcess = Order::where('vendor_id', $vendor->id)
            ->whereIn('status', ['pending', 'processing'])
            ->count();
        if ($ordersToProcess > 0) {
            $tasks[] = [
                'priority' => 'high',
                'icon' => '📦',
                'title' => 'Fulfill Active Orders',
                'detail' => "{$ordersToProcess} order(s) waiting for packing, kitchen prep, or dispatch.",
                'count' => $ordersToProcess,
                'url' => route('vendor.orders.index'),
                'cta' => 'Open Orders',
            ];
        }

        $pendingBookings = Booking::where('vendor_id', $vendor->id)
            ->where('status', 'pending')
            ->count();
        if ($pendingBookings > 0) {
            $tasks[] = [
                'priority' => 'medium',
                'icon' => '📅',
                'title' => 'Confirm Pending Bookings',
                'detail' => "{$pendingBookings} customer appointment slot(s) awaiting confirmation.",
                'count' => $pendingBookings,
                'url' => route('vendor.bookings.index'),
                'cta' => 'View Bookings',
            ];
        }

        $openServiceOrders = ServiceOrder::where('vendor_id', $vendor->id)
            ->whereIn('status', ['in_progress', 'revision_requested'])
            ->count();
        if ($openServiceOrders > 0) {
            $tasks[] = [
                'priority' => 'medium',
                'icon' => '🛠️',
                'title' => 'Active Service Deliveries',
                'detail' => "{$openServiceOrders} service order(s) require delivery or revision action.",
                'count' => $openServiceOrders,
                'url' => route('vendor.service_orders.index'),
                'cta' => 'Open Workspaces',
            ];
        }

        $openEnquiries = Enquiry::where('vendor_id', $vendor->id)
            ->where('status', 'open')
            ->whereDoesntHave('quotes', fn ($q) => $q->where('vendor_id', $vendor->id))
            ->count();
        if ($openEnquiries > 0) {
            $tasks[] = [
                'priority' => 'medium',
                'icon' => '📋',
                'title' => 'Quote Direct RFQs',
                'detail' => "{$openEnquiries} direct buyer enquiry(ies) have no quote submitted yet.",
                'count' => $openEnquiries,
                'url' => route('vendor.enquiries.index', ['tab' => 'direct']),
                'cta' => 'Send Quotes',
            ];
        }

        $lowStockCount = Product::where('vendor_id', $vendor->id)
            ->where('status', 'active')
            ->whereNotIn('vertical', ['services', 'used', 'enquiry'])
            ->where('total_stock', '<', 5)
            ->count();
        if ($lowStockCount > 0) {
            $tasks[] = [
                'priority' => 'normal',
                'icon' => '⚠️',
                'title' => 'Replenish Low-Stock Listings',
                'detail' => "{$lowStockCount} active listing(s) have fewer than 5 units in stock.",
                'count' => $lowStockCount,
                'url' => route('vendor.products.index', ['stock' => 'low']),
                'cta' => 'Review Inventory',
            ];
        }

        $draftCount = Product::where('vendor_id', $vendor->id)
            ->where('status', 'draft')
            ->count();
        if ($draftCount > 0) {
            $tasks[] = [
                'priority' => 'low',
                'icon' => '📝',
                'title' => 'Finish Draft Listings',
                'detail' => "{$draftCount} draft listing(s) are hidden from buyers until published.",
                'count' => $draftCount,
                'url' => route('vendor.products.index', ['status' => 'draft']),
                'cta' => 'Edit Drafts',
            ];
        }

        $expiringSub = VendorSubscription::where('vendor_id', $vendor->id)
            ->whereIn('status', [VendorSubscription::STATUS_ACTIVE, VendorSubscription::STATUS_GRACE_PERIOD])
            ->whereNotNull('ends_at')
            ->where('ends_at', '<=', now()->addDays(7))
            ->orderBy('ends_at')
            ->first();
        if ($expiringSub) {
            $daysLeft = max(0, (int) now()->diffInDays($expiringSub->ends_at, false));
            $tasks[] = [
                'priority' => 'high',
                'icon' => '🔔',
                'title' => $expiringSub->status === VendorSubscription::STATUS_GRACE_PERIOD ? 'Subscription in Grace Period' : 'Subscription Expiring Soon',
                'detail' => $expiringSub->status === VendorSubscription::STATUS_GRACE_PERIOD
                    ? 'Request renewal with K71 Admin to keep all storefront features uninterrupted.'
                    : "Plan '{$expiringSub->plan_name_snapshot}' ends in {$daysLeft} day(s).",
                'count' => 1,
                'url' => route('vendor.subscription.index'),
                'cta' => 'View Plan',
            ];
        }

        return $tasks;
    }

    /**
     * Build a real unified chronological activity feed for the vendor workspace.
     */
    public function buildActivityTimeline(Vendor $vendor, int $limit = 12): Collection
    {
        $events = collect();

        $orders = Order::where('vendor_id', $vendor->id)
            ->latest('created_at')
            ->take(5)
            ->get(['id', 'order_number', 'status', 'payment_status', 'total_amount', 'created_at']);
        foreach ($orders as $order) {
            $events->push([
                'icon' => '🛍️',
                'category' => 'Order',
                'title' => "Order #{$order->order_number} (" . ucfirst(str_replace('_', ' ', $order->status)) . ")",
                'subtitle' => '₹' . number_format($order->total_amount / 100, 2) . ' • Payment: ' . ucfirst($order->payment_status),
                'url' => route('vendor.orders.show', $order->id),
                'timestamp' => $order->created_at,
            ]);
        }

        $bookings = Booking::where('vendor_id', $vendor->id)
            ->latest('created_at')
            ->take(4)
            ->get(['id', 'booking_number', 'customer_name', 'status', 'booking_date', 'created_at']);
        foreach ($bookings as $booking) {
            $events->push([
                'icon' => '📅',
                'category' => 'Booking',
                'title' => "Booking #{$booking->booking_number} — {$booking->customer_name}",
                'subtitle' => ucfirst($booking->status) . ' • Slot date: ' . optional($booking->booking_date)->format('d M Y'),
                'url' => route('vendor.bookings.index'),
                'timestamp' => $booking->created_at,
            ]);
        }

        $enquiries = Enquiry::where('vendor_id', $vendor->id)
            ->latest('created_at')
            ->take(4)
            ->get(['id', 'enquiry_number', 'title', 'status', 'created_at']);
        foreach ($enquiries as $enquiry) {
            $events->push([
                'icon' => '📋',
                'category' => 'RFQ',
                'title' => "RFQ #{$enquiry->enquiry_number}",
                'subtitle' => $enquiry->title . ' • ' . ucfirst($enquiry->status),
                'url' => route('vendor.enquiries.index', ['tab' => 'direct']),
                'timestamp' => $enquiry->created_at,
            ]);
        }

        $products = Product::where('vendor_id', $vendor->id)
            ->latest('created_at')
            ->take(4)
            ->get(['id', 'title', 'status', 'vertical', 'created_at']);
        foreach ($products as $prod) {
            $events->push([
                'icon' => '✨',
                'category' => 'Catalog',
                'title' => "Listing: {$prod->title}",
                'subtitle' => ucfirst($prod->vertical ?: 'marketplace') . ' • ' . ucfirst($prod->status),
                'url' => route('vendor.products.edit', $prod->id),
                'timestamp' => $prod->created_at,
            ]);
        }

        $subEvents = SubscriptionEvent::where('vendor_id', $vendor->id)
            ->latest('created_at')
            ->take(4)
            ->get(['id', 'event_type', 'sector_key', 'summary', 'created_at']);
        foreach ($subEvents as $se) {
            $events->push([
                'icon' => '🛡️',
                'category' => 'Plan',
                'title' => ucwords(str_replace('_', ' ', (string) $se->event_type)),
                'subtitle' => (string) ($se->summary ?: strtoupper((string) $se->sector_key)),
                'url' => route('vendor.subscription.index'),
                'timestamp' => $se->created_at,
            ]);
        }

        return $events
            ->filter(fn ($e) => !empty($e['timestamp']))
            ->sortByDesc('timestamp')
            ->take($limit)
            ->values();
    }
}

