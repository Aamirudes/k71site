@php
    $arch = $manifest['layout']['page_architecture'] ?? 'editorial-runway';
    $storeName = $storeName ?? ($vendor->shop_name ?? 'K71 Store');
    $frameBase = $previewFrameBaseUrl ?? null;
    $frameGlue = $frameBase ? (str_contains($frameBase, '?') ? '&' : '?') : '?';
    $storeCollectionUrl = $storeCollectionUrl ?? ($frameBase ? ($frameBase . $frameGlue . 'page=collection') : route('storefront.collection', ['slug' => $vendor->slug]));
    $allItemsCount = (isset($products) && method_exists($products, 'total'))
        ? (int) $products->total()
        : ((isset($products) && method_exists($products, 'items')) ? count($products->items()) : (is_countable($products ?? []) ? count($products) : 0));
    $catCount = ($vendorCategories ?? collect())->count();
    $returnDays = (int) ($vendor->settings?->return_window_days ?? 0);
    $locationLabel = trim(implode(', ', array_filter([$vendor->city ?? '', $vendor->state ?? ''])));

    $tickerItems = array_values(array_filter([
        strtoupper($storeName),
        strtoupper($manifest['name'] ?? 'FLAGSHIP'),
        $allItemsCount > 0 ? ($allItemsCount . ' VERIFIED PIECES') : 'CURATED CATALOGUE',
        $returnDays > 0 ? ($returnDays . '-DAY VERIFIED RETURNS') : 'DIRECT MERCHANT FULFILLMENT',
        $locationLabel !== '' ? strtoupper($locationLabel) : 'K71 VERIFIED STOREFRONT',
    ]));

    $isBrutal = in_array($arch, ['brutalist-poster', 'typography-as-architecture'], true);
    $isTech = in_array($arch, ['swiss-manifesto', 'b2b-specimen-ledger', 'bento-modular-flagship', 'pro-commerce-catalog'], true);
    $isCinema = in_array($arch, ['cinematic-dark-stage', 'spatial-glass-studio'], true);
@endphp

{{-- KINETIC BRAND MARQUEE & FLAGSHIP MERCHANT PROVENANCE ARCHITECTURE --}}
@if($isBrutal)
    <section data-k71-reveal class="w-full" style="border-top: 2px solid var(--t-text); border-bottom: 2px solid var(--t-text); background-color: var(--t-accent); color: var(--t-accent-contrast);">
        <div class="k71-ticker-viewport py-3.5">
            <div class="k71-ticker-track">
                @for($rep = 0; $rep < 4; $rep++)
                    <div class="flex items-center gap-8 pr-8 text-xs sm:text-sm font-black uppercase tracking-[0.24em] whitespace-nowrap">
                        @foreach($tickerItems as $ti)
                            <span>{{ $ti }}</span>
                            <span class="opacity-50">//</span>
                        @endforeach
                    </div>
                @endfor
            </div>
        </div>
    </section>

    <section data-k71-reveal class="w-full py-12 sm:py-16">
        <div class="max-w-[1480px] mx-auto px-4 sm:px-8 grid grid-cols-1 md:grid-cols-3 gap-5">
            <div class="p-6 space-y-2 k71-brutal-box" style="background-color: var(--t-surface); border: 2px solid var(--t-text);">
                <p class="font-mono text-[10px] uppercase tracking-[0.2em]" style="color: var(--t-accent);">01 // AUTHENTICATED</p>
                <h3 class="k71-heading text-xl uppercase">Direct From {{ $storeName }}</h3>
                <p class="text-xs leading-relaxed" style="color: var(--t-muted);">Every release is fulfilled directly from verified K71 merchant stock with transparent pricing.</p>
            </div>
            <div class="p-6 space-y-2 k71-brutal-box" style="background-color: var(--t-surface); border: 2px solid var(--t-text);">
                <p class="font-mono text-[10px] uppercase tracking-[0.2em]" style="color: var(--t-accent);">02 // LIVE INVENTORY</p>
                <h3 class="k71-heading text-xl uppercase">{{ $allItemsCount }} Active Releases</h3>
                <p class="text-xs leading-relaxed" style="color: var(--t-muted);">Real-time stock visibility across {{ max(1, $catCount) }} curated {{ $catCount === 1 ? 'line' : 'lines' }} with instant checkout.</p>
            </div>
            <div class="p-6 space-y-2 k71-brutal-box" style="background-color: var(--t-surface); border: 2px solid var(--t-text);">
                <p class="font-mono text-[10px] uppercase tracking-[0.2em]" style="color: var(--t-accent);">03 // BUYER PROTECTION</p>
                <h3 class="k71-heading text-xl uppercase">{{ $returnDays > 0 ? $returnDays . '-Day Return Window' : 'Protected K71 Checkout' }}</h3>
                <p class="text-xs leading-relaxed" style="color: var(--t-muted);">{{ $locationLabel ? ('Dispatched from ' . $locationLabel . ' via verified K71 logistics.') : 'Backed by K71 Marketplace order verification and support.' }}</p>
            </div>
        </div>
    </section>

@elseif($isTech)
    <section data-k71-reveal class="w-full py-10 sm:py-14" style="background-color: var(--t-surface); border-top: 1px solid var(--t-border); border-bottom: 1px solid var(--t-border);">
        <div class="max-w-[1480px] mx-auto px-4 sm:px-8 space-y-8">
            <div class="flex flex-wrap items-end justify-between gap-4 pb-4" style="border-bottom: 1px solid var(--t-border);">
                <div>
                    <p class="font-mono text-[10px] uppercase tracking-[0.22em]" style="color: var(--t-accent);">MERCHANT TELEMETRY // {{ strtoupper($manifest['name'] ?? 'SPEC') }}</p>
                    <h2 class="k71-heading text-2xl sm:text-3xl mt-1">Verified Fulfillment Standard</h2>
                </div>
                <a href="{{ $storeCollectionUrl }}" class="k71-btn k71-btn-outline !min-h-[40px] text-xs font-mono uppercase">Full Specification Index &rarr;</a>
            </div>
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="p-5 space-y-1.5" style="background-color: var(--t-bg); border: 1px solid var(--t-border); border-radius: var(--t-radius);">
                    <p class="font-mono text-[10px] uppercase tracking-widest" style="color: var(--t-muted);">ACTIVE CATALOG</p>
                    <p class="font-mono text-2xl sm:text-3xl font-extrabold" style="color: var(--t-accent);">{{ $allItemsCount }}</p>
                    <p class="text-xs" style="color: var(--t-muted);">Live verified SKUs ready for order</p>
                </div>
                <div class="p-5 space-y-1.5" style="background-color: var(--t-bg); border: 1px solid var(--t-border); border-radius: var(--t-radius);">
                    <p class="font-mono text-[10px] uppercase tracking-widest" style="color: var(--t-muted);">DEPARTMENTS</p>
                    <p class="font-mono text-2xl sm:text-3xl font-extrabold">{{ max(1, $catCount) }}</p>
                    <p class="text-xs" style="color: var(--t-muted);">Structured product lines &amp; specs</p>
                </div>
                <div class="p-5 space-y-1.5" style="background-color: var(--t-bg); border: 1px solid var(--t-border); border-radius: var(--t-radius);">
                    <p class="font-mono text-[10px] uppercase tracking-widest" style="color: var(--t-muted);">RETURN STANDARD</p>
                    <p class="font-mono text-2xl sm:text-3xl font-extrabold">{{ $returnDays > 0 ? ($returnDays . ' Days') : 'Verified' }}</p>
                    <p class="text-xs" style="color: var(--t-muted);">Transparent merchant policy</p>
                </div>
                <div class="p-5 space-y-1.5" style="background-color: var(--t-bg); border: 1px solid var(--t-border); border-radius: var(--t-radius);">
                    <p class="font-mono text-[10px] uppercase tracking-widest" style="color: var(--t-muted);">DISPATCH ORIGIN</p>
                    <p class="font-mono text-lg sm:text-xl font-extrabold truncate">{{ $locationLabel ?: 'K71 Direct' }}</p>
                    <p class="text-xs" style="color: var(--t-muted);">Authenticated seller fulfillment</p>
                </div>
            </div>
        </div>
    </section>

@elseif($isCinema)
    <section data-k71-reveal class="w-full py-16 sm:py-20 relative overflow-hidden" style="border-top: 1px solid var(--t-border);">
        <div class="max-w-7xl mx-auto px-5 sm:px-8 space-y-12">
            <div class="k71-ticker-viewport py-3" style="border-top: 1px solid var(--t-border); border-bottom: 1px solid var(--t-border);">
                <div class="k71-ticker-track">
                    @for($rep = 0; $rep < 4; $rep++)
                        <div class="flex items-center gap-10 pr-10 text-xs sm:text-sm uppercase tracking-[0.28em] whitespace-nowrap" style="color: var(--t-muted);">
                            @foreach($tickerItems as $ti)
                                <span>{{ $ti }}</span>
                                <span style="color: var(--t-accent);">✦</span>
                            @endforeach
                        </div>
                    @endfor
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="p-7 rounded-2xl k71-glass-panel space-y-3" data-k71-3d-card>
                    <span class="font-mono text-[10px] uppercase tracking-[0.24em]" style="color: var(--t-accent);">PROVENANCE · 01</span>
                    <h3 class="k71-heading text-xl">Curated Flagship</h3>
                    <p class="text-xs sm:text-sm leading-relaxed" style="color: var(--t-muted);">{{ $storeName }} presents {{ $allItemsCount }} verified pieces curated directly for discerning buyers on K71.</p>
                </div>
                <div class="p-7 rounded-2xl k71-glass-panel space-y-3" data-k71-3d-card>
                    <span class="font-mono text-[10px] uppercase tracking-[0.24em]" style="color: var(--t-accent);">ASSURANCE · 02</span>
                    <h3 class="k71-heading text-xl">{{ $returnDays > 0 ? $returnDays . '-Day Guarantee' : 'Verified Checkout' }}</h3>
                    <p class="text-xs sm:text-sm leading-relaxed" style="color: var(--t-muted);">Protected K71 order processing, instant bag sync, and verified merchant accountability.</p>
                </div>
                <div class="p-7 rounded-2xl k71-glass-panel space-y-3" data-k71-3d-card>
                    <span class="font-mono text-[10px] uppercase tracking-[0.24em]" style="color: var(--t-accent);">CONCIERGE · 03</span>
                    <h3 class="k71-heading text-xl">{{ $locationLabel ?: 'Direct Atelier Support' }}</h3>
                    <p class="text-xs sm:text-sm leading-relaxed" style="color: var(--t-muted);">Direct communication with the merchant for sizing, materials, and bespoke dispatch inquiries.</p>
                </div>
            </div>
        </div>
    </section>

@else
    {{-- EDITORIAL / GALLERY / SANCTUARY / COLOR-BLOCK FLAGSHIP STORY STRIP --}}
    <section data-k71-reveal class="w-full mt-4" style="border-top: 1px solid var(--t-border); border-bottom: 1px solid var(--t-border); background-color: var(--t-surface);">
        <div class="k71-ticker-viewport py-4">
            <div class="k71-ticker-track">
                @for($rep = 0; $rep < 4; $rep++)
                    <div class="flex items-center gap-10 pr-10 k71-heading text-lg sm:text-2xl tracking-wide whitespace-nowrap">
                        @foreach($tickerItems as $ti)
                            <span>{{ $ti }}</span>
                            <span class="text-xs" style="color: var(--t-accent);">◆</span>
                        @endforeach
                    </div>
                @endfor
            </div>
        </div>
    </section>

    <section data-k71-reveal class="w-full py-14 sm:py-18">
        <div class="max-w-7xl mx-auto px-5 sm:px-8 grid grid-cols-1 md:grid-cols-3 gap-8 sm:gap-10">
            <div class="space-y-2.5 pt-4" style="border-top: 1px solid var(--t-border);">
                <p class="k71-eyebrow" style="color: var(--t-accent);">01 · House Selection</p>
                <h3 class="k71-heading text-xl">{{ $allItemsCount }} Curated Works</h3>
                <p class="text-xs sm:text-sm leading-relaxed" style="color: var(--t-muted);">Every piece in {{ $storeName }} is catalogued directly by the merchant with live pricing and availability.</p>
            </div>
            <div class="space-y-2.5 pt-4" style="border-top: 1px solid var(--t-border);">
                <p class="k71-eyebrow" style="color: var(--t-accent);">02 · Client Care</p>
                <h3 class="k71-heading text-xl">{{ $returnDays > 0 ? $returnDays . '-Day Return Policy' : 'Protected K71 Orders' }}</h3>
                <p class="text-xs sm:text-sm leading-relaxed" style="color: var(--t-muted);">Seamless bag, instant quick-view inspection, and secure checkout powered by K71 Marketplace.</p>
            </div>
            <div class="space-y-2.5 pt-4" style="border-top: 1px solid var(--t-border);">
                <p class="k71-eyebrow" style="color: var(--t-accent);">03 · Provenance</p>
                <h3 class="k71-heading text-xl">{{ $locationLabel ?: 'Verified Merchant' }}</h3>
                <p class="text-xs sm:text-sm leading-relaxed" style="color: var(--t-muted);">Direct fulfillment and responsive merchant communication from studio to doorstep.</p>
            </div>
        </div>
    </section>
@endif
