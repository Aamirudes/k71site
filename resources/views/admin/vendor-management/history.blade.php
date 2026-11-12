<x-layouts.admin title="Renewal Requests & Offline Ledger">
    @include('admin.vendor-management._nav')

    <!-- Vendor Renewal Requests Queue -->
    <div class="bg-white border border-zinc-200/80 rounded-2xl p-5 space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-zinc-100 pb-3">
            <div>
                <h2 class="text-sm font-black text-zinc-950">Vendor Renewal &amp; Plan Upgrade Requests</h2>
                <p class="text-[11px] text-zinc-500 mt-0.5">Read vendor renewal messages, reply directly to their lock screen/portal, reject, or approve &amp; renew in one click.</p>
            </div>
            <div class="flex flex-wrap gap-1.5 text-xs">
                @foreach(['' => 'All', 'pending' => 'Pending', 'reviewed' => 'Replied', 'fulfilled' => 'Fulfilled', 'rejected' => 'Rejected'] as $sVal => $sLabel)
                    <a href="{{ route('admin.vendor_management.history', $sVal ? ['status' => $sVal] : []) }}"
                       class="px-3 py-1.5 rounded-xl font-bold transition {{ ($statusFilter ?? '') === $sVal ? 'bg-zinc-950 text-white' : 'bg-zinc-100 text-zinc-600 hover:bg-zinc-200' }}">
                        {{ $sLabel }}
                    </a>
                @endforeach
            </div>
        </div>

        <div class="space-y-4">
            @forelse($renewalRequests as $req)
                @php
                    $waDigits = preg_replace('/\D+/', '', (string) ($req->vendor?->whatsapp_number ?: $req->vendor?->contact_phone ?: ''));
                    $waLink = strlen($waDigits) >= 10 ? 'https://wa.me/' . (strlen($waDigits) === 10 ? '91' . $waDigits : $waDigits) : null;
                @endphp
                <div class="rounded-2xl border border-zinc-200 p-4 space-y-3 bg-zinc-50/50">
                    <div class="flex flex-col md:flex-row md:items-center justify-between gap-2">
                        <div class="flex flex-wrap items-center gap-2">
                            <a href="{{ route('admin.vendor_management.show', $req->vendor_id) }}" class="text-sm font-black text-zinc-950 underline">
                                {{ $req->vendor?->shop_name ?? 'Vendor #' . $req->vendor_id }}
                            </a>
                            <span class="px-2 py-0.5 rounded bg-zinc-200 text-zinc-800 text-[10px] font-mono uppercase">{{ $req->sector_key }}</span>
                            <span class="text-xs text-zinc-600">
                                Plan: <strong>{{ $req->requestedPlan?->name ?? ($req->currentSubscription?->plan_name_snapshot ?: 'Renewal') }}</strong>
                            </span>
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase {{ $req->status === 'pending' ? 'bg-amber-100 text-amber-900' : (in_array($req->status, ['fulfilled', 'approved'], true) ? 'bg-emerald-100 text-emerald-900' : ($req->status === 'rejected' ? 'bg-rose-100 text-rose-900' : 'bg-zinc-200 text-zinc-800')) }}">
                                {{ $req->status }}
                            </span>
                        </div>
                        <div class="flex items-center gap-2 text-[11px] font-mono text-zinc-500">
                            <span>#{{ $req->id }} • {{ $req->created_at?->format('d M Y H:i') }}</span>
                            @if($waLink)
                                <a href="{{ $waLink }}" target="_blank" class="px-2 py-1 rounded bg-emerald-600 text-white font-sans font-bold text-[10px]">WhatsApp ↗</a>
                            @endif
                        </div>
                    </div>

                    <div class="rounded-xl bg-white border border-zinc-200 p-3 text-xs text-zinc-800">
                        {{ $req->message }}
                    </div>

                    @if($req->admin_reply || $req->rejection_reason)
                        <div class="rounded-xl bg-zinc-900 text-white p-3 text-xs">
                            <span class="text-[10px] font-mono uppercase text-zinc-400 block">Sent Admin Response ({{ $req->replied_at?->format('d M Y H:i') ?? 'Recorded' }})</span>
                            <p class="mt-0.5">{{ $req->admin_reply ?: $req->rejection_reason }}</p>
                        </div>
                    @endif

                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-3 pt-1">
                        <!-- One-Click Approve & Renew Subscription -->
                        <form method="POST" action="{{ route('admin.vendor_management.renewal_requests.approve_renew', $req) }}" class="lg:col-span-2 rounded-xl border border-emerald-200 bg-emerald-50/40 p-3 space-y-2 text-xs">
                            @csrf
                            <p class="text-[10px] font-black uppercase text-emerald-950">Approve &amp; Renew / Activate Immediately</p>
                            <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
                                <select name="plan_id" class="col-span-2 rounded-lg border border-zinc-200 px-2 py-1.5 text-xs bg-white">
                                    <option value="">Same / Sector Default Plan</option>
                                    @foreach($activePlans as $ap)
                                        <option value="{{ $ap->id }}" @selected((int) ($req->requested_plan_id ?? $req->currentSubscription?->plan_id) === (int) $ap->id)>
                                            [{{ strtoupper($ap->sector_key) }}] {{ $ap->name }} ({{ $ap->is_free ? 'Free' : '₹' . number_format($ap->price_amount / 100, 0) }})
                                        </option>
                                    @endforeach
                                </select>
                                <input type="number" name="duration_days" min="1" placeholder="Days (default)" class="rounded-lg border border-zinc-200 px-2 py-1.5 text-xs bg-white">
                                <input type="text" name="external_payment_ref" placeholder="UPI/UTR Ref" class="rounded-lg border border-zinc-200 px-2 py-1.5 text-xs bg-white">
                            </div>
                            <div class="flex flex-wrap items-center gap-2">
                                <label class="inline-flex items-center gap-1.5 text-[11px] font-semibold">
                                    <input type="checkbox" name="external_payment_received" value="1"> Offline Payment Confirmed
                                </label>
                                <input type="text" name="admin_reply" placeholder="Reply shown to vendor upon activation..." class="flex-1 min-w-[180px] rounded-lg border border-zinc-200 px-2.5 py-1.5 text-xs bg-white">
                                <button type="submit" class="px-3.5 py-1.5 rounded-lg bg-zinc-950 text-white text-xs font-bold hover:bg-zinc-800">
                                    Approve &amp; Renew
                                </button>
                            </div>
                        </form>

                        <!-- Reply OR Reject -->
                        <div class="space-y-2">
                            <form method="POST" action="{{ route('admin.vendor_management.renewal_requests.reply', $req) }}" class="flex gap-1.5">
                                @csrf
                                <input type="text" name="admin_reply" required placeholder="Reply to vendor (e.g. Share UTR on WhatsApp)" class="flex-1 rounded-lg border border-zinc-200 px-2.5 py-1.5 text-xs bg-white">
                                <button type="submit" class="px-3 py-1.5 rounded-lg border border-zinc-300 bg-white text-xs font-bold hover:bg-zinc-100">Reply</button>
                            </form>
                            <form method="POST" action="{{ route('admin.vendor_management.renewal_requests.reject', $req) }}" class="flex gap-1.5">
                                @csrf
                                <input type="text" name="rejection_reason" required placeholder="Reject reason (shown to vendor)" class="flex-1 rounded-lg border border-rose-200 px-2.5 py-1.5 text-xs bg-white">
                                <button type="submit" class="px-3 py-1.5 rounded-lg border border-rose-300 bg-rose-50 text-rose-800 text-xs font-bold hover:bg-rose-100">Reject</button>
                            </form>
                        </div>
                    </div>
                </div>
            @empty
                <div class="py-8 text-center text-xs text-zinc-400">No vendor renewal requests found.</div>
            @endforelse
        </div>
        {{ $renewalRequests->links() }}
    </div>

    <!-- Collapsible Offline Payment Confirmations Log -->
    <details class="bg-white border border-zinc-200/80 rounded-2xl p-5 group">
        <summary class="cursor-pointer list-none flex items-center justify-between text-sm font-black text-zinc-950">
            <span>Manual Offline Payment Confirmations ({{ $manualPayments->total() }})</span>
            <span class="text-xs font-mono text-zinc-400 group-open:hidden">Expand ↓</span>
            <span class="text-xs font-mono text-zinc-400 hidden group-open:inline">Collapse ↑</span>
        </summary>
        <div class="mt-4 overflow-x-auto">
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
            <div class="mt-3">{{ $manualPayments->links() }}</div>
        </div>
    </details>

    <!-- Collapsible Subscription Lifecycle Events -->
    <details class="bg-white border border-zinc-200/80 rounded-2xl p-5 group">
        <summary class="cursor-pointer list-none flex items-center justify-between text-sm font-black text-zinc-950">
            <span>Subscription Lifecycle Audit Events ({{ $events->total() }})</span>
            <span class="text-xs font-mono text-zinc-400 group-open:hidden">Expand ↓</span>
            <span class="text-xs font-mono text-zinc-400 hidden group-open:inline">Collapse ↑</span>
        </summary>
        <div class="mt-4 divide-y divide-zinc-100 text-xs">
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
            <div class="mt-3">{{ $events->links() }}</div>
        </div>
    </details>
</x-layouts.admin>
