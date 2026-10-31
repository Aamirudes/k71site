<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Order;
use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $vendor = Auth::user()->vendor;
        if (!$vendor) abort(403);

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

        $totalRevenue = (int) $realizedQuery->sum(DB::raw('subtotal - discount_amount')); // in paise
        $paidOrdersCount = $realizedQuery->count();

        // 95% Vendor Payout / 5% Marketplace Commission on Product Sales
        $netPayout = (int) round($totalRevenue * 0.95);
        $platformFee = $totalRevenue - $netPayout;
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
        $chartData = [];
        if ($period === 'today' || $period === 'yesterday') {
            // Group by 4-hour intervals
            for ($h = 0; $h < 24; $h += 4) {
                $bStart = $startDate->copy()->addHours($h);
                $bEnd = $bStart->copy()->addHours(4)->subSecond();
                $sum = Order::where('vendor_id', $vendor->id)
                    ->where('payment_status', 'paid')
                    ->where('status', '!=', 'cancelled')
                    ->whereBetween('created_at', [$bStart, $bEnd])
                    ->sum(DB::raw('subtotal - discount_amount'));
                $chartData[] = [
                    'label' => $bStart->format('g A'),
                    'revenue' => round($sum / 100, 2),
                    'net' => round(($sum * 0.95) / 100, 2),
                ];
            }
        } elseif ($period === '7_days' || ($startDate && $endDate && $startDate->diffInDays($endDate) <= 14)) {
            // Group by each day
            $days = $startDate->diffInDays($endDate) + 1;
            for ($d = 0; $d < $days; $d++) {
                $currentDay = $startDate->copy()->addDays($d);
                $sum = Order::where('vendor_id', $vendor->id)
                    ->where('payment_status', 'paid')
                    ->where('status', '!=', 'cancelled')
                    ->whereDate('created_at', $currentDay->toDateString())
                    ->sum(DB::raw('subtotal - discount_amount'));
                $chartData[] = [
                    'label' => $currentDay->format('d M'),
                    'revenue' => round($sum / 100, 2),
                    'net' => round(($sum * 0.95) / 100, 2),
                ];
            }
        } elseif ($period === 'this_month' || $period === 'last_month' || ($startDate && $endDate && $startDate->diffInDays($endDate) <= 31)) {
            // Group by day
            $days = $startDate->diffInDays($endDate) + 1;
            for ($d = 0; $d < $days; $d++) {
                $currentDay = $startDate->copy()->addDays($d);
                $sum = Order::where('vendor_id', $vendor->id)
                    ->where('payment_status', 'paid')
                    ->where('status', '!=', 'cancelled')
                    ->whereDate('created_at', $currentDay->toDateString())
                    ->sum(DB::raw('subtotal - discount_amount'));
                $chartData[] = [
                    'label' => $currentDay->format('d M'),
                    'revenue' => round($sum / 100, 2),
                    'net' => round(($sum * 0.95) / 100, 2),
                ];
            }
        } else {
            // Monthly points
            $monthsCount = ($period === 'this_year') ? max(1, Carbon::now()->month) : 6;
            for ($i = $monthsCount - 1; $i >= 0; $i--) {
                $month = Carbon::now()->subMonths($i);
                $sum = Order::where('vendor_id', $vendor->id)
                    ->where('payment_status', 'paid')
                    ->where('status', '!=', 'cancelled')
                    ->whereYear('created_at', $month->year)
                    ->whereMonth('created_at', $month->month)
                    ->sum(DB::raw('subtotal - discount_amount'));
                $chartData[] = [
                    'label' => $month->format('M Y'),
                    'revenue' => round($sum / 100, 2),
                    'net' => round(($sum * 0.95) / 100, 2),
                ];
            }
        }

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

        // Also ensure any UPI orders awaiting payment review appear in the vendor queue
        $unclaimedUpiOrders = Order::with('user')
            ->where('vendor_id', $vendor->id)
            ->where('payment_method', 'upi')
            ->whereIn('payment_status', ['processing', 'pending'])
            ->whereDoesntHave('paymentClaim')
            ->latest()
            ->get();

        foreach ($unclaimedUpiOrders as $uOrder) {
            $claim = \App\Models\PaymentClaim::firstOrCreate(
                ['order_id' => $uOrder->id],
                [
                    'user_id' => $uOrder->user_id,
                    'utr_number' => 'WAITING_PROOF',
                    'amount_claimed' => $uOrder->total_amount,
                    'status' => 'pending',
                ]
            );
            if ($claim && !$pendingPaymentClaims->contains('id', $claim->id)) {
                $claim->setRelation('order', $uOrder);
                $pendingPaymentClaims->push($claim);
            }
        }

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
            'pendingPaymentClaims'
        ));
    }
}
