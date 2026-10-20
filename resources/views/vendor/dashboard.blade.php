<x-layouts.vendor title="Dashboard Overview">
    @php
        $period = $period ?? request('period', 'all');
        $periodLabel = $periodLabel ?? 'Lifetime Overview';
        $vType = $vendor->vendor_type ?? 'clothing';
        $vBadges = [
            'clothing' => ['icon' => '👗', 'name' => 'Fashion & Apparel Atelier', 'catalogLabel' => 'Catalog Items', 'action' => 'New Product'],
            'food' => ['icon' => '🍽️', 'name' => 'Cloud Kitchen & Menu', 'catalogLabel' => 'Menu Dishes', 'action' => 'Add Dish'],
            'services' => ['icon' => '🛠️', 'name' => 'On-Demand Service Studio', 'catalogLabel' => 'Service Tiers', 'action' => 'New Service'],
            'used' => ['icon' => '📱', 'name' => 'Certified Pre-Owned Hub', 'catalogLabel' => 'Pre-Loved Items', 'action' => 'List Item'],
            'b2b' => ['icon' => '🏭', 'name' => 'Industrial Manufacturing & RFQ', 'catalogLabel' => 'Bulk Products', 'action' => 'Post RFQ'],
        ];
        $currV = $vBadges[$vType] ?? $vBadges['clothing'];
        $shopName = $vendor->shop_name ?? $vendor->store_name ?? 'Vendor Store';
        $totalRevenue = $totalRevenue ?? ($metrics['total_revenue'] ?? 0);
        $netPayout = $netPayout ?? ($metrics['net_payout'] ?? ($totalRevenue * 0.95));
        $platformFee = $platformFee ?? ($metrics['platform_fee'] ?? ($totalRevenue * 0.05));
        $paidOrdersCount = $paidOrdersCount ?? ($metrics['paid_orders_count'] ?? 0);
        $avgOrderValue = $avgOrderValue ?? ($metrics['avg_order_value'] ?? 0);
        $pendingRevenue = $pendingRevenue ?? ($metrics['pending_revenue'] ?? 0);
        $pendingOrdersInPeriod = $pendingOrdersInPeriod ?? ($metrics['pending_orders'] ?? 0);
        $cancelledRevenue = $cancelledRevenue ?? 0;
        $cancelledOrdersCount = $cancelledOrdersCount ?? 0;
        $totalProducts = $totalProducts ?? ($metrics['total_products'] ?? 0);
        $totalOrdersInPeriod = $totalOrdersInPeriod ?? $paidOrdersCount;
        $actionableOrders = $actionableOrders ?? collect();
        $recentOrders = $recentOrders ?? collect();
        $chartData = $chartData ?? [];
    @endphp

    <div class="space-y-8" x-data="{ showCustomRange: {{ ($period ?? 'all') === 'custom' ? 'true' : 'false' }} }">
        
        <!-- 1. Hero Welcome & Operations Bar -->
        <div class="relative overflow-hidden rounded-3xl p-6 sm:p-8 bg-gradient-to-br from-slate-900 via-[#0d101d] to-black border border-slate-800 dark:border-white/10 shadow-2xl">
            <!-- Background Ambient Glow Accents -->
            <div class="absolute -top-24 -right-24 w-96 h-96 bg-purple-600/20 rounded-full blur-3xl pointer-events-none"></div>
            <div class="absolute -bottom-24 -left-24 w-96 h-96 bg-blue-600/15 rounded-full blur-3xl pointer-events-none"></div>

            <div class="relative z-10 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
                <div>
                    <div class="flex items-center gap-2 mb-2">
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-white/10 backdrop-blur-md text-purple-300 border border-white/15">
                            <span>{{ $currV['icon'] }}</span>
                            <span>{{ $currV['name'] }}</span>
                        </span>
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold bg-emerald-500/20 text-emerald-300 border border-emerald-500/30">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                            Store Online
                        </span>
                    </div>

                    <h1 class="text-2xl sm:text-4xl font-display font-extrabold text-white tracking-tight">
                        {{ $shopName }}
                    </h1>
                    <p class="text-xs sm:text-sm text-slate-300 mt-1 max-w-xl leading-relaxed">
                        Live analytics & revenue terminal. Realized transactions are audited in real time (unpaid & cancelled orders excluded from gross revenue).
                    </p>
                </div>

                <!-- Action Controls -->
                <div class="flex flex-wrap items-center gap-3">
                    <a href="{{ route('vendor.products.create') }}" 
                       class="px-5 py-2.5 rounded-xl bg-gradient-to-r from-purple-600 via-fuchsia-600 to-indigo-600 text-white font-bold text-xs sm:text-sm shadow-lg shadow-purple-600/30 hover:shadow-purple-600/50 hover:scale-[1.02] active:scale-95 transition-all flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        <span>{{ $currV['action'] }}</span>
                    </a>

                    @if($vendor->storefront_url)
                    <a href="{{ $vendor->storefront_url }}" target="_blank" 
                       class="px-4 py-2.5 rounded-xl bg-white/10 hover:bg-white/15 backdrop-blur-md text-white font-semibold text-xs sm:text-sm border border-white/20 transition-all flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                        <span>Visit Store</span>
                    </a>
                    @endif

                    <a href="{{ route('vendor.shorts.index') }}" 
                       class="px-4 py-2.5 rounded-xl bg-rose-500/20 hover:bg-rose-500/30 text-rose-300 font-semibold text-xs sm:text-sm border border-rose-500/30 transition-all flex items-center gap-1.5">
                        <span>🎬</span>
                        <span>Shorts Studio</span>
                    </a>
                </div>
            </div>

            <!-- Address Warning Alert if unconfigured -->
            @php
                $pAddr = is_array($vendor->pickup_address) ? $vendor->pickup_address : (json_decode($vendor->pickup_address, true) ?: []);
                $missingPickupAddress = empty($pAddr['line_1']) || empty($pAddr['pincode']);
            @endphp
            @if($missingPickupAddress)
            <div class="mt-6 p-4 rounded-2xl bg-amber-500/15 border border-amber-500/30 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 text-amber-200">
                <div class="flex items-center gap-3">
                    <span class="text-2xl">📍</span>
                    <div class="text-xs">
                        <strong class="font-bold text-amber-100">Action Required:</strong> Add dispatch/pickup address for logistics and buyer delivery calculations.
                    </div>
                </div>
                <a href="{{ route('vendor.settings') }}#pickup-address" class="px-3.5 py-1.5 rounded-lg bg-amber-400 hover:bg-amber-300 text-slate-950 font-bold text-xs shrink-0 transition-colors">
                    Configure Address &rarr;
                </a>
            </div>
            @endif
        </div>

        <!-- 2. Time Horizon & Filter Tabs Bar -->
        <div class="p-3 rounded-2xl bg-white dark:bg-[#0c0d14] border border-slate-200 dark:border-white/[0.08] shadow-sm flex flex-wrap items-center justify-between gap-3">
            <div class="flex flex-wrap items-center gap-1.5">
                <span class="text-xs font-bold text-slate-400 dark:text-zinc-500 uppercase tracking-wider px-2">Window:</span>
                
                @php
                    $periods = [
                        'all' => 'Lifetime',
                        'today' => 'Today',
                        'yesterday' => 'Yesterday',
                        '7_days' => 'Last 7 Days',
                        'this_month' => 'This Month',
                        'last_month' => 'Last Month',
                        'this_year' => 'This Year',
                    ];
                @endphp

                @foreach($periods as $key => $lbl)
                    <a href="{{ route('vendor.dashboard', ['period' => $key]) }}" 
                       class="px-3 py-1.5 rounded-xl text-xs font-bold transition-all duration-150 {{ $period === $key ? 'bg-gradient-to-r from-purple-600 to-indigo-600 text-white shadow-md shadow-purple-600/20' : 'text-slate-600 dark:text-zinc-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-white/[0.05]' }}">
                        {{ $lbl }}
                    </a>
                @endforeach

                <button type="button" 
                        @click="showCustomRange = !showCustomRange"
                        class="px-3 py-1.5 rounded-xl text-xs font-bold transition-all flex items-center gap-1.5 {{ $period === 'custom' ? 'bg-gradient-to-r from-purple-600 to-indigo-600 text-white shadow-md' : 'text-slate-600 dark:text-zinc-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-white/[0.05] border border-slate-200 dark:border-white/10' }}">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    <span>Custom</span>
                </button>
            </div>

            <div class="text-xs font-semibold text-slate-500 dark:text-zinc-400 flex items-center gap-1.5 px-2">
                <span class="w-2 h-2 rounded-full bg-purple-500"></span>
                <span>Audited Scope: <strong class="text-slate-900 dark:text-white">{{ $periodLabel }}</strong></span>
            </div>
        </div>

        <!-- Custom Date Range Form (Expandable) -->
        <div x-show="showCustomRange" x-cloak class="p-5 rounded-2xl bg-white dark:bg-[#0c0d14] border border-purple-500/30 shadow-xl" x-transition>
            <form method="GET" action="{{ route('vendor.dashboard') }}" class="flex flex-wrap items-end gap-4">
                <input type="hidden" name="period" value="custom">
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-zinc-300 mb-1">From Date</label>
                    <input type="date" name="start_date" value="{{ $startDateInput ?? request('start_date') }}" required
                           class="px-3.5 py-2 text-xs font-medium border border-slate-200 dark:border-white/10 rounded-xl bg-slate-50 dark:bg-zinc-900 text-slate-900 dark:text-white focus:ring-2 focus:ring-purple-500 focus:outline-none">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-zinc-300 mb-1">To Date</label>
                    <input type="date" name="end_date" value="{{ $endDateInput ?? request('end_date') }}" required
                           class="px-3.5 py-2 text-xs font-medium border border-slate-200 dark:border-white/10 rounded-xl bg-slate-50 dark:bg-zinc-900 text-slate-900 dark:text-white focus:ring-2 focus:ring-purple-500 focus:outline-none">
                </div>
                <div class="flex items-center gap-2">
                    <button type="submit" class="px-5 py-2 bg-gradient-to-r from-purple-600 to-indigo-600 text-white text-xs font-bold rounded-xl shadow-md hover:opacity-95 transition-all">
                        Apply Scope
                    </button>
                    <a href="{{ route('vendor.dashboard') }}" class="px-3 py-2 text-xs font-bold text-slate-500 dark:text-zinc-400 hover:text-slate-900 dark:hover:text-white">
                        Reset
                    </a>
                </div>
            </form>
        </div>

        <!-- 3. Award-Winning 4-Pillar KPI Intelligence Grid -->
        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
            
            <!-- KPI 1: Realized Net Payout -->
            <div class="aw-glow-card rounded-3xl p-6 bg-white dark:bg-[#0d0f18] border border-slate-200 dark:border-white/[0.08] shadow-sm flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-[11px] font-black uppercase tracking-wider text-slate-400 dark:text-zinc-400">
                            {{ $period === 'all' ? 'Lifetime Realized' : 'Period Sales' }}
                        </span>
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-emerald-500/15 text-emerald-600 dark:text-emerald-400 border border-emerald-500/30">
                            ✓ Paid
                        </span>
                    </div>
                    <div class="text-3xl font-display font-extrabold text-slate-900 dark:text-white tracking-tight">
                        ₹{{ number_format($totalRevenue / 100, 2) }}
                    </div>
                    <p class="text-[11px] text-slate-400 dark:text-zinc-500 mt-1">Realized paid orders (cancelled excluded)</p>
                </div>

                <div class="mt-5 pt-4 border-t border-slate-100 dark:border-white/[0.06] grid grid-cols-2 gap-2 text-xs">
                    <div class="p-2.5 rounded-xl bg-slate-50 dark:bg-white/[0.03] border border-slate-100 dark:border-white/[0.05]">
                        <div class="text-[9px] uppercase font-bold text-slate-400 dark:text-zinc-400">Net Take-Home</div>
                        <div class="text-xs font-extrabold text-emerald-600 dark:text-emerald-400">₹{{ number_format($netPayout / 100, 2) }}</div>
                    </div>
                    <div class="p-2.5 rounded-xl bg-slate-50 dark:bg-white/[0.03] border border-slate-100 dark:border-white/[0.05]">
                        <div class="text-[9px] uppercase font-bold text-slate-400 dark:text-zinc-400">Platform Fee</div>
                        <div class="text-xs font-extrabold text-slate-700 dark:text-zinc-300">₹{{ number_format($platformFee / 100, 2) }}</div>
                    </div>
                </div>
            </div>

            <!-- KPI 2: Order Velocity & AOV -->
            <div class="aw-glow-card rounded-3xl p-6 bg-white dark:bg-[#0d0f18] border border-slate-200 dark:border-white/[0.08] shadow-sm flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-[11px] font-black uppercase tracking-wider text-slate-400 dark:text-zinc-400">
                            Order Volume
                        </span>
                        <div class="w-8 h-8 rounded-xl bg-blue-500/10 text-blue-500 flex items-center justify-center text-sm font-bold">
                            🛍️
                        </div>
                    </div>
                    <div class="text-3xl font-display font-extrabold text-slate-900 dark:text-white tracking-tight">
                        {{ $paidOrdersCount }} <span class="text-sm font-semibold text-slate-400 dark:text-zinc-500">paid</span>
                    </div>
                    <p class="text-[11px] text-slate-400 dark:text-zinc-500 mt-1">{{ $totalOrdersInPeriod }} total recorded transactions</p>
                </div>

                <div class="mt-5 pt-4 border-t border-slate-100 dark:border-white/[0.06] flex items-center justify-between text-xs">
                    <div>
                        <div class="text-[9px] uppercase font-bold text-slate-400 dark:text-zinc-400">Avg. Order Value</div>
                        <div class="text-xs font-extrabold text-slate-900 dark:text-white">₹{{ number_format($avgOrderValue / 100, 2) }}</div>
                    </div>
                    <a href="{{ route('vendor.orders.index') }}" class="text-xs font-bold text-purple-600 dark:text-purple-400 hover:underline">
                        Orders &rarr;
                    </a>
                </div>
            </div>

            <!-- KPI 3: Actionable Pipeline -->
            <div class="aw-glow-card rounded-3xl p-6 bg-white dark:bg-[#0d0f18] border border-slate-200 dark:border-white/[0.08] shadow-sm flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-[11px] font-black uppercase tracking-wider text-slate-400 dark:text-zinc-400">
                            Fulfillment Queue
                        </span>
                        <div class="w-8 h-8 rounded-xl bg-amber-500/10 text-amber-500 flex items-center justify-center text-sm font-bold">
                            ⚡
                        </div>
                    </div>
                    <div class="text-3xl font-display font-extrabold text-amber-500 tracking-tight">
                        {{ $actionableOrders }} <span class="text-sm font-semibold text-slate-400 dark:text-zinc-500">pending</span>
                    </div>
                    <p class="text-[11px] text-slate-400 dark:text-zinc-500 mt-1">Orders requiring dispatch or prep</p>
                </div>

                <div class="mt-5 pt-4 border-t border-slate-100 dark:border-white/[0.06] grid grid-cols-2 gap-2 text-xs">
                    <div class="p-2 rounded-xl bg-amber-500/10 border border-amber-500/20">
                        <div class="text-[9px] uppercase font-bold text-amber-600 dark:text-amber-400">Unpaid / In Cart</div>
                        <div class="text-xs font-bold text-amber-700 dark:text-amber-300">₹{{ number_format($pendingRevenue / 100, 2) }}</div>
                    </div>
                    <div class="p-2 rounded-xl bg-rose-500/10 border border-rose-500/20">
                        <div class="text-[9px] uppercase font-bold text-rose-600 dark:text-rose-400">Cancelled</div>
                        <div class="text-xs font-bold text-rose-700 dark:text-rose-300">₹{{ number_format($cancelledRevenue / 100, 2) }}</div>
                    </div>
                </div>
            </div>

            <!-- KPI 4: Catalog & Inventory Health -->
            <div class="aw-glow-card rounded-3xl p-6 bg-white dark:bg-[#0d0f18] border border-slate-200 dark:border-white/[0.08] shadow-sm flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-[11px] font-black uppercase tracking-wider text-slate-400 dark:text-zinc-400">
                            {{ $currV['catalogLabel'] }}
                        </span>
                        <div class="w-8 h-8 rounded-xl bg-purple-500/10 text-purple-500 flex items-center justify-center text-sm font-bold">
                            {{ $currV['icon'] }}
                        </div>
                    </div>
                    <div class="text-3xl font-display font-extrabold text-slate-900 dark:text-white tracking-tight">
                        {{ $totalProducts }} <span class="text-sm font-semibold text-slate-400 dark:text-zinc-500">listed</span>
                    </div>
                    <p class="text-[11px] text-slate-400 dark:text-zinc-500 mt-1">Active verified catalog listings</p>
                </div>

                <div class="mt-5 pt-4 border-t border-slate-100 dark:border-white/[0.06] flex items-center justify-between text-xs">
                    <span class="text-slate-400 dark:text-zinc-500 text-[11px]">Ready on storefront</span>
                    <a href="{{ route('vendor.products.index') }}" class="text-xs font-bold text-purple-600 dark:text-purple-400 hover:underline">
                        Manage Catalog &rarr;
                    </a>
                </div>
            </div>
        </div>

        <!-- 4. Interactive Visual Intelligence & Operations Center -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            
            <!-- Revenue Curve Chart (2 cols) -->
            <div class="lg:col-span-2 rounded-3xl p-6 sm:p-7 bg-white dark:bg-[#0d0f18] border border-slate-200 dark:border-white/[0.08] shadow-sm flex flex-col">
                <div class="flex flex-wrap items-center justify-between gap-3 mb-6">
                    <div>
                        <h2 class="text-base sm:text-lg font-display font-bold text-slate-900 dark:text-white tracking-tight">
                            Realized Revenue Velocity
                        </h2>
                        <p class="text-xs text-slate-400 dark:text-zinc-500 mt-0.5">
                            {{ $periodLabel }} · Audited cash inflows (Unpaid, draft & cancelled items excluded)
                        </p>
                    </div>

                    <div class="flex items-center gap-4 text-xs font-semibold">
                        <div class="flex items-center gap-1.5 text-purple-600 dark:text-purple-400">
                            <span class="w-3 h-3 rounded-full bg-purple-500"></span>
                            <span>Gross Paid (₹)</span>
                        </div>
                        <div class="flex items-center gap-1.5 text-emerald-600 dark:text-emerald-400">
                            <span class="w-3 h-3 rounded-full bg-emerald-500"></span>
                            <span>Net Payout (₹)</span>
                        </div>
                    </div>
                </div>

                <!-- Canvas Container -->
                <div class="relative w-full h-72 sm:h-80">
                    <canvas id="revenueChart"></canvas>
                </div>
            </div>

            <!-- Vertical Command Deck & Fast Actions (1 col) -->
            <div class="rounded-3xl p-6 sm:p-7 bg-white dark:bg-[#0d0f18] border border-slate-200 dark:border-white/[0.08] shadow-sm flex flex-col justify-between space-y-6">
                <div>
                    <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-white/[0.06] mb-4">
                        <h3 class="text-sm font-bold text-slate-900 dark:text-white uppercase tracking-wider">
                            Store Command Deck
                        </h3>
                        <span class="text-xs text-purple-600 dark:text-purple-400 font-extrabold">100% Isolated</span>
                    </div>

                    <div class="space-y-3">
                        <a href="{{ route('vendor.products.create') }}" class="group flex items-center justify-between p-3.5 rounded-2xl bg-slate-50 dark:bg-white/[0.03] border border-slate-100 dark:border-white/[0.06] hover:border-purple-500/40 hover:bg-purple-500/5 transition-all">
                            <div class="flex items-center gap-3">
                                <span class="w-9 h-9 rounded-xl bg-purple-500/10 text-purple-500 flex items-center justify-center text-lg">
                                    {{ $currV['icon'] }}
                                </span>
                                <div>
                                    <div class="text-xs font-bold text-slate-900 dark:text-white group-hover:text-purple-500 transition-colors">
                                        {{ $currV['action'] }}
                                    </div>
                                    <div class="text-[10px] text-slate-400 dark:text-zinc-500">Add to your live catalog</div>
                                </div>
                            </div>
                            <span class="text-xs text-slate-400 group-hover:text-purple-500 transition-colors">&rarr;</span>
                        </a>

                        <a href="{{ route('vendor.shorts.index') }}" class="group flex items-center justify-between p-3.5 rounded-2xl bg-slate-50 dark:bg-white/[0.03] border border-slate-100 dark:border-white/[0.06] hover:border-rose-500/40 hover:bg-rose-500/5 transition-all">
                            <div class="flex items-center gap-3">
                                <span class="w-9 h-9 rounded-xl bg-rose-500/10 text-rose-500 flex items-center justify-center text-lg">
                                    🎬
                                </span>
                                <div>
                                    <div class="text-xs font-bold text-slate-900 dark:text-white group-hover:text-rose-500 transition-colors">
                                        Upload Shoppable Reel
                                    </div>
                                    <div class="text-[10px] text-slate-400 dark:text-zinc-500">Tag products & boost sales</div>
                                </div>
                            </div>
                            <span class="text-xs text-slate-400 group-hover:text-rose-500 transition-colors">&rarr;</span>
                        </a>

                        <a href="{{ route('vendor.coupons.index') }}" class="group flex items-center justify-between p-3.5 rounded-2xl bg-slate-50 dark:bg-white/[0.03] border border-slate-100 dark:border-white/[0.06] hover:border-emerald-500/40 hover:bg-emerald-500/5 transition-all">
                            <div class="flex items-center gap-3">
                                <span class="w-9 h-9 rounded-xl bg-emerald-500/10 text-emerald-500 flex items-center justify-center text-lg">
                                    🏷️
                                </span>
                                <div>
                                    <div class="text-xs font-bold text-slate-900 dark:text-white group-hover:text-emerald-500 transition-colors">
                                        Promotions & Wheel Prizes
                                    </div>
                                    <div class="text-[10px] text-slate-400 dark:text-zinc-500">Create discount vouchers</div>
                                </div>
                            </div>
                            <span class="text-xs text-slate-400 group-hover:text-emerald-500 transition-colors">&rarr;</span>
                        </a>

                        <a href="{{ route('vendor.settings') }}" class="group flex items-center justify-between p-3.5 rounded-2xl bg-slate-50 dark:bg-white/[0.03] border border-slate-100 dark:border-white/[0.06] hover:border-blue-500/40 hover:bg-blue-500/5 transition-all">
                            <div class="flex items-center gap-3">
                                <span class="w-9 h-9 rounded-xl bg-blue-500/10 text-blue-500 flex items-center justify-center text-lg">
                                    ⚙️
                                </span>
                                <div>
                                    <div class="text-xs font-bold text-slate-900 dark:text-white group-hover:text-blue-500 transition-colors">
                                        Store Settings & Payouts
                                    </div>
                                    <div class="text-[10px] text-slate-400 dark:text-zinc-500">Bank accounts & operations</div>
                                </div>
                            </div>
                            <span class="text-xs text-slate-400 group-hover:text-blue-500 transition-colors">&rarr;</span>
                        </a>
                    </div>
                </div>

                <!-- Subdomain Tenancy Info Pill -->
                <div class="p-4 rounded-2xl bg-gradient-to-br from-purple-900/40 to-indigo-950/40 border border-purple-500/20 text-xs">
                    <div class="font-bold text-purple-300 flex items-center gap-1.5 mb-1">
                        <span>⚡</span> Wildcard Routing Active
                    </div>
                    <p class="text-[11px] text-purple-200/80 leading-relaxed">
                        Your store runs on independent tenancy at 
                        <code class="px-1 py-0.5 rounded bg-black/40 text-purple-300 font-mono">{{ $vendor->slug }}.{{ config('marketplace.domain') }}</code>
                    </p>
                </div>
            </div>
        </div>

        <!-- 5. Recent Transaction Flow Stream -->
        <div class="rounded-3xl p-6 sm:p-7 bg-white dark:bg-[#0d0f18] border border-slate-200 dark:border-white/[0.08] shadow-sm">
            <div class="flex items-center justify-between mb-6 pb-4 border-b border-slate-100 dark:border-white/[0.06]">
                <div>
                    <h3 class="text-base sm:text-lg font-display font-bold text-slate-900 dark:text-white tracking-tight">
                        Recent Transactions
                    </h3>
                    <p class="text-xs text-slate-400 dark:text-zinc-500 mt-0.5">
                        Latest incoming orders and booking requests
                    </p>
                </div>
                <a href="{{ route('vendor.orders.index') }}" class="px-3.5 py-1.5 rounded-xl text-xs font-bold text-purple-600 dark:text-purple-400 hover:bg-purple-50 dark:hover:bg-purple-500/10 transition-colors">
                    View All Orders &rarr;
                </a>
            </div>

            @if($recentOrders->isEmpty())
                <div class="text-center py-12 px-4">
                    <div class="w-16 h-16 rounded-3xl bg-slate-100 dark:bg-white/[0.04] text-3xl flex items-center justify-center mx-auto mb-3">
                        📦
                    </div>
                    <h4 class="text-sm font-bold text-slate-900 dark:text-white">No Orders in this Window</h4>
                    <p class="text-xs text-slate-400 dark:text-zinc-500 mt-1 max-w-sm mx-auto">
                        There are no orders matching {{ $periodLabel }}. Once customers complete purchases or book services, they will appear here instantly.
                    </p>
                    <a href="{{ route('vendor.products.create') }}" class="mt-4 inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-purple-600 text-white text-xs font-bold hover:bg-purple-500 transition-colors">
                        <span>Add New Listing</span>
                    </a>
                </div>
            @else
                <div class="overflow-x-auto no-scrollbar">
                    <table class="w-full text-left text-xs">
                        <thead>
                            <tr class="text-[10px] font-black uppercase tracking-wider text-slate-400 dark:text-zinc-500 border-b border-slate-100 dark:border-white/[0.06]">
                                <th class="pb-3 px-3">Order / Reference</th>
                                <th class="pb-3 px-3">Customer</th>
                                <th class="pb-3 px-3">Date</th>
                                <th class="pb-3 px-3">Items / Units</th>
                                <th class="pb-3 px-3">Gross Total</th>
                                <th class="pb-3 px-3">Payment</th>
                                <th class="pb-3 px-3">Status</th>
                                <th class="pb-3 px-3 text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-white/[0.04]">
                            @foreach($recentOrders as $o)
                            <tr class="hover:bg-slate-50 dark:hover:bg-white/[0.02] transition-colors">
                                <td class="py-3.5 px-3 font-mono font-bold text-slate-900 dark:text-white">
                                    #{{ $o->order_number ?? $o->id }}
                                </td>
                                <td class="py-3.5 px-3">
                                    <div class="font-bold text-slate-900 dark:text-white">{{ $o->user?->name ?? 'Guest Buyer' }}</div>
                                    <div class="text-[10px] text-slate-400 dark:text-zinc-500">{{ $o->user?->email ?? $o->phone }}</div>
                                </td>
                                <td class="py-3.5 px-3 text-slate-500 dark:text-zinc-400 whitespace-nowrap">
                                    {{ $o->created_at->format('d M, g:i A') }}
                                </td>
                                <td class="py-3.5 px-3 font-medium text-slate-600 dark:text-zinc-300">
                                    {{ $o->items_count ?? ($o->items ? $o->items->count() : 1) }} items
                                </td>
                                <td class="py-3.5 px-3 font-extrabold text-slate-900 dark:text-white whitespace-nowrap">
                                    ₹{{ number_format(($o->subtotal - $o->discount_amount) / 100, 2) }}
                                </td>
                                <td class="py-3.5 px-3 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-extrabold {{ $o->payment_status === 'paid' ? 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20' : 'bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/20' }}">
                                        {{ ucfirst($o->payment_status) }}
                                    </span>
                                </td>
                                <td class="py-3.5 px-3 whitespace-nowrap">
                                    @php
                                        $sColors = [
                                            'completed' => 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border-emerald-500/20',
                                            'processing' => 'bg-blue-500/10 text-blue-600 dark:text-blue-400 border-blue-500/20',
                                            'pending' => 'bg-amber-500/10 text-amber-600 dark:text-amber-400 border-amber-500/20',
                                            'cancelled' => 'bg-rose-500/10 text-rose-600 dark:text-rose-400 border-rose-500/20',
                                        ];
                                    @endphp
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-extrabold border {{ $sColors[$o->status] ?? 'bg-slate-100 text-slate-600' }}">
                                        {{ ucfirst($o->status) }}
                                    </span>
                                </td>
                                <td class="py-3.5 px-3 text-right whitespace-nowrap">
                                    <a href="{{ route('vendor.orders.show', $o->id) }}" class="px-2.5 py-1 rounded-lg bg-slate-100 dark:bg-white/10 hover:bg-purple-600 hover:text-white dark:hover:bg-purple-600 text-slate-700 dark:text-zinc-300 font-bold text-[11px] transition-colors">
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
            const ctx = chartCanvas.getContext('2d');
            const data = @json($chartData);
            
            // Modern Gradients
            let gradientGross = ctx.createLinearGradient(0, 0, 0, 320);
            gradientGross.addColorStop(0, 'rgba(168, 85, 247, 0.45)'); // purple-500
            gradientGross.addColorStop(1, 'rgba(168, 85, 247, 0.0)');

            let gradientNet = ctx.createLinearGradient(0, 0, 0, 320);
            gradientNet.addColorStop(0, 'rgba(16, 185, 129, 0.35)'); // emerald-500
            gradientNet.addColorStop(1, 'rgba(16, 185, 129, 0.0)');
            
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: data.map(d => d.label),
                    datasets: [
                        {
                            label: 'Gross Paid Revenue',
                            data: data.map(d => d.revenue),
                            borderColor: '#a855f7', // purple-500
                            backgroundColor: gradientGross,
                            borderWidth: 2.5,
                            pointBackgroundColor: isDark ? '#0d0f18' : '#ffffff',
                            pointBorderColor: '#a855f7',
                            pointBorderWidth: 2,
                            pointRadius: 4,
                            pointHoverRadius: 6,
                            fill: true,
                            tension: 0.38
                        },
                        {
                            label: 'Net Payout (95%)',
                            data: data.map(d => d.net),
                            borderColor: '#10b981', // emerald-500
                            backgroundColor: gradientNet,
                            borderWidth: 2,
                            borderDash: [5, 5],
                            pointBackgroundColor: isDark ? '#0d0f18' : '#ffffff',
                            pointBorderColor: '#10b981',
                            pointBorderWidth: 2,
                            pointRadius: 3,
                            pointHoverRadius: 5,
                            fill: false,
                            tension: 0.38
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            backgroundColor: isDark ? 'rgba(13, 15, 24, 0.95)' : 'rgba(15, 23, 42, 0.95)',
                            padding: 12,
                            cornerRadius: 14,
                            borderColor: isDark ? 'rgba(255, 255, 255, 0.1)' : 'rgba(0, 0, 0, 0.1)',
                            borderWidth: 1,
                            titleFont: { size: 12, family: 'Plus Jakarta Sans', weight: 'bold' },
                            bodyFont: { size: 13, family: 'Plus Jakarta Sans' },
                            displayColors: true,
                            callbacks: {
                                label: function(context) {
                                    return ' ' + context.dataset.label + ': ₹' + Number(context.parsed.y).toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: {
                                color: isDark ? 'rgba(255, 255, 255, 0.04)' : 'rgba(0, 0, 0, 0.04)',
                                drawBorder: false,
                            },
                            ticks: {
                                font: { family: 'Plus Jakarta Sans', size: 11 },
                                color: isDark ? '#71717a' : '#94a3b8',
                                callback: function(value) {
                                    return '₹' + value.toLocaleString('en-IN');
                                }
                            }
                        },
                        x: {
                            grid: {
                                display: false,
                                drawBorder: false,
                            },
                            ticks: {
                                font: { family: 'Plus Jakarta Sans', size: 11, weight: 'bold' },
                                color: isDark ? '#a1a1aa' : '#64748b',
                                maxRotation: 45,
                                autoSkip: true,
                                maxTicksLimit: 12
                            }
                        }
                    },
                    interaction: {
                        intersect: false,
                        mode: 'index',
                    },
                }
            });
        });
    </script>
</x-layouts.vendor>
