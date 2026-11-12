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

<div class="bg-white border border-zinc-200/80 rounded-2xl p-2 flex flex-wrap items-center gap-1.5 shadow-2xs">
    <a href="{{ route('admin.vendor_management.index') }}"
       class="px-3 py-1.5 rounded-xl text-xs font-semibold transition {{ request()->routeIs('admin.vendor_management.index') || request()->routeIs('admin.vendor_management.show') ? 'bg-zinc-950 text-white' : 'text-zinc-600 hover:bg-zinc-100' }}">
        All Vendors &amp; Queues
    </a>
    <a href="{{ route('admin.vendor_management.leads') }}"
       class="px-3 py-1.5 rounded-xl text-xs font-semibold transition {{ request()->routeIs('admin.vendor_management.leads*') ? 'bg-zinc-950 text-white' : 'text-zinc-600 hover:bg-zinc-100' }}">
        WhatsApp Leads
    </a>
    <a href="{{ route('admin.vendor_management.plans') }}"
       class="px-3 py-1.5 rounded-xl text-xs font-semibold transition {{ request()->routeIs('admin.vendor_management.plans*') ? 'bg-zinc-950 text-white' : 'text-zinc-600 hover:bg-zinc-100' }}">
        Subscription Plans
    </a>
    <a href="{{ route('admin.vendor_management.commissions') }}"
       class="px-3 py-1.5 rounded-xl text-xs font-semibold transition {{ request()->routeIs('admin.vendor_management.commissions*') ? 'bg-zinc-950 text-white' : 'text-zinc-600 hover:bg-zinc-100' }}">
        Commission Rules &amp; Ledger
    </a>
    <a href="{{ route('admin.vendor_management.history') }}"
       class="px-3 py-1.5 rounded-xl text-xs font-semibold transition {{ request()->routeIs('admin.vendor_management.history*') ? 'bg-zinc-950 text-white' : 'text-zinc-600 hover:bg-zinc-100' }}">
        Payments, Events &amp; Renewals
    </a>
    <a href="{{ route('admin.vendor_management.automation') }}"
       class="px-3 py-1.5 rounded-xl text-xs font-semibold transition {{ request()->routeIs('admin.vendor_management.automation*') ? 'bg-zinc-950 text-white' : 'text-zinc-600 hover:bg-zinc-100' }}">
        Expiry &amp; Reminders
    </a>
    <a href="{{ route('admin.vendor_management.reports') }}"
       class="px-3 py-1.5 rounded-xl text-xs font-semibold transition {{ request()->routeIs('admin.vendor_management.reports*') ? 'bg-zinc-950 text-white' : 'text-zinc-600 hover:bg-zinc-100' }}">
        Reports
    </a>
    <a href="{{ route('admin.audit_logs.index') }}"
       class="px-3 py-1.5 rounded-xl text-xs font-semibold text-zinc-600 hover:bg-zinc-100 transition ml-auto">
        Audit Logs ↗
    </a>
</div>
