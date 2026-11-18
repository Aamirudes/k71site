@php
    $storeName = $storeName ?? ($vendor->shop_name ?? 'K71 Store');
    $frameBase = $previewFrameBaseUrl ?? null;
    $frameGlue = $frameBase ? (str_contains($frameBase, '?') ? '&' : '?') : '?';
    $storeCollectionUrl = $storeCollectionUrl ?? ($frameBase ? ($frameBase . $frameGlue . 'page=collection') : route('storefront.collection', ['slug' => $vendor->slug]));
    $prodUrl = fn ($p) => $frameBase ? ($frameBase . $frameGlue . 'page=product&product=' . urlencode($p->slug)) : route('storefront.product', ['slug' => $vendor->slug, 'productSlug' => $p->slug]);

    $heroCfg = $themeConfig['hero'] ?? [];
    $heroHeadline = trim((string) ($heroCfg['headline'] ?? '')) ?: $storeName;
    $heroSub = trim((string) ($heroCfg['subheadline'] ?? '')) ?: \Illuminate\Support\Str::limit(strip_tags((string) ($vendor->description ?? 'Sculptural translucent surfaces, layered spatial depth, and effortless tactile shopping.')), 140);
    $allProducts = (isset($products) && method_exists($products, 'items')) ? collect($products->items())->values() : collect($products ?? [])->values();
    $sv = $manifest['layout']['structural_variant'] ?? 'A';
    $lead = $allProducts->first();
    $heroImg = trim((string) ($heroCfg['image_url'] ?? '')) ?: ($lead?->images?->first()?->url ?? asset('images/theme-editorial/' . ($manifest['layout']['sample_art'] ?? 'fashion-editorial') . '.jpg'));
@endphp

@if($sv === 'B')
{{-- VARIANT B (Daily Ritual): CENTERED FLOATING GLASS SANCTUARY OVER SOFT STUDIO BACKDROP --}}
<section data-k71-hero-stage class="relative w-full min-h-[76vh] flex items-center justify-center py-14 px-4 overflow-hidden">
    <img src="{{ $heroImg }}" alt="{{ $heroHeadline }}" class="pointer-events-none absolute inset-0 w-full h-full object-cover opacity-35 scale-105 blur-xs">
    <div class="relative z-10 max-w-2xl w-full k71-glass-panel rounded-[2.25rem] p-8 sm:p-14 text-center space-y-6">
        <span data-k71-hero-meta class="inline-block px-4 py-1 rounded-full text-[11px] font-bold uppercase tracking-[0.2em]" style="background-color: var(--t-accent); color: var(--t-accent-contrast);">{{ $manifest['name'] }}</span>
        <h1 data-k71-hero-title class="k71-heading text-4xl sm:text-6xl leading-[1.02]">{{ $heroHeadline }}</h1>
        <p data-k71-hero-meta class="text-sm sm:text-base max-w-lg mx-auto" style="color: var(--t-muted);">{{ $heroSub }}</p>
        <div class="flex flex-wrap justify-center gap-3 pt-2">
            <a href="{{ $storeCollectionUrl }}" data-k71-magnetic class="px-8 py-3.5 rounded-full text-xs font-bold" style="background-color: var(--t-accent); color: var(--t-accent-contrast);">Explore Essentials</a>
            @if($lead)<a href="{{ $prodUrl($lead) }}" class="px-6 py-3.5 rounded-full text-xs font-bold k71-glass-panel">Spotlight · ₹{{ number_format(($lead->base_price ?? 0) / 100, 0) }}</a>@endif
        </div>
    </div>
</section>
@else
{{-- VARIANT A (Future Form): SPATIAL LIQUID GLASS 3D FLOATING STUDIO HERO --}}
<section data-k71-hero-stage class="w-full py-10 sm:py-16 lg:py-20">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="k71-glass-panel rounded-[2rem] p-6 sm:p-10 lg:p-14 grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12 items-center">
            <div class="lg:col-span-6 space-y-6">
                <div data-k71-hero-meta class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full text-xs font-semibold k71-glass-panel">
                    <span class="w-2 h-2 rounded-full" style="background-color: var(--t-accent);"></span>
                    <span>{{ $manifest['name'] }} · Spatial Studio</span>
                </div>
                <h1 data-k71-hero-title class="k71-heading text-4xl sm:text-5xl lg:text-6xl leading-[1.02]">
                    {{ $heroHeadline }}
                </h1>
                <p data-k71-hero-meta class="text-sm sm:text-base leading-relaxed max-w-lg" style="color: var(--t-muted);">
                    {{ $heroSub }}
                </p>

                <div data-k71-hero-meta class="flex flex-wrap items-center gap-3 pt-1">
                    <a href="{{ $storeCollectionUrl }}" data-k71-magnetic
                       class="px-7 py-3.5 rounded-full text-xs sm:text-sm font-bold inline-flex items-center gap-2 shadow-lg"
                       style="background-color: var(--t-accent); color: var(--t-accent-contrast);">
                        <span>Explore Studio</span>
                        @include('storefront.themes.partials.icon', ['name' => 'arrow-right', 'class' => 'w-4 h-4'])
                    </a>
                    @if($lead)
                        <a href="{{ $prodUrl($lead) }}" class="px-6 py-3.5 rounded-full text-xs sm:text-sm font-semibold k71-glass-panel hover:opacity-80 transition">
                            Featured · ₹{{ number_format(($lead->base_price ?? 0) / 100, 0) }}
                        </a>
                    @endif
                </div>
            </div>

            {{-- Floating 3D Specular Showcase Capsule --}}
            <div class="lg:col-span-6">
                <div data-k71-hero-media data-k71-3d-card class="k71-glass-panel rounded-[1.75rem] p-4 sm:p-5 overflow-hidden">
                    <div class="aspect-[4/3] rounded-[1.25rem] overflow-hidden" style="background-color: var(--t-surface-alt);">
                        <img src="{{ $heroImg }}" alt="{{ $heroHeadline }}" class="w-full h-full object-cover">
                    </div>
                    @if($lead)
                        <div class="pt-4 px-2 flex items-center justify-between gap-4">
                            <div class="min-w-0">
                                <p class="text-[11px] uppercase tracking-wider" style="color: var(--t-muted);">{{ $lead->category?->name ?? 'Studio Selection' }}</p>
                                <p class="k71-heading text-base sm:text-lg truncate">{{ $lead->title }}</p>
                            </div>
                            <a href="{{ $prodUrl($lead) }}" class="shrink-0 w-10 h-10 rounded-full flex items-center justify-center"
                               style="background-color: var(--t-accent); color: var(--t-accent-contrast);" aria-label="View {{ $lead->title }}">
                                @include('storefront.themes.partials.icon', ['name' => 'arrow-right', 'class' => 'w-4 h-4'])
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Floating Frosted Capsule Department Rail --}}
        @if(($vendorCategories ?? collect())->isNotEmpty())
            <div data-k71-reveal class="mt-6 flex items-center justify-center flex-wrap gap-2.5">
                <a href="{{ $storeCollectionUrl }}" class="px-5 py-2.5 rounded-full text-xs font-bold shadow-sm"
                   style="background-color: var(--t-accent); color: var(--t-accent-contrast);">All Works</a>
                @foreach($vendorCategories->take(7) as $cat)
                    <a href="{{ $storeCollectionUrl }}{{ str_contains($storeCollectionUrl, '?') ? '&' : '?' }}category={{ urlencode($cat->slug) }}"
                       class="px-5 py-2.5 rounded-full text-xs font-semibold k71-glass-panel hover:opacity-80 transition">
                        {{ $cat->name }}
                    </a>
                @endforeach
            </div>
        @endif
    </div>
</section>
@endif

{{-- 2. SCULPTURAL GLASS PRODUCT GALLERY --}}
<section class="w-full py-10 sm:py-16">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center max-w-2xl mx-auto mb-10 sm:mb-14 space-y-2">
            <p class="k71-eyebrow" style="color: var(--t-accent);">Curated Objects</p>
            <h2 class="k71-heading text-3xl sm:text-4xl">Sculpted Collection</h2>
        </div>

        <div data-k71-stagger-grid class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 sm:gap-8">
            @foreach($allProducts->take(12) as $product)
                @php
                    $pImg = $product->images?->first()?->url ?? asset('images/theme-editorial/' . ($manifest['layout']['sample_art'] ?? 'fashion-editorial') . '.jpg');
                    $pLink = $prodUrl($product);
                    $hasVariants = ($product->variants ?? collect())->isNotEmpty();
                @endphp
                <article data-k71-card-item data-k71-3d-card class="k71-glass-panel rounded-[1.75rem] p-4 flex flex-col justify-between gap-4 group">
                    <div>
                        <a href="{{ $pLink }}" class="block aspect-[4/4] rounded-[1.25rem] overflow-hidden relative mb-4" style="background-color: var(--t-surface-alt);">
                            <img src="{{ $pImg }}" alt="{{ $product->title }}" loading="lazy" class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-105">
                            <span class="absolute top-3 right-3 px-3 py-1 rounded-full text-xs font-bold k71-glass-panel">
                                ₹{{ number_format(($product->base_price ?? 0) / 100, 0) }}
                            </span>
                        </a>
                        <div class="px-1.5 space-y-1">
                            <p class="text-[11px] uppercase tracking-wider" style="color: var(--t-muted);">{{ $product->category?->name ?? 'Studio' }}</p>
                            <h3 class="k71-heading text-lg leading-snug"><a href="{{ $pLink }}">{{ $product->title }}</a></h3>
                        </div>
                    </div>

                    <div class="px-1.5 pt-2 flex items-center justify-between gap-3">
                        <a href="{{ $pLink }}" class="text-xs font-semibold hover:opacity-70" style="color: var(--t-muted);">Details</a>
                        @if($hasVariants)
                            <a href="{{ $pLink }}" class="px-4 py-2 rounded-full text-xs font-bold" style="background-color: var(--t-accent); color: var(--t-accent-contrast);">Choose</a>
                        @else
                            <form action="{{ route('cart.add') }}" method="POST" data-k71-cart-form>
                                @csrf
                                <input type="hidden" name="product_id" value="{{ $product->id }}">
                                <input type="hidden" name="quantity" value="1">
                                <button type="submit" class="px-4 py-2 rounded-full text-xs font-bold cursor-pointer" style="background-color: var(--t-accent); color: var(--t-accent-contrast);">+ Add</button>
                            </form>
                        @endif
                    </div>
                </article>
            @endforeach
        </div>
    </div>
</section>
