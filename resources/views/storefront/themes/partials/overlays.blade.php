{{-- Mobile Navigation Drawer --}}
<div id="k71-mobile-drawer" data-k71-modal aria-hidden="true" role="dialog" aria-modal="true" aria-label="Store navigation menu"
     class="hidden fixed inset-0 z-50 flex">
    <div class="fixed inset-0 bg-black/55" data-k71-close="k71-mobile-drawer"></div>
    <div class="relative z-10 w-[85vw] max-w-xs h-full flex flex-col justify-between p-6 overflow-y-auto shadow-2xl"
         style="background-color: var(--t-surface); color: var(--t-text); border-right: 1px solid var(--t-border);">
        <div class="space-y-6">
            <div class="flex items-center justify-between pb-4" style="border-bottom: 1px solid var(--t-border);">
                <span class="k71-heading text-lg truncate">{{ $storeName }}</span>
                <button type="button" data-k71-close="k71-mobile-drawer" class="min-w-[44px] min-h-[44px] flex items-center justify-center rounded-lg" aria-label="Close menu">✕</button>
            </div>
            <nav class="flex flex-col gap-1" aria-label="Mobile store navigation">
                <a href="{{ $storeHomeUrl }}" class="min-h-[44px] px-3 rounded-lg flex items-center text-sm font-semibold hover:opacity-75">Home</a>
                <a href="{{ $storeCollectionUrl }}" class="min-h-[44px] px-3 rounded-lg flex items-center text-sm font-semibold hover:opacity-75">All Products</a>
                @foreach(($vendorCategories ?? collect())->take(8) as $cat)
                    <a href="{{ $storeCollectionUrl }}{{ str_contains($storeCollectionUrl, '?') ? '&' : '?' }}category={{ urlencode($cat->slug) }}"
                       class="min-h-[44px] px-3 rounded-lg flex items-center justify-between text-sm hover:opacity-75" style="color: var(--t-muted);">
                        <span>{{ $cat->name }}</span>
                        <span class="text-xs">{{ $cat->products_count ?? '' }}</span>
                    </a>
                @endforeach
            </nav>
        </div>
        <div class="pt-4 space-y-2" style="border-top: 1px solid var(--t-border);">
            <a href="{{ route('cart.index') }}" class="k71-btn k71-btn-primary w-full">View Bag ({{ $cartCount }})</a>
            <a href="{{ auth()->check() ? route('account') : route('login') }}" class="k71-btn k71-btn-outline w-full">{{ auth()->check() ? 'My Account' : 'Sign In' }}</a>
        </div>
    </div>
</div>

{{-- Search Modal --}}
<div id="k71-search-modal" data-k71-modal aria-hidden="true" role="dialog" aria-modal="true" aria-label="Search store"
     class="hidden fixed inset-0 z-50 flex items-start justify-center p-4 pt-16 sm:pt-24">
    <div class="fixed inset-0 bg-black/60" data-k71-close="k71-search-modal"></div>
    <div class="relative z-10 w-full max-w-xl p-6 shadow-2xl k71-glass" style="border-radius: var(--t-radius);">
        <div class="flex items-center justify-between mb-4">
            <h2 class="k71-heading text-lg">Search {{ $storeName }}</h2>
            <button type="button" data-k71-close="k71-search-modal" class="min-w-[44px] min-h-[44px] flex items-center justify-center" aria-label="Close search">✕</button>
        </div>
        <form action="{{ $storeCollectionUrl }}" method="GET" class="flex flex-col sm:flex-row gap-2.5">
            @if(!empty($isCataloguePreview))
                <input type="hidden" name="page" value="collection">
            @endif
            <input type="search" name="search" value="{{ request('search') }}" placeholder="Search products, materials, categories..."
                   class="flex-1 min-h-[44px] px-4 text-sm rounded-lg"
                   style="background-color: var(--t-bg); color: var(--t-text); border: 1px solid var(--t-border);">
            <button type="submit" class="k71-btn k71-btn-primary shrink-0">Search</button>
        </form>
    </div>
</div>

{{-- Image Lightbox Modal --}}
<div id="k71-lightbox-modal" data-k71-modal aria-hidden="true" role="dialog" aria-modal="true" aria-label="Image preview"
     class="hidden fixed inset-0 z-50 flex items-center justify-center p-4">
    <div class="fixed inset-0 bg-black/85" data-k71-close="k71-lightbox-modal"></div>
    <div class="relative z-10 max-w-4xl w-full flex flex-col items-center gap-3">
        <button type="button" data-k71-close="k71-lightbox-modal" class="self-end min-w-[44px] min-h-[44px] px-3 rounded-full bg-white/15 text-white font-bold" aria-label="Close image preview">✕</button>
        <img id="k71-lightbox-img" src="" alt="" class="max-h-[80vh] max-w-full object-contain rounded-xl shadow-2xl">
        <p id="k71-lightbox-caption" class="text-xs text-white/80 text-center"></p>
    </div>
</div>

{{-- Optional Vendor-Controlled Promotional Popup (Off by default, frequency-capped) --}}
@php $popupCfg = $themeConfig['popup'] ?? []; @endphp
@if(!empty($popupCfg['enabled']) && (!empty($popupCfg['heading']) || !empty($popupCfg['body'])) && empty($isCataloguePreview))
    <div id="k71-promo-modal" data-k71-modal data-enabled="true" data-vendor-id="{{ $vendor->id }}" data-delay="{{ (int) ($popupCfg['delay_seconds'] ?? 8) }}"
         aria-hidden="true" role="dialog" aria-modal="true" aria-label="Store announcement"
         class="hidden fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="fixed inset-0 bg-black/60" data-k71-close="k71-promo-modal"></div>
        <div class="relative z-10 w-full max-w-md p-6 sm:p-8 shadow-2xl k71-glass space-y-4" style="border-radius: var(--t-radius);">
            <div class="flex items-start justify-between gap-3">
                <h2 class="k71-heading text-2xl">{{ $popupCfg['heading'] }}</h2>
                <button type="button" data-k71-close="k71-promo-modal" class="min-w-[44px] min-h-[44px] -mt-2 -mr-2 flex items-center justify-center" aria-label="Close announcement">✕</button>
            </div>
            @if(!empty($popupCfg['body']))
                <p class="text-sm leading-relaxed" style="color: var(--t-muted);">{{ $popupCfg['body'] }}</p>
            @endif
            @if(!empty($popupCfg['cta_label']) && !empty($popupCfg['cta_url']))
                <a href="{{ $popupCfg['cta_url'] }}" class="k71-btn k71-btn-primary w-full">{{ $popupCfg['cta_label'] }}</a>
            @endif
        </div>
    </div>
@endif

{{-- Live Cart / Action Status Toast --}}
<div id="k71-theme-toast" role="status" aria-live="polite"
     class="fixed bottom-5 right-5 z-50 max-w-xs px-4 py-3 rounded-xl shadow-2xl text-xs font-semibold transition-all duration-300 opacity-0 translate-y-4 pointer-events-none"
     style="background-color: var(--t-text); color: var(--t-bg);">
    <span id="k71-theme-toast-text"></span>
</div>
