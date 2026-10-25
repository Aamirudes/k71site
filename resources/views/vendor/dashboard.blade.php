<x-layouts.vendor title="Dashboard Overview">
    @php
        $period = $period ?? request('period', 'all');
        $periodLabel = $periodLabel ?? 'Lifetime';
        $vKey = $vendor ? $vendor->getVerticalKey() : 'marketplace';
        
        $sec = $vendor ? $vendor->getSectorConfig() : [
            'name' => 'Marketplace Store',
            'catalog_name' => 'Catalog Items',
            'item_singular' => 'Product',
            'icon' => '🛍️',
            'badge' => 'Curated Retail',
            'orders_name' => 'Orders'
        ];
        $currV = [
            'icon' => $sec['icon'] ?? '🛍️',
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
    @endphp

    <style>
        /* ==========================================================================
           VISIONOS / APPLE LIQUID GLASS DEPTH STYLING
           ========================================================================== */
        .glass-shell {
            background: rgba(255, 255, 255, 0.75);
            backdrop-filter: blur(28px) saturate(190%);
            -webkit-backdrop-filter: blur(28px) saturate(190%);
            border: 1px solid rgba(255, 255, 255, 0.65);
            box-shadow: 0 20px 50px -15px rgba(15, 23, 42, 0.06), 
                        inset 0 1px 1px 0 rgba(255, 255, 255, 0.95),
                        inset 0 -1px 2px 0 rgba(0, 0, 0, 0.02);
            border-radius: 24px;
            transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
        }

        .dark .glass-shell {
            background: linear-gradient(135deg, rgba(20, 26, 42, 0.72) 0%, rgba(12, 16, 28, 0.78) 100%);
            backdrop-filter: blur(32px) saturate(180%);
            -webkit-backdrop-filter: blur(32px) saturate(180%);
            border: 1px solid rgba(255, 255, 255, 0.09);
            box-shadow: 0 24px 60px -15px rgba(0, 0, 0, 0.65), 
                        inset 0 1px 1.5px 0 rgba(255, 255, 255, 0.14),
                        inset 0 -1px 2px 0 rgba(0, 0, 0, 0.5);
        }

        .glass-shell-hover:hover {
            transform: translateY(-2px);
            border-color: rgba(99, 102, 241, 0.35);
            box-shadow: 0 28px 65px -15px rgba(99, 102, 241, 0.15), 
                        inset 0 1px 2px 0 rgba(255, 255, 255, 0.22);
        }

        .glass-pill {
            background: rgba(255, 255, 255, 0.6);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.7);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.03), inset 0 1px 1px rgba(255, 255, 255, 0.9);
            border-radius: 9999px;
        }

        .dark .glass-pill {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            box-shadow: 0 4px 14px rgba(0, 0, 0, 0.3), inset 0 1px 1px rgba(255, 255, 255, 0.12);
        }

        .glass-btn-primary {
            background: #0f172a;
            border: 1px solid rgba(255, 255, 255, 0.2);
            box-shadow: 0 10px 25px -5px rgba(15, 23, 42, 0.3), inset 0 1px 1px rgba(255, 255, 255, 0.3);
            color: #ffffff;
            transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
        }
        .glass-btn-primary:hover {
            background: #000000;
            transform: translateY(-1px);
            box-shadow: 0 14px 30px -5px rgba(0, 0, 0, 0.4);
        }
    </style>

    <div class="space-y-6 sm:space-y-8 max-w-7xl mx-auto" x-data="{ showDateFilter: {{ $period === 'custom' ? 'true' : 'false' }} }">
        
        <!-- ====================================================================
             1. REFINED LIQUID GLASS STORE HEADER
             ==================================================================== -->
        <div class="glass-shell p-5 sm:p-7 relative overflow-hidden">
            <!-- Subtle Optical Background Glows -->
            <div class="absolute -top-24 -right-24 w-80 h-80 bg-indigo-500/10 dark:bg-indigo-600/15 rounded-full blur-3xl pointer-events-none"></div>
            <div class="absolute -bottom-24 -left-24 w-80 h-80 bg-blue-500/10 dark:bg-purple-600/10 rounded-full blur-3xl pointer-events-none"></div>

            <div class="relative z-10 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-5">
                <!-- Store Identity & Badges -->
                <div class="min-w-0">
                    <div class="flex flex-wrap items-center gap-2 mb-2">
                        <span class="glass-pill px-3 py-1 text-xs font-bold text-slate-700 dark:text-slate-300 flex items-center gap-1.5">
                            <span>{{ $currV['icon'] }}</span>
                            <span>{{ $currV['badge'] }}</span>
                        </span>
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                            Online & Live
                        </span>
                    </div>

                    <h1 class="text-2xl sm:text-3xl lg:text-4xl font-extrabold text-slate-900 dark:text-white tracking-tight truncate">
                        {{ $shopName }}
                    </h1>
                    <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-1 max-w-xl">
                        Real-time revenue, order fulfillment stream, and catalog performance overview.
                    </p>
                </div>

                <!-- Fast Actions -->
                <div class="flex flex-wrap items-center gap-2.5 sm:gap-3">
                    <a href="{{ route('vendor.products.create') }}" 
                       class="glass-btn-primary px-4 sm:px-5 py-2.5 rounded-xl text-xs sm:text-sm font-bold flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
                        </svg>
                        <span>{{ $currV['action'] }}</span>
                    </a>

                    @if($vendor->storefront_url)
                    <a href="{{ $vendor->storefront_url }}" target="_blank" 
                       class="glass-shell glass-shell-hover px-3.5 sm:px-4 py-2.5 rounded-xl text-xs sm:text-sm font-semibold text-slate-700 dark:text-slate-200 flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                        </svg>
                        <span>Storefront</span>
                    </a>
                    @endif

                    <a href="/messages" 
                       class="glass-shell glass-shell-hover px-3.5 sm:px-4 py-2.5 rounded-xl text-xs sm:text-sm font-semibold text-slate-700 dark:text-slate-200 flex items-center gap-1.5">
                        <span>💬</span>
                        <span class="hidden sm:inline">Direct Messages</span>
                        <span class="sm:hidden">Chats</span>
                    </a>
                </div>
            </div>
        </div>

        <!-- ====================================================================
             2. MINIMAL TIME HORIZON SEGMENTED TABS
             ==================================================================== -->
        <div class="glass-shell p-2 sm:p-2.5 flex flex-wrap items-center justify-between gap-2.5">
            <div class="flex flex-wrap items-center gap-1">
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
                       class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition-all duration-200 {{ $period === $key ? 'bg-indigo-600 text-white shadow-md shadow-indigo-600/30' : 'text-slate-600 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-white/[0.06]' }}">
                        {{ $lbl }}
                    </a>
                @endforeach

                <button type="button" 
                        @click="showDateFilter = !showDateFilter"
                        class="px-3 py-1.5 rounded-xl text-xs font-bold transition-all flex items-center gap-1.5 {{ $period === 'custom' ? 'bg-indigo-600 text-white shadow-md' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-white/[0.06]' }}">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    <span>Custom</span>
                </button>
            </div>

            <div class="text-[11px] sm:text-xs font-medium text-slate-500 dark:text-slate-400 px-2 flex items-center gap-1.5">
                <span class="w-2 h-2 rounded-full bg-indigo-500"></span>
                <span>Active Scope: <strong class="text-slate-900 dark:text-white font-bold">{{ $periodLabel }}</strong></span>
            </div>
        </div>

        <!-- Custom Date Range Form (Expandable) -->
        <div x-show="showDateFilter" x-cloak class="glass-shell p-4 sm:p-5" x-transition>
            <form method="GET" action="{{ route('vendor.dashboard') }}" class="flex flex-wrap items-end gap-3 sm:gap-4">
                <input type="hidden" name="period" value="custom">
                <div>
                    <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1">From</label>
                    <input type="date" name="start_date" value="{{ $startDateInput ?? request('start_date') }}" required
                           class="px-3 py-1.5 text-xs font-medium border border-slate-200 dark:border-white/10 rounded-xl bg-white dark:bg-slate-900 text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1">To</label>
                    <input type="date" name="end_date" value="{{ $endDateInput ?? request('end_date') }}" required
                           class="px-3 py-1.5 text-xs font-medium border border-slate-200 dark:border-white/10 rounded-xl bg-white dark:bg-slate-900 text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
                <div class="flex items-center gap-2">
                    <button type="submit" class="glass-btn-primary px-4 py-2 text-xs font-bold rounded-xl shadow-md">
                        Filter
                    </button>
                    <a href="{{ route('vendor.dashboard') }}" class="px-3 py-2 text-xs font-semibold text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white">
                        Reset
                    </a>
                </div>
            </form>
        </div>

        <!-- ====================================================================
             3. THE 4 ESSENTIAL LIQUID GLASS METRIC TILES
             ==================================================================== -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-5">
            
            <!-- Tile 1: Total Revenue -->
            <div class="glass-shell glass-shell-hover p-5 sm:p-6 flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                            Total Revenue
                        </span>
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20">
                            Realized
                        </span>
                    </div>
                    <div class="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight">
                        ₹{{ number_format($totalRev / 100, 2) }}
                    </div>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Paid customer orders</p>
                </div>

                <div class="mt-4 pt-3 border-t border-slate-100 dark:border-white/[0.06] flex items-center justify-between text-xs">
                    <span class="text-slate-400 dark:text-slate-500 text-[11px]">Net Payout (95%)</span>
                    <span class="font-extrabold text-emerald-600 dark:text-emerald-400">₹{{ number_format($netPay / 100, 2) }}</span>
                </div>
            </div>

            <!-- Tile 2: Order Volume -->
            <div class="glass-shell glass-shell-hover p-5 sm:p-6 flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                            Completed Orders
                        </span>
                        <div class="w-7 h-7 rounded-lg bg-blue-500/10 text-blue-500 flex items-center justify-center text-xs font-bold">
                            📦
                        </div>
                    </div>
                    <div class="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight">
                        {{ $paidCount }}
                    </div>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Successfully fulfilled</p>
                </div>

                <div class="mt-4 pt-3 border-t border-slate-100 dark:border-white/[0.06] flex items-center justify-between text-xs">
                    <span class="text-slate-400 dark:text-slate-500 text-[11px]">Avg. Order Value</span>
                    <span class="font-bold text-slate-900 dark:text-white">₹{{ number_format($avgVal / 100, 2) }}</span>
                </div>
            </div>

            <!-- Tile 3: Actionable Orders (Fulfillment Queue) -->
            <div class="glass-shell glass-shell-hover p-5 sm:p-6 flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                            Fulfillment Queue
                        </span>
                        <div class="w-7 h-7 rounded-lg bg-amber-500/10 text-amber-500 flex items-center justify-center text-xs font-bold">
                            ⚡
                        </div>
                    </div>
                    <div class="text-2xl sm:text-3xl font-extrabold {{ $actionableCount > 0 ? 'text-amber-500' : 'text-slate-900 dark:text-white' }} tracking-tight">
                        {{ $actionableCount }}
                    </div>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Pending dispatch or preparation</p>
                </div>

                <div class="mt-4 pt-3 border-t border-slate-100 dark:border-white/[0.06] flex items-center justify-between text-xs">
                    <span class="text-slate-400 dark:text-slate-500 text-[11px]">Action status</span>
                    <a href="{{ route('vendor.orders.index') }}" class="font-bold text-indigo-600 dark:text-indigo-400 hover:underline">
                        Review Queue &rarr;
                    </a>
                </div>
            </div>

            <!-- Tile 4: Active Listings -->
            <div class="glass-shell glass-shell-hover p-5 sm:p-6 flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                            {{ $currV['catalog'] }}
                        </span>
                        <div class="w-7 h-7 rounded-lg bg-purple-500/10 text-purple-500 flex items-center justify-center text-xs font-bold">
                            {{ $currV['icon'] }}
                        </div>
                    </div>
                    <div class="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight">
                        {{ $catalogCount }}
                    </div>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Live active listings on store</p>
                </div>

                <div class="mt-4 pt-3 border-t border-slate-100 dark:border-white/[0.06] flex items-center justify-between text-xs">
                    <span class="text-slate-400 dark:text-slate-500 text-[11px]">Store catalog</span>
                    <a href="{{ route('vendor.products.index') }}" class="font-bold text-indigo-600 dark:text-indigo-400 hover:underline">
                        Manage &rarr;
                    </a>
                </div>
            </div>
        </div>

        <!-- ====================================================================
             4. SLEEK REVENUE VELOCITY LINE CHART
             ==================================================================== -->
        <div class="glass-shell p-5 sm:p-7">
            <div class="flex flex-wrap items-center justify-between gap-3 mb-5">
                <div>
                    <h2 class="text-base sm:text-lg font-bold text-slate-900 dark:text-white tracking-tight">
                        Revenue Trajectory
                    </h2>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                        {{ $periodLabel }} cash inflows across settled transactions.
                    </p>
                </div>

                <div class="flex items-center gap-4 text-xs font-semibold">
                    <div class="flex items-center gap-1.5 text-indigo-600 dark:text-indigo-400">
                        <span class="w-2.5 h-2.5 rounded-full bg-indigo-500"></span>
                        <span>Gross (₹)</span>
                    </div>
                    <div class="flex items-center gap-1.5 text-emerald-600 dark:text-emerald-400">
                        <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                        <span>Net Take-Home (₹)</span>
                    </div>
                </div>
            </div>

            <!-- Canvas Container -->
            <div class="relative w-full h-64 sm:h-72">
                <canvas id="revenueChart"></canvas>
            </div>
        </div>

        <!-- ====================================================================
             5. RECENT TRANSACTIONS (FULLY RESPONSIVE: CARDS ON MOBILE, TABLE ON DESKTOP)
             ==================================================================== -->
        <div class="glass-shell p-5 sm:p-7">
            <div class="flex items-center justify-between mb-5 pb-3 border-b border-slate-100 dark:border-white/[0.06]">
                <div>
                    <h3 class="text-base sm:text-lg font-bold text-slate-900 dark:text-white tracking-tight">
                        Recent Transactions
                    </h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                        Latest incoming orders and booking requests
                    </p>
                </div>
                <a href="{{ route('vendor.orders.index') }}" class="px-3 py-1.5 rounded-xl text-xs font-bold text-indigo-600 dark:text-indigo-400 hover:bg-indigo-50 dark:hover:bg-indigo-500/10 transition-colors">
                    View All Orders &rarr;
                </a>
            </div>

            @if($recentOrders->isEmpty())
                <div class="text-center py-12 px-4">
                    <div class="w-14 h-14 rounded-2xl glass-shell flex items-center justify-center text-2xl mx-auto mb-3">
                        📦
                    </div>
                    <h4 class="text-sm font-bold text-slate-900 dark:text-white">No Recent Orders</h4>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 max-w-sm mx-auto">
                        New customer purchases and booking inquiries will appear here in real-time.
                    </p>
                    <a href="{{ route('vendor.products.create') }}" class="mt-4 inline-flex items-center gap-1.5 glass-btn-primary px-4 py-2 rounded-xl text-xs font-bold">
                        <span>{{ $currV['action'] }}</span>
                    </a>
                </div>
            @else
                <!-- A. MOBILE CARD VIEW (< md breakpoint, zero horizontal scrollbar) -->
                <div class="md:hidden space-y-3">
                    @foreach($recentOrders as $o)
                    <div class="p-4 rounded-2xl bg-white/50 dark:bg-white/[0.03] border border-slate-200/80 dark:border-white/10 space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="font-mono font-extrabold text-xs text-slate-900 dark:text-white">
                                #{{ $o->order_number ?? $o->id }}
                            </span>
                            @php
                                $sColors = [
                                    'completed' => 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border-emerald-500/20',
                                    'processing' => 'bg-blue-500/10 text-blue-600 dark:text-blue-400 border-blue-500/20',
                                    'pending' => 'bg-amber-500/10 text-amber-600 dark:text-amber-400 border-amber-500/20',
                                    'cancelled' => 'bg-rose-500/10 text-rose-600 dark:text-rose-400 border-rose-500/20',
                                ];
                            @endphp
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold border {{ $sColors[$o->status] ?? 'bg-slate-100 text-slate-600' }}">
                                {{ ucfirst($o->status) }}
                            </span>
                        </div>

                        <div class="flex items-center justify-between text-xs">
                            <div>
                                <div class="font-bold text-slate-900 dark:text-white">{{ $o->user?->name ?? 'Customer' }}</div>
                                <div class="text-[10px] text-slate-400">{{ $o->created_at->format('d M, g:i A') }}</div>
                            </div>
                            <div class="text-right">
                                <div class="font-extrabold text-sm text-slate-900 dark:text-white">
                                    ₹{{ number_format(($o->subtotal - $o->discount_amount) / 100, 2) }}
                                </div>
                                <div class="text-[10px] font-semibold {{ $o->payment_status === 'paid' ? 'text-emerald-500' : 'text-amber-500' }}">
                                    {{ ucfirst($o->payment_status) }}
                                </div>
                            </div>
                        </div>

                        <div class="pt-2 border-t border-slate-100 dark:border-white/[0.05] flex justify-end">
                            <a href="{{ route('vendor.orders.show', $o->id) }}" class="px-3 py-1.5 rounded-lg bg-indigo-50 dark:bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 text-xs font-bold">
                                View Details &rarr;
                            </a>
                        </div>
                    </div>
                    @endforeach
                </div>

                <!-- B. DESKTOP TABLE VIEW (>= md breakpoint) -->
                <div class="hidden md:block overflow-x-auto no-scrollbar">
                    <table class="w-full text-left text-xs">
                        <thead>
                            <tr class="text-[10px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500 border-b border-slate-100 dark:border-white/[0.06]">
                                <th class="pb-3 px-3">Order Ref</th>
                                <th class="pb-3 px-3">Customer</th>
                                <th class="pb-3 px-3">Date</th>
                                <th class="pb-3 px-3">Items</th>
                                <th class="pb-3 px-3">Total</th>
                                <th class="pb-3 px-3">Payment</th>
                                <th class="pb-3 px-3">Status</th>
                                <th class="pb-3 px-3 text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-white/[0.04]">
                            @foreach($recentOrders as $o)
                            <tr class="hover:bg-slate-50/50 dark:hover:bg-white/[0.02] transition-colors">
                                <td class="py-3 px-3 font-mono font-bold text-slate-900 dark:text-white">
                                    #{{ $o->order_number ?? $o->id }}
                                </td>
                                <td class="py-3 px-3">
                                    <div class="font-bold text-slate-900 dark:text-white">{{ $o->user?->name ?? 'Guest Buyer' }}</div>
                                    <div class="text-[10px] text-slate-400">{{ $o->user?->email ?? $o->phone }}</div>
                                </td>
                                <td class="py-3 px-3 text-slate-500 dark:text-slate-400 whitespace-nowrap">
                                    {{ $o->created_at->format('d M, g:i A') }}
                                </td>
                                <td class="py-3 px-3 font-medium text-slate-600 dark:text-slate-300">
                                    {{ $o->items_count ?? ($o->items ? $o->items->count() : 1) }} items
                                </td>
                                <td class="py-3 px-3 font-extrabold text-slate-900 dark:text-white whitespace-nowrap">
                                    ₹{{ number_format(($o->subtotal - $o->discount_amount) / 100, 2) }}
                                </td>
                                <td class="py-3 px-3 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold {{ $o->payment_status === 'paid' ? 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20' : 'bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/20' }}">
                                        {{ ucfirst($o->payment_status) }}
                                    </span>
                                </td>
                                <td class="py-3 px-3 whitespace-nowrap">
                                    @php
                                        $sColors = [
                                            'completed' => 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border-emerald-500/20',
                                            'processing' => 'bg-blue-500/10 text-blue-600 dark:text-blue-400 border-blue-500/20',
                                            'pending' => 'bg-amber-500/10 text-amber-600 dark:text-amber-400 border-amber-500/20',
                                            'cancelled' => 'bg-rose-500/10 text-rose-600 dark:text-rose-400 border-rose-500/20',
                                        ];
                                    @endphp
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold border {{ $sColors[$o->status] ?? 'bg-slate-100 text-slate-600' }}">
                                        {{ ucfirst($o->status) }}
                                    </span>
                                </td>
                                <td class="py-3 px-3 text-right whitespace-nowrap">
                                    <a href="{{ route('vendor.orders.show', $o->id) }}" class="px-2.5 py-1 rounded-lg bg-slate-100 dark:bg-white/10 hover:bg-indigo-600 hover:text-white dark:hover:bg-indigo-600 text-slate-700 dark:text-slate-300 font-bold text-[11px] transition-colors">
                                        Inspect &rarr;
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

    <!-- Chart.js Injection with Dark/Light Theme Reactivity -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const chartCanvas = document.getElementById('revenueChart');
            if (!chartCanvas) return;

            const isDark = document.documentElement.classList.contains('dark');
            const chartData = @json($chartData);

            const labels = chartData.map(item => item.label);
            const revenue = chartData.map(item => item.revenue);
            const net = chartData.map(item => item.net);

            const ctx = chartCanvas.getContext('2d');

            const gradGross = ctx.createLinearGradient(0, 0, 0, 260);
            gradGross.addColorStop(0, isDark ? 'rgba(99, 102, 241, 0.45)' : 'rgba(79, 70, 229, 0.25)');
            gradGross.addColorStop(1, 'rgba(99, 102, 241, 0)');

            const gradNet = ctx.createLinearGradient(0, 0, 0, 260);
            gradNet.addColorStop(0, isDark ? 'rgba(16, 185, 129, 0.4)' : 'rgba(16, 185, 129, 0.2)');
            gradNet.addColorStop(1, 'rgba(16, 185, 129, 0)');

            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: labels.length ? labels : ['No Data'],
                    datasets: [
                        {
                            label: 'Gross Paid (₹)',
                            data: revenue.length ? revenue : [0],
                            borderColor: '#6366f1',
                            borderWidth: 2.5,
                            backgroundColor: gradGross,
                            tension: 0.38,
                            fill: true,
                            pointBackgroundColor: '#6366f1',
                            pointRadius: 3,
                            pointHoverRadius: 6,
                        },
                        {
                            label: 'Net Payout (₹)',
                            data: net.length ? net : [0],
                            borderColor: '#10b981',
                            borderWidth: 2,
                            borderDash: [4, 4],
                            backgroundColor: gradNet,
                            tension: 0.38,
                            fill: true,
                            pointBackgroundColor: '#10b981',
                            pointRadius: 2.5,
                            pointHoverRadius: 5,
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
                            backgroundColor: isDark ? 'rgba(15, 23, 42, 0.95)' : 'rgba(255, 255, 255, 0.95)',
                            titleColor: isDark ? '#ffffff' : '#0f172a',
                            bodyColor: isDark ? '#94a3b8' : '#475569',
                            borderColor: isDark ? 'rgba(255, 255, 255, 0.12)' : 'rgba(0, 0, 0, 0.1)',
                            borderWidth: 1,
                            padding: 10,
                            boxPadding: 4,
                            usePointStyle: true,
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
                            ticks: {
                                color: isDark ? '#64748b' : '#94a3b8',
                                font: { size: 11, weight: '500' }
                            }
                        },
                        y: {
                            beginAtZero: true,
                            grid: {
                                color: isDark ? 'rgba(255, 255, 255, 0.04)' : 'rgba(0, 0, 0, 0.04)'
                            },
                            ticks: {
                                color: isDark ? '#64748b' : '#94a3b8',
                                font: { size: 10, weight: '500' },
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
