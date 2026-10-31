<x-layouts.vendor title="Manage Orders">
@php
    $search = $search ?? request('search', '');
    $status = $status ?? request('status', 'all');
    $counts = array_merge([
        'all' => 0,
        'to_pack' => 0,
        'shipped' => 0,
        'delivered' => 0,
        'cancelled' => 0,
    ], is_array($counts ?? null) ? $counts : []);
    $curVendor = auth()->user()?->vendor;
    $sec = $curVendor ? $curVendor->getSectorConfig() : [
        'name' => 'Marketplace',
        'orders_name' => 'Customer Orders & Shipments',
        'icon' => '📦',
        'badge' => 'Orders'
    ];
@endphp
    <!-- Header -->
    <div class="sm:flex sm:items-center sm:justify-between mb-6">
        <div>
            <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-indigo-50 dark:bg-indigo-950/50 text-indigo-700 dark:text-indigo-300 border border-indigo-200/50 dark:border-indigo-800/50 mb-2">
                <span>{{ $sec['icon'] }}</span>
                <span>{{ $sec['name'] }} Pipeline</span>
            </div>
            <h1 class="text-3xl font-serif font-bold text-gray-900 dark:text-white tracking-tight flex items-center gap-2">
                <span>{{ $sec['icon'] }}</span>
                <span>{{ $sec['orders_name'] }}</span>
            </h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400 font-medium">Manage, fulfill shipments, dispatch tracking, and settlement history for your {{ strtolower($sec['name']) }} store.</p>
        </div>
    </div>

    <!-- Filter & Search Strip -->
    <div class="mb-6 space-y-4">
        <!-- Status Pipeline Tabs -->
        <div class="flex items-center gap-2 overflow-x-auto pb-1 no-scrollbar">
            <a href="{{ route('vendor.orders.index', ['status' => 'all', 'search' => $search]) }}" 
               class="px-4 py-2 rounded-2xl text-xs font-bold transition-all whitespace-nowrap {{ $status === 'all' ? 'bg-gray-900 text-white shadow-xs' : 'bg-white text-gray-600 hover:bg-gray-50 border border-gray-200/70' }}">
                All Orders ({{ $counts['all'] ?? 0 }})
            </a>
            <a href="{{ route('vendor.orders.index', ['status' => 'pending_payment', 'search' => $search]) }}" 
               class="px-4 py-2 rounded-2xl text-xs font-bold transition-all whitespace-nowrap flex items-center gap-1.5 {{ $status === 'pending_payment' ? 'bg-black text-white shadow-xs' : 'bg-white text-zinc-900 hover:bg-zinc-50 border border-zinc-300' }}">
                <span>💳 Payment Proofs</span>
                <span class="px-2 py-0.2 rounded-full text-[10px] font-mono {{ ($counts['pending_payment'] ?? 0) > 0 ? 'bg-amber-400 text-black font-black animate-pulse' : 'bg-zinc-100 text-zinc-600' }}">
                    {{ $counts['pending_payment'] ?? 0 }}
                </span>
            </a>
            <a href="{{ route('vendor.orders.index', ['status' => 'to_pack', 'search' => $search]) }}" 
               class="px-4 py-2 rounded-2xl text-xs font-bold transition-all whitespace-nowrap {{ $status === 'to_pack' ? 'bg-amber-600 text-white shadow-xs' : 'bg-white text-amber-700 hover:bg-amber-50 border border-amber-200/70' }}">
                ⚡ To Pack ({{ $counts['to_pack'] ?? 0 }})
            </a>
            <a href="{{ route('vendor.orders.index', ['status' => 'shipped', 'search' => $search]) }}" 
               class="px-4 py-2 rounded-2xl text-xs font-bold transition-all whitespace-nowrap {{ $status === 'shipped' ? 'bg-indigo-600 text-white shadow-xs' : 'bg-white text-indigo-700 hover:bg-indigo-50 border border-indigo-200/70' }}">
                🚚 In Transit ({{ $counts['shipped'] ?? 0 }})
            </a>
            <a href="{{ route('vendor.orders.index', ['status' => 'delivered', 'search' => $search]) }}" 
               class="px-4 py-2 rounded-2xl text-xs font-bold transition-all whitespace-nowrap {{ $status === 'delivered' ? 'bg-emerald-600 text-white shadow-xs' : 'bg-white text-emerald-700 hover:bg-emerald-50 border border-emerald-200/70' }}">
                ✓ Delivered ({{ $counts['delivered'] ?? 0 }})
            </a>
            <a href="{{ route('vendor.orders.index', ['status' => 'cancelled', 'search' => $search]) }}" 
               class="px-4 py-2 rounded-2xl text-xs font-bold transition-all whitespace-nowrap {{ $status === 'cancelled' ? 'bg-rose-600 text-white shadow-xs' : 'bg-white text-rose-700 hover:bg-rose-50 border border-rose-200/70' }}">
                ✕ Cancelled ({{ $counts['cancelled'] ?? 0 }})
            </a>
        </div>

        <!-- Search Box -->
        <form method="GET" action="{{ route('vendor.orders.index') }}" class="flex items-center gap-2">
            <input type="hidden" name="status" value="{{ $status }}">
            <div class="relative flex-1 max-w-md">
                <input type="text" name="search" value="{{ $search }}" 
                       placeholder="Search by Order #, Customer Name, Phone, or AWB..." 
                       class="w-full rounded-2xl border border-gray-200 bg-white px-4 py-2.5 text-xs text-gray-900 placeholder:text-gray-400 focus:border-brand-sage focus:ring-brand-sage shadow-2xs">
                @if(!empty($search))
                    <a href="{{ route('vendor.orders.index', ['status' => $status]) }}" 
                       class="absolute right-3 top-2.5 text-gray-400 hover:text-gray-600 text-xs font-bold">
                        ✕
                    </a>
                @endif
            </div>
            <button type="submit" class="px-4 py-2.5 bg-gray-900 hover:bg-black text-white text-xs font-bold rounded-2xl shadow-xs transition-colors">
                Search
            </button>
        </form>
    </div>

    @if($orders->isEmpty())
        <div class="bg-white/60 backdrop-blur-xl border border-gray-100 rounded-3xl shadow-sm p-12 text-center">
            <div class="w-20 h-20 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg class="w-10 h-10 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
            </div>
            <h3 class="text-lg font-bold text-gray-900 mb-1">No Orders Found</h3>
            <p class="text-gray-500 text-sm max-w-sm mx-auto">
                @if(!empty($search))
                    No orders matching "<span class="font-bold">{{ $search }}</span>". Try clearing your search filter.
                @else
                    When customers place orders on your storefront, they will appear here.
                @endif
            </p>
        </div>
    @else
        <!-- Mobile View (Cards) -->
        <div class="block sm:hidden space-y-4">
            @foreach($orders as $order)
                @php
                    $rawPhone = $order->address_snapshot['phone'] ?? $order->user->phone ?? '';
                    $cleanPhone = preg_replace('/[^0-9]/', '', $rawPhone);
                    if (strlen($cleanPhone) === 10) { $cleanPhone = '91' . $cleanPhone; }
                    $customerGreeting = $order->user->first_name ?? $order->user->name ?? 'Customer';
                    $waMsg = rawurlencode("Namaste {$customerGreeting}! This is {$order->vendor->shop_name} regarding your Order #{$order->order_number}.");
                @endphp
                <div class="bg-white/80 backdrop-blur-xl shadow-sm border border-gray-100 rounded-3xl p-5 hover:shadow-md transition-shadow relative overflow-hidden group">
                    <div class="absolute top-0 left-0 w-1.5 h-full 
                        {{ $order->status === 'pending' ? 'bg-orange-400' : '' }}
                        {{ $order->status === 'processing' ? 'bg-blue-400' : '' }}
                        {{ $order->status === 'shipped' ? 'bg-indigo-400' : '' }}
                        {{ $order->status === 'delivered' ? 'bg-green-400' : '' }}
                        {{ $order->status === 'cancelled' ? 'bg-red-400' : '' }}
                    "></div>
                    
                    <div class="flex justify-between items-start mb-3">
                        <div>
                            <span class="font-mono font-bold text-gray-900 text-sm bg-gray-100 px-2 py-0.5 rounded border border-gray-200">{{ $order->order_number }}</span>
                            <p class="text-xs text-gray-400 mt-1">{{ $order->created_at->format('d M, Y · g:i A') }}</p>
                        </div>
                        <span class="inline-flex items-center rounded-xl px-2.5 py-1 text-xs font-bold ring-1 ring-inset 
                            {{ $order->status === 'pending' ? 'bg-orange-50 text-orange-700 ring-orange-600/20' : '' }}
                            {{ $order->status === 'processing' ? 'bg-blue-50 text-blue-700 ring-blue-600/20' : '' }}
                            {{ $order->status === 'shipped' ? 'bg-indigo-50 text-indigo-700 ring-indigo-600/20' : '' }}
                            {{ $order->status === 'delivered' ? 'bg-green-50 text-green-700 ring-green-600/20' : '' }}
                            {{ $order->status === 'cancelled' ? 'bg-red-50 text-red-700 ring-red-600/20' : '' }}">
                            {{ ucfirst($order->status) }}
                        </span>
                    </div>

                    <div class="flex items-center justify-between py-2 border-y border-gray-100 text-xs">
                        <div>
                            <span class="text-gray-400">Customer:</span>
                            <span class="font-bold text-gray-800 ml-1">{{ $order->user->name ?? 'Customer' }}</span>
                        </div>
                        @if(!empty($cleanPhone))
                            <a href="https://wa.me/{{ $cleanPhone }}?text={{ $waMsg }}" target="_blank"
                               class="inline-flex items-center gap-1 text-emerald-700 font-bold bg-emerald-50 px-2 py-0.5 rounded border border-emerald-200">
                                💬 WhatsApp
                            </a>
                        @endif
                    </div>

                    <div class="flex items-center justify-between pt-3">
                        <div>
                            <p class="text-xs text-gray-400">Total</p>
                            <p class="text-base font-black text-brand-text">₹{{ number_format($order->total_amount / 100, 2) }}</p>
                        </div>
                        <div class="flex items-center gap-2">
                            <a href="{{ route('vendor.orders.shipping_label', $order) }}" target="_blank" 
                               class="bg-gray-100 text-gray-800 rounded-xl px-3 py-1.5 text-xs font-bold border border-gray-200 shadow-2xs">
                                🏷️ Label
                            </a>
                            <a href="{{ route('vendor.orders.show', $order) }}" 
                               class="bg-gray-900 text-white rounded-xl px-4 py-1.5 text-xs font-bold shadow-xs hover:bg-black transition-colors">
                                Manage →
                            </a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Desktop View (Table) -->
        <div class="hidden sm:block bg-white/80 backdrop-blur-xl shadow-xs border border-gray-100 rounded-3xl overflow-hidden">
            <table class="min-w-full divide-y divide-gray-100">
                <thead class="bg-gray-50/70">
                    <tr>
                        <th scope="col" class="py-4 pl-6 pr-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Order</th>
                        <th scope="col" class="px-3 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Customer</th>
                        <th scope="col" class="px-3 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Total & Mode</th>
                        <th scope="col" class="px-3 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Courier / AWB</th>
                        <th scope="col" class="px-3 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Status</th>
                        <th scope="col" class="relative py-4 pl-3 pr-6 text-right">
                            <span class="sr-only">Actions</span>
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 bg-white">
                    @foreach($orders as $order)
                        @php
                            $rawPhone = $order->address_snapshot['phone'] ?? $order->user->phone ?? '';
                            $cleanPhone = preg_replace('/[^0-9]/', '', $rawPhone);
                            if (strlen($cleanPhone) === 10) { $cleanPhone = '91' . $cleanPhone; }
                            $customerGreeting = $order->user->first_name ?? $order->user->name ?? 'Customer';
                            $waMsg = rawurlencode("Namaste {$customerGreeting}! This is {$order->vendor->shop_name} regarding your Order #{$order->order_number}.");
                        @endphp
                        <tr class="hover:bg-stone-50/60 transition-colors group">
                            <!-- Order & Date -->
                            <td class="whitespace-nowrap py-4 pl-6 pr-3">
                                <div class="flex items-center">
                                    <div class="w-1.5 h-8 rounded-full mr-3
                                        {{ $order->status === 'pending' ? 'bg-orange-400' : '' }}
                                        {{ $order->status === 'processing' ? 'bg-blue-400' : '' }}
                                        {{ $order->status === 'shipped' ? 'bg-indigo-400' : '' }}
                                        {{ $order->status === 'delivered' ? 'bg-green-400' : '' }}
                                        {{ $order->status === 'cancelled' ? 'bg-red-400' : '' }}
                                    "></div>
                                    <div>
                                        <div class="font-mono font-bold text-gray-900 text-xs">{{ $order->order_number }}</div>
                                        <div class="text-[11px] text-gray-400 mt-0.5">{{ $order->created_at->format('d M, Y · h:i A') }}</div>
                                    </div>
                                </div>
                            </td>

                            <!-- Customer & WhatsApp -->
                            <td class="whitespace-nowrap px-3 py-4">
                                <div class="flex items-center gap-2">
                                    @if($order->payment_method === 'phonepe' && $order->settlement_status === 'pending')
                                        <span class="text-xs font-bold text-gray-700">{{ substr($order->user->first_name ?? 'Customer', 0, 1) }}**** {{ substr($order->user->last_name ?? '', 0, 1) }}****</span>
                                        <span class="text-[10px] text-amber-700 bg-amber-50 px-1.5 py-0.5 rounded border border-amber-200 font-medium">🔒 Locked</span>
                                    @else
                                        <span class="text-xs font-bold text-gray-900">{{ $order->user->name ?? 'Customer' }}</span>
                                        @if(!empty($cleanPhone))
                                            <a href="https://wa.me/{{ $cleanPhone }}?text={{ $waMsg }}" target="_blank" 
                                               title="Chat with Customer on WhatsApp"
                                               class="text-emerald-600 hover:text-emerald-700 p-1 rounded-md hover:bg-emerald-50 transition-colors">
                                                <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/></svg>
                                            </a>
                                        @endif
                                    @endif
                                </div>
                                <div class="text-[11px] text-gray-400">{{ $order->address_snapshot['city'] ?? '' }} - {{ $order->address_snapshot['pincode'] ?? '' }}</div>
                            </td>

                            <!-- Total & Payment -->
                            <td class="whitespace-nowrap px-3 py-4">
                                <div class="text-xs font-black text-brand-text">₹{{ number_format($order->total_amount / 100, 2) }}</div>
                                <div class="text-[10px] uppercase font-bold mt-0.5 {{ $order->payment_status === 'paid' ? 'text-emerald-700' : 'text-amber-600' }}">
                                    {{ $order->payment_method === 'phonepe' ? '⚡ PhonePe' : ($order->payment_method === 'cod' ? '💵 COD' : '📱 UPI') }} · {{ $order->payment_status }}
                                </div>
                            </td>

                            <!-- Courier & Tracking -->
                            <td class="whitespace-nowrap px-3 py-4">
                                @if($order->tracking_number && !\Illuminate\Support\Str::startsWith($order->tracking_number, 'TXN_'))
                                    <div class="text-xs font-mono font-bold text-gray-800">{{ $order->tracking_number }}</div>
                                    <div class="text-[10px] text-gray-500">{{ $order->courier_name ?? 'Courier' }}</div>
                                @else
                                    <span class="text-[11px] text-gray-400 italic">Not Dispatched</span>
                                @endif
                            </td>

                            <!-- Fulfillment & Payout Status -->
                            <td class="whitespace-nowrap px-3 py-4">
                                @if($order->status === 'cancelled')
                                    <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-[11px] font-bold border shadow-2xs bg-rose-50 text-rose-700 border-rose-200">
                                        Cancelled
                                    </span>
                                @elseif($order->payment_method === 'phonepe' && $order->settlement_status === 'pending')
                                    <span class="inline-flex items-center gap-1 rounded-full px-2.5 py-0.5 text-[11px] font-bold border shadow-2xs bg-purple-50 text-purple-700 border-purple-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-purple-500 animate-ping"></span>
                                        ⏳ Clearing (~11 AM)
                                    </span>
                                @elseif($order->payment_method === 'phonepe' && $order->settlement_status === 'cleared' && $order->vendor_payout_status !== 'paid')
                                    <span class="inline-flex items-center gap-1 rounded-full px-2.5 py-0.5 text-[11px] font-bold border shadow-2xs bg-emerald-50 text-emerald-800 border-emerald-300 ring-1 ring-emerald-400/30">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-600 animate-pulse"></span>
                                        🟢 Ready to Accept
                                    </span>
                                @elseif($order->vendor_payout_status === 'paid')
                                    <span class="inline-flex items-center gap-1 rounded-full px-2.5 py-0.5 text-[11px] font-bold border shadow-2xs bg-emerald-50 text-emerald-700 border-emerald-200">
                                        ✓ Paid ({{ ucfirst($order->status) }})
                                    </span>
                                @else
                                    <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-[11px] font-bold border shadow-2xs
                                        {{ $order->status === 'pending' ? 'bg-amber-50 text-amber-700 border-amber-200' : '' }}
                                        {{ $order->status === 'processing' ? 'bg-sky-50 text-sky-700 border-sky-200' : '' }}
                                        {{ $order->status === 'shipped' ? 'bg-indigo-50 text-indigo-700 border-indigo-200' : '' }}
                                        {{ $order->status === 'delivered' ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : '' }}">
                                        {{ ucfirst($order->status) }}
                                    </span>
                                @endif
                            </td>

                            <!-- Actions -->
                            <td class="whitespace-nowrap py-4 pl-3 pr-6 text-right">
                                <div class="inline-flex items-center gap-1.5">
                                    <a href="{{ route('vendor.orders.shipping_label', $order) }}" target="_blank" 
                                       title="Print Box Label (4x6)"
                                       class="p-1.5 rounded-lg bg-stone-100 hover:bg-stone-200 text-stone-700 transition-colors">
                                        🏷️
                                    </a>
                                    <a href="{{ route('vendor.orders.show', $order) }}" 
                                       class="inline-flex items-center justify-center bg-gray-900 hover:bg-black text-white rounded-xl px-3 py-1.5 text-xs font-bold shadow-xs transition-colors">
                                        Manage
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        
        <div class="mt-6">
            {{ $orders->links() }}
        </div>
    @endif
</x-layouts.vendor>
