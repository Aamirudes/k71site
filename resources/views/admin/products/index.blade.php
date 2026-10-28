<x-layouts.admin title="Marketplace Catalog Inventory">
    <div class="space-y-6">
        
        <!-- Header Banner -->
        <div class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-200/80 shadow-xs flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div>
                <div class="flex items-center gap-2">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                        Global Catalog
                    </span>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-amber-50 text-amber-700 border border-amber-200">
                        Multi-Vendor Inventory
                    </span>
                </div>
                <h2 class="text-2xl font-serif font-bold text-slate-900 mt-2">Marketplace Catalog Inventory</h2>
                <p class="text-xs sm:text-sm text-slate-500 mt-1">Cross-vendor SKU auditing, boutique stock telemetry, and catalog visibility control across the marketplace.</p>
            </div>
            <div class="flex items-center gap-3">
                <span class="px-3.5 py-2 rounded-2xl bg-slate-900 text-xs font-mono font-bold text-white shadow-xs">
                    {{ $products->total() ?? $products->count() }} Products
                </span>
            </div>
        </div>

        @if(session('success'))
            <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-2xl p-4 text-xs sm:text-sm flex items-center gap-3 shadow-xs">
                <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        <!-- Filter & Search Controls -->
        <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-xs space-y-4">
            
            <!-- Filter Tabs -->
            <div class="flex flex-wrap items-center gap-2 border-b border-slate-100 pb-4">
                <a href="{{ route('admin.products') }}" class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition-all {{ !request('status') && !request('stock') ? 'bg-slate-900 text-white shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                    All Products ({{ $counts['all'] }})
                </a>
                <a href="{{ route('admin.products', array_merge(request()->query(), ['status' => 'active', 'stock' => null])) }}" class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition-all {{ request('status') === 'active' && !request('stock') ? 'bg-emerald-600 text-white shadow-xs' : 'bg-emerald-50 text-emerald-700 hover:bg-emerald-100' }}">
                    Active ({{ $counts['active'] }})
                </a>
                <a href="{{ route('admin.products', array_merge(request()->query(), ['stock' => 'low', 'status' => null])) }}" class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition-all {{ request('stock') === 'low' ? 'bg-amber-500 text-slate-950 shadow-xs' : 'bg-amber-50 text-amber-700 hover:bg-amber-100' }}">
                    Low Stock (≤3) ({{ $counts['low_stock'] }})
                </a>
                <a href="{{ route('admin.products', array_merge(request()->query(), ['stock' => 'out', 'status' => null])) }}" class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition-all {{ request('stock') === 'out' ? 'bg-rose-600 text-white shadow-xs' : 'bg-rose-50 text-rose-700 hover:bg-rose-100' }}">
                    Out of Stock ({{ $counts['out_of_stock'] }})
                </a>
            </div>

            <!-- Search Form -->
            <form action="{{ route('admin.products') }}" method="GET" class="flex flex-col sm:flex-row gap-3">
                @if(request('status'))
                    <input type="hidden" name="status" value="{{ request('status') }}">
                @endif
                @if(request('stock'))
                    <input type="hidden" name="stock" value="{{ request('stock') }}">
                @endif
                <div class="relative flex-1">
                    <svg class="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by product title, SKU code, or origin boutique..." class="w-full pl-10 pr-4 py-2.5 rounded-2xl border border-slate-200 text-xs text-slate-900 placeholder-slate-400 focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 transition-all font-medium">
                </div>

                <div class="flex items-center gap-2">
                    <button type="submit" class="px-5 py-2.5 rounded-2xl bg-amber-500 hover:bg-amber-600 text-xs font-bold text-slate-950 shadow-xs transition-all cursor-pointer">
                        Filter Catalog
                    </button>
                    @if(request()->hasAny(['search', 'status', 'stock']))
                        <a href="{{ route('admin.products') }}" class="px-4 py-2.5 rounded-2xl bg-slate-100 hover:bg-slate-200 text-xs font-semibold text-slate-600 transition-colors">
                            Clear
                        </a>
                    @endif
                </div>
            </form>
        </div>

        <!-- Catalog Products Table -->
        <div class="bg-white rounded-3xl border border-slate-200/80 shadow-xs overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-100">
                    <thead class="bg-slate-50/80">
                        <tr>
                            <th class="px-6 py-4 text-left text-[11px] font-bold text-slate-400 uppercase tracking-wider font-mono">Product &amp; SKU</th>
                            <th class="px-6 py-4 text-left text-[11px] font-bold text-slate-400 uppercase tracking-wider font-mono">Origin Boutique</th>
                            <th class="px-6 py-4 text-left text-[11px] font-bold text-slate-400 uppercase tracking-wider font-mono">Category</th>
                            <th class="px-6 py-4 text-left text-[11px] font-bold text-slate-400 uppercase tracking-wider font-mono">Base Price</th>
                            <th class="px-6 py-4 text-left text-[11px] font-bold text-slate-400 uppercase tracking-wider font-mono">Stock Level</th>
                            <th class="px-6 py-4 text-left text-[11px] font-bold text-slate-400 uppercase tracking-wider font-mono">Catalog Status</th>
                            <th class="px-6 py-4 text-right text-[11px] font-bold text-slate-400 uppercase tracking-wider font-mono">Public View</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-xs">
                        @forelse($products as $product)
                            <tr class="hover:bg-slate-50/60 transition-colors">
                                
                                <!-- Product & SKU -->
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center gap-3">
                                        <div class="w-12 h-14 bg-slate-100 rounded-xl overflow-hidden flex-shrink-0 border border-slate-200">
                                            @if($product->images && $product->images->count() > 0)
                                                <img src="{{ $product->primary_image_url }}" alt="{{ $product->title }}" class="w-full h-full object-cover">
                                            @else
                                                <div class="w-full h-full flex items-center justify-center text-slate-400 text-base">👗</div>
                                            @endif
                                        </div>
                                        <div>
                                            <span class="font-bold text-slate-900 block text-sm">{{ $product->title }}</span>
                                            <span class="text-[11px] font-mono text-slate-400 block mt-0.5">SKU: {{ $product->sku ?? 'N/A' }}</span>
                                        </div>
                                    </div>
                                </td>

                                <!-- Origin Boutique -->
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($product->vendor)
                                        <a href="{{ route('admin.orders', ['vendor_id' => $product->vendor->id]) }}" class="font-bold text-slate-900 hover:text-amber-600 block">
                                            {{ $product->vendor->shop_name }}
                                        </a>
                                        <a href="{{ $product->vendor->storefront_url }}" target="_blank" class="text-[11px] font-mono text-amber-600 hover:underline">
                                            {{ $product->vendor->slug }}.{{ config('marketplace.domain', 'marblemandi.org') }} ↗
                                        </a>
                                    @else
                                        <span class="text-slate-400 font-medium">Direct Marketplace</span>
                                    @endif
                                </td>

                                <!-- Category -->
                                <td class="px-6 py-4 whitespace-nowrap text-slate-600 font-medium">
                                    {{ $product->category->name ?? 'Uncategorized' }}
                                </td>

                                <!-- Base Price -->
                                <td class="px-6 py-4 whitespace-nowrap font-mono font-bold text-slate-900 text-sm">
                                    ₹{{ number_format($product->base_price / 100, 2) }}
                                </td>

                                <!-- Stock Level -->
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($product->total_stock <= 0)
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold bg-rose-50 text-rose-700 border border-rose-200 uppercase tracking-wider font-mono">
                                            <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                                            Out of Stock (0)
                                        </span>
                                    @elseif($product->total_stock <= 3)
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold bg-amber-50 text-amber-800 border border-amber-200 uppercase tracking-wider font-mono">
                                            <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                                            Low: {{ $product->total_stock }} left
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-800 border border-emerald-200 uppercase tracking-wider font-mono">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                            {{ $product->total_stock }} in stock
                                        </span>
                                    @endif
                                </td>

                                <!-- Catalog Status -->
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($product->status === 'active')
                                        <span class="px-2.5 py-1 inline-flex text-[10px] font-bold rounded-full bg-emerald-100 text-emerald-800 uppercase tracking-wider">
                                            Active
                                        </span>
                                    @else
                                        <span class="px-2.5 py-1 inline-flex text-[10px] font-bold rounded-full bg-slate-100 text-slate-600 uppercase tracking-wider">
                                            {{ ucfirst($product->status ?? 'Draft') }}
                                        </span>
                                    @endif
                                </td>

                                <!-- Actions -->
                                <td class="px-6 py-4 whitespace-nowrap text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="{{ route('product.show', $product->slug) }}" target="_blank" class="inline-flex items-center gap-1 text-xs font-bold text-slate-700 hover:text-slate-900 bg-slate-100 hover:bg-slate-200 px-3 py-1.5 rounded-xl transition-colors">
                                            <span>View</span>
                                            <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                        </a>

                                        <form action="{{ route('admin.products.destroy', $product) }}" method="POST" onsubmit="return confirm('⚠️ DANGER: Are you sure you want to delete product \'{{ addslashes($product->title) }}\' (ID #{{ $product->id }})? This cannot be undone.');" class="inline-block">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="p-1.5 text-rose-500 hover:text-white hover:bg-rose-600 rounded-xl transition border border-rose-200 hover:border-rose-600 shadow-xs cursor-pointer" title="Delete Product">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            </button>
                                        </form>
                                    </div>
                                </td>

                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-12 text-center text-xs text-slate-400 italic">
                                    No products listed in catalog matching your criteria.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if(method_exists($products, 'links'))
                <div class="px-6 py-4 border-t border-slate-100 bg-slate-50/50">
                    {{ $products->links() }}
                </div>
            @endif
        </div>

    </div>
</x-layouts.admin>
