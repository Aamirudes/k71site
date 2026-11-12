<x-layouts.admin title="Plans & Subscriptions Overview">
    @include('admin.vendor-management._nav')

    <!-- Top Summary Strip -->
    <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-3">
        <div class="bg-white border border-zinc-200/80 rounded-2xl p-4">
            <p class="text-[10px] font-mono uppercase text-zinc-400">Active Vendors</p>
            <p class="text-2xl font-black text-zinc-950 mt-1">{{ $metrics['active_vendors'] }}</p>
            <p class="text-[11px] text-zinc-500 mt-0.5">{{ $metrics['active_subs'] }} active subs</p>
        </div>
        <div class="bg-white border border-zinc-200/80 rounded-2xl p-4">
            <p class="text-[10px] font-mono uppercase text-rose-600">Expired Plans</p>
            <p class="text-2xl font-black text-zinc-950 mt-1">{{ $metrics['expired_subs'] }}</p>
            <a href="{{ route('admin.vendor_management.index', ['tab' => 'expired']) }}" class="text-[11px] text-zinc-600 underline mt-0.5 inline-block">View expired →</a>
        </div>
        <div class="bg-white border border-zinc-200/80 rounded-2xl p-4">
            <p class="text-[10px] font-mono uppercase text-amber-600">Pending Renewals</p>
            <p class="text-2xl font-black text-zinc-950 mt-1">{{ $metrics['pending_renewals'] }}</p>
            <a href="{{ route('admin.vendor_management.history') }}" class="text-[11px] text-zinc-600 underline mt-0.5 inline-block">Review queue →</a>
        </div>
        <div class="bg-white border border-zinc-200/80 rounded-2xl p-4">
            <p class="text-[10px] font-mono uppercase text-orange-600">Expiring (7d)</p>
            <p class="text-2xl font-black text-zinc-950 mt-1">{{ $metrics['expiring_7d'] }}</p>
            <a href="{{ route('admin.vendor_management.index', ['tab' => 'expiring']) }}" class="text-[11px] text-zinc-600 underline mt-0.5 inline-block">Upcoming →</a>
        </div>
        <div class="bg-white border border-zinc-200/80 rounded-2xl p-4">
            <p class="text-[10px] font-mono uppercase text-blue-600">Pending Activation</p>
            <p class="text-2xl font-black text-zinc-950 mt-1">{{ $metrics['pending_activation'] }}</p>
            <a href="{{ route('admin.vendor_management.index', ['tab' => 'verified']) }}" class="text-[11px] text-zinc-600 underline mt-0.5 inline-block">Activate manually →</a>
        </div>
        <div class="bg-zinc-950 text-white rounded-2xl p-4">
            <p class="text-[10px] font-mono uppercase text-zinc-400">Offline Sub Revenue</p>
            <p class="text-2xl font-black mt-1">₹{{ number_format($metrics['sub_revenue_paise'] / 100, 0) }}</p>
            <p class="text-[11px] text-zinc-400 mt-0.5">{{ $metrics['total_vendors'] }} total vendors</p>
        </div>
    </div>

    <!-- Action Controls Bar -->
    <div class="bg-white border border-zinc-200/80 rounded-2xl p-4 flex flex-wrap items-center justify-between gap-3">
        <div class="flex flex-wrap items-center gap-2 text-xs">
            <span class="px-2.5 py-1 rounded-full font-bold {{ $strictEnforcement ? 'bg-emerald-100 text-emerald-900' : 'bg-amber-100 text-amber-900' }}">
                Visibility Enforcement: {{ $strictEnforcement ? 'ON' : 'OFF' }}
            </span>
            <span class="text-zinc-500 font-mono text-[11px]">Last Expiry Sweep: {{ $lastSweepAt ?: 'Never run yet' }}</span>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <form method="POST" action="{{ route('admin.vendor_management.automation.run_now') }}" class="inline">
                @csrf
                <button type="submit" class="px-3.5 py-2 rounded-xl border border-zinc-200 text-xs font-bold text-zinc-800 hover:bg-zinc-50">
                    Run Expiry Check Now
                </button>
            </form>
            <a href="{{ route('admin.vendor_management.plans') }}" class="px-3.5 py-2 rounded-xl bg-zinc-950 text-white text-xs font-bold hover:bg-zinc-800">
                Configure 3-Tier Plans →
            </a>
            <a href="{{ route('admin.vendor_management.reports.export') }}" class="px-3.5 py-2 rounded-xl border border-zinc-200 text-xs font-semibold text-zinc-700 hover:bg-zinc-50">
                Export CSV ↗
            </a>
        </div>
    </div>

    <!-- Sector Health Matrix -->
    <div class="bg-white border border-zinc-200/80 rounded-2xl p-5 space-y-3">
        <div class="flex items-center justify-between">
            <h3 class="text-sm font-black text-zinc-950">Sector Subscription Summary</h3>
            <a href="{{ route('admin.vendor_management.plans') }}" class="text-xs font-bold text-zinc-700 underline">Open Plan Builder →</a>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
            @foreach($sectorHealth as $row)
                <div class="rounded-xl border border-zinc-200 p-3.5 space-y-2 bg-zinc-50/50">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-black text-zinc-950">{{ $row['sector']->name }}</span>
                        <a href="{{ route('admin.vendor_management.plans', ['sector' => $row['sector']->key]) }}" class="text-[10px] font-mono underline text-zinc-500">Plans</a>
                    </div>
                    <div class="grid grid-cols-3 gap-1 text-center text-[11px]">
                        <div class="rounded bg-white border border-zinc-100 py-1.5">
                            <span class="block text-[9px] text-zinc-400">Active</span>
                            <strong class="text-emerald-700">{{ $row['active'] }}</strong>
                        </div>
                        <div class="rounded bg-white border border-zinc-100 py-1.5">
                            <span class="block text-[9px] text-zinc-400">Pending</span>
                            <strong class="text-blue-700">{{ $row['pending'] }}</strong>
                        </div>
                        <div class="rounded bg-white border border-zinc-100 py-1.5">
                            <span class="block text-[9px] text-zinc-400">Expired</span>
                            <strong class="text-rose-700">{{ $row['expired'] }}</strong>
                        </div>
                    </div>
                    <p class="text-[11px] font-mono text-zinc-600">Collected: <strong>₹{{ number_format($row['revenue_paise'] / 100, 0) }}</strong></p>
                </div>
            @endforeach
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Upcoming Expirations -->
        <div class="bg-white border border-zinc-200/80 rounded-2xl p-5 space-y-3">
            <div class="flex items-center justify-between">
                <h3 class="text-sm font-black text-zinc-950">Upcoming Expirations (Next 14 Days)</h3>
                <a href="{{ route('admin.vendor_management.index', ['tab' => 'expiring']) }}" class="text-xs font-semibold underline text-zinc-600">View All</a>
            </div>
            <div class="divide-y divide-zinc-100 text-xs">
                @forelse($upcomingExpirations as $sub)
                    <div class="py-2.5 flex items-center justify-between gap-3">
                        <div>
                            <a href="{{ route('admin.vendor_management.show', $sub->vendor_id) }}" class="font-bold text-zinc-950 underline">{{ $sub->vendor?->shop_name ?? 'Vendor #' . $sub->vendor_id }}</a>
                            <p class="text-[11px] text-zinc-500">{{ ucfirst($sub->sector_key) }} • {{ $sub->plan_name_snapshot }}</p>
                        </div>
                        <div class="text-right">
                            <span class="font-mono font-bold text-amber-700">{{ $sub->ends_at?->format('d M Y') }}</span>
                            <span class="block text-[10px] text-zinc-400">{{ $sub->days_remaining }}d left</span>
                        </div>
                    </div>
                @empty
                    <p class="py-6 text-center text-zinc-400">No active subscriptions expiring in the next 14 days.</p>
                @endforelse
            </div>
        </div>

        <!-- Pending Renewal Requests -->
        <div class="bg-white border border-zinc-200/80 rounded-2xl p-5 space-y-3">
            <div class="flex items-center justify-between">
                <h3 class="text-sm font-black text-zinc-950">Pending Renewal Requests</h3>
                <a href="{{ route('admin.vendor_management.history') }}" class="text-xs font-semibold underline text-zinc-600">Open Queue →</a>
            </div>
            <div class="divide-y divide-zinc-100 text-xs">
                @forelse($pendingRenewalList as $req)
                    <div class="py-2.5 flex items-start justify-between gap-3">
                        <div class="space-y-0.5">
                            <a href="{{ route('admin.vendor_management.show', $req->vendor_id) }}" class="font-bold text-zinc-950 underline">{{ $req->vendor?->shop_name ?? 'Vendor #' . $req->vendor_id }}</a>
                            <span class="ml-1.5 px-1.5 py-0.5 rounded bg-zinc-100 text-[10px] font-mono uppercase">{{ $req->sector_key }}</span>
                            <p class="text-[11px] text-zinc-600 line-clamp-2">{{ $req->message }}</p>
                        </div>
                        <a href="{{ route('admin.vendor_management.history') }}" class="shrink-0 px-2.5 py-1 rounded-lg bg-zinc-950 text-white text-[11px] font-bold">
                            Review
                        </a>
                    </div>
                @empty
                    <p class="py-6 text-center text-zinc-400">No pending vendor renewal requests.</p>
                @endforelse
            </div>
        </div>
    </div>
</x-layouts.admin>

