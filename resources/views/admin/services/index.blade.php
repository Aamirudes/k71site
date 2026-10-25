<x-layouts.admin title="Manage Marketplace Services & Gigs">
    <div class="space-y-6">
        
        <!-- Header Banner -->
        <div class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-200/80 shadow-xs flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div>
                <div class="flex items-center gap-2">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-purple-50 text-purple-700 border border-purple-200">
                        Freelance &amp; Services
                    </span>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-indigo-50 text-indigo-700 border border-indigo-200">
                        Superpower Moderation
                    </span>
                </div>
                <h2 class="text-2xl font-serif font-bold text-slate-900 mt-2">Services &amp; Gigs Moderation Center</h2>
                <p class="text-xs sm:text-sm text-slate-500 mt-1">Review, approve, pause, or remove freelance gigs and on-demand professional services across K71.</p>
            </div>
            <div class="flex items-center gap-3">
                <span class="px-3.5 py-2 rounded-2xl bg-purple-900 text-xs font-mono font-bold text-white shadow-xs">
                    {{ $services->total() ?? $services->count() }} Services Listed
                </span>
            </div>
        </div>

        @if(session('success'))
            <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-2xl p-4 text-xs sm:text-sm flex items-center gap-3 shadow-xs">
                <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        @if(session('error'))
            <div class="bg-rose-50 border border-rose-200 text-rose-800 rounded-2xl p-4 text-xs sm:text-sm flex items-center gap-3 shadow-xs">
                <svg class="w-5 h-5 text-rose-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        <!-- Filter & Search Panel -->
        <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-xs space-y-4">
            
            <!-- Filter Tabs -->
            <div class="flex flex-wrap items-center gap-2 border-b border-slate-100 pb-4">
                <a href="{{ route('admin.services.index') }}" class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition-all {{ !request('status') ? 'bg-purple-900 text-white shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                    All Services ({{ $counts['all'] }})
                </a>
                <a href="{{ route('admin.services.index', array_merge(request()->query(), ['status' => 'active'])) }}" class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition-all {{ request('status') === 'active' ? 'bg-emerald-600 text-white shadow-xs' : 'bg-emerald-50 text-emerald-700 hover:bg-emerald-100' }}">
                    Active &amp; Published ({{ $counts['active'] }})
                </a>
                <a href="{{ route('admin.services.index', array_merge(request()->query(), ['status' => 'draft'])) }}" class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition-all {{ request('status') === 'draft' ? 'bg-amber-500 text-slate-950 shadow-xs' : 'bg-amber-50 text-amber-700 hover:bg-amber-100' }}">
                    Draft / Pending Review ({{ $counts['pending'] }})
                </a>
            </div>

            <!-- Search Form -->
            <form action="{{ route('admin.services.index') }}" method="GET" class="flex flex-col sm:flex-row gap-3">
                @if(request('status'))
                    <input type="hidden" name="status" value="{{ request('status') }}">
                @endif
                <div class="relative flex-1">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </div>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search service title, description, or provider name..." class="w-full pl-10 pr-4 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-purple-600 focus:bg-white transition">
                </div>
                <div class="flex items-center gap-2">
                    <select name="category" onchange="this.form.submit()" class="text-xs font-semibold py-2 px-3 rounded-xl border border-slate-200 bg-slate-50 cursor-pointer">
                        <option value="">All Categories</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->slug }}" {{ request('category') === $cat->slug ? 'selected' : '' }}>{{ $cat->name }}</option>
                        @endforeach
                    </select>

                    <button type="submit" class="px-5 py-2 bg-purple-900 text-white text-xs font-bold rounded-xl hover:bg-purple-800 transition shadow-xs">
                        Search
                    </button>
                    @if(request()->hasAny(['search', 'status', 'category']))
                        <a href="{{ route('admin.services.index') }}" class="px-4 py-2 bg-slate-100 text-slate-600 text-xs font-semibold rounded-xl hover:bg-slate-200 transition">
                            Reset
                        </a>
                    @endif
                </div>
            </form>
        </div>

        <!-- Services Table -->
        <div class="bg-white rounded-3xl border border-slate-200/80 shadow-xs overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-slate-100 bg-slate-50/50 text-[11px] font-mono uppercase tracking-wider text-slate-500">
                            <th class="py-3.5 px-6">Service Gig &amp; Cover</th>
                            <th class="py-3.5 px-4">Professional / Provider</th>
                            <th class="py-3.5 px-4">Category</th>
                            <th class="py-3.5 px-4">Packages</th>
                            <th class="py-3.5 px-4">Starting Price</th>
                            <th class="py-3.5 px-4">Status</th>
                            <th class="py-3.5 px-6 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-xs">
                        @forelse($services as $svc)
                            <tr class="hover:bg-slate-50/60 transition group">
                                <td class="py-4 px-6">
                                    <div class="flex items-center gap-3">
                                        <div class="w-14 h-12 rounded-xl overflow-hidden bg-slate-100 shrink-0 border border-slate-200">
                                            <img src="{{ $svc->primary_image_url }}" alt="{{ $svc->title }}" class="w-full h-full object-cover">
                                        </div>
                                        <div>
                                            <div class="font-bold text-slate-900 line-clamp-1 max-w-xs">{{ $svc->title }}</div>
                                            <div class="text-[11px] text-slate-400 font-mono">ID: #{{ $svc->id }} · {{ $svc->slug }}</div>
                                        </div>
                                    </div>
                                </td>

                                <td class="py-4 px-4 whitespace-nowrap">
                                    @if($svc->vendor)
                                        <div class="font-bold text-slate-900">{{ $svc->vendor->shop_name }}</div>
                                        <div class="text-[11px] text-purple-600 font-medium">📍 {{ $svc->vendor->city ?: 'Remote / Online' }}</div>
                                    @else
                                        <span class="text-slate-400 font-mono">Direct K71</span>
                                    @endif
                                </td>

                                <td class="py-4 px-4 whitespace-nowrap text-slate-600 font-medium">
                                    {{ $svc->category->name ?? 'Professional Services' }}
                                </td>

                                <td class="py-4 px-4 whitespace-nowrap">
                                    @if($svc->gigPackages->count() > 0)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-black bg-purple-50 text-purple-700 border border-purple-200">
                                            {{ $svc->gigPackages->count() }} Tiers
                                        </span>
                                    @else
                                        <span class="text-slate-400 text-[11px]">Fixed Price</span>
                                    @endif
                                </td>

                                <td class="py-4 px-4 whitespace-nowrap font-mono font-bold text-slate-900 text-sm">
                                    ₹{{ number_format($svc->base_price / 100, 2) }}
                                </td>

                                <td class="py-4 px-4 whitespace-nowrap">
                                    <form action="{{ route('admin.services.status', $svc) }}" method="POST">
                                        @csrf
                                        @method('PATCH')
                                        <select name="status" onchange="this.form.submit()" class="text-xs font-bold py-1 px-2.5 rounded-lg border border-slate-200 bg-white cursor-pointer focus:ring-1 focus:ring-purple-600 {{ $svc->status === 'active' ? 'text-emerald-700' : 'text-amber-700' }}">
                                            <option value="active" {{ $svc->status === 'active' ? 'selected' : '' }}>● ACTIVE</option>
                                            <option value="draft" {{ $svc->status === 'draft' ? 'selected' : '' }}>● DRAFT</option>
                                            <option value="suspended" {{ $svc->status === 'suspended' ? 'selected' : '' }}>● SUSPENDED</option>
                                        </select>
                                    </form>
                                </td>

                                <td class="py-4 px-6 whitespace-nowrap text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="{{ route('services.show', $svc->slug) }}" target="_blank" class="inline-flex items-center gap-1 text-xs font-bold text-purple-700 hover:text-purple-900 bg-purple-50 hover:bg-purple-100 px-3 py-1.5 rounded-xl transition-colors">
                                            <span>Preview</span>
                                            <svg class="w-3.5 h-3.5 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                        </a>

                                        <form action="{{ route('admin.services.destroy', $svc) }}" method="POST" onsubmit="return confirm('⚠️ DANGER: Permanently delete service \'{{ addslashes($svc->title) }}\' (ID #{{ $svc->id }})? This action cannot be undone.');" class="inline-block">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="p-1.5 text-rose-500 hover:text-white hover:bg-rose-600 rounded-xl transition border border-rose-200 hover:border-rose-600 shadow-xs cursor-pointer" title="Delete Service">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-12 text-center text-slate-400 text-xs">
                                    No services found in database.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($services->hasPages())
                <div class="p-4 border-t border-slate-100">
                    {{ $services->links() }}
                </div>
            @endif
        </div>
    </div>
</x-layouts.admin>
