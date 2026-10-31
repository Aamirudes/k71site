<x-layouts.vendor title="Vendor Command Dashboard">
    @php
        $period = $period ?? request('period', 'all');
        $periodLabel = $periodLabel ?? 'Lifetime';
        $vKey = $vendor ? $vendor->getVerticalKey() : 'marketplace';
        
        $sec = $vendor ? $vendor->getSectorConfig() : [
            'name' => 'Marketplace Store',
            'catalog_name' => 'Catalog Items',
            'item_singular' => 'Product',
            'icon' => '🏷️',
            'badge' => 'Verified Vendor',
            'orders_name' => 'Orders'
        ];
        $currV = [
            'icon' => $sec['icon'] ?? '🏷️',
            'badge' => $sec['badge'] ?? 'Merchant Store',
            'action' => 'Add ' . ($sec['item_singular'] ?? 'Item'),
            'catalog' => $sec['catalog_name'] ?? 'Catalog Items',
            'orders' => $sec['orders_name'] ?? 'Orders',
        ];
        
        $shopName = $vendor->shop_name ?? $vendor->store_name ?? 'Vendor Store';
        $actionableCount = is_numeric($actionableOrders) ? (int)$actionableOrders : (is_countable($actionableOrders) ? count($actionableOrders) : 0);
        $totalRev = (float)($totalRevenue ?? 0);
        $netPay = (float)($netPayout ?? ($totalRev * 0.95));
        $avgVal = (float)($avgOrderValue ?? 0);
        $paidCount = (int)($paidOrdersCount ?? 0);
        $catalogCount = (int)($totalProducts ?? 0);
        $chartData = $chartData ?? [];
        $pendingClaims = $pendingPaymentClaims ?? collect();
    @endphp

    <style>
        /* MINIMAL PURE BLACK & WHITE HIGH-CONTRAST DESIGN */
        .bw-card {
            background: #ffffff;
            border: 1px solid #e4e4e7;
            border-radius: 16px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.02);
            transition: all 0.2s ease;
        }
        .bw-card:hover {
            border-color: #18181b;
        }
        .bw-btn-black {
            background: #000000;
            color: #ffffff;
            border: 1px solid #000000;
            border-radius: 12px;
            font-weight: 700;
            transition: all 0.15s ease;
        }
        .bw-btn-black:hover {
            background: #27272a;
            border-color: #27272a;
        }
        .bw-btn-white {
            background: #ffffff;
            color: #000000;
            border: 1px solid #d4d4d8;
            border-radius: 12px;
            font-weight: 700;
            transition: all 0.15s ease;
        }
        .bw-btn-white:hover {
            background: #f4f4f5;
            border-color: #18181b;
        }
        .bw-badge {
            background: #f4f4f5;
            color: #18181b;
            border: 1px solid #e4e4e7;
            border-radius: 9999px;
            font-weight: 700;
        }
        .bw-badge-active {
            background: #000000;
            color: #ffffff;
            border: 1px solid #000000;
            border-radius: 9999px;
            font-weight: 700;
        }
    </style>

    <div class="space-y-6 max-w-7xl mx-auto pb-12" x-data="{ 
        showDateFilter: {{ $period === 'custom' ? 'true' : 'false' }},
        previewScreenshot: null,
        rejectingClaimId: null,
        rejectionReason: ''
    }">
        
        <!-- 1. MINIMAL HEADER -->
        <div class="bw-card p-6 flex flex-col md:flex-row md:items-center justify-between gap-5">
            <div class="space-y-1">
                <div class="flex items-center gap-2">
                    <span class="bw-badge text-[10px] font-mono uppercase tracking-wider px-2.5 py-0.5">
                        {{ $currV['badge'] }}
                    </span>
                    <span class="inline-flex items-center gap-1.5 text-[11px] font-bold text-zinc-600 bg-zinc-100 px-2.5 py-0.5 rounded-full border border-zinc-200">
                        <span class="w-1.5 h-1.5 rounded-full bg-black"></span>
                        Merchant Active
                    </span>
                </div>
                <h1 class="text-2xl sm:text-3xl font-black text-black tracking-tight">
                    {{ $shopName }}
                </h1>
                <p class="text-xs text-zinc-500 font-medium">
                    Store Operations, Payment Clearances, and Product Catalog Engine
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-2.5">
                <a href="{{ route('vendor.products.create') }}" class="bw-btn-black px-4 py-2.5 text-xs font-bold flex items-center gap-2 shadow-xs cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                    <span>{{ $currV['action'] }}</span>
                </a>

                @if($vendor->storefront_url)
                <a href="{{ $vendor->storefront_url }}" target="_blank" class="bw-btn-white px-3.5 py-2.5 text-xs font-bold flex items-center gap-1.5">
                    <span>View Storefront</span>
                    <span>↗</span>
                </a>
                @endif

                <a href="/messages" class="bw-btn-white px-3.5 py-2.5 text-xs font-bold flex items-center gap-1.5">
                    <span>Messages</span>
                </a>
            </div>
        </div>

        @if(session('success'))
            <div class="p-4 bg-zinc-100 text-black border border-black rounded-xl text-xs sm:text-sm font-bold flex items-center justify-between shadow-xs">
                <span>✓ {{ session('success') }}</span>
                <span class="text-[10px] font-mono uppercase bg-black text-white px-2 py-0.5 rounded">Updated</span>
            </div>
        @endif

        @if(session('error'))
            <div class="p-4 bg-zinc-900 text-white rounded-xl text-xs sm:text-sm font-bold flex items-center justify-between shadow-xs">
                <span>✕ {{ session('error') }}</span>
            </div>
        @endif

        <!-- ====================================================================
             2. CUSTOMER PAYMENT SUBMISSIONS (UTR / SCREENSHOT) TO APPROVE OR REJECT
             ==================================================================== -->
        <div class="bw-card p-6 border-2 border-black space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 border-b border-zinc-200">
                <div class="flex items-center gap-2.5">
                    <div class="w-7 h-7 rounded-lg bg-black text-white flex items-center justify-center text-xs font-black">
                        ₹
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <h2 class="text-sm sm:text-base font-black text-black uppercase tracking-wider">
                                Payment Submissions (UTR &amp; Screenshots)
                            </h2>
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-black {{ $pendingClaims->isNotEmpty() ? 'bg-black text-white animate-pulse' : 'bg-zinc-100 text-zinc-600' }}">
                                {{ $pendingClaims->count() }} PENDING
                            </span>
                        </div>
                        <p class="text-xs text-zinc-500 mt-0.5">
                            Customer manual UPI transaction IDs and proof screenshots waiting for your review.
                        </p>
                    </div>
                </div>

                <a href="{{ route('vendor.orders.index', ['status' => 'pending']) }}" class="text-xs font-bold text-black hover:underline flex items-center gap-1">
                    <span>All Orders</span>
                    <span>&rarr;</span>
                </a>
            </div>

            @if($pendingClaims->isEmpty())
                <div class="text-center py-8 px-4 bg-zinc-50 rounded-xl border border-dashed border-zinc-300">
                    <p class="text-xs font-bold text-zinc-700">All payment submissions are up to date.</p>
                    <p class="text-[11px] text-zinc-500 mt-0.5">When customers upload payment screenshots or UTR numbers during UPI checkout, they will immediately appear here for 1-tap verification.</p>
                </div>
            @else
                <div class="space-y-3">
                    @foreach($pendingClaims as $claim)
                    @php $o = $claim->order; @endphp
                    @if($o)
                    <div class="p-4 rounded-xl border border-zinc-200 bg-white hover:border-black transition-all flex flex-col lg:flex-row lg:items-center justify-between gap-4">
                        <div class="flex items-start gap-3.5">
                            <div class="w-9 h-9 rounded-xl bg-zinc-100 border border-zinc-300 flex items-center justify-center font-black text-black shrink-0 text-sm">
                                #
                            </div>
                            <div class="space-y-1 min-w-0">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <span class="font-mono font-black text-sm text-black">Order #{{ $o->order_number }}</span>
                                    <span class="bw-badge text-[10px] font-bold px-2 py-0.5">
                                        ₹{{ number_format(($claim->amount_claimed ?: $o->total_amount) / 100, 2) }}
                                    </span>
                                    <span class="text-[11px] text-zinc-400 font-mono">{{ $claim->created_at->format('d M, g:i A') }}</span>
                                </div>
                                <div class="text-xs text-zinc-700 font-medium">
                                    Customer: <strong class="text-black">{{ $o->user?->name ?? 'Guest Buyer' }}</strong>
                                    @if($o->user?->phone || !empty($o->address_snapshot['phone']))
                                        • <span class="font-mono text-zinc-500">{{ $o->user?->phone ?? $o->address_snapshot['phone'] }}</span>
                                    @endif
                                </div>
                                <div class="flex items-center gap-2 text-xs pt-1">
                                    <span class="text-zinc-500 font-semibold">Submitted UTR / Ref:</span>
                                    <span class="font-mono font-bold bg-zinc-100 text-black px-2 py-0.5 rounded border border-zinc-300 select-all">
                                        {{ $claim->utr_number ?? 'Not provided' }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Actions & Screenshot Preview -->
                        <div class="flex items-center gap-2.5 flex-wrap self-end lg:self-center">
                            @if($claim->screenshot)
                                <button type="button" 
                                        @click="previewScreenshot = '{{ asset('storage/' . $claim->screenshot) }}'"
                                        class="bw-btn-white px-3 py-1.5 text-xs font-bold flex items-center gap-1.5 cursor-pointer">
                                    <span>🖼️ View Screenshot</span>
                                </button>
                            @endif

                            <form action="{{ route('vendor.orders.approve_payment', $o->id) }}" method="POST" onsubmit="return confirm('Approve payment for Order #{{ $o->order_number }}? Order will be marked as PAID and ready for dispatch.');">
                                @csrf
                                <button type="submit" class="bw-btn-black px-3.5 py-1.5 text-xs font-black cursor-pointer shadow-xs">
                                    ✓ Approve Payment
                                </button>
                            </form>

                            <button type="button" 
                                    @click="rejectingClaimId = {{ $o->id }}"
                                    class="bw-btn-white px-3 py-1.5 text-xs font-bold text-red-600 hover:text-red-700 hover:border-red-600 cursor-pointer">
                                ✕ Reject
                            </button>

                            <a href="{{ route('vendor.orders.show', $o->id) }}" class="text-xs font-bold text-zinc-500 hover:text-black px-2 py-1">
                                Order Details &rarr;
                            </a>
                        </div>
                    </div>
                    @endif
                    @endforeach
                </div>
            @endif
        </div>

        <!-- Screenshot Modal -->
        <div x-show="previewScreenshot" x-cloak class="fixed inset-0 z-50 bg-black/80 flex items-center justify-center p-4 backdrop-blur-xs" @click.self="previewScreenshot = null">
            <div class="bg-white rounded-2xl max-w-lg w-full p-5 border border-black space-y-3 relative">
                <div class="flex items-center justify-between pb-2 border-b border-zinc-200">
                    <h3 class="text-sm font-black text-black">Payment Proof Screenshot</h3>
                    <button type="button" @click="previewScreenshot = null" class="w-7 h-7 rounded-lg bg-zinc-100 hover:bg-black hover:text-white flex items-center justify-center text-xs font-black">✕</button>
                </div>
                <div class="max-h-[70vh] overflow-auto rounded-xl bg-zinc-100 border border-zinc-200 p-2 flex items-center justify-center">
                    <img :src="previewScreenshot" alt="Payment Proof" class="max-w-full max-h-[60vh] object-contain rounded-lg">
                </div>
                <div class="flex justify-end pt-2">
                    <button type="button" @click="previewScreenshot = null" class="bw-btn-black px-4 py-2 text-xs font-bold">Close</button>
                </div>
            </div>
        </div>

        <!-- Rejection Reason Modal -->
        <div x-show="rejectingClaimId" x-cloak class="fixed inset-0 z-50 bg-black/80 flex items-center justify-center p-4 backdrop-blur-xs" @click.self="rejectingClaimId = null">
            <div class="bg-white rounded-2xl max-w-md w-full p-5 border border-black space-y-4">
                <div class="flex items-center justify-between pb-2 border-b border-zinc-200">
                    <h3 class="text-sm font-black text-black">Reject Payment Submission</h3>
                    <button type="button" @click="rejectingClaimId = null" class="w-7 h-7 rounded-lg bg-zinc-100 hover:bg-black hover:text-white flex items-center justify-center text-xs font-black">✕</button>
                </div>
                <p class="text-xs text-zinc-600 leading-relaxed">
                    Please provide the reason for payment rejection. The customer will be informed so they can correct and resubmit the payment.
                </p>
                <form :action="'/vendor/orders/' + rejectingClaimId + '/reject-payment'" method="POST" class="space-y-3">
                    @csrf
                    <div>
                        <label class="block text-xs font-bold text-black mb-1">Rejection Reason</label>
                        <input type="text" name="rejection_reason" required x-model="rejectionReason"
                               placeholder="e.g. UTR not matching statement, Invalid screenshot, Uncredited"
                               class="w-full px-3 py-2 text-xs rounded-xl border border-zinc-300 text-black font-semibold focus:border-black focus:outline-none">
                    </div>
                    <div class="flex justify-end gap-2 pt-2">
                        <button type="button" @click="rejectingClaimId = null" class="bw-btn-white px-3.5 py-2 text-xs font-bold">Cancel</button>
                        <button type="submit" class="bw-btn-black px-4 py-2 text-xs font-black bg-red-600 hover:bg-red-700 border-red-600 text-white">Confirm Rejection</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- ====================================================================
             3. TIME HORIZON FILTER BAR
             ==================================================================== -->
        <div class="bw-card p-3 flex flex-wrap items-center justify-between gap-3">
            <div class="flex flex-wrap items-center gap-1.5">
                @php
                    $periods = [
                        'all' => 'Lifetime',
                        'today' => 'Today',
                        '7_days' => 'Last 7 Days',
                        'this_month' => 'This Month',
                    ];
                @endphp

                @foreach($periods as $key => $lbl)
                    <a href="{{ route('vendor.dashboard', ['period' => $key]) }}" 
                       class="px-3.5 py-1.5 text-xs font-bold transition-all {{ $period === $key ? 'bw-badge-active' : 'bw-badge hover:bg-zinc-200' }}">
                        {{ $lbl }}
                    </a>
                @endforeach

                <button type="button" 
                        @click="showDateFilter = !showDateFilter"
                        class="px-3.5 py-1.5 text-xs font-bold transition-all flex items-center gap-1.5 {{ $period === 'custom' ? 'bw-badge-active' : 'bw-badge hover:bg-zinc-200' }}">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    <span>Custom</span>
                </button>
            </div>

            <div class="text-[11px] font-mono text-zinc-600 px-2 flex items-center gap-1.5">
                <span>Active Period: <strong class="text-black font-bold">{{ $periodLabel }}</strong></span>
            </div>
        </div>

        <!-- Custom Date Range -->
        <div x-show="showDateFilter" x-cloak class="bw-card p-4">
            <form method="GET" action="{{ route('vendor.dashboard') }}" class="flex flex-wrap items-end gap-3">
                <input type="hidden" name="period" value="custom">
                <div>
                    <label class="block text-[10px] font-bold uppercase tracking-wider text-zinc-500 mb-1">From Date</label>
                    <input type="date" name="start_date" value="{{ $startDateInput ?? request('start_date') }}" required
                           class="px-3 py-1.5 text-xs font-mono font-bold border border-zinc-300 rounded-lg text-black bg-white focus:border-black focus:outline-none">
                </div>
                <div>
                    <label class="block text-[10px] font-bold uppercase tracking-wider text-zinc-500 mb-1">To Date</label>
                    <input type="date" name="end_date" value="{{ $endDateInput ?? request('end_date') }}" required
                           class="px-3 py-1.5 text-xs font-mono font-bold border border-zinc-300 rounded-lg text-black bg-white focus:border-black focus:outline-none">
                </div>
                <button type="submit" class="bw-btn-black px-4 py-2 text-xs font-bold">Apply Filter</button>
                <a href="{{ route('vendor.dashboard') }}" class="bw-btn-white px-3 py-2 text-xs font-semibold">Reset</a>
            </form>
        </div>

        <!-- ====================================================================
             4. MINIMAL 4 METRIC TILES
             ==================================================================== -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            
            <!-- Tile 1: Total Revenue -->
            <div class="bw-card p-5 flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-zinc-500 font-mono">
                            Total Net Revenue
                        </span>
                        <span class="bw-badge text-[9px] px-2 py-0.5">
                            Realized
                        </span>
                    </div>
                    <div class="text-2xl sm:text-3xl font-black text-black tracking-tight font-mono">
                        ₹{{ number_format($totalRev / 100, 2) }}
                    </div>
                    <p class="text-xs text-zinc-500 mt-0.5">Cleared customer payments</p>
                </div>
                <div class="mt-4 pt-3 border-t border-zinc-100 flex items-center justify-between text-xs">
                    <span class="text-zinc-500 text-[11px]">Vendor Payout (95%)</span>
                    <span class="font-mono font-black text-black">₹{{ number_format($netPay / 100, 2) }}</span>
                </div>
            </div>

            <!-- Tile 2: Completed Orders -->
            <div class="bw-card p-5 flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-zinc-500 font-mono">
                            Paid Orders
                        </span>
                        <span class="bw-badge text-[9px] px-2 py-0.5 font-mono">
                            Count
                        </span>
                    </div>
                    <div class="text-2xl sm:text-3xl font-black text-black tracking-tight font-mono">
                        {{ $paidCount }}
                    </div>
                    <p class="text-xs text-zinc-500 mt-0.5">Orders successfully settled</p>
                </div>
                <div class="mt-4 pt-3 border-t border-zinc-100 flex items-center justify-between text-xs">
                    <span class="text-zinc-500 text-[11px]">Avg. Order Value</span>
                    <span class="font-mono font-bold text-black">₹{{ number_format($avgVal / 100, 2) }}</span>
                </div>
            </div>

            <!-- Tile 3: Fulfillment Queue -->
            <div class="bw-card p-5 flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-zinc-500 font-mono">
                            Fulfillment Queue
                        </span>
                        <span class="bw-badge text-[9px] px-2 py-0.5 font-mono">
                            Pending
                        </span>
                    </div>
                    <div class="text-2xl sm:text-3xl font-black text-black tracking-tight font-mono">
                        {{ $actionableCount }}
                    </div>
                    <p class="text-xs text-zinc-500 mt-0.5">Orders awaiting packing/dispatch</p>
                </div>
                <div class="mt-4 pt-3 border-t border-zinc-100 flex items-center justify-between text-xs">
                    <span class="text-zinc-500 text-[11px]">Action</span>
                    <a href="{{ route('vendor.orders.index') }}" class="font-bold text-black hover:underline">
                        Review Orders &rarr;
                    </a>
                </div>
            </div>

            <!-- Tile 4: Active Listings -->
            <div class="bw-card p-5 flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-zinc-500 font-mono">
                            {{ $currV['catalog'] }}
                        </span>
                        <span class="bw-badge text-[9px] px-2 py-0.5 font-mono">
                            Live
                        </span>
                    </div>
                    <div class="text-2xl sm:text-3xl font-black text-black tracking-tight font-mono">
                        {{ $catalogCount }}
                    </div>
                    <p class="text-xs text-zinc-500 mt-0.5">Products on your store</p>
                </div>
                <div class="mt-4 pt-3 border-t border-zinc-100 flex items-center justify-between text-xs">
                    <span class="text-zinc-500 text-[11px]">Catalog</span>
                    <a href="{{ route('vendor.products.index') }}" class="font-bold text-black hover:underline">
                        Manage Listings &rarr;
                    </a>
                </div>
            </div>

        </div>

        <!-- ====================================================================
             5. MONOCHROMATIC REVENUE TRAJECTORY CHART
             ==================================================================== -->
        <div class="bw-card p-6">
            <div class="flex flex-wrap items-center justify-between gap-3 mb-5 pb-3 border-b border-zinc-100">
                <div>
                    <h2 class="text-sm sm:text-base font-black text-black uppercase tracking-wider">
                        Revenue Velocity &amp; Cashflow
                    </h2>
                    <p class="text-xs text-zinc-500 mt-0.5">
                        {{ $periodLabel }} realized gross revenue and 95% net payout.
                    </p>
                </div>

                <div class="flex items-center gap-4 text-xs font-mono">
                    <div class="flex items-center gap-1.5 font-bold text-black">
                        <span class="w-2.5 h-2.5 rounded-full bg-black"></span>
                        <span>Gross (₹)</span>
                    </div>
                    <div class="flex items-center gap-1.5 font-semibold text-zinc-500">
                        <span class="w-2.5 h-2.5 rounded-full bg-zinc-400"></span>
                        <span>Net 95% (₹)</span>
                    </div>
                </div>
            </div>

            <div class="relative w-full h-60 sm:h-64">
                <canvas id="revenueChart"></canvas>
            </div>
        </div>

        <!-- ====================================================================
             6. RECENT ORDERS TABLE (CLEAN MINIMAL BLACK & WHITE)
             ==================================================================== -->
        <div class="bw-card p-6">
            <div class="flex items-center justify-between mb-5 pb-3 border-b border-zinc-100">
                <div>
                    <h3 class="text-sm sm:text-base font-black text-black uppercase tracking-wider">
                        Recent Transactions &amp; Orders
                    </h3>
                    <p class="text-xs text-zinc-500 mt-0.5">
                        Orders placed across your store catalog.
                    </p>
                </div>
                <a href="{{ route('vendor.orders.index') }}" class="bw-btn-white px-3 py-1.5 text-xs font-bold">
                    View All Orders &rarr;
                </a>
            </div>

            @if($recentOrders->isEmpty())
                <div class="text-center py-10 px-4 bg-zinc-50 rounded-xl border border-dashed border-zinc-300">
                    <p class="text-xs font-bold text-zinc-700">No recent orders yet</p>
                    <p class="text-[11px] text-zinc-500 mt-0.5">When shoppers buy your items, orders will be listed here.</p>
                </div>
            @else
                <!-- Desktop Table -->
                <div class="overflow-x-auto no-scrollbar">
                    <table class="w-full text-left text-xs">
                        <thead>
                            <tr class="text-[10px] font-mono font-bold uppercase tracking-wider text-zinc-400 border-b border-zinc-200">
                                <th class="pb-3 px-3">Order Ref</th>
                                <th class="pb-3 px-3">Customer</th>
                                <th class="pb-3 px-3">Date</th>
                                <th class="pb-3 px-3">Items</th>
                                <th class="pb-3 px-3">Amount</th>
                                <th class="pb-3 px-3">Payment</th>
                                <th class="pb-3 px-3">Status</th>
                                <th class="pb-3 px-3 text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-100">
                            @foreach($recentOrders as $o)
                            <tr class="hover:bg-zinc-50 transition-colors">
                                <td class="py-3 px-3 font-mono font-bold text-black">
                                    #{{ $o->order_number ?? $o->id }}
                                </td>
                                <td class="py-3 px-3">
                                    <div class="font-bold text-black">{{ $o->user?->name ?? 'Guest Buyer' }}</div>
                                    <div class="text-[10px] text-zinc-400 font-mono">{{ $o->user?->email ?? $o->phone }}</div>
                                </td>
                                <td class="py-3 px-3 text-zinc-500 whitespace-nowrap font-mono text-[11px]">
                                    {{ $o->created_at->format('d M, g:i A') }}
                                </td>
                                <td class="py-3 px-3 font-medium text-zinc-700">
                                    {{ $o->items_count ?? ($o->items ? $o->items->count() : 1) }} items
                                </td>
                                <td class="py-3 px-3 font-mono font-bold text-black whitespace-nowrap">
                                    ₹{{ number_format(($o->subtotal - $o->discount_amount) / 100, 2) }}
                                </td>
                                <td class="py-3 px-3 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-mono font-bold border {{ $o->payment_status === 'paid' ? 'bg-black text-white border-black' : 'bg-zinc-100 text-zinc-800 border-zinc-300' }}">
                                        {{ ucfirst($o->payment_status) }}
                                    </span>
                                </td>
                                <td class="py-3 px-3 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-mono font-bold border border-zinc-300 bg-white text-zinc-800">
                                        {{ ucfirst($o->status) }}
                                    </span>
                                </td>
                                <td class="py-3 px-3 text-right whitespace-nowrap">
                                    <a href="{{ route('vendor.orders.show', $o->id) }}" class="bw-btn-white px-2.5 py-1 text-[11px] font-bold">
                                        View &rarr;
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

    </div>

    <!-- Monochromatic Chart.js Script -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const chartCanvas = document.getElementById('revenueChart');
            if (!chartCanvas) return;

            const chartData = @json($chartData);
            const labels = chartData.map(item => item.label);
            const revenue = chartData.map(item => item.revenue);
            const net = chartData.map(item => item.net);

            const ctx = chartCanvas.getContext('2d');

            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: labels.length ? labels : ['No Data'],
                    datasets: [
                        {
                            label: 'Gross Paid (₹)',
                            data: revenue.length ? revenue : [0],
                            borderColor: '#000000',
                            borderWidth: 2,
                            tension: 0.2,
                            fill: false,
                            pointBackgroundColor: '#000000',
                            pointRadius: 3,
                        },
                        {
                            label: 'Net Payout (₹)',
                            data: net.length ? net : [0],
                            borderColor: '#71717a',
                            borderWidth: 1.5,
                            borderDash: [4, 4],
                            tension: 0.2,
                            fill: false,
                            pointBackgroundColor: '#71717a',
                            pointRadius: 2,
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: { intersect: false, mode: 'index' },
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            backgroundColor: '#000000',
                            titleColor: '#ffffff',
                            bodyColor: '#e4e4e7',
                            padding: 8,
                            cornerRadius: 8,
                            callbacks: {
                                label: function(c) {
                                    return ' ' + c.dataset.label + ': ₹' + Number(c.parsed.y).toLocaleString('en-IN', { minimumFractionDigits: 2 });
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            grid: { display: false },
                            ticks: { color: '#71717a', font: { size: 10 } }
                        },
                        y: {
                            beginAtZero: true,
                            grid: { color: '#f4f4f5' },
                            ticks: {
                                color: '#71717a',
                                font: { size: 10 },
                                callback: function(val) {
                                    return '₹' + val.toLocaleString('en-IN');
                                }
                            }
                        }
                    }
                }
            });
        });
    </script>
</x-layouts.vendor>