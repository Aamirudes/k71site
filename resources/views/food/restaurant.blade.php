<x-layouts.food 
    :title="$restaurant->shop_name . ' — Menu & Online Ordering'"
    :metaDescription="'Order dishes from ' . $restaurant->shop_name . ' on K71 Food. Real menu pricing, custom portions & add-ons, and live order tracking.'"
>
    @php
        $opStatus = $operationalStatus ?? ['is_open' => (bool) $restaurant->isOpen, 'accepting_orders' => (bool) $restaurant->isOpen, 'label' => ($restaurant->isOpen ? 'Accepting Orders' : 'Currently Closed'), 'reason' => null, 'hours_text' => (($restaurant->settings?->opening_time ?? '10:00') . ' - ' . ($restaurant->settings?->closing_time ?? '23:00'))];
        $acceptingOrders = (bool) ($opStatus['accepting_orders'] ?? false);
        $coverImg = $restaurant->cover_image_url ?: asset('images/brand-banner.jpg');
        $logoImg = $restaurant->logo_url;
        $realRating = $restaurant->food_rating;
        $realReviewCount = (int) ($restaurant->food_review_count_value ?? 0);
        $deliveryFeeRupees = ($restaurant->settings?->food_delivery_fee ?? 0) / 100;
        $packagingFeeRupees = ($restaurant->settings?->food_packaging_fee ?? 0) / 100;
        $freeDeliveryAboveRupees = $restaurant->settings?->food_free_delivery_above ? ($restaurant->settings->food_free_delivery_above / 100) : null;
        $minOrderRupees = $restaurant->settings?->min_food_order_amount ? ($restaurant->settings->min_food_order_amount / 100) : null;
        $deliveryMode = $restaurant->settings?->food_delivery_mode ?? 'both';
        $deliveryModeLabel = match($deliveryMode) {
            'delivery' => 'Doorstep Delivery Only',
            'takeaway' => 'Self-Pickup Only',
            default => 'Delivery & Takeaway',
        };
        $activeLocLabel = $selectedLocation['label'] ?? null;
    @endphp

    <div x-data="restaurantMenu({
        restaurantId: {{ $restaurant->id }},
        restaurantName: @js($restaurant->shop_name),
        deliveryFee: {{ $deliveryFeeRupees }},
        acceptingOrders: {{ $acceptingOrders ? 'true' : 'false' }}
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

        <!-- Operational / Delivery Zone Banners -->
        @if(session('success'))
            <div class="mb-4 p-4 rounded-2xl bg-emerald-950/90 border border-emerald-400/40 text-emerald-200 text-xs font-semibold">
                {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div class="mb-4 p-4 rounded-2xl bg-rose-950/90 border border-rose-400/40 text-rose-200 text-xs font-semibold">
                {{ session('error') }}
            </div>
        @endif

        @if(!$acceptingOrders)
            <div class="mb-4 p-4 rounded-2xl bg-rose-950/85 border border-rose-500/45 text-rose-100 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                <div class="flex items-center gap-2.5 text-xs">
                    <span class="text-base">⏸</span>
                    <div>
                        <strong class="uppercase tracking-wider">{{ $opStatus['label'] ?? 'Orders Paused' }}:</strong>
                        <span>{{ $opStatus['reason'] ?: 'This kitchen is currently not accepting new orders.' }}</span>
                    </div>
                </div>
                <a href="{{ route('food.index') }}" class="text-xs font-bold text-[#ECD2F3] hover:underline shrink-0">Browse Open Kitchens →</a>
            </div>
        @endif

        @if(isset($isServiceable) && !$isServiceable && !empty($activeLocLabel))
            <div class="mb-4 p-4 rounded-2xl bg-amber-950/80 border border-amber-400/40 text-amber-100 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                <div class="flex items-center gap-2.5 text-xs">
                    <span class="text-base">📍</span>
                    <span>
                        Your selected location (<strong>{{ $activeLocLabel }}</strong>) is outside {{ $restaurant->shop_name }}'s delivery zone.
                        @if(in_array($deliveryMode, ['both', 'takeaway']))
                            You can still place a <strong>Self-Pickup</strong> order at checkout.
                        @endif
                    </span>
                </div>
                <button type="button" @click="locationModalOpen = true" class="text-xs font-bold text-[#ECD2F3] hover:underline shrink-0 cursor-pointer">
                    Change Location
                </button>
            </div>
        @endif

        <!-- 2. HERO RESTAURANT HEADER CARD -->
        <header class="relative rounded-[28px] overflow-hidden grail-dark-card">
            <!-- Cover / Backdrop -->
            <div class="h-44 sm:h-56 w-full relative overflow-hidden bg-black">
                <img src="{{ $coverImg }}" 
                     alt="{{ $restaurant->shop_name }} cover" 
                     class="w-full h-full object-cover opacity-65">
                <div class="absolute inset-0 bg-gradient-to-t from-[#0D0D0D] via-[#0D0D0D]/40 to-transparent"></div>
                
                <!-- Top Right Badges -->
                <div class="absolute top-4 right-4 flex items-center gap-2 flex-wrap justify-end">
                    @if($restaurant->isPureVeg())
                        <span class="px-3 py-1 rounded-full bg-emerald-600 text-white text-xs font-black uppercase tracking-wider shadow-lg flex items-center gap-1.5">
                            <span class="w-2 h-2 rounded-full bg-white"></span>
                            <span>Pure Veg</span>
                        </span>
                    @endif
                    <span class="px-3 py-1 rounded-full bg-black/80 backdrop-blur-md text-white text-xs font-bold border border-white/20 flex items-center gap-1.5 shadow-lg">
                        <span class="w-2 h-2 rounded-full {{ $acceptingOrders ? 'bg-emerald-400 animate-pulse' : 'bg-rose-500' }}"></span>
                        <span>{{ $opStatus['label'] ?? ($acceptingOrders ? 'Accepting Orders' : 'Currently Closed') }}</span>
                    </span>
                </div>
            </div>

            <!-- Restaurant Identity & Meta -->
            <div class="p-6 sm:p-8 relative">
                <div class="flex flex-col md:flex-row md:items-end justify-between gap-6 -mt-16 sm:-mt-20">
                    <div class="flex items-start sm:items-end gap-5">
                        <!-- Restaurant Logo Avatar -->
                        <div class="w-24 h-24 sm:w-28 sm:h-28 rounded-2xl bg-white p-1.5 border-2 border-[#ECD2F3]/50 shadow-xl overflow-hidden shrink-0 relative z-10 flex items-center justify-center">
                            @if($logoImg)
                                <img src="{{ $logoImg }}" 
                                     alt="{{ $restaurant->shop_name }}" 
                                     class="w-full h-full object-cover rounded-xl">
                            @else
                                <div class="w-full h-full rounded-xl bg-[#1A1018] text-[#ECD2F3] font-black text-2xl flex items-center justify-center">
                                    {{ strtoupper(substr($restaurant->shop_name, 0, 2)) }}
                                </div>
                            @endif
                        </div>

                        <div class="pt-2">
                            <div class="flex flex-wrap items-center gap-2.5 mb-1">
                                <h1 class="text-3xl sm:text-4xl font-light text-white tracking-tight" style="font-family: var(--font-grail-display);">
                                    {{ $restaurant->shop_name }}
                                </h1>
                                @if($restaurant->isApprovedOrActive())
                                    <span class="px-2.5 py-0.5 rounded-full bg-white/10 border border-white/20 text-[#ECD2F3] text-[11px] font-bold">
                                        Partner Kitchen
                                    </span>
                                @endif
                            </div>

                            <!-- Cuisines & Address -->
                            <p class="text-xs sm:text-sm text-[#EFE9F2]/75 font-medium flex flex-wrap items-center gap-x-2 gap-y-1">
                                <span>{{ is_array($restaurant->cuisine_list) ? implode(' • ', $restaurant->cuisine_list) : $restaurant->cuisine_list }}</span>
                                @if($restaurant->city || $restaurant->state)
                                    <span class="text-white/25">•</span>
                                    <span class="text-[#ECD2F3]">{{ collect([$restaurant->address, $restaurant->city, $restaurant->state])->filter()->implode(', ') }}</span>
                                @endif
                            </p>
                        </div>
                    </div>

                    <!-- Genuine Rating & Review Summary (Zero Fabricated Numbers) -->
                    <div class="flex items-center gap-3 shrink-0 self-start md:self-auto">
                        @if($realReviewCount > 0 && $realRating !== null)
                            <a href="#kitchen-reviews" class="px-4 py-2.5 rounded-2xl bg-black border border-[#C9A24A]/35 text-white flex items-center gap-2.5 shadow-sm hover:border-[#ECD2F3] transition">
                                <span class="text-[#C9A24A] text-base">★</span>
                                <div class="text-left">
                                    <div class="text-sm font-black leading-tight">{{ number_format($realRating, 1) }}</div>
                                    <div class="text-[10px] text-[#EFE9F2]/55 font-medium">{{ $realReviewCount }} verified {{ Str::plural('review', $realReviewCount) }}</div>
                                </div>
                            </a>
                        @else
                            <a href="#kitchen-reviews" class="px-4 py-2.5 rounded-2xl bg-black border border-white/15 text-white flex items-center gap-2 shadow-sm hover:border-[#ECD2F3]/50 transition">
                                <span class="text-[#ECD2F3] text-sm">☆</span>
                                <div class="text-left">
                                    <div class="text-xs font-bold leading-tight">New on K71</div>
                                    <div class="text-[10px] text-[#EFE9F2]/55 font-medium">No reviews yet</div>
                                </div>
                            </a>
                        @endif
                    </div>
                </div>

                <!-- Operational Metrics Strip (Preserves required test strings + honest fees/FSSAI) -->
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mt-6 pt-6 border-t border-white/10">
                    <div class="flex items-center gap-3 p-3 rounded-2xl bg-white/[0.04] border border-white/10">
                        <div class="w-8 h-8 rounded-xl bg-[#ECD2F3] text-[#1A1018] flex items-center justify-center text-sm font-bold shrink-0">⚡</div>
                        <div class="min-w-0">
                            <div class="text-[11px] text-[#EFE9F2]/55 font-medium">Estimated Prep</div>
                            <div class="text-xs font-bold text-white">Prep: ~{{ $restaurant->settings?->avg_prep_time_mins ?? 25 }} mins</div>
                        </div>
                    </div>

                    <div class="flex items-center gap-3 p-3 rounded-2xl bg-white/[0.04] border border-white/10">
                        <div class="w-8 h-8 rounded-xl bg-[#C9A24A]/20 text-[#C9A24A] border border-[#C9A24A]/35 flex items-center justify-center text-sm font-bold shrink-0">🛵</div>
                        <div class="min-w-0">
                            <div class="text-[11px] text-[#EFE9F2]/55 font-medium">
                                Delivery Fee{{ $freeDeliveryAboveRupees ? ' (Free ≥ ₹' . number_format($freeDeliveryAboveRupees, 0) . ')' : '' }}
                            </div>
                            <div class="text-xs font-bold text-white">Delivery: ₹{{ number_format($deliveryFeeRupees, 0) }}</div>
                        </div>
                    </div>

                    <div class="flex items-center gap-3 p-3 rounded-2xl bg-white/[0.04] border border-white/10">
                        <div class="w-8 h-8 rounded-xl bg-white/10 text-[#EFE9F2] flex items-center justify-center text-sm font-bold shrink-0">🕒</div>
                        <div class="min-w-0">
                            <div class="text-[11px] text-[#EFE9F2]/55 font-medium">Kitchen Hours</div>
                            <div class="text-xs font-bold text-white">Hours: {{ $restaurant->settings?->opening_time ?? '10:00' }} - {{ $restaurant->settings?->closing_time ?? '23:00' }}</div>
                        </div>
                    </div>

                    @if($restaurant->isFssaiVerified())
                        <div class="flex items-center gap-3 p-3 rounded-2xl bg-emerald-500/10 border border-emerald-400/25">
                            <div class="w-8 h-8 rounded-xl bg-emerald-600 text-white flex items-center justify-center text-sm font-bold shrink-0">🛡️</div>
                            <div class="min-w-0">
                                <div class="text-[11px] text-emerald-200/75 font-medium">FSSAI License Verified</div>
                                <div class="text-xs font-bold text-emerald-300 truncate">{{ $restaurant->settings->fssai_license_number }}</div>
                            </div>
                        </div>
                    @else
                        <div class="flex items-center gap-3 p-3 rounded-2xl bg-white/[0.04] border border-white/10">
                            <div class="w-8 h-8 rounded-xl bg-white/10 text-[#ECD2F3] flex items-center justify-center text-sm font-bold shrink-0">🥡</div>
                            <div class="min-w-0">
                                <div class="text-[11px] text-[#EFE9F2]/55 font-medium">Fulfillment</div>
                                <div class="text-xs font-bold text-white truncate">{{ $deliveryModeLabel }}</div>
                            </div>
                        </div>
                    @endif
                </div>

                @if($minOrderRupees || $packagingFeeRupees > 0)
                    <div class="mt-3 flex flex-wrap items-center gap-3 text-[11px] text-[#EFE9F2]/70">
                        @if($minOrderRupees)
                            <span class="px-3 py-1 rounded-full bg-white/5 border border-white/10">Min. Order: <strong class="text-white">₹{{ number_format($minOrderRupees, 0) }}</strong></span>
                        @endif
                        @if($packagingFeeRupees > 0)
                            <span class="px-3 py-1 rounded-full bg-white/5 border border-white/10">Kitchen Packaging Fee: <strong class="text-white">₹{{ number_format($packagingFeeRupees, 0) }}</strong></span>
                        @endif
                    </div>
                @endif

                <!-- Active Restaurant Offers / Coupon Carousel -->
                @if(isset($restaurantCoupons) && $restaurantCoupons->isNotEmpty())
                    <div class="mt-4 pt-4 border-t border-dashed border-white/15 flex items-center gap-3 overflow-x-auto no-scrollbar">
                        <span class="grail-eyebrow shrink-0">Offers</span>
                        @foreach($restaurantCoupons as $coupon)
                            @php
                                $isPct = in_array($coupon->type, ['percent', 'percentage'], true);
                            @endphp
                            <div class="flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-[#ECD2F3]/10 border border-[#ECD2F3]/30 text-xs font-bold text-[#EFE9F2] shrink-0">
                                <span>🏷️ Use code <strong class="text-[#ECD2F3] font-black select-all">{{ $coupon->code }}</strong></span>
                                <span class="text-white/25">|</span>
                                <span class="text-[#C9A24A] font-semibold">
                                    {{ $isPct ? $coupon->value . '% OFF' : '₹' . number_format($coupon->value / 100, 0) . ' FLAT OFF' }}
                                </span>
                                @if($coupon->min_cart_value)
                                    <span class="text-white/50 text-[10px]">(Min ₹{{ number_format($coupon->min_cart_value / 100, 0) }})</span>
                                @endif
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
        <div class="max-w-7xl mx-auto space-y-12 pb-20">

        <!-- 3. IN-MENU FILTER & SEARCH BAR -->
        <div class="relative grail-card p-3">
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
                <p class="text-xs text-[#1A1018]/60 mb-5">This kitchen is currently updating its dishes. Check back shortly!</p>
                <a href="{{ route('food.index') }}" class="px-5 py-2.5 grail-btn-obsidian text-xs font-bold inline-block">
                    Explore Other Restaurants
                </a>
            </div>
        @else
            <div class="space-y-12">
                @foreach($groupedMenu as $categoryName => $items)
                    <section id="cat-{{ Str::slug($categoryName) }}" 
                             x-show="shouldShowCategory('{{ Str::slug($categoryName) }}')"
                             class="scroll-mt-36">
                        
                        <!-- Category Header -->
                        <div class="flex items-center gap-3 mb-6 pb-2 border-b border-black/10">
                            <h2 class="text-2xl font-light text-[#1A1018] tracking-tight" style="font-family: var(--font-grail-display);">{{ $categoryName }}</h2>
                            <span class="px-2.5 py-0.5 rounded-full bg-[#ECD2F3]/60 text-[#1A1018] text-xs font-bold">
                                {{ $items->count() }} {{ Str::plural('item', $items->count()) }}
                            </span>
                        </div>

                        <!-- Dishes Grid -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            @foreach($items as $dish)
                                @php
                                    $isVeg = $dish->isVeg();
                                    $spiceLevel = $dish->spice_level;
                                    $servingSize = $dish->serving_size;
                                    $dishAvailable = $dish->isDishCurrentlyAvailable();
                                    $activeOptionGroups = $dish->optionGroups->where('is_active', true)->values();
                                    $ungroupedModifiers = $dish->modifiers->whereNull('option_group_id')->values();
                                    $hasVariants = $dish->variants->count() > 1;
                                    $hasOptionGroups = $activeOptionGroups->isNotEmpty();
                                    $hasLegacyModifiers = $ungroupedModifiers->isNotEmpty();
                                    $needsCustomization = $hasVariants || $hasOptionGroups || $hasLegacyModifiers;

                                    $dishJson = json_encode([
                                        'id' => $dish->id,
                                        'title' => $dish->title,
                                        'description' => $dish->description,
                                        'base_price' => (float) ($dish->base_price / 100),
                                        'image' => $dish->primary_image_url,
                                        'is_veg' => $isVeg,
                                        'variants' => $dish->variants->map(fn ($v) => [
                                            'id' => $v->id,
                                            'title' => $v->title ?: ($v->sku ?: 'Standard'),
                                            'price' => (float) $v->effective_price,
                                        ])->values(),
                                        'option_groups' => $activeOptionGroups->map(fn ($g) => [
                                            'id' => $g->id,
                                            'name' => $g->name,
                                            'selection_type' => $g->selection_type,
                                            'is_required' => (bool) $g->is_required,
                                            'min_selections' => $g->effectiveMinSelections(),
                                            'max_selections' => $g->effectiveMaxSelections(),
                                            'options' => $g->modifiers->map(fn ($m) => [
                                                'id' => $m->id,
                                                'title' => $m->modifier_name ?: ($m->title ?: 'Option'),
                                                'price' => (float) ($m->price_delta ?? 0),
                                                'is_available' => (bool) ($m->is_available ?? true),
                                                'is_default' => (bool) ($m->is_default ?? false),
                                            ])->values(),
                                        ])->values(),
                                        'modifiers' => $ungroupedModifiers->map(fn ($m) => [
                                            'id' => $m->id,
                                            'title' => $m->modifier_name ?: ($m->title ?: 'Add-on'),
                                            'group_name' => $m->group_name ?: 'Add Extras & Sides',
                                            'price' => (float) ($m->price_delta ?? $m->price ?? 0),
                                            'is_required' => (bool) $m->is_required,
                                            'is_available' => (bool) ($m->is_available ?? true),
                                            'is_default' => (bool) ($m->is_default ?? false),
                                        ])->values(),
                                    ]);
                                @endphp

                                @php
                                    $dishPricePaise = (int) ($dish->base_price ?? 0);
                                    $dishComparePaise = (int) ($dish->compare_at_price ?? 0);
                                    $dishPriceLabel = ($dishPricePaise % 100 === 0)
                                        ? number_format($dishPricePaise / 100, 0)
                                        : number_format($dishPricePaise / 100, 2);
                                    $dishCompareLabel = ($dishComparePaise % 100 === 0)
                                        ? number_format($dishComparePaise / 100, 0)
                                        : number_format($dishComparePaise / 100, 2);
                                    $dishDiscount = ($dishComparePaise > $dishPricePaise && $dishComparePaise > 0)
                                        ? (int) round((($dishComparePaise - $dishPricePaise) / $dishComparePaise) * 100)
                                        : 0;
                                    $dishRating = ((float) ($dish->avg_rating ?? 0) > 0 && (int) ($dish->review_count ?? 0) > 0)
                                        ? (float) $dish->avg_rating
                                        : null;
                                @endphp
                                <article x-show="shouldShowDish('{{ addslashes(strtolower($dish->title)) }}', {{ $isVeg ? 'true' : 'false' }})"
                                     class="p-4 sm:p-5 rounded-[22px] bg-white border border-black/8 hover:border-[#623562]/30 shadow-[0_8px_24px_-14px_rgba(26,16,24,0.12)] hover:shadow-[0_16px_36px_-14px_rgba(98,53,98,0.18)] transition-all duration-200 flex justify-between items-stretch gap-4 group {{ !$dishAvailable ? 'opacity-75 bg-[#FAFAF8]' : '' }}">
                                    
                                    <!-- Dish Details (Left) -->
                                    <div class="flex-1 min-w-0 flex flex-col justify-between gap-2">
                                        <div class="space-y-1.5">
                                            <!-- Dietary / Spice / Time Pills -->
                                            <div class="flex items-center gap-2 flex-wrap">
                                                @if($isVeg)
                                                    <span class="w-4 h-4 rounded-[3px] border-2 border-emerald-600 flex items-center justify-center shrink-0" title="{{ $dish->dietary_label_resolved }}">
                                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-600"></span>
                                                    </span>
                                                @else
                                                    <span class="w-4 h-4 rounded-[3px] border-2 border-rose-600 flex items-center justify-center shrink-0" title="{{ $dish->dietary_label_resolved }}">
                                                        <span class="w-0 h-0 border-l-[3.5px] border-l-transparent border-r-[3.5px] border-r-transparent border-b-[6px] border-b-rose-600"></span>
                                                    </span>
                                                @endif

                                                @if($dishRating !== null)
                                                    <span class="text-[10px] font-bold text-emerald-700 bg-emerald-50 border border-emerald-200 px-2 py-0.5 rounded-full">
                                                        ★ {{ number_format($dishRating, 1) }} ({{ $dish->review_count }})
                                                    </span>
                                                @endif

                                                @if($dish->prep_time_mins)
                                                    <span class="text-[10px] font-bold text-[#623562] bg-[#F8EFFA] px-2 py-0.5 rounded-full">
                                                        ~{{ $dish->prep_time_mins }}m
                                                    </span>
                                                @endif

                                                @if($spiceLevel)
                                                    <span class="text-[10px] font-bold uppercase text-[#5F5138] bg-[#FAF5EB] border border-[#C9A24A]/30 px-2 py-0.5 rounded-full">
                                                        🌶️ {{ $spiceLevel }}
                                                    </span>
                                                @endif

                                                @if($servingSize)
                                                    <span class="text-[11px] text-[#1A1018]/55 font-medium">
                                                        Serves {{ $servingSize }}
                                                    </span>
                                                @endif

                                                @if(!$dishAvailable)
                                                    <span class="text-[10px] font-bold uppercase text-rose-700 bg-rose-50 border border-rose-200 px-2 py-0.5 rounded-full">
                                                        {{ ($dish->available_from && $dish->available_until) ? ('Available ' . $dish->available_from . '-' . $dish->available_until) : 'Sold Out' }}
                                                    </span>
                                                @endif
                                            </div>

                                            <!-- Dish Name -->
                                            <h3 class="text-base font-bold text-[#1A1018] group-hover:text-[#623562] transition leading-snug">
                                                {{ $dish->title }}
                                            </h3>

                                            <!-- Price Tag -->
                                            <div class="flex items-baseline gap-2 flex-wrap">
                                                <span class="text-base sm:text-lg font-extrabold text-[#1A1018] tabular-nums">
                                                    ₹{{ $dishPriceLabel }}
                                                </span>
                                                @if($dishDiscount > 0)
                                                    <span class="text-xs text-[#1A1018]/40 line-through tabular-nums">
                                                        ₹{{ $dishCompareLabel }}
                                                    </span>
                                                    <span class="text-[10.5px] font-bold text-emerald-700 bg-emerald-50 px-1.5 py-0.5 rounded">
                                                        {{ $dishDiscount }}% OFF
                                                    </span>
                                                @endif
                                            </div>

                                            <!-- Description -->
                                            @if($dish->description)
                                                <p class="text-xs text-[#1A1018]/60 line-clamp-2 leading-relaxed">
                                                    {{ Str::limit(strip_tags($dish->description), 110) }}
                                                </p>
                                            @endif
                                        </div>

                                        @if($needsCustomization)
                                            <div class="text-[11px] font-semibold text-[#623562] pt-1">
                                                Customisable{{ $hasOptionGroups ? (' • ' . $activeOptionGroups->count() . ' ' . Str::plural('option', $activeOptionGroups->count())) : '' }}
                                            </div>
                                        @endif
                                    </div>

                                    <!-- Dish Thumbnail + Tactile CTA Below (Zero Clipping) -->
                                    <div class="w-28 sm:w-32 shrink-0 flex flex-col items-center justify-between gap-2.5">
                                        <div class="relative w-full h-24 sm:h-28 rounded-2xl overflow-hidden bg-[#141115] border border-black/8 shadow-xs">
                                            <img src="{{ $dish->primary_image_url ?: asset('images/product-placeholder.svg') }}" 
                                                 alt="{{ $dish->title }}" 
                                                 loading="lazy"
                                                 class="w-full h-full object-cover group-hover:scale-105 transition duration-300"
                                                 onerror="this.onerror=null; this.src='{{ asset('images/product-placeholder.svg') }}';">
                                        </div>

                                        <div class="w-full">
                                            @if(!$acceptingOrders)
                                                <button type="button" disabled
                                                        class="w-full py-2 rounded-full bg-stone-200 text-stone-600 text-[11px] font-bold text-center cursor-not-allowed">
                                                    PAUSED
                                                </button>
                                            @elseif(!$dishAvailable)
                                                <button type="button" disabled
                                                        class="w-full py-2 rounded-full bg-rose-50 border border-rose-200 text-rose-700 text-[11px] font-bold text-center cursor-not-allowed">
                                                    UNAVAILABLE
                                                </button>
                                            @elseif($needsCustomization)
                                                <button type="button" 
                                                        @click='openCustomizer(@json(json_decode($dishJson)))'
                                                        class="w-full py-2 rounded-full bg-[#1A1018] hover:bg-[#623562] text-[#ECD2F3] text-xs font-bold text-center shadow-sm transition active:scale-95 cursor-pointer">
                                                    ADD +
                                                </button>
                                            @else
                                                <form action="{{ route('cart.add') }}" method="POST" class="w-full" onsubmit="event.preventDefault(); window.k71AddToCart(this);">
                                                    @csrf
                                                    <input type="hidden" name="product_id" value="{{ $dish->id }}">
                                                    <input type="hidden" name="variant_id" value="{{ $dish->variants->first()?->id }}">
                                                    <input type="hidden" name="quantity" value="1">
                                                    <button type="submit" 
                                                            class="w-full py-2 rounded-full bg-[#1A1018] hover:bg-[#623562] text-[#ECD2F3] text-xs font-bold text-center shadow-sm transition active:scale-95 cursor-pointer">
                                                        + ADD
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </div>
                                </article>
                            @endforeach
                        </div>
                    </section>
                @endforeach
            </div>
        @endif

        <!-- 5. CUSTOMER REVIEWS & TRUST REPORTING SECTION -->
        <section id="kitchen-reviews" class="pt-8 border-t border-black/10 space-y-6">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <span class="grail-eyebrow !text-[#623562] block mb-1">Diner Feedback</span>
                    <h2 class="text-2xl font-light text-[#1A1018] tracking-tight" style="font-family: var(--font-grail-display);">
                        Customer Reviews &amp; Ratings
                    </h2>
                    <p class="text-xs text-[#1A1018]/60">
                        @if($realReviewCount > 0 && $realRating !== null)
                            Average {{ number_format($realRating, 1) }} / 5 from {{ $realReviewCount }} verified {{ Str::plural('review', $realReviewCount) }}.
                        @else
                            Only customers with delivered orders or verified purchases appear with verified badges.
                        @endif
                    </p>
                </div>
                @auth
                    <button type="button" @click="reportModalOpen = true" class="px-4 py-2 rounded-full bg-white border border-black/15 hover:border-rose-300 hover:text-rose-700 text-xs font-bold text-[#1A1018]/75 transition cursor-pointer self-start">
                        ⚑ Report Kitchen Issue
                    </button>
                @endauth
            </div>

            <!-- Verified Order Review Form (Shown when customer has a delivered, unreviewed food order from this kitchen) -->
            @if(!empty($eligibleOrderForReview))
                <div class="grail-card p-6 border-[#623562]/25 bg-white">
                    <h3 class="text-sm font-bold text-[#1A1018] mb-1">
                        Rate Your Delivered Order (#{{ $eligibleOrderForReview->order_number }})
                    </h3>
                    <p class="text-xs text-[#1A1018]/60 mb-4">Share feedback on food quality, temperature, packaging, and service.</p>
                    <form action="{{ route('food.orders.review', $eligibleOrderForReview->id) }}" method="POST" class="space-y-4">
                        @csrf
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-[#1A1018] mb-1">Rating</label>
                                <select name="rating" required class="grail-input-light w-full rounded-xl px-3.5 py-2 text-xs font-bold">
                                    <option value="5">5 Stars — Excellent</option>
                                    <option value="4">4 Stars — Very Good</option>
                                    <option value="3">3 Stars — Good</option>
                                    <option value="2">2 Stars — Fair</option>
                                    <option value="1">1 Star — Poor</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-[#1A1018] mb-1">Review Title (Optional)</label>
                                <input type="text" name="title" placeholder="e.g. Hot & authentic flavors" class="grail-input-light w-full rounded-xl px-3.5 py-2 text-xs">
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-[#1A1018] mb-1">Your Review</label>
                            <textarea name="body" rows="3" required placeholder="Tell fellow diners what you ordered and how it tasted..." class="grail-input-light w-full rounded-xl px-3.5 py-2 text-xs"></textarea>
                        </div>
                        <button type="submit" class="px-5 py-2.5 grail-btn-obsidian text-xs font-bold cursor-pointer">
                            Submit Verified Review
                        </button>
                    </form>
                </div>
            @endif

            <!-- Reviews List -->
            @if(isset($reviews) && $reviews->isNotEmpty())
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @foreach($reviews as $review)
                        <div class="p-5 grail-card space-y-2.5">
                            <div class="flex items-start justify-between gap-2">
                                <div>
                                    <div class="flex items-center gap-2 flex-wrap">
                                        <span class="text-xs font-bold text-[#1A1018]">{{ $review->user?->name ?? 'Customer' }}</span>
                                        @if($review->is_verified_purchase)
                                            <span class="px-2 py-0.5 rounded-full bg-emerald-50 border border-emerald-200 text-emerald-700 text-[10px] font-bold">✓ Verified Order</span>
                                        @endif
                                        @if($review->product)
                                            <span class="px-2 py-0.5 rounded-full bg-[#ECD2F3]/35 text-[#623562] text-[10px] font-semibold">{{ $review->product->title }}</span>
                                        @endif
                                    </div>
                                    <div class="text-[10px] text-[#1A1018]/50 mt-0.5">{{ $review->created_at->format('M d, Y') }}</div>
                                </div>
                                <div class="px-2.5 py-1 rounded-full bg-[#1A1018] text-[#C9A24A] text-xs font-black shrink-0">
                                    ★ {{ $review->rating }}
                                </div>
                            </div>
                            @if($review->title)
                                <h4 class="text-xs font-bold text-[#1A1018]">{{ $review->title }}</h4>
                            @endif
                            <p class="text-xs text-[#1A1018]/75 leading-relaxed">{{ $review->body }}</p>
                            @if($review->vendor_reply)
                                <div class="p-3 rounded-xl bg-[#FAFAF8] border border-black/8 text-xs">
                                    <span class="font-bold text-[#623562] block mb-0.5">Kitchen Reply ({{ $restaurant->shop_name }}):</span>
                                    <span class="text-[#1A1018]/75">{{ $review->vendor_reply }}</span>
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            @else
                <div class="p-8 grail-card text-center text-xs text-[#1A1018]/60">
                    No customer reviews published for {{ $restaurant->shop_name }} yet.
                </div>
            @endif
        </section>

        </div>
        </div>

        <!-- 6. CUSTOMIZATION MODAL (Variants + Option Groups Single/Multi + Legacy Modifiers + Notes) -->
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
                        
                        <template x-if="customizerError">
                            <div class="p-3 rounded-xl bg-rose-50 border border-rose-200 text-rose-700 text-xs font-bold" x-text="customizerError"></div>
                        </template>

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
                                               :class="selectedVariantId == v.id ? 'border-[#1A1018] bg-[#ECD2F3]/35' : 'bg-white border-black/10 hover:bg-[#F6F6F6]'">
                                            <div class="flex items-center gap-3">
                                                <input type="radio" 
                                                       name="variant_option" 
                                                       :value="v.id" 
                                                       x-model="selectedVariantId" 
                                                       class="text-[#1A1018] focus:ring-[#623562]">
                                                <span class="text-xs font-bold text-[#1A1018]" x-text="v.title"></span>
                                            </div>
                                            <span class="text-xs font-black text-[#1A1018]" x-text="'₹' + Number(v.price).toFixed(2)"></span>
                                        </label>
                                    </template>
                                </div>
                            </div>
                        </template>

                        <!-- Structured Option Groups (Single / Multi with Min & Max rules) -->
                        <template x-if="activeDish && activeDish.option_groups && activeDish.option_groups.length > 0">
                            <div class="space-y-4">
                                <template x-for="group in activeDish.option_groups" :key="group.id">
                                    <div>
                                        <h4 class="grail-eyebrow !text-[#623562] mb-2 flex items-center justify-between gap-2">
                                            <span x-text="group.name"></span>
                                            <span class="text-[10px] font-semibold"
                                                  :class="group.is_required ? 'text-rose-600' : 'text-[#1A1018]/50'"
                                                  x-text="groupConstraintLabel(group)"></span>
                                        </h4>
                                        <div class="space-y-2">
                                            <template x-for="opt in group.options" :key="opt.id">
                                                <label class="flex items-center justify-between p-3.5 rounded-2xl border transition-all"
                                                       :class="[
                                                           !opt.is_available ? 'opacity-50 bg-stone-100 border-black/8 cursor-not-allowed' : 'cursor-pointer',
                                                           isOptionSelected(opt.id) ? 'border-[#1A1018] bg-[#ECD2F3]/35 shadow-xs' : 'bg-white border-black/10 hover:border-[#623562]/30'
                                                       ]">
                                                    <div class="flex items-center gap-3">
                                                        <template x-if="group.selection_type === 'single'">
                                                            <input type="radio"
                                                                   :name="'group_' + group.id"
                                                                   :disabled="!opt.is_available"
                                                                   :checked="isOptionSelected(opt.id)"
                                                                   @change="selectSingleOption(group, opt.id)"
                                                                   class="text-[#1A1018] focus:ring-[#623562]">
                                                        </template>
                                                        <template x-if="group.selection_type !== 'single'">
                                                            <input type="checkbox"
                                                                   :disabled="!opt.is_available"
                                                                   :checked="isOptionSelected(opt.id)"
                                                                   @change="toggleMultiOption(group, opt.id, $event)"
                                                                   class="w-4 h-4 text-[#1A1018] rounded-md focus:ring-[#623562] border-black/25">
                                                        </template>
                                                        <span class="text-xs font-bold text-[#1A1018]" x-text="opt.title + (!opt.is_available ? ' (Out of stock)' : '')"></span>
                                                    </div>
                                                    <span class="text-xs font-black text-[#1A1018]" x-text="Number(opt.price) > 0 ? ('+₹' + Number(opt.price).toFixed(2)) : 'Included'"></span>
                                                </label>
                                            </template>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </template>

                        <!-- Legacy Ungrouped Add-ons / Modifiers -->
                        <template x-if="activeDish && activeDish.modifiers && activeDish.modifiers.length > 0">
                            <div>
                                <h4 class="grail-eyebrow !text-[#623562] mb-2 flex items-center justify-between">
                                    <span>Add Extras &amp; Sides</span>
                                    <span class="text-[10px] text-[#1A1018]/50 font-semibold">Optional</span>
                                </h4>
                                <div class="space-y-2.5">
                                    <template x-for="m in activeDish.modifiers" :key="m.id">
                                        <label class="flex items-center justify-between p-3.5 rounded-2xl border transition-all"
                                               :class="[
                                                   !m.is_available ? 'opacity-50 bg-stone-100 border-black/8 cursor-not-allowed' : 'cursor-pointer',
                                                   isOptionSelected(m.id) ? 'border-[#1A1018] bg-[#ECD2F3]/35 shadow-xs' : 'bg-white border-black/10 hover:border-[#623562]/30'
                                               ]">
                                            <div class="flex items-center gap-3">
                                                <input type="checkbox" 
                                                       :disabled="!m.is_available"
                                                       :checked="isOptionSelected(m.id)"
                                                       @change="toggleLegacyModifier(m.id)"
                                                       class="w-4 h-4 text-[#1A1018] rounded-md focus:ring-[#623562] border-black/25">
                                                <span class="text-xs font-bold text-[#1A1018]" x-text="m.title + (!m.is_available ? ' (Out of stock)' : '')"></span>
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
                            <p class="text-[11px] text-[#1A1018]/55 mb-2">e.g. Less spicy, send extra green chutney, no cutlery needed</p>
                            <input type="text" 
                                   x-model="kitchenNote" 
                                   maxlength="300"
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
                                @click="submitCustomizedItem(false)" 
                                :disabled="addingCustomized"
                                class="flex-1 py-3 px-5 grail-btn-obsidian text-xs font-bold flex items-center justify-between shadow-lg cursor-pointer">
                            <span x-text="addingCustomized ? 'Adding to bag...' : 'Add Item to Bag'"></span>
                            <span class="text-[#ECD2F3] text-xs font-black" x-text="'₹' + calculatedCustomPrice.toFixed(2)"></span>
                        </button>
                    </div>

                </div>
            </div>
        </div>

        <!-- 7. SINGLE-RESTAURANT BAG CONFLICT MODAL (409) -->
        <div x-show="conflictModalOpen" x-cloak class="fixed inset-0 z-[60] flex items-center justify-center p-4 bg-black/75 backdrop-blur-sm">
            <div class="bg-[#FAFAF8] text-[#1A1018] rounded-[28px] max-w-md w-full p-6 shadow-2xl border border-black/10 space-y-4" @click.outside="conflictModalOpen = false">
                <div class="w-12 h-12 rounded-2xl bg-amber-100 text-amber-800 flex items-center justify-center text-2xl font-bold">🛍️</div>
                <h3 class="text-xl font-bold text-[#1A1018]" style="font-family: var(--font-grail-display);">Replace items in your bag?</h3>
                <p class="text-xs text-[#1A1018]/75 leading-relaxed" x-text="conflictMessage || 'Your bag contains dishes from another kitchen. Replace existing items with this dish?'"></p>
                <div class="flex items-center justify-end gap-2.5 pt-2">
                    <button type="button" @click="conflictModalOpen = false; pendingRetryAction = null;" class="px-4 py-2.5 rounded-full border border-black/15 text-xs font-bold text-[#1A1018] hover:bg-black/5 cursor-pointer">
                        Keep Current Bag
                    </button>
                    <button type="button" @click="confirmClearAndRetry()" class="px-5 py-2.5 grail-btn-obsidian text-xs font-bold cursor-pointer">
                        Clear Bag &amp; Add
                    </button>
                </div>
            </div>
        </div>

        <!-- 8. REPORT KITCHEN MODAL -->
        @auth
        <div x-show="reportModalOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/75 backdrop-blur-sm">
            <div class="bg-[#FAFAF8] text-[#1A1018] rounded-[28px] max-w-md w-full p-6 shadow-2xl border border-black/10 space-y-4" @click.outside="reportModalOpen = false">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-bold text-[#1A1018]" style="font-family: var(--font-grail-display);">Report an Issue</h3>
                    <button type="button" @click="reportModalOpen = false" class="w-8 h-8 rounded-full bg-[#EFE9F2] text-xs font-bold cursor-pointer">✕</button>
                </div>
                <form action="{{ route('reports.store') }}" method="POST" class="space-y-3">
                    @csrf
                    <input type="hidden" name="reportable_type" value="vendor">
                    <input type="hidden" name="reportable_id" value="{{ $restaurant->id }}">
                    <div>
                        <label class="block text-xs font-bold text-[#1A1018] mb-1">Reason</label>
                        <select name="reason" required class="grail-input-light w-full rounded-xl px-3.5 py-2 text-xs">
                            <option value="Food hygiene / safety concern">Food hygiene / safety concern</option>
                            <option value="Misleading menu pricing or items">Misleading menu pricing or items</option>
                            <option value="Fake FSSAI license details">Fake FSSAI license details</option>
                            <option value="Other operational issue">Other operational issue</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-[#1A1018] mb-1">Details</label>
                        <textarea name="details" rows="3" placeholder="Provide clear details for K71 moderation review..." class="grail-input-light w-full rounded-xl px-3.5 py-2 text-xs"></textarea>
                    </div>
                    <div class="flex justify-end gap-2 pt-2">
                        <button type="button" @click="reportModalOpen = false" class="px-4 py-2 rounded-full border border-black/15 text-xs font-bold cursor-pointer">Cancel</button>
                        <button type="submit" class="px-5 py-2 rounded-full bg-rose-600 text-white text-xs font-bold hover:bg-rose-700 cursor-pointer">Submit Report</button>
                    </div>
                </form>
            </div>
        </div>
        @endauth

    </div>

    @push('scripts')
    <script>
    function restaurantMenu(config) {
        return {
            restaurantId: config.restaurantId,
            restaurantName: config.restaurantName,
            deliveryFee: config.deliveryFee,
            acceptingOrders: config.acceptingOrders,
            selectedCategory: 'all',
            vegOnly: false,
            searchQuery: '',
            reportModalOpen: false,

            // Conflict modal state
            conflictModalOpen: false,
            conflictMessage: '',
            pendingRetryAction: null,

            // Customizer Modal State
            customizerOpen: false,
            activeDish: null,
            selectedVariantId: null,
            selectedModifiers: [],
            kitchenNote: '',
            itemQuantity: 1,
            addingCustomized: false,
            customizerError: '',

            init() {
                window.addEventListener('k71-food-cart-conflict', (e) => {
                    this.conflictMessage = e.detail?.message || '';
                    this.pendingRetryAction = e.detail?.retry || null;
                    this.conflictModalOpen = true;
                });
            },

            shouldShowCategory(catSlug) {
                if (this.selectedCategory === 'all') return true;
                return this.selectedCategory === catSlug;
            },

            shouldShowDish(titleLower, isVeg) {
                if (this.vegOnly && !isVeg) return false;
                if (this.searchQuery && !titleLower.includes(this.searchQuery.toLowerCase())) return false;
                return true;
            },

            groupConstraintLabel(group) {
                if (group.selection_type === 'single') {
                    return group.is_required ? 'Required • Choose 1' : 'Optional • Choose up to 1';
                }
                const min = Number(group.min_selections || 0);
                const max = group.max_selections ? Number(group.max_selections) : null;
                if (min > 0 && max) return `Required • Choose ${min} to ${max}`;
                if (min > 0) return `Required • Min ${min}`;
                if (max) return `Optional • Up to ${max}`;
                return 'Optional';
            },

            isOptionSelected(id) {
                return this.selectedModifiers.some(m => String(m) === String(id));
            },

            selectSingleOption(group, optionId) {
                const groupIds = (group.options || []).map(o => String(o.id));
                this.selectedModifiers = this.selectedModifiers.filter(id => !groupIds.includes(String(id)));
                this.selectedModifiers.push(Number(optionId));
                this.customizerError = '';
            },

            toggleMultiOption(group, optionId, event) {
                const strId = String(optionId);
                if (this.isOptionSelected(optionId)) {
                    this.selectedModifiers = this.selectedModifiers.filter(id => String(id) !== strId);
                    this.customizerError = '';
                    return;
                }
                const groupIds = (group.options || []).map(o => String(o.id));
                const currentCount = this.selectedModifiers.filter(id => groupIds.includes(String(id))).length;
                if (group.max_selections && currentCount >= Number(group.max_selections)) {
                    if (event && event.target) event.target.checked = false;
                    this.customizerError = `You can select at most ${group.max_selections} option(s) for '${group.name}'.`;
                    return;
                }
                this.selectedModifiers.push(Number(optionId));
                this.customizerError = '';
            },

            toggleLegacyModifier(modId) {
                const strId = String(modId);
                if (this.isOptionSelected(modId)) {
                    this.selectedModifiers = this.selectedModifiers.filter(id => String(id) !== strId);
                } else {
                    this.selectedModifiers.push(Number(modId));
                }
            },

            openCustomizer(dish) {
                this.activeDish = dish;
                this.selectedVariantId = dish.variants && dish.variants.length > 0 ? dish.variants[0].id : null;
                this.selectedModifiers = [];
                this.customizerError = '';

                // Pre-select default options where configured & available
                (dish.option_groups || []).forEach(g => {
                    let chosen = false;
                    (g.options || []).forEach(opt => {
                        if (opt.is_default && opt.is_available && !(g.selection_type === 'single' && chosen)) {
                            this.selectedModifiers.push(Number(opt.id));
                            chosen = true;
                        }
                    });
                });
                (dish.modifiers || []).forEach(m => {
                    if (m.is_default && m.is_available) {
                        this.selectedModifiers.push(Number(m.id));
                    }
                });

                this.kitchenNote = '';
                this.itemQuantity = 1;
                this.customizerOpen = true;
            },

            get calculatedCustomPrice() {
                if (!this.activeDish) return 0;
                let unitPrice = Number(this.activeDish.base_price) || 0;
                if (this.selectedVariantId && this.activeDish.variants) {
                    const foundV = this.activeDish.variants.find(v => String(v.id) === String(this.selectedVariantId));
                    if (foundV && Number(foundV.price) > 0) unitPrice = Number(foundV.price);
                }
                const allMods = [...(this.activeDish.modifiers || [])];
                (this.activeDish.option_groups || []).forEach(g => {
                    (g.options || []).forEach(o => allMods.push(o));
                });
                this.selectedModifiers.forEach(modId => {
                    const foundM = allMods.find(m => String(m.id) === String(modId));
                    if (foundM && Number(foundM.price) > 0) unitPrice += Number(foundM.price);
                });
                return unitPrice * (this.itemQuantity || 1);
            },

            validateBeforeSubmit() {
                if (!this.activeDish) return false;
                const groups = this.activeDish.option_groups || [];
                for (const g of groups) {
                    const groupIds = (g.options || []).map(o => String(o.id));
                    const count = this.selectedModifiers.filter(id => groupIds.includes(String(id))).length;
                    const min = Number(g.min_selections || (g.is_required ? 1 : 0));
                    if (count < min) {
                        this.customizerError = `Please select at least ${min} option(s) for '${g.name}'.`;
                        return false;
                    }
                }
                return true;
            },

            async submitCustomizedItem(clearExisting = false) {
                if (!this.activeDish || !this.validateBeforeSubmit()) return;
                this.addingCustomized = true;
                this.customizerError = '';

                const formData = new FormData();
                formData.append('product_id', this.activeDish.id);
                if (this.selectedVariantId) {
                    formData.append('variant_id', this.selectedVariantId);
                }
                formData.append('quantity', this.itemQuantity);
                if (this.selectedModifiers.length > 0) {
                    this.selectedModifiers.forEach(id => {
                        formData.append('selected_modifiers[]', id);
                    });
                }
                if (this.kitchenNote) {
                    formData.append('special_instructions', this.kitchenNote);
                }
                if (clearExisting) {
                    formData.append('clear_existing', '1');
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

                    if (res.status === 409 && data.conflict) {
                        this.conflictMessage = data.message;
                        this.pendingRetryAction = () => this.submitCustomizedItem(true);
                        this.conflictModalOpen = true;
                        return;
                    }

                    if (res.ok && (data.success || data.cart_count)) {
                        this.customizerOpen = false;
                        window.dispatchEvent(new CustomEvent('cart-updated', { detail: data }));
                        window.dispatchEvent(new CustomEvent('cart-count-updated', { detail: { count: data.cart_count } }));
                        window.dispatchEvent(new CustomEvent('open-mini-cart'));
                        if (window.showToast) window.showToast('Item customized & added to your bag! 🛍️');
                    } else {
                        const errMsg = data.message || (data.errors ? Object.values(data.errors).flat()[0] : 'Could not add item.');
                        this.customizerError = errMsg;
                        if (window.showToast) window.showToast(errMsg, false);
                    }
                } catch (e) {
                    console.error(e);
                    this.customizerError = 'Error adding customized item. Please try again.';
                } finally {
                    this.addingCustomized = false;
                }
            },

            confirmClearAndRetry() {
                const retryFn = this.pendingRetryAction;
                this.conflictModalOpen = false;
                this.pendingRetryAction = null;
                if (typeof retryFn === 'function') retryFn();
            }
        };
    }

    // Direct Quick Add Helper (with 409 conflict & 422 validation support)
    window.k71AddToCart = async function(form, clearExisting = false) {
        const formData = new FormData(form);
        if (clearExisting) {
            formData.set('clear_existing', '1');
        }
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

            if (res.status === 409 && data.conflict) {
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalText;
                }
                window.dispatchEvent(new CustomEvent('k71-food-cart-conflict', {
                    detail: {
                        message: data.message,
                        retry: () => window.k71AddToCart(form, true)
                    }
                }));
                return;
            }

            if (res.ok && (data.success || data.cart_count)) {
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
                const msg = data.message || (data.errors ? Object.values(data.errors).flat()[0] : 'Could not add item.');
                if (window.showToast) {
                    window.showToast(msg, false);
                } else {
                    alert(msg);
                }
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalText;
                }
            }
        } catch (e) {
            console.error(e);
            if (window.showToast) {
                window.showToast('Error adding item. Please try again.', false);
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
