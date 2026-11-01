<x-layouts.vendor title="Delivery Fleet & Delivery Boys">
    @php
        $shopName = $vendor->shop_name ?? $vendor->store_name ?? 'Vendor Store';
        $onlineCount = $partners->where('is_online', true)->where('is_busy', false)->count();
        $busyCount = $partners->where('is_busy', true)->count();
        $totalRiders = $partners->count();
        $ordersCount = $activeOrdersNeedingDispatch->count();
    @endphp

    <style>
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
            color: #ffffff !important;
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
            color: #000000 !important;
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
            color: #18181b !important;
            border: 1px solid #e4e4e7;
            border-radius: 9999px;
            font-weight: 700;
        }
    </style>

    <div class="space-y-6 max-w-7xl mx-auto pb-12" x-data="{ 
        assigningPartner: null,
        selectedOrderId: '{{ $activeOrdersNeedingDispatch->first()?->id ?? '' }}',
        dispatching: false,
        successMsg: null,
        errorMsg: null,
        assignPartner(partner) {
            this.assigningPartner = partner;
        },
        confirmDispatch() {
            if (!this.selectedOrderId || !this.assigningPartner) return;
            this.dispatching = true;
            this.errorMsg = null;
            
            fetch(`/vendor/orders/${this.selectedOrderId}/assign-partner`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name=\'csrf-token\']').getAttribute('content'),
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    delivery_partner_id: this.assigningPartner.id
                })
            })
            .then(r => r.json())
            .then(data => {
                this.dispatching = false;
                if (data.status === 'success') {
                    this.successMsg = data.message;
                    setTimeout(() => window.location.reload(), 1500);
                } else {
                    this.errorMsg = data.error || 'Failed to dispatch order.';
                }
            })
            .catch(err => {
                this.dispatching = false;
                this.errorMsg = 'Network error. Please try again.';
            });
        }
    }">

        <!-- 1. Header -->
        <div class="bw-card p-6 flex flex-col md:flex-row md:items-center justify-between gap-5">
            <div class="space-y-1">
                <div class="flex items-center gap-2">
                    <span class="bw-badge text-[10px] font-mono uppercase tracking-wider px-2.5 py-0.5">
                        ⚡ Quick Commerce Logistics
                    </span>
                    <span class="inline-flex items-center gap-1.5 text-[11px] font-bold text-emerald-800 bg-emerald-100 px-2.5 py-0.5 rounded-full border border-emerald-300">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-600 animate-pulse"></span>
                        Live Fleet Active
                    </span>
                </div>
                <h1 class="text-2xl sm:text-3xl font-black text-black tracking-tight">
                    Delivery Fleet &amp; Delivery Boys
                </h1>
                <p class="text-xs text-zinc-500 font-medium">
                    Real-time Kishangarh delivery partners available for instant pickup and door-to-door food &amp; groceries dispatch.
                </p>
            </div>

            <div class="flex items-center gap-2.5">
                <a href="{{ route('vendor.orders.index') }}" class="bw-btn-white px-3.5 py-2.5 text-xs font-bold flex items-center gap-1.5">
                    <span>Kitchen Orders</span>
                    <span>&rarr;</span>
                </a>
                <a href="{{ route('vendor.dashboard') }}" class="bw-btn-black px-4 py-2.5 text-xs font-bold flex items-center gap-1.5">
                    <span>Back to Dashboard</span>
                </a>
            </div>
        </div>

        <!-- Notification Alerts -->
        <div x-show="successMsg" x-cloak class="p-4 bg-emerald-100 text-emerald-950 border border-emerald-300 rounded-xl text-xs sm:text-sm font-bold flex items-center justify-between">
            <span x-text="'✓ ' + successMsg"></span>
        </div>
        <div x-show="errorMsg" x-cloak class="p-4 bg-rose-100 text-rose-950 border border-rose-300 rounded-xl text-xs sm:text-sm font-bold flex items-center justify-between">
            <span x-text="'✕ ' + errorMsg"></span>
        </div>

        <!-- 2. Fleet Stats Grid -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
            <div class="bw-card p-4 sm:p-5">
                <div class="text-[10px] font-mono uppercase tracking-wider text-zinc-400 font-bold">Total Fleet</div>
                <div class="text-2xl sm:text-3xl font-black text-black mt-1">{{ $totalRiders }}</div>
                <div class="text-[11px] text-zinc-500 mt-0.5">Verified riders in Kishangarh</div>
            </div>

            <div class="bw-card p-4 sm:p-5 border-emerald-200">
                <div class="text-[10px] font-mono uppercase tracking-wider text-emerald-700 font-bold flex items-center gap-1">
                    <span class="w-2 h-2 rounded-full bg-emerald-600 animate-ping"></span>
                    Online &amp; Ready
                </div>
                <div class="text-2xl sm:text-3xl font-black text-emerald-900 mt-1">{{ $onlineCount }}</div>
                <div class="text-[11px] text-emerald-700 mt-0.5">Ready for instant dispatch</div>
            </div>

            <div class="bw-card p-4 sm:p-5">
                <div class="text-[10px] font-mono uppercase tracking-wider text-amber-700 font-bold">On Active Delivery</div>
                <div class="text-2xl sm:text-3xl font-black text-amber-900 mt-1">{{ $busyCount }}</div>
                <div class="text-[11px] text-zinc-500 mt-0.5">Currently on the road</div>
            </div>

            <div class="bw-card p-4 sm:p-5">
                <div class="text-[10px] font-mono uppercase tracking-wider text-zinc-400 font-bold">Orders Needing Dispatch</div>
                <div class="text-2xl sm:text-3xl font-black text-black mt-1">{{ $ordersCount }}</div>
                <div class="text-[11px] text-zinc-500 mt-0.5">Ready to assign</div>
            </div>
        </div>

        <!-- 3. Active Orders Ready for Dispatch Banner -->
        @if($ordersCount > 0)
        <div class="bw-card p-5 border-2 border-black bg-zinc-50 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-black text-white flex items-center justify-center text-lg font-black shrink-0">
                    🛵
                </div>
                <div>
                    <h3 class="text-sm font-black text-black">
                        {{ $ordersCount }} {{ Str::plural('Order', $ordersCount) }} Waiting for Courier Pickup
                    </h3>
                    <p class="text-xs text-zinc-600">
                        Select any active rider below to assign pickup and send them instant audio/push alert.
                    </p>
                </div>
            </div>
            <a href="{{ route('vendor.orders.index', ['status' => 'pending']) }}" class="bw-btn-black px-4 py-2 text-xs font-bold shrink-0 text-center">
                Review Orders &rarr;
            </a>
        </div>
        @endif

        <!-- 4. Delivery Boys / Fleet Listing Cards -->
        <div class="bw-card p-6 space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-zinc-200">
                <div class="flex items-center gap-2">
                    <span class="text-lg">🛵</span>
                    <h2 class="text-base font-black text-black uppercase tracking-wider">
                        Available Delivery Boys &amp; Fleet Partners
                    </h2>
                </div>
                <span class="text-xs text-zinc-500 font-mono">Sorted by proximity</span>
            </div>

            @if($partners->isEmpty())
                <div class="text-center py-12 px-4 bg-zinc-50 rounded-xl border border-dashed border-zinc-300">
                    <p class="text-sm font-bold text-black">No delivery partners registered in this zone yet.</p>
                    <p class="text-xs text-zinc-500 mt-1">Delivery riders can register at <a href="{{ route('delivery.onboarding') }}" target="_blank" class="text-black underline font-bold">/delivery-partner</a> to start taking orders.</p>
                </div>
            @else
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @foreach($partners as $dp)
                    @php
                        $cleanPhone = preg_replace('/[^0-9]/', '', $dp->phone);
                        if (strlen($cleanPhone) === 10) { $cleanPhone = '91' . $cleanPhone; }
                    @endphp
                    <div class="p-5 rounded-2xl border border-zinc-200 bg-white hover:border-black transition-all flex flex-col justify-between gap-4 shadow-2xs">
                        <div class="flex items-start justify-between gap-3">
                            <div class="flex items-start gap-3.5">
                                <div class="relative w-12 h-12 rounded-2xl bg-zinc-100 border border-zinc-300 flex items-center justify-center text-xl shrink-0">
                                    🛵
                                    @if($dp->is_online && !$dp->is_busy)
                                        <span class="absolute -top-1 -right-1 w-3.5 h-3.5 rounded-full bg-emerald-500 border-2 border-white animate-pulse" title="Online & Available"></span>
                                    @elseif($dp->is_busy)
                                        <span class="absolute -top-1 -right-1 w-3.5 h-3.5 rounded-full bg-amber-500 border-2 border-white" title="On Delivery"></span>
                                    @else
                                        <span class="absolute -top-1 -right-1 w-3.5 h-3.5 rounded-full bg-zinc-400 border-2 border-white" title="Offline"></span>
                                    @endif
                                </div>
                                <div class="space-y-1">
                                    <div class="flex items-center gap-2 flex-wrap">
                                        <h3 class="font-black text-sm text-black">{{ $dp->name }}</h3>
                                        @if($dp->is_online && !$dp->is_busy)
                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-black bg-emerald-100 text-emerald-900 border border-emerald-300">
                                                Online &amp; Ready
                                            </span>
                                        @elseif($dp->is_busy)
                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-black bg-amber-100 text-amber-900 border border-amber-300">
                                                On Delivery
                                            </span>
                                        @else
                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-black bg-zinc-100 text-zinc-700 border border-zinc-300">
                                                Offline
                                            </span>
                                        @endif
                                    </div>
                                    
                                    <div class="text-xs text-zinc-600 flex items-center gap-2 flex-wrap">
                                        <span>🏍️ {{ ucfirst($dp->vehicle_type ?? 'Bike') }}: <strong class="text-black font-mono">{{ $dp->vehicle_number ?? 'RJ-21-SD-4091' }}</strong></span>
                                        <span>•</span>
                                        <span>★ <strong class="text-black">{{ number_format($dp->rating, 2) }}</strong> ({{ $dp->total_deliveries }} completed)</span>
                                    </div>

                                    <div class="text-xs text-zinc-500 flex items-center gap-2 pt-0.5">
                                        <span>📍 <strong>{{ $dp->distance_km }} km</strong> away</span>
                                        <span>•</span>
                                        <span>⏱️ ETA: ~<strong>{{ $dp->eta_minutes }} mins</strong></span>
                                        @if($dp->city)
                                            <span>•</span>
                                            <span>{{ $dp->city }}</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Action Bar: Call, WhatsApp & Assign -->
                        <div class="pt-3 border-t border-zinc-100 flex items-center justify-between gap-2 flex-wrap">
                            <div class="flex items-center gap-2">
                                <a href="tel:{{ $dp->phone }}" 
                                   class="bw-btn-white px-3 py-1.5 text-xs font-bold flex items-center gap-1.5"
                                   title="Call Delivery Boy">
                                    <span>📞 Call</span>
                                    <span class="font-mono text-[11px] text-zinc-500">{{ $dp->phone }}</span>
                                </a>

                                @if($cleanPhone)
                                <a href="https://wa.me/{{ $cleanPhone }}?text={{ rawurlencode('Namaste ' . $dp->name . '! I have an order ready for pickup at ' . $shopName) }}" 
                                   target="_blank" 
                                   rel="noopener noreferrer"
                                   class="px-3 py-1.5 rounded-xl bg-[#25D366] text-white font-bold text-xs flex items-center gap-1 shadow-2xs hover:opacity-90">
                                    <span>💬 WhatsApp</span>
                                </a>
                                @endif
                            </div>

                            @if($ordersCount > 0 && $dp->is_online && !$dp->is_busy)
                                <button type="button" 
                                        @click="assignPartner({{ Js::from($dp) }})"
                                        class="bw-btn-black px-4 py-1.5 text-xs font-black cursor-pointer shadow-xs">
                                    ⚡ Assign Order
                                </button>
                            @elseif($dp->is_busy)
                                <span class="text-xs font-mono text-amber-700 bg-amber-50 px-2.5 py-1 rounded-lg border border-amber-200">
                                    Busy on delivery
                                </span>
                            @else
                                <span class="text-xs text-zinc-400">
                                    Currently offline
                                </span>
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>
            @endif
        </div>

        <!-- 5. Assign Modal -->
        <div x-show="assigningPartner" x-cloak class="fixed inset-0 z-50 bg-black/80 flex items-center justify-center p-4 backdrop-blur-xs" @click.self="assigningPartner = null">
            <div class="bg-white rounded-2xl max-w-md w-full p-6 border border-black space-y-4 relative shadow-2xl">
                <div class="flex items-center justify-between pb-3 border-b border-zinc-200">
                    <div class="flex items-center gap-2">
                        <span class="text-lg">🛵</span>
                        <h3 class="text-sm font-black text-black">Assign Delivery Partner</h3>
                    </div>
                    <button type="button" @click="assigningPartner = null" class="w-7 h-7 rounded-lg bg-zinc-100 hover:bg-black hover:text-white flex items-center justify-center text-xs font-black">✕</button>
                </div>

                <div class="space-y-3">
                    <div class="p-3 bg-zinc-50 rounded-xl border border-zinc-200 space-y-1">
                        <div class="text-xs text-zinc-500 font-bold uppercase tracking-wider">Assigning To:</div>
                        <div class="font-black text-sm text-black" x-text="assigningPartner ? assigningPartner.name : ''"></div>
                        <div class="text-xs text-zinc-600 font-mono" x-text="assigningPartner ? (assigningPartner.vehicle_number + ' • ' + assigningPartner.distance_km + ' km away') : ''"></div>
                    </div>

                    <div class="space-y-1.5">
                        <label class="text-xs font-black text-black uppercase tracking-wider">Select Pending Order:</label>
                        <select x-model="selectedOrderId" class="w-full px-3 py-2 text-xs font-bold rounded-xl border border-zinc-300 bg-white text-black focus:outline-none focus:border-black">
                            @foreach($activeOrdersNeedingDispatch as $aOrder)
                                <option value="{{ $aOrder->id }}">
                                    Order #{{ $aOrder->order_number }} • ₹{{ number_format($aOrder->total_amount / 100, 2) }} • {{ $aOrder->user?->name ?? 'Customer' }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <p class="text-[11px] text-zinc-500 leading-relaxed">
                        The delivery partner will receive an instant ring alert with store pickup directions and the customer delivery destination.
                    </p>
                </div>

                <div class="flex items-center justify-end gap-2.5 pt-3 border-t border-zinc-100">
                    <button type="button" @click="assigningPartner = null" class="bw-btn-white px-4 py-2 text-xs font-bold">
                        Cancel
                    </button>
                    <button type="button" 
                            @click="confirmDispatch()" 
                            :disabled="dispatching || !selectedOrderId"
                            class="bw-btn-black px-5 py-2 text-xs font-black cursor-pointer shadow-xs flex items-center gap-1.5">
                        <span x-show="!dispatching">Confirm Dispatch</span>
                        <span x-show="dispatching">Dispatching...</span>
                    </button>
                </div>
            </div>
        </div>

    </div>
</x-layouts.vendor>

