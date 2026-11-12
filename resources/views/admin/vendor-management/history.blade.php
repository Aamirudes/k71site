<x-layouts.admin title="Payments, Events & Renewal Requests">
    @include('admin.vendor-management._nav')

    <!-- Vendor Renewal Requests Queue -->
    <div class="bg-white border border-zinc-200/80 rounded-2xl p-5 space-y-4">
        <h3 class="text-sm font-black text-zinc-950">Vendor Renewal &amp; Upgrade Requests</h3>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="border-b border-zinc-100 text-[10px] font-mono uppercase text-zinc-400">
                        <th class="py-2 px-2">Vendor</th>
                        <th class="py-2 px-2">Sector / Requested Plan</th>
                        <th class="py-2 px-2">Vendor Message</th>
                        <th class="py-2 px-2">Status</th>
                        <th class="py-2 px-2">Admin Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-100">
                    @forelse($renewalRequests as $req)
                        <tr>
                            <td class="py-2.5 px-2">
                                <a href="{{ route('admin.vendor_management.show', $req->vendor_id) }}" class="font-bold underline">{{ $req->vendor?->shop_name ?? 'Vendor #' . $req->vendor_id }}</a>
                            </td>
                            <td class="py-2.5 px-2">
                                <span class="uppercase font-mono text-[10px] bg-zinc-100 px-1.5 py-0.5 rounded">{{ $req->sector_key }}</span>
                                {{ $req->requestedPlan?->name ?? 'General Renewal' }}
                            </td>
                            <td class="py-2.5 px-2 text-zinc-600">{{ $req->message }}</td>
                            <td class="py-2.5 px-2">
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase {{ $req->status === 'pending' ? 'bg-amber-100 text-amber-800' : 'bg-emerald-100 text-emerald-800' }}">
                                    {{ $req->status }}
                                </span>
                            </td>
                            <td class="py-2.5 px-2">
                                <form method="POST" action="{{ route('admin.vendor_management.renewal_requests.resolve', $req) }}" class="flex items-center gap-1.5">
                                    @csrf
                                    <select name="status" class="rounded border border-zinc-200 px-2 py-1 text-[11px]">
                                        @foreach(['pending', 'reviewed', 'fulfilled', 'dismissed'] as $st)
                                            <option value="{{ $st }}" @selected($req->status === $st)>{{ ucfirst($st) }}</option>
                                        @endforeach
                                    </select>
                                    <input type="text" name="admin_notes" value="{{ $req->admin_notes }}" placeholder="Note" class="rounded border border-zinc-200 px-2 py-1 text-[11px]">
                                    <button type="submit" class="px-2.5 py-1 rounded bg-zinc-950 text-white text-[11px] font-bold">Save</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="py-6 text-center text-zinc-400">No vendor renewal requests yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $renewalRequests->links() }}
    </div>

    <!-- Offline Payment Confirmations Log -->
    <div class="bg-white border border-zinc-200/80 rounded-2xl p-5 space-y-4">
        <h3 class="text-sm font-black text-zinc-950">Manual Offline Payment Confirmations (WhatsApp / Bank / UPI)</h3>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="border-b border-zinc-100 text-[10px] font-mono uppercase text-zinc-400">
                        <th class="py-2 px-2">Sub #</th>
                        <th class="py-2 px-2">Vendor</th>
                        <th class="py-2 px-2">Plan / Sector</th>
                        <th class="py-2 px-2">Amount</th>
                        <th class="py-2 px-2">Method &amp; Ref</th>
                        <th class="py-2 px-2">Status</th>
                        <th class="py-2 px-2">Date</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-100">
                    @forelse($manualPayments as $mp)
                        <tr>
                            <td class="py-2 px-2 font-mono">#{{ $mp->id }}</td>
                            <td class="py-2 px-2"><a href="{{ route('admin.vendor_management.show', $mp->vendor_id) }}" class="font-bold underline">{{ $mp->vendor?->shop_name }}</a></td>
                            <td class="py-2 px-2">{{ $mp->plan_name_snapshot }} ({{ $mp->sector_key }})</td>
                            <td class="py-2 px-2 font-bold text-emerald-700">₹{{ number_format(($mp->external_payment_amount ?? 0) / 100, 2) }}</td>
                            <td class="py-2 px-2">{{ $mp->external_payment_method ?: 'Offline' }} {{ $mp->external_payment_ref ? '• ' . $mp->external_payment_ref : '' }}</td>
                            <td class="py-2 px-2 uppercase text-[10px] font-bold">{{ $mp->status }}</td>
                            <td class="py-2 px-2 text-zinc-500">{{ $mp->external_payment_date?->format('d M Y') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="py-6 text-center text-zinc-400">No external payments recorded yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $manualPayments->links() }}
    </div>

    <!-- Subscription Lifecycle Events -->
    <div class="bg-white border border-zinc-200/80 rounded-2xl p-5 space-y-4">
        <h3 class="text-sm font-black text-zinc-950">Subscription Lifecycle Events</h3>
        <div class="divide-y divide-zinc-100 text-xs">
            @forelse($events as $ev)
                <div class="py-2.5 flex items-center justify-between gap-3">
                    <div>
                        <span class="font-mono font-bold text-zinc-950">{{ $ev->event_type }}</span>
                        <span class="text-zinc-600">— <a href="{{ route('admin.vendor_management.show', $ev->vendor_id) }}" class="underline font-semibold">{{ $ev->vendor?->shop_name ?? 'Vendor #' . $ev->vendor_id }}</a> (Sub #{{ $ev->vendor_subscription_id }})</span>
                        <span class="text-zinc-500">{{ $ev->from_status ? "{$ev->from_status} → " : '' }}{{ $ev->to_status }}</span>
                        @if($ev->reason) <span class="block text-[11px] text-zinc-400">{{ $ev->reason }}</span> @endif
                    </div>
                    <span class="text-[10px] font-mono text-zinc-400 shrink-0">{{ $ev->created_at?->format('d M Y H:i') }} ({{ $ev->actor_type }})</span>
                </div>
            @empty
                <p class="py-6 text-center text-zinc-400">No subscription events logged yet.</p>
            @endforelse
        </div>
        {{ $events->links() }}
    </div>
</x-layouts.admin>
