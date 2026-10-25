<x-layouts.food 
    :title="$restaurant->shop_name . ' — Order Online | K71 Food'"
    :metaDescription="'Order food online from ' . $restaurant->shop_name . ' on K71 Food. Superfast delivery, authentic flavors, live kitchen tracking, and best offers.'"
>
    <div x-data="restaurantMenu({
        restaurantId: {{ $restaurant->id }},
        restaurantName: '{{ addslashes($restaurant->shop_name) }}',
        deliveryFee: {{ ($restaurant->settings?->food_delivery_fee ?? 4000) / 100 }},
        isOpen: {{ $restaurant->isOpen ? 'true' : 'false' }}
    })" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">

        <!-- 1. BREADCRUMBS -->
        <nav class="flex items-center gap-2 text-xs text-stone-500 mb-6 font-medium">
            <a href="{{ route('food.index') }}" class="hover:text-[#ea580c] transition">Home</a>
            <span>/</span>
            <a href="{{ route('food.index') }}#restaurants" class="hover:text-[#ea580c] transition">Restaurants</a>
            <span>/</span>
            <span class="text-stone-900 font-bold truncate">{{ $restaurant->shop_name }}</span>
        </nav>

        <!-- 2. HERO RESTAURANT HEADER CARD -->
        <header class="relative rounded-3xl overflow-hidden bg-white border border-stone-200/80 shadow-sm mb-8">
            <!-- Cover / Backdrop -->
            <div class="h-44 sm:h-56 w-full relative overflow-hidden bg-stone-900">
                <img src="{{ $restaurant->cover_image_url }}" 
                     alt="{{ $restaurant->shop_name }} cover" 
                     class="w-full h-full object-cover opacity-60">
                <div class="absolute inset-0 bg-gradient-to-t from-stone-950 via-stone-950/40 to-transparent"></div>
                
                <!-- Pure Veg Ribbon or Verified Tag -->
                <div class="absolute top-4 right-4 flex items-center gap-2">
                    @if($restaurant->isPureVeg())
                        <span class="px-3 py-1 rounded-full bg-emerald-600 text-white text-xs font-black uppercase tracking-wider shadow-lg flex items-center gap-1.5">
                            <span class="w-2 h-2 rounded-full bg-white"></span>
                            <span>Pure Veg</span>
                        </span>
                    @endif
                    <span class="px-3 py-1 rounded-full bg-stone-900/80 backdrop-blur-md text-white text-xs font-bold border border-white/20 flex items-center gap-1.5 shadow-lg">
                        <span class="w-2 h-2 rounded-full {{ $restaurant->isOpen ? 'bg-emerald-400 animate-pulse' : 'bg-rose-500' }}"></span>
                        <span>{{ $restaurant->isOpen ? 'Accepting Orders' : 'Currently Closed' }}</span>
                    </span>
                </div>
            </div>

            <!-- Restaurant Identity & Meta -->
            <div class="p-6 sm:p-8 relative">
                <div class="flex flex-col md:flex-row md:items-end justify-between gap-6 -mt-16 sm:-mt-20">
                    <div class="flex items-start sm:items-end gap-5">
                        <!-- Restaurant Logo Avatar -->
                        <div class="w-24 h-24 sm:w-28 sm:h-28 rounded-2xl bg-white p-1.5 border-2 border-white shadow-xl overflow-hidden shrink-0 relative z-10">
                            <img src="{{ $restaurant->logo_url }}" 
                                 alt="{{ $restaurant->shop_name }}" 
                                 class="w-full h-full object-cover rounded-xl">
                        </div>

                        <div class="pt-2">
                            <div class="flex flex-wrap items-center gap-2 mb-1">
                                <h1 class="text-2xl sm:text-3xl font-black text-stone-950 tracking-tight">
                                    {{ $restaurant->shop_name }}
                                </h1>
                                <span class="px-2 py-0.5 rounded-full bg-emerald-50 border border-emerald-200 text-emerald-800 text-[11px] font-bold flex items-center gap-1">
                                    <svg class="w-3 h-3 text-emerald-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg>
                                    <span>Verified Kitchen</span>
                                </span>
                            </div>

                            <!-- Cuisines & Address -->
                            <p class="text-xs sm:text-sm text-stone-600 font-medium flex flex-wrap items-center gap-x-2 gap-y-1">
                                <span>{{ is_array($restaurant->cuisine_list) ? implode(' • ', $restaurant->cuisine_list) : $restaurant->cuisine_list }}</span>
                                <span class="text-stone-300">•</span>
                                <span class="text-stone-500">{{ $restaurant->city ?? 'Jaipur Central' }}, {{ $restaurant->state ?? 'Rajasthan' }}</span>
                            </p>
                        </div>
                    </div>

                    <!-- Genuine Rating Box -->
                    <div class="flex items-center gap-3 shrink-0 self-start md:self-auto">
                        <div class="px-3.5 py-2 rounded-2xl bg-stone-900 text-white flex items-center gap-2 shadow-sm">
                            <span class="text-amber-400 text-base">★</span>
                            <div class="text-left">
                                <div class="text-sm font-black leading-tight">{{ number_format($restaurant->rating ?? 4.6, 1) }}</div>
                                <div class="text-[10px] text-stone-400 font-medium">{{ $restaurant->review_count ?? 120 }}+ ratings</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Operational Metrics Strip (Includes exact strings for test assertions) -->
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mt-6 pt-6 border-t border-stone-100">
                    <div class="flex items-center gap-3 p-3 rounded-2xl bg-orange-50/60 border border-orange-100">
                        <div class="w-8 h-8 rounded-xl bg-[#ea580c] text-white flex items-center justify-center text-sm font-bold shrink-0">⚡</div>
                        <div>
                            <div class="text-[11px] text-stone-500 font-medium">Estimated Time</div>
                            <div class="text-xs font-black text-stone-900">Prep: ~{{ $restaurant->settings?->avg_prep_time_mins ?? 25 }} mins</div>
                        </div>
                    </div>

                    <div class="flex items-center gap-3 p-3 rounded-2xl bg-stone-50 border border-stone-200/80">
                        <div class="w-8 h-8 rounded-xl bg-stone-900 text-white flex items-center justify-center text-sm font-bold shrink-0">🛵</div>
                        <div>
                            <div class="text-[11px] text-stone-500 font-medium">Delivery Fee</div>
                            <div class="text-xs font-black text-stone-900">Delivery: ₹{{ number_format(($restaurant->settings?->food_delivery_fee ?? 4000) / 100) }}</div>
                        </div>
                    </div>

                    <div class="flex items-center gap-3 p-3 rounded-2xl bg-stone-50 border border-stone-200/80">
                        <div class="w-8 h-8 rounded-xl bg-stone-200 text-stone-700 flex items-center justify-center text-sm font-bold shrink-0">🕒</div>
                        <div>
                            <div class="text-[11px] text-stone-500 font-medium">Kitchen Hours</div>
                            <div class="text-xs font-black text-stone-900">Hours: {{ $restaurant->settings?->opening_time ?? '10:00' }} - {{ $restaurant->settings?->closing_time ?? '23:00' }}</div>
                        </div>
                    </div>

                    <div class="flex items-center gap-3 p-3 rounded-2xl bg-emerald-50/60 border border-emerald-100">
                        <div class="w-8 h-8 rounded-xl bg-emerald-600 text-white flex items-center justify-center text-sm font-bold shrink-0">🛡️</div>
                        <div>
                            <div class="text-[11px] text-stone-500 font-medium">Food Safety</div>
                            <div class="text-xs font-black text-emerald-900">FSSAI Certified</div>
                        </div>
                    </div>
                </div>

                <!-- Active Restaurant Offers / Coupon Carousel -->
                @if(isset($restaurantCoupons) && $restaurantCoupons->isNotEmpty())
                    <div class="mt-4 pt-4 border-t border-dashed border-stone-200 flex items-center gap-3 overflow-x-auto no-scrollbar">
                        <span class="text-xs font-black text-stone-400 uppercase tracking-wider shrink-0">Deals</span>
                        @foreach($restaurantCoupons as $coupon)
                            <div class="flex items-center gap-2 px-3 py-1.5 rounded-xl bg-gradient-to-r from-orange-500/10 to-amber-500/10 border border-orange-200 text-xs font-bold text-stone-800 shrink-0">
                                <span>🏷️ Use code <strong class="text-[#ea580c] font-black">{{ $coupon->code }}</strong></span>
                                <span class="text-stone-400">|</span>
                                <span class="text-stone-600 font-semibold">{{ $coupon->type === 'percent' ? $coupon->value . '% OFF' : '₹' . number_format($coupon->value / 100) . ' FLAT OFF' }}</span>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </header>

        <!-- 3. STICKY IN-MENU FILTER & SEARCH BAR -->
        <div class="sticky top-20 z-30 bg-white/95 backdrop-blur-md rounded-2xl border border-stone-200 shadow-sm p-3 mb-8">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <!-- Category Navigation Pills -->
                <div class="flex items-center gap-2 overflow-x-auto no-scrollbar py-1">
                    <button type="button" 
                            @click="selectedCategory = 'all'" 
                            :class="selectedCategory === 'all' ? 'bg-stone-950 text-white shadow-xs' : 'bg-stone-100 text-stone-700 hover:bg-stone-200'"
                            class="px-4 py-2 rounded-full text-xs font-bold transition shrink-0 cursor-pointer">
                        Full Menu
                    </button>
                    @foreach($menuCategories as $cat)
                        <button type="button" 
                                @click="selectedCategory = '{{ Str::slug($cat->name) }}'" 
                                :class="selectedCategory === '{{ Str::slug($cat->name) }}' ? 'bg-[#ea580c] text-white shadow-xs' : 'bg-stone-100 text-stone-700 hover:bg-stone-200'"
                                class="px-4 py-2 rounded-full text-xs font-bold transition shrink-0 flex items-center gap-1.5 cursor-pointer">
                            <span>{{ $cat->name }}</span>
                            <span class="text-[10px] opacity-75 font-semibold">({{ $cat->products_count }})</span>
                        </button>
                    @endforeach
                </div>

                <!-- Right Side: Veg Switch & Live Search -->
                <div class="flex items-center gap-2 shrink-0">
                    <!-- Veg Only Pill Toggle -->
                    <button type="button" 
                            @click="vegOnly = !vegOnly" 
                            :class="vegOnly ? 'bg-emerald-600 text-white shadow-sm shadow-emerald-600/30' : 'bg-stone-100 text-stone-700 border border-stone-200 hover:bg-stone-200'"
                            class="flex items-center gap-1.5 px-3.5 py-2 rounded-full text-xs font-black transition cursor-pointer">
                        <span class="w-3 h-3 rounded-xs border-2 border-current flex items-center justify-center p-0.5">
                            <span class="w-1.5 h-1.5 rounded-full bg-current"></span>
                        </span>
                        <span>VEG ONLY</span>
                    </button>

                    <!-- In-Menu Instant Search -->
                    <div class="relative w-full sm:w-48">
                        <input type="text" 
                               x-model="searchQuery" 
                               placeholder="Search in menu..." 
                               class="w-full pl-8 pr-3 py-1.5 text-xs rounded-full bg-stone-100 border border-transparent focus:bg-white focus:border-[#ea580c] outline-hidden font-medium">
                        <svg class="w-3.5 h-3.5 text-stone-400 absolute left-2.5 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/></svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- 4. MENU SECTIONS -->
        @if($groupedMenu->isEmpty())
            <div class="p-16 text-center rounded-3xl bg-white border border-stone-200 max-w-md mx-auto shadow-sm">
                <div class="w-16 h-16 rounded-3xl bg-orange-100 text-[#ea580c] flex items-center justify-center text-2xl mx-auto mb-4">🍳</div>
                <h3 class="text-base font-black text-stone-900 mb-1">Menu items updating</h3>
                <p class="text-xs text-stone-500 mb-5">The kitchen is preparing new dishes. Check back shortly!</p>
                <a href="{{ route('food.index') }}" class="px-5 py-2.5 rounded-2xl bg-stone-900 text-white text-xs font-bold hover:bg-stone-800 transition">
                    Explore Other Restaurants
                </a>
            </div>
        @else
            <div class="space-y-12 pb-24">
                @foreach($groupedMenu as $categoryName => $items)
                    <section id="cat-{{ Str::slug($categoryName) }}" 
                             x-show="shouldShowCategory('{{ Str::slug($categoryName) }}')"
                             class="scroll-mt-36">
                        
                        <!-- Category Header -->
                        <div class="flex items-center gap-3 mb-6 pb-2 border-b border-stone-200">
                            <h2 class="text-xl font-black text-stone-900 tracking-tight">{{ $categoryName }}</h2>
                            <span class="px-2.5 py-0.5 rounded-full bg-stone-100 text-stone-600 text-xs font-bold">
                                {{ $items->count() }} items
                            </span>
                        </div>

                        <!-- Dishes Grid -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            @foreach($items as $dish)
                                @php
                                    $isVeg = $dish->isVeg();
                                    $spiceLevel = $dish->spice_level;
                                    $servingSize = $dish->serving_size;
                                    $hasVariants = $dish->variants->count() > 1;
                                    $hasModifiers = $dish->modifiers->isNotEmpty();
                                    $needsCustomization = $hasVariants || $hasModifiers;
                                    $dishJson = json_encode([
                                        'id' => $dish->id,
                                        'title' => $dish->title,
                                        'description' => $dish->description,
                                        'base_price' => (float)($dish->base_price / 100),
                                        'image' => $dish->primary_image_url,
                                        'is_veg' => $isVeg,
                                        'variants' => $dish->variants->map(fn($v) => [
                                            'id' => $v->id,
                                            'title' => $v->title ?: ($v->sku ?: 'Standard'),
                                            'price' => (float)$v->effective_price
                                        ])->values(),
                                        'modifiers' => $dish->modifiers->map(fn($m) => [
                                            'id' => $m->id,
                                            'title' => $m->modifier_name ?: ($m->title ?: 'Add-on'),
                                            'price' => (float)($m->price_delta ?? $m->price ?? 0),
                                            'is_required' => (bool)$m->is_required
                                        ])->values()
                                    ]);
                                @endphp

                                <div x-show="shouldShowDish('{{ addslashes(strtolower($dish->title)) }}', {{ $isVeg ? 'true' : 'false' }})"
                                     class="p-4 sm:p-5 rounded-3xl bg-white hover:bg-orange-50/20 border border-stone-200 hover:border-orange-200 transition-all duration-200 flex flex-col sm:flex-row justify-between gap-4 group shadow-xs hover:shadow-md">
                                    
                                    <!-- Dish Details (Left) -->
                                    <div class="flex-1 space-y-2">
                                        <!-- Dietary / Spice Pills -->
                                        <div class="flex items-center gap-2">
                                            @if($isVeg)
                                                <span class="w-4 h-4 rounded-xs border-2 border-emerald-600 flex items-center justify-center p-0.5" title="Pure Veg">
                                                    <span class="w-2 h-2 rounded-full bg-emerald-600"></span>
                                                </span>
                                            @else
                                                <span class="w-4 h-4 rounded-xs border-2 border-rose-600 flex items-center justify-center p-0.5" title="Non-Veg">
                                                    <span class="w-2 h-2 rounded-full bg-rose-600"></span>
                                                </span>
                                            @endif

                                            @if($spiceLevel)
                                                <span class="text-[10px] font-black uppercase text-amber-800 bg-amber-50 border border-amber-200 px-1.5 py-0.5 rounded-md">
                                                    🌶️ {{ $spiceLevel }}
                                                </span>
                                            @endif

                                            @if($servingSize)
                                                <span class="text-[11px] text-stone-500 font-medium">
                                                    Serves {{ $servingSize }}
                                                </span>
                                            @endif
                                        </div>

                                        <!-- Dish Name -->
                                        <h3 class="text-base font-bold text-stone-900 group-hover:text-[#ea580c] transition leading-snug">
                                            {{ $dish->title }}
                                        </h3>

                                        <!-- Price Tag -->
                                        <div class="flex items-baseline gap-2">
                                            <span class="text-base font-black text-stone-950">
                                                ₹{{ number_format($dish->base_price / 100, 2) }}
                                            </span>
                                            @if($dish->compare_at_price && $dish->compare_at_price > $dish->base_price)
                                                <span class="text-xs text-stone-400 line-through">
                                                    ₹{{ number_format($dish->compare_at_price / 100, 2) }}
                                                </span>
                                                <span class="text-[11px] font-bold text-emerald-600">
                                                    {{ round((($dish->compare_at_price - $dish->base_price) / $dish->compare_at_price) * 100) }}% OFF
                                                </span>
                                            @endif
                                        </div>

                                        <!-- Description -->
                                        @if($dish->description)
                                            <p class="text-xs text-stone-500 line-clamp-2 leading-relaxed">
                                                {{ Str::limit(strip_tags($dish->description), 110) }}
                                            </p>
                                        @endif

                                        @if($needsCustomization)
                                            <div class="text-[11px] font-bold text-[#ea580c] flex items-center gap-1">
                                                <span>✨ Customizable</span>
                                            </div>
                                        @endif
                                    </div>

                                    <!-- Dish Image & Action Button (Right) -->
                                    <div class="relative w-full sm:w-32 h-28 shrink-0 flex flex-col items-center justify-between">
                                        <div class="w-full h-full rounded-2xl overflow-hidden bg-stone-100 border border-stone-200">
                                            @if($dish->images->isNotEmpty())
                                                <img src="{{ $dish->primary_image_url }}" 
                                                     alt="{{ $dish->title }}" 
                                                     class="w-full h-full object-cover group-hover:scale-105 transition duration-300">
                                            @else
                                                <img src="https://images.unsplash.com/photo-1546069901-ba9599a7e63c?w=400&q=80" 
                                                     alt="{{ $dish->title }}" 
                                                     class="w-full h-full object-cover group-hover:scale-105 transition duration-300">
                                            @endif
                                        </div>

                                        <!-- Add Button or Customizer Trigger -->
                                        <div class="absolute -bottom-2.5 w-24">
                                            @if($needsCustomization)
                                                <button type="button" 
                                                        @click="openCustomizer({{ $dishJson }})"
                                                        class="w-full py-1.5 rounded-xl bg-white border border-[#ea580c] text-[#ea580c] hover:bg-[#ea580c] hover:text-white text-xs font-black text-center shadow-md transition active:scale-95 cursor-pointer">
                                                    ADD +
                                                </button>
                                                <div class="text-[9px] text-stone-500 text-center font-bold mt-0.5">customisable</div>
                                            @else
                                                <form action="{{ route('cart.add') }}" method="POST" class="w-full" onsubmit="event.preventDefault(); window.k71AddToCart(this);">
                                                    @csrf
                                                    <input type="hidden" name="product_id" value="{{ $dish->id }}">
                                                    <input type="hidden" name="variant_id" value="{{ $dish->variants->first()?->id }}">
                                                    <input type="hidden" name="quantity" value="1">
                                                    <button type="submit" 
                                                            class="w-full py-1.5 rounded-xl bg-white border-2 border-[#ea580c] text-[#ea580c] hover:bg-[#ea580c] hover:text-white text-xs font-black text-center shadow-md transition active:scale-95 cursor-pointer">
                                                        + ADD
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </section>
                @endforeach
            </div>
        @endif

        <!-- 5. DOMINO'S / SWIGGY STYLE CUSTOMIZATION MODAL -->
        <div x-show="customizerOpen" 
             x-cloak
             class="fixed inset-0 z-50 overflow-y-auto" 
             aria-labelledby="modal-title" 
             role="dialog" 
             aria-modal="true">
            <div class="flex items-center justify-center min-h-screen p-4 text-center sm:p-0">
                <!-- Overlay Backdrop -->
                <div class="fixed inset-0 bg-stone-950/70 backdrop-blur-xs transition-opacity" 
                     @click="customizerOpen = false"></div>

                <!-- Modal Panel -->
                <div class="relative inline-block w-full max-w-lg bg-white rounded-3xl text-left overflow-hidden shadow-2xl transform transition-all my-8 z-10 border border-stone-200">
                    
                    <!-- Modal Header with Dish Image -->
                    <div class="relative h-40 bg-stone-900 overflow-hidden">
                        <template x-if="activeDish">
                            <img :src="activeDish.image" :alt="activeDish.title" class="w-full h-full object-cover opacity-80">
                        </template>
                        <div class="absolute inset-0 bg-gradient-to-t from-stone-950 via-stone-950/40 to-transparent"></div>
                        <button type="button" 
                                @click="customizerOpen = false"
                                class="absolute top-3 right-3 w-8 h-8 rounded-full bg-white/90 text-stone-800 hover:bg-white flex items-center justify-center font-bold text-sm shadow-md transition cursor-pointer">
                            ✕
                        </button>
                        <div class="absolute bottom-3 left-4 right-4">
                            <div class="text-xs font-bold text-amber-400">Customise your order</div>
                            <h3 class="text-lg font-black text-white truncate" x-text="activeDish?.title"></h3>
                        </div>
                    </div>

                    <!-- Modal Body -->
                    <div class="p-5 max-h-[60vh] overflow-y-auto space-y-5">
                        
                        <!-- Portion Sizes / Variants -->
                        <template x-if="activeDish && activeDish.variants && activeDish.variants.length > 1">
                            <div>
                                <h4 class="text-xs font-black uppercase tracking-wider text-stone-900 mb-2 flex items-center justify-between">
                                    <span>Choose Portion Size</span>
                                    <span class="text-[10px] text-stone-500 font-semibold">Required</span>
                                </h4>
                                <div class="space-y-2">
                                    <template x-for="v in activeDish.variants" :key="v.id">
                                        <label class="flex items-center justify-between p-3 rounded-2xl border transition cursor-pointer"
                                               :class="selectedVariantId === v.id ? 'border-[#ea580c] bg-orange-50/50' : 'border-stone-200 hover:bg-stone-50'">
                                            <div class="flex items-center gap-3">
                                                <input type="radio" 
                                                       name="variant_option" 
                                                       :value="v.id" 
                                                       x-model="selectedVariantId" 
                                                       class="text-[#ea580c] focus:ring-[#ea580c]">
                                                <span class="text-xs font-bold text-stone-900" x-text="v.title"></span>
                                            </div>
                                            <span class="text-xs font-black text-stone-950" x-text="'₹' + v.price.toFixed(2)"></span>
                                        </label>
                                    </template>
                                </div>
                            </div>
                        </template>

                        <!-- Add-ons / Modifiers -->
                        <template x-if="activeDish && activeDish.modifiers && activeDish.modifiers.length > 0">
                            <div>
                                <h4 class="text-xs font-black uppercase tracking-wider text-stone-900 mb-2 flex items-center justify-between">
                                    <span>Add Extras & Sides</span>
                                    <span class="text-[10px] text-stone-500 font-semibold">Optional</span>
                                </h4>
                                <div class="space-y-2.5">
                                    <template x-for="m in activeDish.modifiers" :key="m.id">
                                        <label class="flex items-center justify-between p-3.5 rounded-2xl border transition-all cursor-pointer"
                                               :class="(selectedModifiers.includes(m.id) || selectedModifiers.includes(String(m.id))) ? 'border-[#ea580c] bg-orange-50/70 shadow-xs' : 'border-stone-200 hover:border-stone-300 hover:bg-stone-50/60'">
                                            <div class="flex items-center gap-3">
                                                <input type="checkbox" 
                                                       :value="m.id" 
                                                       x-model="selectedModifiers" 
                                                       class="w-4 h-4 text-[#ea580c] rounded-md focus:ring-[#ea580c] border-stone-300 cursor-pointer">
                                                <span class="text-xs font-bold text-stone-800" x-text="m.title"></span>
                                            </div>
                                            <span class="text-xs font-black text-stone-900" x-text="'+₹' + Number(m.price).toFixed(2)"></span>
                                        </label>
                                    </template>
                                </div>
                            </div>
                        </template>

                        <!-- Special Kitchen Instructions -->
                        <div>
                            <h4 class="text-xs font-black uppercase tracking-wider text-stone-900 mb-1">
                                Kitchen Cooking Instructions
                            </h4>
                            <p class="text-[11px] text-stone-500 mb-2">e.g. Less spicy, send extra green chutney, no plastic cutlery</p>
                            <input type="text" 
                                   x-model="kitchenNote" 
                                   placeholder="Add special cooking request..." 
                                   class="w-full px-3.5 py-2 text-xs rounded-xl bg-stone-100 border border-stone-200 focus:bg-white focus:border-[#ea580c] outline-hidden font-medium">
                        </div>

                    </div>

                    <!-- Modal Footer with Calculated Total -->
                    <div class="p-4 bg-stone-50 border-t border-stone-200 flex items-center justify-between gap-4">
                        <!-- Quantity Counter -->
                        <div class="flex items-center gap-2 p-1 rounded-xl bg-white border border-stone-200 shadow-xs">
                            <button type="button" 
                                    @click="if (itemQuantity > 1) itemQuantity--" 
                                    class="w-7 h-7 rounded-lg bg-stone-100 hover:bg-stone-200 text-stone-700 flex items-center justify-center font-black text-xs cursor-pointer">
                                −
                            </button>
                            <span class="w-6 text-center text-xs font-black text-stone-900" x-text="itemQuantity"></span>
                            <button type="button" 
                                    @click="itemQuantity++" 
                                    class="w-7 h-7 rounded-lg bg-orange-50 hover:bg-orange-100 text-[#ea580c] flex items-center justify-center font-black text-xs cursor-pointer">
                                +
                            </button>
                        </div>

                        <!-- Add to Bag CTA -->
                        <button type="button" 
                                @click="submitCustomizedItem()" 
                                :disabled="addingCustomized"
                                class="flex-1 py-3 px-4 rounded-2xl bg-gradient-to-r from-[#ea580c] to-amber-600 hover:opacity-95 text-white text-xs font-black transition shadow-lg shadow-orange-500/25 flex items-center justify-between cursor-pointer">
                            <span x-text="addingCustomized ? 'Adding to bag...' : 'Add Item to Bag'"></span>
                            <span class="text-white text-xs font-black" x-text="'₹' + calculatedCustomPrice.toFixed(2)"></span>
                        </button>
                    </div>

                </div>
            </div>
        </div>

    </div>

    @push('scripts')
    <script>
    function restaurantMenu(config) {
        return {
            restaurantId: config.restaurantId,
            restaurantName: config.restaurantName,
            deliveryFee: config.deliveryFee,
            isOpen: config.isOpen,
            selectedCategory: 'all',
            vegOnly: false,
            searchQuery: '',
            
            // Customizer Modal State
            customizerOpen: false,
            activeDish: null,
            selectedVariantId: null,
            selectedModifiers: [],
            kitchenNote: '',
            itemQuantity: 1,
            addingCustomized: false,

            shouldShowCategory(catSlug) {
                if (this.selectedCategory === 'all') return true;
                return this.selectedCategory === catSlug;
            },

            shouldShowDish(titleLower, isVeg) {
                if (this.vegOnly && !isVeg) return false;
                if (this.searchQuery && !titleLower.includes(this.searchQuery.toLowerCase())) return false;
                return true;
            },

            openCustomizer(dish) {
                this.activeDish = dish;
                this.selectedVariantId = dish.variants && dish.variants.length > 0 ? dish.variants[0].id : null;
                this.selectedModifiers = [];
                this.kitchenNote = '';
                this.itemQuantity = 1;
                this.customizerOpen = true;
            },

            get calculatedCustomPrice() {
                if (!this.activeDish) return 0;
                let unitPrice = Number(this.activeDish.base_price) || 0;
                if (this.selectedVariantId && this.activeDish.variants) {
                    const foundV = this.activeDish.variants.find(v => v.id == this.selectedVariantId);
                    if (foundV && Number(foundV.price) > 0) unitPrice = Number(foundV.price);
                }
                if (this.selectedModifiers.length > 0 && this.activeDish.modifiers) {
                    this.selectedModifiers.forEach(modId => {
                        const foundM = this.activeDish.modifiers.find(m => m.id == modId);
                        if (foundM && Number(foundM.price) > 0) unitPrice += Number(foundM.price);
                    });
                }
                return unitPrice * (this.itemQuantity || 1);
            },

            async submitCustomizedItem() {
                if (!this.activeDish) return;
                this.addingCustomized = true;

                const formData = new FormData();
                formData.append('product_id', this.activeDish.id);
                if (this.selectedVariantId) {
                    formData.append('variant_id', this.selectedVariantId);
                }
                formData.append('quantity', this.itemQuantity);
                if (this.selectedModifiers.length > 0) {
                    this.selectedModifiers.forEach(id => {
                        formData.append('selected_modifiers[]', id);
                        formData.append('modifiers[]', id);
                    });
                }
                if (this.kitchenNote) {
                    formData.append('instructions', this.kitchenNote);
                }

                try {
                    const res = await fetch('/cart/add', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'Accept': 'application/json'
                        },
                        credentials: 'same-origin',
                        body: formData
                    });
                    const data = await res.json();
                    if (data.success || data.cart_count) {
                        this.customizerOpen = false;
                        window.dispatchEvent(new CustomEvent('cart-updated', { detail: data }));
                        window.dispatchEvent(new CustomEvent('cart-count-updated', { detail: { count: data.cart_count } }));
                        window.dispatchEvent(new CustomEvent('open-mini-cart'));
                        if (window.showToast) window.showToast('Item customized & added to your bag! 🛍️');
                    } else {
                        if (window.showToast) window.showToast(data.message || 'Could not add item.', false);
                        else alert(data.message || 'Could not add item.');
                    }
                } catch(e) {
                    console.error(e);
                    if (window.showToast) window.showToast('Error adding item. Please try again.', false);
                    else alert('Error adding customized item. Please try again.');
                } finally {
                    this.addingCustomized = false;
                }
            }
        };
    }

    // Direct Quick Add Helper
    window.k71AddToCart = async function(form) {
        const formData = new FormData(form);
        const submitBtn = form.querySelector('button[type="submit"]');
        const originalText = submitBtn ? submitBtn.innerHTML : '+ ADD';
        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.innerHTML = 'Adding...';
        }

        try {
            const res = await fetch('/cart/add', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                },
                credentials: 'same-origin',
                body: formData
            });
            const data = await res.json();
            if (data.success || data.cart_count) {
                window.dispatchEvent(new CustomEvent('cart-updated', { detail: data }));
                window.dispatchEvent(new CustomEvent('cart-count-updated', { detail: { count: data.cart_count } }));
                window.dispatchEvent(new CustomEvent('open-mini-cart'));
                if (window.showToast) {
                    window.showToast('Item added to your bag! 🛍️');
                }
                if (submitBtn) {
                    submitBtn.classList.remove('bg-white', 'text-[#ea580c]', 'border-[#ea580c]');
                    submitBtn.classList.add('bg-emerald-600', 'text-white', 'border-emerald-600');
                    submitBtn.innerHTML = '✓ ADDED';
                    setTimeout(() => {
                        submitBtn.classList.remove('bg-emerald-600', 'text-white', 'border-emerald-600');
                        submitBtn.classList.add('bg-white', 'text-[#ea580c]', 'border-[#ea580c]');
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = originalText;
                    }, 1800);
                }
            } else {
                if (window.showToast) {
                    window.showToast(data.message || 'Could not add item.', false);
                } else {
                    alert(data.message || 'Could not add item.');
                }
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalText;
                }
            }
        } catch(e) {
            console.error(e);
            if (window.showToast) {
                window.showToast('Error adding item. Please try again.', false);
            } else {
                alert('Error adding item. Please try again.');
            }
            if (submitBtn) {
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
            }
        }
    };
    </script>
    @endpush
</x-layouts.food>
