@if(session('success'))
    <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-xs font-semibold text-emerald-900 flex items-center justify-between">
        <span>{{ session('success') }}</span>
    </div>
@endif

@if(session('error'))
    <div class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-xs font-semibold text-rose-900">
        {{ session('error') }}
    </div>
@endif

@if($errors->any())
    <div class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-xs text-rose-900">
        <ul class="list-disc pl-4 space-y-0.5">
            @foreach($errors->all() as $err)
                <li>{{ $err }}</li>
            @endforeach
        </ul>
    </div>
@endif

@php
    $pendingRenewalsBadge = \App\Models\SubscriptionRenewalRequest::where('status', 'pending')->count();
@endphp

<div class="bg-white border border-zinc-200/80 rounded-2xl p-2 flex flex-wrap items-center justify-between gap-2 shadow-2xs">
    <div class="flex flex-wrap items-center gap-1.5">
        <a href="{{ route('admin.vendor_management.overview') }}"
           class="px-3.5 py-2 rounded-xl text-xs font-bold transition {{ request()->routeIs('admin.vendor_management.overview') || request()->routeIs('admin.vendor_management.automation*') || request()->routeIs('admin.vendor_management.reports*') ? 'bg-zinc-950 text-white' : 'text-zinc-600 hover:bg-zinc-100' }}">
            Overview
        </a>
        <a href="{{ route('admin.vendor_management.plans') }}"
           class="px-3.5 py-2 rounded-xl text-xs font-bold transition {{ request()->routeIs('admin.vendor_management.plans*') || request()->routeIs('admin.vendor_management.commissions*') ? 'bg-zinc-950 text-white' : 'text-zinc-600 hover:bg-zinc-100' }}">
            Plan Builder
        </a>
        <a href="{{ route('admin.vendor_management.index') }}"
           class="px-3.5 py-2 rounded-xl text-xs font-bold transition {{ request()->routeIs('admin.vendor_management.index') || request()->routeIs('admin.vendor_management.show') || request()->routeIs('admin.vendor_management.leads*') ? 'bg-zinc-950 text-white' : 'text-zinc-600 hover:bg-zinc-100' }}">
            Vendor Subscriptions
        </a>
        <a href="{{ route('admin.vendor_management.history') }}"
           class="px-3.5 py-2 rounded-xl text-xs font-bold transition inline-flex items-center gap-1.5 {{ request()->routeIs('admin.vendor_management.history*') || request()->routeIs('admin.vendor_management.renewal_requests*') ? 'bg-zinc-950 text-white' : 'text-zinc-600 hover:bg-zinc-100' }}">
            <span>Renewal Requests</span>
            @if($pendingRenewalsBadge > 0)
                <span class="px-1.5 py-0.5 rounded-full text-[10px] font-black {{ request()->routeIs('admin.vendor_management.history*') ? 'bg-white text-zinc-950' : 'bg-zinc-950 text-white' }}">
                    {{ $pendingRenewalsBadge }}
                </span>
            @endif
        </a>
    </div>

    <div class="flex flex-wrap items-center gap-1.5">
        <a href="{{ route('admin.vendor_management.commissions') }}"
           class="px-2.5 py-1.5 rounded-lg border border-zinc-200 text-[11px] font-semibold text-zinc-700 hover:bg-zinc-50 transition">
            Commissions &amp; Simulator
        </a>
        <a href="{{ route('admin.vendor_management.automation') }}"
           class="px-2.5 py-1.5 rounded-lg border border-zinc-200 text-[11px] font-semibold text-zinc-700 hover:bg-zinc-50 transition">
            Expiry Policy
        </a>
        <a href="{{ route('admin.vendor_management.reports') }}"
           class="px-2.5 py-1.5 rounded-lg border border-zinc-200 text-[11px] font-semibold text-zinc-700 hover:bg-zinc-50 transition">
            Reports
        </a>
    </div>
</div>
