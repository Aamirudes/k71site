<x-layouts.public 
    :title="$restaurant->shop_name . ' — Digital Menu & Food Delivery | K71 Food'"
    :metaDescription="'Order online from ' . $restaurant->shop_name . ' on K71 Food. Fast doorstep delivery, authentic cuisine, and live kitchen tracking.'"
>
    <!-- RESTAURANT HEADER BANNER -->
    <header class="relative rounded-3xl bg-gradient-to-br from-amber-500/10 via-orange-500/5 to-transparent border border-zinc-200/80 p-6 sm:p-8 mb-8 shadow-xs">
        <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-6">
            <div class="flex items-start sm:items-center gap-4">
                <!-- Restaurant Avatar -->
                <div class="w-20 h-20 sm:w-24 sm:h-24 rounded-2xl bg-white border border-zinc-200 p-1 shrink-0 overflow-hidden shadow-xs flex items-center justify-center">
                    @if($restaurant->logo_url)
                        <img src="{{ $restaurant->logo_url }}" alt="{{ $restaurant->shop_name }}" class="w-full h-full object-cover rounded-xl">
                    @else
                        <svg class="w-10 h-10 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                        </svg>
                    @endif
                </div>

                <div>
                    <div class="flex flex-wrap items-center gap-2 mb-1.5">
                        <h1 class="text-2xl sm:text-3xl font-bold text-zinc-950 tracking-tight">
                            {{ $restaurant->shop_name }}
                        </h1>
                        <span class="px-2 py-0.5 rounded-full bg-emerald-50 border border-emerald-200 text-emerald-700 text-[11px] font-semibold flex items-center gap-1">
                            <svg class="w-3 h-3 text-emerald-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg>
                            <span>Verified Kitchen</span>
                        </span>
                        @if($restaurant->isPureVeg())
                            <span class="px-2 py-0.5 rounded-full bg-emerald-100 border border-emerald-300 text-emerald-800 text-[11px] font-bold flex items-center gap-1">
                                <span class="w-2 h-2 rounded-full bg-emerald-600"></span>
                                <span>Pure Veg</span>
                            </span>
                        @endif
                    </div>

                    <!-- Cuisines & Location -->
                    <div class="text-xs sm:text-sm text-zinc-600 flex flex-wrap items-center gap-y-1 gap-x-3 mb-2">
                        @if(!empty($restaurant->settings?->cuisine_types))
                            @php
                                $cuisines = is_array($restaurant->settings->cuisine_types) 
                                    ? $restaurant->settings->cuisine_types 
                                    : (json_decode($restaurant->settings->cuisine_types, true) ?: [$restaurant->settings->cuisine_types]);
                            @endphp
                            <span>{{ implode(' • ', $cuisines) }}</span>
                            <span>•</span>
                        @endif
                        <span class="text-zinc-500">{{ $restaurant->city ?? 'Local' }}</span>
                    </div>

                    <!-- Highlights Pills -->
                    <div class="flex flex-wrap items-center gap-2 text-xs">
                        <span class="px-2.5 py-1 rounded-lg bg-white border border-zinc-200 text-zinc-700 flex items-center gap-1.5 shadow-xs">
                            <svg class="w-3.5 h-3.5 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <span>Prep: ~{{ $restaurant->settings?->avg_prep_time_mins ?? 25 }} mins</span>
                        </span>
                        <span class="px-2.5 py-1 rounded-lg bg-white border border-zinc-200 text-zinc-700 flex items-center gap-1.5 shadow-xs">
                            <svg class="w-3.5 h-3.5 text-zinc-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                            <span>Hours: {{ $restaurant->settings?->opening_time ?? '10:00' }} - {{ $restaurant->settings?->closing_time ?? '23:00' }}</span>
                        </span>
                        <span class="px-2.5 py-1 rounded-lg bg-white border border-zinc-200 text-zinc-700 flex items-center gap-1.5 shadow-xs">
                            <svg class="w-3.5 h-3.5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                            </svg>
                            <span>Delivery: ₹{{ number_format(($restaurant->settings?->food_delivery_fee ?? 4000) / 100) }}</span>
                        </span>
                        @if($restaurant->settings?->cod_enabled)
                            <span class="px-2.5 py-1 rounded-lg bg-amber-50 border border-amber-200 text-amber-800 font-medium flex items-center gap-1.5">
                                <span>COD Available</span>
                            </span>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Action / Store Profile Button -->
            <div class="flex items-center gap-2 shrink-0">
                <a href="{{ route('storefront.show', $restaurant->slug) }}" 
                   class="px-4 py-2 rounded-xl bg-white hover:bg-zinc-50 text-zinc-800 text-xs font-semibold transition border border-zinc-300 shadow-xs">
                    View Seller Profile
                </a>
            </div>
        </div>
    </header>

    <!-- STICKY MENU CATEGORY BAR & FILTERS -->
    <div class="sticky top-16 z-30 bg-white/90 backdrop-blur-md rounded-2xl border border-zinc-200/80 p-3 mb-8 shadow-xs">
        <div class="flex items-center justify-between gap-4 overflow-x-auto no-scrollbar">
            <!-- Categories -->
            <div class="flex items-center gap-2 shrink-0">
                <a href="{{ route('food.restaurant', ['slug' => $restaurant->slug]) }}" 
                   class="px-3.5 py-1.5 rounded-full text-xs font-semibold transition {{ empty($selectedCategory) ? 'bg-zinc-900 text-white shadow-xs' : 'bg-zinc-100 hover:bg-zinc-200 text-zinc-700' }}">
                    All Items
                </a>
                @foreach($menuCategories as $cat)
                    <a href="{{ route('food.restaurant', ['slug' => $restaurant->slug, 'cat' => $cat->slug, 'veg_only' => $vegOnly ? 1 : 0]) }}" 
                       class="px-3.5 py-1.5 rounded-full text-xs font-semibold transition {{ $selectedCategory === $cat->slug ? 'bg-orange-600 text-white shadow-xs' : 'bg-zinc-100 hover:bg-zinc-200 text-zinc-700' }}">
                        {{ $cat->name }}
                    </a>
                @endforeach
            </div>

            <!-- Veg Only Toggle -->
            <div class="shrink-0 flex items-center gap-2 pl-4 border-l border-zinc-200">
                <a href="{{ route('food.restaurant', ['slug' => $restaurant->slug, 'cat' => $selectedCategory, 'veg_only' => $vegOnly ? 0 : 1]) }}" 
                   class="px-3 py-1 rounded-full text-xs font-semibold transition flex items-center gap-2 {{ $vegOnly ? 'bg-emerald-600 text-white shadow-xs' : 'bg-zinc-100 hover:bg-zinc-200 text-zinc-700 border border-zinc-200' }}">
                    <span class="w-3.5 h-3.5 rounded-xs border-2 border-current flex items-center justify-center p-0.5">
                        <span class="w-1.5 h-1.5 rounded-full bg-current"></span>
                    </span>
                    <span>Veg Only</span>
                </a>
            </div>
        </div>
    </div>

    <!-- MENU CONTAINER -->
    <div class="pb-16">
        @if($groupedMenu->isEmpty())
            <div class="p-12 text-center rounded-3xl bg-white border border-zinc-200 max-w-lg mx-auto shadow-xs">
                <div class="w-12 h-12 rounded-full bg-zinc-100 text-zinc-400 flex items-center justify-center mx-auto mb-3">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                </div>
                <h3 class="text-lg font-bold text-zinc-900 mb-1">No dishes found in this section</h3>
                <p class="text-xs text-zinc-500 mb-4">Try clearing your filters or exploring all items on this menu.</p>
                <a href="{{ route('food.restaurant', $restaurant->slug) }}" class="px-4 py-2 rounded-xl bg-zinc-900 text-white text-xs font-semibold hover:bg-zinc-800 transition">
                    View Full Menu
                </a>
            </div>
        @else
            <div class="space-y-10">
                @foreach($groupedMenu as $categoryName => $items)
                    <section id="cat-{{ Str::slug($categoryName) }}" class="scroll-mt-32">
                        <div class="flex items-center gap-3 mb-4">
                            <h2 class="text-xl font-bold text-zinc-950 tracking-tight">{{ $categoryName }}</h2>
                            <span class="px-2 py-0.5 rounded-full bg-zinc-100 text-zinc-700 text-xs font-semibold">
                                {{ $items->count() }}
                            </span>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            @foreach($items as $dish)
                                @php
                                    $dietary = $dish->getCustomField('dietary_type', 'Veg');
                                    $isVeg = str_contains(strtolower($dietary), 'veg') && !str_contains(strtolower($dietary), 'non-veg');
                                    $spiceLevel = $dish->getCustomField('spice_level');
                                    $servingSize = $dish->getCustomField('serving_size');
                                    $prepTime = $dish->getCustomField('prep_time_mins', $restaurant->settings?->avg_prep_time_mins ?? 25);
                                @endphp
                                <div class="p-4 rounded-2xl bg-white hover:bg-orange-50/20 border border-zinc-200/80 hover:border-zinc-300 transition-all duration-200 flex flex-col sm:flex-row justify-between gap-4 group shadow-xs hover:shadow-md">
                                    <div class="flex-1 space-y-2">
                                        <!-- Veg / Non-Veg Icon & Spice -->
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
                                                <span class="text-[11px] font-semibold text-amber-800 bg-amber-50 border border-amber-200 px-1.5 py-0.5 rounded">
                                                    Spice: {{ $spiceLevel }}
                                                </span>
                                            @endif

                                            @if($servingSize)
                                                <span class="text-[11px] text-zinc-500">
                                                    Serves {{ $servingSize }}
                                                </span>
                                            @endif
                                        </div>

                                        <!-- Dish Title -->
                                        <h3 class="text-base font-bold text-zinc-900 group-hover:text-orange-600 transition leading-snug">
                                            <a href="{{ route('product.show', $dish->slug) }}">
                                                {{ $dish->title }}
                                            </a>
                                        </h3>

                                        <!-- Price -->
                                        <div class="flex items-baseline gap-2">
                                            <span class="text-base font-bold text-zinc-950">
                                                ₹{{ number_format($dish->base_price / 100, 2) }}
                                            </span>
                                            @if($dish->compare_at_price && $dish->compare_at_price > $dish->base_price)
                                                <span class="text-xs text-zinc-400 line-through">
                                                    ₹{{ number_format($dish->compare_at_price / 100, 2) }}
                                                </span>
                                            @endif
                                        </div>

                                        <!-- Description -->
                                        @if($dish->description)
                                            <p class="text-xs text-zinc-500 line-clamp-2 leading-relaxed">
                                                {{ Str::limit(strip_tags($dish->description), 110) }}
                                            </p>
                                        @endif
                                    </div>

                                    <!-- Dish Image & Quick Add -->
                                    <div class="relative w-full sm:w-32 h-28 shrink-0 flex flex-col items-center justify-between">
                                        <div class="w-full h-full rounded-xl overflow-hidden bg-zinc-100 border border-zinc-200">
                                            @if($dish->images->isNotEmpty())
                                                <img src="{{ asset('storage/' . $dish->images->first()->path) }}" alt="{{ $dish->title }}" 
                                                     class="w-full h-full object-cover group-hover:scale-105 transition duration-300">
                                            @else
                                                <div class="w-full h-full flex items-center justify-center text-zinc-300 bg-zinc-50">
                                                    <svg class="w-8 h-8 text-zinc-300" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                                                    </svg>
                                                </div>
                                            @endif
                                        </div>

                                        <!-- Add Button -->
                                        <div class="absolute -bottom-2 w-24">
                                            @if($dish->variants->count() > 1)
                                                <a href="{{ route('product.show', $dish->slug) }}" 
                                                   class="w-full py-1.5 rounded-lg bg-white border border-zinc-300 text-zinc-800 text-xs font-semibold text-center block shadow-xs transition active:scale-95 hover:bg-zinc-50">
                                                    Options +
                                                </a>
                                            @else
                                                <form action="{{ route('cart.add') }}" method="POST" class="w-full" onsubmit="event.preventDefault(); window.k71AddToCart(this);">
                                                    @csrf
                                                    <input type="hidden" name="product_id" value="{{ $dish->id }}">
                                                    <input type="hidden" name="variant_id" value="{{ $dish->variants->first()?->id }}">
                                                    <input type="hidden" name="quantity" value="1">
                                                    <button type="submit" 
                                                            class="w-full py-1.5 rounded-lg bg-orange-50 border border-orange-600 text-orange-700 hover:bg-orange-600 hover:text-white text-xs font-bold text-center shadow-xs transition active:scale-95">
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

    @push('scripts')
    <script>
        window.k71AddToCart = async function(form) {
            const formData = new FormData(form);
            const submitBtn = form.querySelector('button[type="submit"]');
            const originalText = submitBtn ? submitBtn.innerHTML : '+ ADD';
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = 'Adding...';
            }

            try {
                const res = await fetch(form.action, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json'
                    },
                    body: formData
                });
                const data = await res.json();
                if (data.success) {
                    if (window.dispatchEvent) {
                        window.dispatchEvent(new CustomEvent('cart-updated', { detail: data }));
                    }
                    if (window.showToast) {
                        window.showToast('Item added to your bag!');
                    }
                } else {
                    if (window.showToast) {
                        window.showToast(data.message || 'Could not add item.', false);
                    }
                }
            } catch(e) {
                console.error(e);
                if (window.showToast) {
                    window.showToast('Error adding item. Please try again.', false);
                }
            } finally {
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalText;
                }
            }
        };
    </script>
    @endpush
</x-layouts.public>
