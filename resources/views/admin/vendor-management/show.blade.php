<x-layouts.admin :title="'Manage Vendor • ' . $vendor->shop_name">
    @include('admin.vendor-management._nav')

    @php
        $waDigits = preg_replace('/\D+/', '', (string) ($vendor->whatsapp_number ?: $vendor->contact_phone ?: ''));
        $waLink = strlen($waDigits) >= 10 ? 'https://wa.me/' . (strlen($waDigits) === 10 ? '91' . $waDigits : $waDigits) : null;
        $vStatus = $vendor->verification_status ?: ($vendor->status === 'approved' ? 'verified' : 'pending_review');
    @endphp

    <!-- Top Merchant Identity Header -->
    <div class="bg-white border border-zinc-200/80 rounded-2xl p-5 flex flex-col lg:flex-row lg:items-center justify-between gap-4">
        <div class="space-y-1">
            <div class="flex flex-wrap items-center gap-2">
                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase {{ $vendor->isVerifiedMerchant() ? 'bg-emerald-100 text-emerald-900' : 'bg-amber-100 text-amber-900' }}">
                    Verification: {{ str_replace('_', ' ', $vStatus) }}
                </span>
                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-mono uppercase bg-zinc-100 text-zinc-700">
                    Primary Sector: {{ $vendorPrimarySector }}
                </span>
                @if($vendor->last_admin_action)
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-mono bg-zinc-100 text-zinc-500">
                        Last Action: {{ $vendor->last_admin_action }}
                    </span>
                @endif
            </div>
            <h2 class="text-xl font-black text-zinc-950">{{ $vendor->shop_name }}</h2>
            <p class="text-xs text-zinc-500">
                Contact: {{ $vendor->contact_person ?: ($vendor->user?->name ?? 'N/A') }}
                • Email: {{ $vendor->email ?: $vendor->user?->email }}
                • Phone/WhatsApp: {{ $vendor->whatsapp_number ?: ($vendor->contact_phone ?: 'Not set') }}
                • City: {{ $vendor->city ?: 'N/A' }}
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            @if($waLink)
                <a href="{{ $waLink }}" target="_blank" class="px-3.5 py-2 rounded-xl bg-emerald-600 text-white text-xs font-bold hover:bg-emerald-700 transition">
                    Message on WhatsApp ↗
                </a>
            @endif
            <a href="{{ $vendor->storefrontUrl(['preview' => 1]) }}" target="_blank" class="px-3.5 py-2 rounded-xl border border-zinc-200 text-xs font-bold text-zinc-700 hover:bg-zinc-50 transition">
                Admin Preview Storefront ↗
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- LEFT COLUMN: Verification + Assign Plan -->
        <div class="space-y-6">
            <!-- 1. Verification Control -->
            <div class="bg-white border border-zinc-200/80 rounded-2xl p-5 space-y-4">
                <div>
                    <h3 class="text-sm font-black text-zinc-950">1. Manual Verification</h3>
                    <p class="text-[11px] text-zinc-500 mt-0.5">Verify documents &amp; identity from WhatsApp. Verification NEVER activates any subscription automatically.</p>
                </div>

                <form method="POST" action="{{ route('admin.vendor_management.verification.update', $vendor) }}" class="space-y-3">
                    @csrf
                    <div>
                        <label class="block text-[11px] font-bold text-zinc-700 mb-1">Verification Decision</label>
                        <select name="verification_status" class="w-full rounded-xl border border-zinc-200 px-3 py-2 text-xs font-semibold">
                            @foreach(['unverified' => 'Unverified', 'pending_review' => 'Pending Review', 'verified' => 'Verified (Eligible for Plan)', 'rejected' => 'Rejected', 'suspended' => 'Suspended'] as $optVal => $optLabel)
                                <option value="{{ $optVal }}" @selected($vStatus === $optVal)>{{ $optLabel }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="block text-[11px] font-bold text-zinc-700 mb-1">Contact Person</label>
                            <input type="text" name="contact_person" value="{{ $vendor->contact_person ?: $vendor->user?->name }}" class="w-full rounded-xl border border-zinc-200 px-3 py-1.5 text-xs">
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold text-zinc-700 mb-1">WhatsApp Number</label>
                            <input type="text" name="whatsapp_number" value="{{ $vendor->whatsapp_number ?: $vendor->contact_phone }}" class="w-full rounded-xl border border-zinc-200 px-3 py-1.5 text-xs">
                        </div>
                    </div>

                    <div>
                        <label class="block text-[11px] font-bold text-zinc-700 mb-1">Permitted Sectors</label>
                        <div class="flex flex-wrap gap-2">
                            @foreach($allSectors as $sec)
                                <label class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg border border-zinc-200 text-xs">
                                    <input type="checkbox" name="operating_sectors[]" value="{{ $sec->key }}" @checked(in_array($sec->key, $vendorSectors, true))>
                                    <span>{{ ucfirst($sec->key) }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <div>
                        <label class="block text-[11px] font-bold text-zinc-700 mb-1">Internal Verification Notes</label>
                        <textarea name="verification_notes" rows="2" class="w-full rounded-xl border border-zinc-200 px-3 py-2 text-xs" placeholder="WhatsApp KYC check, GST/FSSAI notes...">{{ $vendor->verification_notes }}</textarea>
                    </div>

                    <button type="submit" class="w-full py-2.5 rounded-xl bg-zinc-950 text-white text-xs font-bold hover:bg-zinc-800 transition">
                        Save Verification Decision
                    </button>
                </form>
            </div>

            <!-- 2. Assign New Subscription Plan -->
            <div class="bg-white border border-zinc-200/80 rounded-2xl p-5 space-y-4">
                <div>
                    <h3 class="text-sm font-black text-zinc-950">2. Assign Sector Plan (Pending Activation)</h3>
                    <p class="text-[11px] text-zinc-500 mt-0.5">Creates a subscription snapshot in <code>pending_activation</code>. You still confirm offline payment &amp; explicitly activate afterwards.</p>
                </div>

                @if($plans->isEmpty())
                    <div class="p-3 rounded-xl bg-amber-50 border border-amber-200 text-xs text-amber-900">
                        No active subscription plans exist yet.
                        <a href="{{ route('admin.vendor_management.plans') }}" class="underline font-bold">Create a Plan first →</a>
                    </div>
                @else
                    <form method="POST" action="{{ route('admin.vendor_management.assign_plan', $vendor) }}" class="space-y-3">
                        @csrf
                        <div>
                            <label class="block text-[11px] font-bold text-zinc-700 mb-1">Sector</label>
                            <select name="sector_key" class="w-full rounded-xl border border-zinc-200 px-3 py-2 text-xs font-semibold">
                                @foreach($allSectors as $sec)
                                    <option value="{{ $sec->key }}" @selected($vendorPrimarySector === $sec->key)>{{ $sec->name }}</option>
                                @endforeach
                                <option value="all">Cross-Sector (All K71 Sectors)</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-[11px] font-bold text-zinc-700 mb-1">Subscription Plan</label>
                            <select name="plan_id" required class="w-full rounded-xl border border-zinc-200 px-3 py-2 text-xs font-semibold">
                                @foreach($plans as $p)
                                    <option value="{{ $p->id }}">
                                        [{{ strtoupper($p->sector_key) }}@if($p->tier_key) • {{ strtoupper($p->tier_key) }}@endif] {{ $p->name }} (v{{ $p->current_version }}) — {{ $p->is_free ? 'FREE' : '₹' . number_format($p->price_amount / 100, 0) }} / {{ $p->duration_value && $p->duration_unit ? $p->duration_value . ' ' . $p->duration_unit : $p->duration_days . 'd' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <label class="block text-[11px] font-bold text-zinc-700 mb-1">Custom Duration (days)</label>
                                <input type="number" name="duration_days" min="1" placeholder="Plan default" class="w-full rounded-xl border border-zinc-200 px-3 py-1.5 text-xs">
                            </div>
                            <div>
                                <label class="block text-[11px] font-bold text-zinc-700 mb-1">Grace Period (days)</label>
                                <input type="number" name="grace_days" min="0" placeholder="Policy default" class="w-full rounded-xl border border-zinc-200 px-3 py-1.5 text-xs">
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <label class="block text-[11px] font-bold text-zinc-700 mb-1">Commission Override (%)</label>
                                <input type="number" step="0.01" min="0" max="100" name="commission_rate_override" placeholder="Leave blank for rule" class="w-full rounded-xl border border-zinc-200 px-3 py-1.5 text-xs">
                            </div>
                            <div>
                                <label class="block text-[11px] font-bold text-zinc-700 mb-1">Fixed Fee Override (₹)</label>
                                <input type="number" step="0.01" min="0" name="commission_fixed_override_rupees" placeholder="Optional" class="w-full rounded-xl border border-zinc-200 px-3 py-1.5 text-xs">
                            </div>
                        </div>

                        <div>
                            <label class="block text-[11px] font-bold text-zinc-700 mb-1">Assigned Reason / Admin Notes (Private)</label>
                            <textarea name="internal_notes" rows="2" class="w-full rounded-xl border border-zinc-200 px-3 py-2 text-xs" placeholder="WhatsApp deal terms, special discount reason..."></textarea>
                        </div>

                        <button type="submit" class="w-full py-2.5 rounded-xl bg-zinc-950 text-white text-xs font-bold hover:bg-zinc-800 transition">
                            Assign Plan as Pending Activation
                        </button>
                    </form>
                @endif
            </div>
        </div>

        <!-- RIGHT 2 COLUMNS: Subscriptions Workflow, Sector Usage & Audit History -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Sector Status & Live Commission + Usage Summary -->
            <div class="bg-white border border-zinc-200/80 rounded-2xl p-5 space-y-3">
                <h3 class="text-sm font-black text-zinc-950">Sector Entitlements &amp; Active Commission Rules</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    @foreach($sectorSummaries as $secKey => $summary)
                        @php $sub = $summary['effective_sub']; @endphp
                        <div class="border border-zinc-200 rounded-xl p-3.5 {{ $sub ? 'bg-emerald-50/40 border-emerald-200' : 'bg-zinc-50/60' }}">
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-black uppercase text-zinc-900">{{ $summary['sector']->name }}</span>
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ $sub ? 'bg-emerald-100 text-emerald-800' : 'bg-zinc-200 text-zinc-600' }}">
                                    {{ $sub ? strtoupper($sub->status) : 'INACTIVE' }}
                                </span>
                            </div>
                            <p class="text-[11px] text-zinc-600 mt-1">
                                Plan: <strong>{{ $sub ? ($sub->plan_name_snapshot . ' (v' . $sub->plan_version_number . ')') : 'None active' }}</strong>
                            </p>
                            <p class="text-[11px] text-zinc-600">
                                Commission: <strong>{{ $summary['commission']['rate_percent'] }}%</strong>
                                @if(($summary['commission']['fixed_fee_paise'] ?? 0) > 0)
                                    + ₹{{ number_format($summary['commission']['fixed_fee_paise'] / 100, 2) }}
                                @endif
                                <span class="text-zinc-400">({{ $summary['commission']['source'] }})</span>
                            </p>
                            @if($sub && $sub->ends_at)
                                <p class="text-[10px] font-mono text-zinc-500 mt-1">Valid until: {{ $sub->ends_at->format('d M Y') }} ({{ $sub->days_remaining }}d left)</p>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Subscriptions List & Manual Actions -->
            <div class="space-y-4">
                <h3 class="text-sm font-black text-zinc-950 px-1">Assigned Subscriptions &amp; Offline Payment / Activation Controls</h3>

                @forelse($vendor->subscriptions as $sub)
                    <div class="bg-white border border-zinc-200/80 rounded-2xl p-5 space-y-4">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 pb-3 border-b border-zinc-100">
                            <div>
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="text-xs font-black text-zinc-950">#{{ $sub->id }} • {{ $sub->plan_name_snapshot ?: ($sub->plan?->name ?? 'Plan') }}</span>
                                    <span class="px-2 py-0.5 rounded bg-zinc-100 text-[10px] font-mono uppercase">v{{ $sub->plan_version_number }} • {{ $sub->sector_key }}</span>
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase {{ $sub->status === 'active' ? 'bg-emerald-100 text-emerald-900' : ($sub->status === 'pending_activation' ? 'bg-blue-100 text-blue-900' : 'bg-rose-100 text-rose-900') }}">
                                        {{ str_replace('_', ' ', $sub->status) }}
                                    </span>
                                    @if($sub->plan && $sub->plan->current_version > $sub->plan_version_number && in_array($sub->status, ['active', 'pending_activation']))
                                        <form method="POST" action="{{ route('admin.vendor_management.subscriptions.apply_version', $sub) }}" class="inline">
                                            @csrf
                                            <button type="submit" class="px-2 py-0.5 rounded bg-indigo-100 text-indigo-800 text-[10px] font-bold hover:bg-indigo-200">
                                                Upgrade Snapshot to v{{ $sub->plan->current_version }}
                                            </button>
                                        </form>
                                    @endif
                                </div>
                                <p class="text-[11px] text-zinc-500 mt-1">
                                    Fee: {{ $sub->is_free_snapshot ? 'FREE' : '₹' . number_format($sub->price_amount_snapshot / 100, 2) }}
                                    • Duration: {{ $sub->duration_days }} days
                                    @if($sub->starts_at) • Start: {{ $sub->starts_at->format('d M Y') }} @endif
                                    @if($sub->ends_at) • End: {{ $sub->ends_at->format('d M Y') }} @endif
                                </p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <!-- Record Offline Payment Form -->
                            <div class="border border-zinc-200 rounded-xl p-3.5 bg-zinc-50/40 space-y-2.5">
                                <p class="text-[11px] font-black text-zinc-900 uppercase">Step A: External Payment Confirmation (WhatsApp/UPI/Bank)</p>
                                <form method="POST" action="{{ route('admin.vendor_management.subscriptions.payment', $sub) }}" enctype="multipart/form-data" class="space-y-2">
                                    @csrf
                                    <div class="flex items-center gap-3 text-xs">
                                        <label class="inline-flex items-center gap-1.5 font-semibold">
                                            <input type="radio" name="external_payment_received" value="1" @checked($sub->external_payment_received)> Received
                                        </label>
                                        <label class="inline-flex items-center gap-1.5 font-semibold">
                                            <input type="radio" name="external_payment_received" value="0" @checked(!$sub->external_payment_received)> Pending
                                        </label>
                                    </div>
                                    <div class="grid grid-cols-2 gap-2">
                                        <input type="number" step="0.01" name="external_payment_amount_rupees" value="{{ $sub->external_payment_amount !== null ? $sub->external_payment_amount / 100 : ($sub->price_amount_snapshot / 100) }}" placeholder="Amount (₹)" class="rounded-lg border border-zinc-200 px-2.5 py-1.5 text-xs bg-white">
                                        <input type="text" name="external_payment_method" value="{{ $sub->external_payment_method }}" placeholder="Method (UPI/NEFT/Cash)" class="rounded-lg border border-zinc-200 px-2.5 py-1.5 text-xs bg-white">
                                    </div>
                                    <div class="grid grid-cols-2 gap-2">
                                        <input type="text" name="external_payment_ref" value="{{ $sub->external_payment_ref }}" placeholder="UTR / Ref Number" class="rounded-lg border border-zinc-200 px-2.5 py-1.5 text-xs bg-white">
                                        <input type="date" name="external_payment_date" value="{{ $sub->external_payment_date?->format('Y-m-d') ?? now()->format('Y-m-d') }}" class="rounded-lg border border-zinc-200 px-2.5 py-1.5 text-xs bg-white">
                                    </div>
                                    <input type="file" name="external_receipt" class="block w-full text-[11px] text-zinc-500">
                                    <button type="submit" class="w-full py-1.5 rounded-lg border border-zinc-900 text-zinc-950 text-xs font-bold hover:bg-zinc-900 hover:text-white transition">
                                        Save Payment Record (No Auto-Activate)
                                    </button>
                                </form>
                            </div>

                            <!-- Explicit Manual Activation OR Lifecycle Actions -->
                            <div class="border border-zinc-200 rounded-xl p-3.5 bg-zinc-50/40 space-y-3">
                                @if($sub->status === 'pending_activation')
                                    <p class="text-[11px] font-black text-emerald-900 uppercase">Step B: Explicit Admin Activation Confirmation</p>
                                    @if(!$vendor->isVerifiedMerchant())
                                        <p class="text-[11px] text-amber-800 bg-amber-50 border border-amber-200 rounded-lg p-2">
                                            Vendor verification status must be <strong>Verified</strong> before activation can be confirmed.
                                        </p>
                                    @else
                                        <form method="POST" action="{{ route('admin.vendor_management.subscriptions.activate', $sub) }}" class="space-y-2">
                                            @csrf
                                            <div class="grid grid-cols-2 gap-2">
                                                <input type="date" name="starts_at" value="{{ $sub->starts_at?->format('Y-m-d') ?? now()->format('Y-m-d') }}" class="rounded-lg border border-zinc-200 px-2.5 py-1.5 text-xs bg-white">
                                                <input type="number" name="duration_days" value="{{ $sub->duration_days }}" min="1" class="rounded-lg border border-zinc-200 px-2.5 py-1.5 text-xs bg-white" placeholder="Days">
                                            </div>
                                            <input type="text" name="reason" placeholder="Activation note / confirmation" class="w-full rounded-lg border border-zinc-200 px-2.5 py-1.5 text-xs bg-white">
                                            <label class="flex items-start gap-2 text-[11px] text-zinc-700 font-semibold">
                                                <input type="checkbox" name="confirm_activation" value="1" required class="mt-0.5">
                                                <span>I confirm plan entitlements, dates, and external arrangement and want to ACTIVATE this subscription now.</span>
                                            </label>
                                            <button type="submit" class="w-full py-2 rounded-lg bg-emerald-600 text-white text-xs font-bold hover:bg-emerald-700 transition">
                                                Confirm &amp; Activate Subscription
                                            </button>
                                        </form>
                                    @endif
                                @else
                                    <p class="text-[11px] font-black text-zinc-900 uppercase">Manual Renew / Suspend / Cancel</p>
                                    <form method="POST" action="{{ route('admin.vendor_management.subscriptions.renew', $sub) }}" class="space-y-2">
                                        @csrf
                                        <div class="grid grid-cols-2 gap-2">
                                            <input type="number" name="duration_days" value="{{ $sub->duration_days }}" min="1" class="rounded-lg border border-zinc-200 px-2.5 py-1.5 text-xs bg-white" placeholder="Extend days">
                                            <input type="text" name="external_payment_ref" placeholder="Renewal Ref (optional)" class="rounded-lg border border-zinc-200 px-2.5 py-1.5 text-xs bg-white">
                                        </div>
                                        <input type="text" name="reason" required placeholder="Reason for renewal (required)" class="w-full rounded-lg border border-zinc-200 px-2.5 py-1.5 text-xs bg-white">
                                        <button type="submit" class="w-full py-1.5 rounded-lg bg-zinc-950 text-white text-xs font-bold hover:bg-zinc-800">
                                            Manually Renew / Extend
                                        </button>
                                    </form>

                                    @if(in_array($sub->status, ['active', 'grace_period']))
                                        <div class="grid grid-cols-2 gap-2 pt-1 border-t border-zinc-200">
                                            <form method="POST" action="{{ route('admin.vendor_management.subscriptions.suspend', $sub) }}" class="space-y-1">
                                                @csrf
                                                <input type="hidden" name="reason" value="Suspended manually by administrator">
                                                <button type="submit" class="w-full py-1.5 rounded-lg bg-amber-100 text-amber-900 text-[11px] font-bold hover:bg-amber-200">Suspend</button>
                                            </form>
                                            <form method="POST" action="{{ route('admin.vendor_management.subscriptions.cancel', $sub) }}" class="space-y-1">
                                                @csrf
                                                <input type="hidden" name="reason" value="Cancelled manually by administrator">
                                                <button type="submit" class="w-full py-1.5 rounded-lg bg-rose-100 text-rose-900 text-[11px] font-bold hover:bg-rose-200">Cancel</button>
                                            </form>
                                        </div>
                                    @endif
                                @endif
                            </div>
                        </div>

                        <!-- Copyable WhatsApp Reminder Message Draft -->
                        @if(isset($whatsappDrafts[$sub->id]))
                            <div class="rounded-xl bg-emerald-50/70 border border-emerald-200 p-3 flex flex-col sm:flex-row sm:items-center justify-between gap-2" x-data="{ copied: false }">
                                <div class="text-[11px] text-emerald-950">
                                    <span class="font-bold uppercase text-[10px] text-emerald-800 block">WhatsApp Reminder Draft</span>
                                    {{ $whatsappDrafts[$sub->id] }}
                                </div>
                                <div class="flex shrink-0 gap-2">
                                    <button type="button"
                                            @click="navigator.clipboard?.writeText(@js($whatsappDrafts[$sub->id])); copied = true; setTimeout(() => copied = false, 2000)"
                                            class="px-2.5 py-1.5 rounded-lg bg-white border border-emerald-300 text-[11px] font-bold text-emerald-900">
                                        <span x-text="copied ? 'Copied!' : 'Copy Draft'"></span>
                                    </button>
                                    @if($waLink)
                                        <a href="{{ $waLink }}?text={{ urlencode($whatsappDrafts[$sub->id]) }}" target="_blank"
                                           class="px-2.5 py-1.5 rounded-lg bg-emerald-600 text-white text-[11px] font-bold hover:bg-emerald-700">
                                            Open in WhatsApp ↗
                                        </a>
                                    @endif
                                </div>
                            </div>
                        @endif
                    </div>
                @empty
                    <div class="bg-white border border-zinc-200/80 rounded-2xl p-6 text-center text-xs text-zinc-500">
                        No subscriptions have been assigned to this vendor yet. Use "2. Assign Sector Plan" on the left to start.
                    </div>
                @endforelse
            </div>

            <!-- Vendor Audit Logs -->
            <div class="bg-white border border-zinc-200/80 rounded-2xl p-5 space-y-3">
                <h3 class="text-sm font-black text-zinc-950">Vendor Verification &amp; Subscription Audit Trail</h3>
                <div class="divide-y divide-zinc-100 text-xs">
                    @forelse($auditLogs as $log)
                        <div class="py-2 flex items-start justify-between gap-3">
                            <div>
                                <span class="font-mono font-bold text-zinc-900">{{ $log->action }}</span>
                                @if($log->reason)
                                    <span class="text-zinc-600">— {{ $log->reason }}</span>
                                @endif
                                <span class="block text-[10px] text-zinc-400">By {{ $log->user?->name ?? 'System' }} • {{ $log->ip_address ?: 'CLI/Cron' }}</span>
                            </div>
                            <span class="text-[10px] font-mono text-zinc-400 shrink-0">{{ $log->created_at?->format('d M Y H:i') }}</span>
                        </div>
                    @empty
                        <p class="py-4 text-center text-zinc-400">No audit actions logged for this vendor yet.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</x-layouts.admin>
