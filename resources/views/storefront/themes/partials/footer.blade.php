@php
    $footerVariant = $manifest['layout']['footer_variant'] ?? 'editorial-compact';
    $footerCfg = $themeConfig['footer'] ?? [];
    $aboutText = trim((string) ($footerCfg['about_text'] ?? '')) ?: \Illuminate\Support\Str::limit(strip_tags((string) ($vendor->description ?? '')), 200);
    $copyrightText = trim((string) ($footerCfg['copyright_text'] ?? '')) ?: ('© ' . date('Y') . ' ' . $storeName . ' · Powered by K71 Marketplace.');
    $showPolicies = array_key_exists('show_policies', $footerCfg) ? (bool) $footerCfg['show_policies'] : true;
    $showContact = array_key_exists('show_contact', $footerCfg) ? (bool) $footerCfg['show_contact'] : true;
@endphp

<footer class="w-full mt-12 k71-safe-bottom"
        style="border-top: 1px solid var(--t-border); background-color: {{ $footerVariant === 'dark-structured' ? '#09090b' : 'var(--t-surface)' }}; color: {{ $footerVariant === 'dark-structured' ? '#f4f4f5' : 'var(--t-text)' }};">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 sm:py-14">
        @if($footerVariant === 'signature-71')
            <div class="pb-8 mb-8 flex flex-col md:flex-row md:items-end justify-between gap-6" style="border-bottom: 1px solid var(--t-border);">
                <div class="space-y-3 max-w-xl">
                    <p class="k71-eyebrow" style="color: var(--t-accent);">Signature Storefront · K71</p>
                    <h2 class="k71-heading text-3xl sm:text-5xl">{{ $storeName }}</h2>
                    @if($aboutText)
                        <p class="text-sm leading-relaxed" style="color: var(--t-muted);">{{ $aboutText }}</p>
                    @endif
                </div>
                <div class="flex flex-wrap gap-3">
                    <a href="{{ $storeCollectionUrl }}" class="k71-btn k71-btn-primary">Shop Collection</a>
                    <a href="{{ route('cart.index') }}" class="k71-btn k71-btn-outline">View Bag</a>
                </div>
            </div>
        @endif

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8">
            <div class="space-y-3 {{ $footerVariant === 'signature-71' ? 'lg:col-span-1' : 'lg:col-span-2' }}">
                <a href="{{ $storeHomeUrl }}" class="k71-heading text-xl inline-block">{{ $storeName }}</a>
                @if($aboutText && $footerVariant !== 'signature-71')
                    <p class="text-xs sm:text-sm leading-relaxed max-w-md opacity-80">{{ $aboutText }}</p>
                @endif
                @if($vendor->city)
                    <p class="text-xs opacity-70">{{ $vendor->city }}{{ $vendor->state ? ', ' . $vendor->state : '' }}</p>
                @endif
            </div>

            <div class="space-y-2.5">
                <h3 class="k71-eyebrow opacity-70">Store Navigation</h3>
                <ul class="space-y-2 text-xs sm:text-sm">
                    <li><a href="{{ $storeHomeUrl }}" class="hover:opacity-75">Home</a></li>
                    <li><a href="{{ $storeCollectionUrl }}" class="hover:opacity-75">All Products</a></li>
                    <li><a href="{{ route('cart.index') }}" class="hover:opacity-75">Shopping Bag</a></li>
                    <li><a href="{{ url('/') }}" class="hover:opacity-75">K71 Marketplace</a></li>
                </ul>
            </div>

            @if($showPolicies || $showContact)
                <div class="space-y-2.5">
                    <h3 class="k71-eyebrow opacity-70">Information</h3>
                    <ul class="space-y-2 text-xs sm:text-sm opacity-85">
                        @if($showPolicies && $vendor->settings?->return_window_days)
                            <li>{{ $vendor->settings->return_window_days }}-Day Return Policy</li>
                        @endif
                        @if($showContact && $vendor->contact_phone)
                            <li>Phone: {{ $vendor->contact_phone }}</li>
                        @endif
                        @if($showContact && $vendor->contact_email)
                            <li>Email: {{ $vendor->contact_email }}</li>
                        @endif
                        @if($showContact && auth()->check())
                            <li><a href="{{ route('messages.index') }}" class="underline underline-offset-4 hover:opacity-75">Message Seller</a></li>
                        @endif
                    </ul>
                </div>
            @endif
        </div>

        <div class="mt-10 pt-6 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs opacity-70" style="border-top: 1px solid var(--t-border);">
            <p>{{ $copyrightText }}</p>
            <p class="k71-eyebrow">Theme: {{ $manifest['name'] }}</p>
        </div>
    </div>
</footer>
