<x-layouts.vendor title="Services Studio">
    <div class="max-w-7xl mx-auto space-y-6 pb-12">

        <div class="bg-white rounded-2xl border border-zinc-200 p-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <div class="text-[11px] font-mono uppercase tracking-wider text-zinc-500 font-bold">Layer C · Provider Operations</div>
                <h1 class="text-2xl font-black text-zinc-950 tracking-tight mt-0.5">Services Studio</h1>
                <p class="text-xs text-zinc-500 mt-1">Single source of truth — every active service stays synced across K71 Services Marketplace and your personal website.</p>
            </div>

            <div class="flex flex-wrap items-center gap-2.5">
                <a href="{{ route('vendor.service_orders.index') }}"
                   class="px-4 py-2.5 rounded-xl bg-white border border-zinc-300 text-zinc-800 hover:bg-zinc-50 text-xs font-bold transition">
                    Client Orders
                </a>
                <a href="{{ route('vendor.services.create') }}"
                   class="px-4 py-2.5 rounded-xl bg-black hover:bg-zinc-800 text-white text-xs font-bold transition">
                    + New Service
                </a>
            </div>
        </div>

        @if($services->isEmpty())
            <div class="p-12 text-center rounded-2xl bg-white border border-zinc-200 space-y-4">
                <div class="w-14 h-14 rounded-2xl bg-zinc-100 text-zinc-700 flex items-center justify-center text-2xl mx-auto">⚡</div>
                <h3 class="text-base font-bold text-zinc-900">No Services Published Yet</h3>
                <p class="text-xs text-zinc-500 max-w-md mx-auto">Create your first 3-tier service offering to accept Fiverr-style instant package bookings and showcase it on your personal website.</p>
                <a href="{{ route('vendor.services.create') }}" class="inline-block px-5 py-2.5 rounded-xl bg-black text-white text-xs font-bold hover:bg-zinc-800 transition">
                    Create Your First Service
                </a>
            </div>
        @else
            <div class="rounded-2xl bg-white border border-zinc-200 overflow-hidden shadow-xs">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-zinc-50 border-b border-zinc-200 text-[10px] font-mono font-bold uppercase tracking-wider text-zinc-500">
                            <tr>
                                <th class="py-3.5 px-5">Service</th>
                                <th class="py-3.5 px-4">Category</th>
                                <th class="py-3.5 px-4">Starting</th>
                                <th class="py-3.5 px-4">Website Visibility</th>
                                <th class="py-3.5 px-4">Status</th>
                                <th class="py-3.5 px-4">Orders</th>
                                <th class="py-3.5 px-5 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-100">
                            @foreach($services as $svc)
                                @php
                                    $img = $svc->primary_image_url;
                                    $basic = $svc->gigPackages->firstWhere('tier', 'basic') ?? $svc->gigPackages->first();
                                    $starting = $basic ? (float) $basic->price : ((float) $svc->base_price / 100);
                                @endphp
                                <tr class="hover:bg-zinc-50/70 transition">
                                    <td class="py-4 px-5">
                                        <div class="flex items-center gap-3">
                                            <img src="{{ $img }}" alt="{{ $svc->title }}" class="w-12 h-12 rounded-xl object-cover border border-zinc-200 shrink-0">
                                            <div class="min-w-0 max-w-xs">
                                                <a href="{{ route('vendor.services.edit', $svc) }}" class="font-bold text-zinc-950 hover:underline truncate block">
                                                    {{ $svc->title }}
                                                </a>
                                                <div class="text-[11px] text-zinc-400 font-mono mt-0.5">
                                                    {{ $svc->gigPackages->count() }} package{{ $svc->gigPackages->count() === 1 ? '' : 's' }}
                                                    @if($svc->is_website_featured) · Featured @endif
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="py-4 px-4 text-zinc-600">{{ $svc->category?->name ?? 'Service' }}</td>
                                    <td class="py-4 px-4 font-mono font-black text-black">₹{{ number_format($starting) }}</td>
                                    <td class="py-4 px-4">
                                        <form action="{{ route('vendor.services.toggle_website', $svc) }}" method="POST">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="px-2.5 py-1 rounded-full text-[10px] font-bold border cursor-pointer transition {{ ($svc->show_on_website ?? true) ? 'bg-emerald-50 text-emerald-800 border-emerald-200 hover:bg-emerald-100' : 'bg-zinc-100 text-zinc-600 border-zinc-300 hover:bg-zinc-200' }}">
                                                {{ ($svc->show_on_website ?? true) ? 'Shown on Site' : 'Hidden on Site' }}
                                            </button>
                                        </form>
                                    </td>
                                    <td class="py-4 px-4">
                                        <form action="{{ route('vendor.services.toggle_status', $svc) }}" method="POST">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase border cursor-pointer transition {{ $svc->status === 'active' ? 'bg-black text-white border-black hover:bg-zinc-800' : 'bg-amber-50 text-amber-800 border-amber-200 hover:bg-amber-100' }}">
                                                {{ $svc->status }}
                                            </button>
                                        </form>
                                    </td>
                                    <td class="py-4 px-4 font-mono font-bold text-zinc-800">{{ $svc->service_orders_count ?? 0 }}</td>
                                    <td class="py-4 px-5 text-right">
                                        <div class="flex flex-wrap items-center justify-end gap-1.5">
                                            <a href="{{ route('vendor.services.edit', $svc) }}" class="px-2.5 py-1.5 rounded-lg border border-zinc-300 text-zinc-800 hover:bg-zinc-100 font-bold text-[11px]">
                                                Edit
                                            </a>
                                            <form action="{{ route('vendor.services.duplicate', $svc) }}" method="POST">
                                                @csrf
                                                <button type="submit" class="px-2.5 py-1.5 rounded-lg border border-zinc-300 text-zinc-700 hover:bg-zinc-100 font-semibold text-[11px] cursor-pointer">
                                                    Duplicate
                                                </button>
                                            </form>
                                            <a href="{{ route('services.show', $svc->slug) }}" target="_blank" class="px-2.5 py-1.5 rounded-lg border border-zinc-300 text-zinc-700 hover:bg-zinc-100 font-semibold text-[11px]">
                                                View
                                            </a>
                                            <form action="{{ route('vendor.services.destroy', $svc) }}" method="POST"
                                                  onsubmit="return confirm('Delete this service listing permanently?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="px-2.5 py-1.5 rounded-lg bg-rose-50 text-rose-700 hover:bg-rose-100 font-bold text-[11px] cursor-pointer">
                                                    Delete
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <div>{{ $services->links() }}</div>
        @endif

    </div>
</x-layouts.vendor>
