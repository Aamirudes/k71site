<x-layouts.vendor title="My Plan, Entitlements & Commission">
    <div class="max-w-6xl mx-auto space-y-6 pb-12">
        @if(session('success'))
            <div class="rounded-2xl border border-emerald-200 bg-emerald-50 p-4 text-xs font-semibold text-emerald-900">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="rounded-2xl border border-rose-200 bg-rose-50 p-4 text-xs font-semibold text-rose-900">
                {{ session('error') }}
            </div>
        @endif

        <!-- Verification & Subscription Overview Header -->
        <div class="bg-white border border-zinc-200 rounded-2xl p-6 flex flex-col lg:flex-row lg:items-center justify-between gap-4">
            <div class="space-y-1">
                <div class="flex flex-wrap items-center gap-2">
                    <span class="px-3 py-1 rounded-full text-[11px] font-bold uppercase {{ $vendor->isVerifiedMerchant() ? 'bg-emerald-100 text-emerald-900' : 'bg-amber-100 text-amber-900' }}">
                        Verification: {{ ucfirst(str_replace('_', ' ', $vendor->verification_status ?: $vendor->status)) }}
                    </span>
                    <span class="px-3 py-1 rounded-full text-[11px] font-mono bg-zinc-100 text-zinc-700">
                        Managed Manually by K71 Admin via WhatsApp
                    </span>
                </div>
                <h1 class="text-2xl font-black text-zinc-950">{{ $vendor->shop_name }} — Plan &amp; Access</h1>
                <p class="text-xs text-zinc-600 max-w-2xl">
                    Subscriptions and commission terms are verified and activated personally by the K71 administrator via WhatsApp. Online self-checkout is disabled.
                </p>
            </div>
        </div>

        <!-- Per-Sector Active Plans & Usage Meters -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            @foreach($sectorCards as $secKey => $card)
                @php
                    $activeSub = $card['active_sub'];
                    $latestSub = $card['latest_sub'];
                    $displaySub = $activeSub ?: $latestSub;
                @endphp
                <div class="bg-white border border-zinc-200 rounded-2xl p-5 space-y-4">
                    <div class="flex items-start justify-between gap-3 pb-3 border-b border-zinc-100">
                        <div>
                            <p class="text-[10px] font-mono uppercase text-zinc-400">Sector: {{ $card['sector_label'] }}</p>
                            <h2 class="text-lg font-black text-zinc-950">
                                {{ $displaySub ? ($displaySub->plan_name_snapshot . ' (v' . $displaySub->plan_version_number . ')') : 'No Plan Assigned Yet' }}
                            </h2>
                        </div>
                        @if($displaySub)
                            <span class="px-3 py-1 rounded-full text-[11px] font-bold uppercase {{ $displaySub->status === 'active' ? 'bg-emerald-100 text-emerald-900' : ($displaySub->status === 'pending_activation' ? 'bg-blue-100 text-blue-900' : ($displaySub->status === 'grace_period' ? 'bg-amber-100 text-amber-900' : 'bg-rose-100 text-rose-900')) }}">
                                {{ str_replace('_', ' ', $displaySub->status) }}
                            </span>
                        @else
                            <span class="px-3 py-1 rounded-full text-[11px] font-bold uppercase bg-zinc-100 text-zinc-600">Inactive</span>
                        @endif
                    </div>

                    @if($displaySub)
                        <div class="grid grid-cols-3 gap-2 text-xs">
                            <div class="rounded-xl bg-zinc-50 p-3 border border-zinc-100">
                                <span class="block text-[10px] text-zinc-400 font-mono">START DATE</span>
                                <strong class="text-zinc-900">{{ $displaySub->starts_at?->format('d M Y') ?? 'Pending' }}</strong>
                            </div>
                            <div class="rounded-xl bg-zinc-50 p-3 border border-zinc-100">
                                <span class="block text-[10px] text-zinc-400 font-mono">EXPIRES ON</span>
                                <strong class="text-zinc-900">{{ $displaySub->ends_at?->format('d M Y') ?? 'Pending' }}</strong>
                            </div>
                            <div class="rounded-xl bg-zinc-50 p-3 border border-zinc-100">
                                <span class="block text-[10px] text-zinc-400 font-mono">REMAINING</span>
                                <strong class="text-zinc-900">{{ $displaySub->days_remaining !== null ? $displaySub->days_remaining . ' day(s)' : '—' }}</strong>
                            </div>
                        </div>
                    @endif

                    <!-- Read-only Commission Rate -->
                    <div class="rounded-xl bg-zinc-950 text-white p-3.5 text-xs flex items-center justify-between">
                        <div>
                            <span class="text-[10px] font-mono uppercase text-zinc-400 block">Effective Platform Commission</span>
                            <strong>{{ $card['commission']['rate_percent'] }}%</strong>
                            @if(($card['commission']['fixed_fee_paise'] ?? 0) > 0)
                                + ₹{{ number_format($card['commission']['fixed_fee_paise'] / 100, 2) }} fixed
                            @endif
                        </div>
                        <span class="text-[10px] font-mono uppercase text-zinc-400">{{ $card['commission']['name'] }}</span>
                    </div>

                    <!-- Entitlements & Live Usage -->
                    <div class="space-y-2">
                        <p class="text-[11px] font-black uppercase text-zinc-700">Included Limits &amp; Features</p>
                        @foreach($card['usage'] as $item)
                            <div class="flex items-center justify-between text-xs py-1 border-b border-zinc-100 last:border-0">
                                <span class="text-zinc-700">{{ $item['label'] }}</span>
                                @if($item['type'] === 'bool')
                                    <span class="font-bold {{ $item['allowed'] ? 'text-emerald-700' : 'text-zinc-400' }}">
                                        {{ $item['allowed'] ? 'Enabled' : 'Locked' }}
                                    </span>
                                @else
                                    <span class="font-mono font-bold text-zinc-900">
                                        {{ $item['used'] }} / {{ $item['limit'] === null ? 'Unlimited' : $item['limit'] }}
                                    </span>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Request Renewal / Upgrade Form (Offline Admin Workflow) -->
            <div class="bg-white border border-zinc-200 rounded-2xl p-6 space-y-4">
                <div>
                    <h3 class="text-base font-black text-zinc-950">Request Renewal or Plan Upgrade</h3>
                    <p class="text-xs text-zinc-500 mt-0.5">Submit your request here and coordinate with the K71 administrator on WhatsApp. Changes take effect once the admin confirms activation.</p>
                </div>

                <form method="POST" action="{{ route('vendor.subscription.request_renewal') }}" class="space-y-3">
                    @csrf
                    <div>
                        <label class="block text-[11px] font-bold text-zinc-700 mb-1">Sector</label>
                        <select name="sector_key" class="w-full rounded-xl border border-zinc-200 px-3 py-2 text-xs font-semibold">
                            @foreach($sectorCards as $secKey => $card)
                                <option value="{{ $secKey }}">{{ $card['sector_label'] }}</option>
                            @endforeach
                        </select>
                    </div>

                    @if($publicPlans->isNotEmpty())
                        <div>
                            <label class="block text-[11px] font-bold text-zinc-700 mb-1">Preferred Plan (Optional)</label>
                            <select name="requested_plan_id" class="w-full rounded-xl border border-zinc-200 px-3 py-2 text-xs">
                                <option value="">Discuss with admin on WhatsApp</option>
                                @foreach($publicPlans as $pp)
                                    <option value="{{ $pp->id }}">{{ $pp->name }} ({{ $pp->sector_key }} — {{ $pp->is_free ? 'Free' : '₹' . number_format($pp->price_amount / 100, 0) }})</option>
                                @endforeach
                            </select>
                        </div>
                    @endif

                    <div>
                        <label class="block text-[11px] font-bold text-zinc-700 mb-1">Message to Admin</label>
                        <textarea name="message" rows="3" placeholder="Mention your WhatsApp contact, renewal duration, or upgrade needs..." class="w-full rounded-xl border border-zinc-200 px-3 py-2 text-xs"></textarea>
                    </div>

                    <button type="submit" class="w-full py-2.5 rounded-xl bg-zinc-950 text-white text-xs font-bold hover:bg-zinc-800 transition">
                        Send Renewal / Activation Request to Admin
                    </button>
                </form>

                @if($renewalRequests->isNotEmpty())
                    <div class="pt-3 border-t border-zinc-100 space-y-2">
                        <p class="text-[11px] font-bold uppercase text-zinc-500">Your Recent Requests</p>
                        @foreach($renewalRequests as $rr)
                            <div class="rounded-lg border border-zinc-100 bg-zinc-50 p-2.5 space-y-1 text-xs">
                                <div class="flex items-center justify-between">
                                    <span class="font-semibold">{{ ucfirst($rr->sector_key) }} — {{ $rr->requestedPlan?->name ?? 'Renewal' }}</span>
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase bg-zinc-200 text-zinc-900">{{ $rr->status }}</span>
                                </div>
                                @if($rr->admin_reply || $rr->rejection_reason)
                                    <p class="text-[11px] text-zinc-700"><strong>Admin Reply:</strong> {{ $rr->admin_reply ?: $rr->rejection_reason }}</p>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            <!-- Notifications & Reminders Inbox -->
            <div class="bg-white border border-zinc-200 rounded-2xl p-6 space-y-4">
                <h3 class="text-base font-black text-zinc-950">Subscription Alerts &amp; Reminders</h3>
                <div class="divide-y divide-zinc-100 text-xs">
                    @forelse($notifications as $notif)
                        <div class="py-3">
                            <div class="flex items-center justify-between gap-2">
                                <strong class="text-zinc-950">{{ $notif->title }}</strong>
                                <span class="text-[10px] font-mono text-zinc-400">{{ $notif->created_at?->format('d M Y') }}</span>
                            </div>
                            <p class="text-zinc-600 mt-1">{{ $notif->message }}</p>
                        </div>
                    @empty
                        <p class="py-8 text-center text-zinc-400">No expiry reminders or alerts at this time.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</x-layouts.vendor>
