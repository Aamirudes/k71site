<x-layouts.public title="Order {{ $order->order_number }}">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 pt-8 pb-24" x-data="{ showCancelModal: false }">
        
        <!-- Header -->
        <div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <a href="{{ route('orders.index') }}" class="text-xs font-bold text-brand-sage hover:text-brand-text flex items-center gap-1.5 mb-2 transition-colors uppercase tracking-wider">
                    ← Back to My Orders
                </a>
                <h1 class="text-3xl font-serif font-bold text-brand-text">Order {{ $order->order_number }}</h1>
                <p class="text-sm text-gray-500 mt-1">Placed on {{ $order->created_at->format('F d, Y · h:i A') }}</p>
            </div>
            <div class="flex items-center gap-2.5">
                <a href="{{ route('orders.invoice', $order) }}" target="_blank" 
                   class="inline-flex items-center gap-2 rounded-2xl bg-white px-4 py-2.5 text-xs font-bold text-gray-800 shadow-sm border border-gray-200 hover:bg-gray-50 transition-colors">
                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    <span>Download Invoice</span>
                </a>

                @php
                    $canCancel = in_array($order->status, ['pending', 'processing']) && ($order->vendor?->settings?->allow_customer_cancellation ?? true);
                @endphp
                @if($canCancel)
                    <button type="button" @click="showCancelModal = true" class="px-4 py-2.5 rounded-2xl text-xs font-bold text-rose-600 hover:bg-rose-50 border border-rose-200 transition-colors">
                        Cancel Order
                    </button>
                @endif
            </div>
        </div>

        @if(session('success'))
            <div class="mb-6 p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-2xl flex items-center gap-3 shadow-xs">
                <span class="text-emerald-600 font-bold">✓</span>
                <p class="font-medium text-sm">{{ session('success') }}</p>
            </div>
        @endif

        @if(session('error'))
            <div class="mb-6 p-4 bg-rose-50 border border-rose-200 text-rose-800 rounded-2xl flex items-center gap-3 shadow-xs">
                <span class="text-rose-600 font-bold">!</span>
                <p class="font-medium text-sm">{{ session('error') }}</p>
            </div>
        @endif

        @php
            $isOutForDelivery = ($order->shipment && $order->shipment->status === 'out_for_delivery') || ($order->status === 'out_for_delivery');
            $isDelivered = ($order->status === 'delivered') || ($order->shipment && $order->shipment->status === 'delivered');

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
        @endphp

        <!-- Main Card -->
        <div class="bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden mb-6">
            <!-- Header Strip -->
            <div class="bg-gradient-to-r from-stone-50/90 via-white to-stone-50/50 px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                <div class="flex items-center gap-2 text-xs">
                    <span class="text-gray-400 font-medium">Sold by</span>
                    @if($order->vendor)
                        <a href="{{ $order->vendor->storefront_url }}" class="font-bold text-brand-text hover:text-brand-sage underline decoration-dotted">
                            {{ $order->vendor->shop_name }}
                        </a>
                    @else
                        <span class="font-bold text-brand-text">K71 Direct Store</span>
                    @endif
                </div>
                <div>
                    @if($isDelivered)
                        <span class="px-3 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                            ✓ Delivered
                        </span>
                    @elseif($isOutForDelivery)
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-amber-50 text-amber-800 border border-amber-200 shadow-2xs">
                            <span class="relative flex h-2 w-2">
                              <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-amber-400 opacity-75"></span>
                              <span class="relative inline-flex rounded-full h-2 w-2 bg-amber-600"></span>
                            </span>
                            🛵 Out for Delivery
                        </span>
                    @elseif($order->status === 'shipped')
                        <span class="px-3 py-1 rounded-full text-xs font-bold bg-indigo-50 text-indigo-700 border border-indigo-200">
                            Shipped · In Transit
                        </span>
                    @elseif($order->status === 'processing')
                        <span class="px-3 py-1 rounded-full text-xs font-bold bg-sky-50 text-sky-700 border border-sky-200">
                            Processing · Packing
                        </span>
                    @elseif($order->status === 'pending')
                        <span class="px-3 py-1 rounded-full text-xs font-bold bg-amber-50 text-amber-700 border border-amber-200">
                            Pending Verification
                        </span>
                    @elseif($order->status === 'cancelled')
                        <span class="px-3 py-1 rounded-full text-xs font-bold bg-rose-50 text-rose-700 border border-rose-200">
                            ✕ Cancelled
                        </span>
                    @endif
                </div>
            </div>

            <!-- Stepper -->
            @if($order->status !== 'cancelled')
                @if($order->isFoodOrder())
                    <!-- Dedicated Food Culinary Live Stepper -->
                    <div class="px-6 sm:px-8 pt-6 pb-5 border-b border-orange-100 bg-gradient-to-b from-orange-50/40 to-white">
                        <!-- Food ETA / Status Banner -->
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-6 p-4 rounded-2xl bg-white border border-orange-200/80 shadow-xs">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-2xl bg-[#ea580c] text-white flex items-center justify-center text-lg font-bold shadow-md shadow-orange-500/20">
                                    {{ $isDelivered ? '🍽️' : ($isOutForDelivery ? '🛵' : '🍳') }}
                                </div>
                                <div>
                                    <h3 class="text-sm font-black text-stone-900">
                                        {{ $isDelivered ? 'Order Delivered!' : ($isOutForDelivery ? ($order->isFoodTakeaway() ? 'Ready for Self Pickup' : 'Out for Doorstep Delivery') : ($order->status === 'processing' ? 'Kitchen is Cooking your Meal' : 'Order Placed & Confirmed')) }}
                                    </h3>
                                    <p class="text-xs text-stone-500">
                                        @if($isDelivered)
                                            Delivered at {{ $order->delivered_at ? $order->delivered_at->format('h:i A') : 'Doorstep' }}. Enjoy your meal!
                                        @else
                                            Estimated Delivery: ~{{ $order->estimated_delivery_time ? $order->estimated_delivery_time->format('h:i A') : $order->created_at->addMinutes(30)->format('h:i A') }} (approx 25-35 mins)
                                        @endif
                                    </p>
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                <a href="{{ route('food.reorder', $order->id) }}" class="px-4 py-2 rounded-xl bg-orange-50 border border-[#ea580c] text-[#ea580c] hover:bg-[#ea580c] hover:text-white text-xs font-black transition active:scale-95 shadow-xs flex items-center gap-1.5 cursor-pointer">
                                    <span>🔄</span>
                                    <span>1-Click Reorder</span>
                                </a>
                            </div>
                        </div>

                        <!-- 4-Stage Food Stepper -->
                        <div class="grid grid-cols-4 gap-2 relative">
                            <div class="absolute top-3.5 left-6 right-6 h-1 bg-stone-100 -z-0"></div>
                            <div class="absolute top-3.5 left-6 h-1 bg-[#ea580c] -z-0 transition-all duration-500" 
                                 style="width: {{ $isDelivered ? '92%' : ($isOutForDelivery ? '70%' : ($statusStep >= 2 ? '38%' : '5%')) }};"></div>

                            <div class="flex flex-col items-center text-center relative z-10">
                                <div class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-black shadow-xs {{ $statusStep >= 1 ? 'bg-[#ea580c] text-white ring-4 ring-orange-100' : 'bg-stone-100 text-stone-400' }}">✓</div>
                                <span class="text-[11px] font-bold text-stone-900 mt-2">Placed</span>
                            </div>
                            <div class="flex flex-col items-center text-center relative z-10">
                                <div class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-black shadow-xs {{ $statusStep >= 2 ? 'bg-[#ea580c] text-white ring-4 ring-orange-100' : 'bg-stone-100 text-stone-400' }}">{{ $statusStep >= 2 ? '✓' : '2' }}</div>
                                <span class="text-[11px] font-bold {{ $statusStep >= 2 ? 'text-stone-900' : 'text-stone-400' }} mt-2">Cooking</span>
                            </div>
                            <div class="flex flex-col items-center text-center relative z-10">
                                <div class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-black shadow-xs {{ $statusStep >= 3 ? ($isOutForDelivery ? 'bg-amber-500 text-white ring-4 ring-amber-100 animate-bounce' : 'bg-[#ea580c] text-white ring-4 ring-orange-100') : 'bg-stone-100 text-stone-400' }}">
                                    {{ $isOutForDelivery ? '🛵' : ($statusStep >= 3 ? '✓' : '3') }}
                                </div>
                                <span class="text-[11px] font-bold {{ $isOutForDelivery ? 'text-amber-800 font-black' : ($statusStep >= 3 ? 'text-stone-900' : 'text-stone-400') }} mt-2">
                                    {{ $order->isFoodTakeaway() ? 'Ready for Pickup' : 'Out for Delivery' }}
                                </span>
                            </div>
                            <div class="flex flex-col items-center text-center relative z-10">
                                <div class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-black shadow-xs {{ $statusStep >= 4 ? 'bg-emerald-600 text-white ring-4 ring-emerald-100' : 'bg-stone-100 text-stone-400' }}">{{ $statusStep >= 4 ? '✓' : '4' }}</div>
                                <span class="text-[11px] font-bold {{ $statusStep >= 4 ? 'text-emerald-700' : 'text-stone-400' }} mt-2">Delivered</span>
                            </div>
                        </div>

                        @if($order->kitchen_notes)
                            <div class="mt-5 p-3 rounded-xl bg-amber-50/80 border border-amber-200 text-xs text-amber-900 flex items-center gap-2">
                                <span>📝</span>
                                <span><strong>Special Kitchen Request:</strong> {{ $order->kitchen_notes }}</span>
                            </div>
                        @endif
                    </div>
                @else
                    <div class="px-6 sm:px-8 pt-5 pb-3 border-b border-gray-100 bg-white">
                        <div class="grid grid-cols-4 gap-2 relative">
                            <div class="absolute top-3 left-4 right-4 h-1 bg-gray-100 -z-0"></div>
                            <div class="absolute top-3 left-4 h-1 bg-[#1F2A26] -z-0 transition-all duration-500" 
                                 style="width: {{ $isOutForDelivery ? '82%' : match((int)$statusStep) { 1 => '10%', 2 => '38%', 3 => '70%', 4 => '96%', default => '0%' } }};"></div>

                            <div class="flex flex-col items-center text-center relative z-10">
                                <div class="w-7 h-7 rounded-full flex items-center justify-center text-xs font-bold shadow-2xs {{ $statusStep >= 1 ? 'bg-[#1F2A26] text-white ring-4 ring-white' : 'bg-gray-100 text-gray-400' }}">✓</div>
                                <span class="text-[11px] font-bold text-brand-text mt-1.5">Confirmed</span>
                            </div>
                            <div class="flex flex-col items-center text-center relative z-10">
                                <div class="w-7 h-7 rounded-full flex items-center justify-center text-xs font-bold shadow-2xs {{ $statusStep >= 2 ? 'bg-[#1F2A26] text-white ring-4 ring-white' : 'bg-gray-100 text-gray-400' }}">{{ $statusStep >= 2 ? '✓' : '2' }}</div>
                                <span class="text-[11px] font-bold {{ $statusStep >= 2 ? 'text-brand-text' : 'text-gray-400' }} mt-1.5">Packing</span>
                            </div>
                            <div class="flex flex-col items-center text-center relative z-10">
                                <div class="w-7 h-7 rounded-full flex items-center justify-center text-xs font-bold shadow-2xs {{ $statusStep >= 3 ? ($isOutForDelivery ? 'bg-amber-600 text-white ring-4 ring-amber-100' : 'bg-[#1F2A26] text-white ring-4 ring-white') : 'bg-gray-100 text-gray-400' }}">
                                    {{ $isOutForDelivery ? '🛵' : ($statusStep >= 3 ? '✓' : '3') }}
                                </div>
                                <span class="text-[11px] font-bold {{ $isOutForDelivery ? 'text-amber-800' : ($statusStep >= 3 ? 'text-brand-text' : 'text-gray-400') }} mt-1.5">
                                    {{ $isOutForDelivery ? 'Out for Delivery' : 'Shipped' }}
                                </span>
                            </div>
                            <div class="flex flex-col items-center text-center relative z-10">
                                <div class="w-7 h-7 rounded-full flex items-center justify-center text-xs font-bold shadow-2xs {{ $statusStep >= 4 ? 'bg-emerald-600 text-white ring-4 ring-white' : 'bg-gray-100 text-gray-400' }}">{{ $statusStep >= 4 ? '✓' : '4' }}</div>
                                <span class="text-[11px] font-bold {{ $statusStep >= 4 ? 'text-emerald-700' : 'text-gray-400' }} mt-1.5">Delivered</span>
                            </div>
                        </div>
                    </div>
                @endif
            @endif

            <!-- Items -->
            <div class="p-6 sm:p-8 space-y-4">
                @foreach($order->items as $item)
                    <div class="flex items-center gap-4 sm:gap-5 pb-4 border-b border-gray-100 last:border-b-0 last:pb-0">
                        <div class="w-18 h-22 sm:w-20 sm:h-24 bg-gray-100 rounded-2xl overflow-hidden shrink-0 border border-gray-200/50 shadow-2xs">
                            @if($item->product)
                                <img src="{{ $item->product->primary_image_url }}" 
                                     alt="{{ $item->product->title ?? 'Product' }}"
                                     class="w-full h-full object-cover"
                                     onerror="this.onerror=null; this.src='{{ asset('images/product-placeholder.svg') }}';">
                            @else
                                <img src="{{ asset('images/product-placeholder.svg') }}" 
                                     alt="Product"
                                     class="w-full h-full object-cover">
                            @endif
                        </div>
                        <div class="flex-1 min-w-0">
                            <h4 class="font-bold text-sm sm:text-base text-brand-text truncate">{{ $item->product->title ?? $item->snapshot['title'] ?? 'Product' }}</h4>
                            <div class="flex flex-wrap items-center gap-2 mt-1">
                                <span class="bg-stone-100 text-stone-700 px-2 py-0.5 rounded text-xs font-medium">{{ $item->variant->sku ?? $item->snapshot['variant_sku'] ?? 'Standard' }}</span>
                                <span class="text-xs text-gray-500 font-medium">Qty: {{ $item->quantity }}</span>
                            </div>
                            @if(!empty($item->selected_modifiers_json))
                                <div class="flex flex-wrap gap-1 mt-1.5">
                                    @foreach($item->selected_modifiers_json as $mod)
                                        <span class="text-[10px] text-amber-800 bg-amber-50 px-2 py-0.5 rounded border border-amber-200 font-medium">
                                            + {{ $mod['name'] }}
                                        </span>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                        <div class="text-right shrink-0">
                            <p class="text-sm sm:text-base font-bold text-brand-text">₹{{ number_format($item->total_price / 100, 2) }}</p>
                            @if($item->quantity > 1)
                                <p class="text-[11px] text-gray-400">₹{{ number_format($item->unit_price / 100, 2) }} each</p>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Tracking Strip -->
            @if($order->tracking_number || $order->tracking_url)
                <div class="mx-6 sm:mx-8 mb-6 p-4 rounded-2xl bg-indigo-50/50 border border-indigo-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3.5">
                    <div class="flex items-start sm:items-center gap-3 min-w-0 flex-1">
                        <div class="w-9 h-9 rounded-xl bg-white border border-indigo-100 flex items-center justify-center text-indigo-600 shadow-2xs shrink-0 mt-0.5 sm:mt-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0"/></svg>
                        </div>
                        <div class="min-w-0 flex-1">
                            <div class="flex flex-wrap items-center gap-1.5 sm:gap-2">
                                <span class="text-xs font-bold text-indigo-950 uppercase tracking-wider">Tracking Number (AWB):</span>
                                <span class="font-mono font-bold text-indigo-900 text-sm ml-1 bg-white px-2 py-0.5 rounded border border-indigo-200 select-all">{{ $order->tracking_number ?? 'In Transit' }}</span>
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
                        </div>
                    </div>
                    @if($order->tracking_url)
                        <a href="{{ $order->tracking_url }}" target="_blank" class="inline-flex items-center justify-center gap-1.5 px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-bold transition-colors shadow-xs shrink-0 w-full sm:w-auto">
                            <span>Track Shipment ↗</span>
                        </a>
                    @endif
                </div>
            @endif

            <!-- 2-Column Details: Shipping & Financial Breakdown -->
            <div class="px-6 sm:px-8 py-5 border-t border-gray-100 grid grid-cols-1 md:grid-cols-2 gap-4 bg-stone-50/50">
                <!-- Address -->
                <div class="bg-white rounded-2xl border border-gray-100 p-4">
                    <p class="text-xs uppercase tracking-wider font-bold text-gray-400 mb-2">Delivery Address</p>
                    @if($order->address_snapshot)
                        <div class="text-xs text-gray-700 space-y-0.5 leading-relaxed">
                            <p class="font-bold text-gray-900">{{ $order->address_snapshot['line_1'] ?? '' }}</p>
                            <p>{{ $order->address_snapshot['city'] ?? '' }}, {{ $order->address_snapshot['state'] ?? '' }} - {{ $order->address_snapshot['pincode'] ?? '' }}</p>
                            @if(!empty($order->address_snapshot['phone']))
                                <p class="text-gray-500 font-mono mt-1">Phone: {{ $order->address_snapshot['phone'] }}</p>
                            @endif
                        </div>
                    @else
                        <p class="text-xs text-gray-400 italic">No address provided</p>
                    @endif
                </div>

                <!-- Payment Details -->
                <div class="bg-white rounded-2xl border border-gray-100 p-4">
                    <p class="text-xs uppercase tracking-wider font-bold text-gray-400 mb-2">Payment Details</p>
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
                        <div class="flex justify-between text-sm font-bold text-gray-900 border-t border-gray-100 pt-2">
                            <dt>Total</dt>
                            <dd>₹{{ number_format($order->total_amount / 100, 2) }}</dd>
                        </div>
                    </dl>
                    <div class="mt-3 pt-2 border-t border-gray-100 flex items-center justify-between text-xs">
                        <span class="text-gray-400">Method: {{ strtoupper($order->payment_method) }}</span>
                        @if($order->payment_status === 'pending' && $order->payment_method === 'phonepe' && $order->status !== 'cancelled')
                            <a href="{{ route('payment.phonepe.initiate', $order) }}" class="px-3 py-1 bg-purple-600 text-white rounded-lg text-xs font-bold hover:bg-purple-700">
                                ⚡ Pay Now
                            </a>
                        @elseif($order->payment_method === 'upi' && $order->payment_status !== 'paid' && $order->status !== 'cancelled')
                            <a href="{{ route('checkout.payment', $order) }}" class="px-3 py-1 bg-emerald-600 text-white rounded-lg text-xs font-bold hover:bg-emerald-700">
                                📸 Submit Payment Proof
                            </a>
                        @else
                            <span class="font-bold {{ $order->payment_status === 'paid' ? 'text-emerald-700' : 'text-amber-700' }}">
                                {{ ucfirst($order->payment_status) }}
                            </span>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Cancellation Modal -->
        @if($canCancel)
            <div x-show="showCancelModal" 
                 x-cloak 
                 class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs"
                 @keydown.escape.window="showCancelModal = false">
                <div class="bg-white rounded-3xl p-6 sm:p-8 max-w-md w-full shadow-2xl border border-gray-100" @click.away="showCancelModal = false">
                    <div class="w-12 h-12 rounded-2xl bg-rose-50 text-rose-600 flex items-center justify-center text-xl mb-4 font-bold border border-rose-100">
                        ✕
                    </div>
                    <h3 class="text-xl font-serif font-bold text-brand-text mb-1.5">Cancel Order #{{ $order->order_number }}?</h3>
                    <p class="text-sm text-gray-500 mb-6 leading-relaxed">Are you sure you want to cancel this order? The reserved inventory will be restored back to the store.</p>
                    
                    <form action="{{ route('orders.cancel', $order) }}" method="POST" class="space-y-4">
                        @csrf
                        <div>
                            <label for="reason" class="block text-xs font-bold uppercase tracking-wider text-gray-500 mb-1.5">Reason for Cancellation</label>
                            <select name="reason" id="reason" class="w-full rounded-xl border border-gray-200 px-3.5 py-2.5 text-sm focus:border-brand-sage focus:ring-brand-sage">
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
</x-layouts.public>
