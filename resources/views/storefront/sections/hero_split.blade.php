@php
    $title = $section['title'] ?? 'Designed with Distinction';
    $subtitle = $section['subtitle'] ?? $config['brand']['tagline'] ?? '';
    $description = $section['description'] ?? $config['brand']['description'] ?? '';
    $badge = $section['badge'] ?? null;
    $ctaPrimaryText = $section['cta_primary_text'] ?? 'Explore Catalog';
    $ctaPrimaryLink = $section['cta_primary_link'] ?? '#catalog';
    $ctaSecondaryText = $section['cta_secondary_text'] ?? 'Maker Story';
    $ctaSecondaryLink = $section['cta_secondary_link'] ?? '#story';
    $imageUrl = $section['image_url'] ?? $config['brand']['banner_url'] ?? $vendor->banner_url;
@endphp

<section id="{{ $section['id'] }}" 
         data-section-id="{{ $section['id'] }}"
         class="relative py-12 sm:py-20 lg:py-24 border-b border-stone-200/60 overflow-hidden"
         style="background-color: var(--sf-surface, #ffffff);">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12 items-center">
            
            <!-- Left Copy Column (7 cols) -->
            <div class="lg:col-span-7 flex flex-col items-start pr-0 lg:pr-8">
                @if(!empty($badge))
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider mb-5 border"
                         style="background-color: rgba(var(--sf-accent-rgb, 200, 90, 50), 0.08); border-color: rgba(var(--sf-accent-rgb, 200, 90, 50), 0.25); color: var(--sf-accent, #c85a32);">
                        <span>{{ $badge }}</span>
                    </div>
                @endif

                <h1 class="text-3xl sm:text-4xl lg:text-5xl font-black tracking-tight text-stone-900 font-heading leading-tight mb-5">
                    {{ $title }}
                </h1>

                @if(!empty($subtitle))
                    <p class="text-lg sm:text-xl font-medium text-stone-700 mb-4 leading-snug">
                        {{ $subtitle }}
                    </p>
                @endif

                @if(!empty($description))
                    <p class="text-sm sm:text-base text-stone-500 mb-8 max-w-xl leading-relaxed">
                        {{ $description }}
                    </p>
                @endif

                <div class="flex flex-wrap items-center gap-3 sm:gap-4">
                    @if(!empty($ctaPrimaryText))
                        <a href="{{ $ctaPrimaryLink }}" 
                           class="px-6 sm:px-8 py-3.5 text-xs sm:text-sm font-bold uppercase tracking-wider text-white rounded-xl transition-all duration-300 shadow-sm hover:shadow-md hover:scale-101"
                           style="background-color: var(--sf-primary, #0f172a);">
                            {{ $ctaPrimaryText }}
                        </a>
                    @endif

                    @if(!empty($ctaSecondaryText))
                        <a href="{{ $ctaSecondaryLink }}" 
                           class="px-5 sm:px-6 py-3.5 text-xs sm:text-sm font-bold uppercase tracking-wider text-stone-700 rounded-xl border border-stone-300 hover:border-stone-800 bg-white hover:bg-stone-50 transition-all duration-300">
                            {{ $ctaSecondaryText }}
                        </a>
                    @endif
                </div>
            </div>

            <!-- Right Image Column (5 cols) -->
            <div class="lg:col-span-5 w-full">
                <div class="relative w-full aspect-[4/3] sm:aspect-[16/10] lg:aspect-[4/5] rounded-2xl overflow-hidden shadow-lg border border-stone-200/80 bg-stone-100">
                    @if(!empty($imageUrl))
                        <img src="{{ $imageUrl }}" 
                             alt="{{ $title }}" 
                             class="w-full h-full object-cover transition-transform duration-700 hover:scale-103"
                             loading="eager">
                    @else
                        <!-- Elegant Workshop Graphic Placeholder -->
                        <div class="w-full h-full flex flex-col items-center justify-center p-8 text-center bg-gradient-to-br from-stone-100 to-stone-200">
                            <span class="text-4xl mb-3">🏬</span>
                            <span class="font-heading font-bold text-lg text-stone-800">{{ $config['brand']['store_name'] ?? $vendor->shop_name }}</span>
                            <span class="text-xs text-stone-500 mt-1">Verified K71 Studio</span>
                        </div>
                    @endif
                </div>
            </div>

        </div>
    </div>
</section>

