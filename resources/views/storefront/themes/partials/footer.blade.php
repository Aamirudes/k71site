@php
    $footerTemplate = $manifest['layout']['footer_template'] ?? ($manifest['layout']['footer_variant'] ?? 'maison-minimal');
    $footerCfg = $themeConfig['footer'] ?? [];
    $aboutText = trim((string) ($footerCfg['about_text'] ?? '')) ?: \Illuminate\Support\Str::limit(strip_tags((string) ($vendor->description ?? '')), 200);
    $copyrightText = trim((string) ($footerCfg['copyright_text'] ?? '')) ?: ('© ' . date('Y') . ' ' . $storeName . ' · Powered by K71 Marketplace.');
    $showPolicies = array_key_exists('show_policies', $footerCfg) ? (bool) $footerCfg['show_policies'] : true;
    $showContact = array_key_exists('show_contact', $footerCfg) ? (bool) $footerCfg['show_contact'] : true;
    $isDarkFooter = in_array($footerTemplate, ['obsidian-architectural', 'dark-structured', 'monolith-dark', 'technical-footer'], true);
    $footerBg = $isDarkFooter ? '#09090b' : 'var(--t-surface)';
    $footerFg = $isDarkFooter ? '#f4f4f5' : 'var(--t-text)';
    $footerMuted = $isDarkFooter ? '#a1a1aa' : 'var(--t-muted)';
    $footerBorder = $isDarkFooter ? 'rgba(255,255,255,0.12)' : 'var(--t-border)';
@endphp

<footer class="w-full mt-14 k71-safe-bottom"
        style="border-top: 1px solid {{ $footerBorder }}; background-color: {{ $footerBg }}; color: {{ $footerFg }};">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 sm:py-14">

        @if(in_array($footerTemplate, ['maison-minimal', 'editorial-minimal'], true))
            {{-- Minimalist Luxury Maison Footer: Oversized wordmark + slim balanced columns --}}
            <div class="pb-10 mb-8 flex flex-col lg:flex-row lg:items-end justify-between gap-6" style="border-bottom: 1px solid {{ $footerBorder }};">
                <div class="space-y-2.5 max-w-xl">
                    <p class="k71-eyebrow" style="color: var(--t-accent);">{{ $manifest['category_label'] ?? 'Maison Storefront' }}</p>
                    <a href="{{ $storeHomeUrl }}" class="k71-heading text-3xl sm:text-5xl block tracking-tight">{{ $storeName }}</a>
                    @if($aboutText)
                        <p class="text-xs sm:text-sm leading-relaxed" style="color: {{ $footerMuted }};">{{ $aboutText }}</p>
                    @endif
                </div>
                <div class="flex flex-wrap items-center gap-3">
                    <a href="{{ $storeCollectionUrl }}" class="k71-btn k71-btn-primary">
                        <span>Explore Catalogue</span>
                        @include('storefront.themes.partials.icon', ['name' => 'arrow-right', 'class' => 'w-3.5 h-3.5'])
                    </a>
                    <a href="{{ route('cart.index') }}" class="k71-btn k71-btn-outline">View Bag</a>
                </div>
            </div>

        @elseif(in_array($footerTemplate, ['colophon-hairline', 'gallery-colophon'], true))
            {{-- Architectural Gallery Colophon: Hairline split metadata row --}}
            <div class="grid grid-cols-1 md:grid-cols-12 gap-8 pb-8 mb-8" style="border-bottom: 1px solid {{ $footerBorder }};">
                <div class="md:col-span-6 space-y-2">
                    <p class="k71-eyebrow" style="color: {{ $footerMuted }};">Colophon / Index</p>
                    <a href="{{ $storeHomeUrl }}" class="k71-heading text-2xl sm:text-3xl inline-block">{{ $storeName }}</a>
                    @if($aboutText)
                        <p class="text-xs sm:text-sm leading-relaxed max-w-lg" style="color: {{ $footerMuted }};">{{ $aboutText }}</p>
                    @endif
                </div>
                <div class="md:col-span-6 grid grid-cols-2 gap-6 text-xs font-mono">
                    <div class="space-y-2">
                        <p class="k71-eyebrow" style="color: {{ $footerMuted }};">Archive</p>
                        <ul class="space-y-1.5">
                            <li><a href="{{ $storeHomeUrl }}" class="hover:opacity-75">Index</a></li>
                            <li><a href="{{ $storeCollectionUrl }}" class="hover:opacity-75">Catalogue</a></li>
                            <li><a href="{{ route('cart.index') }}" class="hover:opacity-75">Bag</a></li>
                        </ul>
                    </div>
                    <div class="space-y-2">
                        <p class="k71-eyebrow" style="color: {{ $footerMuted }};">Concierge</p>
                        <ul class="space-y-1.5" style="color: {{ $footerMuted }};">
                            @if($showPolicies && $vendor->settings?->return_window_days)
                                <li>{{ $vendor->settings->return_window_days }}-Day Returns</li>
                            @endif
                            @if($showContact && $vendor->contact_phone)
                                <li>{{ $vendor->contact_phone }}</li>
                            @endif
                            @if($showContact && $vendor->contact_email)
                                <li>{{ $vendor->contact_email }}</li>
                            @endif
                            @if($showContact && auth()->check())
                                <li><a href="{{ route('messages.index') }}" class="underline underline-offset-4" style="color: {{ $footerFg }};">Direct Inquiry</a></li>
                            @endif
                        </ul>
                    </div>
                </div>
            </div>

        @elseif($footerTemplate === 'obsidian-architectural' || $isDarkFooter)
            {{-- Dark Architectural / Technical Monolith Footer --}}
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 pb-10 mb-8" style="border-bottom: 1px solid {{ $footerBorder }};">
                <div class="lg:col-span-5 space-y-3">
                    <span class="inline-flex items-center gap-2 text-[10px] font-bold uppercase tracking-[0.22em]" style="color: var(--t-accent);">
                        @include('storefront.themes.partials.icon', ['name' => 'shield', 'class' => 'w-3.5 h-3.5'])
                        <span>Verified Flagship · K71</span>
                    </span>
                    <a href="{{ $storeHomeUrl }}" class="k71-heading text-3xl sm:text-4xl block text-white">{{ $storeName }}</a>
                    @if($aboutText)
                        <p class="text-xs sm:text-sm leading-relaxed text-zinc-400 max-w-md">{{ $aboutText }}</p>
                    @endif
                    @if($vendor->city)
                        <p class="text-[11px] uppercase tracking-widest text-zinc-500">{{ $vendor->city }}{{ $vendor->state ? ', ' . $vendor->state : '' }}</p>
                    @endif
                </div>
                <div class="lg:col-span-3 space-y-3">
                    <h3 class="k71-eyebrow text-zinc-500">Navigation</h3>
                    <ul class="space-y-2 text-xs sm:text-sm text-zinc-300">
                        <li><a href="{{ $storeHomeUrl }}" class="hover:text-white transition-colors">Storefront Home</a></li>
                        <li><a href="{{ $storeCollectionUrl }}" class="hover:text-white transition-colors">All Releases</a></li>
                        <li><a href="{{ route('cart.index') }}" class="hover:text-white transition-colors">Shopping Bag</a></li>
                        <li><a href="{{ url('/') }}" class="hover:text-white transition-colors">K71 Marketplace</a></li>
                    </ul>
                </div>
                <div class="lg:col-span-4 space-y-3">
                    <h3 class="k71-eyebrow text-zinc-500">Client Services</h3>
                    <ul class="space-y-2 text-xs sm:text-sm text-zinc-400">
                        @if($showPolicies && $vendor->settings?->return_window_days)
                            <li>{{ $vendor->settings->return_window_days }}-Day Verified Return Window</li>
                        @endif
                        @if($showContact && $vendor->contact_phone)
                            <li>Tel: {{ $vendor->contact_phone }}</li>
                        @endif
                        @if($showContact && $vendor->contact_email)
                            <li>Mail: {{ $vendor->contact_email }}</li>
                        @endif
                        @if($showContact && auth()->check())
                            <li><a href="{{ route('messages.index') }}" class="inline-flex items-center gap-1.5 text-white underline underline-offset-4">Message Concierge</a></li>
                        @endif
                    </ul>
                </div>
            </div>

        @elseif($footerTemplate === 'heritage-columns')
            {{-- Heritage Framed Multi-Column House Footer --}}
            <div class="p-6 sm:p-8 mb-6" style="border: 1px solid {{ $footerBorder }};">
                <div class="grid grid-cols-1 md:grid-cols-12 gap-8">
                    <div class="md:col-span-5 space-y-3">
                        <p class="k71-eyebrow" style="color: var(--t-accent);">House Registry</p>
                        <a href="{{ $storeHomeUrl }}" class="k71-heading text-2xl sm:text-3xl block">{{ $storeName }}</a>
                        @if($aboutText)
                            <p class="text-xs sm:text-sm leading-relaxed" style="color: {{ $footerMuted }};">{{ $aboutText }}</p>
                        @endif
                        @if($vendor->city)
                            <p class="text-xs" style="color: {{ $footerMuted }};">{{ $vendor->city }}{{ $vendor->state ? ', ' . $vendor->state : '' }}</p>
                        @endif
                    </div>
                    <div class="md:col-span-3 space-y-2.5">
                        <h3 class="k71-eyebrow" style="color: {{ $footerMuted }};">Departments</h3>
                        <ul class="space-y-2 text-xs sm:text-sm">
                            <li><a href="{{ $storeHomeUrl }}" class="hover:opacity-75">Front Page</a></li>
                            <li><a href="{{ $storeCollectionUrl }}" class="hover:opacity-75">Complete Catalogue</a></li>
                            @foreach(($vendorCategories ?? collect())->take(3) as $fCat)
                                <li><a href="{{ $storeCollectionUrl }}{{ str_contains($storeCollectionUrl, '?') ? '&' : '?' }}category={{ urlencode($fCat->slug) }}" class="hover:opacity-75">{{ $fCat->name }}</a></li>
                            @endforeach
                        </ul>
                    </div>
                    <div class="md:col-span-4 space-y-2.5">
                        <h3 class="k71-eyebrow" style="color: {{ $footerMuted }};">Customer Care</h3>
                        <ul class="space-y-2 text-xs sm:text-sm" style="color: {{ $footerMuted }};">
                            @if($showPolicies && $vendor->settings?->return_window_days)
                                <li>{{ $vendor->settings->return_window_days }}-Day Return Guarantee</li>
                            @endif
                            @if($showContact && $vendor->contact_phone)
                                <li>Telephone: {{ $vendor->contact_phone }}</li>
                            @endif
                            @if($showContact && $vendor->contact_email)
                                <li>Correspondence: {{ $vendor->contact_email }}</li>
                            @endif
                            <li><a href="{{ route('cart.index') }}" class="underline underline-offset-4" style="color: {{ $footerFg }};">View Shopping Bag</a></li>
                        </ul>
                    </div>
                </div>
            </div>

        @elseif($footerTemplate === 'utility-directory')
            {{-- Pro Commercial Department Directory Footer --}}
            <div class="grid grid-cols-2 md:grid-cols-4 gap-6 sm:gap-8 pb-8 mb-6" style="border-bottom: 1px solid {{ $footerBorder }};">
                <div class="space-y-3 col-span-2 sm:col-span-1">
                    <span class="text-[10px] font-mono uppercase tracking-widest font-bold" style="color: var(--t-accent);">COMMERCIAL CATALOGUE</span>
                    <a href="{{ $storeHomeUrl }}" class="k71-heading text-xl font-bold block">{{ $storeName }}</a>
                    @if($aboutText)
                        <p class="text-xs leading-relaxed" style="color: {{ $footerMuted }};">{{ $aboutText }}</p>
                    @endif
                </div>
                <div class="space-y-2">
                    <h3 class="k71-eyebrow" style="color: {{ $footerMuted }};">Catalogue Index</h3>
                    <ul class="space-y-1.5 text-xs">
                        <li><a href="{{ $storeHomeUrl }}" class="hover:opacity-75">Overview</a></li>
                        <li><a href="{{ $storeCollectionUrl }}" class="hover:opacity-75">All Departments</a></li>
                        @foreach(($vendorCategories ?? collect())->take(4) as $cat)
                            <li><a href="{{ $storeCollectionUrl }}{{ str_contains($storeCollectionUrl, '?') ? '&' : '?' }}category={{ urlencode($cat->slug) }}" class="hover:opacity-75 truncate block">{{ $cat->name }}</a></li>
                        @endforeach
                    </ul>
                </div>
                <div class="space-y-2">
                    <h3 class="k71-eyebrow" style="color: {{ $footerMuted }};">Buyer Protection</h3>
                    <ul class="space-y-1.5 text-xs" style="color: {{ $footerMuted }};">
                        <li>Verified Merchant</li>
                        <li>Encrypted Checkout</li>
                        @if($showPolicies && $vendor->settings?->return_window_days)
                            <li>{{ $vendor->settings->return_window_days }}-Day Return Window</li>
                        @endif
                        <li>GST / Tax Invoice Ready</li>
                    </ul>
                </div>
                <div class="space-y-2">
                    <h3 class="k71-eyebrow" style="color: {{ $footerMuted }};">Trade Dispatch</h3>
                    <ul class="space-y-1.5 text-xs" style="color: {{ $footerMuted }};">
                        @if($vendor->city)
                            <li>Hub: {{ $vendor->city }}</li>
                        @endif
                        @if($showContact && $vendor->contact_email)
                            <li>{{ $vendor->contact_email }}</li>
                        @endif
                        <li><a href="{{ route('cart.index') }}" class="underline underline-offset-4" style="color: {{ $footerFg }};">Order Summary</a></li>
                    </ul>
                </div>
            </div>

        @else
            {{-- Structured Studio / Warm Editorial Footer --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8 pb-8">
                <div class="space-y-3 lg:col-span-2">
                    <a href="{{ $storeHomeUrl }}" class="k71-heading text-xl sm:text-2xl inline-block">{{ $storeName }}</a>
                    @if($aboutText)
                        <p class="text-xs sm:text-sm leading-relaxed max-w-md" style="color: {{ $footerMuted }};">{{ $aboutText }}</p>
                    @endif
                    @if($vendor->city)
                        <p class="text-xs" style="color: {{ $footerMuted }};">{{ $vendor->city }}{{ $vendor->state ? ', ' . $vendor->state : '' }}</p>
                    @endif
                </div>

                <div class="space-y-2.5">
                    <h3 class="k71-eyebrow" style="color: {{ $footerMuted }};">Store Navigation</h3>
                    <ul class="space-y-2 text-xs sm:text-sm">
                        <li><a href="{{ $storeHomeUrl }}" class="hover:opacity-75">Home</a></li>
                        <li><a href="{{ $storeCollectionUrl }}" class="hover:opacity-75">All Products</a></li>
                        <li><a href="{{ route('cart.index') }}" class="hover:opacity-75">Shopping Bag</a></li>
                        <li><a href="{{ url('/') }}" class="hover:opacity-75">K71 Marketplace</a></li>
                    </ul>
                </div>

                @if($showPolicies || $showContact)
                    <div class="space-y-2.5">
                        <h3 class="k71-eyebrow" style="color: {{ $footerMuted }};">Information</h3>
                        <ul class="space-y-2 text-xs sm:text-sm" style="color: {{ $footerMuted }};">
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
                                <li><a href="{{ route('messages.index') }}" class="underline underline-offset-4 hover:opacity-75" style="color: {{ $footerFg }};">Message Seller</a></li>
                            @endif
                        </ul>
                    </div>
                @endif
            </div>
        @endif

        @if(in_array($footerTemplate, ['maison-minimal', 'editorial-minimal'], true))
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-6 pb-8 text-xs sm:text-sm">
                <div class="space-y-2">
                    <h3 class="k71-eyebrow" style="color: {{ $footerMuted }};">Store</h3>
                    <ul class="space-y-1.5">
                        <li><a href="{{ $storeHomeUrl }}" class="hover:opacity-75">Home</a></li>
                        <li><a href="{{ $storeCollectionUrl }}" class="hover:opacity-75">All Products</a></li>
                        <li><a href="{{ url('/') }}" class="hover:opacity-75">K71 Marketplace</a></li>
                    </ul>
                </div>
                <div class="space-y-2">
                    <h3 class="k71-eyebrow" style="color: {{ $footerMuted }};">Policies</h3>
                    <ul class="space-y-1.5" style="color: {{ $footerMuted }};">
                        @if($showPolicies && $vendor->settings?->return_window_days)
                            <li>{{ $vendor->settings->return_window_days }}-Day Return Policy</li>
                        @else
                            <li>Verified K71 Merchant</li>
                        @endif
                        <li>Secure Checkout</li>
                    </ul>
                </div>
                <div class="space-y-2">
                    <h3 class="k71-eyebrow" style="color: {{ $footerMuted }};">Contact</h3>
                    <ul class="space-y-1.5" style="color: {{ $footerMuted }};">
                        @if($showContact && $vendor->contact_phone)
                            <li>{{ $vendor->contact_phone }}</li>
                        @endif
                        @if($showContact && $vendor->contact_email)
                            <li>{{ $vendor->contact_email }}</li>
                        @endif
                        @if($vendor->city)
                            <li>{{ $vendor->city }}{{ $vendor->state ? ', ' . $vendor->state : '' }}</li>
                        @endif
                    </ul>
                </div>
            </div>
        @endif

        <div class="pt-6 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs"
             style="border-top: 1px solid {{ $footerBorder }}; color: {{ $footerMuted }};">
            <p>{{ $copyrightText }}</p>
            <p class="k71-eyebrow">Theme: {{ $manifest['name'] }}</p>
        </div>
    </div>
</footer>
