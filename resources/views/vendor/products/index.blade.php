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
        <div class="mt-4 sm:mt-0">
            <a href="{{ route('vendor.products.create') }}" class="btn-primary inline-flex items-center justify-center text-sm shadow-sm hover:shadow transition-all">
                <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                <span>Add {{ $sec['item_singular'] }}</span>
            </a>
        </div>
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
                <div class="bg-white/80 backdrop-blur-xl shadow-sm border border-gray-100 rounded-3xl p-5 hover:shadow-md transition-shadow relative overflow-hidden group">
                    <div class="absolute top-0 left-0 w-1.5 h-full 
                        {{ $product->status === 'active' ? 'bg-green-400' : '' }}
                        {{ $product->status === 'draft' ? 'bg-yellow-400' : '' }}
                        {{ $product->status === 'archived' ? 'bg-gray-400' : '' }}
                    "></div>
                    
                    <div class="flex gap-4">
                        <div class="w-20 h-24 rounded-xl overflow-hidden bg-gray-100 flex-shrink-0 border border-gray-100">
                            <img src="{{ $product->primary_image_url }}" 
                                 alt="{{ $product->title }}" 
                                 class="w-full h-full object-cover"
                                 onerror="this.onerror=null; this.src='{{ asset('images/product-placeholder.svg') }}';">
                        </div>
                        
                        <div class="flex-1 min-w-0 flex flex-col justify-between">
                            <div>
                                <h3 class="font-bold text-gray-900 truncate">{{ $product->title }}</h3>
                                <p class="text-xs text-gray-400 mt-0.5 truncate">{{ $product->category->name ?? 'Uncategorized' }} • SKU: {{ $product->sku ?? 'N/A' }}</p>
                            </div>
                            
                            <div class="flex items-center justify-between mt-3 pt-2 border-t border-gray-100">
                                <div>
                                    <p class="text-base font-black text-brand-text">₹{{ number_format($product->base_price / 100, 2) }}</p>
                                    <p class="text-[11px] font-medium {{ $product->total_stock < 5 ? 'text-red-500' : 'text-gray-500' }}">Stock: {{ $product->total_stock }}</p>
                                </div>
                                <div class="flex items-center gap-2">
                                    <form action="{{ route('vendor.products.toggle_status', $product) }}" method="POST" class="inline">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" 
                                                class="px-2.5 py-1 text-[11px] font-bold rounded-full border {{ $product->status === 'active' ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-amber-50 text-amber-700 border-amber-200' }}">
                                            {{ $product->status === 'active' ? '● Live' : '○ Draft' }}
                                        </button>
                                    </form>
                                    <form action="{{ route('vendor.products.duplicate', $product) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" 
                                                title="Duplicate product"
                                                onclick="return confirm('Duplicate this product to create a draft copy?');"
                                                class="inline-flex items-center justify-center bg-stone-100 hover:bg-stone-200 text-stone-700 border border-stone-200 rounded-xl px-2.5 py-1 text-xs font-bold shadow-2xs transition-colors cursor-pointer">
                                            <span>📋 Clone</span>
                                        </button>
                                    </form>
                                    <a href="{{ route('vendor.products.edit', $product) }}" class="inline-flex items-center justify-center bg-gray-900 text-white rounded-xl px-3 py-1 text-xs font-bold shadow-xs hover:bg-black transition-colors">
                                        Edit
                                    </a>
                                    <form action="{{ route('vendor.products.destroy', $product) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this product? This will remove it from your store.');" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="inline-flex items-center gap-1 bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200/80 rounded-xl px-2.5 py-1 text-xs font-bold transition-colors cursor-pointer" title="Delete Product">
                                            <svg class="w-3.5 h-3.5 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            <span>Delete</span>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Desktop View (Table) -->
        <div class="hidden sm:block bg-white/60 backdrop-blur-xl shadow-sm border border-gray-100 rounded-3xl overflow-hidden">
            <table class="min-w-full divide-y divide-gray-100">
                <thead class="bg-gray-50/50">
                    <tr>
                        <th scope="col" class="py-4 pl-6 pr-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">{{ ($targetVertical ?? '') === 'food' ? 'Dish / Menu Item' : (($targetVertical ?? '') === 'services' ? 'Service Package' : (($targetVertical ?? '') === 'used' ? 'Item / Device' : 'Product')) }}</th>
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
                        <td class="whitespace-nowrap py-4 pl-6 pr-3">
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
                                    <div class="font-bold text-gray-900 max-w-[200px] truncate" title="{{ $product->title }}">{{ $product->title }}</div>
                                    <div class="text-xs text-gray-500 mt-0.5">SKU: {{ $product->sku ?? 'N/A' }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="whitespace-nowrap px-3 py-5">
                            <div class="text-sm font-medium text-gray-600">{{ $product->category->name ?? 'Uncategorized' }}</div>
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
</x-layouts.vendor>
