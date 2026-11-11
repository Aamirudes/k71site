<div class="space-y-6 text-xs select-none text-[#1A1018]">
    <!-- 1. Stores & Brands (Store Selector) -->
    <div class="pb-5 border-b border-black/8">
        <div class="flex items-center justify-between mb-3">
            <h3 class="font-bold uppercase tracking-[0.12em] text-[#623562] text-[10.5px]">Stores &amp; Brands</h3>
            <button type="button" 
                    x-show="filters.store" 
                    @click="updateFilter('store', '')" 
                    class="text-[10px] text-[#7C4F74] hover:text-[#1A1018] cursor-pointer">
                Clear
            </button>
        </div>
        <div class="space-y-1 max-h-56 overflow-y-auto no-scrollbar pr-1" style="scrollbar-width: none; -ms-overflow-style: none;">
            <label class="flex items-center justify-between cursor-pointer group py-2 px-2.5 rounded-xl hover:bg-[#ECD2F3]/20 transition active:scale-98 min-h-[38px]"
                   :class="!filters.store ? 'bg-[#ECD2F3]/35 font-bold text-[#1A1018]' : ''">
                <div class="flex items-center gap-2.5">
                    <input type="radio" 
                           name="{{ $formId ?? 'filter' }}_store" 
                           value="" 
                           :checked="!filters.store" 
                           @change="updateFilter('store', '')"
                           class="w-4 h-4 accent-[#1A1018] border-black/20 cursor-pointer">
                    <span class="text-[#1A1018]/80 group-hover:text-[#1A1018] font-medium">All Stores</span>
                </div>
            </label>
            @foreach($vendors as $v)
                <label class="flex items-center justify-between cursor-pointer group py-2 px-2.5 rounded-xl hover:bg-[#ECD2F3]/20 transition active:scale-98 min-h-[38px]"
                       :class="filters.store === '{{ $v->slug }}' ? 'bg-[#ECD2F3]/35 font-bold text-[#1A1018]' : ''">
                    <div class="flex items-center gap-2.5 min-w-0">
                        <input type="radio" 
                               name="{{ $formId ?? 'filter' }}_store" 
                               value="{{ $v->slug }}" 
                               :checked="filters.store === '{{ $v->slug }}'" 
                               @change="updateFilter('store', '{{ $v->slug }}')"
                               class="w-4 h-4 accent-[#1A1018] border-black/20 cursor-pointer">
                        <span class="text-[#1A1018]/80 group-hover:text-[#1A1018] truncate font-medium">
                            {{ $v->shop_name }}
                        </span>
                    </div>
                    <span class="text-[10px] font-mono text-[#7C4F74] shrink-0">({{ $v->products_count ?? 0 }})</span>
                </label>
            @endforeach
        </div>
    </div>

    <!-- 2. Categories -->
    <div class="pb-5 border-b border-black/8">
        <div class="flex items-center justify-between mb-3">
            <h3 class="font-bold uppercase tracking-[0.12em] text-[#623562] text-[10.5px]">Category</h3>
            <button type="button" 
                    x-show="filters.category" 
                    @click="updateFilter('category', '')" 
                    class="text-[10px] text-[#7C4F74] hover:text-[#1A1018] cursor-pointer">
                Clear
            </button>
        </div>
        <div class="space-y-1 max-h-56 overflow-y-auto no-scrollbar pr-1" style="scrollbar-width: none; -ms-overflow-style: none;">
            <label class="flex items-center justify-between cursor-pointer group py-2 px-2.5 rounded-xl hover:bg-[#ECD2F3]/20 transition active:scale-98 min-h-[38px]"
                   :class="!filters.category ? 'bg-[#ECD2F3]/35 font-bold text-[#1A1018]' : ''">
                <div class="flex items-center gap-2.5">
                    <input type="radio" 
                           name="{{ $formId ?? 'filter' }}_category" 
                           value="" 
                           :checked="!filters.category" 
                           @change="updateFilter('category', '')"
                           class="w-4 h-4 accent-[#1A1018] border-black/20 cursor-pointer">
                    <span class="text-[#1A1018]/80 group-hover:text-[#1A1018] font-medium">All Categories</span>
                </div>
            </label>
            @foreach($categories as $cat)
                <label class="flex items-center justify-between cursor-pointer group py-2 px-2.5 rounded-xl hover:bg-[#ECD2F3]/20 transition active:scale-98 min-h-[38px]"
                       :class="filters.category === '{{ $cat->slug }}' ? 'bg-[#ECD2F3]/35 font-bold text-[#1A1018]' : ''">
                    <div class="flex items-center gap-2.5 min-w-0">
                        <input type="radio" 
                               name="{{ $formId ?? 'filter' }}_category" 
                               value="{{ $cat->slug }}" 
                               :checked="filters.category === '{{ $cat->slug }}'" 
                               @change="updateFilter('category', '{{ $cat->slug }}')"
                               class="w-4 h-4 accent-[#1A1018] border-black/20 cursor-pointer">
                        <span class="text-[#1A1018]/80 group-hover:text-[#1A1018] truncate font-medium">
                            {{ $cat->name }}
                        </span>
                    </div>
                    @if($cat->products_count > 0)
                        <span class="text-[10px] font-mono text-[#7C4F74] shrink-0">({{ $cat->products_count }})</span>
                    @endif
                </label>
            @endforeach
        </div>
    </div>

    <!-- 3. Gender / Department -->
    <div class="pb-5 border-b border-black/8">
        <div class="flex items-center justify-between mb-3">
            <h3 class="font-bold uppercase tracking-[0.12em] text-[#623562] text-[10.5px]">Gender</h3>
            <button type="button" 
                    x-show="filters.gender" 
                    @click="updateFilter('gender', '')" 
                    class="text-[10px] text-[#7C4F74] hover:text-[#1A1018] cursor-pointer">
                Clear
            </button>
        </div>
        <div class="grid grid-cols-2 gap-2">
            @foreach(['all' => 'All', 'women' => 'Women', 'men' => 'Men', 'unisex' => 'Unisex'] as $gKey => $gLabel)
                <label class="flex items-center justify-center py-2.5 px-3 rounded-full border cursor-pointer transition select-none text-center active:scale-95 min-h-[40px]"
                       :class="((filters.gender === '{{ $gKey }}') || ('{{ $gKey }}' === 'all' && !filters.gender)) ? 'border-[#1A1018] bg-[#1A1018] text-white font-semibold shadow-xs' : 'border-black/12 bg-white text-[#1A1018] hover:border-[#623562]/40'">
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

    <!-- 4. Price Ranges & Custom Min/Max -->
    <div class="pb-5 border-b border-black/8">
        <div class="flex items-center justify-between mb-3">
            <h3 class="font-bold uppercase tracking-[0.12em] text-[#623562] text-[10.5px]">Price Range</h3>
            <button type="button" 
                    x-show="filters.price_range || filters.min_price || filters.max_price" 
                    @click="filters.price_range = ''; filters.min_price = ''; filters.max_price = ''; applyFilters(1)" 
                    class="text-[10px] text-[#7C4F74] hover:text-[#1A1018] cursor-pointer">
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
                <label class="flex items-center gap-2.5 cursor-pointer group py-2 px-2.5 rounded-xl hover:bg-[#ECD2F3]/20 transition active:scale-98 min-h-[38px]"
                       :class="filters.price_range === '{{ $rKey }}' ? 'bg-[#ECD2F3]/35 font-bold' : ''">
                    <input type="radio" 
                           name="{{ $formId ?? 'filter' }}_price_range" 
                           value="{{ $rKey }}" 
                           :checked="filters.price_range === '{{ $rKey }}'" 
                           @change="filters.min_price = ''; filters.max_price = ''; updateFilter('price_range', '{{ $rKey }}')"
                           class="w-4 h-4 accent-[#1A1018] border-black/20 cursor-pointer">
                    <span class="text-[#1A1018]/80 group-hover:text-[#1A1018] font-medium"
                          :class="filters.price_range === '{{ $rKey }}' ? 'font-bold text-[#1A1018]' : ''">
                        {{ $rLabel }}
                    </span>
                </label>
            @endforeach
        </div>

        <!-- Custom Min/Max Inputs -->
        <div class="pt-2 border-t border-black/6">
            <span class="text-[10px] text-[#7C4F74] uppercase font-semibold block mb-1.5">Custom Range</span>
            <div class="flex items-center gap-1.5">
                <input type="number" 
                       x-model="filters.min_price" 
                       placeholder="Min ₹" 
                       class="w-full px-2.5 py-2 grail-input-light text-xs">
                <span class="text-zinc-400 text-xs">-</span>
                <input type="number" 
                       x-model="filters.max_price" 
                       placeholder="Max ₹" 
                       class="w-full px-2.5 py-2 grail-input-light text-xs">
                <button type="button" 
                        @click="filters.price_range = ''; applyFilters(1)" 
                        class="px-3.5 py-2 grail-btn-obsidian text-xs shrink-0 cursor-pointer active:scale-95">
                    Go
                </button>
            </div>
        </div>
    </div>

    <!-- 5. Discount Percentage -->
    <div class="pb-5 border-b border-black/8">
        <div class="flex items-center justify-between mb-3">
            <h3 class="font-bold uppercase tracking-[0.12em] text-[#623562] text-[10.5px]">Discount</h3>
            <button type="button" 
                    x-show="filters.discount" 
                    @click="updateFilter('discount', '')" 
                    class="text-[10px] text-[#7C4F74] hover:text-[#1A1018] cursor-pointer">
                Clear
            </button>
        </div>
        <div class="space-y-1">
            @foreach([50 => '50% or more', 30 => '30% or more', 20 => '20% or more', 10 => '10% or more'] as $dVal => $dLabel)
                <label class="flex items-center gap-2.5 cursor-pointer group py-2 px-2.5 rounded-xl hover:bg-[#ECD2F3]/20 transition active:scale-98 min-h-[38px]"
                       :class="filters.discount == '{{ $dVal }}' ? 'bg-[#ECD2F3]/35 font-bold' : ''">
                    <input type="radio" 
                           name="{{ $formId ?? 'filter' }}_discount" 
                           value="{{ $dVal }}" 
                           :checked="filters.discount == '{{ $dVal }}'" 
                           @change="updateFilter('discount', '{{ $dVal }}')"
                           class="w-4 h-4 accent-[#1A1018] border-black/20 cursor-pointer">
                    <span class="text-[#1A1018]/80 group-hover:text-[#1A1018] font-medium"
                          :class="filters.discount == '{{ $dVal }}' ? 'font-bold text-[#1A1018]' : ''">
                        {{ $dLabel }}
                    </span>
                </label>
            @endforeach
        </div>
    </div>

    <!-- 6. Customer Ratings -->
    <div class="pb-5 border-b border-black/8">
        <div class="flex items-center justify-between mb-3">
            <h3 class="font-bold uppercase tracking-[0.12em] text-[#623562] text-[10.5px]">Customer Rating</h3>
            <button type="button" 
                    x-show="filters.rating" 
                    @click="updateFilter('rating', '')" 
                    class="text-[10px] text-[#7C4F74] hover:text-[#1A1018] cursor-pointer">
                Clear
            </button>
        </div>
        <div class="space-y-1">
            @foreach([4 => '4★ & Above', 3 => '3★ & Above'] as $rVal => $rLabel)
                <label class="flex items-center gap-2.5 cursor-pointer group py-2 px-2.5 rounded-xl hover:bg-[#ECD2F3]/20 transition active:scale-98 min-h-[38px]"
                       :class="filters.rating == '{{ $rVal }}' ? 'bg-[#ECD2F3]/35 font-bold' : ''">
                    <input type="radio" 
                           name="{{ $formId ?? 'filter' }}_rating" 
                           value="{{ $rVal }}" 
                           :checked="filters.rating == '{{ $rVal }}'" 
                           @change="updateFilter('rating', '{{ $rVal }}')"
                           class="w-4 h-4 accent-[#1A1018] border-black/20 cursor-pointer">
                    <div class="flex items-center gap-1.5 text-[#1A1018]/80 group-hover:text-[#1A1018] font-medium">
                        <span class="text-[#C9A24A] font-bold text-xs">★</span>
                        <span>{{ $rLabel }}</span>
                    </div>
                </label>
            @endforeach
        </div>
    </div>

    <!-- 7. In-Stock & Curations Toggles -->
    <div class="space-y-2.5">
        <label class="flex items-center justify-between cursor-pointer group p-3 rounded-2xl border border-black/10 hover:border-[#623562]/40 bg-[#FAFAF8] active:scale-98 min-h-[44px]">
            <div class="flex items-center gap-2.5">
                <input type="checkbox" 
                       x-model="filters.in_stock" 
                       @change="applyFilters(1)"
                       class="w-4 h-4 rounded accent-[#1A1018] border-black/20 cursor-pointer">
                <span class="font-semibold text-[#1A1018] text-xs">In Stock Only</span>
            </div>
            <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
        </label>

        <label class="flex items-center justify-between cursor-pointer group p-3 rounded-2xl border border-black/10 hover:border-[#623562]/40 bg-[#FAFAF8] active:scale-98 min-h-[44px]">
            <div class="flex items-center gap-2.5">
                <input type="checkbox" 
                       x-model="filters.featured" 
                       @change="applyFilters(1)"
                       class="w-4 h-4 rounded accent-[#1A1018] border-black/20 cursor-pointer">
                <span class="font-semibold text-[#1A1018] text-xs">Artisanal Handcrafted</span>
            </div>
            <span class="text-xs text-[#623562]">✦</span>
        </label>
    </div>
</div>
