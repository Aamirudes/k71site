@php
    $flagship = $featuredProducts->first() ?? $products->first();
    $title = $section['title'] ?? ($flagship ? $flagship->title : 'Engineered Precision');
    $subtitle = $section['subtitle'] ?? $config['brand']['tagline'] ?? 'Designed with purpose, finished by hand';
    $badge = $section['badge'] ?? '✦ New Season Release';

    if ($flagship) {
        $priceRupees = number_format($flagship->base_price / 100, 2);
        $flagshipImg = $flagship->images->first()?->path ? \App\Services\ImageService::url($flagship->images->first()->path, 800) : asset('images/product-placeholder.svg');
        $defaultVariantId = $flagship->variants->first()?->id;
    }
@endphp

<!-- CYBER-GLASS PRODUCT SPLIT HERO -->
<section id="hero-split" class="relative overflow-hidden py-8 sm:py-14">
    <!-- Ambient Blur Bubble -->
    <div class="absolute top-0 right-1/4 w-80 h-80 rounded-full opacity-20 filter blur-3xl pointer-events-none"
         style="background: var(--sf-accent, #0284c7);"></div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
        <div class="rounded-3xl sm:rounded-[2.5rem] p-6 sm:p-10 border border-white/60 shadow-[0_15px_40px_rgba(0,0,0,0.05)]"
             style="background: rgba(255, 255, 255, 0.7); backdrop-filter: blur(28px); -webkit-backdrop-filter: blur(28px);">
            
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-center">
                
                <!-- Left Details Column (7 cols) -->
                <div class="lg:col-span-7 flex flex-col items-start pr-0 lg:pr-8">
                    <span class="px-3 py-1 rounded-full text-[11px] font-bold uppercase tracking-wider mb-4 border"
                          style="background: rgba(var(--sf-accent-rgb, 2, 132, 199), 0.1); border-color: rgba(var(--sf-accent-rgb, 2, 132, 199), 0.3); color: var(--sf-accent, #0284c7);">
                        {{ $badge }}
                    </span>

                    <h1 class="text-3xl sm:text-4xl lg:text-5xl font-black tracking-tight text-stone-900 font-heading leading-tight mb-4">
                        {{ $flagship ? $flagship->title : $vendor->shop_name }}
                    </h1>

                    <p class="text-sm sm:text-base text-stone-600 mb-6 leading-relaxed">
                        {{ $flagship && !empty($flagship->short_description) ? $flagship->short_description : $subtitle }}
                    </p>

                    @if($flagship)
                        <!-- Tech Spec Chips / Interactive Pills -->
                        <div class="flex flex-wrap gap-2 mb-6">
                            <span class="px-3 py-1 rounded-xl text-xs font-semibold bg-stone-100/80 text-stone-700 border border-stone-200/60">
                                📦 Express Direct Dispatch
                            </span>
                            <span class="px-3 py-1 rounded-xl text-xs font-semibold bg-stone-100/80 text-stone-700 border border-stone-200/60">
                                🛡️ Verified Quality Guarantee
                            </span>
                            <span class="px-3 py-1 rounded-xl text-xs font-semibold bg-stone-100/80 text-stone-700 border border-stone-200/60">
                                ↺ 7-Day Exchange
                            </span>
                        </div>

                        <!-- Price & Instant Cart Trigger -->
                        <div class="flex flex-wrap items-center gap-4">
                            <div class="text-2xl font-black text-stone-950">
                                ₹{{ $priceRupees }}
                            </div>

                            <button type="button" 
                                    onclick="window.quickAddToCart({{ $flagship->id }}, {{ $defaultVariantId ?: 'null' }})"
                                    class="px-8 py-3.5 rounded-2xl text-xs sm:text-sm font-extrabold uppercase tracking-wider text-white shadow-lg hover:shadow-xl transition-all duration-300 hover:scale-102 flex items-center gap-2 cursor-pointer"
                                    style="background-color: var(--sf-primary, #0f172a);">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                                </svg>
                                <span>Quick Add · Open Bag</span>
                            </button>
                        </div>
                    @endif
                </div>

                <!-- Right Flagship Image (5 cols) -->
                <div class="lg:col-span-5 w-full">
                    <div class="relative aspect-square sm:aspect-[4/3] lg:aspect-[4/5] rounded-3xl overflow-hidden shadow-xl border border-white/80 bg-stone-100">
                        @if($flagship)
                            <img src="{{ $flagshipImg }}" 
                                 alt="{{ $flagship->title }}" 
                                 class="w-full h-full object-cover">
                        @else
                            <div class="w-full h-full flex items-center justify-center text-4xl">🛍️</div>
                        @endif
                    </div>
                </div>

            </div>

        </div>
    </div>
</section>
