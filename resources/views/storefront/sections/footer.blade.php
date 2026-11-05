@php
    $footerCfg = $config['footer'] ?? [];
    $brandCfg = $config['brand'] ?? [];
    
    $storeName = $footerCfg['about_title'] ?? $brandCfg['store_name'] ?? $vendor->shop_name;
    $aboutText = $footerCfg['about_text'] ?? $brandCfg['description'] ?? $vendor->description ?? '';
    $bgColor = $footerCfg['bg_color'] ?? '#0c1310';
    $textColor = $footerCfg['text_color'] ?? '#fafaf9';
    $copyrightText = $footerCfg['copyright_text'] ?? ("© " . date('Y') . " {$storeName}. Verified K71 Merchant.");
    $showNewsletter = $footerCfg['show_newsletter'] ?? true;
    $newsTitle = $footerCfg['newsletter_title'] ?? 'Private Studio Dispatches';
    $newsSubtext = $footerCfg['newsletter_subtext'] ?? 'Receive notifications on new atelier drops and private sales.';

    $instagram = $brandCfg['instagram'] ?? null;
    if ($instagram) {
        $instaHandle = ltrim($instagram, '@');
        $instaUrl = str_starts_with($instagram, 'http') ? $instagram : 'https://instagram.com/' . $instaHandle;
    } else {
        $instaUrl = null;
    }

    $waPhone = preg_replace('/[^0-9]/', '', (string) ($brandCfg['whatsapp'] ?? $vendor->contact_phone));
    if (strlen($waPhone) === 10) { $waPhone = '91' . $waPhone; }
    $waUrl = $waPhone ? "https://wa.me/{$waPhone}" : null;
@endphp

<footer id="storefront-footer" 
        data-section-id="footer"
        class="border-t border-stone-800 pt-16 pb-12 transition-colors relative z-20"
        style="background-color: {{ $bgColor }}; color: {{ $textColor }};">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <div class="grid grid-cols-1 md:grid-cols-12 gap-10 lg:gap-12 pb-12 border-b border-white/10">
            
            <!-- Column 1: Brand & Bio (4 cols) -->
            <div class="md:col-span-4 space-y-4">
                <a href="{{ route('storefront.show', $vendor->slug) }}" class="inline-block">
                    <span class="text-xl sm:text-2xl font-bold tracking-tight font-heading block text-white">
                        {{ $storeName }}
                    </span>
                    @if(!empty($brandCfg['city']))
                        <span class="text-[10px] uppercase tracking-widest text-stone-400 font-medium">
                            {{ $brandCfg['city'] }} · Studio
                        </span>
                    @endif
                </a>

                @if(!empty($aboutText))
                    <p class="text-xs sm:text-sm text-stone-300 leading-relaxed max-w-sm">
                        {{ Str::limit($aboutText, 220) }}
                    </p>
                @endif

                <!-- Social Links -->
                <div class="flex items-center gap-3 pt-2">
                    @if($waUrl)
                        <a href="{{ $waUrl }}" target="_blank" rel="noopener"
                           class="w-9 h-9 rounded-full bg-white/10 hover:bg-emerald-600 text-white flex items-center justify-center text-sm transition"
                           title="WhatsApp">
                            💬
                        </a>
                    @endif
                    @if($instaUrl)
                        <a href="{{ $instaUrl }}" target="_blank" rel="noopener"
                           class="w-9 h-9 rounded-full bg-white/10 hover:bg-pink-600 text-white flex items-center justify-center text-sm transition"
                           title="Instagram">
                            📷
                        </a>
                    @endif
                    <a href="{{ route('storefront.show', $vendor->slug) }}" 
                       class="w-9 h-9 rounded-full bg-white/10 hover:bg-white/20 text-white flex items-center justify-center text-sm transition"
                       title="Store Home">
                        🏬
                    </a>
                </div>
            </div>

            <!-- Column 2: Categories (3 cols) -->
            <div class="md:col-span-3 space-y-3">
                <h4 class="text-xs uppercase font-bold tracking-widest text-white/60">
                    Collections
                </h4>
                <ul class="space-y-2 text-xs sm:text-sm text-stone-300">
                    <li>
                        <a href="{{ route('storefront.show', $vendor->slug) }}" class="hover:text-white transition">
                            All Drops ({{ $products->total() ?? 0 }})
                        </a>
                    </li>
                    @foreach($vendorCategories->take(5) as $cat)
                        <li>
                            <a href="{{ route('storefront.show', ['slug' => $vendor->slug, 'category' => $cat->slug]) }}" class="hover:text-white transition">
                                {{ $cat->name }}
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>

            <!-- Column 3: Customer Care (2 cols) -->
            <div class="md:col-span-2 space-y-3">
                <h4 class="text-xs uppercase font-bold tracking-widest text-white/60">
                    Customer Care
                </h4>
                <ul class="space-y-2 text-xs sm:text-sm text-stone-300">
                    <li><a href="#catalog" class="hover:text-white transition">Catalog</a></li>
                    <li><a href="#trust" class="hover:text-white transition">Shipping &amp; Delivery</a></li>
                    <li><a href="#contact" class="hover:text-white transition">Help &amp; Inquiries</a></li>
                    <li><a href="{{ route('catalog.index') }}" class="hover:text-white transition">K71 Directory</a></li>
                </ul>
            </div>

            <!-- Column 4: Newsletter (3 cols) -->
            @if($showNewsletter)
            <div class="md:col-span-3 space-y-3">
                <h4 class="text-xs uppercase font-bold tracking-widest text-white/60">
                    {{ $newsTitle }}
                </h4>
                <p class="text-xs text-stone-300 leading-relaxed">
                    {{ $newsSubtext }}
                </p>

                <div x-data="{ email: '', subscribed: false }" class="pt-1">
                    <form @submit.prevent="if(email && email.includes('@')) { subscribed = true; }" class="space-y-2">
                        <div x-show="!subscribed" class="flex gap-1.5">
                            <input type="email" 
                                   x-model="email" 
                                   placeholder="Enter email..." 
                                   required
                                   class="w-full px-3 py-2 text-xs rounded-lg bg-white/10 text-white placeholder-stone-400 border border-white/15 focus:outline-none focus:border-white">
                            <button type="submit" 
                                    class="px-3.5 py-2 text-xs font-bold rounded-lg bg-white text-stone-900 hover:bg-stone-200 transition shrink-0">
                                Join
                            </button>
                        </div>
                        <div x-show="subscribed" class="text-xs text-emerald-400 font-semibold p-2 rounded bg-emerald-500/10 border border-emerald-500/20" style="display:none;">
                            ✓ Thank you. You are on the private studio dispatch list.
                        </div>
                    </form>
                </div>
            </div>
            @endif

        </div>

        <!-- Bottom Copyright & K71 Verification -->
        <div class="pt-8 flex flex-col sm:flex-row items-center justify-between gap-4 text-xs text-stone-400">
            <div>
                {{ $copyrightText }}
            </div>

            <div class="flex items-center gap-4">
                <a href="{{ url('/') }}" class="hover:text-white transition flex items-center gap-1.5 font-medium">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 inline-block"></span>
                    <span>Powered by K71 Commerce Engine</span>
                </a>
                <span>•</span>
                <a href="{{ route('catalog.index') }}" class="hover:text-white transition">
                    Explore Verified Stores
                </a>
            </div>
        </div>

    </div>
</footer>

