@php
    $curVendor = auth()->user()?->vendor;
    $sec = $curVendor ? $curVendor->getSectorConfig() : [
        'name' => 'Marketplace Catalog',
        'catalog_name' => 'Products & Catalog',
        'item_singular' => 'Product',
        'icon' => '📦',
        'badge' => 'Verified Merchant'
    ];
@endphp

<x-layouts.vendor :title="$sec['catalog_name']">
    <!-- Header -->
    <div class="sm:flex sm:items-center sm:justify-between mb-8">
        <div>
            <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-indigo-50 dark:bg-indigo-950/50 text-indigo-700 dark:text-indigo-300 border border-indigo-200/50 dark:border-indigo-800/50 mb-2">
                <span>{{ $sec['icon'] }}</span>
                <span>{{ $sec['badge'] }}</span>
            </div>
            <h1 class="text-3xl font-serif font-bold text-gray-900 dark:text-white tracking-tight flex items-center gap-2">
                <span>{{ $sec['icon'] }}</span>
                <span>{{ $sec['catalog_name'] }}</span>
            </h1>
            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400 font-medium">
                Manage live {{ strtolower($sec['catalog_name']) }}, track inventory stock, set trade pricing, and configure warranty or specifications.
            </p>
        </div>
        <div class="mt-4 sm:mt-0 flex flex-wrap items-center gap-2.5">
            <a href="{{ route('vendor.products.export', request()->only(['search', 'status', 'stock', 'category_id'])) }}"
               class="inline-flex items-center justify-center rounded-2xl border border-gray-200 bg-white px-4 py-2.5 text-xs font-bold text-gray-700 shadow-2xs hover:bg-gray-50 transition-all">
                <span>⬇ Export CSV</span>
            </a>
            <a href="{{ route('vendor.products.create') }}" class="btn-primary inline-flex items-center justify-center text-sm shadow-sm hover:shadow transition-all">
                <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                <span>Add {{ $sec['item_singular'] }}</span>
            </a>
        </div>
    </div>

    @php
        $counts = $counts ?? ['all' => $products->total(), 'active' => 0, 'draft' => 0, 'low_stock' => 0];
        $status = $status ?? 'all';
        $stockFilter = $stockFilter ?? 'all';
        $search = $search ?? '';
        $categoryId = $categoryId ?? 0;
        $categories = $categories ?? collect();
        $pageProductIds = $products->pluck('id')->values()->all();
    @endphp

    <!-- Filter & Bulk Operations Toolbar -->
    <div x-data="{
            selectedIds: [],
            allPageIds: @js($pageProductIds),
            toggleAll(checked) {
                this.selectedIds = checked ? [...this.allPageIds] : [];
            }
         }" class="space-y-4">

        <div class="bg-white/75 backdrop-blur-xl border border-gray-100 rounded-3xl p-4 shadow-2xs space-y-3">
            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ route('vendor.products.index', array_filter(['search' => $search, 'category_id' => $categoryId ?: null])) }}"
                   class="px-3.5 py-1.5 rounded-xl text-xs font-bold border transition-all {{ $status === 'all' && $stockFilter === 'all' ? 'bg-gray-900 text-white border-gray-900' : 'bg-white text-gray-600 border-gray-200 hover:bg-gray-50' }}">
                    All ({{ $counts['all'] ?? 0 }})
                </a>
                <a href="{{ route('vendor.products.index', array_filter(['status' => 'active', 'search' => $search, 'category_id' => $categoryId ?: null])) }}"
                   class="px-3.5 py-1.5 rounded-xl text-xs font-bold border transition-all {{ $status === 'active' ? 'bg-emerald-600 text-white border-emerald-600' : 'bg-white text-emerald-700 border-emerald-200 hover:bg-emerald-50' }}">
                    ● Live ({{ $counts['active'] ?? 0 }})
                </a>
                <a href="{{ route('vendor.products.index', array_filter(['status' => 'draft', 'search' => $search, 'category_id' => $categoryId ?: null])) }}"
                   class="px-3.5 py-1.5 rounded-xl text-xs font-bold border transition-all {{ $status === 'draft' ? 'bg-amber-600 text-white border-amber-600' : 'bg-white text-amber-700 border-amber-200 hover:bg-amber-50' }}">
                    ○ Draft ({{ $counts['draft'] ?? 0 }})
                </a>
                <a href="{{ route('vendor.products.index', array_filter(['stock' => 'low', 'search' => $search, 'category_id' => $categoryId ?: null])) }}"
                   class="px-3.5 py-1.5 rounded-xl text-xs font-bold border transition-all {{ $stockFilter === 'low' ? 'bg-rose-600 text-white border-rose-600' : 'bg-white text-rose-700 border-rose-200 hover:bg-rose-50' }}">
                    ⚠ Low Stock ({{ $counts['low_stock'] ?? 0 }})
                </a>
            </div>

            <form method="GET" action="{{ route('vendor.products.index') }}" class="flex flex-wrap items-center gap-2">
                @if($status !== 'all') <input type="hidden" name="status" value="{{ $status }}"> @endif
                @if($stockFilter !== 'all') <input type="hidden" name="stock" value="{{ $stockFilter }}"> @endif
                <input type="text" name="search" value="{{ $search }}"
                       placeholder="Search title, SKU, brand, category..."
                       class="flex-1 min-w-[200px] rounded-2xl border border-gray-200 bg-white px-4 py-2 text-xs text-gray-900 placeholder:text-gray-400">
                @if($categories->isNotEmpty())
                    <select name="category_id" class="rounded-2xl border border-gray-200 bg-white px-3 py-2 text-xs font-semibold text-gray-700">
                        <option value="">All Categories</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ (int)$categoryId === (int)$cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                        @endforeach
                    </select>
                @endif
                <button type="submit" class="px-4 py-2 rounded-2xl bg-gray-900 text-white text-xs font-bold hover:bg-black transition-colors cursor-pointer">Filter</button>
                @if($search !== '' || $status !== 'all' || $stockFilter !== 'all' || $categoryId > 0)
                    <a href="{{ route('vendor.products.index') }}" class="px-3 py-2 rounded-2xl border border-gray-200 text-xs font-bold text-gray-600 hover:bg-gray-50">Reset</a>
                @endif
            </form>
        </div>

        <!-- Bulk Action Sticky Strip (Hidden Table Selection State) -->
        <div x-show="selectedIds.length > 0" x-cloak
             class="flex flex-wrap items-center justify-between gap-3 rounded-2xl bg-zinc-900 text-white px-4 py-3 shadow-lg">
            <span class="text-xs font-bold"><span x-text="selectedIds.length"></span> listing(s) selected</span>
            <form method="POST" action="{{ route('vendor.products.bulk_action') }}" class="flex flex-wrap items-center gap-2 m-0">
                @csrf
                <template x-for="pid in selectedIds" :key="pid">
                    <input type="hidden" name="product_ids[]" :value="pid">
                </template>
                <button type="submit" name="action" value="activate" class="px-3 py-1.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold cursor-pointer">Publish Selected</button>
                <button type="submit" name="action" value="draft" class="px-3 py-1.5 rounded-xl bg-amber-500 hover:bg-amber-400 text-black text-xs font-bold cursor-pointer">Move to Draft</button>
                <button type="submit" name="action" value="delete" onclick="return confirm('Permanently delete selected products? This cannot be undone.');" class="px-3 py-1.5 rounded-xl bg-rose-600 hover:bg-rose-500 text-white text-xs font-bold cursor-pointer">Delete Selected</button>
                <button type="button" @click="selectedIds = []" class="px-2.5 py-1.5 rounded-xl bg-white/10 hover:bg-white/20 text-xs font-semibold cursor-pointer">Clear</button>
            </form>
        </div>

    @if($products->isEmpty())
        <div class="bg-white/60 dark:bg-zinc-900/60 backdrop-blur-xl border border-gray-100 dark:border-zinc-800 rounded-3xl shadow-sm p-12 text-center">
            <div class="w-20 h-20 bg-gray-50 dark:bg-zinc-800 rounded-full flex items-center justify-center mx-auto mb-4 text-3xl">
                <span>{{ $sec['icon'] }}</span>
            </div>
            <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-1">
                No {{ $sec['catalog_name'] }} Listed Yet
            </h3>
            <p class="text-gray-500 dark:text-gray-400 text-sm max-w-sm mx-auto mb-6">
                Start building your {{ $sec['name'] }} store by adding your first {{ strtolower($sec['item_singular']) }}.
            </p>
            <a href="{{ route('vendor.products.create') }}" class="btn-primary inline-flex items-center justify-center text-sm px-6 py-2">
                <span>Add Your First {{ $sec['item_singular'] }}</span>
            </a>
        </div>
    @else
        <!-- Mobile View (Cards) -->
        <div class="block sm:hidden space-y-4">
            @foreach($products as $product)
                <div class="bg-white/80 backdrop-blur-xl shadow-sm border border-gray-100 rounded-3xl p-4 hover:shadow-md transition-shadow relative overflow-hidden group">
                    <div class="absolute top-0 left-0 w-1.5 h-full 
                        {{ $product->status === 'active' ? 'bg-green-500' : '' }}
                        {{ $product->status === 'draft' ? 'bg-amber-400' : '' }}
                        {{ $product->status === 'archived' ? 'bg-gray-400' : '' }}
                    "></div>
                    
                    <div class="flex gap-3.5">
                        <div class="w-20 h-24 rounded-2xl overflow-hidden bg-gray-100 flex-shrink-0 border border-gray-100 shadow-2xs">
                            <img src="{{ $product->primary_image_url }}" 
                                 alt="{{ $product->title }}" 
                                 class="w-full h-full object-cover"
                                 onerror="this.onerror=null; this.src='{{ asset('images/product-placeholder.svg') }}';">
                        </div>
                        
                        <div class="flex-1 min-w-0 flex flex-col justify-between py-0.5">
                            <div>
                                <div class="flex items-center gap-1.5">
                                    @if($product->vertical === 'food')
                                        <span class="inline-flex items-center justify-center w-3.5 h-3.5 rounded border {{ $product->isVeg() ? 'border-emerald-600' : 'border-rose-600' }} shrink-0" title="{{ ucfirst(str_replace('_', ' ', $product->dietary_label_resolved ?? ($product->isVeg() ? 'veg' : 'non_veg'))) }}">
                                            <span class="w-1.5 h-1.5 rounded-full {{ $product->isVeg() ? 'bg-emerald-600' : 'bg-rose-600' }}"></span>
                                        </span>
                                    @endif
                                    <h3 class="font-bold text-gray-900 text-sm sm:text-base truncate">{{ $product->title }}</h3>
                                </div>
                                <p class="text-xs text-gray-400 mt-0.5 truncate">
                                    {{ $product->foodMenuCategory?->name ?? $product->category->name ?? 'Uncategorized' }} • SKU: {{ $product->sku ?? 'N/A' }}
                                </p>
                            </div>
                            
                            <div class="flex flex-wrap items-center gap-2 mt-2">
                                <span class="text-base font-black text-brand-text">₹{{ number_format($product->base_price / 100, 2) }}</span>
                                <span class="text-xs font-semibold {{ $product->total_stock < 5 ? 'text-red-500' : 'text-gray-500' }}">Stock: {{ $product->total_stock }}</span>
                                @if(!empty($completenessMap[$product->id]))
                                    @php $comp = $completenessMap[$product->id]; @endphp
                                    <span class="text-[10px] font-bold px-2 py-0.5 rounded-full border {{ $comp['badge_class'] }}"
                                          title="{{ !empty($comp['missing']) ? 'Missing: ' . implode(', ', $comp['missing']) : 'Listing quality verified' }}">
                                        {{ $comp['score'] }}% {{ $comp['grade'] }}
                                    </span>
                                @endif
                                @if($product->vertical === 'food')
                                    <span class="text-[10px] font-bold px-2 py-0.5 rounded-full border {{ $product->is_dish_available ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-rose-50 text-rose-700 border-rose-200' }}">
                                        {{ $product->is_dish_available ? 'In Kitchen Stock' : 'Sold Out' }}
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>

                    @if($product->vertical === 'food')
                        <div class="mt-2.5 flex justify-end">
                            <form action="{{ route('vendor.products.toggle_availability', $product) }}" method="POST" class="w-full m-0">
                                @csrf
                                @method('PATCH')
                                <button type="submit"
                                        class="w-full py-1.5 px-2 text-[11px] font-bold rounded-xl border transition-all cursor-pointer {{ $product->is_dish_available ? 'bg-emerald-50 hover:bg-emerald-100 text-emerald-800 border-emerald-200' : 'bg-rose-50 hover:bg-rose-100 text-rose-800 border-rose-200' }}">
                                    {{ $product->is_dish_available ? '✓ Kitchen Available — Tap to Mark Sold Out' : '⏸ Sold Out — Tap to Mark Available' }}
                                </button>
                            </form>
                        </div>
                    @endif

                    <!-- Dedicated Responsive Mobile Action Toolbar (All 4 buttons visible) -->
                    <div class="grid grid-cols-4 gap-1.5 mt-3 pt-3 border-t border-gray-100/80">
                        <form action="{{ route('vendor.products.toggle_status', $product) }}" method="POST" class="w-full m-0">
                            @csrf
                            @method('PATCH')
                            <button type="submit" 
                                    title="Toggle between Live and Draft"
                                    class="w-full py-1.5 px-1 text-[11px] font-bold rounded-xl border flex items-center justify-center transition-all active:scale-95 cursor-pointer shadow-2xs {{ $product->status === 'active' ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-amber-50 text-amber-700 border-amber-200' }}">
                                <span class="truncate">{{ $product->status === 'active' ? '● Live' : '○ Draft' }}</span>
                            </button>
                        </form>

                        <form action="{{ route('vendor.products.duplicate', $product) }}" method="POST" class="w-full m-0">
                            @csrf
                            <button type="submit" 
                                    title="Duplicate product"
                                    onclick="return confirm('Duplicate this product to create a draft copy?');"
                                    class="w-full py-1.5 px-1 text-[11px] font-bold rounded-xl border border-stone-200 bg-stone-50 hover:bg-stone-100 text-stone-700 flex items-center justify-center transition-all active:scale-95 cursor-pointer shadow-2xs">
                                <span class="truncate">📋 Clone</span>
                            </button>
                        </form>

                        <a href="{{ route('vendor.products.edit', $product) }}" 
                           class="w-full py-1.5 px-1 text-[11px] font-bold rounded-xl bg-gray-900 text-white hover:bg-black flex items-center justify-center transition-all active:scale-95 shadow-2xs">
                            <span class="truncate">✏️ Edit</span>
                        </a>

                        <form action="{{ route('vendor.products.destroy', $product) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this product? This will remove it from your store.');" class="w-full m-0">
                            @csrf
                            @method('DELETE')
                            <button type="submit" 
                                    title="Delete Product"
                                    class="w-full py-1.5 px-1 text-[11px] font-bold rounded-xl border border-rose-200 bg-rose-50 hover:bg-rose-100 text-rose-700 flex items-center justify-center transition-all active:scale-95 cursor-pointer shadow-2xs">
                                <span class="truncate">🗑️ Delete</span>
                            </button>
                        </form>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Desktop View (Table) -->
        <div class="hidden sm:block bg-white/60 backdrop-blur-xl shadow-sm border border-gray-100 rounded-3xl overflow-hidden">
            <table class="min-w-full divide-y divide-gray-100">
                <thead class="bg-gray-50/50">
                    <tr>
                        <th scope="col" class="py-4 pl-6 pr-2 w-8">
                            <input type="checkbox"
                                   @change="toggleAll($event.target.checked)"
                                   :checked="allPageIds.length > 0 && selectedIds.length === allPageIds.length"
                                   class="rounded border-gray-300 text-black focus:ring-black cursor-pointer"
                                   aria-label="Select all listings on this page">
                        </th>
                        <th scope="col" class="py-4 pl-2 pr-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">{{ ($targetVertical ?? '') === 'food' ? 'Dish / Menu Item' : (($targetVertical ?? '') === 'services' ? 'Service Package' : (($targetVertical ?? '') === 'used' ? 'Item / Device' : 'Product')) }}</th>
                        <th scope="col" class="px-3 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Category</th>
                        <th scope="col" class="px-3 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Price</th>
                        <th scope="col" class="px-3 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Stock</th>
                        <th scope="col" class="px-3 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Status</th>
                        <th scope="col" class="relative py-4 pl-3 pr-6">
                            <span class="sr-only">Edit</span>
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 bg-white/40">
                    @foreach($products as $product)
                    <tr class="hover:bg-white/80 transition-colors group">
                        <td class="whitespace-nowrap py-4 pl-6 pr-2">
                            <input type="checkbox" value="{{ $product->id }}" x-model="selectedIds" class="rounded border-gray-300 text-black focus:ring-black cursor-pointer" aria-label="Select {{ $product->title }}">
                        </td>
                        <td class="whitespace-nowrap py-4 pl-2 pr-3">
                            <div class="flex items-center">
                                <div class="w-1.5 h-10 rounded-full mr-4
                                    {{ $product->status === 'active' ? 'bg-green-400' : '' }}
                                    {{ $product->status === 'draft' ? 'bg-yellow-400' : '' }}
                                    {{ $product->status === 'archived' ? 'bg-gray-400' : '' }}
                                "></div>
                                <div class="h-12 w-12 flex-shrink-0 rounded-lg overflow-hidden border border-gray-100 bg-gray-50">
                                    <img src="{{ $product->primary_image_url }}" 
                                         alt="{{ $product->title }}" 
                                         class="h-full w-full object-cover"
                                         onerror="this.onerror=null; this.src='{{ asset('images/product-placeholder.svg') }}';">
                                </div>
                                <div class="ml-4">
                                    <div class="flex items-center gap-1.5">
                                        @if($product->vertical === 'food')
                                            <span class="inline-flex items-center justify-center w-3.5 h-3.5 rounded border {{ $product->isVeg() ? 'border-emerald-600' : 'border-rose-600' }} shrink-0" title="{{ ucfirst(str_replace('_', ' ', $product->dietary_label_resolved ?? ($product->isVeg() ? 'veg' : 'non_veg'))) }}">
                                                <span class="w-1.5 h-1.5 rounded-full {{ $product->isVeg() ? 'bg-emerald-600' : 'bg-rose-600' }}"></span>
                                            </span>
                                        @endif
                                        <div class="font-bold text-gray-900 max-w-[200px] truncate" title="{{ $product->title }}">{{ $product->title }}</div>
                                    </div>
                                    <div class="text-xs text-gray-500 mt-0.5">SKU: {{ $product->sku ?? 'N/A' }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="whitespace-nowrap px-3 py-5">
                            <div class="text-sm font-medium text-gray-600">{{ $product->category->name ?? 'Uncategorized' }}</div>
                            @if($product->vertical === 'food' && $product->foodMenuCategory)
                                <span class="inline-flex items-center mt-1 px-2 py-0.5 rounded-md text-[10px] font-bold bg-orange-50 text-orange-700 border border-orange-200">
                                    🍽️ {{ $product->foodMenuCategory->name }}
                                </span>
                            @endif
                        </td>
                        <td class="whitespace-nowrap px-3 py-5">
                            <div class="text-sm font-black text-brand-text">₹{{ number_format($product->base_price / 100, 2) }}</div>
                        </td>
                        <td class="whitespace-nowrap px-3 py-5">
                            <div class="text-sm font-bold {{ $product->total_stock < 5 ? 'text-red-500' : 'text-gray-900' }}">{{ $product->total_stock }}</div>
                            @if($product->variants->count() > 1)
                                <div class="text-xs text-gray-400 mt-0.5">Across {{ $product->variants->count() }} variants</div>
                            @endif
                        </td>
                        <td class="whitespace-nowrap px-3 py-5">
                            <div class="flex flex-col items-start gap-1.5">
                                <form action="{{ route('vendor.products.toggle_status', $product) }}" method="POST" class="inline">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" 
                                            title="Click to toggle between Live and Draft"
                                            class="inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-xs font-bold border transition-all cursor-pointer shadow-2xs hover:scale-102
                                        {{ $product->status === 'active' ? 'bg-emerald-50 text-emerald-700 border-emerald-200 hover:bg-emerald-100' : 'bg-amber-50 text-amber-700 border-amber-200 hover:bg-amber-100' }}">
                                        <span class="w-1.5 h-1.5 rounded-full {{ $product->status === 'active' ? 'bg-emerald-500' : 'bg-amber-500' }}"></span>
                                        <span>{{ $product->status === 'active' ? 'Live on Store' : 'Draft (Hidden)' }}</span>
                                    </button>
                                </form>
                                @if(!empty($completenessMap[$product->id]))
                                    @php $comp = $completenessMap[$product->id]; @endphp
                                    <span class="inline-flex items-center gap-1 rounded-full px-2.5 py-0.5 text-[10px] font-bold border {{ $comp['badge_class'] }}"
                                          title="{{ !empty($comp['missing']) ? 'Improve by: ' . implode(' • ', $comp['missing']) : 'All key listing fields complete' }}">
                                        <span>Readiness {{ $comp['score'] }}%</span>
                                        @if(!empty($comp['missing']))
                                            <span class="opacity-70">({{ count($comp['missing']) }} tip)</span>
                                        @endif
                                    </span>
                                @endif
                                @if($product->vertical === 'food')
                                    <form action="{{ route('vendor.products.toggle_availability', $product) }}" method="POST" class="inline">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit"
                                                title="Toggle Kitchen Availability (Available / Sold Out)"
                                                class="inline-flex items-center gap-1 rounded-full px-2.5 py-0.5 text-[11px] font-bold border transition-all cursor-pointer {{ $product->is_dish_available ? 'bg-teal-50 text-teal-800 border-teal-200 hover:bg-teal-100' : 'bg-rose-50 text-rose-700 border-rose-200 hover:bg-rose-100' }}">
                                            <span>{{ $product->is_dish_available ? '✓ Kitchen Open' : '⏸ Sold Out' }}</span>
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                        <td class="relative whitespace-nowrap py-5 pl-3 pr-6 text-right">
                            <div class="inline-flex items-center gap-2">
                                <form action="{{ route('vendor.products.duplicate', $product) }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit" 
                                            title="Duplicate product (copy specs, sizes & photos to a new draft)"
                                            onclick="return confirm('Duplicate this product to create a draft copy?');"
                                            class="inline-flex items-center justify-center bg-stone-50 hover:bg-stone-100 text-stone-700 border border-stone-200 rounded-xl px-3 py-1.5 text-xs font-bold shadow-2xs transition-colors cursor-pointer">
                                        <svg class="w-3.5 h-3.5 mr-1 text-stone-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                                        <span>Clone</span>
                                    </button>
                                </form>
                                <a href="{{ route('vendor.products.edit', $product) }}" class="inline-flex items-center justify-center bg-white border border-gray-200 text-gray-700 rounded-xl px-3.5 py-1.5 text-xs font-bold shadow-2xs hover:bg-gray-50 transition-colors">
                                    Edit
                                </a>
                                <form action="{{ route('vendor.products.destroy', $product) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this product? This will remove it from your store.');" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="inline-flex items-center gap-1.5 bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200/80 rounded-xl px-3 py-1.5 text-xs font-bold transition-colors cursor-pointer" title="Delete Product">
                                        <svg class="w-3.5 h-3.5 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        <span>Delete</span>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        
        <div class="mt-6">
            {{ $products->links() }}
        </div>
    @endif
    </div>
</x-layouts.vendor>
