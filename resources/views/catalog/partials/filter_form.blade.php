<div class="space-y-6 text-xs select-none">
    <!-- 1. Stores & Brands (Store Selector) -->
    <div class="pb-5 border-b border-stone-100">
        <div class="flex items-center justify-between mb-3">
            <h3 class="font-bold uppercase tracking-wider text-stone-900 text-[11px]">Stores &amp; Brands</h3>
            <button type="button" 
                    x-show="filters.store" 
                    @click="updateFilter('store', '')" 
                    class="text-[10px] text-stone-400 hover:text-stone-700 cursor-pointer">
                Clear
            </button>
        </div>
        <div class="space-y-1 max-h-56 overflow-y-auto no-scrollbar pr-1" style="scrollbar-width: none; -ms-overflow-style: none;">
            <label class="flex items-center justify-between cursor-pointer group py-2 px-2.5 rounded-xl hover:bg-stone-50 transition active:scale-98 min-h-[38px]"
                   :class="!filters.store ? 'bg-stone-100 font-bold' : ''">
                <div class="flex items-center gap-2.5">
                    <input type="radio" 
                           name="{{ $formId ?? 'filter' }}_store" 
                           value="" 
                           :checked="!filters.store" 
                           @change="updateFilter('store', '')"
                           class="w-4 h-4 text-stone-900 border-stone-300 focus:ring-stone-900 cursor-pointer">
                    <span class="text-stone-700 group-hover:text-stone-900 font-medium">All Stores</span>
                </div>
            </label>
            @foreach($vendors as $v)
                <label class="flex items-center justify-between cursor-pointer group py-2 px-2.5 rounded-xl hover:bg-stone-50 transition active:scale-98 min-h-[38px]"
                       :class="filters.store === '{{ $v->slug }}' ? 'bg-stone-100 font-bold' : ''">
                    <div class="flex items-center gap-2.5 min-w-0">
                        <input type="radio" 
                               name="{{ $formId ?? 'filter' }}_store" 
                               value="{{ $v->slug }}" 
                               :checked="filters.store === '{{ $v->slug }}'" 
                               @change="updateFilter('store', '{{ $v->slug }}')"
                               class="w-4 h-4 text-stone-900 border-stone-300 focus:ring-stone-900 cursor-pointer">
                        <span class="text-stone-700 group-hover:text-stone-900 truncate font-medium">
                            {{ $v->shop_name }}
                        </span>
                    </div>
                    <span class="text-[10px] font-mono text-stone-400 shrink-0">({{ $v->products_count ?? 0 }})</span>
                </label>
            @endforeach
        </div>
    </div>

    <!-- 2. Categories -->
    <div class="pb-5 border-b border-stone-100">
        <div class="flex items-center justify-between mb-3">
            <h3 class="font-bold uppercase tracking-wider text-stone-900 text-[11px]">Category</h3>
            <button type="button" 
                    x-show="filters.category" 
                    @click="updateFilter('category', '')" 
                    class="text-[10px] text-stone-400 hover:text-stone-700 cursor-pointer">
                Clear
            </button>
        </div>
        <div class="space-y-1 max-h-56 overflow-y-auto no-scrollbar pr-1" style="scrollbar-width: none; -ms-overflow-style: none;">
            <label class="flex items-center justify-between cursor-pointer group py-2 px-2.5 rounded-xl hover:bg-stone-50 transition active:scale-98 min-h-[38px]"
                   :class="!filters.category ? 'bg-stone-100 font-bold' : ''">
                <div class="flex items-center gap-2.5">
                    <input type="radio" 
                           name="{{ $formId ?? 'filter' }}_category" 
                           value="" 
                           :checked="!filters.category" 
                           @change="updateFilter('category', '')"
                           class="w-4 h-4 text-stone-900 border-stone-300 focus:ring-stone-900 cursor-pointer">
                    <span class="text-stone-700 group-hover:text-stone-900 font-medium">All Categories</span>
                </div>
            </label>
            @foreach($categories as $cat)
                <label class="flex items-center justify-between cursor-pointer group py-2 px-2.5 rounded-xl hover:bg-stone-50 transition active:scale-98 min-h-[38px]"
                       :class="filters.category === '{{ $cat->slug }}' ? 'bg-stone-100 font-bold' : ''">
                    <div class="flex items-center gap-2.5 min-w-0">
                        <input type="radio" 
                               name="{{ $formId ?? 'filter' }}_category" 
                               value="{{ $cat->slug }}" 
                               :checked="filters.category === '{{ $cat->slug }}'" 
                               @change="updateFilter('category', '{{ $cat->slug }}')"
                               class="w-4 h-4 text-stone-900 border-stone-300 focus:ring-stone-900 cursor-pointer">
                        <span class="text-stone-700 group-hover:text-stone-900 truncate font-medium">
                            {{ $cat->name }}
                        </span>
                    </div>
                    @if($cat->products_count > 0)
                        <span class="text-[10px] font-mono text-stone-400 shrink-0">({{ $cat->products_count }})</span>
                    @endif
                </label>
            @endforeach
        </div>
    </div>

    <!-- 3. Gender / Department -->
    <div class="pb-5 border-b border-stone-100">
        <div class="flex items-center justify-between mb-3">
            <h3 class="font-bold uppercase tracking-wider text-stone-900 text-[11px]">Gender</h3>
            <button type="button" 
                    x-show="filters.gender" 
                    @click="updateFilter('gender', '')" 
                    class="text-[10px] text-stone-400 hover:text-stone-700 cursor-pointer">
                Clear
            </button>
        </div>
        <div class="grid grid-cols-2 gap-2">
            @foreach(['all' => 'All', 'women' => 'Women', 'men' => 'Men', 'unisex' => 'Unisex'] as $gKey => $gLabel)
                <label class="flex items-center justify-center py-2.5 px-3 rounded-xl border cursor-pointer transition select-none text-center active:scale-95 min-h-[40px]"
                       :class="((filters.gender === '{{ $gKey }}') || ('{{ $gKey }}' === 'all' && !filters.gender)) ? 'border-stone-900 bg-stone-900 text-white font-semibold shadow-xs' : 'border-stone-200 bg-white text-stone-700 hover:border-stone-300'">
                    <input type="radio" 
                           name="{{ $formId ?? 'filter' }}_gender" 
                           value="{{ $gKey === 'all' ? '' : $gKey }}" 
                           :checked="((filters.gender === '{{ $gKey }}') || ('{{ $gKey }}' === 'all' && !filters.gender))" 
                           @change="updateFilter('gender', '{{ $gKey === 'all' ? '' : $gKey }}')"
                           class="sr-only">
                    <span>{{ $gLabel }}</span>
                </label>
            @endforeach
        </div>
    </div>

    <!-- 4. Price Ranges & Custom Min/Max (Amazon / Flipkart style) -->
    <div class="pb-5 border-b border-stone-100">
        <div class="flex items-center justify-between mb-3">
            <h3 class="font-bold uppercase tracking-wider text-stone-900 text-[11px]">Price Range</h3>
            <button type="button" 
                    x-show="filters.price_range || filters.min_price || filters.max_price" 
                    @click="filters.price_range = ''; filters.min_price = ''; filters.max_price = ''; applyFilters(1)" 
                    class="text-[10px] text-stone-400 hover:text-stone-700 cursor-pointer">
                Clear
            </button>
        </div>
        <div class="space-y-1 mb-3">
            @php
                $ranges = [
                    'under_999' => 'Under ₹999',
                    '1000_1999' => '₹1,000 - ₹1,999',
                    '2000_4999' => '₹2,000 - ₹4,999',
                    '5000_above' => '₹5,000 & Above'
                ];
            @endphp
            @foreach($ranges as $rKey => $rLabel)
                <label class="flex items-center gap-2.5 cursor-pointer group py-2 px-2.5 rounded-xl hover:bg-stone-50 transition active:scale-98 min-h-[38px]"
                       :class="filters.price_range === '{{ $rKey }}' ? 'bg-stone-100 font-bold' : ''">
                    <input type="radio" 
                           name="{{ $formId ?? 'filter' }}_price_range" 
                           value="{{ $rKey }}" 
                           :checked="filters.price_range === '{{ $rKey }}'" 
                           @change="filters.min_price = ''; filters.max_price = ''; updateFilter('price_range', '{{ $rKey }}')"
                           class="w-4 h-4 text-stone-900 border-stone-300 focus:ring-stone-900 cursor-pointer">
                    <span class="text-stone-700 group-hover:text-stone-900 font-medium"
                          :class="filters.price_range === '{{ $rKey }}' ? 'font-bold text-stone-900' : ''">
                        {{ $rLabel }}
                    </span>
                </label>
            @endforeach
        </div>

        <!-- Custom Min/Max Inputs -->
        <div class="pt-2 border-t border-stone-100">
            <span class="text-[10px] text-stone-400 uppercase font-semibold block mb-1.5">Custom Range</span>
            <div class="flex items-center gap-1.5">
                <input type="number" 
                       x-model="filters.min_price" 
                       placeholder="Min ₹" 
                       class="w-full px-2.5 py-2 rounded-xl border border-stone-200 text-xs bg-stone-50 focus:bg-white focus:outline-none focus:border-stone-900">
                <span class="text-stone-400 text-xs">-</span>
                <input type="number" 
                       x-model="filters.max_price" 
                       placeholder="Max ₹" 
                       class="w-full px-2.5 py-2 rounded-xl border border-stone-200 text-xs bg-stone-50 focus:bg-white focus:outline-none focus:border-stone-900">
                <button type="button" 
                        @click="filters.price_range = ''; applyFilters(1)" 
                        class="px-3.5 py-2 rounded-xl bg-stone-900 text-white font-semibold text-xs hover:bg-black transition shrink-0 cursor-pointer active:scale-95">
                    Go
                </button>
            </div>
        </div>
    </div>

    <!-- 5. Discount Percentage (Flipkart / Amazon style) -->
    <div class="pb-5 border-b border-stone-100">
        <div class="flex items-center justify-between mb-3">
            <h3 class="font-bold uppercase tracking-wider text-stone-900 text-[11px]">Discount</h3>
            <button type="button" 
                    x-show="filters.discount" 
                    @click="updateFilter('discount', '')" 
                    class="text-[10px] text-stone-400 hover:text-stone-700 cursor-pointer">
                Clear
            </button>
        </div>
        <div class="space-y-1">
            @foreach([50 => '50% or more', 30 => '30% or more', 20 => '20% or more', 10 => '10% or more'] as $dVal => $dLabel)
                <label class="flex items-center gap-2.5 cursor-pointer group py-2 px-2.5 rounded-xl hover:bg-stone-50 transition active:scale-98 min-h-[38px]"
                       :class="filters.discount == '{{ $dVal }}' ? 'bg-stone-100 font-bold' : ''">
                    <input type="radio" 
                           name="{{ $formId ?? 'filter' }}_discount" 
                           value="{{ $dVal }}" 
                           :checked="filters.discount == '{{ $dVal }}'" 
                           @change="updateFilter('discount', '{{ $dVal }}')"
                           class="w-4 h-4 text-stone-900 border-stone-300 focus:ring-stone-900 cursor-pointer">
                    <span class="text-stone-700 group-hover:text-stone-900 font-medium"
                          :class="filters.discount == '{{ $dVal }}' ? 'font-bold text-stone-900' : ''">
                        {{ $dLabel }}
                    </span>
                </label>
            @endforeach
        </div>
    </div>

    <!-- 6. Customer Ratings -->
    <div class="pb-5 border-b border-stone-100">
        <div class="flex items-center justify-between mb-3">
            <h3 class="font-bold uppercase tracking-wider text-stone-900 text-[11px]">Customer Rating</h3>
            <button type="button" 
                    x-show="filters.rating" 
                    @click="updateFilter('rating', '')" 
                    class="text-[10px] text-stone-400 hover:text-stone-700 cursor-pointer">
                Clear
            </button>
        </div>
        <div class="space-y-1">
            @foreach([4 => '4★ & Above', 3 => '3★ & Above'] as $rVal => $rLabel)
                <label class="flex items-center gap-2.5 cursor-pointer group py-2 px-2.5 rounded-xl hover:bg-stone-50 transition active:scale-98 min-h-[38px]"
                       :class="filters.rating == '{{ $rVal }}' ? 'bg-stone-100 font-bold' : ''">
                    <input type="radio" 
                           name="{{ $formId ?? 'filter' }}_rating" 
                           value="{{ $rVal }}" 
                           :checked="filters.rating == '{{ $rVal }}'" 
                           @change="updateFilter('rating', '{{ $rVal }}')"
                           class="w-4 h-4 text-stone-900 border-stone-300 focus:ring-stone-900 cursor-pointer">
                    <div class="flex items-center gap-1.5 text-stone-700 group-hover:text-stone-900 font-medium">
                        <span class="text-amber-500 font-bold text-xs">★</span>
                        <span>{{ $rLabel }}</span>
                    </div>
                </label>
            @endforeach
        </div>
    </div>

    <!-- 7. In-Stock & Curations Toggles -->
    <div class="space-y-2.5">
        <label class="flex items-center justify-between cursor-pointer group p-3 rounded-xl border border-stone-200 hover:border-stone-300 bg-stone-50/50 active:scale-98 min-h-[44px]">
            <div class="flex items-center gap-2.5">
                <input type="checkbox" 
                       x-model="filters.in_stock" 
                       @change="applyFilters(1)"
                       class="w-4 h-4 rounded text-stone-900 border-stone-300 focus:ring-stone-900 cursor-pointer">
                <span class="font-semibold text-stone-800 text-xs">In Stock Only</span>
            </div>
            <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
        </label>

        <label class="flex items-center justify-between cursor-pointer group p-3 rounded-xl border border-stone-200 hover:border-stone-300 bg-stone-50/50 active:scale-98 min-h-[44px]">
            <div class="flex items-center gap-2.5">
                <input type="checkbox" 
                       x-model="filters.featured" 
                       @change="applyFilters(1)"
                       class="w-4 h-4 rounded text-stone-900 border-stone-300 focus:ring-stone-900 cursor-pointer">
                <span class="font-semibold text-stone-800 text-xs">Artisanal Handcrafted</span>
            </div>
            <span class="text-xs">✦</span>
        </label>
    </div>
</div>
