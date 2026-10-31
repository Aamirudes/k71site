<x-layouts.public title="My Orders">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 pt-8 pb-24" x-data="{ filter: 'all' }">
        
        <!-- Page Header -->
        <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4 mb-8">
            <div>
                <span class="text-xs uppercase tracking-widest font-bold text-brand-sage">Account Center</span>
                <h1 class="text-3xl sm:text-4xl font-serif font-bold text-brand-text tracking-tight mt-1">My Orders</h1>
                <p class="text-brand-muted text-sm sm:text-base mt-1">Click on any order to view its delivery timeline, tracking and full invoice details.</p>
            </div>
            
            @if(!$orders->isEmpty())
                <!-- Status Filter Pills -->
                <div class="flex items-center gap-1.5 p-1.5 bg-white rounded-2xl border border-gray-100 shadow-xs overflow-x-auto no-scrollbar">
                    <button type="button" @click="filter = 'all'" 
                            :class="filter === 'all' ? 'bg-[#1F2A26] text-white shadow-xs' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-50'"
                            class="px-3.5 py-1.5 rounded-xl text-xs font-semibold transition-all whitespace-nowrap">
                        All Orders ({{ $orders->total() }})
                    </button>
                    <button type="button" @click="filter = 'active'" 
                            :class="filter === 'active' ? 'bg-[#1F2A26] text-white shadow-xs' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-50'"
                            class="px-3.5 py-1.5 rounded-xl text-xs font-semibold transition-all whitespace-nowrap">
                        In Transit / Active
                    </button>
                    <button type="button" @click="filter = 'delivered'" 
                            :class="filter === 'delivered' ? 'bg-[#1F2A26] text-white shadow-xs' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-50'"
                            class="px-3.5 py-1.5 rounded-xl text-xs font-semibold transition-all whitespace-nowrap">
                        Delivered
                    </button>
                    <button type="button" @click="filter = 'cancelled'" 
                            :class="filter === 'cancelled' ? 'bg-[#1F2A26] text-white shadow-xs' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-50'"
                            class="px-3.5 py-1.5 rounded-xl text-xs font-semibold transition-all whitespace-nowrap">
                        Cancelled
                    </button>
                </div>
            @endif
        </div>

        <!-- Flash Notifications -->
        @if(session('success'))
            <div class="mb-6 p-4 bg-emerald-50/90 border border-emerald-200 text-emerald-800 rounded-2xl flex items-center justify-between shadow-xs">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-full bg-emerald-100 flex items-center justify-center text-emerald-700 font-bold shrink-0">✓</div>
                    <p class="font-medium text-sm">{{ session('success') }}</p>
                </div>
            </div>
        @endif

        @if(session('error'))
            <div class="mb-6 p-4 bg-rose-50/90 border border-rose-200 text-rose-800 rounded-2xl flex items-center justify-between shadow-xs">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-full bg-rose-100 flex items-center justify-center text-rose-700 font-bold shrink-0">!</div>
                    <p class="font-medium text-sm">{{ session('error') }}</p>
                </div>
            </div>
        @endif

        @if($orders->isEmpty())
            <!-- Empty State -->
            <div class="bg-white rounded-3xl p-12 sm:p-16 text-center border border-gray-100 shadow-sm flex flex-col items-center justify-center max-w-xl mx-auto my-8">
                <div class="w-20 h-20 bg-brand-surface rounded-full flex items-center justify-center mb-5 shadow-xs">
                    <svg class="h-9 w-9 text-brand-sage" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                </div>
                <h2 class="text-2xl font-serif font-bold text-brand-text mb-2">No orders placed yet</h2>
                <p class="text-gray-500 mb-6 text-sm sm:text-base leading-relaxed max-w-sm">When you shop from our curated independent designers, your order progress and tracking will appear right here.</p>
                <a href="{{ route('catalog.index') }}" class="px-8 py-3.5 bg-[#1F2A26] text-white font-sans font-bold text-xs uppercase tracking-widest rounded-full hover:bg-brand-sage transition-colors shadow-md">
                    Explore Collections
                </a>
            </div>
        @else
            <!-- Compact & Clickable Orders List -->
            <div class="space-y-4">
                @foreach($orders as $order)
                    @php
                        $canCancel = in_array($order->status, ['pending', 'processing']) && ($order->vendor?->settings?->allow_customer_cancellation ?? true);
                        $isOutForDelivery = ($order->shipment && $order->shipment->status === 'out_for_delivery') || ($order->status === 'out_for_delivery');
                        $isDelivered = ($order->status === 'delivered') || ($order->shipment && $order->shipment->status === 'delivered');
                        $isActive = in_array($order->status, ['pending', 'processing', 'shipped', 'out_for_delivery']) && !$isDelivered;
                        
                        // Status Progress Mapping
                        $statusStep = match($order->status) {
                            'pending' => 1,
                            'processing' => 2,
                            'shipped', 'out_for_delivery' => 3,
                            'delivered' => 4,
                            default => 0,
                        };
                        if ($isDelivered) {
                            $statusStep = 4;
                        }

                        $firstItem = $order->items->first();
                        $itemCount = $order->items->sum('quantity');
                        $extraItemsCount = $order->items->count() - 1;
                    @endphp

                    <div x-show="filter === 'all' || 
                                (filter === 'active' && {{ $isActive ? 'true' : 'false' }}) || 
                                (filter === 'delivered' && {{ $isDelivered ? 'true' : 'false' }}) || 
                                (filter === 'cancelled' && '{{ $order->status }}' === 'cancelled')"
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0 transform scale-98"
                         x-transition:enter-end="opacity-100 transform scale-100"
                         class="bg-white rounded-3xl border border-gray-100 shadow-xs hover:shadow-md transition-all duration-300 overflow-hidden"
                         x-data="{ expanded: false, showCancelModal: false }">
                        
                        <!-- ======================================================== -->
                        <!-- COMPACT CLICKABLE ORDER CARD ROW -->
                        <!-- Clicking anywhere on this header toggles full details -->
                        <!-- ======================================================== -->
                        <div @click="expanded = !expanded" 
                             role="button"
                             tabindex="0"
                             @keydown.enter="expanded = !expanded"
                             @keydown.space.prevent="expanded = !expanded"
                             class="p-4 sm:p-5 flex items-center justify-between gap-4 cursor-pointer select-none group transition-colors duration-150 outline-none focus:outline-none focus:ring-0"
                             :class="expanded ? 'bg-stone-50/60' : 'hover:bg-stone-50/40'">
                            
                            <!-- Left: Thumbnail & Info -->
                            <div class="flex items-center gap-3.5 sm:gap-5 min-w-0 flex-1">
                                <!-- Product Thumbnail with Multi-Item Counter -->
                                <div class="relative w-16 h-18 sm:w-20 sm:h-22 rounded-2xl bg-stone-100 overflow-hidden shrink-0 border border-gray-200/60 shadow-2xs group-hover:scale-102 transition-transform duration-200">
                                    @if($firstItem && $firstItem->product)
                                        <img src="{{ $firstItem->product->primary_image_url }}" 
                                             alt="{{ $firstItem->product->title ?? 'Order Item' }}" 
                                             class="w-full h-full object-cover"
                                             onerror="this.onerror=null; this.src='{{ asset('images/product-placeholder.svg') }}';">
                                    @else
                                        <div class="w-full h-full flex items-center justify-center text-stone-400 text-xl font-bold bg-stone-100">
                                            🛍️
                                        </div>
                                    @endif

                                    @if($extraItemsCount > 0)
                                        <div class="absolute bottom-1 right-1 bg-black/75 backdrop-blur-xs text-white text-[10px] font-bold px-1.5 py-0.5 rounded-md leading-none shadow-xs">
                                            +{{ $extraItemsCount }}
                                        </div>
                                    @endif
                                </div>

                                <!-- Text Details -->
                                <div class="min-w-0 flex-1">
                                    <!-- Meta strip: Order # & Date -->
                                    <div class="flex flex-wrap items-center gap-x-2.5 gap-y-1 text-xs mb-1">
                                        <span class="font-mono font-bold text-brand-text bg-stone-100 text-stone-800 px-2 py-0.5 rounded-md text-[11px] border border-stone-200/70">
                                            {{ $order->order_number }}
                                        </span>
                                        <span class="text-gray-400 text-xs hidden sm:inline">•</span>
                                        <span class="text-gray-500 text-xs font-medium">
                                            {{ $order->created_at->format('d M, Y') }}
                                        </span>
                                        <span class="text-gray-400 text-xs hidden md:inline">•</span>
                                        <span class="text-gray-500 text-xs truncate hidden md:inline">
                                            Sold by <strong class="text-gray-800">{{ $order->vendor?->shop_name ?? 'K71 Direct Store' }}</strong>
                                        </span>
                                    </div>

                                    <!-- Main Title & Items Count -->
                                    <h3 class="text-sm sm:text-base font-bold text-brand-text truncate group-hover:text-brand-sage transition-colors">
                                        @if($firstItem)
                                            {{ $firstItem->product->title ?? $firstItem->snapshot['title'] ?? 'Fashion Product' }}
                                        @else
                                            Order Item
                                        @endif
                                    </h3>

                                    <!-- Variant / Quantity summary -->
                                    <div class="flex flex-wrap items-center gap-2 mt-1 text-xs">
                                        @if($firstItem && $firstItem->variant && $firstItem->variant->sku)
                                            <span class="text-gray-500 font-medium">
                                                {{ $firstItem->variant->sku }}
                                            </span>
                                            <span class="text-gray-300">•</span>
                                        @endif
                                        <span class="text-gray-500 font-medium">
                                            {{ $itemCount }} {{ Str::plural('item', $itemCount) }}
                                        </span>
                                    </div>

                                    <!-- Mobile Status Badge (shown under title on mobile) -->
                                    <div class="mt-2 sm:hidden flex items-center gap-2">
                                        @if($isDelivered)
                                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200 whitespace-nowrap">
                                                ✓ Delivered
                                            </span>
                                        @elseif($isOutForDelivery)
                                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-amber-50 text-amber-800 border border-amber-200 whitespace-nowrap shadow-2xs">
                                                <span class="relative flex h-2 w-2">
                                                  <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-amber-400 opacity-75"></span>
                                                  <span class="relative inline-flex rounded-full h-2 w-2 bg-amber-600"></span>
                                                </span>
                                                🛵 Out for Delivery
                                            </span>
                                        @elseif($order->status === 'shipped')
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-indigo-50 text-indigo-700 border border-indigo-200">
                                                <span class="relative flex h-1.5 w-1.5">
                                                  <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-indigo-400 opacity-75"></span>
                                                  <span class="relative inline-flex rounded-full h-1.5 w-1.5 bg-indigo-600"></span>
                                                </span>
                                                In Transit
                                            </span>
                                        @elseif($order->status === 'processing')
                                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-sky-50 text-sky-700 border border-sky-200">
                                                Packing
                                            </span>
                                        @elseif($order->status === 'pending')
                                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-amber-100 text-amber-950 border border-amber-300">
                                                Pending
                                            </span>
                                        @elseif($order->status === 'cancelled')
                                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-rose-50 text-rose-800 border border-rose-200">
                                                ✕ Cancelled
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <!-- Right: Status, Price & Interactive Expand Trigger -->
                            <div class="flex items-center gap-3 sm:gap-6 shrink-0">
                                <!-- Desktop Status Badge -->
                                <div class="hidden sm:block text-right">
                                    @if($isDelivered)
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-900 border border-emerald-300 shadow-2xs">
                                            <svg class="w-3.5 h-3.5 text-emerald-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                            Delivered
                                        </span>
                                    @elseif($isOutForDelivery)
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-950 border border-amber-300 shadow-2xs">
                                            <span class="relative flex h-2 w-2">
                                              <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-amber-400 opacity-75"></span>
                                              <span class="relative inline-flex rounded-full h-2 w-2 bg-amber-700"></span>
                                            </span>
                                            🛵 Out for Delivery
                                        </span>
                                    @elseif($order->status === 'shipped')
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-indigo-100 text-indigo-900 border border-indigo-300 shadow-2xs">
                                            <span class="relative flex h-2 w-2">
                                              <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-indigo-400 opacity-75"></span>
                                              <span class="relative inline-flex rounded-full h-2 w-2 bg-indigo-700"></span>
                                            </span>
                                            Shipped · In Transit
                                        </span>
                                    @elseif($order->status === 'processing')
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-sky-100 text-sky-900 border border-sky-300 shadow-2xs">
                                            <svg class="w-3.5 h-3.5 text-sky-700 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                            Processing
                                        </span>
                                    @elseif($order->status === 'pending')
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-950 border border-amber-300 shadow-2xs">
                                            <svg class="w-3.5 h-3.5 text-amber-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                            Pending
                                        </span>
                                    @elseif($order->status === 'cancelled')
                                        <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-bold bg-rose-100 text-rose-900 border border-rose-300 shadow-2xs">
                                            <span>✕</span> Cancelled
                                        </span>
                                    @endif

                                    <div class="text-[11px] text-gray-500 mt-1 font-medium">
                                        @if($order->payment_status === 'paid')
                                            <span class="text-emerald-800 font-semibold">● Paid</span>
                                        @else
                                            <span class="text-amber-900 font-semibold">● Payment Pending</span>
                                        @endif
                                    </div>
                                </div>

                                <!-- Total Price -->
                                <div class="text-right">
                                    <p class="text-base sm:text-lg font-serif font-black text-brand-text">
                                        ₹{{ number_format($order->total_amount / 100, 2) }}
                                    </p>
                                    <p class="text-[11px] text-gray-400 font-medium sm:hidden">
                                        {{ $order->payment_status === 'paid' ? 'Paid' : 'Unpaid' }}
                                    </p>
                                </div>

                                <!-- Chevron / Expand Button -->
                                <div class="flex items-center justify-center w-8 h-8 sm:w-9 sm:h-9 rounded-full bg-stone-100 group-hover:bg-[#1F2A26] text-stone-600 group-hover:text-white transition-all duration-200 shadow-2xs shrink-0">
                                    <svg class="w-4 h-4 transition-transform duration-300"
                                         :class="expanded ? 'rotate-180' : 'rotate-0'"
                                         fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/>
                                    </svg>
                                </div>
                            </div>
                        </div>

                        <!-- ======================================================== -->
                        <!-- REVEALED COMPLETE ORDER DETAILS (Revealed on Click)      -->
                        <!-- ======================================================== -->
                        <div x-show="expanded" 
                             x-collapse 
                             x-cloak 
                             class="border-t border-gray-100 bg-gradient-to-b from-stone-50/50 to-white">
                            
                            <!-- 1. Delivery Progress Stepper (If not cancelled) -->
                            @if($order->status !== 'cancelled')
                                <div class="px-5 sm:px-8 pt-6 pb-4 border-b border-gray-100 bg-white">
                                    <div class="flex items-center justify-between mb-4">
                                        <h4 class="text-xs uppercase tracking-wider font-bold text-gray-400">Order Progress</h4>
                                        <span class="text-xs font-medium text-gray-500">
                                            @if($isDelivered)
                                                Delivered on {{ $order->delivered_at ? \Carbon\Carbon::parse($order->delivered_at)->format('d M, Y') : $order->updated_at->format('d M, Y') }}
                                            @elseif($isOutForDelivery)
                                                <span class="text-amber-700 font-bold inline-flex items-center gap-1">🛵 Out for delivery today with courier</span>
                                            @elseif($order->status === 'shipped')
                                                Package is in transit with courier
                                            @elseif($order->status === 'processing')
                                                Vendor is packing your garments
                                            @else
                                                Order placed & pending verification
                                            @endif
                                        </span>
                                    </div>

                                    <div class="grid grid-cols-4 gap-2 relative max-w-2xl mx-auto py-2">
                                        <!-- Background line -->
                                        <div class="absolute top-5 left-6 right-6 h-1 bg-gray-100 -z-0"></div>
                                        
                                        <!-- Dynamic Progress Fill Line -->
                                        <div class="absolute top-5 left-6 h-1 bg-[#1F2A26] -z-0 transition-all duration-500" 
                                             style="width: {{ $isOutForDelivery ? '82%' : match((int)$statusStep) { 1 => '10%', 2 => '38%', 3 => '70%', 4 => '94%', default => '0%' } }};"></div>

                                        <!-- Step 1: Placed -->
                                        <div class="flex flex-col items-center text-center relative z-10">
                                            <div class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold shadow-2xs {{ $statusStep >= 1 ? 'bg-[#1F2A26] text-white ring-4 ring-white' : 'bg-gray-100 text-gray-400' }}">
                                                ✓
                                            </div>
                                            <span class="text-xs font-bold text-brand-text mt-2">Confirmed</span>
                                        </div>

                                        <!-- Step 2: Processing -->
                                        <div class="flex flex-col items-center text-center relative z-10">
                                            <div class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold shadow-2xs {{ $statusStep >= 2 ? 'bg-[#1F2A26] text-white ring-4 ring-white' : 'bg-gray-100 text-gray-400' }}">
                                                {{ $statusStep >= 2 ? '✓' : '2' }}
                                            </div>
                                            <span class="text-xs font-bold {{ $statusStep >= 2 ? 'text-brand-text' : 'text-gray-400' }} mt-2">Packing</span>
                                        </div>

                                        <!-- Step 3: Shipped / Out for Delivery -->
                                        <div class="flex flex-col items-center text-center relative z-10">
                                            <div class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold shadow-2xs {{ $statusStep >= 3 ? ($isOutForDelivery ? 'bg-amber-600 text-white ring-4 ring-amber-100' : 'bg-[#1F2A26] text-white ring-4 ring-white') : 'bg-gray-100 text-gray-400' }}">
                                                {{ $isOutForDelivery ? '🛵' : ($statusStep >= 3 ? '✓' : '3') }}
                                            </div>
                                            <span class="text-xs font-bold {{ $isOutForDelivery ? 'text-amber-800' : ($statusStep >= 3 ? 'text-brand-text' : 'text-gray-400') }} mt-2">
                                                {{ $isOutForDelivery ? 'Out for Delivery' : 'Shipped' }}
                                            </span>
                                        </div>

                                        <!-- Step 4: Delivered -->
                                        <div class="flex flex-col items-center text-center relative z-10">
                                            <div class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold shadow-2xs {{ $statusStep >= 4 ? 'bg-emerald-600 text-white ring-4 ring-white' : 'bg-gray-100 text-gray-400' }}">
                                                {{ $statusStep >= 4 ? '✓' : '4' }}
                                            </div>
                                            <span class="text-xs font-bold {{ $statusStep >= 4 ? 'text-emerald-700' : 'text-gray-400' }} mt-2">Delivered</span>
                                        </div>
                                    </div>
                                </div>
                            @else
                                <!-- Cancelled Banner -->
                                <div class="px-5 sm:px-8 py-4 bg-rose-50/60 border-b border-rose-100 flex items-center gap-3 text-xs text-rose-800">
                                    <span class="font-bold">Order Cancelled:</span>
                                    <span>Cancelled by {{ ucfirst($order->cancelled_by ?? 'vendor') }}. {{ $order->cancel_reason ? 'Reason: ' . $order->cancel_reason : '' }}</span>
                                </div>
                            @endif

                            <!-- 2. Logistics & Tracking Strip (if courier or AWB exists) -->
                            @if($order->tracking_number || $order->tracking_url)
                                <div class="mx-4 sm:mx-8 my-4 sm:my-5 p-4 rounded-2xl bg-indigo-50/60 border border-indigo-100/90 flex flex-col sm:flex-row sm:items-center justify-between gap-3.5">
                                    <div class="flex items-start sm:items-center gap-3 min-w-0 flex-1">
                                        <div class="w-10 h-10 rounded-xl bg-white border border-indigo-100 flex items-center justify-center text-indigo-600 shadow-2xs shrink-0 mt-0.5 sm:mt-0">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0"/></svg>
                                        </div>
                                        <div class="min-w-0 flex-1">
                                            <div class="flex flex-wrap items-center gap-1.5 sm:gap-2">
                                                <span class="text-xs font-bold text-indigo-950 uppercase tracking-wider">Tracking AWB:</span>
                                                <span class="font-mono font-bold text-indigo-900 text-sm bg-white px-2 py-0.5 rounded border border-indigo-200/60 select-all">{{ $order->tracking_number ?? 'In Transit' }}</span>
                                                @if($isOutForDelivery)
                                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-amber-100 text-amber-900 border border-amber-200 whitespace-nowrap shadow-2xs">
                                                        🛵 Out for Delivery
                                                    </span>
                                                @elseif($order->shipment && $order->shipment->status)
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-bold uppercase bg-indigo-100 text-indigo-800 border border-indigo-200 whitespace-nowrap">
                                                        {{ str_replace('_', ' ', $order->shipment->status) }}
                                                    </span>
                                                @endif
                                            </div>
                                            <p class="text-xs text-indigo-700/80 mt-1">Carrier: <strong class="text-indigo-950">{{ $order->courier_name ?? 'Standard Courier Service' }}</strong></p>
                                        </div>
                                    </div>

                                    @if($order->tracking_url)
                                        <a href="{{ $order->tracking_url }}" target="_blank" @click.stop 
                                           class="inline-flex items-center justify-center gap-1.5 px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-bold transition-colors shadow-xs shrink-0 w-full sm:w-auto">
                                            <span>Track Live Shipment</span>
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                        </a>
                                    @endif
                                </div>
                            @endif

                            <!-- 3. Complete Items List -->
                            <div class="px-5 sm:px-8 py-5">
                                <h4 class="text-xs uppercase tracking-wider font-bold text-gray-400 mb-3">Items in this order</h4>
                                <div class="divide-y divide-gray-100 bg-white rounded-2xl border border-gray-100 px-4 sm:px-5">
                                    @foreach($order->items as $item)
                                        <div class="flex items-center gap-4 py-4">
                                            <!-- Thumbnail -->
                                            <div class="w-14 h-16 sm:w-16 sm:h-20 bg-stone-100 rounded-xl overflow-hidden shrink-0 border border-gray-200/50 shadow-2xs">
                                                @if($item->product)
                                                    <img src="{{ $item->product->primary_image_url }}" 
                                                         alt="{{ $item->product->title ?? 'Product' }}"
                                                         class="w-full h-full object-cover"
                                                         onerror="this.onerror=null; this.src='{{ asset('images/product-placeholder.svg') }}';">
                                                @else
                                                    <div class="w-full h-full flex items-center justify-center text-stone-300 text-lg font-bold">
                                                        🛍️
                                                    </div>
                                                @endif
                                            </div>

                                            <!-- Info -->
                                            <div class="flex-1 min-w-0">
                                                <h5 class="font-bold text-sm text-brand-text truncate">
                                                    {{ $item->product->title ?? $item->snapshot['title'] ?? 'Fashion Garment' }}
                                                </h5>
                                                <div class="flex flex-wrap items-center gap-2 mt-1">
                                                    @if($item->variant && $item->variant->sku)
                                                        <span class="inline-flex items-center px-2 py-0.5 rounded-md bg-stone-100 text-stone-700 text-[11px] font-medium">
                                                            {{ $item->variant->sku }}
                                                        </span>
                                                    @elseif(!empty($item->snapshot['variant_sku']))
                                                        <span class="inline-flex items-center px-2 py-0.5 rounded-md bg-stone-100 text-stone-700 text-[11px] font-medium">
                                                            {{ $item->snapshot['variant_sku'] }}
                                                        </span>
                                                    @endif
                                                    <span class="text-xs text-gray-500 font-medium">Qty: {{ $item->quantity }}</span>
                                                </div>
                                            </div>

                                            <!-- Price -->
                                            <div class="text-right shrink-0">
                                                <p class="text-sm font-bold text-brand-text">₹{{ number_format($item->total_price / 100, 2) }}</p>
                                                @if($item->quantity > 1)
                                                    <p class="text-[11px] text-gray-400">₹{{ number_format($item->unit_price / 100, 2) }} each</p>
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            <!-- 4. Shipping Address & Cost Breakdown (2-Column Grid) -->
                            <div class="px-5 sm:px-8 pb-5 grid grid-cols-1 md:grid-cols-2 gap-4">
                                <!-- Delivery Address Card -->
                                <div class="bg-white rounded-2xl border border-gray-100 p-4 sm:p-5">
                                    <div class="flex items-center gap-2 text-xs uppercase tracking-wider font-bold text-gray-400 mb-2">
                                        <svg class="w-4 h-4 text-brand-sage" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                        <span>Shipping Address</span>
                                    </div>
                                    @if($order->address_snapshot)
                                        <div class="text-xs text-gray-600 space-y-0.5 leading-relaxed">
                                            <p class="font-bold text-gray-800">{{ $order->address_snapshot['line_1'] ?? '' }}</p>
                                            <p>{{ $order->address_snapshot['city'] ?? '' }}, {{ $order->address_snapshot['state'] ?? '' }} - {{ $order->address_snapshot['pincode'] ?? '' }}</p>
                                            @if(!empty($order->address_snapshot['phone']))
                                                <p class="text-gray-500 font-mono mt-1">Phone: {{ $order->address_snapshot['phone'] }}</p>
                                            @endif
                                        </div>
                                    @else
                                        <p class="text-xs text-gray-400 italic">No delivery address recorded.</p>
                                    @endif

                                    <div class="mt-4 pt-3 border-t border-gray-100 flex items-center justify-between text-xs">
                                        <span class="text-gray-400 font-medium">Vendor Store:</span>
                                        @if($order->vendor)
                                            <a href="{{ $order->vendor->storefront_url }}" @click.stop class="font-bold text-brand-text hover:text-brand-sage underline decoration-dotted">
                                                {{ $order->vendor->shop_name }} ↗
                                            </a>
                                        @else
                                            <span class="font-bold text-brand-text">K71 Direct Store</span>
                                        @endif
                                    </div>
                                </div>

                                <!-- Payment Breakdown Card -->
                                <div class="bg-white rounded-2xl border border-gray-100 p-4 sm:p-5">
                                    <div class="flex items-center gap-2 text-xs uppercase tracking-wider font-bold text-gray-400 mb-2">
                                        <svg class="w-4 h-4 text-brand-sage" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                                        <span>Payment Breakdown</span>
                                    </div>
                                    <dl class="space-y-1.5 text-xs text-gray-600">
                                        <div class="flex justify-between">
                                            <dt>Subtotal</dt>
                                            <dd class="font-medium text-gray-900">₹{{ number_format($order->subtotal / 100, 2) }}</dd>
                                        </div>
                                        <div class="flex justify-between">
                                            <dt>Shipping</dt>
                                            <dd class="font-medium text-gray-900">₹{{ number_format($order->shipping_amount / 100, 2) }}</dd>
                                        </div>
                                        @if($order->discount_amount > 0)
                                            <div class="flex justify-between text-emerald-700 font-semibold">
                                                <dt>Discount</dt>
                                                <dd>-₹{{ number_format($order->discount_amount / 100, 2) }}</dd>
                                            </div>
                                        @endif
                                        <div class="flex justify-between font-bold text-brand-text border-t border-gray-100 pt-2 text-sm">
                                            <dt>Total Amount</dt>
                                            <dd>₹{{ number_format($order->total_amount / 100, 2) }}</dd>
                                        </div>
                                    </dl>

                                    <div class="mt-3 pt-2.5 border-t border-gray-100 flex items-center justify-between text-xs">
                                        <span class="text-gray-400 font-medium">Method:</span>
                                        <span class="font-bold text-gray-700">
                                            @if($order->payment_method === 'phonepe')
                                                ⚡ PhonePe Gateway
                                            @elseif($order->payment_method === 'cod')
                                                💵 Cash on Delivery
                                            @elseif($order->payment_method === 'upi')
                                                📱 UPI Transfer
                                            @else
                                                {{ strtoupper($order->payment_method) }}
                                            @endif
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <!-- 5. Interactive Action Strip -->
                            <div class="bg-stone-100/70 px-5 sm:px-8 py-4 border-t border-gray-100 flex flex-wrap items-center justify-between gap-3">
                                <div class="flex items-center gap-2">
                                    <span class="text-xs text-gray-400 font-medium">Order Reference:</span>
                                    <span class="text-xs font-mono font-bold text-gray-700 bg-white px-2 py-0.5 rounded border border-gray-200/80">{{ $order->order_number }}</span>
                                </div>

                                <div class="flex flex-wrap items-center gap-2.5">
                                    <!-- Retry / Complete Payment button -->
                                    @if($order->payment_status === 'pending' && $order->payment_method === 'phonepe' && $order->status !== 'cancelled')
                                        <a href="{{ route('payment.phonepe.initiate', $order) }}" @click.stop 
                                           class="px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded-xl text-xs font-bold transition-colors shadow-xs flex items-center gap-1.5">
                                            <span>⚡ Complete Payment</span>
                                        </a>
                                    @endif

                                    <!-- Download Invoice -->
                                    <a href="{{ route('orders.invoice', $order) }}" target="_blank" @click.stop 
                                       class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-white border border-gray-200 text-gray-700 hover:bg-gray-50 text-xs font-bold transition-colors shadow-2xs">
                                        <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                        <span>Invoice</span>
                                    </a>

                                    <!-- Full Page Permalinks -->
                                    <a href="{{ route('orders.show', $order) }}" @click.stop 
                                       class="inline-flex items-center gap-1 px-3.5 py-2 rounded-xl bg-white border border-gray-200 text-gray-700 hover:bg-gray-50 text-xs font-bold transition-colors shadow-2xs">
                                        <span>Full Page</span>
                                        <svg class="w-3 h-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                    </a>

                                    <!-- Cancel Order Trigger -->
                                    @if($canCancel)
                                        <button type="button" @click.stop="showCancelModal = true" 
                                                class="px-3.5 py-2 rounded-xl text-xs font-bold text-rose-600 hover:bg-rose-50 border border-transparent hover:border-rose-200 transition-colors">
                                            Cancel Order
                                        </button>
                                    @endif
                                </div>
                            </div>

                        </div>

                        <!-- ======================================================== -->
                        <!-- CANCELLATION MODAL                                       -->
                        <!-- ======================================================== -->
                        @if($canCancel)
                            <div x-show="showCancelModal" 
                                 x-cloak 
                                 class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs"
                                 @keydown.escape.window="showCancelModal = false"
                                 @click.stop>
                                <div class="bg-white rounded-3xl p-6 sm:p-8 max-w-md w-full shadow-2xl border border-gray-100" @click.away="showCancelModal = false">
                                    <div class="w-12 h-12 rounded-2xl bg-rose-50 text-rose-600 flex items-center justify-center text-xl mb-4 font-bold border border-rose-100">
                                        ✕
                                    </div>
                                    <h3 class="text-xl font-serif font-bold text-brand-text mb-1.5">Cancel Order #{{ $order->order_number }}?</h3>
                                    <p class="text-sm text-gray-500 mb-6 leading-relaxed">Are you sure you want to cancel this order? The reserved inventory will be restored back to the store.</p>
                                    
                                    <form action="{{ route('orders.cancel', $order) }}" method="POST" class="space-y-4">
                                        @csrf
                                        <div>
                                            <label for="reason_{{ $order->id }}" class="block text-xs font-bold uppercase tracking-wider text-gray-500 mb-1.5">Reason for Cancellation</label>
                                            <select name="reason" id="reason_{{ $order->id }}" class="w-full rounded-xl border border-gray-200 px-3.5 py-2.5 text-sm focus:border-brand-sage focus:ring-brand-sage">
                                                <option value="Changed my mind">Changed my mind / Found another item</option>
                                                <option value="Ordered wrong size or product">Ordered wrong size / color / product</option>
                                                <option value="Need to change shipping address">Need to change shipping address</option>
                                                <option value="Delivery time is too long">Delivery time is too long</option>
                                                <option value="Other">Other reason</option>
                                            </select>
                                        </div>

                                        <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100">
                                            <button type="button" @click="showCancelModal = false" class="px-5 py-2.5 text-sm font-semibold text-gray-700 hover:bg-gray-100 rounded-xl transition-colors">
                                                Keep Order
                                            </button>
                                            <button type="submit" class="px-5 py-2.5 text-sm font-semibold text-white bg-rose-600 hover:bg-rose-700 rounded-xl transition-colors shadow-md">
                                                Yes, Cancel Order
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        @endif

                    </div>
                @endforeach
            </div>

            <!-- Pagination -->
            <div class="mt-10">
                {{ $orders->links() }}
            </div>
        @endif
    </div>
</x-layouts.public>
