<x-layouts.vendor title="Manage Order {{ $order->order_number }}">
    <div class="mb-8 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <div class="flex items-center gap-3 mb-1">
                <a href="{{ route('vendor.orders.index') }}" class="text-sm text-gray-500 hover:text-gray-700">← Back to Orders</a>
            </div>
            <h1 class="text-2xl font-serif font-bold text-gray-900">Order {{ $order->order_number }}</h1>
            <p class="text-sm text-gray-500 mt-1">Placed on {{ $order->created_at->format('F d, Y h:i A') }}</p>
        </div>
        <div class="flex flex-wrap items-center gap-2.5">
            <a href="{{ route('vendor.orders.shipping_label', $order) }}" target="_blank" 
               class="inline-flex items-center gap-1.5 rounded-xl bg-gray-900 px-3.5 py-2 text-xs font-bold text-white shadow-sm hover:bg-black transition-colors">
                <span>🏷️ Print Shipping Label (4×6")</span>
            </a>
            <a href="{{ route('orders.invoice', $order) }}" target="_blank" 
               class="inline-flex items-center gap-1.5 rounded-xl bg-white px-3.5 py-2 text-xs font-semibold text-gray-700 shadow-sm border border-gray-200 hover:bg-gray-50 transition-colors">
                <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                <span>Print Invoice</span>
            </a>
        </div>
    </div>
        @if($order->status === 'cancelled')
        <div class="mb-8 p-6 bg-red-50 border border-red-200 rounded-2xl flex items-start gap-4">
            <div class="w-10 h-10 rounded-full bg-red-100 flex items-center justify-center text-red-600 flex-shrink-0 text-xl font-bold">
                ✕
            </div>
            <div>
                <h3 class="text-base font-bold text-red-900">Order Cancelled</h3>
                <p class="text-sm text-red-700 mt-0.5">
                    Cancelled by <span class="font-semibold">{{ ucfirst($order->cancelled_by ?? 'vendor') }}</span> 
                    on {{ $order->cancelled_at ? $order->cancelled_at->format('M d, Y h:i A') : $order->updated_at->format('M d, Y') }}.
                </p>
                @if($order->cancel_reason)
                    <p class="text-sm text-red-800 mt-2 bg-red-100/70 px-3 py-1.5 rounded-lg inline-block">
                        <strong>Reason:</strong> {{ $order->cancel_reason }}
                    </p>
                @endif
                <p class="text-xs text-red-600 mt-2">All product stock has been restored to your inventory.</p>
            </div>
        </div>
    @endif

    @php
        $productNetSubtotal = max(0, $order->subtotal - $order->discount_amount);
        $platformCommissionPaise = (int) round($productNetSubtotal * 0.05);
        $vendorNetPayoutPaise = $order->vendor_payout_amount ?: ($productNetSubtotal - $platformCommissionPaise);
    @endphp

    @if($order->status !== 'cancelled' && $order->payment_method === 'phonepe' && $order->payment_status === 'paid')
        @if($order->settlement_status === 'pending')
            <!-- STAGE 1: INCOMING ORDER PREVIEW (BANK SETTLEMENT IN PROGRESS) -->
            <div class="mb-8 p-6 rounded-3xl shadow-xl border" style="background: linear-gradient(135deg, #1e1b4b 0%, #312e81 40%, #4c1d95 100%) !important; color: #ffffff !important; border: 1px solid rgba(168, 85, 247, 0.4) !important;">
                <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6">
                    <div class="space-y-3">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-black tracking-wide uppercase shadow-sm" style="background: #fbbf24 !important; color: #451a03 !important;">
                                ⏳ INCOMING ORDER PREVIEW
                            </span>
                            <span class="text-xs font-medium" style="color: #e9d5ff !important;">Payment Verified • Bank Settlement in Progress</span>
                        </div>
                        <h2 class="text-xl sm:text-2xl font-bold font-serif" style="color: #ffffff !important;">
                            Prepare Garment in Stock (Size & Qty Confirmed)
                        </h2>
                        <p class="text-sm leading-relaxed max-w-2xl" style="color: #f3e8ff !important;">
                            Customer paid <strong style="color: #ffffff !important;">₹{{ number_format($order->total_amount / 100, 2) }}</strong> via PhonePe. Inter-bank clearing is in progress. 
                            Your 95% payout of <strong style="color: #4ade80 !important; font-family: monospace; font-size: 1.15rem;">₹{{ number_format($vendorNetPayoutPaise / 100, 2) }}</strong> will unlock on 
                            <strong style="color: #ffffff !important; text-decoration: underline;">{{ $order->settlement_expected_at ? $order->settlement_expected_at->format('M d, Y h:i A') : 'Next Banking Day 11:00 AM' }}</strong>.
                        </p>
                        <div class="p-3.5 rounded-2xl flex items-center gap-2 text-xs" style="background: rgba(255, 255, 255, 0.1) !important; color: #fdf4ff !important; border: 1px solid rgba(255, 255, 255, 0.15) !important;">
                            <span>🔒 Customer address is locked during clearing to protect you. Please locate the item and keep it ready on your packing desk!</span>
                        </div>
                    </div>

                    <div class="flex flex-col sm:flex-row lg:flex-col items-stretch sm:items-center lg:items-end gap-3 flex-shrink-0">
                        <div class="text-right hidden lg:block">
                            <span class="text-xs uppercase tracking-wider block font-bold" style="color: #d8b4fe !important;">Your Net Payout</span>
                            <span class="text-3xl font-extrabold font-mono" style="color: #4ade80 !important;">₹{{ number_format($vendorNetPayoutPaise / 100, 2) }}</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <button type="button" disabled class="px-5 py-3 rounded-2xl font-bold text-xs flex items-center justify-center gap-2 cursor-not-allowed shadow-inner" style="background: rgba(15, 23, 42, 0.75) !important; color: #d8b4fe !important; border: 1px solid rgba(216, 180, 254, 0.3) !important;">
                                <span>🔒 Payout Locked (Clearing ~11 AM)</span>
                            </button>
                            <form action="{{ route('vendor.orders.simulate_settlement', $order) }}" method="POST">
                                @csrf
                                <button type="submit" title="Simulate bank settlement for instant testing" 
                                        class="px-4 py-3 rounded-2xl font-black text-xs transition shadow-md hover:shadow-lg flex items-center gap-1.5 cursor-pointer active:scale-95"
                                        style="background: #fbbf24 !important; color: #451a03 !important; border: 1px solid #f59e0b !important;">
                                    <span>⚡ Simulate Settlement</span>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        @elseif($order->settlement_status === 'cleared' && $order->vendor_payout_status !== 'paid')
            <!-- STAGE 2: SETTLEMENT CLEARED — READY TO ACCEPT & GET PAID INSTANTLY -->
            <div class="mb-8 p-6 rounded-3xl shadow-2xl border" style="background: linear-gradient(135deg, #064e3b 0%, #065f46 45%, #047857 100%) !important; color: #ffffff !important; border: 1px solid rgba(52, 211, 153, 0.5) !important;">
                <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6">
                    <div class="space-y-3">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-black tracking-wide uppercase shadow-sm" style="background: #34d399 !important; color: #022c22 !important;">
                                🟢 FUNDS SETTLED IN BANK
                            </span>
                            <span class="text-xs font-medium" style="color: #a7f3d0 !important;">Customer Address Unlocked • Cash Waiting</span>
                        </div>
                        <h2 class="text-2xl sm:text-3xl font-extrabold font-serif" style="color: #ffffff !important;">
                            ₹{{ number_format($vendorNetPayoutPaise / 100, 2) }} Ready for Instant UPI Transfer!
                        </h2>
                        <p class="text-sm leading-relaxed max-w-2xl" style="color: #ecfdf5 !important;">
                            Payment settlement has cleared into the platform Current Account. Tap <strong style="color: #ffffff !important;">Accept Order</strong> below to immediately receive 
                            <strong style="color: #a7f3d0 !important; font-family: monospace; font-size: 1.15rem;">₹{{ number_format($vendorNetPayoutPaise / 100, 2) }}</strong> directly into your UPI ID 
                            (<strong style="color: #fef08a !important; font-family: monospace;">{{ $order->vendor->settings->upi_id ?? 'UPI not set' }}</strong>) in 2 seconds.
                        </p>
                    </div>

                    <div class="flex flex-wrap items-center gap-3 flex-shrink-0">
                        <form action="{{ route('vendor.orders.accept', $order) }}" method="POST">
                            @csrf
                            <button type="submit" class="px-6 py-3.5 rounded-2xl font-black text-sm shadow-xl hover:shadow-2xl transition-all flex items-center gap-2 active:scale-95 cursor-pointer"
                                    style="background: #34d399 !important; color: #022c22 !important; border: 1px solid #10b981 !important;">
                                <span>🚀 ACCEPT & RECEIVE ₹{{ number_format($vendorNetPayoutPaise / 100, 2) }} INSTANTLY</span>
                            </button>
                        </form>
                        <form action="{{ route('vendor.orders.reject', $order) }}" method="POST" onsubmit="return confirm('Are you sure you want to reject this order? Customer will be refunded 100% immediately.')">
                            @csrf
                            <button type="submit" class="px-4 py-3.5 rounded-2xl font-bold text-xs transition cursor-pointer"
                                    style="background: rgba(153, 27, 27, 0.45) !important; color: #fecaca !important; border: 1px solid rgba(239, 68, 68, 0.4) !important;">
                                <span>❌ Reject (Out of Stock)</span>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        @elseif($order->vendor_payout_status === 'paid')
            <!-- STAGE 3: PAYOUT DISBURSED TO VENDOR UPI -->
            <div class="mb-8 p-6 rounded-3xl shadow-xl border" style="background: linear-gradient(135deg, #172554 0%, #1e40af 100%) !important; color: #ffffff !important; border: 1px solid rgba(96, 165, 250, 0.4) !important;">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div class="space-y-1">
                        <div class="flex items-center gap-2">
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-black tracking-wide uppercase" style="background: #60a5fa !important; color: #0c4a6e !important;">
                                ✓ 95% PAYOUT TRANSFERRED
                            </span>
                            <span class="text-xs" style="color: #bfdbfe !important;">Instant UPI Disbursal Complete</span>
                        </div>
                        <h2 class="text-xl font-bold font-serif" style="color: #ffffff !important;">
                            ₹{{ number_format($vendorNetPayoutPaise / 100, 2) }} Sent to {{ $order->vendor->settings->upi_id ?? 'your UPI' }}
                        </h2>
                        <p class="text-xs font-mono" style="color: #93c5fd !important;">
                            Bank UTR: <strong style="color: #ffffff !important;">{{ $order->vendor_payout_utr }}</strong> 
                            • Settled at {{ $order->vendor_payout_at ? $order->vendor_payout_at->format('M d, Y h:i A') : $order->updated_at->format('M d, Y') }}
                        </p>
                    </div>
                    <div class="text-right">
                        <span class="px-4 py-2 rounded-2xl text-xs font-bold inline-flex items-center gap-1" style="background: rgba(255, 255, 255, 0.15) !important; color: #ffffff !important; border: 1px solid rgba(255, 255, 255, 0.25) !important;">
                            <span>📦 Doorstep Courier Dispatch Unlocked</span>
                        </span>
                    </div>
                </div>
            </div>
        @endif
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <div class="lg:col-span-2 space-y-8">
            <!-- Order Items -->
            <div class="bg-white shadow-sm ring-1 ring-gray-900/5 sm:rounded-xl overflow-hidden">
                <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
                    <h3 class="text-base font-semibold leading-6 text-gray-900">Order Items</h3>
                </div>
                <div class="px-4 py-5 sm:p-6">
                    <ul class="divide-y divide-gray-100">
                        @foreach($order->items as $item)
                            <li class="py-4 flex gap-4">
                                <div class="w-16 h-20 bg-gray-100 rounded-md overflow-hidden flex-shrink-0">
                                    @if($item->product && $item->product->images->count() > 0)
                                        <img src="{{ asset('storage/' . $item->product->images->first()->path) }}" class="w-full h-full object-cover">
                                    @endif
                                </div>
                                <div class="flex-1">
                                    <h4 class="font-medium text-gray-900">{{ $item->product->title ?? $item->snapshot['title'] ?? 'Product' }}</h4>
                                    <div class="flex flex-wrap items-center gap-2 mt-1">
                                        <p class="text-sm text-gray-500">Variant SKU: {{ $item->variant->sku ?? $item->snapshot['variant_sku'] ?? 'N/A' }}</p>
                                        @php
                                            $itemWeightGrams = $item->product->weight_grams ?? null;
                                        @endphp
                                        @if($itemWeightGrams)
                                            <span class="inline-flex items-center gap-1 text-[11px] font-medium text-stone-600 bg-stone-100 px-2 py-0.5 rounded border border-stone-200">
                                                ⚖️ {{ $itemWeightGrams }}g / pc
                                                @if($item->quantity > 1)
                                                    <span class="text-stone-400">({{ $itemWeightGrams * $item->quantity }}g total)</span>
                                                @endif
                                            </span>
                                        @endif
                                    </div>
                                    @if(!empty($item->selected_modifiers_json))
                                        <div class="flex flex-wrap gap-1 mt-1.5">
                                            @foreach($item->selected_modifiers_json as $mod)
                                                <span class="text-[11px] text-amber-800 bg-amber-50 px-2 py-0.5 rounded border border-amber-200 font-semibold">
                                                    + {{ $mod['name'] }} (+₹{{ number_format($mod['price_delta'], 2) }})
                                                </span>
                                            @endforeach
                                        </div>
                                    @endif
                                    @if($item->gigPackage)
                                        <div class="mt-1.5">
                                            <span class="text-xs text-indigo-700 bg-indigo-50 border border-indigo-200 font-bold px-2 py-0.5 rounded">
                                                {{ ucfirst($item->gigPackage->tier) }} Milestone Deliverable • {{ $item->gigPackage->delivery_days }} Days Turnaround
                                            </span>
                                        </div>
                                    @endif
                                    <div class="flex justify-between items-center mt-2">
                                        <p class="text-sm text-gray-900 font-medium">₹{{ number_format($item->unit_price / 100, 2) }} x {{ $item->quantity }}</p>
                                        <p class="text-sm font-bold text-gray-900">₹{{ number_format($item->total_price / 100, 2) }}</p>
                                    </div>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                </div>
                <div class="bg-gray-50 px-4 py-5 sm:px-6 border-t border-gray-200 space-y-4">
                    @php
                        $totalConsignmentWeight = $order->items->sum(function($i) {
                            return ($i->product->weight_grams ?? 250) * $i->quantity;
                        });
                        $productNetSubtotal = max(0, $order->subtotal - $order->discount_amount);
                        $platformCommissionPaise = (int) round($productNetSubtotal * 0.05);
                        $vendorNetPayoutPaise = $productNetSubtotal - $platformCommissionPaise;
                    @endphp

                    <!-- 1. Vendor Net Payout Breakdown (Product Value Only) -->
                    <div class="bg-white rounded-xl p-4 border border-emerald-200/80 shadow-2xs space-y-2.5">
                        <div class="flex items-center justify-between pb-2 border-b border-gray-100">
                            <div>
                                <h4 class="text-xs uppercase tracking-wider font-bold text-gray-800">Your Earnings (95% Payout)</h4>
                                <p class="text-[11px] text-gray-500 mt-0.5">Calculated exclusively on your actual product price</p>
                            </div>
                            <span class="text-[10px] uppercase font-bold text-emerald-800 bg-emerald-100/80 px-2 py-0.5 rounded-full border border-emerald-300">Net Settlement</span>
                        </div>

                        <dl class="space-y-2 text-sm text-gray-600">
                            <div class="flex justify-between items-center">
                                <dt class="text-gray-700">Actual Product Price (Subtotal)</dt>
                                <dd class="font-bold text-gray-900">₹{{ number_format($order->subtotal / 100, 2) }}</dd>
                            </div>

                            @if($order->discount_amount > 0)
                                <div class="flex justify-between items-center text-emerald-700 text-xs">
                                    <dt>Coupon Discount</dt>
                                    <dd class="font-medium">-₹{{ number_format($order->discount_amount / 100, 2) }}</dd>
                                </div>
                            @endif

                            <div class="flex justify-between items-center text-xs text-purple-700 bg-purple-50 px-2.5 py-1.5 rounded-lg border border-purple-100">
                                <dt class="flex items-center gap-1">
                                    <span>Platform Fee (5% of ₹{{ number_format($productNetSubtotal / 100, 2) }} Product Cost)</span>
                                </dt>
                                <dd class="font-bold">-₹{{ number_format($platformCommissionPaise / 100, 2) }}</dd>
                            </div>

                            <div class="flex justify-between items-center text-base font-bold text-emerald-800 bg-emerald-50 px-3 py-2.5 rounded-xl border border-emerald-200 mt-1">
                                <dt class="flex flex-col">
                                    <span>Your Net Payout</span>
                                    <span class="text-[10px] text-emerald-600 font-normal">95% of Product Value (₹{{ number_format($productNetSubtotal / 100, 2) }})</span>
                                </dt>
                                <dd class="text-lg">₹{{ number_format($vendorNetPayoutPaise / 100, 2) }}</dd>
                            </div>
                        </dl>
                    </div>

                    <!-- 2. Customer Payment & Shipping Breakdown (Pass-through) -->
                    <div class="bg-stone-50 rounded-xl p-4 border border-stone-200/80 space-y-2 text-xs text-stone-600">
                        <div class="flex items-center justify-between pb-2 border-b border-stone-200">
                            <span class="font-bold uppercase tracking-wider text-stone-600 text-[11px]">Customer Payment Breakdown</span>
                            <span class="text-[10px] text-stone-500 font-medium">Order Total</span>
                        </div>

                        <div class="flex justify-between text-stone-700">
                            <span>Product Value</span>
                            <span class="font-medium">₹{{ number_format($order->subtotal / 100, 2) }}</span>
                        </div>

                        <div class="flex justify-between text-stone-700">
                            <span class="flex items-center gap-1">
                                <span>Doorstep Shipping Fee</span>
                                <span class="text-[10px] text-stone-500 font-mono">({{ $totalConsignmentWeight >= 1000 ? number_format($totalConsignmentWeight / 1000, 2) . ' kg' : $totalConsignmentWeight . 'g' }} parcel)</span>
                            </span>
                            <span class="font-medium">₹{{ number_format($order->shipping_amount / 100, 2) }}</span>
                        </div>

                        <div class="flex justify-between text-sm font-bold text-stone-900 border-t border-stone-200 pt-2">
                            <span>Total Paid by Customer</span>
                            <span>₹{{ number_format($order->total_amount / 100, 2) }}</span>
                        </div>

                        <div class="mt-2.5 p-2.5 bg-amber-50/80 border border-amber-200/80 rounded-lg text-[11px] text-amber-900 leading-relaxed flex items-start gap-2">
                            <span class="text-amber-600 text-sm leading-none shrink-0">💡</span>
                            <span>
                                The 5% platform commission is taken <strong>strictly from your product cost (₹{{ number_format($order->subtotal / 100, 2) }})</strong>. The ₹{{ number_format($order->shipping_amount / 100, 2) }} delivery fee is paid by the customer directly for courier shipping and is not part of your product deduction.
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            @if($order->isFoodOrder())
            <!-- 🍳 Food Order Kitchen & Delivery Console (Decision D13: Bypasses Shiprocket) -->
            <div class="bg-white shadow-sm ring-1 ring-gray-900/5 sm:rounded-xl overflow-hidden mb-6">
                <div class="px-4 py-5 sm:px-6 border-b border-gray-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-gradient-to-r from-rose-50/50 to-amber-50/30">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-rose-100 text-rose-700 flex items-center justify-center text-xl shadow-2xs">
                            🍳
                        </div>
                        <div>
                            <div class="flex items-center gap-2">
                                <h3 class="text-base font-bold text-gray-900">Food Order &amp; Kitchen Dispatch</h3>
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold {{ $order->isFoodTakeaway() ? 'bg-amber-100 text-amber-800' : 'bg-rose-100 text-rose-800' }}">
                                    {{ $order->isFoodTakeaway() ? '🥡 Takeaway / Pickup' : '🛵 Doorstep Delivery' }}
                                </span>
                            </div>
                            <p class="text-xs text-gray-500 mt-0.5">Manage live kitchen cooking lifecycle and direct local dispatch.</p>
                        </div>
                    </div>

                    <div class="flex items-center gap-2 text-xs">
                        <span class="text-gray-500">Target Delivery / Pickup:</span>
                        <span class="font-bold font-mono text-gray-900 bg-white px-2.5 py-1 rounded-lg border border-gray-200">
                            {{ $order->estimated_delivery_time ? $order->estimated_delivery_time->format('h:i A') : 'Within 30 mins' }}
                        </span>
                    </div>
                </div>

                <div class="p-6 space-y-6">
                    <!-- Customer Kitchen Notes Alert if present -->
                    @if($order->kitchen_notes)
                        <div class="p-4 rounded-xl bg-amber-50/80 border border-amber-200 text-xs text-amber-950 flex items-start gap-3">
                            <span class="text-lg">📝</span>
                            <div>
                                <span class="font-bold block text-amber-900">Customer Cooking Instructions:</span>
                                <p class="mt-0.5 text-amber-800 font-medium">{{ $order->kitchen_notes }}</p>
                            </div>
                        </div>
                    @endif

                    <!-- Stage Progress Cards -->
                    <div class="grid grid-cols-1 sm:grid-cols-4 gap-3 text-center">
                        <div class="p-3 rounded-xl border {{ $order->status === 'pending' ? 'bg-amber-50 border-amber-300 ring-2 ring-amber-400/30' : 'bg-gray-50 border-gray-200 opacity-60' }}">
                            <div class="text-lg mb-1">⏳</div>
                            <div class="text-xs font-bold text-gray-900">1. Order Placed</div>
                            <div class="text-[10px] text-gray-500">Awaiting kitchen</div>
                        </div>
                        <div class="p-3 rounded-xl border {{ $order->status === 'processing' ? 'bg-rose-50 border-rose-300 ring-2 ring-rose-400/30' : 'bg-gray-50 border-gray-200 opacity-60' }}">
                            <div class="text-lg mb-1">🍳</div>
                            <div class="text-xs font-bold text-gray-900">2. In The Kitchen</div>
                            <div class="text-[10px] text-gray-500">Cooking & packing</div>
                        </div>
                        <div class="p-3 rounded-xl border {{ in_array($order->status, ['shipped', 'ready_for_pickup']) ? 'bg-blue-50 border-blue-300 ring-2 ring-blue-400/30' : 'bg-gray-50 border-gray-200 opacity-60' }}">
                            <div class="text-lg mb-1">{{ $order->isFoodTakeaway() ? '🥡' : '🛵' }}</div>
                            <div class="text-xs font-bold text-gray-900">3. {{ $order->isFoodTakeaway() ? 'Ready for Pickup' : 'Out for Delivery' }}</div>
                            <div class="text-[10px] text-gray-500">{{ $order->isFoodTakeaway() ? 'Waiting for customer' : 'Rider on route' }}</div>
                        </div>
                        <div class="p-3 rounded-xl border {{ $order->status === 'delivered' ? 'bg-emerald-50 border-emerald-300 ring-2 ring-emerald-400/30' : 'bg-gray-50 border-gray-200 opacity-60' }}">
                            <div class="text-lg mb-1">✅</div>
                            <div class="text-xs font-bold text-gray-900">4. Handed Over</div>
                            <div class="text-[10px] text-gray-500">Meal enjoyed</div>
                        </div>
                    </div>

                    <!-- 1-Click Action Flow -->
                    <div class="pt-4 border-t border-gray-100 flex flex-wrap items-center justify-between gap-4">
                        <div class="text-xs text-gray-600">
                            Current Stage: <span class="font-bold text-gray-900 uppercase">{{ str_replace('_', ' ', $order->status) }}</span>
                        </div>

                        <div class="flex flex-wrap items-center gap-2.5">
                            @if($order->status === 'pending')
                                <form action="{{ route('vendor.orders.status', $order) }}" method="POST">
                                    @csrf
                                    @method('PUT')
                                    <input type="hidden" name="status" value="processing">
                                    <button type="submit" class="px-4 py-2 rounded-xl bg-rose-600 hover:bg-rose-500 text-white font-bold text-xs shadow transition active:scale-95 flex items-center gap-1.5 cursor-pointer">
                                        <span>🍳 Start Cooking (Send to Kitchen)</span>
                                    </button>
                                </form>
                            @endif

                            @if($order->status === 'processing')
                                @if($order->isFoodTakeaway())
                                    <form action="{{ route('vendor.orders.status', $order) }}" method="POST">
                                        @csrf
                                        @method('PUT')
                                        <input type="hidden" name="status" value="ready_for_pickup">
                                        <button type="submit" class="px-4 py-2 rounded-xl bg-amber-600 hover:bg-amber-500 text-white font-bold text-xs shadow transition active:scale-95 flex items-center gap-1.5 cursor-pointer">
                                            <span>🥡 Mark Ready for Pickup</span>
                                        </button>
                                    </form>
                                @else
                                    <form action="{{ route('vendor.orders.status', $order) }}" method="POST">
                                        @csrf
                                        @method('PUT')
                                        <input type="hidden" name="status" value="shipped">
                                        <button type="submit" class="px-4 py-2 rounded-xl bg-blue-600 hover:bg-blue-500 text-white font-bold text-xs shadow transition active:scale-95 flex items-center gap-1.5 cursor-pointer">
                                            <span>🛵 Dispatch Rider (Out for Delivery)</span>
                                        </button>
                                    </form>
                                @endif
                            @endif

                            @if(in_array($order->status, ['processing', 'shipped', 'ready_for_pickup']))
                                <form action="{{ route('vendor.orders.status', $order) }}" method="POST">
                                    @csrf
                                    @method('PUT')
                                    <input type="hidden" name="status" value="delivered">
                                    <button type="submit" class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs shadow transition active:scale-95 flex items-center gap-1.5 cursor-pointer">
                                        <span>✅ Mark Delivered / Handed Over</span>
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            @else
            <!-- ⚡ Automated Shiprocket Logistics Card -->
            <div class="bg-white shadow-sm ring-1 ring-gray-900/5 sm:rounded-xl overflow-hidden mb-6">
                <div class="px-4 py-5 sm:px-6 border-b border-gray-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-xl bg-emerald-50 border border-emerald-200/60 flex items-center justify-center text-lg shadow-2xs">
                            🚚
                        </div>
                        <div>
                            <div class="flex items-center gap-2">
                                <h3 class="text-base font-semibold leading-6 text-gray-900">Doorstep Courier Dispatch</h3>
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-semibold {{ ($shippingMode ?? 'live') === 'live' ? 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-600/20' : 'bg-amber-50 text-amber-700 ring-1 ring-amber-600/20' }}">
                                    <span class="w-1.5 h-1.5 rounded-full {{ ($shippingMode ?? 'live') === 'live' ? 'bg-emerald-500' : 'bg-amber-500' }}"></span>
                                    {{ ($shippingMode ?? 'live') === 'live' ? 'Shiprocket Live' : 'Sandbox Test' }}
                                </span>
                            </div>
                            <p class="text-xs text-gray-500 mt-0.5">Doorstep parcel pickup by Delhivery, BlueDart, Shadowfax with automated tracking & thermal labels.</p>
                        </div>
                    </div>

                    @if($order->shipment)
                        <div class="inline-flex items-center gap-2 bg-emerald-50 text-emerald-800 border border-emerald-200 px-3 py-1.5 rounded-lg text-xs font-semibold">
                            <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                            <span>AWB: {{ $order->shipment->awb_code }}</span>
                        </div>
                    @endif
                </div>

                <div class="px-4 py-5 sm:p-6 space-y-5">
                    @if($order->shipment)
                        <!-- Shipment Details (Already Booked) -->
                        <div class="bg-stone-50 border border-stone-200 rounded-xl p-4 sm:p-5">
                            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 text-xs mb-4">
                                <div>
                                    <span class="text-gray-500 block mb-1">Courier Partner</span>
                                    <span class="font-bold text-gray-900 text-sm">{{ $order->shipment->courier_name ?? 'Courier Partner' }}</span>
                                </div>
                                <div>
                                    <span class="text-gray-500 block mb-1">AWB Tracking #</span>
                                    <span class="font-mono font-bold text-emerald-700 text-sm select-all">{{ $order->shipment->awb_code }}</span>
                                </div>
                                <div>
                                    <span class="text-gray-500 block mb-1">Status</span>
                                    <span class="inline-block uppercase font-bold text-[11px] px-2 py-0.5 rounded-md bg-emerald-100 text-emerald-800 border border-emerald-200">
                                        {{ ucfirst(str_replace('_', ' ', $order->shipment->status)) }}
                                    </span>
                                </div>
                                <div>
                                    <span class="text-gray-500 block mb-1">Package Weight</span>
                                    <span class="font-bold text-gray-900">{{ $order->shipment->package_weight_kg }} kg</span>
                                </div>
                            </div>

                            <!-- Action Buttons -->
                            <div class="flex flex-wrap items-center gap-3 pt-3 border-t border-gray-200">
                                @if($order->shipment->label_url)
                                    <a href="{{ $order->shipment->label_url }}" target="_blank" 
                                       class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg bg-gray-900 hover:bg-black text-white font-semibold text-xs transition shadow-xs">
                                        <span>🏷️ Download Shiprocket Label (PDF)</span>
                                    </a>
                                @else
                                    <a href="{{ route('vendor.orders.shipping_label', $order) }}" target="_blank" 
                                       class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg bg-gray-900 hover:bg-black text-white font-semibold text-xs transition shadow-xs">
                                        <span>🏷️ Print Thermal Barcode Label</span>
                                    </a>
                                @endif

                                @if($order->shipment->tracking_url)
                                    <a href="{{ $order->shipment->tracking_url }}" target="_blank" 
                                       class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg bg-white hover:bg-gray-50 text-gray-700 font-semibold text-xs transition border border-gray-300 shadow-2xs">
                                       <span>📍 Live Courier Tracking ↗</span>
                                    </a>
                                @endif
                            </div>
                        </div>

                        <!-- Simulator / Testing Toolbar (Always available for instant testing) -->
                        <div class="bg-amber-50/70 rounded-xl p-3 border border-amber-200/80 flex flex-wrap items-center justify-between gap-3 text-xs">
                            <span class="text-amber-900 font-medium">🧪 <b>Testing Simulator:</b> Simulate courier scan events:</span>
                            <div class="flex flex-wrap items-center gap-2">
                                <form action="{{ route('vendor.orders.simulate_shipping', $order) }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="target_status" value="in_transit">
                                    <button type="submit" class="px-2.5 py-1 rounded-md bg-white hover:bg-blue-50 text-blue-700 border border-blue-200 text-[11px] font-semibold transition cursor-pointer shadow-2xs">
                                        🚚 In-Transit
                                    </button>
                                </form>
                                <form action="{{ route('vendor.orders.simulate_shipping', $order) }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="target_status" value="out_for_delivery">
                                    <button type="submit" class="px-2.5 py-1 rounded-md bg-white hover:bg-amber-50 text-amber-700 border border-amber-200 text-[11px] font-semibold transition cursor-pointer shadow-2xs">
                                        🛵 Out for Delivery
                                    </button>
                                </form>
                                <form action="{{ route('vendor.orders.simulate_shipping', $order) }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="target_status" value="delivered">
                                    <button type="submit" class="px-2.5 py-1 rounded-md bg-white hover:bg-emerald-50 text-emerald-700 border border-emerald-200 text-[11px] font-semibold transition cursor-pointer shadow-2xs">
                                        🎁 Mark Delivered
                                    </button>
                                </form>
                            </div>
                        </div>

                    @else
                        @if($order->payment_method === 'phonepe' && $order->settlement_status === 'pending')
                            <!-- Courier Locked while Settlement is Pending -->
                            <div class="p-6 bg-indigo-50/70 border border-indigo-200/80 rounded-2xl text-center space-y-2.5">
                                <div class="w-12 h-12 rounded-full bg-indigo-100 text-indigo-700 flex items-center justify-center mx-auto text-xl shadow-2xs">
                                    ⏳
                                </div>
                                <h4 class="text-sm font-bold text-indigo-950">Doorstep Courier Booking Unlocks After Bank Settlement</h4>
                                <p class="text-xs text-indigo-700 max-w-md mx-auto leading-relaxed">
                                    Customer payment settlement is clearing with PhonePe (credits tomorrow by ~11:00 AM). 
                                    Once settled, the <strong>Accept Order & Receive ₹{{ number_format($vendorNetPayoutPaise / 100, 2) }}</strong> button at the top will unlock, transferring your payout and enabling 1-click courier pickup instantly.
                                </p>
                            </div>
                        @elseif($order->payment_method === 'phonepe' && $order->settlement_status === 'cleared' && $order->vendor_payout_status !== 'paid')
                            <!-- Courier Locked until Accept is Clicked -->
                            <div class="p-6 bg-emerald-50/80 border border-emerald-200 rounded-2xl text-center space-y-2.5">
                                <div class="w-12 h-12 rounded-full bg-emerald-100 text-emerald-800 flex items-center justify-center mx-auto text-xl shadow-2xs">
                                    👉
                                </div>
                                <h4 class="text-sm font-bold text-emerald-950">Tap "Accept Order" Above to Receive Your Payout First!</h4>
                                <p class="text-xs text-emerald-700 max-w-md mx-auto leading-relaxed">
                                    Click the green <strong>Accept Order & Receive ₹{{ number_format($vendorNetPayoutPaise / 100, 2) }} Instantly</strong> button at the top of the page. Your 95% cash payout will immediately transfer to your UPI ID in 2 seconds, and then courier booking will activate here!
                                </p>
                            </div>
                        @else
                            <!-- Ready to Book 1-Click Shipment -->
                            <div class="space-y-4" x-data="{ selectedCourier: '{{ $ratesData['recommended']['courier_company_id'] ?? ($ratesData['available_couriers'][0]['courier_company_id'] ?? '') }}' }">
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 bg-stone-50 p-3.5 rounded-xl border border-stone-200/80 text-xs">
                                <div>
                                    <span class="text-gray-500 block mb-0.5">Pickup Location</span>
                                    <span class="font-semibold text-gray-900 truncate block">
                                        {{ $order->vendor->shop_name }} ({{ is_array($order->vendor->pickup_address) ? ($order->vendor->pickup_address['city'] ?? 'Ajmer') : 'Ajmer' }})
                                    </span>
                                </div>
                                <div>
                                    <span class="text-gray-500 block mb-0.5">Destination Pincode</span>
                                    <span class="font-semibold text-gray-900 block">
                                        {{ $order->address_snapshot['city'] ?? 'City' }} ({{ $order->address_snapshot['pincode'] ?? '400001' }})
                                    </span>
                                </div>
                                <div>
                                    <span class="text-gray-500 block mb-0.5">Calculated Weight</span>
                                    <span class="font-semibold text-gray-900 block">⚖️ {{ $weightKg ?? 0.50 }} kg</span>
                                </div>
                            </div>

                                             <div class="space-y-2.5">
                                    <div class="p-3 bg-emerald-50/80 rounded-xl border border-emerald-200 text-xs text-emerald-950 flex flex-wrap items-center justify-between gap-2">
                                        <div class="flex items-center gap-2">
                                            <span class="text-sm">💰</span>
                                            <span>Customer Paid Shipping: <strong class="text-emerald-900 font-mono text-sm">₹{{ number_format(($order->shipping_amount ?? 6000) / 100, 2) }}</strong></span>
                                        </div>
                                        <span class="text-[11px] font-semibold text-emerald-800 bg-emerald-100/70 px-2 py-0.5 rounded-md border border-emerald-300/60">
                                            ✓ Recommended Partner is 100% Funded
                                        </span>
                                    </div>

                                    <div class="flex items-center justify-between pt-1">
                                        <span class="text-xs font-semibold uppercase tracking-wider text-gray-700">Choose Courier Partner:</span>
                                        <span class="text-xs text-emerald-700 font-medium">★ Best matched rate is pre-selected</span>
                                    </div>
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                                        @foreach(array_slice($ratesData['available_couriers'], 0, 4) as $courier)
                                            @php
                                                $isRec = (!empty($ratesData['recommended']['courier_company_id']) && $ratesData['recommended']['courier_company_id'] == $courier['courier_company_id']);
                                            @endphp
                                            <div @click="selectedCourier = '{{ $courier['courier_company_id'] }}'"
                                                 :class="selectedCourier == '{{ $courier['courier_company_id'] }}' 
                                                     ? 'border-emerald-600 bg-emerald-50/50 ring-2 ring-emerald-600/20 shadow-xs' 
                                                     : 'border-gray-200 hover:border-gray-300 bg-white hover:bg-stone-50/50'"
                                                 class="flex items-center justify-between p-3 rounded-xl border transition-all duration-150 cursor-pointer select-none">
                                                
                                                <div class="flex items-center gap-3">
                                                    <!-- Radio Indicator -->
                                                    <div class="w-4 h-4 rounded-full border flex items-center justify-center transition flex-shrink-0"
                                                         :class="selectedCourier == '{{ $courier['courier_company_id'] }}' 
                                                             ? 'border-emerald-600 bg-emerald-600 text-white' 
                                                             : 'border-gray-300 bg-white'">
                                                        <svg x-show="selectedCourier == '{{ $courier['courier_company_id'] }}'" class="w-2.5 h-2.5 fill-current" viewBox="0 0 20 20">
                                                             <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                                        </svg>
                                                    </div>
                                                    <div>
                                                        <div class="flex items-center gap-1.5 flex-wrap">
                                                            <span class="font-bold text-gray-900 text-xs">{{ $courier['courier_name'] }}</span>
                                                            @if($isRec)
                                                                <span class="text-[9px] font-bold uppercase px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-800 border border-emerald-200">Matched Quote</span>
                                                            @endif
                                                        </div>
                                                        <span class="text-[11px] text-gray-500">Est. {{ $courier['estimated_delivery_days'] }} Days</span>
                                                    </div>
                                                </div>

                                                <div class="text-right flex-shrink-0">
                                                    <span class="font-mono font-bold text-gray-900 text-sm">₹{{ number_format($courier['customer_rate'] ?? ($courier['rate'] + 20), 2) }}</span>
                                                    <span class="text-[10px] text-gray-500 block">Courier Rate</span>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>

                            <!-- Dispatch Form -->
                            <form action="{{ route('vendor.orders.ship_automated', $order) }}" method="POST" class="pt-1">
                                @csrf
                                <input type="hidden" name="courier_company_id" :value="selectedCourier">

                                <!-- Package Dimensions & Weight Card (Sent to Shiprocket) -->
                                <div class="p-3.5 bg-stone-50 rounded-xl border border-stone-200/90 mb-3 space-y-2">
                                    <div class="flex items-center justify-between">
                                        <span class="text-xs font-bold text-gray-800 flex items-center gap-1.5">
                                            <span>📦 Package Measurements for Courier</span>
                                            <span class="text-[10px] text-gray-500 font-normal">(Sent to Shiprocket)</span>
                                        </span>
                                        <span class="text-[10px] text-gray-500 font-mono hidden sm:inline">Volumetric formula: (L×B×H ÷ 5000)</span>
                                    </div>
                                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 text-xs">
                                        <div>
                                            <label class="text-[10.5px] font-semibold text-gray-600 block mb-0.5">Dead Weight</label>
                                            <div class="relative">
                                                <input type="number" step="0.01" min="0.1" name="package_weight" value="{{ $weightKg ?? 0.45 }}" class="w-full pl-2.5 pr-7 py-1.5 rounded-lg border border-gray-300 text-xs font-mono font-bold text-gray-900 bg-white focus:outline-none focus:ring-1 focus:ring-gray-900">
                                                <span class="absolute right-2 top-1/2 -translate-y-1/2 text-[10px] text-gray-400">kg</span>
                                            </div>
                                        </div>
                                        <div>
                                            <label class="text-[10.5px] font-semibold text-gray-600 block mb-0.5">Length</label>
                                            <div class="relative">
                                                <input type="number" min="1" max="200" name="package_length" value="22" class="w-full pl-2.5 pr-7 py-1.5 rounded-lg border border-gray-300 text-xs font-mono font-bold text-gray-900 bg-white focus:outline-none focus:ring-1 focus:ring-gray-900">
                                                <span class="absolute right-2 top-1/2 -translate-y-1/2 text-[10px] text-gray-400">cm</span>
                                            </div>
                                        </div>
                                        <div>
                                            <label class="text-[10.5px] font-semibold text-gray-600 block mb-0.5">Breadth</label>
                                            <div class="relative">
                                                <input type="number" min="1" max="200" name="package_breadth" value="18" class="w-full pl-2.5 pr-7 py-1.5 rounded-lg border border-gray-300 text-xs font-mono font-bold text-gray-900 bg-white focus:outline-none focus:ring-1 focus:ring-gray-900">
                                                <span class="absolute right-2 top-1/2 -translate-y-1/2 text-[10px] text-gray-400">cm</span>
                                            </div>
                                        </div>
                                        <div>
                                            <label class="text-[10.5px] font-semibold text-gray-600 block mb-0.5">Height</label>
                                            <div class="relative">
                                                <input type="number" min="1" max="200" name="package_height" value="8" class="w-full pl-2.5 pr-7 py-1.5 rounded-lg border border-gray-300 text-xs font-mono font-bold text-gray-900 bg-white focus:outline-none focus:ring-1 focus:ring-gray-900">
                                                <span class="absolute right-2 top-1/2 -translate-y-1/2 text-[10px] text-gray-400">cm</span>
                                            </div>
                                        </div>
                                    </div>
                                    <p class="text-[10.5px] text-gray-400 leading-normal">
                                        Auto-calculated for standard apparel pouches (22×18×8 cm). For bulky items (footwear box, heavy lehengas), adjust box size to prevent courier hub discrepancy charges.
                                    </p>
                                </div>

                                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 p-4 rounded-xl bg-stone-50 border border-stone-200">
                                    <div>
                                        <h4 class="text-sm font-semibold text-gray-900">Schedule Doorstep Courier Pickup</h4>
                                        <p class="text-xs text-gray-500 mt-0.5">Click when this order is packed in a polybag and ready on your shop counter.</p>
                                    </div>
                                    <button type="submit" 
                                            onclick="return confirm('Book courier pickup with Shiprocket for Order #{{ $order->order_number }}?');"
                                            class="px-5 py-2.5 rounded-xl bg-gray-900 hover:bg-black text-white font-semibold text-sm transition shadow-sm flex items-center justify-center gap-2 cursor-pointer flex-shrink-0">
                                        <span>📦 Confirm & Book Pickup</span>
                                    </button>
                                </div>
                            </form>
                        </div>
                        @endif
                    @endif
                </div>
            </div>
            @endif

            <!-- Fulfillment & Tracking (Manual Option) -->
            <div class="bg-white shadow-sm ring-1 ring-gray-900/5 sm:rounded-xl overflow-hidden" 
                 x-data="{ status: '{{ $order->status }}', paymentStatus: '{{ $order->payment_status }}' }">
                <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
                    <h3 class="text-base font-semibold leading-6 text-gray-900">Manual Order Status & Tracking Override</h3>
                </div>
                <div class="px-4 py-5 sm:p-6">
                    <form action="{{ route('vendor.orders.status', $order) }}" method="POST" class="space-y-4">
                        @csrf
                        @method('PUT')
                        
                        <div>
                            <label for="status" class="block text-sm font-medium leading-6 text-gray-900">Fulfillment Status</label>
                            <select id="status" name="status" x-model="status" class="mt-2 block w-full rounded-md border-0 py-1.5 pl-3 pr-10 text-gray-900 ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-brand-sage sm:text-sm sm:leading-6">
                                <option value="pending">Pending Verification</option>
                                <option value="processing">Processing (Packing / Kitchen Cooking)</option>
                                <option value="ready_for_pickup">Ready for Pickup (Takeaway)</option>
                                <option value="shipped">Shipped (Dispatched / Out for Delivery)</option>
                                <option value="delivered">Delivered</option>
                                <option value="cancelled">Cancelled</option>
                            </select>
                        </div>

                        <!-- Cancellation Reason when status is cancelled -->
                        <div x-show="status === 'cancelled'" class="bg-red-50 p-4 rounded-lg border border-red-100">
                            <label for="cancel_reason" class="block text-sm font-medium text-red-900">Cancellation Reason</label>
                            <input type="text" name="cancel_reason" id="cancel_reason" 
                                   value="{{ $order->cancel_reason }}" 
                                   placeholder="e.g. Payment not received / Invalid UTR, Out of stock, Customer requested" 
                                   class="mt-1 block w-full rounded-md border-0 py-1.5 text-gray-900 ring-1 ring-inset ring-red-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-red-500 sm:text-sm sm:leading-6">
                            <p class="text-xs text-red-600 mt-1">Stock for these items will automatically be returned to inventory upon saving.</p>
                        </div>

                        <!-- Tracking fields when shipped -->
                        <div class="space-y-3 bg-stone-50/70 p-4 rounded-xl border border-stone-200/70" x-data="{
                            selectedCourier: '{{ $order->courier_name ?? '' }}',
                            awb: '{{ \Illuminate\Support\Str::startsWith($order->tracking_number, 'TXN_') ? '' : $order->tracking_number }}',
                            trackingUrl: '{{ $order->tracking_url ?? '' }}',
                            updateUrl() {
                                if (!this.awb) return;
                                const c = this.selectedCourier.toLowerCase();
                                if (c.includes('delhivery')) {
                                    this.trackingUrl = 'https://www.delhivery.com/track/package/' + this.awb;
                                } else if (c.includes('blue') || c.includes('dart')) {
                                    this.trackingUrl = 'https://www.bluedart.com/tracking?trackNumber=' + this.awb;
                                } else if (c.includes('dtdc')) {
                                    this.trackingUrl = 'https://www.dtdc.in/tracking/shipment-tracking.asp?awb=' + this.awb;
                                } else if (c.includes('post') || c.includes('speed')) {
                                    this.trackingUrl = 'https://www.indiapost.gov.in/_layouts/15/dpt.cept.tracking/trackconsignment.aspx?consignmentNumber=' + this.awb;
                                } else if (c.includes('shadowfax')) {
                                    this.trackingUrl = 'https://tracker.shadowfax.in/#/track?awb=' + this.awb;
                                } else if (c.includes('xpressbees')) {
                                    this.trackingUrl = 'https://www.xpressbees.com/track?tracking_id=' + this.awb;
                                } else if (c.includes('ekart')) {
                                    this.trackingUrl = 'https://ekartlogistics.com/shipmenttrack/' + this.awb;
                                }
                            }
                        }">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <div>
                                    <label for="courier_name" class="block text-xs font-bold uppercase tracking-wider text-gray-700">Courier Partner</label>
                                    <select name="courier_name" id="courier_name" x-model="selectedCourier" @change="updateUrl()" 
                                            class="mt-1 block w-full rounded-lg border-0 py-1.5 pl-3 pr-10 text-gray-900 ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-brand-sage sm:text-xs">
                                        <option value="">Select Indian Courier...</option>
                                        <option value="Delhivery">Delhivery</option>
                                        <option value="Blue Dart">Blue Dart</option>
                                        <option value="DTDC">DTDC Express</option>
                                        <option value="India Post (Speed Post)">India Post (Speed Post)</option>
                                        <option value="Shadowfax">Shadowfax</option>
                                        <option value="Xpressbees">Xpressbees</option>
                                        <option value="Ekart Logistics">Ekart Logistics</option>
                                        <option value="Other Courier">Other Courier</option>
                                    </select>
                                </div>
                                <div>
                                    <label for="tracking_number" class="block text-xs font-bold uppercase tracking-wider text-gray-700">Tracking Number (AWB)</label>
                                    <input type="text" name="tracking_number" id="tracking_number" 
                                           x-model="awb" @input="updateUrl()" 
                                           placeholder="e.g. 14238910082" 
                                           class="mt-1 block w-full rounded-lg border-0 py-1.5 text-gray-900 ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-brand-sage sm:text-xs font-mono">
                                </div>
                            </div>

                            <div>
                                <div class="flex items-center justify-between">
                                    <label for="tracking_url" class="block text-xs font-bold uppercase tracking-wider text-gray-700">Live Tracking URL</label>
                                    <span class="text-[11px] text-gray-400">Auto-generated from Courier & AWB</span>
                                </div>
                                <input type="url" name="tracking_url" id="tracking_url" 
                                       x-model="trackingUrl" 
                                       placeholder="https://www.delhivery.com/track/package/..." 
                                       class="mt-1 block w-full rounded-lg border-0 py-1.5 text-gray-900 ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-brand-sage sm:text-xs">
                            </div>
                        </div>

                        @if($order->payment_method === 'phonepe')
                            <div class="border-t border-gray-200 pt-4 mt-4">
                                <div class="bg-purple-50 border border-purple-200 p-4 rounded-xl text-sm">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center gap-2 text-purple-900 font-bold">
                                            <span>⚡ PhonePe Auto-Verified Payment</span>
                                        </div>
                                        @if($order->payment_status !== 'paid')
                                            <div class="flex items-center gap-2">
                                                <a href="{{ route('payment.phonepe.callback', ['order' => $order->id, 'code' => 'PAYMENT_SUCCESS', 'transactionId' => $order->idempotency_key]) }}" 
                                                   title="Simulate successful payment for local testing" 
                                                   class="text-xs bg-emerald-600 hover:bg-emerald-700 text-white font-semibold px-2.5 py-1 rounded-md transition-colors shadow-sm flex items-center gap-1">
                                                    <span>⚡ Simulate Success</span>
                                                </a>
                                                <a href="{{ route('payment.phonepe.initiate', $order) }}" target="_blank" class="text-xs bg-purple-600 hover:bg-purple-700 text-white font-semibold px-2.5 py-1 rounded-md transition-colors shadow-sm">
                                                    Open PhonePe ↗
                                                </a>
                                            </div>
                                        @endif
                                    </div>
                                    <p class="text-xs text-purple-700 mt-1">Payment is confirmed automatically by PhonePe Gateway upon successful customer checkout.</p>
                                    <div class="mt-3 pt-3 border-t border-purple-200/60 grid grid-cols-2 gap-2 text-xs">
                                        <div>
                                            <span class="text-gray-500">Transaction ID:</span>
                                            <span class="font-mono font-semibold text-gray-900 block truncate">{{ $order->paymentClaim->utr_number ?? $order->idempotency_key ?? ($order->tracking_number && \Illuminate\Support\Str::startsWith($order->tracking_number, 'TXN_') ? $order->tracking_number : 'TXN_PENDING') }}</span>
                                        </div>
                                        <div>
                                            <span class="text-gray-500">Payment Status:</span>
                                            @if($order->payment_status === 'paid')
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-bold bg-green-100 text-green-800">
                                                    Paid & Confirmed
                                                </span>
                                            @elseif($order->payment_status === 'failed')
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-bold bg-red-100 text-red-800">
                                                    Payment Failed
                                                </span>
                                            @else
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-bold bg-amber-100 text-amber-800">
                                                    Payment Pending
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @elseif($order->payment_method === 'upi')
                            <div class="border-t border-gray-200 pt-4 mt-4">
                                <h4 class="text-sm font-medium text-gray-900 mb-2">Payment Verification (UPI)</h4>
                                <div class="bg-gray-50 p-3 rounded-md mb-3 text-sm">
                                    <p class="flex justify-between items-center">
                                        <span class="text-gray-500">Customer Submitted UTR:</span> 
                                        <span class="font-mono font-bold text-gray-900 bg-white px-2 py-1 rounded border">{{ $order->paymentClaim->utr_number ?? 'Not provided' }}</span>
                                    </p>
                                    <p class="text-xs text-gray-500 mt-1">Check your bank statement or UPI app to confirm ₹{{ number_format($order->total_amount / 100, 2) }} was credited.</p>
                                </div>
                                <label for="payment_status" class="block text-sm font-medium leading-6 text-gray-900">Verify Payment Status</label>
                                <select id="payment_status" name="payment_status" x-model="paymentStatus" class="mt-2 block w-full rounded-md border-0 py-1.5 pl-3 pr-10 text-gray-900 ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-brand-sage sm:text-sm sm:leading-6">
                                    <option value="processing">Processing / Unverified</option>
                                    <option value="paid">Paid & Verified (Payment Received)</option>
                                    <option value="failed">Failed / Invalid UTR (Fake/Unpaid)</option>
                                </select>

                                <div x-show="paymentStatus === 'failed'" class="mt-3">
                                    <label for="rejection_reason" class="block text-xs font-medium text-red-700">Payment Rejection Reason</label>
                                    <input type="text" name="rejection_reason" id="rejection_reason" placeholder="e.g. Transaction ID not found in bank statement" class="mt-1 block w-full rounded-md border-0 py-1.5 text-gray-900 ring-1 ring-inset ring-red-300 sm:text-xs">
                                </div>
                            </div>
                        @endif

                        <div class="mt-5 flex items-center justify-end">
                            <button type="submit" class="rounded-md bg-brand-sage px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-brand-sage/90 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-sage">Save Changes</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="space-y-8">
            <!-- Customer Info -->
            <div class="bg-white shadow-sm ring-1 ring-gray-900/5 sm:rounded-xl overflow-hidden">
                <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
                    <h3 class="text-base font-semibold leading-6 text-gray-900">Customer Details</h3>
                </div>
                <div class="px-4 py-5 sm:p-6 text-sm">
                    @if($order->payment_method === 'phonepe' && $order->settlement_status === 'pending')
                        <!-- STAGE 1: MASKED PRIVACY VIEW (BANK CLEARING IN PROGRESS) -->
                        <div class="mb-4">
                            <p class="font-bold text-gray-900">{{ substr($order->user->first_name ?? 'Customer', 0, 1) }}**** {{ substr($order->user->last_name ?? '', 0, 1) }}****</p>
                            <p class="text-xs text-gray-400">Email hidden until payment clears in bank</p>
                        </div>
                        
                        <div class="mt-4 p-3.5 bg-amber-50/80 border border-amber-200/80 rounded-xl space-y-2">
                            <div class="flex items-center gap-1.5 text-xs font-bold text-amber-900">
                                <span>🔒 Shipping Address Locked</span>
                            </div>
                            <p class="text-xs text-amber-800">
                                Destination: <strong>{{ $order->address_snapshot['city'] ?? 'City' }}, {{ $order->address_snapshot['state'] ?? 'State' }} ({{ $order->address_snapshot['pincode'] ?? 'Pincode' }})</strong>
                            </p>
                            <p class="text-[11px] text-amber-700 leading-relaxed pt-1 border-t border-amber-200/60">
                                Full street address and contact phone will unlock automatically tomorrow on 
                                <strong>{{ $order->settlement_expected_at ? $order->settlement_expected_at->format('M d, h:i A') : 'settlement day' }}</strong> 
                                when the <strong>Accept & Payout</strong> button activates.
                            </p>
                        </div>
                    @else
                        <!-- STAGE 2 & 3: UNLOCKED FULL CUSTOMER DETAILS -->
                        <div class="mb-4">
                            <p class="font-medium text-gray-900">{{ $order->user->first_name }} {{ $order->user->last_name }}</p>
                            <p class="text-gray-500"><a href="mailto:{{ $order->user->email }}" class="hover:underline">{{ $order->user->email }}</a></p>
                        </div>
                        
                        <h4 class="font-medium text-gray-900 mb-1 mt-6">Shipping Address</h4>
                        <address class="text-gray-600 not-italic">
                            {{ $order->address_snapshot['line_1'] ?? '' }}<br>
                            {{ $order->address_snapshot['city'] ?? '' }}, {{ $order->address_snapshot['state'] ?? '' }} - {{ $order->address_snapshot['pincode'] ?? '' }}<br>
                            <abbr title="Phone" class="font-bold">Phone:</abbr> {{ $order->address_snapshot['phone'] ?? $order->user->phone ?? 'N/A' }}
                        </address>

                        @php
                            $rawPhone = $order->address_snapshot['phone'] ?? $order->user->phone ?? '';
                            $cleanPhone = preg_replace('/[^0-9]/', '', $rawPhone);
                            if (strlen($cleanPhone) === 10) {
                                $cleanPhone = '91' . $cleanPhone;
                            }
                            $customerGreeting = $order->user->first_name ?? $order->user->name ?? 'Customer';
                            $waMsg = rawurlencode("Namaste {$customerGreeting}! This is {$order->vendor->shop_name} regarding your Order #{$order->order_number}. We are preparing your garment for dispatch.");
                        @endphp

                        @if(!empty($cleanPhone))
                            <div class="mt-4 pt-4 border-t border-gray-100">
                                <a href="https://wa.me/{{ $cleanPhone }}?text={{ $waMsg }}" target="_blank"
                                   class="inline-flex items-center justify-center gap-2 w-full py-2.5 px-4 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold shadow-xs transition-colors">
                                    <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/></svg>
                                    <span>Message on WhatsApp</span>
                                </a>
                            </div>
                        @endif
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-layouts.vendor>
