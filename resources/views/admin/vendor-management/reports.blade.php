<x-layouts.admin title="Monetization & Commission Reports">
    @include('admin.vendor-management._nav')

    <div class="bg-white border border-zinc-200/80 rounded-2xl p-4 flex flex-wrap items-center justify-between gap-3">
        <form method="GET" class="flex flex-wrap items-center gap-2 text-xs">
            <label class="font-bold text-zinc-700">From:</label>
            <input type="date" name="from" value="{{ $startDate->format('Y-m-d') }}" class="rounded-xl border border-zinc-200 px-3 py-1.5">
            <label class="font-bold text-zinc-700">To:</label>
            <input type="date" name="to" value="{{ $endDate->format('Y-m-d') }}" class="rounded-xl border border-zinc-200 px-3 py-1.5">
            <button type="submit" class="px-4 py-1.5 rounded-xl bg-zinc-950 text-white font-bold">Apply Range</button>
        </form>
        <a href="{{ route('admin.vendor_management.reports.export') }}" class="px-3.5 py-2 rounded-xl border border-zinc-200 text-xs font-bold hover:bg-zinc-50">
            Export Subscriptions CSV ↓
        </a>
    </div>

    <!-- Funnel & Financial Summary -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white border border-zinc-200/80 rounded-2xl p-4">
            <p class="text-[10px] font-mono uppercase text-zinc-400">Offline Subscription Revenue</p>
            <p class="text-2xl font-black text-zinc-950 mt-1">₹{{ number_format($subRevenueTotal / 100, 2) }}</p>
            <p class="text-[11px] text-zinc-500 mt-0.5">Confirmed external plan payments</p>
        </div>
        <div class="bg-white border border-zinc-200/80 rounded-2xl p-4">
            <p class="text-[10px] font-mono uppercase text-zinc-400">Gross Order Volume</p>
            <p class="text-2xl font-black text-zinc-950 mt-1">₹{{ number_format($commissionSummary['gross_paise'] / 100, 2) }}</p>
            <p class="text-[11px] text-zinc-500 mt-0.5">{{ $commissionSummary['transactions_count'] }} ledger transaction(s)</p>
        </div>
        <div class="bg-white border border-zinc-200/80 rounded-2xl p-4">
            <p class="text-[10px] font-mono uppercase text-zinc-400">Net Platform Commission</p>
            <p class="text-2xl font-black text-emerald-700 mt-1">₹{{ number_format($commissionSummary['commission_paise'] / 100, 2) }}</p>
            <p class="text-[11px] text-zinc-500 mt-0.5">Calculated via hierarchy rules</p>
        </div>
        <div class="bg-white border border-zinc-200/80 rounded-2xl p-4">
            <p class="text-[10px] font-mono uppercase text-zinc-400">Net Vendor Payout</p>
            <p class="text-2xl font-black text-zinc-950 mt-1">₹{{ number_format($commissionSummary['vendor_net_paise'] / 100, 2) }}</p>
            <p class="text-[11px] text-zinc-500 mt-0.5">Verified {{ $funnel['vendors_verified'] }} • Active Subs {{ $funnel['vendors_with_active_sub'] }}</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Sector Breakdown -->
        <div class="bg-white border border-zinc-200/80 rounded-2xl p-5 space-y-3">
            <h3 class="text-sm font-black text-zinc-950">Subscriptions &amp; Offline Revenue by Sector</h3>
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="border-b border-zinc-100 text-[10px] font-mono uppercase text-zinc-400">
                        <th class="py-2">Sector</th>
                        <th class="py-2">Active</th>
                        <th class="py-2">Grace</th>
                        <th class="py-2">Pending</th>
                        <th class="py-2">Expired</th>
                        <th class="py-2 text-right">Plan Revenue</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-100">
                    @foreach($sectorBreakdown as $row)
                        <tr>
                            <td class="py-2.5 font-bold">{{ $row['name'] }}</td>
                            <td class="py-2.5">{{ $row['active'] }}</td>
                            <td class="py-2.5">{{ $row['grace'] }}</td>
                            <td class="py-2.5">{{ $row['pending'] }}</td>
                            <td class="py-2.5">{{ $row['expired'] }}</td>
                            <td class="py-2.5 text-right font-bold">₹{{ number_format($row['sub_revenue_paise'] / 100, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Order Commissions by Sector -->
        <div class="bg-white border border-zinc-200/80 rounded-2xl p-5 space-y-3">
            <h3 class="text-sm font-black text-zinc-950">Order Commission Breakdown by Sector</h3>
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="border-b border-zinc-100 text-[10px] font-mono uppercase text-zinc-400">
                        <th class="py-2">Sector</th>
                        <th class="py-2">Tx Count</th>
                        <th class="py-2">Gross</th>
                        <th class="py-2">Commission</th>
                        <th class="py-2 text-right">Vendor Net</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-100">
                    @forelse($commissionBySector as $crow)
                        <tr>
                            <td class="py-2.5 font-bold uppercase">{{ $crow->sector_key }}</td>
                            <td class="py-2.5">{{ $crow->tx_count }}</td>
                            <td class="py-2.5">₹{{ number_format($crow->gross_paise / 100, 2) }}</td>
                            <td class="py-2.5 font-bold text-emerald-700">₹{{ number_format($crow->commission_paise / 100, 2) }}</td>
                            <td class="py-2.5 text-right">₹{{ number_format($crow->vendor_net_paise / 100, 2) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="py-6 text-center text-zinc-400">No commission ledger activity in selected range.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-layouts.admin>
