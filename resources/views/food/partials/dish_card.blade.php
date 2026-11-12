@php
    $dImage = $dish->primary_image_url ?: asset('images/product-placeholder.svg');
    $isDietVeg = $dish->isVeg();
    $pricePaise = (int) ($dish->base_price ?? 0);
    $comparePaise = (int) ($dish->compare_at_price ?? 0);
    $priceFormatted = ($pricePaise % 100 === 0)
        ? number_format($pricePaise / 100, 0)
        : number_format($pricePaise / 100, 2);
    $compareFormatted = ($comparePaise % 100 === 0)
        ? number_format($comparePaise / 100, 0)
        : number_format($comparePaise / 100, 2);
    $discountPct = ($comparePaise > $pricePaise && $comparePaise > 0)
        ? (int) round((($comparePaise - $pricePaise) / $comparePaise) * 100)
        : 0;

    $requiresConfig = ($dish->optionGroups?->where('is_active', true)->isNotEmpty() ?? false)
        || ($dish->modifiers?->isNotEmpty() ?? false)
        || (($dish->variants?->count() ?? 0) > 1);

    $vendorUrl = $dish->vendor ? route('food.restaurant', $dish->vendor->slug) : '#';
    $opStatus = $dish->getAttribute('kitchen_operational_status') ?? ['accepting_orders' => true, 'label' => 'Open'];
    $kitchenAccepting = (bool) ($opStatus['accepting_orders'] ?? true);
    $prepMins = $dish->prep_time_mins ?: $dish->vendor?->settings?->avg_prep_time_mins;
    $realRating = ((float) ($dish->avg_rating ?? 0) > 0 && (int) ($dish->review_count ?? 0) > 0)
        ? (float) $dish->avg_rating
        : null;
    $servingSize = $dish->serving_size ?? null;
    $spiceLevel = $dish->spice_level ?? null;
@endphp

<article class="group relative bg-white rounded-[22px] border border-black/8 hover:border-[#623562]/30 shadow-[0_8px_24px_-14px_rgba(26,16,24,0.14)] hover:shadow-[0_20px_42px_-16px_rgba(98,53,98,0.22)] hover:-translate-y-1 transition-all duration-300 flex flex-col overflow-hidden {{ !$kitchenAccepting ? 'opacity-90' : '' }}">
    <!-- Photo Stage -->
    <a href="{{ $vendorUrl }}" class="relative aspect-[16/11] w-full overflow-hidden bg-[#141115] block">
        <img src="{{ $dImage }}"
             alt="{{ $dish->title }}"
             loading="lazy"
             class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500 ease-out {{ !$kitchenAccepting ? 'grayscale-[35%]' : '' }}"
             onerror="this.onerror=null; this.src='{{ asset('images/product-placeholder.svg') }}';">
        <div class="pointer-events-none absolute inset-x-0 bottom-0 h-20 bg-gradient-to-t from-black/70 via-black/25 to-transparent"></div>

        <!-- Top Left: Authentic FSSAI Veg/Non-Veg Symbol -->
        <div class="absolute top-3 left-3 flex items-center gap-1.5 z-10">
            <span class="w-5 h-5 rounded-md bg-white/95 backdrop-blur-sm shadow-sm flex items-center justify-center" title="{{ $dish->dietary_label_resolved }}">
                @if($isDietVeg)
                    <span class="w-3.5 h-3.5 border-2 border-emerald-600 rounded-[3px] flex items-center justify-center">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-600"></span>
                    </span>
                @else
                    <span class="w-3.5 h-3.5 border-2 border-rose-600 rounded-[3px] flex items-center justify-center">
                        <span class="w-0 h-0 border-l-[3.5px] border-l-transparent border-r-[3.5px] border-r-transparent border-b-[6px] border-b-rose-600"></span>
                    </span>
                @endif
            </span>
        </div>

        <!-- Top Right: Discount / Kitchen Closed Pill -->
        <div class="absolute top-3 right-3 flex items-center gap-1.5 z-10">
            @if(!$kitchenAccepting)
                <span class="px-2.5 py-0.5 rounded-full bg-rose-600/95 text-white text-[10px] font-bold tracking-wide shadow-sm">
                    {{ $opStatus['label'] ?? 'Closed' }}
                </span>
            @elseif($discountPct > 0)
                <span class="px-2.5 py-0.5 rounded-full bg-[#1A1018]/90 text-[#ECD2F3] border border-[#ECD2F3]/30 text-[10px] font-bold tracking-wider uppercase shadow-sm backdrop-blur-sm">
                    {{ $discountPct }}% OFF
                </span>
            @endif
        </div>

        <!-- Bottom Overlay Pills: Prep Time & Verified Rating -->
        <div class="absolute bottom-2.5 left-3 right-3 flex items-center justify-between gap-2 z-10">
            @if($prepMins)
                <span class="px-2.5 py-0.5 rounded-full bg-black/75 backdrop-blur-md text-white text-[10px] font-semibold tracking-wide border border-white/15">
                    ~{{ $prepMins }} mins
                </span>
            @else
                <span></span>
            @endif

            @if($realRating !== null)
                <span class="px-2 py-0.5 rounded-full bg-emerald-600 text-white text-[10px] font-bold flex items-center gap-1 shadow-sm">
                    <span>★</span>
                    <span>{{ number_format($realRating, 1) }}</span>
                    <span class="opacity-80 font-normal">({{ $dish->review_count }})</span>
                </span>
            @endif
        </div>
    </a>

    <!-- Dish Content Body -->
    <div class="p-4 flex flex-col flex-1">
        <!-- Kitchen Attribution -->
        @if($dish->vendor)
            <div class="flex items-center justify-between gap-2 mb-1">
                <a href="{{ $vendorUrl }}" class="inline-flex items-center gap-1.5 text-[11px] font-semibold text-[#623562] hover:text-[#1A1018] transition truncate">
                    <span class="w-1.5 h-1.5 rounded-full {{ $kitchenAccepting ? 'bg-emerald-500' : 'bg-stone-400' }} shrink-0"></span>
                    <span class="truncate">{{ $dish->vendor->shop_name }}</span>
                </a>
                @if($dish->vendor->city)
                    <span class="text-[10px] text-[#1A1018]/45 font-medium shrink-0">{{ $dish->vendor->city }}</span>
                @endif
            </div>
        @endif

        <!-- Title -->
        <h3 class="text-[15px] font-bold text-[#1A1018] group-hover:text-[#623562] transition-colors leading-snug line-clamp-2">
            <a href="{{ $vendorUrl }}">{{ $dish->title }}</a>
        </h3>

        <!-- Subtle Dietary/Portion Tags (Only if present in DB) -->
        @if($servingSize || $spiceLevel || $requiresConfig)
            <div class="flex items-center flex-wrap gap-1.5 mt-2">
                @if($servingSize)
                    <span class="px-2 py-0.5 rounded-md bg-[#F6F6F6] text-[#1A1018]/70 text-[10px] font-semibold border border-black/5">
                        {{ $servingSize }}
                    </span>
                @endif
                @if($spiceLevel)
                    <span class="px-2 py-0.5 rounded-md bg-[#FAF5EB] text-[#5F5138] text-[10px] font-semibold border border-[#C9A24A]/25">
                        🌶️ {{ $spiceLevel }}
                    </span>
                @endif
                @if($requiresConfig)
                    <span class="px-2 py-0.5 rounded-md bg-[#F8EFFA] text-[#623562] text-[10px] font-semibold">
                        Customisable
                    </span>
                @endif
            </div>
        @endif

        @if($dish->description)
            <p class="text-xs text-[#1A1018]/60 line-clamp-2 leading-relaxed mt-2">
                {{ Str::limit(strip_tags($dish->description), 88) }}
            </p>
        @endif

        <!-- Price & Direct Add Footer -->
        <div class="mt-auto pt-3.5 mt-3 border-t border-black/8 flex items-center justify-between gap-2">
            <div class="flex items-baseline gap-1.5 min-w-0">
                <span class="text-lg font-extrabold text-[#1A1018] tabular-nums tracking-tight">₹{{ $priceFormatted }}</span>
                @if($discountPct > 0)
                    <span class="text-xs text-[#1A1018]/40 line-through tabular-nums">₹{{ $compareFormatted }}</span>
                @endif
            </div>

            @if(!$kitchenAccepting)
                <a href="{{ $vendorUrl }}"
                   class="px-3.5 py-2 rounded-full bg-stone-100 text-stone-600 text-[11px] font-bold hover:bg-stone-200 transition shrink-0">
                    View Menu
                </a>
            @elseif($requiresConfig)
                <a href="{{ $vendorUrl }}"
                   class="px-4 py-2 rounded-full bg-[#1A1018] hover:bg-[#623562] text-[#ECD2F3] shadow-sm text-xs font-bold transition-all inline-flex items-center gap-1 shrink-0">
                    <span>Customise</span>
                    <span>+</span>
                </a>
            @else
                <button type="button"
                        @click="quickAddToCart($event, {{ $dish->id }}, '{{ addslashes($dish->title) }}', {{ $dish->vendor_id }}, {{ $dish->variants->first()?->id ?? 'null' }})"
                        class="px-4 py-2 rounded-full bg-[#1A1018] hover:bg-[#623562] text-[#ECD2F3] shadow-sm text-xs font-bold transition-all cursor-pointer active:scale-95 inline-flex items-center gap-1.5 shrink-0">
                    <span>ADD</span>
                    <span class="text-sm font-bold leading-none">+</span>
                </button>
            @endif
        </div>
    </div>
</article>

