<x-layouts.vendor :title="$featureTitle ?? 'Feature Locked'">
    <div class="max-w-3xl mx-auto py-10 px-4">
        <div class="rounded-2xl bg-zinc-950 text-white border border-zinc-800 p-8 space-y-5 shadow-sm">
            <div class="flex flex-wrap items-center gap-2">
                <span class="px-3 py-1 rounded-full bg-white text-zinc-950 text-[10px] font-black uppercase tracking-widest">Plan Entitlement Gate</span>
                <span class="px-2.5 py-1 rounded-full bg-zinc-900 border border-zinc-700 text-zinc-300 text-[10px] font-mono uppercase">Sector: {{ ucfirst($sectorKey ?? 'products') }}</span>
            </div>
            <h1 class="text-2xl font-black tracking-tight">{{ $featureTitle ?? 'Feature Not Included in Current Plan' }}</h1>
            <p class="text-sm text-zinc-300 leading-relaxed">{{ $reason }}</p>
            <div class="pt-2 flex flex-wrap gap-3">
                <a href="{{ route('vendor.subscription.index') }}" class="px-5 py-2.5 rounded-xl bg-white text-zinc-950 text-xs font-black hover:bg-zinc-200 transition">
                    Request Plan Upgrade via Admin
                </a>
                <a href="{{ route('vendor.dashboard') }}" class="px-5 py-2.5 rounded-xl border border-zinc-700 text-zinc-200 text-xs font-bold hover:bg-zinc-900 transition">
                    ← Back to Dashboard
                </a>
            </div>
        </div>
    </div>
</x-layouts.vendor>

