@php
    $title = $section['title'] ?? $config['brand']['store_name'] ?? $vendor->shop_name;
    $subtitle = $section['subtitle'] ?? $config['brand']['tagline'] ?? '';
    $description = $section['description'] ?? '';
    $badge = $section['badge'] ?? null;
    $ctaPrimaryText = $section['cta_primary_text'] ?? 'Explore Catalog';
    $ctaPrimaryLink = $section['cta_primary_link'] ?? '#catalog';
    $ctaSecondaryText = $section['cta_secondary_text'] ?? null;
    $ctaSecondaryLink = $section['cta_secondary_link'] ?? '#story';
    $imageUrl = $section['image_url'] ?? $config['brand']['banner_url'] ?? $vendor->banner_url;
    $alignment = $section['alignment'] ?? 'center';

    $alignClass = match($alignment) {
        'left' => 'text-left items-start',
        'right' => 'text-right items-end',
        default => 'text-center items-center mx-auto',
    };
@endphp

<section id="{{ $section['id'] }}" 
         data-section-id="{{ $section['id'] }}"
         class="relative overflow-hidden py-16 sm:py-24 lg:py-32 transition-all"
         style="background-color: var(--sf-surface, #ffffff);">
    
    @if(!empty($imageUrl))
        <!-- Ambient Backdrop Image with Smooth Gradient Overlay -->
        <div class="absolute inset-0 z-0">
            <img src="{{ $imageUrl }}" 
                 alt="{{ $title }}" 
                 class="w-full h-full object-cover opacity-25 dark:opacity-20 filter contrast-105"
                 loading="eager">
            <div class="absolute inset-0 bg-gradient-to-t from-[var(--sf-bg,#fafaf9)] via-[var(--sf-bg,#fafaf9)]/80 to-transparent"></div>
        </div>
    @else
        <!-- Refined Abstract Studio Background Pattern -->
        <div class="absolute inset-0 opacity-[0.03] pointer-events-none" 
             style="background-image: radial-gradient(#000000 1px, transparent 1px); background-size: 24px 24px;"></div>
    @endif

    <div class="relative z-10 max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col {{ $alignClass }} max-w-3xl {{ $alignment === 'center' ? 'mx-auto' : '' }}">
            
            @if(!empty($badge))
                <div class="inline-flex items-center gap-2 px-3.5 py-1 rounded-full text-xs font-semibold tracking-wider uppercase mb-6 shadow-2xs border"
                     style="background-color: rgba(var(--sf-accent-rgb, 197, 155, 39), 0.1); border-color: rgba(var(--sf-accent-rgb, 197, 155, 39), 0.3); color: var(--sf-accent, #c59b27);">
                    <span class="w-1.5 h-1.5 rounded-full" style="background-color: var(--sf-accent, #c59b27);"></span>
                    <span>{{ $badge }}</span>
                </div>
            @endif

            <h1 class="text-3xl sm:text-5xl lg:text-6xl font-bold tracking-tight text-stone-900 font-heading leading-[1.1] mb-6">
                {{ $title }}
            </h1>

            @if(!empty($subtitle))
                <p class="text-lg sm:text-xl font-medium text-stone-600 mb-4 max-w-2xl leading-relaxed">
                    {{ $subtitle }}
                </p>
            @endif

            @if(!empty($description))
                <p class="text-sm sm:text-base text-stone-500 mb-8 max-w-xl leading-relaxed">
                    {{ $description }}
                </p>
            @endif

            <div class="flex flex-wrap items-center gap-3 sm:gap-4 pt-2">
                @if(!empty($ctaPrimaryText))
                    <a href="{{ $ctaPrimaryLink }}" 
                       class="px-6 sm:px-8 py-3.5 text-xs sm:text-sm font-bold uppercase tracking-wider text-white rounded-full transition-all duration-300 shadow-md hover:shadow-lg hover:scale-102"
                       style="background-color: var(--sf-primary, #18181b);">
                        {{ $ctaPrimaryText }}
                    </a>
                @endif

                @if(!empty($ctaSecondaryText))
                    <a href="{{ $ctaSecondaryLink }}" 
                       class="px-6 sm:px-8 py-3.5 text-xs sm:text-sm font-bold uppercase tracking-wider text-stone-800 rounded-full border border-stone-300 hover:border-stone-900 bg-white/70 backdrop-blur-xs hover:bg-white transition-all duration-300">
                        {{ $ctaSecondaryText }}
                    </a>
                @endif
            </div>

        </div>
    </div>
</section>

