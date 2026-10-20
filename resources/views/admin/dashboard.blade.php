<x-layouts.admin title="Platform Command Center">
    <div class="space-y-8">
        
        <!-- Welcome Greeting & Status Banner -->
        <div class="bg-gradient-to-r from-slate-900 via-slate-800 to-slate-900 rounded-3xl p-6 sm:p-8 text-white border border-slate-800 shadow-xl flex flex-col lg:flex-row justify-between items-start lg:items-center gap-6">
            <div>
                <div class="flex items-center gap-2 text-xs font-mono uppercase tracking-wider text-amber-400 font-semibold mb-2">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                    <span>Executive Command Hub</span>
                    <span>•</span>
                    <span>Real-time Financial Reconciliations</span>
                </div>
                <h2 class="text-2xl sm:text-3xl font-serif font-bold text-white tracking-tight">
                    Marketplace Treasury & Operations
                </h2>
                <p class="text-xs sm:text-sm text-slate-300 mt-1 max-w-2xl leading-relaxed">
                    Audited overview of PhonePe customer collections, Current Account bank settlements, vendor 95% UPI disbursals, and fulfillment telemetry across all boutique ateliers.
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-3">
                <form action="{{ route('admin.orders.batch_settlement') }}" method="POST" onsubmit="return confirm('Process clearing for all eligible T+1 pending customer orders? This will mark funds as received in your Current Account and unlock vendor payouts.');">
                    @csrf
                    <button type="submit" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs font-extrabold bg-emerald-500 hover:bg-emerald-400 text-slate-950 transition-all shadow-md shadow-emerald-500/20 active:scale-95 cursor-pointer">
                        <span>⚡ Process Today's Bank Settlements</span>
                    </button>
                </form>
                <a href="{{ route('admin.homepage') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs font-bold bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700 transition-all">
                    <span>✨ Homepage Studio</span>
                </a>
            </div>
        </div>

        @if(session('success'))
            <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-2xl p-4 text-xs sm:text-sm flex items-center gap-3 shadow-xs">
                <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        @if(session('info'))
            <div class="bg-blue-50 border border-blue-200 text-blue-800 rounded-2xl p-4 text-xs sm:text-sm flex items-center gap-3 shadow-xs">
                <svg class="w-5 h-5 text-blue-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span>{{ session('info') }}</span>
            </div>
        @endif

        <!-- SECTION 1: FINANCIAL & TREASURY LEDGER (RECONCILED ACCURACY) -->
        <div>
            <div class="flex items-center justify-between mb-3 px-1">
                <div>
                    <h3 class="text-sm font-bold uppercase tracking-wider text-slate-400 font-mono">1. Marketplace Treasury Ledger</h3>
                    <p class="text-xs text-slate-500">Live balance breakdown: Customer Payments = Vendor Share (95%) + Platform Profit (5%) + Courier Pass-through</p>
                </div>
                <span class="text-[11px] font-mono text-emerald-600 bg-emerald-50 border border-emerald-200 px-2.5 py-1 rounded-lg font-semibold">
                    Zero Working Capital Float
                </span>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <!-- Gross Online GMV -->
                <div class="bg-white p-5 rounded-3xl border border-slate-200/80 shadow-xs hover:shadow-md transition-all">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400 font-mono">Gross Volume (PhonePe)</span>
                        <span class="w-7 h-7 rounded-lg bg-slate-100 text-slate-700 flex items-center justify-center font-bold text-xs">₹</span>
                    </div>
                    <p class="text-2xl font-extrabold text-slate-900 tracking-tight font-mono">₹{{ number_format($grossGmv / 100, 2) }}</p>
                    <p class="text-[11px] text-slate-500 mt-1">100% customer payments received online</p>
                </div>

                <!-- Current Account Cleared Cash -->
                <div class="bg-white p-5 rounded-3xl border border-emerald-200/90 shadow-xs hover:shadow-md transition-all bg-gradient-to-br from-white to-emerald-50/20">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-[11px] font-bold uppercase tracking-wider text-emerald-700 font-mono">Cleared in Current A/C</span>
                        <span class="w-7 h-7 rounded-lg bg-emerald-100 text-emerald-700 flex items-center justify-center font-bold text-xs">🏦</span>
                    </div>
                    <p class="text-2xl font-extrabold text-emerald-700 tracking-tight font-mono">₹{{ number_format($settledCash / 100, 2) }}</p>
                    <p class="text-[11px] text-emerald-600 mt-1">Confirmed in your master bank account</p>
                </div>

                <!-- Clearing Transit Buffer (T+1) -->
                <div class="bg-white p-5 rounded-3xl border border-amber-200/90 shadow-xs hover:shadow-md transition-all bg-gradient-to-br from-white to-amber-50/20">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-[11px] font-bold uppercase tracking-wider text-amber-700 font-mono">Clearing Buffer (T+1)</span>
                        <span class="w-7 h-7 rounded-lg bg-amber-100 text-amber-700 flex items-center justify-center font-bold text-xs">⏳</span>
                    </div>
                    <p class="text-2xl font-extrabold text-amber-700 tracking-tight font-mono">₹{{ number_format($clearingBuffer / 100, 2) }}</p>
                    <p class="text-[11px] text-amber-600 mt-1">{{ $health['unsettled_orders'] }} order(s) clearing tomorrow ~11 AM</p>
                </div>

                <!-- Disbursed to Vendors -->
                <div class="bg-white p-5 rounded-3xl border border-blue-200/90 shadow-xs hover:shadow-md transition-all bg-gradient-to-br from-white to-blue-50/20">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-[11px] font-bold uppercase tracking-wider text-blue-700 font-mono">Disbursed to Vendors</span>
                        <span class="w-7 h-7 rounded-lg bg-blue-100 text-blue-700 flex items-center justify-center font-bold text-xs">💸</span>
                    </div>
                    <p class="text-2xl font-extrabold text-blue-700 tracking-tight font-mono">₹{{ number_format($vendorDisbursed / 100, 2) }}</p>
                    <p class="text-[11px] text-blue-600 mt-1">95% net transferred to vendor UPI IDs</p>
                </div>
            </div>

            <!-- Second Row: Net Margin & Pass-through Pools -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mt-4">
                <!-- Platform Retained Net Profit -->
                <div class="bg-white p-4.5 rounded-2xl border border-purple-200/80 shadow-xs flex items-center justify-between">
                    <div>
                        <span class="text-[11px] font-bold uppercase tracking-wider text-purple-700 font-mono">Platform Net Profit (5%)</span>
                        <p class="text-xl font-extrabold text-purple-700 font-mono mt-0.5">₹{{ number_format($platformProfit / 100, 2) }}</p>
                        <p class="text-[10px] text-purple-600">Pure retained commission on sold items</p>
                    </div>
                    <div class="w-10 h-10 rounded-xl bg-purple-100 text-purple-700 flex items-center justify-center font-bold text-sm">
                        💎
                    </div>
                </div>

                <!-- Courier Pass-Through Shipping Pool -->
                <div class="bg-white p-4.5 rounded-2xl border border-slate-200/80 shadow-xs flex items-center justify-between">
                    <div>
                        <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500 font-mono">Customer Shipping Pool</span>
                        <p class="text-xl font-extrabold text-slate-800 font-mono mt-0.5">₹{{ number_format($shippingPool / 100, 2) }}</p>
                        <p class="text-[10px] text-slate-400">100% pass-through for Shiprocket couriers</p>
                    </div>
                    <div class="w-10 h-10 rounded-xl bg-slate-100 text-slate-600 flex items-center justify-center font-bold text-sm">
                        🚚
                    </div>
                </div>

                <!-- Ready for Vendor Disbursal -->
                <div class="bg-white p-4.5 rounded-2xl border border-emerald-200/80 shadow-xs flex items-center justify-between">
                    <div>
                        <span class="text-[11px] font-bold uppercase tracking-wider text-emerald-800 font-mono">Ready for Disbursal</span>
                        <p class="text-xl font-extrabold text-emerald-800 font-mono mt-0.5">₹{{ number_format($pendingDisbursalAmount / 100, 2) }}</p>
                        <p class="text-[10px] text-emerald-600">{{ $health['ready_payout_count'] }} order(s) awaiting vendor acceptance</p>
                    </div>
                    <div class="w-10 h-10 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center font-bold text-sm">
                        ⚡
                    </div>
                </div>
            </div>
        </div>

        <!-- SECTION 2: FULFILLMENT PIPELINE FUNNEL -->
        <div>
            <div class="flex items-center justify-between mb-3 px-1">
                <h3 class="text-sm font-bold uppercase tracking-wider text-slate-400 font-mono">2. Order Fulfillment Pipeline</h3>
                <a href="{{ route('admin.orders') }}" class="text-xs text-amber-600 hover:underline font-bold">View Pipeline →</a>
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-5 gap-3">
                <a href="{{ route('admin.orders', ['status' => 'pending']) }}" class="bg-white p-4 rounded-2xl border border-slate-200 hover:border-amber-400 transition-all text-center group">
                    <span class="text-xl block mb-1">⏳</span>
                    <span class="text-2xl font-extrabold font-mono text-slate-900 group-hover:text-amber-600">{{ $pipeline['pending'] }}</span>
                    <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400 block mt-0.5">Pending</span>
                </a>

                <a href="{{ route('admin.orders', ['status' => 'processing']) }}" class="bg-white p-4 rounded-2xl border border-slate-200 hover:border-blue-400 transition-all text-center group">
                    <span class="text-xl block mb-1">📦</span>
                    <span class="text-2xl font-extrabold font-mono text-slate-900 group-hover:text-blue-600">{{ $pipeline['processing'] }}</span>
                    <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400 block mt-0.5">Packing</span>
                </a>

                <a href="{{ route('admin.orders', ['status' => 'shipped']) }}" class="bg-white p-4 rounded-2xl border border-slate-200 hover:border-purple-400 transition-all text-center group">
                    <span class="text-xl block mb-1">🚚</span>
                    <span class="text-2xl font-extrabold font-mono text-slate-900 group-hover:text-purple-600">{{ $pipeline['shipped'] }}</span>
                    <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400 block mt-0.5">In Transit</span>
                </a>

                <a href="{{ route('admin.orders', ['status' => 'delivered']) }}" class="bg-white p-4 rounded-2xl border border-slate-200 hover:border-emerald-400 transition-all text-center group">
                    <span class="text-xl block mb-1">✅</span>
                    <span class="text-2xl font-extrabold font-mono text-slate-900 group-hover:text-emerald-600">{{ $pipeline['delivered'] }}</span>
                    <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400 block mt-0.5">Delivered</span>
                </a>

                <a href="{{ route('admin.orders', ['status' => 'cancelled']) }}" class="bg-white p-4 rounded-2xl border border-slate-200 hover:border-rose-400 transition-all text-center group">
                    <span class="text-xl block mb-1">❌</span>
                    <span class="text-2xl font-extrabold font-mono text-slate-900 group-hover:text-rose-600">{{ $pipeline['cancelled'] }}</span>
                    <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400 block mt-0.5">Cancelled</span>
                </a>
            </div>
        </div>

        <!-- SECTION 3: TOP PERFORMING BOUTIQUES & OPERATIONAL HEALTH -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Top Boutiques (2 Cols) -->
            <div class="lg:col-span-2 bg-white rounded-3xl border border-slate-200/80 shadow-xs overflow-hidden">
                <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between">
                    <div>
                        <h3 class="font-serif font-bold text-lg text-slate-900">Top Performing Boutiques</h3>
                        <p class="text-xs text-slate-400 mt-0.5">Ranked by gross sales volume and fulfillment performance</p>
                    </div>
                    <a href="{{ route('admin.vendors') }}" class="text-xs text-amber-600 hover:underline font-bold">Manage Vendors →</a>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-100">
                        <thead class="bg-slate-50/60">
                            <tr>
                                <th class="px-6 py-3 text-left text-[11px] font-bold text-slate-400 uppercase tracking-wider">Boutique</th>
                                <th class="px-6 py-3 text-left text-[11px] font-bold text-slate-400 uppercase tracking-wider">Paid Orders</th>
                                <th class="px-6 py-3 text-left text-[11px] font-bold text-slate-400 uppercase tracking-wider">Gross GMV</th>
                                <th class="px-6 py-3 text-left text-[11px] font-bold text-slate-400 uppercase tracking-wider">Payout Account</th>
                                <th class="px-6 py-3 text-right text-[11px] font-bold text-slate-400 uppercase tracking-wider">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($topVendors as $vendor)
                                <tr class="hover:bg-slate-50/50 transition-colors">
                                    <td class="px-6 py-3.5 whitespace-nowrap">
                                        <div class="text-xs font-bold text-slate-900">{{ $vendor->shop_name }}</div>
                                        <div class="text-[10px] text-slate-400 font-mono">{{ $vendor->user->email ?? 'N/A' }}</div>
                                    </td>
                                    <td class="px-6 py-3.5 whitespace-nowrap text-xs font-mono font-bold text-slate-700">
                                        {{ $vendor->orders_count }} orders
                                    </td>
                                    <td class="px-6 py-3.5 whitespace-nowrap text-xs font-mono font-bold text-slate-900">
                                        ₹{{ number_format(($vendor->gmv ?? 0) / 100, 2) }}
                                    </td>
                                    <td class="px-6 py-3.5 whitespace-nowrap text-xs">
                                        @if(!empty($vendor->settings->upi_id))
                                            <span class="inline-flex items-center gap-1 text-[10px] font-mono font-bold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded border border-emerald-200">
                                                <span>⚡</span> {{ $vendor->settings->upi_id }}
                                            </span>
                                        @else
                                            <span class="text-[10px] text-rose-500 italic">No UPI Set</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-3.5 whitespace-nowrap text-right text-xs">
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ $vendor->status === 'active' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }} uppercase">
                                            {{ $vendor->status }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-8 text-center text-xs text-slate-400 italic">No boutique vendors registered yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Platform Health Checks (1 Col) -->
            <div class="bg-white rounded-3xl border border-slate-200/80 shadow-xs p-6 flex flex-col justify-between">
                <div>
                    <h3 class="font-serif font-bold text-lg text-slate-900 mb-1">Operational Diagnostics</h3>
                    <p class="text-xs text-slate-400 mb-4">Live inventory, merchant and user metrics</p>

                    <div class="space-y-3.5">
                        <div class="flex items-center justify-between p-3 rounded-2xl bg-slate-50 border border-slate-100">
                            <span class="text-xs font-medium text-slate-600">Total Boutique Merchants</span>
                            <span class="text-xs font-bold font-mono text-slate-900">{{ $health['total_vendors'] }} ({{ $health['active_vendors'] }} Active)</span>
                        </div>

                        <div class="flex items-center justify-between p-3 rounded-2xl bg-slate-50 border border-slate-100">
                            <span class="text-xs font-medium text-slate-600">Pending Vendor Review</span>
                            @if($health['pending_vendors'] > 0)
                                <a href="{{ route('admin.vendors', ['status' => 'pending']) }}" class="text-xs font-bold font-mono text-amber-600 hover:underline">
                                    {{ $health['pending_vendors'] }} Awaiting Review ⚠️
                                </a>
                            @else
                                <span class="text-xs font-bold font-mono text-emerald-600">0 Pending ✓</span>
                            @endif
                        </div>

                        <div class="flex items-center justify-between p-3 rounded-2xl bg-slate-50 border border-slate-100">
                            <span class="text-xs font-medium text-slate-600">Total Catalog Products</span>
                            <span class="text-xs font-bold font-mono text-slate-900">{{ $health['total_products'] }} items</span>
                        </div>

                        <div class="flex items-center justify-between p-3 rounded-2xl bg-slate-50 border border-slate-100">
                            <span class="text-xs font-medium text-slate-600">Out of Stock Alerts</span>
                            @if($health['out_of_stock_products'] > 0)
                                <a href="{{ route('admin.products', ['stock' => 'out']) }}" class="text-xs font-bold font-mono text-rose-600 hover:underline">
                                    {{ $health['out_of_stock_products'] }} item(s) out of stock
                                </a>
                            @else
                                <span class="text-xs font-bold font-mono text-emerald-600">Stock Healthy ✓</span>
                            @endif
                        </div>

                        <div class="flex items-center justify-between p-3 rounded-2xl bg-slate-50 border border-slate-100">
                            <span class="text-xs font-medium text-slate-600">Registered Customer Accounts</span>
                            <span class="text-xs font-bold font-mono text-slate-900">{{ $health['total_customers'] }} shoppers</span>
                        </div>
                    </div>
                </div>

                <div class="mt-6 pt-4 border-t border-slate-100">
                    <a href="{{ route('admin.orders') }}" class="w-full flex items-center justify-center gap-2 py-2.5 bg-slate-900 hover:bg-black text-white text-xs font-bold rounded-xl transition-all shadow-xs">
                        <span>Open Comprehensive Order Auditor</span>
                        <span>&rarr;</span>
                    </a>
                </div>
            </div>
        </div>

        <!-- SECTION 4: RECENT MARKETPLACE TRANSACTIONS -->
        <div class="bg-white rounded-3xl border border-slate-200/80 shadow-xs overflow-hidden">
            <div class="px-6 py-5 border-b border-slate-100 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3">
                <div>
                    <h3 class="font-serif font-bold text-xl text-slate-900">Recent Marketplace Transactions</h3>
                    <p class="text-xs text-slate-400 mt-0.5">Real-time incoming customer orders with PhonePe settlement &amp; payout audit tags</p>
                </div>
                <a href="{{ route('admin.orders') }}" class="inline-flex items-center gap-1 text-xs font-bold text-slate-700 hover:text-amber-600 transition-colors bg-slate-50 px-3.5 py-2 rounded-xl border border-slate-200">
                    <span>View All Orders ({{ $health['unsettled_orders'] }} Clearing)</span>
                    <span>&rarr;</span>
                </a>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-100">
                    <thead class="bg-slate-50/60">
                        <tr>
                            <th class="px-6 py-3.5 text-left text-[11px] font-bold text-slate-400 uppercase tracking-wider">Order ID</th>
                            <th class="px-6 py-3.5 text-left text-[11px] font-bold text-slate-400 uppercase tracking-wider">Boutique</th>
                            <th class="px-6 py-3.5 text-left text-[11px] font-bold text-slate-400 uppercase tracking-wider">Customer</th>
                            <th class="px-6 py-3.5 text-left text-[11px] font-bold text-slate-400 uppercase tracking-wider">Amount &amp; 95% Split</th>
                            <th class="px-6 py-3.5 text-left text-[11px] font-bold text-slate-400 uppercase tracking-wider">Bank Settlement</th>
                            <th class="px-6 py-3.5 text-left text-[11px] font-bold text-slate-400 uppercase tracking-wider">Vendor Payout</th>
                            <th class="px-6 py-3.5 text-right text-[11px] font-bold text-slate-400 uppercase tracking-wider">Audit</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($recentOrders as $order)
                            @php
                                $isSettled = $order->settlement_status === 'cleared';
                                $isPaid = $order->vendor_payout_status === 'paid';
                            @endphp
                            <tr class="hover:bg-slate-50/50 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-xs font-mono font-bold text-slate-900">{{ $order->order_number }}</div>
                                    <div class="text-[10px] text-slate-400 font-mono mt-0.5">{{ $order->created_at->format('d M, H:i') }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-xs">
                                    <div class="font-bold text-slate-900">{{ $order->vendor->shop_name ?? 'Marketplace' }}</div>
                                    @if(!empty($order->vendor->settings->upi_id))
                                        <div class="text-[10px] text-slate-400 font-mono">⚡ {{ $order->vendor->settings->upi_id }}</div>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-xs text-slate-700">
                                    <div class="font-medium text-slate-900">{{ $order->user->first_name ?? '' }} {{ $order->user->last_name ?? '' }}</div>
                                    <div class="text-[10px] text-slate-400">{{ $order->address_snapshot['city'] ?? 'India' }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-xs font-bold font-mono text-slate-900">₹{{ number_format($order->total_amount / 100, 2) }}</div>
                                    <div class="text-[10px] text-slate-400 font-mono">
                                        Vendor: <span class="text-blue-700 font-semibold">₹{{ number_format(($order->vendor_payout_amount ?: ($order->subtotal * 0.95)) / 100, 2) }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-xs">
                                    @if($isSettled)
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">
                                            ✓ Cleared in Bank
                                        </span>
                                    @elseif($order->payment_status === 'paid')
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800 animate-pulse">
                                            ⏳ Clearing (T+1)
                                        </span>
                                    @else
                                        <span class="text-[10px] text-slate-400">Unpaid</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-xs">
                                    @if($isPaid)
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-blue-100 text-blue-800 font-mono">
                                            ✓ Disbursed
                                        </span>
                                    @elseif($isSettled)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                            Ready
                                        </span>
                                    @else
                                        <span class="text-[10px] text-slate-400">Locked</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-xs">
                                    <a href="{{ route('admin.orders.show', $order) }}" class="inline-flex items-center gap-1 text-slate-700 hover:text-amber-600 font-bold bg-slate-100 hover:bg-slate-200 px-3 py-1.5 rounded-xl transition-colors">
                                        <span>Audit</span>
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-8 text-center text-xs text-slate-400 italic">No customer orders recorded yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-layouts.admin>
