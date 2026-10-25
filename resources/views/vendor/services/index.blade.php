<x-layouts.public 
    title="My Services Studio — Freelancer Dashboard | K71"
    metaDescription="Manage your published freelance services, pricing packages, and orders."
>
    <div class="min-h-screen bg-gradient-to-b from-[#faf5ff] via-white to-[#faf5ff] text-zinc-900 pb-20 pt-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            <!-- HEADER -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-6 border-b border-purple-100">
                <div>
                    <div class="text-xs uppercase font-bold tracking-wider text-purple-700">Vendor Freelance Studio</div>
                    <h1 class="text-2xl sm:text-3xl font-black text-zinc-950 tracking-tight">My Services &amp; Gigs</h1>
                    <p class="text-xs text-zinc-500 mt-1">Manage active listings, 3-tier packages, and monitor client orders.</p>
                </div>

                <div class="flex items-center gap-3">
                    <a href="{{ route('vendor.service_orders.index') }}" 
                       class="px-4 py-2.5 rounded-2xl bg-white border border-purple-200 text-purple-800 hover:bg-purple-50 text-xs font-bold transition">
                        View Client Orders
                    </a>
                    <a href="{{ route('vendor.services.create') }}" 
                       class="px-5 py-2.5 rounded-2xl bg-purple-600 hover:bg-purple-700 text-white text-xs font-bold uppercase tracking-wider transition shadow-md shadow-purple-500/25 active:scale-95 flex items-center gap-1.5">
                        <span>+ Create New Service</span>
                    </a>
                </div>
            </div>

            @if(session('success'))
                <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-semibold flex items-center gap-2">
                    <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            <!-- SERVICES TABLE / CARDS -->
            @if($services->isEmpty())
                <div class="p-12 text-center rounded-3xl bg-white/80 backdrop-blur-md border border-purple-100 space-y-4">
                    <div class="w-16 h-16 rounded-full bg-purple-100 text-purple-600 flex items-center justify-center text-3xl mx-auto">
                        ✨
                    </div>
                    <h3 class="text-base font-bold text-zinc-900">No Services Published Yet</h3>
                    <p class="text-xs text-zinc-500 max-w-sm mx-auto">Create your first 3-tier freelance service gig and start receiving orders from clients across India.</p>
                    <a href="{{ route('vendor.services.create') }}" class="inline-block px-6 py-2.5 rounded-2xl bg-purple-600 text-white text-xs font-bold uppercase tracking-wider shadow-sm hover:bg-purple-700 transition">
                        Create Your First Service
                    </a>
                </div>
            @else
                <div class="rounded-3xl bg-white/90 backdrop-blur-md border border-purple-100 shadow-sm overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs">
                            <thead class="bg-purple-50/70 border-b border-purple-100 text-[11px] font-black uppercase tracking-wider text-purple-900">
                                <tr>
                                    <th class="p-4 sm:px-6">Service</th>
                                    <th class="p-4">Category</th>
                                    <th class="p-4">Starting Price</th>
                                    <th class="p-4">Packages</th>
                                    <th class="p-4">Orders</th>
                                    <th class="p-4">Status</th>
                                    <th class="p-4 sm:px-6 text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-purple-50 font-medium">
                                @foreach($services as $svc)
                                    @php
                                        $img = $svc->primary_image_url;
                                        $basic = $svc->gigPackages->firstWhere('tier', 'basic') ?? $svc->gigPackages->first();
                                    @endphp
                                    <tr class="hover:bg-purple-50/30 transition">
                                        <td class="p-4 sm:px-6">
                                            <div class="flex items-center gap-3">
                                                <img src="{{ $img }}" alt="{{ $svc->title }}" class="w-12 h-12 rounded-xl object-cover border border-purple-100 shrink-0">
                                                <div class="min-w-0">
                                                    <a href="{{ route('services.show', $svc->slug) }}" target="_blank" class="font-bold text-zinc-900 hover:text-purple-700 truncate block">
                                                        {{ $svc->title }}
                                                    </a>
                                                    <span class="text-[10px] text-zinc-400">Created {{ $svc->created_at->format('M d, Y') }}</span>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="p-4 text-zinc-600">
                                            {{ $svc->category?->name ?? 'Service' }}
                                        </td>
                                        <td class="p-4 font-black text-purple-700 text-sm">
                                            ₹{{ number_format($basic ? $basic->price : ($svc->base_price / 100)) }}
                                        </td>
                                        <td class="p-4">
                                            <span class="px-2 py-0.5 rounded-md bg-purple-100 text-purple-800 text-[10px] font-bold">
                                                {{ $svc->gigPackages->count() }} Tiers
                                            </span>
                                        </td>
                                        <td class="p-4 font-bold text-zinc-800">
                                            {{ $svc->service_orders_count ?? 0 }}
                                        </td>
                                        <td class="p-4">
                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider {{ $svc->status === 'active' ? 'bg-emerald-100 text-emerald-800' : 'bg-zinc-100 text-zinc-600' }}">
                                                {{ $svc->status }}
                                            </span>
                                        </td>
                                        <td class="p-4 sm:px-6 text-right">
                                            <div class="flex items-center justify-end gap-2">
                                                <a href="{{ route('services.show', $svc->slug) }}" target="_blank" 
                                                   class="px-2.5 py-1 rounded-lg bg-purple-50 hover:bg-purple-100 text-purple-700 text-[11px] font-bold transition">
                                                    Preview
                                                </a>
                                                <form action="{{ route('vendor.services.destroy', $svc) }}" method="POST" 
                                                      onsubmit="return confirm('Are you sure you want to delete this service gig? This cannot be undone.')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="px-2.5 py-1 rounded-lg bg-rose-50 hover:bg-rose-100 text-rose-700 text-[11px] font-bold transition">
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

                <div class="mt-6">
                    {{ $services->links() }}
                </div>
            @endif

        </div>
    </div>
</x-layouts.public>
