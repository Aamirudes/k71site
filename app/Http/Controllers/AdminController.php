<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Vendor;
use App\Models\Order;
use App\Models\Product;
use App\Models\OrderStatusLog;
use App\Models\Report;
use App\Models\Promotion;
use App\Models\AuditLog;
use App\Models\Review;
use App\Models\Short;
use App\Models\Category;
use App\Models\UsedSellerProfile;
use App\Models\UsedThreadMessage;
use App\Services\SettlementService;
use App\Services\PayoutService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{
    /**
     * Executive Marketplace Command Center
     */
    public function dashboard()
    {
        app(\App\Services\SettlementService::class)->autoClearDueOrders();

        // 1. Treasury Ledger & Reconciliations
        $paidOrders = Order::where('payment_status', 'paid')->get();
        $paidPayoutOrders = Order::with('orderCommission')->where('vendor_payout_status', 'paid')->get(['id', 'vendor_id', 'subtotal', 'discount_amount', 'vendor_payout_amount', 'order_type']);

        $grossGmv = $paidOrders->sum('total_amount');
        $settledCash = $paidOrders->where('settlement_status', 'cleared')->sum('total_amount');
        $clearingBuffer = $paidOrders->where('settlement_status', 'pending')->sum('total_amount');
        $vendorDisbursed = Order::where('vendor_payout_status', 'paid')->sum('vendor_payout_amount');
        $commissionService = app(\App\Services\Monetization\CommissionService::class);
        $platformProfit = $paidPayoutOrders->sum(function ($o) use ($commissionService) {
            if ($o->orderCommission) {
                return (int) $o->orderCommission->calculated_commission_paise;
            }
            return (int) $commissionService->calculateBreakdownForOrder($o)['calculated_commission_paise'];
        });
        $shippingPool = $paidOrders->sum('shipping_amount');

        // Ready for payout (cleared, not paid yet, not cancelled)
        $readyPayoutOrders = Order::with('orderCommission')->where('settlement_status', 'cleared')
            ->where('vendor_payout_status', '!=', 'paid')
            ->where('status', '!=', 'cancelled')
            ->get();
        $pendingDisbursalAmount = $readyPayoutOrders->sum(fn($o) => $o->vendor_payout_amount ?: (int) ($o->orderCommission?->vendor_net_paise ?? $commissionService->calculateBreakdownForOrder($o)['vendor_net_paise']));

        // 2. Fulfillment Pipeline Metrics
        $pipeline = [
            'pending' => Order::where('status', 'pending')->count(),
            'processing' => Order::where('status', 'processing')->count(),
            'shipped' => Order::where('status', 'shipped')->count(),
            'delivered' => Order::where('status', 'delivered')->count(),
            'cancelled' => Order::where('status', 'cancelled')->count(),
        ];

        // 3. Operational Health Checks
        $health = [
            'pending_vendors' => Vendor::where('status', 'pending')->count(),
            'active_vendors' => Vendor::where('status', 'active')->count(),
            'total_vendors' => Vendor::count(),
            'unsettled_orders' => $paidOrders->where('settlement_status', 'pending')->count(),
            'ready_payout_count' => $readyPayoutOrders->count(),
            'low_stock_products' => Product::withoutGlobalScopes()->where('total_stock', '<=', 3)->where('total_stock', '>', 0)->count(),
            'out_of_stock_products' => Product::withoutGlobalScopes()->where('total_stock', '<=', 0)->count(),
            'total_products' => Product::withoutGlobalScopes()->count(),
            'total_customers' => User::doesntHave('vendor')->count(),
            'total_users' => User::count(),
        ];

        // 4. Recent Transactions with Full Relationships
        $recentOrders = Order::with(['vendor.settings', 'user'])
            ->latest()
            ->take(8)
            ->get();

        // 5. Top 5 Performing Boutiques
        $topVendors = Vendor::withCount(['orders' => function ($q) {
                $q->where('payment_status', 'paid');
            }])
            ->with(['settings', 'user'])
            ->get()
            ->map(function ($vendor) {
                $orders = $vendor->orders()->where('payment_status', 'paid')->get();
                $vendor->gmv = $orders->sum('total_amount');
                $vendor->disbursed = $orders->where('vendor_payout_status', 'paid')->sum('vendor_payout_amount');
                return $vendor;
            })
            ->sortByDesc('gmv')
            ->take(5);

        return view('admin.dashboard', compact(
            'grossGmv',
            'settledCash',
            'clearingBuffer',
            'vendorDisbursed',
            'platformProfit',
            'shippingPool',
            'pendingDisbursalAmount',
            'pipeline',
            'health',
            'recentOrders',
            'topVendors'
        ));
    }

    /**
     * All Marketplace Orders with Universal Search, Deep Filters, and Auditing
     */
    public function orders(Request $request)
    {
        app(\App\Services\SettlementService::class)->autoClearDueOrders();

        $query = Order::with(['vendor.settings', 'user', 'items.product', 'shipment']);

        // Universal Search
        if ($search = trim($request->input('search', ''))) {
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                  ->orWhere('idempotency_key', 'like', "%{$search}%")
                  ->orWhere('settlement_batch_ref', 'like', "%{$search}%")
                  ->orWhere('vendor_payout_utr', 'like', "%{$search}%")
                  ->orWhere('tracking_number', 'like', "%{$search}%")
                  ->orWhere('address_snapshot', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($uq) use ($search) {
                      $uq->where('first_name', 'like', "%{$search}%")
                         ->orWhere('last_name', 'like', "%{$search}%")
                         ->orWhere('email', 'like', "%{$search}%")
                         ->orWhere('phone', 'like', "%{$search}%");
                  })
                  ->orWhereHas('vendor', function ($vq) use ($search) {
                      $vq->where('shop_name', 'like', "%{$search}%");
                  });
            });
        }

        // Filter: Settlement Status
        if ($settlement = $request->input('settlement')) {
            if ($settlement === 'cleared') {
                $query->where('settlement_status', 'cleared');
            } elseif ($settlement === 'pending') {
                $query->where('settlement_status', 'pending');
            }
        }

        // Filter: Vendor Payout Status
        if ($payout = $request->input('payout')) {
            $query->where('vendor_payout_status', $payout);
        }

        // Filter: Fulfillment Status
        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        // Filter: Vendor
        if ($vendorId = $request->input('vendor_id')) {
            $query->where('vendor_id', $vendorId);
        }

        // Filter: Date Range
        if ($dateFilter = $request->input('date')) {
            if ($dateFilter === 'today') {
                $query->whereDate('created_at', Carbon::today());
            } elseif ($dateFilter === 'this_week') {
                $query->whereBetween('created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()]);
            } elseif ($dateFilter === 'this_month') {
                $query->whereMonth('created_at', Carbon::now()->month)->whereYear('created_at', Carbon::now()->year);
            }
        }

        $orders = $query->latest()->paginate(20)->withQueryString();

        // High-level financial cards
        $paidPayoutOrders = Order::with('orderCommission')->where('vendor_payout_status', 'paid')->get(['id', 'vendor_id', 'subtotal', 'discount_amount', 'vendor_payout_amount', 'order_type']);
        $commissionService = app(\App\Services\Monetization\CommissionService::class);
        $settlementStats = [
            'gross_volume' => Order::where('payment_status', 'paid')->sum('total_amount'),
            'settled_volume' => Order::where('payment_status', 'paid')->where('settlement_status', 'cleared')->sum('total_amount'),
            'pending_settlement' => Order::where('payment_status', 'paid')->where('settlement_status', 'pending')->sum('total_amount'),
            'vendor_disbursed' => Order::where('vendor_payout_status', 'paid')->sum('vendor_payout_amount'),
            'platform_retained' => $paidPayoutOrders->sum(fn($o) => (int) ($o->orderCommission?->calculated_commission_paise ?? $commissionService->calculateBreakdownForOrder($o)['calculated_commission_paise'])),
        ];

        // Counts for tabs/badges
        $counts = [
            'all' => Order::count(),
            'clearing_buffer' => Order::where('payment_status', 'paid')->where('settlement_status', 'pending')->count(),
            'ready_for_payout' => Order::where('settlement_status', 'cleared')->where('vendor_payout_status', '!=', 'paid')->where('status', '!=', 'cancelled')->count(),
            'payout_completed' => Order::where('vendor_payout_status', 'paid')->count(),
            'unfulfilled' => Order::whereIn('status', ['pending', 'processing'])->count(),
            'in_transit' => Order::where('status', 'shipped')->count(),
            'delivered' => Order::where('status', 'delivered')->count(),
        ];

        $vendorsList = Vendor::orderBy('shop_name')->get(['id', 'shop_name']);

        return view('admin.orders.index', compact('orders', 'settlementStats', 'counts', 'vendorsList'));
    }

    /**
     * Dedicated Order Detail & Audit View for Master Admin
     */
    public function showOrder(Order $order)
    {
        $order->load([
            'user',
            'vendor.settings',
            'items.product.images',
            'items.variant',
            'paymentClaim',
            'shipment',
            'statusLogs',
        ]);

        $subtotal = $order->subtotal;
        $discount = $order->discount_amount;
        $shipping = $order->shipping_amount;
        $total = $order->total_amount;
        
        $productNet = max(0, $subtotal - $discount);
        $commissionRecord = $order->orderCommission;
        $platformFee = $commissionRecord
            ? (int) $commissionRecord->calculated_commission_paise
            : (int) app(\App\Services\Monetization\CommissionService::class)->calculateBreakdownForOrder($order)['calculated_commission_paise'];
        $vendorNet = $order->vendor_payout_amount ?: ($commissionRecord ? (int) $commissionRecord->vendor_net_paise : max(0, $productNet - $platformFee));

        return view('admin.orders.show', compact('order', 'subtotal', 'discount', 'shipping', 'total', 'platformFee', 'vendorNet'));
    }

    /**
     * Mark Order Settled Manually by Admin
     */
    public function markSettled(Request $request, Order $order, SettlementService $settlementService)
    {
        if ($order->payment_status !== 'paid') {
            return back()->with('error', "Cannot mark order as settled: Order payment has not been marked as paid.");
        }

        $settlementService->markSettled($order, null, 'master_admin');

        return back()->with('success', "Order #{$order->order_number} settlement marked as CLEARED into Current Account. Vendor payout is now unlocked.");
    }

    /**
     * Disburse Payout to Vendor Manually by Admin
     */
    public function disbursePayout(Request $request, Order $order, PayoutService $payoutService)
    {
        if ($order->settlement_status !== 'cleared') {
            return back()->with('error', "Cannot disburse payout: Bank settlement has not cleared yet.");
        }

        try {
            $result = $payoutService->disburseVendorPayout($order);
            return back()->with('success', "Instant payout of ₹" . number_format($result['amount'] / 100, 2) . " disbursed to {$result['vpa']}. UTR: {$result['utr']}");
        } catch (\Exception $e) {
            return back()->with('error', "Payout disbursement failed: " . $e->getMessage());
        }
    }

    /**
     * 1-Click Run Daily Settlement Clearing Batch
     */
    public function processBatchSettlements(Request $request, SettlementService $settlementService)
    {
        $orders = Order::where('payment_status', 'paid')
            ->where('settlement_status', 'pending')
            ->get();

        if ($orders->isEmpty()) {
            return back()->with('info', "No pending settlements in queue. All eligible orders are already cleared.");
        }

        $clearedCount = 0;
        $totalAmountPaise = 0;

        foreach ($orders as $order) {
            $settlementService->markSettled($order, null, 'master_admin_batch');
            $clearedCount++;
            $totalAmountPaise += $order->total_amount;
        }

        $clearedRupees = number_format($totalAmountPaise / 100, 2);
        return back()->with('success', "⚡ Batch Settlement Processed! Cleared {$clearedCount} order(s) totaling ₹{$clearedRupees} into Current Account. Vendor payouts unlocked.");
    }

    /**
     * Append Administrative Audit Note to Order
     */
    public function addOrderNote(Request $request, Order $order)
    {
        $validated = $request->validate([
            'note' => 'required|string|max:1000',
        ]);

        OrderStatusLog::create([
            'order_id' => $order->id,
            'from_status' => $order->status,
            'to_status' => $order->status,
            'actor_type' => 'master_admin',
            'actor_id' => auth()->id(),
            'note' => "Admin Note: " . $validated['note'],
            'ip_address' => $request->ip(),
        ]);

        return back()->with('success', "Audit note recorded successfully on Order #{$order->order_number}.");
    }

    /**
     * Manage Vendors & Verification
     */
    public function vendors(Request $request)
    {
        $query = Vendor::with(['user', 'settings'])
            ->withCount(['orders', 'products']);

        if ($search = trim($request->input('search', ''))) {
            $query->where(function ($q) use ($search) {
                $q->where('shop_name', 'like', "%{$search}%")
                  ->orWhere('slug', 'like', "%{$search}%")
                  ->orWhere('contact_email', 'like', "%{$search}%")
                  ->orWhere('contact_phone', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($uq) use ($search) {
                      $uq->where('first_name', 'like', "%{$search}%")
                         ->orWhere('last_name', 'like', "%{$search}%")
                         ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }

        if ($status = $request->input('status')) {
            if ($status === 'active') {
                $query->whereIn('status', ['active', 'approved']);
            } else {
                $query->where('status', $status);
            }
        }

        if ($request->input('type') === 'food') {
            $query->where(function ($fq) {
                $fq->where('vendor_type', 'food')
                   ->orWhereHas('settings', fn ($sq) => $sq->where('is_restaurant', true));
            });
        }

        if ($request->input('feature') === 'kinetic') {
            $query->where('can_use_kinetic_scroll', true);
        }

        $vendors = $query->latest()->paginate(20)->withQueryString();

        // Calculate GMV for each vendor on current page
        foreach ($vendors as $vendor) {
            $vendor->total_gmv = $vendor->orders()->where('payment_status', 'paid')->sum('total_amount');
            $vendor->total_payouts = $vendor->orders()->where('vendor_payout_status', 'paid')->sum('vendor_payout_amount');
        }

        $counts = [
            'all' => Vendor::count(),
            'active' => Vendor::whereIn('status', ['active', 'approved'])->count(),
            'pending' => Vendor::where('status', 'pending')->count(),
            'suspended' => Vendor::where('status', 'suspended')->count(),
            'food' => Vendor::where(function ($fq) {
                $fq->where('vendor_type', 'food')
                   ->orWhereHas('settings', fn ($sq) => $sq->where('is_restaurant', true));
            })->count(),
            'kinetic' => Vendor::where('can_use_kinetic_scroll', true)->count(),
        ];

        return view('admin.vendors.index', compact('vendors', 'counts'));
    }

    public function updateVendorStatus(Request $request, Vendor $vendor)
    {
        $validated = $request->validate([
            'status' => 'required|in:active,pending,suspended',
        ]);

        $vendor->update(['status' => $validated['status']]);
        \App\Http\Controllers\SitemapController::clearCache();

        return back()->with('success', "Boutique {$vendor->shop_name} status updated to " . strtoupper($validated['status']) . ".");
    }

    public function toggleKineticScroll(Request $request, Vendor $vendor)
    {
        $enabled = $request->has('enabled')
            ? $request->boolean('enabled')
            : !$vendor->hasKineticScrollAccess();

        $vendor->forceFill(['can_use_kinetic_scroll' => $enabled])->save();

        if (!$enabled) {
            $presetService = app(\App\Services\Storefront\StorefrontPresetService::class);
            if ($presetService->normalizePresetKey((string) $vendor->layout_preset) === 'kinetic') {
                $vendor->update(['layout_preset' => 'velora']);
            }
            $cfg = $vendor->storefrontConfig;
            if ($cfg) {
                $draft = is_array($cfg->draft_config) ? $cfg->draft_config : [];
                $pub = is_array($cfg->published_config) ? $cfg->published_config : [];
                $changed = false;
                if (($cfg->preset_key ?? '') === 'kinetic' || ($draft['preset'] ?? '') === 'kinetic') {
                    $draft = $presetService->sanitizeConfig(array_merge($draft, ['preset' => 'velora']), $vendor);
                    $changed = true;
                }
                if (($pub['preset'] ?? '') === 'kinetic') {
                    $pub = $presetService->sanitizeConfig(array_merge($pub, ['preset' => 'velora']), $vendor);
                    $changed = true;
                }
                if ($changed) {
                    $cfg->update([
                        'preset_key' => 'velora',
                        'draft_config' => $draft,
                        'published_config' => $pub,
                    ]);
                }
            }
        }

        $vendor->bumpWebsiteCache();

        try {
            AuditLog::record(
                $enabled ? 'vendor.kinetic_scroll_enabled' : 'vendor.kinetic_scroll_disabled',
                $vendor,
                null,
                [
                    'vendor_id' => $vendor->id,
                    'can_use_kinetic_scroll' => $enabled,
                ]
            );
        } catch (\Throwable $e) {}

        $msg = $enabled
            ? "Scroll Motion (Kinetic Video-to-JPG) enabled for {$vendor->shop_name}."
            : "Scroll Motion (Kinetic Video-to-JPG) disabled for {$vendor->shop_name}.";

        if ($request->expectsJson()) {
            return response()->json([
                'status' => 'success',
                'can_use_kinetic_scroll' => $enabled,
                'message' => $msg,
            ]);
        }

        return back()->with('success', $msg);
    }

    public function verifyFssai(Request $request, Vendor $vendor)
    {
        $validated = $request->validate([
            'verified' => 'required|boolean',
        ]);

        $settings = \App\Models\VendorSetting::firstOrCreate(['vendor_id' => $vendor->id]);
        $verify = (bool) $validated['verified'];

        if ($verify && empty(trim((string) ($settings->fssai_license_number ?? '')))) {
            return back()->with('error', 'Cannot verify FSSAI: this kitchen has not entered an FSSAI license number yet.');
        }

        $settings->update([
            'fssai_verified' => $verify,
            'fssai_verified_at' => $verify ? now() : null,
        ]);

        try {
            AuditLog::record(
                $verify ? 'vendor.fssai_verified' : 'vendor.fssai_revoked',
                $vendor,
                null,
                [
                    'vendor_id' => $vendor->id,
                    'fssai_license_number' => $settings->fssai_license_number,
                    'verified' => $verify,
                ]
            );
        } catch (\Throwable $e) {}

        return back()->with('success', $verify
            ? "FSSAI license verified for {$vendor->shop_name}."
            : "FSSAI verification revoked for {$vendor->shop_name}.");
    }

    /**
     * Manage Global Marketplace Products & Inventory
     */
    public function products(Request $request)
    {
        $query = Product::withoutGlobalScope(\App\Scopes\VendorScope::class)->with(['vendor', 'sellerUser.usedSellerProfile', 'category', 'images']);

        if ($search = trim($request->input('search', ''))) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%")
                  ->orWhereHas('vendor', function ($vq) use ($search) {
                      $vq->where('shop_name', 'like', "%{$search}%");
                  })
                  ->orWhereHas('sellerUser', function ($uq) use ($search) {
                      $uq->where('first_name', 'like', "%{$search}%")
                         ->orWhere('last_name', 'like', "%{$search}%")
                         ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }

        if ($vertical = $request->input('vertical')) {
            if ($vertical === 'used') {
                $query->where(function ($q) {
                    $q->where('vertical', 'used')->orWhere('listing_type', 'classified');
                });
            }
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        if ($stock = $request->input('stock')) {
            if ($stock === 'low') {
                $query->where('total_stock', '<=', 3)->where('total_stock', '>', 0);
            } elseif ($stock === 'out') {
                $query->where('total_stock', '<=', 0);
            }
        }

        $products = $query->latest()->paginate(20)->withQueryString();

        $counts = [
            'all' => Product::withoutGlobalScope(\App\Scopes\VendorScope::class)->count(),
            'active' => Product::withoutGlobalScope(\App\Scopes\VendorScope::class)->where('status', 'active')->count(),
            'used' => Product::withoutGlobalScope(\App\Scopes\VendorScope::class)->where(fn($q) => $q->where('vertical', 'used')->orWhere('listing_type', 'classified'))->count(),
            'low_stock' => Product::withoutGlobalScope(\App\Scopes\VendorScope::class)->where('total_stock', '<=', 3)->where('total_stock', '>', 0)->count(),
            'out_of_stock' => Product::withoutGlobalScope(\App\Scopes\VendorScope::class)->where('total_stock', '<=', 0)->count(),
        ];

        return view('admin.products.index', compact('products', 'counts'));
    }

    public function deleteProduct(Product $product)
    {
        $title = $product->title;
        $product->variants()->forceDelete();
        if (method_exists($product, 'images')) {
            foreach ($product->images as $img) {
                if ($img->path) {
                    k71_delete_public_storage($img->path);
                }
            }
            $product->images()->delete();
        }
        if ($product->video_path) {
            k71_delete_public_storage($product->video_path);
        }
        $product->forceDelete();
        \App\Http\Controllers\SitemapController::clearCache();

        return back()->with('success', "Product '{$title}' has been completely removed from site.");
    }

    /**
     * Reports & trust moderation queue.
     */
    public function reports(Request $request)
    {
        $status = $request->input('status', 'all');
        $query = Report::with(['user', 'reportable', 'resolver'])->latest();

        if ($status === 'pending') {
            $query->where('status', 'pending');
        } elseif ($status === 'resolved') {
            $query->whereIn('status', ['dismissed', 'action_taken']);
        }

        $reports = $query->paginate(20)->withQueryString();

        $counts = [
            'all' => Report::count(),
            'pending' => Report::where('status', 'pending')->count(),
            'resolved' => Report::whereIn('status', ['dismissed', 'action_taken'])->count(),
        ];

        return view('admin.reports.index', compact('reports', 'counts', 'status'));
    }

    /**
     * Action or dismiss moderation report.
     */
    public function resolveReport(Request $request, Report $report)
    {
        $validated = $request->validate([
            'action' => 'required|in:dismiss,block_product,warn_vendor,delete_review',
            'admin_notes' => 'nullable|string|max:1000',
        ]);

        $action = $validated['action'];
        $notes = $validated['admin_notes'] ?? null;

        // Execute real enforcement action based on target
        $reportable = $report->reportable;
        if ($action === 'block_product' && $reportable instanceof Product) {
            $updates = ['status' => 'inactive'];
            if ($reportable->isUsedListing()) {
                $updates['used_status'] = 'paused';
            }
            $reportable->update($updates);
            \App\Http\Controllers\SitemapController::clearCache();
        } elseif ($action === 'warn_vendor') {
            if ($reportable instanceof Vendor) {
                $reportable->update(['status' => 'suspended']);
                \App\Http\Controllers\SitemapController::clearCache();
            } elseif ($reportable instanceof UsedSellerProfile) {
                $reportable->user?->update([
                    'is_blocked' => true,
                    'blocked_reason' => $notes ?: 'Used marketplace account suspended by moderation.',
                ]);
            } elseif ($reportable instanceof User) {
                $reportable->update([
                    'is_blocked' => true,
                    'blocked_reason' => $notes ?: 'Account suspended by moderation.',
                ]);
            }
        } elseif ($action === 'delete_review') {
            if ($reportable instanceof Review) {
                $reportable->update(['is_approved' => false]);
            } elseif ($reportable instanceof UsedThreadMessage) {
                $reportable->update(['body' => '[Message removed by moderation]']);
            }
        }

        $report->update([
            'status' => $action === 'dismiss' ? 'dismissed' : 'action_taken',
            'action_taken' => $action,
            'admin_notes' => $notes,
            'resolved_by' => auth()->id(),
            'resolved_at' => now(),
        ]);

        AuditLog::create([
            'user_id' => auth()->id(),
            'auditable_type' => $report->reportable_type,
            'auditable_id' => $report->reportable_id,
            'action' => 'moderation_' . $action,
            'new_values' => ['report_id' => $report->id, 'status' => $report->status, 'notes' => $notes],
            'ip_address' => $request->ip(),
        ]);

        return back()->with('success', 'Report has been processed and status updated.');
    }

    /**
     * Promotions & Flash sales hub.
     */
    public function promotions(Request $request)
    {
        $promotions = Promotion::with('vendor')->latest()->paginate(20);

        return view('admin.promotions.index', compact('promotions'));
    }

    /**
     * Store new promotion campaign.
     */
    public function storePromotion(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:120',
            'badge' => 'nullable|string|max:30',
            'discount_type' => 'required|in:percentage,flat',
            'discount_value' => 'required|numeric|min:1',
            'min_spend' => 'nullable|numeric|min:0',
            'starts_at' => 'required|date',
            'ends_at' => 'required|date|after:starts_at',
        ]);

        $slug = \Illuminate\Support\Str::slug($validated['title']) . '-' . rand(100, 999);

        // Convert flat discount to paise if flat
        $discountVal = $validated['discount_type'] === 'flat' 
            ? (int) round($validated['discount_value'] * 100) 
            : (int) $validated['discount_value'];

        $minSpendPaise = !empty($validated['min_spend']) 
            ? (int) round($validated['min_spend'] * 100) 
            : null;

        Promotion::create([
            'title' => $validated['title'],
            'slug' => $slug,
            'badge' => $validated['badge'] ?? null,
            'discount_type' => $validated['discount_type'],
            'discount_value' => $discountVal,
            'min_spend' => $minSpendPaise,
            'starts_at' => $validated['starts_at'],
            'ends_at' => $validated['ends_at'],
            'is_active' => true,
        ]);

        return back()->with('success', 'Promotion campaign created successfully.');
    }

    /**
     * Toggle active state of promotion.
     */
    public function togglePromotion(Promotion $promotion)
    {
        $promotion->update([
            'is_active' => !$promotion->is_active,
        ]);

        return back()->with('success', "Promotion {$promotion->title} status toggled.");
    }

    /**
     * Audit logs browser.
     */
    public function auditLogs(Request $request)
    {
        $query = AuditLog::with('user')->latest();

        if ($action = $request->input('action')) {
            $query->where('action', $action);
        }

        $logs = $query->paginate(30)->withQueryString();

        return view('admin.audit-logs.index', compact('logs'));
    }

    /**
     * Users Directory & Master Account Control
     */
    public function users(Request $request)
    {
        $query = User::with(['vendor', 'roles'])->withCount(['orders']);

        if ($search = trim($request->input('search', ''))) {
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhereHas('vendor', fn($vq) => $vq->where('shop_name', 'like', "%{$search}%"));
            });
        }

        if ($role = $request->input('role')) {
            if ($role === 'admin') {
                $query->where(function($q) {
                    $q->whereHas('roles', fn($rq) => $rq->where('name', 'admin'))
                      ->orWhereIn('email', ['crab.hashir@gmail.com', 'hashir0867@gmail.com']);
                });
            } elseif ($role === 'vendor') {
                $query->whereHas('vendor');
            } elseif ($role === 'customer') {
                $query->doesntHave('vendor')->whereDoesntHave('roles', fn($rq) => $rq->where('name', 'admin'));
            }
        }

        $users = $query->latest()->paginate(25)->withQueryString();

        $counts = [
            'all' => User::count(),
            'admins' => User::whereHas('roles', fn($rq) => $rq->where('name', 'admin'))
                            ->orWhereIn('email', ['crab.hashir@gmail.com', 'hashir0867@gmail.com'])
                            ->count(),
            'vendors' => User::has('vendor')->count(),
            'customers' => User::doesntHave('vendor')->count(),
        ];

        return view('admin.users.index', compact('users', 'counts'));
    }

    /**
     * Delete user permanently or soft-delete safely
     */
    public function deleteUser(Request $request, User $user)
    {
        // Prevent deleting self
        if ($user->id === auth()->id()) {
            return back()->with('error', "Cannot delete your own account while logged in as Master Admin.");
        }

        $name = $user->name ?: $user->email;

        // If user is a vendor, delete or detach vendor
        if ($user->vendor) {
            $user->vendor->products()->delete();
            $user->vendor->delete();
        }

        // Delete user's cart, sessions, etc.
        DB::table('cart_items')->whereIn('cart_id', function($q) use ($user) {
            $q->select('id')->from('carts')->where('user_id', $user->id);
        })->delete();
        DB::table('carts')->where('user_id', $user->id)->delete();
        DB::table('wishlists')->where('user_id', $user->id)->delete();

        $user->delete();

        return back()->with('success', "User '{$name}' (ID #{$user->id}) was permanently deleted by Master Admin.");
    }

    /**
     * Update user role (admin, vendor, customer)
     */
    public function updateUserRole(Request $request, User $user)
    {
        $validated = $request->validate([
            'role' => 'required|in:admin,vendor,customer,user',
        ]);

        $roleName = $validated['role'] === 'user' ? 'customer' : $validated['role'];
        $roleModel = \App\Models\Role::firstOrCreate(['name' => $roleName], ['guard_name' => 'web']);
        $user->roles()->sync([$roleModel->id]);

        return back()->with('success', "Role for {$user->name} updated to " . strtoupper($roleName) . ".");
    }

    /**
     * Delete Vendor by Master Admin
     */
    public function deleteVendor(Request $request, Vendor $vendor)
    {
        $shop = $vendor->shop_name;
        $id = $vendor->id;

        // Force delete associated products, variants, images, shorts & vendor
        $products = Product::withoutGlobalScopes()->where('vendor_id', $vendor->id)->get();
        foreach ($products as $p) {
            $p->variants()->forceDelete();
            if (method_exists($p, 'images')) {
                foreach ($p->images as $img) {
                    if ($img->path) {
                        @unlink(storage_path('app/public/' . $img->path));
                        @unlink(public_path('storage/' . $img->path));
                    }
                }
                $p->images()->delete();
            }
            if ($p->video_path) {
                @unlink(storage_path('app/public/' . $p->video_path));
                @unlink(public_path('storage/' . $p->video_path));
            }
            $p->forceDelete();
        }

        // Delete associated shorts
        \App\Models\Short::withoutGlobalScopes()->where('vendor_id', $vendor->id)->forceDelete();

        $vendor->forceDelete();

        return back()->with('success', "Vendor boutique '{$shop}' (ID #{$id}) and all associated catalog items have been permanently removed.");
    }

    /**
     * Services & Freelance Moderation Hub for Master Admin
     */
    public function services(Request $request)
    {
        $query = Product::withoutGlobalScopes()
            ->where('vertical', 'services')
            ->with(['vendor', 'category', 'images', 'gigPackages']);

        if ($search = trim($request->input('search', ''))) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhereHas('vendor', fn($vq) => $vq->where('shop_name', 'like', "%{$search}%"));
            });
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        if ($cat = $request->input('category')) {
            $query->whereHas('category', fn($q) => $q->where('slug', $cat));
        }

        $services = $query->latest()->paginate(20)->withQueryString();

        $counts = [
            'all' => Product::withoutGlobalScopes()->where('vertical', 'services')->count(),
            'active' => Product::withoutGlobalScopes()->where('vertical', 'services')->where('status', 'active')->count(),
            'pending' => Product::withoutGlobalScopes()->where('vertical', 'services')->where('status', 'draft')->count(),
        ];

        $categories = Category::where('vertical', 'services')->get();

        return view('admin.services.index', compact('services', 'counts', 'categories'));
    }

    /**
     * Delete Service by Master Admin
     */
    public function deleteService(Request $request, Product $service)
    {
        $title = $service->title;
        $id = $service->id;

        $service->gigPackages()->delete();
        $service->images()->delete();
        $service->delete();

        return back()->with('success', "Service #{$id} '{$title}' was permanently deleted.");
    }

    /**
     * Update Service Status (active, draft, suspended)
     */
    public function updateServiceStatus(Request $request, Product $service)
    {
        $validated = $request->validate([
            'status' => 'required|in:active,draft,suspended',
        ]);

        $service->status = $validated['status'];
        $service->save();

        return back()->with('success', "Service '{$service->title}' status updated to " . strtoupper($validated['status']) . ".");
    }
}
