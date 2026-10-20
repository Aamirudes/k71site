<x-layouts.admin title="All Orders & Settlement Auditing">
    <div class="space-y-6">
        
        <!-- Header Banner -->
        <div class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-200/80 shadow-xs flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div>
                <div class="flex items-center gap-2">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                        Two-Stage Settlement Architecture
                    </span>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-blue-50 text-blue-700 border border-blue-200">
                        Zero Working Capital Float
                    </span>
                </div>
                <h2 class="text-2xl font-serif font-bold text-slate-900 mt-2">Marketplace Orders & Banking Settlements</h2>
                <p class="text-xs sm:text-sm text-slate-500 mt-1">Cross-vendor fulfillment auditing, PhonePe T+1 bank settlement clearance, and 95% vendor instant UPI disbursals.</p>
            </div>
            <div class="flex flex-wrap items-center gap-2.5">
                <form action="{{ route('admin.orders.batch_settlement') }}" method="POST" onsubmit="return confirm('Process clearing for all eligible T+1 pending customer orders? This will mark funds as received in your Current Account and unlock vendor payouts.');">
                    @csrf
                    <button type="submit" class="px-3.5 py-2 rounded-2xl bg-emerald-600 hover:bg-emerald-700 text-xs font-bold text-white shadow-xs hover:shadow transition-all inline-flex items-center gap-1.5 cursor-pointer">
                        <span>⚡ Run Settlement Batch</span>
                    </button>
                </form>
                <span class="px-3.5 py-2 rounded-2xl bg-slate-900 text-xs font-mono font-bold text-white shadow-xs">
                    {{ $orders->total() ?? $orders->count() }} Orders Found
                </span>
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

        @if(session('error'))
            <div class="bg-rose-50 border border-rose-200 text-rose-800 rounded-2xl p-4 text-xs sm:text-sm flex items-center gap-3 shadow-xs">
                <svg class="w-5 h-5 text-rose-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        <!-- Financial & Settlement Metric Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
            <!-- Gross Paid Revenue -->
            <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-xs">
                <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400 font-mono">Gross Volume (PhonePe)</p>
                <p class="text-xl font-bold font-mono text-slate-900 mt-1.5">₹{{ number_format(($settlementStats['gross_volume'] ?? 0) / 100, 2) }}</p>
                <p class="text-[11px] text-slate-500 mt-1">100% customer payments received online</p>
            </div>

            <!-- Settled in Current Account -->
            <div class="bg-white rounded-2xl p-5 border border-emerald-200/80 shadow-xs bg-gradient-to-br from-white to-emerald-50/30">
                <p class="text-[11px] font-bold uppercase tracking-wider text-emerald-700 font-mono">Cleared in Current A/C</p>
                <p class="text-xl font-bold font-mono text-emerald-700 mt-1.5">₹{{ number_format(($settlementStats['settled_volume'] ?? 0) / 100, 2) }}</p>
                <p class="text-[11px] text-emerald-600 mt-1">Funds available in bank for payouts</p>
            </div>

            <!-- Pending Clearing -->
            <div class="bg-white rounded-2xl p-5 border border-amber-200/80 shadow-xs bg-gradient-to-br from-white to-amber-50/30">
                <p class="text-[11px] font-bold uppercase tracking-wider text-amber-700 font-mono">Clearing Buffer (T+1)</p>
                <p class="text-xl font-bold font-mono text-amber-700 mt-1.5">₹{{ number_format(($settlementStats['pending_settlement'] ?? 0) / 100, 2) }}</p>
                <p class="text-[11px] text-amber-600 mt-1">Arriving next bank batch (~11 AM)</p>
            </div>

            <!-- Vendor Disbursed (95%) -->
            <div class="bg-white rounded-2xl p-5 border border-blue-200/80 shadow-xs bg-gradient-to-br from-white to-blue-50/30">
                <p class="text-[11px] font-bold uppercase tracking-wider text-blue-700 font-mono">Vendor Payouts (95%)</p>
                <p class="text-xl font-bold font-mono text-blue-700 mt-1.5">₹{{ number_format(($settlementStats['vendor_disbursed'] ?? 0) / 100, 2) }}</p>
                <p class="text-[11px] text-blue-600 mt-1">Disbursed to Vendor UPI accounts</p>
            </div>

            <!-- Platform Fee (5%) -->
            <div class="bg-white rounded-2xl p-5 border border-purple-200/80 shadow-xs bg-gradient-to-br from-white to-purple-50/30">
                <p class="text-[11px] font-bold uppercase tracking-wider text-purple-700 font-mono">Platform Net Profit (5%)</p>
                <p class="text-xl font-bold font-mono text-purple-700 mt-1.5">₹{{ number_format(($settlementStats['platform_retained'] ?? 0) / 100, 2) }}</p>
                <p class="text-[11px] text-purple-600 mt-1">Pure retained net commission</p>
            </div>
        </div>

        <!-- FILTER TABS & SEARCH BAR -->
        <div class="bg-white rounded-3xl p-5 border border-slate-200/80 shadow-xs space-y-4">
            
            <!-- Quick Category Tabs -->
            <div class="flex items-center gap-2 overflow-x-auto pb-1 text-xs">
                <a href="{{ route('admin.orders') }}" 
                   class="px-3.5 py-2 rounded-xl font-bold whitespace-nowrap transition-all {{ !request()->hasAny(['settlement', 'payout', 'status']) ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                    All Orders ({{ $counts['all'] }})
                </a>
                <a href="{{ route('admin.orders', ['settlement' => 'pending']) }}" 
                   class="px-3.5 py-2 rounded-xl font-bold whitespace-nowrap transition-all {{ request('settlement') === 'pending' ? 'bg-amber-600 text-white' : 'bg-amber-50 text-amber-800 hover:bg-amber-100' }}">
                    ⏳ Clearing Buffer ({{ $counts['clearing_buffer'] }})
                </a>
                <a href="{{ route('admin.orders', ['settlement' => 'cleared', 'payout' => 'ready']) }}" 
                   class="px-3.5 py-2 rounded-xl font-bold whitespace-nowrap transition-all {{ request('settlement') === 'cleared' && request('payout') === 'ready' ? 'bg-emerald-600 text-white' : 'bg-emerald-50 text-emerald-800 hover:bg-emerald-100' }}">
                    🟢 Ready to Disburse ({{ $counts['ready_for_payout'] }})
                </a>
                <a href="{{ route('admin.orders', ['payout' => 'paid']) }}" 
                   class="px-3.5 py-2 rounded-xl font-bold whitespace-nowrap transition-all {{ request('payout') === 'paid' ? 'bg-blue-600 text-white' : 'bg-blue-50 text-blue-800 hover:bg-blue-100' }}">
                    ✓ Paid to Vendor ({{ $counts['payout_completed'] }})
                </a>
                <a href="{{ route('admin.orders', ['status' => 'processing']) }}" 
                   class="px-3.5 py-2 rounded-xl font-bold whitespace-nowrap transition-all {{ request('status') === 'processing' ? 'bg-indigo-600 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                    📦 Packing ({{ $counts['unfulfilled'] }})
                </a>
                <a href="{{ route('admin.orders', ['status' => 'shipped']) }}" 
                   class="px-3.5 py-2 rounded-xl font-bold whitespace-nowrap transition-all {{ request('status') === 'shipped' ? 'bg-purple-600 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                    🚚 In Transit ({{ $counts['in_transit'] }})
                </a>
                <a href="{{ route('admin.orders', ['status' => 'delivered']) }}" 
                   class="px-3.5 py-2 rounded-xl font-bold whitespace-nowrap transition-all {{ request('status') === 'delivered' ? 'bg-emerald-700 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                    ✅ Delivered ({{ $counts['delivered'] }})
                </a>
            </div>

            <!-- Search & Filters Form -->
            <form action="{{ route('admin.orders') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-3 pt-2 border-t border-slate-100">
                <!-- Search Input (2 cols) -->
                <div class="lg:col-span-2 relative">
                    <input type="text" name="search" value="{{ request('search') }}" 
                           placeholder="Search Order #, Customer, Phone, UTR, AWB..." 
                           class="w-full text-xs rounded-xl border-slate-200 bg-slate-50/50 py-2.5 pl-3.5 pr-8 focus:bg-white focus:ring-amber-500 focus:border-amber-500">
                    @if(request('search'))
                        <a href="{{ route('admin.orders') }}" class="absolute right-2.5 top-2.5 text-slate-400 hover:text-slate-600 text-xs font-bold">✕</a>
                    @endif
                </div>

                <!-- Vendor Dropdown -->
                <div>
                    <select name="vendor_id" class="w-full text-xs rounded-xl border-slate-200 bg-slate-50/50 py-2.5 px-3 focus:bg-white focus:ring-amber-500 focus:border-amber-500">
                        <option value="">All Boutiques</option>
                        @foreach($vendorsList as $v)
                            <option value="{{ $v->id }}" {{ request('vendor_id') == $v->id ? 'selected' : '' }}>{{ $v->shop_name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Settlement Status -->
                <div>
                    <select name="settlement" class="w-full text-xs rounded-xl border-slate-200 bg-slate-50/50 py-2.5 px-3 focus:bg-white focus:ring-amber-500 focus:border-amber-500">
                        <option value="">Settlement: All</option>
                        <option value="pending" {{ request('settlement') === 'pending' ? 'selected' : '' }}>⏳ Clearing (T+1)</option>
                        <option value="cleared" {{ request('settlement') === 'cleared' ? 'selected' : '' }}>🟢 Cleared in Bank</option>
                    </select>
                </div>

                <!-- Fulfillment Status -->
                <div>
                    <select name="status" class="w-full text-xs rounded-xl border-slate-200 bg-slate-50/50 py-2.5 px-3 focus:bg-white focus:ring-amber-500 focus:border-amber-500">
                        <option value="">Fulfillment: All</option>
                        <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="processing" {{ request('status') === 'processing' ? 'selected' : '' }}>Processing</option>
                        <option value="shipped" {{ request('status') === 'shipped' ? 'selected' : '' }}>Shipped</option>
                        <option value="delivered" {{ request('status') === 'delivered' ? 'selected' : '' }}>Delivered</option>
                        <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                    </select>
                </div>

                <!-- Action Buttons -->
                <div class="flex items-center gap-2">
                    <button type="submit" class="flex-1 py-2.5 px-4 bg-slate-900 hover:bg-black text-white text-xs font-bold rounded-xl shadow-xs transition-all">
                        Filter
                    </button>
                    @if(request()->hasAny(['search', 'vendor_id', 'settlement', 'payout', 'status', 'date']))
                        <a href="{{ route('admin.orders') }}" class="py-2.5 px-3 bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-bold rounded-xl transition-all" title="Clear all filters">
                            Reset
                        </a>
                    @endif
                </div>
            </form>
        </div>

        <!-- Orders Table -->
        <div class="bg-white rounded-3xl border border-slate-200/80 shadow-xs overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-100">
                    <thead class="bg-slate-50/70">
                        <tr>
                            <th class="px-5 py-3.5 text-left text-[11px] font-bold text-slate-500 uppercase tracking-wider">Order ID</th>
                            <th class="px-5 py-3.5 text-left text-[11px] font-bold text-slate-500 uppercase tracking-wider">Storefront Boutique</th>
                            <th class="px-5 py-3.5 text-left text-[11px] font-bold text-slate-500 uppercase tracking-wider">Customer</th>
                            <th class="px-5 py-3.5 text-left text-[11px] font-bold text-slate-500 uppercase tracking-wider">Total / Split</th>
                            <th class="px-5 py-3.5 text-left text-[11px] font-bold text-slate-500 uppercase tracking-wider">Bank Settlement (T+1)</th>
                            <th class="px-5 py-3.5 text-left text-[11px] font-bold text-slate-500 uppercase tracking-wider">Vendor UPI Payout</th>
                            <th class="px-5 py-3.5 text-left text-[11px] font-bold text-slate-500 uppercase tracking-wider">Fulfillment</th>
                            <th class="px-5 py-3.5 text-right text-[11px] font-bold text-slate-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-slate-100">
                        @forelse($orders as $order)
                            @php
                                $isSettled = $order->settlement_status === 'cleared';
                                $isPayoutDone = $order->vendor_payout_status === 'paid';
                                $upiId = $order->vendor->settings->upi_id ?? null;
                            @endphp
                            <tr class="hover:bg-slate-50/60 transition-colors">
                                <!-- Order ID -->
                                <td class="px-5 py-4 whitespace-nowrap">
                                    <a href="{{ route('admin.orders.show', $order) }}" class="text-xs font-mono font-bold text-slate-900 hover:text-amber-600 hover:underline block">
                                        {{ $order->order_number }}
                                    </a>
                                    <div class="text-[10px] text-slate-400 font-mono mt-0.5">{{ $order->created_at->format('d M, H:i') }}</div>
                                    @if($order->idempotency_key)
                                        <div class="text-[9px] text-slate-400 font-mono truncate max-w-[130px]">Ref: {{ $order->idempotency_key }}</div>
                                    @endif
                                </td>

                                <!-- Storefront Boutique & UPI -->
                                <td class="px-5 py-4 whitespace-nowrap">
                                    @if($order->vendor)
                                        <div class="text-xs font-bold text-slate-900">{{ $order->vendor->shop_name }}</div>
                                        @if($upiId)
                                            <div class="inline-flex items-center gap-1 text-[10px] font-mono font-bold text-emerald-700 bg-emerald-50 px-1.5 py-0.5 rounded mt-0.5 border border-emerald-100">
                                                <span>⚡</span> {{ $upiId }}
                                            </div>
                                        @else
                                            <div class="text-[10px] text-rose-500 italic mt-0.5">⚠️ No UPI set</div>
                                        @endif
                                    @else
                                        <span class="text-xs text-slate-400">Direct Marketplace</span>
                                    @endif
                                </td>

                                <!-- Customer -->
                                <td class="px-5 py-4 whitespace-nowrap">
                                    <div class="text-xs font-semibold text-slate-900">
                                        {{ $order->user->first_name ?? '' }} {{ $order->user->last_name ?? '' }}
                                    </div>
                                    <div class="text-[10px] text-slate-400">{{ $order->address_snapshot['city'] ?? 'India' }} • {{ $order->user->phone ?? ($order->address_snapshot['phone'] ?? '') }}</div>
                                </td>

                                <!-- Total / Split -->
                                <td class="px-5 py-4 whitespace-nowrap">
                                    <div class="text-xs font-extrabold font-mono text-slate-900">₹{{ number_format($order->total_amount / 100, 2) }}</div>
                                    <div class="text-[10px] text-slate-500 font-mono mt-0.5">
                                        Vendor: <span class="text-blue-700 font-bold">₹{{ number_format(($order->vendor_payout_amount ?: (($order->subtotal - $order->discount_amount) * 0.95)) / 100, 2) }}</span>
                                        <span class="text-slate-300">|</span> Fee: ₹{{ number_format((($order->subtotal - $order->discount_amount) * 0.05) / 100, 2) }}
                                    </div>
                                </td>

                                <!-- Bank Settlement Status -->
                                <td class="px-5 py-4 whitespace-nowrap">
                                    @if($isSettled)
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold bg-emerald-100 text-emerald-800">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-600"></span>
                                            Cleared in Bank
                                        </span>
                                        @if($order->settled_at)
                                            <div class="text-[10px] text-slate-400 font-mono mt-1">{{ $order->settled_at->format('d M, h:i A') }}</div>
                                        @endif
                                    @elseif($order->settlement_status === 'pending')
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold bg-amber-100 text-amber-800 animate-pulse">
                                            <span class="w-1.5 h-1.5 rounded-full bg-amber-600"></span>
                                            Clearing (T+1)
                                        </span>
                                        @if($order->settlement_expected_at)
                                            <div class="text-[10px] text-amber-700 font-mono mt-1">ETA: {{ $order->settlement_expected_at->format('d M, 11:00 A') }}</div>
                                        @endif
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-medium bg-slate-100 text-slate-600">
                                            {{ ucfirst($order->settlement_status ?? 'Awaiting') }}
                                        </span>
                                    @endif
                                </td>

                                <!-- Vendor UPI Payout -->
                                <td class="px-5 py-4 whitespace-nowrap">
                                    @if($isPayoutDone)
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-blue-100 text-blue-800">
                                            ✓ Paid to UPI
                                        </span>
                                        @if($order->vendor_payout_utr)
                                            <div class="text-[10px] font-mono text-slate-600 mt-1 truncate max-w-[140px]" title="{{ $order->vendor_payout_utr }}">
                                                UTR: {{ $order->vendor_payout_utr }}
                                            </div>
                                        @endif
                                    @elseif($isSettled)
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                            Ready for Accept
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-medium bg-slate-100 text-slate-500">
                                            Settlement Locked
                                        </span>
                                    @endif
                                </td>

                                <!-- Fulfillment Status -->
                                <td class="px-5 py-4 whitespace-nowrap">
                                    @php
                                        $badgeColors = [
                                            'pending' => 'bg-amber-100 text-amber-800',
                                            'processing' => 'bg-blue-100 text-blue-800',
                                            'shipped' => 'bg-purple-100 text-purple-800',
                                            'delivered' => 'bg-emerald-100 text-emerald-800',
                                            'cancelled' => 'bg-rose-100 text-rose-800',
                                        ];
                                        $color = $badgeColors[strtolower($order->status)] ?? 'bg-slate-100 text-slate-800';
                                    @endphp
                                    <span class="px-2.5 py-1 inline-flex text-[10px] font-bold rounded-full {{ $color }} uppercase tracking-wider">
                                        {{ ucfirst($order->status) }}
                                    </span>
                                    @if($order->shipment && $order->shipment->awb_code)
                                        <div class="text-[10px] font-mono text-slate-400 mt-1">{{ $order->shipment->courier_name ?? 'Courier' }} · {{ $order->shipment->awb_code }}</div>
                                    @endif
                                </td>

                                <!-- Actions -->
                                <td class="px-5 py-4 whitespace-nowrap text-right">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <a href="{{ route('admin.orders.show', $order) }}" class="px-2.5 py-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-800 text-[11px] font-bold transition-all inline-flex items-center gap-1">
                                            <span>Audit</span> &rarr;
                                        </a>

                                        @if(!$isSettled && $order->payment_status === 'paid')
                                            <!-- 1-Click Mark Settled in Current Account -->
                                            <form action="{{ route('admin.orders.mark_settled', $order) }}" method="POST" onsubmit="return confirm('Confirm settlement clearance? This flags the customer payment as received in your Current Account.');">
                                                @csrf
                                                <button type="submit" title="Force mark settled in Current Account" class="px-2 py-1.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-[11px] font-bold transition-all inline-flex items-center">
                                                    <span>⚡</span>
                                                </button>
                                            </form>
                                        @endif

                                        @if($isSettled && !$isPayoutDone && $order->status !== 'cancelled')
                                            <!-- Master Admin Force Disburse Payout -->
                                            <form action="{{ route('admin.orders.disburse_payout', $order) }}" method="POST" onsubmit="return confirm('Disburse 95% net payout to vendor now?');">
                                                @csrf
                                                <button type="submit" title="Force disburse UPI payout" class="px-2 py-1.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-[11px] font-bold transition-all inline-flex items-center">
                                                    <span>💸</span>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-6 py-12 text-center text-xs text-slate-400 italic">No orders found matching your filters.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if(method_exists($orders, 'links'))
                <div class="px-6 py-4 border-t border-slate-100 bg-slate-50/50">
                    {{ $orders->links() }}
                </div>
            @endif
        </div>
    </div>
</x-layouts.admin>
