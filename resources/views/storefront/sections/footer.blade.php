@php
    $footerCfg = $config['footer'] ?? [];
    $brandCfg = $config['brand'] ?? [];
    
    $storeName = $footerCfg['about_title'] ?? $brandCfg['store_name'] ?? $vendor->shop_name;
    $aboutText = $footerCfg['about_text'] ?? $brandCfg['description'] ?? $vendor->description ?? '';
    $bgColor = $footerCfg['bg_color'] ?? '#09090b';
    $textColor = $footerCfg['text_color'] ?? '#f4f4f5';
    $copyrightText = $footerCfg['copyright_text'] ?? ("© " . date('Y') . " {$storeName} · Powered by K71 Multi-Vendor Marketplace.");
    $showNewsletter = $footerCfg['show_newsletter'] ?? true;
    $newsTitle = $footerCfg['newsletter_title'] ?? 'Curated Drops & Private Offers';
    $newsSubtext = $footerCfg['newsletter_subtext'] ?? "Subscribe to receive timely updates on new releases, exclusive discounts, and seasonal collections from {$storeName}.";

    $waPhone = preg_replace('/[^0-9]/', '', (string) ($brandCfg['whatsapp'] ?? $vendor->contact_phone));
    if (strlen($waPhone) === 10) { $waPhone = '91' . $waPhone; }
    $waUrl = $waPhone ? "https://wa.me/{$waPhone}" : null;
@endphp

<!-- GIANT K71 BLINKIT / ANTIGRAVITY MEGA FOOTER -->
<footer id="storefront-footer" 
        data-section-id="footer"
        class="bg-[#09090b] text-zinc-300 border-t border-zinc-800/80 mt-16 sm:mt-24 rounded-t-[2.5rem] shadow-2xl relative overflow-hidden z-20">

    <!-- 1. BLINKIT-STYLE TRUST & SHOPPING GUARANTEES PILLARS STRIP -->
    <div class="border-b border-zinc-800/80 bg-zinc-950/70 py-8 px-4 sm:px-6 lg:px-8">
        <div class="max-w-7xl mx-auto grid grid-cols-2 lg:grid-cols-4 gap-6 sm:gap-8">
            
            <div class="flex items-center gap-3.5">
                <div class="w-10 h-10 rounded-2xl bg-amber-500/10 border border-amber-500/25 flex items-center justify-center text-amber-400 shrink-0 shadow-inner">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                </div>
                <div>
                    <h5 class="text-xs sm:text-sm font-bold text-white tracking-tight">Verified Seller Protection</h5>
                    <p class="text-[11px] text-zinc-400 mt-0.5">100% authentic item guarantee</p>
                </div>
            </div>

            <div class="flex items-center gap-3.5">
                <div class="w-10 h-10 rounded-2xl bg-sky-500/10 border border-sky-500/25 flex items-center justify-center text-sky-400 shrink-0 shadow-inner">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                </div>
                <div>
                    <h5 class="text-xs sm:text-sm font-bold text-white tracking-tight">Direct Studio Dispatch</h5>
                    <p class="text-[11px] text-zinc-400 mt-0.5">Packaged directly from {{ $vendor->city ?: 'the vendor atelier' }}</p>
                </div>
            </div>

            <div class="flex items-center gap-3.5">
                <div class="w-10 h-10 rounded-2xl bg-emerald-500/10 border border-emerald-500/25 flex items-center justify-center text-emerald-400 shrink-0 shadow-inner">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                </div>
                <div>
                    <h5 class="text-xs sm:text-sm font-bold text-white tracking-tight">Easy Size Exchange</h5>
                    <p class="text-[11px] text-zinc-400 mt-0.5">{{ $vendor->settings?->return_window_days ?: 7 }}-day direct merchant support</p>
                </div>
            </div>

            <div class="flex items-center gap-3.5">
                <div class="w-10 h-10 rounded-2xl bg-indigo-500/10 border border-indigo-500/25 flex items-center justify-center text-indigo-400 shrink-0 shadow-inner">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                </div>
                <div>
                    <h5 class="text-xs sm:text-sm font-bold text-white tracking-tight">100% Secure Checkout</h5>
                    <p class="text-[11px] text-zinc-400 mt-0.5">Encrypted UPI, Cards &amp; COD</p>
                </div>
            </div>

        </div>
    </div>

    @if($showNewsletter)
    <!-- 2. NEWSLETTER SUBSCRIPTION SECTION -->
    <div class="border-b border-zinc-800/80 py-10 px-4 sm:px-6 lg:px-8 relative z-10"
         x-data="{ email: '', subscribed: false }">
        <div class="max-w-7xl mx-auto flex flex-col lg:flex-row items-center justify-between gap-8">
            <div class="text-center lg:text-left max-w-xl">
                <span class="text-[10px] font-black uppercase tracking-widest text-amber-400 flex items-center justify-center lg:justify-start gap-1.5 mb-1.5">
                    <span>✦ Private Boutique Dispatch</span>
                </span>
                <h3 class="text-xl sm:text-2xl font-black text-white tracking-tight font-heading">{{ $newsTitle }}</h3>
                <p class="text-xs sm:text-sm text-zinc-400 mt-1 leading-relaxed">
                    {{ $newsSubtext }}
                </p>
            </div>

            <!-- Form -->
            <div class="w-full sm:w-auto flex-1 max-w-md">
                <form @submit.prevent="subscribed = true" class="flex flex-col sm:flex-row gap-2">
                    <div class="relative flex-1">
                        <input type="email" 
                               x-model="email" 
                               required
                               placeholder="Enter your email address..." 
                               class="w-full px-4 py-3 rounded-full bg-zinc-900 border border-zinc-700 text-white placeholder-zinc-500 text-xs focus:ring-1 focus:ring-white focus:border-white outline-none transition-all">
                    </div>
                    <button type="submit" 
                            class="px-6 py-3 rounded-full bg-white hover:bg-zinc-100 text-zinc-950 font-bold text-xs tracking-wider uppercase transition-all shadow-md shrink-0 active:scale-95 cursor-pointer">
                        Subscribe
                    </button>
                </form>
                <div x-show="subscribed" x-cloak class="mt-2 text-xs font-semibold text-emerald-400 flex items-center gap-1.5">
                    <span>✓ Subscribed! You will receive new drop alerts from {{ $storeName }}.</span>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- 3. GIANT 5-COLUMN MEGA NAVIGATION RAIL -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 sm:py-16 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-8 lg:gap-10 relative z-10">
        
        <!-- Column 1: Store Brand & Compliance Card -->
        <div class="lg:col-span-2 space-y-4">
            <div class="flex items-center gap-3">
                @if(!empty($brandCfg['logo_url'] ?? $vendor->logo_url))
                    <img src="{{ $brandCfg['logo_url'] ?? $vendor->logo_url }}" alt="{{ $storeName }}" class="h-10 w-auto object-contain rounded-xl bg-white/10 p-1">
                @endif
                <div>
                    <h4 class="font-black font-heading text-xl text-white tracking-tight leading-none">{{ $storeName }}</h4>
                    <span class="text-[10px] font-mono text-zinc-400 uppercase tracking-widest mt-1 block">
                        Verified K71 Boutique · {{ $vendor->city ?: 'India' }}
                    </span>
                </div>
            </div>

            <p class="text-xs text-zinc-400 leading-relaxed max-w-sm">
                {{ Str::limit($aboutText ?: 'Direct seller boutique on the K71 Multi-Vendor Marketplace, bringing authentic artisan drops with nationwide delivery and buyer protection.', 220) }}
            </p>

            <!-- Verified Merchant & Platform Compliance Card -->
            <div class="p-3.5 rounded-2xl bg-zinc-900/90 border border-zinc-800 text-xs space-y-2 max-w-sm">
                <div class="flex items-center justify-between text-[11px]">
                    <span class="text-zinc-500 uppercase tracking-wider text-[10px]">Merchant Status:</span>
                    <span class="font-bold text-emerald-400 flex items-center gap-1">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                        Verified Active
                    </span>
                </div>
                <div class="flex items-center justify-between text-[11px]">
                    <span class="text-zinc-500 uppercase tracking-wider text-[10px]">Platform Operator:</span>
                    <span class="font-medium text-zinc-200">K71.IN (Director: Hashir)</span>
                </div>
                <div class="flex items-center justify-between text-[11px]">
                    <span class="text-zinc-500 uppercase tracking-wider text-[10px]">MSME Registration:</span>
                    <span class="font-mono text-zinc-300 font-semibold">UDYAM-RJ-01-0176167</span>
                </div>
                <div class="border-t border-zinc-800/80 my-1"></div>
                <div class="flex items-center justify-between text-[11px] pt-0.5">
                    <a href="tel:+919549311580" class="text-zinc-400 hover:text-white font-mono flex items-center gap-1 transition-colors">
                        <span>📞 +91 95493 11580</span>
                    </a>
                    <a href="mailto:crab.hashir@gmail.com" class="text-zinc-400 hover:text-white font-mono flex items-center gap-1 transition-colors">
                        <span>✉️ Support Desk</span>
                    </a>
                </div>
            </div>
        </div>

        <!-- Column 2: Store Collections -->
        <div>
            <h4 class="text-xs font-bold tracking-wider uppercase text-zinc-200 mb-4 pb-1 border-b border-zinc-800/60 inline-block">
                Boutique Drops
            </h4>
            <ul class="space-y-2.5 text-xs text-zinc-400">
                <li><a href="{{ $vendor->storefrontUrl([], 'catalog') }}" class="hover:text-white transition-colors">All Boutique Drops ({{ $products->total() ?? 0 }})</a></li>
                @foreach($vendorCategories->take(6) as $cat)
                    <li>
                        <a href="{{ $vendor->storefrontUrl(['category' => $cat->slug], 'catalog') }}" class="hover:text-white transition-colors flex items-center justify-between">
                            <span class="truncate">{{ $cat->name }}</span>
                            <span class="text-[10px] text-zinc-500 font-mono">{{ $cat->products_count }}</span>
                        </a>
                    </li>
                @endforeach
            </ul>
        </div>

        <!-- Column 3: Customer Care & Policies -->
        <div>
            <h4 class="text-xs font-bold tracking-wider uppercase text-zinc-200 mb-4 pb-1 border-b border-zinc-800/60 inline-block">
                Customer Care
            </h4>
            <ul class="space-y-2.5 text-xs text-zinc-400">
                @if($waUrl)
                    <li>
                        <a href="{{ $waUrl }}" target="_blank" rel="noopener" class="text-emerald-400 hover:text-emerald-300 font-semibold flex items-center gap-1 transition">
                            <span>💬 WhatsApp Stylist</span>
                        </a>
                    </li>
                @endif
                <li><a href="{{ route('orders.index') }}" class="hover:text-white transition-colors">Track Order</a></li>
                <li><a href="{{ route('pages.shipping') }}" class="hover:text-white transition-colors">Shipping Policies</a></li>
                <li><a href="{{ route('pages.refund') }}" class="hover:text-white transition-colors">Refund &amp; Return</a></li>
                <li><a href="{{ route('pages.contact') }}" class="hover:text-white transition-colors">Merchant Grievance Desk</a></li>
                <li><a href="{{ route('pages.terms') }}" class="hover:text-white transition-colors">Terms of Service</a></li>
            </ul>
        </div>

        <!-- Column 4: K71 Marketplace Network -->
        <div>
            <h4 class="text-xs font-bold tracking-wider uppercase text-zinc-200 mb-4 pb-1 border-b border-zinc-800/60 inline-block">
                K71 Network
            </h4>
            <ul class="space-y-2.5 text-xs text-zinc-400">
                <li><a href="{{ route('stores.index') }}" class="text-amber-400 hover:text-amber-300 font-semibold flex items-center gap-1 transition"><span>🏪 All K71 Stores</span></a></li>
                <li><a href="{{ route('catalog.index') }}" class="hover:text-white transition-colors">Marketplace Catalog</a></li>
                <li><a href="{{ route('shorts.index') }}" class="hover:text-white transition-colors">Video Commerce Shorts</a></li>
                <li><a href="{{ route('vendor.register') }}" class="hover:text-white transition-colors">Open Your Seller Store</a></li>
                <li><a href="{{ route('login') }}" class="hover:text-white transition-colors">Merchant Dashboard</a></li>
            </ul>
        </div>

    </div>

    <!-- 4. BLINKIT-STYLE SUPPORTED PAYMENTS RAIL -->
    <div class="border-t border-zinc-800/80 py-4 px-4 sm:px-6 lg:px-8 bg-zinc-950/80">
        <div class="max-w-7xl mx-auto flex flex-col sm:flex-row items-center justify-between gap-3 text-center sm:text-left">
            <div class="flex items-center gap-2">
                <span class="text-[10px] uppercase font-bold tracking-wider text-zinc-500">Supported Payments:</span>
                <span class="text-[10px] text-zinc-400 font-mono">256-Bit SSL Encrypted</span>
            </div>
            <div class="flex flex-wrap items-center justify-center gap-1.5 text-[10px] font-bold text-zinc-300">
                <span class="px-2.5 py-1 rounded-md bg-zinc-900 border border-zinc-800">UPI</span>
                <span class="px-2.5 py-1 rounded-md bg-zinc-900 border border-zinc-800">PhonePe</span>
                <span class="px-2.5 py-1 rounded-md bg-zinc-900 border border-zinc-800">Google Pay</span>
                <span class="px-2.5 py-1 rounded-md bg-zinc-900 border border-zinc-800">Paytm</span>
                <span class="px-2.5 py-1 rounded-md bg-zinc-900 border border-zinc-800">Visa / Mastercard</span>
                <span class="px-2.5 py-1 rounded-md bg-zinc-900 border border-zinc-800">Cash on Delivery</span>
            </div>
        </div>
    </div>

    <!-- 5. BOTTOM LEGAL RAIL & COPYRIGHT -->
    <div class="border-t border-zinc-800/80 py-6 px-4 sm:px-6 lg:px-8 bg-black">
        <div class="max-w-7xl mx-auto flex flex-col sm:flex-row items-center justify-between gap-4 text-xs text-zinc-500">
            <div class="text-center sm:text-left">
                <span>{{ $copyrightText }}</span>
            </div>

            <div class="flex flex-wrap items-center justify-center sm:justify-end gap-3.5 text-xs">
                <a href="{{ route('pages.privacy') }}" class="hover:text-zinc-300 transition-colors">Privacy</a>
                <span class="text-zinc-700">•</span>
                <a href="{{ route('pages.terms') }}" class="hover:text-zinc-300 transition-colors">Terms</a>
                <span class="text-zinc-700">•</span>
                <a href="{{ route('pages.refund') }}" class="hover:text-zinc-300 transition-colors">Refunds</a>
                <span class="text-zinc-700">•</span>
                <a href="{{ route('pages.shipping') }}" class="hover:text-zinc-300 transition-colors">Shipping</a>
                <span class="text-zinc-700">•</span>
                <a href="{{ route('pages.contact') }}" class="hover:text-zinc-300 transition-colors">Contact</a>
            </div>
        </div>
    </div>

</footer>
