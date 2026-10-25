<x-layouts.admin title="Audit Order #{{ $order->order_number }}">
    <div class="space-y-6">

        <!-- Top Breadcrumbs & Quick Back Nav -->
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-2 text-xs text-slate-500 font-medium">
                <a href="{{ route('admin.orders') }}" class="hover:text-slate-900 transition-colors flex items-center gap-1">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                    <span>Back to All Orders</span>
                </a>
                <span>/</span>
                <span class="text-slate-800 font-mono font-bold">#{{ $order->order_number }}</span>
            </div>

            <div class="flex items-center gap-2">
                <a href="{{ route('admin.orders') }}" class="px-3 py-1.5 rounded-xl border border-slate-200 text-xs font-semibold text-slate-700 hover:bg-slate-100 transition-colors">
                    <span>View Orders List</span>
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

        @if(session('error'))
            <div class="bg-rose-50 border border-rose-200 text-rose-800 rounded-2xl p-4 text-xs sm:text-sm flex items-center gap-3 shadow-xs">
                <svg class="w-5 h-5 text-rose-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        <!-- Order Header Card -->
        <div class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-200/80 shadow-xs flex flex-col lg:flex-row justify-between items-start lg:items-center gap-6">
            <div>
                <div class="flex flex-wrap items-center gap-2 mb-2">
                    <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-mono font-bold bg-slate-900 text-white">
                        ORDER #{{ $order->order_number }}
                    </span>

                    <!-- Settlement Badge -->
                    @if($order->settlement_status === 'cleared')
                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-bold bg-emerald-50 text-emerald-800 border border-emerald-300">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                            <span>Bank Settlement: Cleared (In Current A/C)</span>
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-bold bg-amber-50 text-amber-800 border border-amber-300">
                            <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                            <span>T+1 Settlement Buffer (In Transit to Bank)</span>
                        </span>
                    @endif

                    <!-- Payout Badge -->
                    @if($order->vendor_payout_status === 'paid')
                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-bold bg-blue-50 text-blue-800 border border-blue-300">
                            <span>💸 Vendor Disbursed (₹{{ number_format($vendorNet / 100, 2) }})</span>
                        </span>
                    @elseif($order->settlement_status === 'cleared')
                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-bold bg-emerald-100 text-emerald-900 border border-emerald-300">
                            <span>⚡ Ready for UPI Payout</span>
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-bold bg-slate-100 text-slate-700 border border-slate-200">
                            <span>🔒 Payout Locked (Awaiting Bank Clearance)</span>
                        </span>
                    @endif

                    <!-- Fulfillment Badge -->
                    @php
                        $fulfillmentStyles = [
                            'pending' => 'bg-amber-100 text-amber-800 border-amber-200',
                            'processing' => 'bg-blue-100 text-blue-800 border-blue-200',
                            'ready_to_ship' => 'bg-indigo-100 text-indigo-800 border-indigo-200',
                            'shipped' => 'bg-purple-100 text-purple-800 border-purple-200',
                            'delivered' => 'bg-emerald-100 text-emerald-800 border-emerald-200',
                            'cancelled' => 'bg-rose-100 text-rose-800 border-rose-200',
                        ];
                        $fulfillBadge = $fulfillmentStyles[$order->status] ?? 'bg-slate-100 text-slate-800 border-slate-200';
                    @endphp
                    <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-bold border uppercase tracking-wider {{ $fulfillBadge }}">
                        {{ str_replace('_', ' ', $order->status) }}
                    </span>
                </div>

                <h2 class="text-2xl font-serif font-bold text-slate-900 tracking-tight">
                    Order Details &amp; Settlement Audit
                </h2>
                <p class="text-xs sm:text-sm text-slate-500 mt-1 flex flex-wrap items-center gap-2">
                    <span>Placed on {{ $order->created_at->format('M d, Y h:i A') }} ({{ $order->created_at->diffForHumans() }})</span>
                    <span>•</span>
                    <span>Origin Boutique: <strong class="text-slate-800">{{ $order->vendor->shop_name ?? 'Independent Boutique' }}</strong></span>
                </p>
            </div>

            <!-- Administrative Action Buttons -->
            <div class="flex flex-wrap items-center gap-2.5">
                @if($order->payment_status === 'paid' && $order->settlement_status === 'pending')
                    <form action="{{ route('admin.orders.mark_settled', $order) }}" method="POST" onsubmit="return confirm('Confirm receipt of ₹{{ number_format($total / 100, 2) }} in your master Current Bank Account? This will clear the settlement and unlock vendor payout.');">
                        @csrf
                        <button type="submit" class="px-4 py-2.5 rounded-2xl bg-emerald-600 hover:bg-emerald-700 text-xs font-bold text-white shadow-sm hover:shadow transition-all inline-flex items-center gap-1.5 cursor-pointer">
                            <span>⚡ Confirm Bank Settlement</span>
                        </button>
                    </form>
                @endif

                @if($order->settlement_status === 'cleared' && $order->vendor_payout_status !== 'paid' && $order->status !== 'cancelled')
                    <form action="{{ route('admin.orders.disburse_payout', $order) }}" method="POST" onsubmit="return confirm('Disburse ₹{{ number_format($vendorNet / 100, 2) }} directly to vendor UPI ID ({{ $order->vendor->settings->upi_id ?? 'Registered VPA' }})?');">
                        @csrf
                        <button type="submit" class="px-4 py-2.5 rounded-2xl bg-blue-600 hover:bg-blue-700 text-xs font-bold text-white shadow-sm hover:shadow transition-all inline-flex items-center gap-1.5 cursor-pointer">
                            <span>💸 Disburse UPI Payout (₹{{ number_format($vendorNet / 100, 2) }})</span>
                        </button>
                    </form>
                @endif

                <a href="#audit-trail" class="px-3.5 py-2.5 rounded-2xl bg-slate-100 hover:bg-slate-200 text-xs font-bold text-slate-700 transition-colors inline-flex items-center gap-1">
                    <span>📜 Audit Trail</span>
                </a>
            </div>
        </div>

        <!-- SECTION 1: 3-WAY FINANCIAL RECONCILIATION MATRIX -->
        <div>
            <div class="flex items-center justify-between mb-3 px-1">
                <div>
                    <h3 class="text-xs sm:text-sm font-bold uppercase tracking-wider text-slate-500 font-mono">1. Marketplace Financial Reconciliation (Truth Matrix)</h3>
                    <p class="text-xs text-slate-500">Mathematical breakdown of every single rupee in this transaction</p>
                </div>
                <span class="text-[11px] font-mono text-emerald-700 bg-emerald-50 border border-emerald-300 px-2.5 py-1 rounded-lg font-bold">
                    Gross GMV = Vendor Net (95%) + Platform Profit (5%) + Courier Pass-Through ✅
                </span>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                
                <!-- Card 1: Customer Inflow (PhonePe) -->
                <div class="bg-white p-5 rounded-3xl border border-slate-200/80 shadow-xs space-y-3">
                    <div class="flex items-center justify-between pb-2 border-b border-slate-100">
                        <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400 font-mono">Customer Collection</span>
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">PhonePe Gateway</span>
                    </div>
                    <div>
                        <p class="text-2xl font-extrabold text-slate-900 font-mono">₹{{ number_format($total / 100, 2) }}</p>
                        <p class="text-[11px] text-slate-500 mt-0.5">Total debited from customer</p>
                    </div>
                    <div class="text-xs text-slate-600 space-y-1.5 pt-2 border-t border-slate-100 font-mono">
                        <div class="flex justify-between">
                            <span>Item Subtotal:</span>
                            <span class="font-bold">₹{{ number_format($subtotal / 100, 2) }}</span>
                        </div>
                        @if($discount > 0)
                            <div class="flex justify-between text-emerald-700">
                                <span>Coupon Discount:</span>
                                <span>-₹{{ number_format($discount / 100, 2) }}</span>
                            </div>
                        @endif
                        <div class="flex justify-between text-slate-600">
                            <span>Shipping Charged:</span>
                            <span>+₹{{ number_format($shipping / 100, 2) }}</span>
                        </div>
                    </div>
                    <div class="pt-2 border-t border-slate-100 text-[10px] text-slate-400 font-mono truncate">
                        Gateway Ref: {{ $order->payment_reference ?? $order->idempotency_key ?? 'N/A' }}
                    </div>
                </div>

                <!-- Card 2: Platform Retained Margin (Net 5%) -->
                <div class="bg-white p-5 rounded-3xl border border-purple-200/90 shadow-xs space-y-3 bg-gradient-to-br from-white to-purple-50/20">
                    <div class="flex items-center justify-between pb-2 border-b border-purple-100">
                        <span class="text-[11px] font-bold uppercase tracking-wider text-purple-700 font-mono">Platform Commission</span>
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-purple-100 text-purple-800">5% Net Margin</span>
                    </div>
                    <div>
                        <p class="text-2xl font-extrabold text-purple-700 font-mono">₹{{ number_format($platformFee / 100, 2) }}</p>
                        <p class="text-[11px] text-purple-600 mt-0.5">Retained platform revenue</p>
                    </div>
                    <div class="text-xs text-purple-900 space-y-1.5 pt-2 border-t border-purple-100 font-mono">
                        <div class="flex justify-between">
                            <span>Base Product Value:</span>
                            <span class="font-bold">₹{{ number_format(max(0, $subtotal - $discount) / 100, 2) }}</span>
                        </div>
                        <div class="flex justify-between text-purple-600">
                            <span>Effective Rate:</span>
                            <span>5.0% flat</span>
                        </div>
                        <div class="flex justify-between text-purple-600">
                            <span>Margin Status:</span>
                            <span class="font-bold">{{ $order->settlement_status === 'cleared' ? 'Secured in A/C' : 'Pending Buffer' }}</span>
                        </div>
                    </div>
                    <div class="pt-2 border-t border-purple-100 text-[10px] text-purple-500 font-mono">
                        Direct Profit Margin
                    </div>
                </div>

                <!-- Card 3: Vendor Net Payout (95%) -->
                <div class="bg-white p-5 rounded-3xl border border-blue-200/90 shadow-xs space-y-3 bg-gradient-to-br from-white to-blue-50/20">
                    <div class="flex items-center justify-between pb-2 border-b border-blue-100">
                        <span class="text-[11px] font-bold uppercase tracking-wider text-blue-700 font-mono">Vendor Payout Share</span>
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-blue-100 text-blue-800">95% Product Value</span>
                    </div>
                    <div>
                        <p class="text-2xl font-extrabold text-blue-700 font-mono">₹{{ number_format($vendorNet / 100, 2) }}</p>
                        <p class="text-[11px] text-blue-600 mt-0.5">Owed to {{ $order->vendor->shop_name ?? 'Vendor' }}</p>
                    </div>
                    <div class="text-xs text-blue-900 space-y-1.5 pt-2 border-t border-blue-100 font-mono">
                        <div class="flex justify-between">
                            <span>Payout Status:</span>
                            <span class="font-bold uppercase">{{ $order->vendor_payout_status }}</span>
                        </div>
                        <div class="flex justify-between text-blue-600 truncate">
                            <span>Vendor UPI:</span>
                            <span class="font-bold truncate max-w-[120px]">{{ $order->vendor->settings->upi_id ?? 'N/A' }}</span>
                        </div>
                        <div class="flex justify-between text-blue-600">
                            <span>UTR / Ref:</span>
                            <span class="font-bold">{{ $order->vendor_payout_utr ? substr($order->vendor_payout_utr, 0, 10) . '...' : 'Pending' }}</span>
                        </div>
                    </div>
                    <div class="pt-2 border-t border-blue-100 text-[10px] text-blue-500 font-mono">
                        Instant Zero-Float Disbursal
                    </div>
                </div>

                <!-- Card 4: Customer Shipping Pool (Courier Pass-Through) -->
                <div class="bg-white p-5 rounded-3xl border border-slate-200/80 shadow-xs space-y-3">
                    <div class="flex items-center justify-between pb-2 border-b border-slate-100">
                        <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500 font-mono">Shipping Pass-Through</span>
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-700">100% Logistics</span>
                    </div>
                    <div>
                        <p class="text-2xl font-extrabold text-slate-800 font-mono">₹{{ number_format($shipping / 100, 2) }}</p>
                        <p class="text-[11px] text-slate-500 mt-0.5">Courier delivery pool</p>
                    </div>
                    <div class="text-xs text-slate-600 space-y-1.5 pt-2 border-t border-slate-100 font-mono">
                        <div class="flex justify-between">
                            <span>Logistics Partner:</span>
                            <span class="font-bold">{{ $order->courier_name ?? $order->shipment->courier_name ?? 'Shiprocket' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span>AWB Tracking:</span>
                            <span class="font-bold truncate max-w-[120px]">{{ $order->tracking_number ?? $order->shipment->awb_code ?? 'Pending' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span>Label Cost:</span>
                            <span>Pass-through</span>
                        </div>
                    </div>
                    <div class="pt-2 border-t border-slate-100 text-[10px] text-slate-400 font-mono">
                        Zero Commission on Shipping
                    </div>
                </div>

            </div>
        </div>

        <!-- SECTION 2: TWO-STAGE SETTLEMENT & BANKING PIPELINE -->
        <div class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-200/80 shadow-xs">
            <h3 class="text-xs sm:text-sm font-bold uppercase tracking-wider text-slate-500 font-mono mb-4">
                2. Two-Stage Settlement Pipeline Telemetry
            </h3>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 relative">
                
                <!-- Stage 1: PhonePe Gateway Inflow -->
                <div class="p-4.5 rounded-2xl border {{ $order->payment_status === 'paid' ? 'bg-emerald-50/50 border-emerald-200' : 'bg-slate-50 border-slate-200' }}">
                    <div class="flex items-center gap-2 mb-2">
                        <span class="w-6 h-6 rounded-full flex items-center justify-center text-xs font-bold {{ $order->payment_status === 'paid' ? 'bg-emerald-600 text-white' : 'bg-slate-300 text-slate-700' }}">1</span>
                        <h4 class="text-xs font-bold uppercase tracking-wider text-slate-800 font-mono">Stage 1: Customer Inflow</h4>
                    </div>
                    <p class="text-sm font-bold text-slate-900 mt-1">₹{{ number_format($total / 100, 2) }} via PhonePe</p>
                    <div class="mt-3 text-xs space-y-1 text-slate-600 font-mono">
                        <p>Status: <strong class="{{ $order->payment_status === 'paid' ? 'text-emerald-700' : 'text-slate-500' }} uppercase">{{ $order->payment_status }}</strong></p>
                        <p>Gateway Ref: <span class="text-slate-500">{{ $order->payment_reference ?? $order->idempotency_key ?? 'None' }}</span></p>
                        <p>Captured: {{ $order->created_at->format('d M, Y h:i A') }}</p>
                    </div>
                </div>

                <!-- Stage 2: Bank Current Account Clearance -->
                <div class="p-4.5 rounded-2xl border {{ $order->settlement_status === 'cleared' ? 'bg-emerald-50/50 border-emerald-200' : 'bg-amber-50/50 border-amber-200' }}">
                    <div class="flex items-center gap-2 mb-2">
                        <span class="w-6 h-6 rounded-full flex items-center justify-center text-xs font-bold {{ $order->settlement_status === 'cleared' ? 'bg-emerald-600 text-white' : 'bg-amber-500 text-white' }}">2</span>
                        <h4 class="text-xs font-bold uppercase tracking-wider text-slate-800 font-mono">Stage 2: Bank Settlement</h4>
                    </div>
                    <p class="text-sm font-bold {{ $order->settlement_status === 'cleared' ? 'text-emerald-700' : 'text-amber-700' }} mt-1">
                        {{ $order->settlement_status === 'cleared' ? 'Cleared into Current Account' : 'T+1 Settlement In Buffer' }}
                    </p>
                    <div class="mt-3 text-xs space-y-1 text-slate-600 font-mono">
                        <p>Buffer Target: <strong class="text-slate-800">{{ $order->settlement_expected_at ? $order->settlement_expected_at->format('d M, Y ~h:i A') : 'Next Morning 11:00 AM' }}</strong></p>
                        <p>Cleared At: <span class="text-slate-500">{{ $order->settled_at ? $order->settled_at->format('d M, Y h:i A') : 'Awaiting clearance' }}</span></p>
                        <p>Batch ID: <span class="text-slate-500">{{ $order->settlement_batch_ref ?? 'Pending' }}</span></p>
                    </div>
                </div>

                <!-- Stage 3: Vendor Instant UPI Payout -->
                <div class="p-4.5 rounded-2xl border {{ $order->vendor_payout_status === 'paid' ? 'bg-blue-50/50 border-blue-200' : ($order->settlement_status === 'cleared' ? 'bg-emerald-50/30 border-emerald-200' : 'bg-slate-50 border-slate-200') }}">
                    <div class="flex items-center gap-2 mb-2">
                        <span class="w-6 h-6 rounded-full flex items-center justify-center text-xs font-bold {{ $order->vendor_payout_status === 'paid' ? 'bg-blue-600 text-white' : 'bg-slate-300 text-slate-700' }}">3</span>
                        <h4 class="text-xs font-bold uppercase tracking-wider text-slate-800 font-mono">Stage 3: Vendor 95% UPI</h4>
                    </div>
                    <p class="text-sm font-bold text-slate-900 mt-1">₹{{ number_format($vendorNet / 100, 2) }} to Vendor VPA</p>
                    <div class="mt-3 text-xs space-y-1 text-slate-600 font-mono">
                        <p>Target VPA: <strong class="text-slate-800">{{ $order->vendor->settings->upi_id ?? 'Not configured in settings' }}</strong></p>
                        <p>Bank UTR: <span class="text-slate-500">{{ $order->vendor_payout_utr ?? 'Pending disbursal' }}</span></p>
                        <p>Disbursed At: <span class="text-slate-500">{{ $order->vendor_payout_at ? $order->vendor_payout_at->format('d M, Y h:i A') : 'Awaiting trigger' }}</span></p>
                    </div>
                </div>

            </div>
        </div>

        <!-- SECTION 3: ORDER ITEMS & CUSTOMER / LOGISTICS SPLIT -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            
            <!-- Left 2 Cols: Order Items & Atelier Breakdown -->
            <div class="lg:col-span-2 space-y-6">
                
                <div class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-200/80 shadow-xs">
                    <div class="flex items-center justify-between pb-4 border-b border-slate-100 mb-6">
                        <div>
                            <h3 class="text-sm font-bold uppercase tracking-wider text-slate-900 font-mono">Consignment Items ({{ $order->items->count() }})</h3>
                            <p class="text-xs text-slate-500">Physical garments in this marketplace order</p>
                        </div>
                        <span class="text-xs font-mono font-bold text-slate-600 bg-slate-100 px-3 py-1 rounded-xl">
                            {{ $order->item_count }} pcs total
                        </span>
                    </div>

                    <div class="divide-y divide-slate-100">
                        @foreach($order->items as $item)
                            <div class="py-4 flex gap-4 items-start first:pt-0 last:pb-0">
                                <div class="w-16 h-20 bg-slate-100 rounded-2xl overflow-hidden flex-shrink-0 border border-slate-200">
                                    @if($item->product)
                                        <img src="{{ $item->product->primary_image_url }}" alt="{{ $item->product->title }}" class="w-full h-full object-cover" onerror="this.onerror=null; this.src='{{ asset('images/product-placeholder.svg') }}';">
                                    @else
                                        <div class="w-full h-full flex items-center justify-center text-slate-400 text-xl font-serif">🛍️</div>
                                    @endif
                                </div>

                                <div class="flex-1 min-w-0">
                                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-1">
                                        <h4 class="text-sm font-bold text-slate-900 truncate">
                                            {{ $item->product->title ?? $item->snapshot['title'] ?? 'Marketplace Item' }}
                                        </h4>
                                        <span class="text-sm font-mono font-bold text-slate-900">
                                            ₹{{ number_format($item->total_price / 100, 2) }}
                                        </span>
                                    </div>

                                    <div class="flex flex-wrap items-center gap-2 mt-1.5 text-xs text-slate-500 font-mono">
                                        <span>SKU: {{ $item->variant->sku ?? $item->snapshot['variant_sku'] ?? $item->sku ?? 'N/A' }}</span>
                                        @if($item->variant && ($item->variant->size || $item->variant->color))
                                            <span>•</span>
                                            <span>Variant: {{ $item->variant->size }} {{ $item->variant->color }}</span>
                                        @endif
                                        <span>•</span>
                                        <span>Qty: {{ $item->quantity }} × ₹{{ number_format($item->unit_price / 100, 2) }}</span>
                                    </div>

                                    @if($item->product)
                                        <div class="mt-2 flex items-center gap-2">
                                            <a href="{{ route('product.show', $item->product->slug) }}" target="_blank" class="text-[11px] font-semibold text-amber-600 hover:underline inline-flex items-center gap-1">
                                                <span>View in Catalog ↗</span>
                                            </a>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Boutique Origin Profile -->
                <div class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-200/80 shadow-xs">
                    <h3 class="text-sm font-bold uppercase tracking-wider text-slate-900 font-mono mb-4">Origin Boutique Details</h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                        <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200/80">
                            <span class="text-slate-400 block font-mono text-[10px] uppercase">Shop Name</span>
                            <span class="text-sm font-bold text-slate-900 block mt-0.5">{{ $order->vendor->shop_name ?? 'Independent Merchant' }}</span>
                            <span class="text-[11px] font-mono text-slate-500">/s/{{ $order->vendor->slug ?? 'store' }}</span>
                        </div>
                        <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200/80">
                            <span class="text-slate-400 block font-mono text-[10px] uppercase">Merchant Contact</span>
                            <span class="text-sm font-bold text-slate-900 block mt-0.5">{{ $order->vendor->contact_email ?? $order->vendor->user->email ?? 'N/A' }}</span>
                            <span class="text-[11px] font-mono text-slate-500">Phone: {{ $order->vendor->contact_phone ?? $order->vendor->user->phone ?? 'N/A' }}</span>
                        </div>
                        <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200/80">
                            <span class="text-slate-400 block font-mono text-[10px] uppercase">Registered UPI ID (VPA)</span>
                            <span class="text-sm font-mono font-bold text-blue-700 block mt-0.5">{{ $order->vendor->settings->upi_id ?? 'Not configured in settings' }}</span>
                            <span class="text-[10px] text-slate-400">Target account for 95% payouts</span>
                        </div>
                        <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200/80">
                            <span class="text-slate-400 block font-mono text-[10px] uppercase">Bank Account (NEFT/RTGS)</span>
                            <span class="text-xs font-mono font-bold text-slate-800 block mt-0.5">
                                A/C: {{ $order->vendor->settings->bank_account_number ?? 'N/A' }}
                            </span>
                            <span class="text-[11px] font-mono text-slate-500">IFSC: {{ $order->vendor->settings->bank_ifsc ?? 'N/A' }}</span>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Right 1 Col: Customer Details & Logistics Tracking -->
            <div class="space-y-6">
                
                <!-- Customer Profile -->
                <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-xs">
                    <h3 class="text-sm font-bold uppercase tracking-wider text-slate-900 font-mono mb-4">Customer Details</h3>
                    
                    <div class="space-y-4 text-xs">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-2xl bg-slate-100 text-slate-800 flex items-center justify-center font-bold text-sm font-mono flex-shrink-0">
                                {{ substr($order->user->first_name ?? 'C', 0, 1) }}{{ substr($order->user->last_name ?? 'U', 0, 1) }}
                            </div>
                            <div class="overflow-hidden">
                                <p class="text-sm font-bold text-slate-900 truncate">
                                    {{ $order->user->first_name ?? 'Guest' }} {{ $order->user->last_name ?? '' }}
                                </p>
                                <p class="text-slate-500 font-mono text-[11px] truncate">
                                    {{ $order->user->email ?? 'No email' }}
                                </p>
                            </div>
                        </div>

                        <div class="pt-3 border-t border-slate-100">
                            <span class="text-[10px] font-mono uppercase text-slate-400 font-bold block mb-1">Shipping Destination</span>
                            <address class="not-italic text-slate-700 leading-relaxed font-sans text-xs bg-slate-50 p-3 rounded-2xl border border-slate-200">
                                @if(!empty($order->address_snapshot))
                                    <strong>{{ $order->address_snapshot['first_name'] ?? $order->user->first_name ?? '' }} {{ $order->address_snapshot['last_name'] ?? $order->user->last_name ?? '' }}</strong><br>
                                    {{ $order->address_snapshot['street_address'] ?? $order->address_snapshot['line_1'] ?? 'Address on file' }}<br>
                                    @if(!empty($order->address_snapshot['address_line_2']))
                                        {{ $order->address_snapshot['address_line_2'] }}<br>
                                    @endif
                                    {{ $order->address_snapshot['city'] ?? '' }}, {{ $order->address_snapshot['state'] ?? '' }} - <strong>{{ $order->address_snapshot['postal_code'] ?? $order->address_snapshot['pincode'] ?? '' }}</strong><br>
                                    <span class="font-mono text-slate-500 mt-1 block">Phone: {{ $order->address_snapshot['phone'] ?? $order->user->phone ?? 'N/A' }}</span>
                                @else
                                    <span class="text-slate-400 italic">No address snapshot recorded for this order.</span>
                                @endif
                            </address>
                        </div>
                    </div>
                </div>

                <!-- Logistics & Shipping Intel -->
                <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-xs">
                    <h3 class="text-sm font-bold uppercase tracking-wider text-slate-900 font-mono mb-4">Logistics &amp; Courier Tracking</h3>
                    
                    <div class="space-y-3 text-xs">
                        <div class="flex justify-between items-center py-1.5 border-b border-slate-100">
                            <span class="text-slate-500">Courier Partner:</span>
                            <span class="font-bold text-slate-900">{{ $order->courier_name ?? $order->shipment->courier_name ?? 'Shiprocket' }}</span>
                        </div>
                        <div class="flex justify-between items-center py-1.5 border-b border-slate-100 font-mono">
                            <span class="text-slate-500 font-sans">AWB Code:</span>
                            <span class="font-bold text-emerald-700 select-all">{{ $order->tracking_number ?? $order->shipment->awb_code ?? 'Not Generated' }}</span>
                        </div>
                        <div class="flex justify-between items-center py-1.5 border-b border-slate-100">
                            <span class="text-slate-500">Fulfillment Status:</span>
                            <span class="font-bold uppercase text-[10px] px-2 py-0.5 rounded-full {{ $fulfillBadge }}">
                                {{ str_replace('_', ' ', $order->status) }}
                            </span>
                        </div>

                        @if($order->tracking_url || ($order->shipment && $order->shipment->tracking_url))
                            <div class="pt-2">
                                <a href="{{ $order->tracking_url ?? $order->shipment->tracking_url }}" target="_blank" class="w-full py-2.5 px-3 bg-slate-900 hover:bg-black text-white text-xs font-bold rounded-xl inline-flex items-center justify-center gap-1.5 transition-colors">
                                    <span>Live Courier Tracking ↗</span>
                                </a>
                            </div>
                        @endif

                        @if($order->shipment && $order->shipment->label_url)
                            <div class="pt-1">
                                <a href="{{ $order->shipment->label_url }}" target="_blank" class="w-full py-2 px-3 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold rounded-xl inline-flex items-center justify-center gap-1.5 transition-colors border border-slate-200">
                                    <span>🏷️ Download Shipping Label</span>
                                </a>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Add Internal Audit Note Card -->
                <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-xs">
                    <h3 class="text-sm font-bold uppercase tracking-wider text-slate-900 font-mono mb-2">Record Audit Note</h3>
                    <p class="text-xs text-slate-500 mb-3">Appends an indelible record to this order's compliance log.</p>

                    <form action="{{ route('admin.orders.add_note', $order) }}" method="POST" class="space-y-3">
                        @csrf
                        <div>
                            <textarea name="note" rows="3" required placeholder="e.g. Bank settlement confirmed against HDFC statement ref #82711..." class="w-full rounded-2xl border-slate-300 text-xs text-slate-800 placeholder-slate-400 p-3 focus:ring-amber-500 focus:border-amber-500"></textarea>
                        </div>
                        <button type="submit" class="w-full py-2.5 px-4 bg-amber-500 hover:bg-amber-600 text-slate-950 font-bold text-xs rounded-xl transition-all shadow-xs cursor-pointer">
                            <span>Add Administrative Note</span>
                        </button>
                    </form>
                </div>

            </div>
        </div>

        <!-- SECTION 4: CHRONOLOGICAL AUDIT LOG TIMELINE -->
        <div id="audit-trail" class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-200/80 shadow-xs">
            <div class="flex items-center justify-between pb-4 border-b border-slate-100 mb-6">
                <div>
                    <h3 class="text-sm font-bold uppercase tracking-wider text-slate-900 font-mono">4. Chronological Compliance &amp; Audit Trail</h3>
                    <p class="text-xs text-slate-500">Immutable ledger of state transitions, actors, timestamps, and IP addresses</p>
                </div>
                <span class="text-xs font-mono font-bold text-slate-600 bg-slate-100 px-3 py-1 rounded-xl">
                    {{ $order->statusLogs->count() }} Recorded Events
                </span>
            </div>

            @if($order->statusLogs->isEmpty())
                <div class="text-center py-10 text-xs text-slate-400 italic">
                    No status log transitions recorded yet for Order #{{ $order->order_number }}.
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-100">
                        <thead class="bg-slate-50/60">
                            <tr>
                                <th class="px-4 py-3 text-left text-[11px] font-bold text-slate-400 uppercase tracking-wider">Timestamp</th>
                                <th class="px-4 py-3 text-left text-[11px] font-bold text-slate-400 uppercase tracking-wider">Transition</th>
                                <th class="px-4 py-3 text-left text-[11px] font-bold text-slate-400 uppercase tracking-wider">Actor</th>
                                <th class="px-4 py-3 text-left text-[11px] font-bold text-slate-400 uppercase tracking-wider">Audit Details &amp; Note</th>
                                <th class="px-4 py-3 text-right text-[11px] font-bold text-slate-400 uppercase tracking-wider">IP Address</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-xs font-mono">
                            @foreach($order->statusLogs as $log)
                                <tr class="hover:bg-slate-50/50 transition-colors">
                                    <td class="px-4 py-3 whitespace-nowrap text-slate-500">
                                        <span class="font-bold text-slate-800">{{ $log->created_at->format('d M, Y') }}</span><br>
                                        <span class="text-[10px] text-slate-400">{{ $log->created_at->format('h:i:s A') }}</span>
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-slate-100 text-slate-700 uppercase">
                                            {{ $log->from_status ?? 'Init' }}
                                        </span>
                                        <span class="text-slate-400 mx-1">→</span>
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800 uppercase">
                                            {{ $log->to_status }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        <span class="font-bold text-slate-800 uppercase text-[11px]">{{ $log->actor_type ?? 'System' }}</span>
                                        @if($log->actor_id)
                                            <span class="text-[10px] text-slate-400 block font-normal">ID: {{ $log->actor_id }}</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-slate-700 font-sans text-xs">
                                        {{ $log->note ?? 'Status transitioned' }}
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap text-right text-slate-400 text-[11px]">
                                        {{ $log->ip_address ?? '127.0.0.1' }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

    </div>
</x-layouts.admin>
