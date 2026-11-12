<x-layouts.admin title="Manage Partner Boutiques">
    <div class="space-y-6">
        
        <!-- Header Banner -->
        <div class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-200/80 shadow-xs flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div>
                <div class="flex items-center gap-2">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-amber-50 text-amber-700 border border-amber-200">
                        Merchant Network
                    </span>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                        Zero-Float 95% Disbursals
                    </span>
                </div>
                <h2 class="text-2xl font-serif font-bold text-slate-900 mt-2">Partner Boutiques &amp; Merchant Ateliers</h2>
                <p class="text-xs sm:text-sm text-slate-500 mt-1">Audit merchant applications, verified UPI settlement VPAs, catalog volume, and gross platform performance.</p>
            </div>
            <div class="flex items-center gap-3">
                <span class="px-3.5 py-2 rounded-2xl bg-slate-900 text-xs font-mono font-bold text-white shadow-xs">
                    {{ $vendors->total() ?? $vendors->count() }} Boutiques
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
                <a href="{{ route('admin.vendors', request('search') ? ['search' => request('search')] : []) }}" class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition-all {{ !request('status') && !request('type') ? 'bg-slate-900 text-white shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                    All Boutiques ({{ $counts['all'] }})
                </a>
                <a href="{{ route('admin.vendors', array_merge(request()->query(), ['status' => 'active'])) }}" class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition-all {{ request('status') === 'active' ? 'bg-emerald-600 text-white shadow-xs' : 'bg-emerald-50 text-emerald-700 hover:bg-emerald-100' }}">
                    Active ({{ $counts['active'] }})
                </a>
                <a href="{{ route('admin.vendors', array_merge(request()->query(), ['status' => 'pending'])) }}" class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition-all {{ request('status') === 'pending' ? 'bg-amber-500 text-slate-950 shadow-xs' : 'bg-amber-50 text-amber-700 hover:bg-amber-100' }}">
                    Pending Review ({{ $counts['pending'] }})
                </a>
                <a href="{{ route('admin.vendors', array_merge(request()->query(), ['status' => 'suspended'])) }}" class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition-all {{ request('status') === 'suspended' ? 'bg-rose-600 text-white shadow-xs' : 'bg-rose-50 text-rose-700 hover:bg-rose-100' }}">
                    Suspended ({{ $counts['suspended'] }})
                </a>
                <a href="{{ route('admin.vendors', array_merge(request()->query(), ['type' => request('type') === 'food' ? null : 'food'])) }}" class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition-all {{ request('type') === 'food' ? 'bg-orange-600 text-white shadow-xs' : 'bg-orange-50 text-orange-700 hover:bg-orange-100' }}">
                    🍽️ Food Kitchens ({{ $counts['food'] ?? 0 }})
                </a>
                <a href="{{ route('admin.vendors', array_merge(request()->query(), ['feature' => request('feature') === 'kinetic' ? null : 'kinetic'])) }}" class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition-all {{ request('feature') === 'kinetic' ? 'bg-zinc-900 text-[#ECD2F3] shadow-xs' : 'bg-fuchsia-50 text-fuchsia-800 hover:bg-fuchsia-100' }}">
                    🎬 Scroll Motion ({{ $counts['kinetic'] ?? 0 }})
                </a>
            </div>

            <!-- Search Form -->
            <form action="{{ route('admin.vendors') }}" method="GET" class="flex flex-col sm:flex-row gap-3">
                @if(request('status'))
                    <input type="hidden" name="status" value="{{ request('status') }}">
                @endif
                @if(request('type'))
                    <input type="hidden" name="type" value="{{ request('type') }}">
                @endif
                @if(request('feature'))
                    <input type="hidden" name="feature" value="{{ request('feature') }}">
                @endif
                <div class="relative flex-1">
                    <svg class="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by shop name, slug, merchant name, email, or phone..." class="w-full pl-10 pr-4 py-2.5 rounded-2xl border border-slate-200 text-xs text-slate-900 placeholder-slate-400 focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 transition-all font-medium">
                </div>

                <div class="flex items-center gap-2">
                    <button type="submit" class="px-5 py-2.5 rounded-2xl bg-amber-500 hover:bg-amber-600 text-xs font-bold text-slate-950 shadow-xs transition-all cursor-pointer">
                        Filter Boutiques
                    </button>
                    @if(request()->hasAny(['search', 'status', 'type', 'feature']))
                        <a href="{{ route('admin.vendors') }}" class="px-4 py-2.5 rounded-2xl bg-slate-100 hover:bg-slate-200 text-xs font-semibold text-slate-600 transition-colors">
                            Clear
                        </a>
                    @endif
                </div>
            </form>
        </div>

        <!-- Vendors Ledger Table -->
        <div class="bg-white rounded-3xl border border-slate-200/80 shadow-xs overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-100">
                    <thead class="bg-slate-50/80">
                        <tr>
                            <th class="px-6 py-4 text-left text-[11px] font-bold text-slate-400 uppercase tracking-wider font-mono">Boutique Profile</th>
                            <th class="px-6 py-4 text-left text-[11px] font-bold text-slate-400 uppercase tracking-wider font-mono">Merchant Contact</th>
                            <th class="px-6 py-4 text-left text-[11px] font-bold text-slate-400 uppercase tracking-wider font-mono">UPI &amp; Payout Target</th>
                            <th class="px-6 py-4 text-left text-[11px] font-bold text-slate-400 uppercase tracking-wider font-mono">Catalog &amp; Orders</th>
                            <th class="px-6 py-4 text-left text-[11px] font-bold text-slate-400 uppercase tracking-wider font-mono">GMV Volume</th>
                            <th class="px-6 py-4 text-left text-[11px] font-bold text-slate-400 uppercase tracking-wider font-mono">Status Override</th>
                            <th class="px-6 py-4 text-right text-[11px] font-bold text-slate-400 uppercase tracking-wider font-mono">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-xs">
                        @forelse($vendors as $vendor)
                            <tr class="hover:bg-slate-50/60 transition-colors">
                                
                                <!-- Shop Profile -->
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-2xl bg-gradient-to-br from-amber-400 to-amber-600 text-slate-950 font-serif font-bold text-base flex items-center justify-center flex-shrink-0 shadow-xs">
                                            {{ substr($vendor->shop_name, 0, 1) }}
                                        </div>
                                        <div>
                                            <div class="flex items-center gap-1.5 flex-wrap">
                                                <span class="font-bold text-slate-900 text-sm">{{ $vendor->shop_name }}</span>
                                                @if($vendor->isFood())
                                                    <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-orange-50 text-orange-700 border border-orange-200">🍽️ Food</span>
                                                @endif
                                            </div>
                                            <a href="{{ $vendor->storefront_url }}" target="_blank" class="text-[11px] font-mono text-amber-600 hover:underline">
                                                {{ $vendor->slug }}.{{ config('marketplace.domain', 'marblemandi.org') }} ↗
                                            </a>
                                            <span class="text-[10px] text-slate-400 block font-mono">Joined {{ $vendor->created_at->format('d M, Y') }}</span>
                                            @if($vendor->isFood() || !empty($vendor->settings?->fssai_license_number))
                                                <div class="mt-1.5 flex items-center gap-1.5 flex-wrap">
                                                    @if(!empty($vendor->settings?->fssai_license_number))
                                                        <span class="text-[10px] font-mono px-1.5 py-0.5 rounded border {{ $vendor->isFssaiVerified() ? 'bg-emerald-50 text-emerald-800 border-emerald-200 font-bold' : 'bg-amber-50 text-amber-800 border-amber-200 font-semibold' }}">
                                                            {{ $vendor->isFssaiVerified() ? '✓ FSSAI Verified' : '⏳ FSSAI Pending' }} · {{ $vendor->settings->fssai_license_number }}
                                                        </span>
                                                        <form action="{{ route('admin.vendors.verify_fssai', $vendor) }}" method="POST" class="inline">
                                                            @csrf
                                                            <input type="hidden" name="verified" value="{{ $vendor->isFssaiVerified() ? '0' : '1' }}">
                                                            <button type="submit" class="text-[10px] font-bold px-2 py-0.5 rounded border cursor-pointer transition-colors {{ $vendor->isFssaiVerified() ? 'bg-rose-50 hover:bg-rose-100 text-rose-700 border-rose-200' : 'bg-emerald-600 hover:bg-emerald-700 text-white border-emerald-600' }}">
                                                                {{ $vendor->isFssaiVerified() ? 'Revoke FSSAI' : 'Verify FSSAI' }}
                                                            </button>
                                                        </form>
                                                    @else
                                                        <span class="text-[10px] text-slate-400 italic">No FSSAI License submitted</span>
                                                    @endif
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </td>

                                <!-- Merchant Contact -->
                                <td class="px-6 py-4 whitespace-nowrap text-slate-600">
                                    <span class="font-semibold text-slate-900 block">{{ $vendor->user->first_name ?? '' }} {{ $vendor->user->last_name ?? '' }}</span>
                                    <a href="mailto:{{ $vendor->contact_email ?? $vendor->user->email ?? '' }}" class="text-slate-500 hover:text-slate-900 font-mono text-[11px] block">
                                        {{ $vendor->contact_email ?? $vendor->user->email ?? 'N/A' }}
                                    </a>
                                    <span class="text-[11px] font-mono text-slate-400">
                                        Phone: {{ $vendor->contact_phone ?? $vendor->user->phone ?? 'N/A' }}
                                    </span>
                                </td>

                                <!-- UPI ID & Payout Account -->
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if(!empty($vendor->settings->upi_id))
                                        <div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-blue-50 text-blue-800 border border-blue-200 font-mono text-xs font-bold">
                                            <span>⚡ {{ $vendor->settings->upi_id }}</span>
                                        </div>
                                        <span class="text-[10px] text-slate-400 block mt-1">Instant UPI VPA configured</span>
                                    @else
                                        <div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-amber-50 text-amber-800 border border-amber-200 text-[11px] font-semibold">
                                            <span>⚠️ No UPI VPA Setup</span>
                                        </div>
                                        <span class="text-[10px] text-slate-400 block mt-1">Payouts will require manual setup</span>
                                    @endif
                                </td>

                                <!-- Products & Orders -->
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="font-bold text-slate-900 block">{{ $vendor->products_count }} Products</span>
                                    <span class="text-[11px] text-slate-500 block font-mono">{{ $vendor->orders_count }} Total Orders</span>
                                </td>

                                <!-- GMV & Disbursed -->
                                <td class="px-6 py-4 whitespace-nowrap font-mono">
                                    <span class="font-extrabold text-slate-900 block">₹{{ number_format(($vendor->total_gmv ?? 0) / 100, 2) }}</span>
                                    <span class="text-[11px] text-blue-700 block">₹{{ number_format(($vendor->total_payouts ?? 0) / 100, 2) }} Disbursed</span>
                                </td>

                                <!-- Operational Status -->
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <form action="{{ route('admin.vendors.status', $vendor) }}" method="POST">
                                        @csrf
                                        @method('PUT')
                                        <select name="status" onchange="this.form.submit()" class="text-xs rounded-xl border-slate-300 py-1.5 pl-3 pr-8 bg-slate-50 font-bold focus:ring-amber-500 focus:border-amber-500 cursor-pointer {{ $vendor->status === 'active' ? 'text-emerald-700' : ($vendor->status === 'pending' ? 'text-amber-700' : 'text-rose-700') }}">
                                            <option value="active" {{ $vendor->status == 'active' ? 'selected' : '' }}>● ACTIVE</option>
                                            <option value="pending" {{ $vendor->status == 'pending' ? 'selected' : '' }}>● PENDING REVIEW</option>
                                            <option value="suspended" {{ $vendor->status == 'suspended' ? 'selected' : '' }}>● SUSPENDED</option>
                                        </select>
                                    </form>
                                    <form action="{{ route('admin.vendors.toggle_kinetic_scroll', $vendor) }}" method="POST" class="mt-2">
                                        @csrf
                                        <input type="hidden" name="enabled" value="{{ $vendor->hasKineticScrollAccess() ? '0' : '1' }}">
                                        <button type="submit"
                                                title="Allow this vendor to use Kinetic Video-to-JPG Scroll Motion"
                                                class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg border text-[11px] font-bold cursor-pointer transition-colors {{ $vendor->hasKineticScrollAccess() ? 'bg-zinc-900 text-[#ECD2F3] border-zinc-800 hover:bg-zinc-800' : 'bg-slate-100 text-slate-600 border-slate-200 hover:bg-slate-200' }}">
                                            <span>{{ $vendor->hasKineticScrollAccess() ? '🎬 Scroll Motion: ON' : '🎬 Scroll Motion: OFF' }}</span>
                                        </button>
                                    </form>
                                </td>

                                <!-- Actions -->
                                <td class="px-6 py-4 whitespace-nowrap text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="{{ route('admin.orders', ['vendor_id' => $vendor->id]) }}" class="px-3 py-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-800 font-bold text-xs transition-colors">
                                            Orders ({{ $vendor->orders_count }})
                                        </a>
                                        <a href="{{ $vendor->storefront_url }}" target="_blank" class="p-1.5 rounded-xl bg-slate-50 hover:bg-slate-100 text-slate-500 hover:text-slate-900 border border-slate-200 transition-colors" title="View Storefront">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                        </a>

                                        <form action="{{ route('admin.vendors.destroy', $vendor) }}" method="POST" onsubmit="return confirm('⚠️ DANGER: Are you sure you want to delete vendor \'{{ addslashes($vendor->shop_name) }}\' and all their products? This cannot be undone.');" class="inline-block">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="p-1.5 text-rose-500 hover:text-white hover:bg-rose-600 rounded-xl transition border border-rose-200 hover:border-rose-600 shadow-xs cursor-pointer" title="Delete Vendor">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            </button>
                                        </form>
                                    </div>
                                </td>

                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-12 text-center text-xs text-slate-400 italic">
                                    No boutiques found matching your criteria.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if(method_exists($vendors, 'links'))
                <div class="px-6 py-4 border-t border-slate-100 bg-slate-50/50">
                    {{ $vendors->links() }}
                </div>
            @endif
        </div>

    </div>
</x-layouts.admin>
