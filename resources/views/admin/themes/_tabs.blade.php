<div class="flex flex-wrap items-center justify-between gap-4 pb-5 border-b border-zinc-200">
    <div>
        <h1 class="text-2xl font-extrabold text-zinc-950 tracking-tight">Storefront Theme Management Center</h1>
        <p class="text-xs text-zinc-500 mt-0.5">Manage all 61 themes (11 Classic + 50 Studio Editions), individual prices, WhatsApp payment verification, vendor entitlements, and audit history.</p>
    </div>
    <nav class="inline-flex flex-wrap gap-1.5 bg-zinc-100 p-1 rounded-xl" aria-label="Theme administration tabs">
        <a href="{{ route('admin.themes.index') }}"
           class="px-3.5 py-2 rounded-lg text-xs font-bold transition {{ request()->routeIs('admin.themes.index') ? 'bg-zinc-950 text-white shadow-xs' : 'text-zinc-600 hover:text-zinc-950' }}">
            Catalogue &amp; Pricing (61)
        </a>
        <a href="{{ route('admin.themes.requests') }}"
           class="px-3.5 py-2 rounded-lg text-xs font-bold transition {{ request()->routeIs('admin.themes.requests') ? 'bg-zinc-950 text-white shadow-xs' : 'text-zinc-600 hover:text-zinc-950' }}">
            Requests &amp; Payments
        </a>
        <a href="{{ route('admin.themes.entitlements') }}"
           class="px-3.5 py-2 rounded-lg text-xs font-bold transition {{ request()->routeIs('admin.themes.entitlements') ? 'bg-zinc-950 text-white shadow-xs' : 'text-zinc-600 hover:text-zinc-950' }}">
            Entitlements &amp; Live/Draft
        </a>
        <a href="{{ route('admin.themes.audit') }}"
           class="px-3.5 py-2 rounded-lg text-xs font-bold transition {{ request()->routeIs('admin.themes.audit') ? 'bg-zinc-950 text-white shadow-xs' : 'text-zinc-600 hover:text-zinc-950' }}">
            Audit History
        </a>
    </nav>
</div>

@if(session('success'))
    <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-900 text-xs font-semibold">{{ session('success') }}</div>
@endif
@if(session('error'))
    <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-xs font-semibold">{{ session('error') }}</div>
@endif
