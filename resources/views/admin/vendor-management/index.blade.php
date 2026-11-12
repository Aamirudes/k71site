<x-layouts.admin title="Vendor Management & Subscriptions">
    @include('admin.vendor-management._nav')

    @if(($stats['verified_without_sub'] ?? 0) > 0 && $strictEnforcement)
        <div class="rounded-2xl border border-amber-300 bg-amber-50 p-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div>
                <p class="text-xs font-black uppercase tracking-wider text-amber-950">
                    Action Required: {{ $stats['verified_without_sub'] }} Verified Vendor(s) Have No Active Subscription
                </p>
                <p class="text-xs text-amber-900 mt-0.5">
                    Strict subscription visibility enforcement is ON. Until you manually assign &amp; activate a plan for a verified vendor, their public storefront &amp; listings remain hidden.
                </p>
            </div>
            <a href="{{ route('admin.vendor_management.index', ['tab' => 'unsubscribed']) }}"
               class="shrink-0 px-3.5 py-2 rounded-xl bg-zinc-950 text-white text-xs font-bold hover:bg-zinc-800 transition">
                Review Unsubscribed Vendors →
            </a>
        </div>
    @endif

    <!-- High-Level Real Metrics -->
    <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-7 gap-3">
        <div class="bg-white border border-zinc-200/80 rounded-2xl p-3.5">
            <p class="text-[10px] font-mono uppercase text-zinc-400">All Vendors</p>
            <p class="text-xl font-black text-zinc-950 mt-0.5">{{ $stats['all'] }}</p>
        </div>
        <div class="bg-white border border-zinc-200/80 rounded-2xl p-3.5">
            <p class="text-[10px] font-mono uppercase text-amber-600">Pending Review</p>
            <p class="text-xl font-black text-zinc-950 mt-0.5">{{ $stats['pending'] }}</p>
        </div>
        <div class="bg-white border border-zinc-200/80 rounded-2xl p-3.5">
            <p class="text-[10px] font-mono uppercase text-emerald-600">Verified Merchants</p>
            <p class="text-xl font-black text-zinc-950 mt-0.5">{{ $stats['verified'] }}</p>
        </div>
        <div class="bg-white border border-zinc-200/80 rounded-2xl p-3.5">
            <p class="text-[10px] font-mono uppercase text-zinc-500">Active Subs</p>
            <p class="text-xl font-black text-zinc-950 mt-0.5">{{ $stats['active_subs'] }}</p>
        </div>
        <div class="bg-white border border-zinc-200/80 rounded-2xl p-3.5">
            <p class="text-[10px] font-mono uppercase text-blue-600">Pending Activation</p>
            <p class="text-xl font-black text-zinc-950 mt-0.5">{{ $stats['pending_activation'] }}</p>
        </div>
        <div class="bg-white border border-zinc-200/80 rounded-2xl p-3.5">
            <p class="text-[10px] font-mono uppercase text-orange-600">Expiring (7d)</p>
            <p class="text-xl font-black text-zinc-950 mt-0.5">{{ $stats['expiring_7d'] }}</p>
        </div>
        <div class="bg-white border border-zinc-200/80 rounded-2xl p-3.5">
            <p class="text-[10px] font-mono uppercase text-rose-600">Renewal Requests</p>
            <p class="text-xl font-black text-zinc-950 mt-0.5">{{ $stats['renewal_requests'] }}</p>
        </div>
    </div>

    <!-- Organized Queue Tabs -->
    <div class="bg-white border border-zinc-200/80 rounded-2xl p-4 space-y-4">
        <div class="flex flex-wrap items-center gap-1.5 border-b border-zinc-100 pb-3">
            @php
                $tabs = [
                    'all' => 'All Vendors (' . $stats['all'] . ')',
                    'pending' => 'Pending Verification (' . $stats['pending'] . ')',
                    'verified' => 'Verified (' . $stats['verified'] . ')',
                    'active' => 'Active Subscribers',
                    'expiring' => 'Expiring Soon (' . $stats['expiring_7d'] . ')',
                    'expired' => 'Expired (' . $stats['expired_subs'] . ')',
                    'suspended' => 'Suspended / Rejected (' . $stats['suspended'] . ')',
                    'unsubscribed' => 'Verified Without Sub (' . $stats['verified_without_sub'] . ')',
                ];
            @endphp
            @foreach($tabs as $tKey => $tLabel)
                <a href="{{ route('admin.vendor_management.index', array_merge(request()->except('page'), ['tab' => $tKey])) }}"
                   class="px-3 py-1.5 rounded-lg text-xs font-semibold transition {{ $tab === $tKey ? 'bg-zinc-950 text-white' : 'bg-zinc-100 text-zinc-600 hover:bg-zinc-200' }}">
                    {{ $tLabel }}
                </a>
            @endforeach
        </div>

        <!-- Search & Filters -->
        <form method="GET" action="{{ route('admin.vendor_management.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-2.5">
            <input type="hidden" name="tab" value="{{ $tab }}">
            <input type="text" name="search" value="{{ $search }}" placeholder="Search shop, owner, phone, email..."
                   class="lg:col-span-2 rounded-xl border border-zinc-200 px-3 py-2 text-xs">
            <select name="sector" class="rounded-xl border border-zinc-200 px-3 py-2 text-xs">
                <option value="">All Sectors</option>
                @foreach($sectors as $s)
                    <option value="{{ $s->key }}" @selected($sectorFilter === $s->key)>{{ $s->name }}</option>
                @endforeach
            </select>
            <select name="plan_id" class="rounded-xl border border-zinc-200 px-3 py-2 text-xs">
                <option value="">All Plans</option>
                @foreach($plans as $p)
                    <option value="{{ $p->id }}" @selected((string)$planFilter === (string)$p->id)>{{ $p->name }} ({{ $p->sector_key }})</option>
                @endforeach
            </select>
            <select name="payment_status" class="rounded-xl border border-zinc-200 px-3 py-2 text-xs">
                <option value="">All Payment States</option>
                <option value="received" @selected($paymentFilter === 'received')>Payment Confirmed</option>
                <option value="pending" @selected($paymentFilter === 'pending')>Awaiting Payment</option>
            </select>
            <div class="flex gap-2">
                <button type="submit" class="flex-1 px-4 py-2 rounded-xl bg-zinc-950 text-white text-xs font-bold hover:bg-zinc-800">Filter</button>
                <a href="{{ route('admin.vendor_management.index') }}" class="px-3 py-2 rounded-xl border border-zinc-200 text-xs font-semibold text-zinc-600 hover:bg-zinc-50">Reset</a>
            </div>
        </form>

        <!-- Vendors Table -->
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-zinc-100 text-[10px] font-mono uppercase tracking-wider text-zinc-400">
                        <th class="py-2.5 px-3">Vendor / Storefront</th>
                        <th class="py-2.5 px-3">Sector(s)</th>
                        <th class="py-2.5 px-3">Verification</th>
                        <th class="py-2.5 px-3">Latest Subscription</th>
                        <th class="py-2.5 px-3">External Payment</th>
                        <th class="py-2.5 px-3 text-right">Manage</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-100 text-xs">
                    @forelse($vendors as $vendor)
                        @php
                            $latestSub = $vendor->subscriptions->first();
                            $vStatus = $vendor->verification_status ?: ($vendor->status === 'approved' ? 'verified' : 'pending_review');
                        @endphp
                        <tr class="hover:bg-zinc-50/80">
                            <td class="py-3 px-3">
                                <div class="font-bold text-zinc-950">{{ $vendor->shop_name }}</div>
                                <div class="text-[11px] text-zinc-500">
                                    {{ $vendor->contact_person ?: ($vendor->user?->name ?? 'Merchant') }}
                                    • {{ $vendor->whatsapp_number ?: ($vendor->contact_phone ?: $vendor->email) }}
                                </div>
                                <div class="text-[10px] font-mono text-zinc-400">/{{ $vendor->slug }} • {{ $vendor->products_count }} items</div>
                            </td>
                            <td class="py-3 px-3">
                                <span class="px-2 py-0.5 rounded-md bg-zinc-100 text-zinc-700 text-[10px] font-bold uppercase">{{ $vendor->getCanonicalSectorKey() }}</span>
                                @if(is_array($vendor->operating_sectors) && count($vendor->operating_sectors) > 1)
                                    <span class="block text-[10px] text-zinc-400 mt-0.5">+{{ count($vendor->operating_sectors) - 1 }} sector</span>
                                @endif
                            </td>
                            <td class="py-3 px-3">
                                @php
                                    $vBadge = match($vStatus) {
                                        'verified' => 'bg-emerald-50 text-emerald-800 border-emerald-200',
                                        'rejected', 'suspended' => 'bg-rose-50 text-rose-800 border-rose-200',
                                        default => 'bg-amber-50 text-amber-800 border-amber-200',
                                    };
                                @endphp
                                <span class="inline-flex px-2 py-0.5 rounded-full border text-[10px] font-bold uppercase {{ $vBadge }}">
                                    {{ str_replace('_', ' ', $vStatus) }}
                                </span>
                            </td>
                            <td class="py-3 px-3">
                                @if($latestSub)
                                    <div class="font-semibold text-zinc-900">{{ $latestSub->plan_name_snapshot ?: ($latestSub->plan?->name ?? 'Plan') }} <span class="text-[10px] text-zinc-400">v{{ $latestSub->plan_version_number }}</span></div>
                                    <div class="text-[11px] text-zinc-500">
                                        <span class="uppercase font-bold {{ $latestSub->status === 'active' ? 'text-emerald-700' : ($latestSub->status === 'pending_activation' ? 'text-blue-700' : 'text-rose-700') }}">
                                            {{ str_replace('_', ' ', $latestSub->status) }}
                                        </span>
                                        @if($latestSub->ends_at)
                                            • Ends {{ $latestSub->ends_at->format('d M Y') }}
                                        @endif
                                    </div>
                                @else
                                    <span class="text-zinc-400 italic">No subscription assigned</span>
                                @endif
                            </td>
                            <td class="py-3 px-3">
                                @if($latestSub)
                                    @if($latestSub->is_free_snapshot)
                                        <span class="text-[11px] font-semibold text-zinc-500">Free Plan</span>
                                    @elseif($latestSub->external_payment_received)
                                        <span class="text-[11px] font-bold text-emerald-700">
                                            Received ₹{{ number_format(($latestSub->external_payment_amount ?? $latestSub->price_amount_snapshot) / 100, 0) }}
                                        </span>
                                        <span class="block text-[10px] text-zinc-400">{{ $latestSub->external_payment_method ?: 'Offline' }} {{ $latestSub->external_payment_ref ? '• ' . $latestSub->external_payment_ref : '' }}</span>
                                    @else
                                        <span class="text-[11px] font-semibold text-amber-700">Unpaid / Pending</span>
                                    @endif
                                @else
                                    <span class="text-zinc-400">—</span>
                                @endif
                            </td>
                            <td class="py-3 px-3 text-right">
                                <a href="{{ route('admin.vendor_management.show', $vendor) }}"
                                   class="inline-flex items-center px-3 py-1.5 rounded-lg bg-zinc-950 text-white text-[11px] font-bold hover:bg-zinc-800 transition">
                                    Manage →
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-10 text-center text-zinc-400 text-xs">No vendors matched this queue/filter.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="pt-2">
            {{ $vendors->links() }}
        </div>
    </div>
</x-layouts.admin>
