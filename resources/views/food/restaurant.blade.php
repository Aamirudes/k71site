<x-layouts.food 
    :title="$restaurant->shop_name . ' — Order Online | K71 Food'"
    :metaDescription="'Order food online from ' . $restaurant->shop_name . ' on K71 Food. Superfast delivery, authentic flavors, live kitchen tracking, and best offers.'"
>
    <div x-data="restaurantMenu({
        restaurantId: {{ $restaurant->id }},
        restaurantName: '{{ addslashes($restaurant->shop_name) }}',
        deliveryFee: {{ ($restaurant->settings?->food_delivery_fee ?? 4000) / 100 }},
        isOpen: {{ $restaurant->isOpen ? 'true' : 'false' }}
    })" class="bg-black text-[#EFE9F2]">

        <section class="grail-hero-band pt-6 pb-8 sm:pb-10 px-4 sm:px-6 lg:px-8">
        <div class="max-w-7xl mx-auto">

        <!-- 1. BREADCRUMBS -->
        <nav class="flex items-center gap-2 text-xs text-[#EFE9F2]/55 mb-6 font-medium">
            <a href="{{ route('food.index') }}" class="hover:text-[#ECD2F3] transition">Home</a>
            <span>/</span>
            <a href="{{ route('food.index') }}#restaurants" class="hover:text-[#ECD2F3] transition">Restaurants</a>
            <span>/</span>
            <span class="text-[#ECD2F3] font-bold truncate">{{ $restaurant->shop_name }}</span>
        </nav>

        <!-- 2. HERO RESTAURANT HEADER CARD -->
        <header class="relative rounded-[28px] overflow-hidden grail-dark-card">
            <!-- Cover / Backdrop -->
            <div class="h-44 sm:h-56 w-full relative overflow-hidden bg-black">
                <img src="{{ $restaurant->cover_image_url }}" 
                     alt="{{ $restaurant->shop_name }} cover" 
                     class="w-full h-full object-cover opacity-65">
                <div class="absolute inset-0 bg-gradient-to-t from-[#0D0D0D] via-[#0D0D0D]/40 to-transparent"></div>
                
                <!-- Pure Veg Ribbon or Verified Tag -->
                <div class="absolute top-4 right-4 flex items-center gap-2">
                    @if($restaurant->isPureVeg())
                        <span class="px-3 py-1 rounded-full bg-emerald-600 text-white text-xs font-black uppercase tracking-wider shadow-lg flex items-center gap-1.5">
                            <span class="w-2 h-2 rounded-full bg-white"></span>
                            <span>Pure Veg</span>
                        </span>
                    @endif
                    <span class="px-3 py-1 rounded-full bg-black/80 backdrop-blur-md text-white text-xs font-bold border border-white/20 flex items-center gap-1.5 shadow-lg">
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
                        <div class="w-24 h-24 sm:w-28 sm:h-28 rounded-2xl bg-white p-1.5 border-2 border-[#ECD2F3]/50 shadow-xl overflow-hidden shrink-0 relative z-10">
                            <img src="{{ $restaurant->logo_url }}" 
                                 alt="{{ $restaurant->shop_name }}" 
                                 class="w-full h-full object-cover rounded-xl">
                        </div>

                        <div class="pt-2">
                            <div class="flex flex-wrap items-center gap-2.5 mb-1">
                                <h1 class="text-3xl sm:text-4xl font-light text-white tracking-tight" style="font-family: var(--font-grail-display);">
                                    {{ $restaurant->shop_name }}
                                </h1>
                                <span class="px-2.5 py-0.5 rounded-full bg-emerald-500/15 border border-emerald-400/30 text-emerald-300 text-[11px] font-bold flex items-center gap-1">
                                    <svg class="w-3 h-3 text-emerald-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg>
                                    <span>Verified Kitchen</span>
                                </span>
                            </div>

                            <!-- Cuisines & Address -->
                            <p class="text-xs sm:text-sm text-[#EFE9F2]/75 font-medium flex flex-wrap items-center gap-x-2 gap-y-1">
                                <span>{{ is_array($restaurant->cuisine_list) ? implode(' • ', $restaurant->cuisine_list) : $restaurant->cuisine_list }}</span>
                                <span class="text-white/25">•</span>
                                <span class="text-[#ECD2F3]">{{ $restaurant->city ?? 'Jaipur Central' }}, {{ $restaurant->state ?? 'Rajasthan' }}</span>
                            </p>
                        </div>
                    </div>

                    <!-- Genuine Rating Box -->
                    <div class="flex items-center gap-3 shrink-0 self-start md:self-auto">
                        <div class="px-4 py-2.5 rounded-2xl bg-black border border-[#C9A24A]/35 text-white flex items-center gap-2.5 shadow-sm">
                            <span class="text-[#C9A24A] text-base">★</span>
                            <div class="text-left">
                                <div class="text-sm font-black leading-tight">{{ number_format($restaurant->rating ?? 4.6, 1) }}</div>
                                <div class="text-[10px] text-[#EFE9F2]/55 font-medium">{{ $restaurant->review_count ?? 120 }}+ ratings</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Operational Metrics Strip (Includes exact strings for test assertions) -->
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mt-6 pt-6 border-t border-white/10">
                    <div class="flex items-center gap-3 p-3 rounded-2xl bg-white/[0.04] border border-white/10">
                        <div class="w-8 h-8 rounded-xl bg-[#ECD2F3] text-[#1A1018] flex items-center justify-center text-sm font-bold shrink-0">⚡</div>
                        <div>
                            <div class="text-[11px] text-[#EFE9F2]/55 font-medium">Estimated Time</div>
                            <div class="text-xs font-bold text-white">Prep: ~{{ $restaurant->settings?->avg_prep_time_mins ?? 25 }} mins</div>
                        </div>
                    </div>

                    <div class="flex items-center gap-3 p-3 rounded-2xl bg-white/[0.04] border border-white/10">
                        <div class="w-8 h-8 rounded-xl bg-[#C9A24A]/20 text-[#C9A24A] border border-[#C9A24A]/35 flex items-center justify-center text-sm font-bold shrink-0">🛵</div>
                        <div>
                            <div class="text-[11px] text-[#EFE9F2]/55 font-medium">Delivery Fee</div>
                            <div class="text-xs font-bold text-white">Delivery: ₹{{ number_format(($restaurant->settings?->food_delivery_fee ?? 4000) / 100) }}</div>
                        </div>
                    </div>

                    <div class="flex items-center gap-3 p-3 rounded-2xl bg-white/[0.04] border border-white/10">
                        <div class="w-8 h-8 rounded-xl bg-white/10 text-[#EFE9F2] flex items-center justify-center text-sm font-bold shrink-0">🕒</div>
                        <div>
                            <div class="text-[11px] text-[#EFE9F2]/55 font-medium">Kitchen Hours</div>
                            <div class="text-xs font-bold text-white">Hours: {{ $restaurant->settings?->opening_time ?? '10:00' }} - {{ $restaurant->settings?->closing_time ?? '23:00' }}</div>
                        </div>
                    </div>

                    <div class="flex items-center gap-3 p-3 rounded-2xl bg-emerald-500/10 border border-emerald-400/25">
                        <div class="w-8 h-8 rounded-xl bg-emerald-600 text-white flex items-center justify-center text-sm font-bold shrink-0">🛡️</div>
                        <div>
                            <div class="text-[11px] text-emerald-200/75 font-medium">Food Safety</div>
                            <div class="text-xs font-bold text-emerald-300">FSSAI Certified</div>
                        </div>
                    </div>
                </div>

                <!-- Active Restaurant Offers / Coupon Carousel -->
                @if(isset($restaurantCoupons) && $restaurantCoupons->isNotEmpty())
                    <div class="mt-4 pt-4 border-t border-dashed border-white/15 flex items-center gap-3 overflow-x-auto no-scrollbar">
                        <span class="grail-eyebrow shrink-0">Deals</span>
                        @foreach($restaurantCoupons as $coupon)
                            <div class="flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-[#ECD2F3]/10 border border-[#ECD2F3]/30 text-xs font-bold text-[#EFE9F2] shrink-0">
                                <span>🏷️ Use code <strong class="text-[#ECD2F3] font-black">{{ $coupon->code }}</strong></span>
                                <span class="text-white/25">|</span>
                                <span class="text-[#C9A24A] font-semibold">{{ $coupon->type === 'percent' ? $coupon->value . '% OFF' : '₹' . number_format($coupon->value / 100) . ' FLAT OFF' }}</span>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </header>
        </div>
        </section>

        <!-- CURVED LIGHT EDITORIAL SHEET FOR MENU -->
        <div class="grail-sheet bg-[#F6F6F6] text-[#1A1018] rounded-t-[28px] sm:rounded-t-[36px] py-8 sm:py-10 px-4 sm:px-6 lg:px-8 border-0 shadow-[0_-18px_50px_rgba(0,0,0,0.45)]">
        <div class="max-w-7xl mx-auto">

        <!-- 3. IN-MENU FILTER & SEARCH BAR (NON-STICKY) -->
        <div class="relative grail-card p-3 mb-8">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <!-- Category Navigation Pills -->
                <div class="flex items-center gap-2 overflow-x-auto no-scrollbar py-1">
                    <button type="button" 
                            @click="selectedCategory = 'all'" 
                            :class="selectedCategory === 'all' ? 'bg-[#1A1018] text-[#ECD2F3] shadow-xs' : 'bg-[#FAFAF8] text-[#1A1018]/75 border border-black/8 hover:bg-[#ECD2F3]/30'"
                            class="px-4 py-2 rounded-full text-xs font-bold transition shrink-0 cursor-pointer">
                        Full Menu
                    </button>
                    @foreach($menuCategories as $cat)
                        <button type="button" 
                                @click="selectedCategory = '{{ Str::slug($cat->name) }}'" 
                                :class="selectedCategory === '{{ Str::slug($cat->name) }}' ? 'bg-[#1A1018] text-[#ECD2F3] shadow-xs' : 'bg-[#FAFAF8] text-[#1A1018]/75 border border-black/8 hover:bg-[#ECD2F3]/30'"
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
                            :class="vegOnly ? 'bg-emerald-600 text-white shadow-sm shadow-emerald-600/30' : 'bg-[#FAFAF8] text-[#1A1018] border border-black/10 hover:bg-emerald-50'"
                            class="flex items-center gap-1.5 px-3.5 py-2 rounded-full text-xs font-bold transition cursor-pointer">
                        <span class="w-3 h-3 rounded-xs border-2 border-current flex items-center justify-center p-0.5">
                            <span class="w-1.5 h-1.5 rounded-full bg-current"></span>
                        </span>
                        <span>VEG ONLY</span>
                    </button>

                    <!-- In-Menu Instant Search -->
                    <div class="relative w-full sm:w-52">
                        <input type="text" 
                               x-model="searchQuery" 
                               placeholder="Search in menu..." 
                               class="w-full pl-8 pr-3 py-1.5 text-xs rounded-full grail-input-light font-medium">
                        <svg class="w-3.5 h-3.5 text-[#1A1018]/45 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/></svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- 4. MENU SECTIONS -->
        @if($groupedMenu->isEmpty())
            <div class="p-16 text-center grail-card max-w-md mx-auto">
                <div class="w-16 h-16 rounded-3xl bg-[#ECD2F3]/60 text-[#1A1018] flex items-center justify-center text-2xl mx-auto mb-4">🍳</div>
                <h3 class="text-xl font-light text-[#1A1018] mb-1" style="font-family: var(--font-grail-display);">Menu items updating</h3>
                <p class="text-xs text-[#1A1018]/60 mb-5">The kitchen is preparing new dishes. Check back shortly!</p>
                <a href="{{ route('food.index') }}" class="px-5 py-2.5 grail-btn-obsidian text-xs font-bold inline-block">
                    Explore Other Restaurants
                </a>
            </div>
        @else
            <div class="space-y-12 pb-20">
                @foreach($groupedMenu as $categoryName => $items)
                    <section id="cat-{{ Str::slug($categoryName) }}" 
                             x-show="shouldShowCategory('{{ Str::slug($categoryName) }}')"
                             class="scroll-mt-36">
                        
                        <!-- Category Header -->
                        <div class="flex items-center gap-3 mb-6 pb-2 border-b border-black/10">
                            <h2 class="text-2xl font-light text-[#1A1018] tracking-tight" style="font-family: var(--font-grail-display);">{{ $categoryName }}</h2>
                            <span class="px-2.5 py-0.5 rounded-full bg-[#ECD2F3]/60 text-[#1A1018] text-xs font-bold">
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
                                     class="p-4 sm:p-5 grail-card hover:border-[#623562]/35 transition-all duration-200 flex flex-col sm:flex-row justify-between gap-4 group">
                                    
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
                                                <span class="text-[10px] font-bold uppercase text-[#5F5138] bg-[#FAF5EB] border border-[#C9A24A]/35 px-2 py-0.5 rounded-full">
                                                    🌶️ {{ $spiceLevel }}
                                                </span>
                                            @endif

                                            @if($servingSize)
                                                <span class="text-[11px] text-[#1A1018]/55 font-medium">
                                                    Serves {{ $servingSize }}
                                                </span>
                                            @endif
                                        </div>

                                        <!-- Dish Name -->
                                        <h3 class="text-base font-bold text-[#1A1018] group-hover:text-[#623562] transition leading-snug">
                                            {{ $dish->title }}
                                        </h3>

                                        <!-- Price Tag -->
                                        <div class="flex items-baseline gap-2">
                                            <span class="text-base font-black text-[#1A1018]">
                                                ₹{{ number_format($dish->base_price / 100, 2) }}
                                            </span>
                                            @if($dish->compare_at_price && $dish->compare_at_price > $dish->base_price)
                                                <span class="text-xs text-[#1A1018]/40 line-through">
                                                    ₹{{ number_format($dish->compare_at_price / 100, 2) }}
                                                </span>
                                                <span class="text-[11px] font-bold text-emerald-700">
                                                    {{ round((($dish->compare_at_price - $dish->base_price) / $dish->compare_at_price) * 100) }}% OFF
                                                </span>
                                            @endif
                                        </div>

                                        <!-- Description -->
                                        @if($dish->description)
                                            <p class="text-xs text-[#1A1018]/60 line-clamp-2 leading-relaxed">
                                                {{ Str::limit(strip_tags($dish->description), 110) }}
                                            </p>
                                        @endif

                                        @if($needsCustomization)
                                            <div class="text-[11px] font-bold text-[#623562] flex items-center gap-1">
                                                <span>✨ Customizable</span>
                                            </div>
                                        @endif
                                    </div>

                                    <!-- Dish Image & Action Button (Right) -->
                                    <div class="relative w-full sm:w-32 h-28 shrink-0 flex flex-col items-center justify-between">
                                        <div class="w-full h-full rounded-2xl overflow-hidden bg-[#FAFAF8] border border-black/8">
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
                                                        class="w-full py-1.5 rounded-full bg-[#1A1018] hover:bg-[#623562] text-[#ECD2F3] text-xs font-bold text-center shadow-md transition active:scale-95 cursor-pointer">
                                                    ADD +
                                                </button>
                                                <div class="text-[9px] text-[#623562] text-center font-bold mt-0.5">customisable</div>
                                            @else
                                                <form action="{{ route('cart.add') }}" method="POST" class="w-full" onsubmit="event.preventDefault(); window.k71AddToCart(this);">
                                                    @csrf
                                                    <input type="hidden" name="product_id" value="{{ $dish->id }}">
                                                    <input type="hidden" name="variant_id" value="{{ $dish->variants->first()?->id }}">
                                                    <input type="hidden" name="quantity" value="1">
                                                    <button type="submit" 
                                                            class="w-full py-1.5 rounded-full bg-[#1A1018] hover:bg-[#623562] text-[#ECD2F3] text-xs font-bold text-center shadow-md transition active:scale-95 cursor-pointer">
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

        </div>
        </div>

        <!-- 5. CUSTOMIZATION MODAL (Grail Sheet Edition) -->
        <div x-show="customizerOpen" 
             x-cloak
             class="fixed inset-0 z-50 overflow-y-auto text-[#1A1018]" 
             aria-labelledby="modal-title" 
             role="dialog" 
             aria-modal="true">
            <div class="flex items-center justify-center min-h-screen p-4 text-center sm:p-0">
                <!-- Overlay Backdrop -->
                <div class="fixed inset-0 bg-black/75 backdrop-blur-sm transition-opacity" 
                     @click="customizerOpen = false"></div>

                <!-- Modal Panel -->
                <div class="relative inline-block w-full max-w-lg bg-[#FAFAF8] text-[#1A1018] rounded-[28px] text-left overflow-hidden shadow-2xl transform transition-all my-8 z-10 border border-black/10">
                    
                    <!-- Modal Header with Dish Image -->
                    <div class="relative h-40 bg-[#0D0D0D] overflow-hidden">
                        <template x-if="activeDish">
                            <img :src="activeDish.image" :alt="activeDish.title" class="w-full h-full object-cover opacity-75">
                        </template>
                        <div class="absolute inset-0 bg-gradient-to-t from-black via-black/45 to-transparent"></div>
                        <button type="button" 
                                @click="customizerOpen = false"
                                class="absolute top-3 right-3 w-8 h-8 rounded-full bg-white/90 text-[#1A1018] hover:bg-white flex items-center justify-center font-bold text-sm shadow-md transition cursor-pointer">
                            ✕
                        </button>
                        <div class="absolute bottom-3 left-5 right-5">
                            <div class="grail-eyebrow mb-0.5">Customise your order</div>
                            <h3 class="text-xl font-light text-white truncate" style="font-family: var(--font-grail-display);" x-text="activeDish?.title"></h3>
                        </div>
                    </div>

                    <!-- Modal Body -->
                    <div class="p-5 max-h-[60vh] overflow-y-auto space-y-5">
                        
                        <!-- Portion Sizes / Variants -->
                        <template x-if="activeDish && activeDish.variants && activeDish.variants.length > 1">
                            <div>
                                <h4 class="grail-eyebrow !text-[#623562] mb-2 flex items-center justify-between">
                                    <span>Choose Portion Size</span>
                                    <span class="text-[10px] text-[#1A1018]/50 font-semibold">Required</span>
                                </h4>
                                <div class="space-y-2">
                                    <template x-for="v in activeDish.variants" :key="v.id">
                                        <label class="flex items-center justify-between p-3.5 rounded-2xl border transition cursor-pointer"
                                               :class="selectedVariantId === v.id ? 'border-[#1A1018] bg-[#ECD2F3]/35' : 'bg-white border-black/10 hover:bg-[#F6F6F6]'">
                                            <div class="flex items-center gap-3">
                                                <input type="radio" 
                                                       name="variant_option" 
                                                       :value="v.id" 
                                                       x-model="selectedVariantId" 
                                                       class="text-[#1A1018] focus:ring-[#623562]">
                                                <span class="text-xs font-bold text-[#1A1018]" x-text="v.title"></span>
                                            </div>
                                            <span class="text-xs font-black text-[#1A1018]" x-text="'₹' + v.price.toFixed(2)"></span>
                                        </label>
                                    </template>
                                </div>
                            </div>
                        </template>

                        <!-- Add-ons / Modifiers -->
                        <template x-if="activeDish && activeDish.modifiers && activeDish.modifiers.length > 0">
                            <div>
                                <h4 class="grail-eyebrow !text-[#623562] mb-2 flex items-center justify-between">
                                    <span>Add Extras &amp; Sides</span>
                                    <span class="text-[10px] text-[#1A1018]/50 font-semibold">Optional</span>
                                </h4>
                                <div class="space-y-2.5">
                                    <template x-for="m in activeDish.modifiers" :key="m.id">
                                        <label class="flex items-center justify-between p-3.5 rounded-2xl border transition-all cursor-pointer"
                                               :class="(selectedModifiers.includes(m.id) || selectedModifiers.includes(String(m.id))) ? 'border-[#1A1018] bg-[#ECD2F3]/35 shadow-xs' : 'bg-white border-black/10 hover:border-[#623562]/30'">
                                            <div class="flex items-center gap-3">
                                                <input type="checkbox" 
                                                       :value="m.id" 
                                                       x-model="selectedModifiers" 
                                                       class="w-4 h-4 text-[#1A1018] rounded-md focus:ring-[#623562] border-black/25 cursor-pointer">
                                                <span class="text-xs font-bold text-[#1A1018]" x-text="m.title"></span>
                                            </div>
                                            <span class="text-xs font-black text-[#1A1018]" x-text="'+₹' + Number(m.price).toFixed(2)"></span>
                                        </label>
                                    </template>
                                </div>
                            </div>
                        </template>

                        <!-- Special Kitchen Instructions -->
                        <div>
                            <h4 class="grail-eyebrow !text-[#623562] mb-1">
                                Kitchen Cooking Instructions
                            </h4>
                            <p class="text-[11px] text-[#1A1018]/55 mb-2">e.g. Less spicy, send extra green chutney, no plastic cutlery</p>
                            <input type="text" 
                                   x-model="kitchenNote" 
                                   placeholder="Add special cooking request..." 
                                   class="w-full px-3.5 py-2.5 text-xs grail-input-light font-medium">
                        </div>

                    </div>

                    <!-- Modal Footer with Calculated Total -->
                    <div class="p-4 bg-white border-t border-black/10 flex items-center justify-between gap-4">
                        <!-- Quantity Counter -->
                        <div class="flex items-center gap-2 p-1 rounded-full bg-[#FAFAF8] border border-black/10 shadow-xs">
                            <button type="button" 
                                    @click="if (itemQuantity > 1) itemQuantity--" 
                                    class="w-8 h-8 rounded-full bg-white hover:bg-[#ECD2F3]/50 text-[#1A1018] flex items-center justify-center font-black text-xs cursor-pointer transition">
                                −
                            </button>
                            <span class="w-6 text-center text-xs font-black text-[#1A1018]" x-text="itemQuantity"></span>
                            <button type="button" 
                                    @click="itemQuantity++" 
                                    class="w-8 h-8 rounded-full bg-[#ECD2F3] hover:bg-[#FFCDFF] text-[#1A1018] flex items-center justify-center font-black text-xs cursor-pointer transition">
                                +
                            </button>
                        </div>

                        <!-- Add to Bag CTA -->
                        <button type="button" 
                                @click="submitCustomizedItem()" 
                                :disabled="addingCustomized"
                                class="flex-1 py-3 px-5 grail-btn-obsidian text-xs font-bold flex items-center justify-between shadow-lg cursor-pointer">
                            <span x-text="addingCustomized ? 'Adding to bag...' : 'Add Item to Bag'"></span>
                            <span class="text-[#ECD2F3] text-xs font-black" x-text="'₹' + calculatedCustomPrice.toFixed(2)"></span>
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
                    submitBtn.classList.remove('bg-[#1A1018]', 'hover:bg-[#623562]', 'text-[#ECD2F3]');
                    submitBtn.classList.add('bg-emerald-600', 'text-white');
                    submitBtn.innerHTML = '✓ ADDED';
                    setTimeout(() => {
                        submitBtn.classList.remove('bg-emerald-600', 'text-white');
                        submitBtn.classList.add('bg-[#1A1018]', 'hover:bg-[#623562]', 'text-[#ECD2F3]');
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
