@php
    if (!app()->bound('current_vendor')) {
        app()->instance('current_vendor', $vendor);
    }
    $storeCanonicalUrl = $vendor->storefront_url;
    $storeLogo = $vendor->logo ? (str_starts_with($vendor->logo, 'http') ? $vendor->logo : asset('storage/' . $vendor->logo)) : null;
    $storeBanner = $vendor->banner ? (str_starts_with($vendor->banner, 'http') ? $vendor->banner : asset('storage/' . $vendor->banner)) : null;

    $storeSchema = [
        '@type' => 'ClothingStore',
        'name' => $vendor->shop_name,
        'url' => $storeCanonicalUrl,
        'description' => Str::limit(strip_tags($vendor->description), 250),
        'telephone' => $vendor->contact_phone,
        'parentOrganization' => [
            '@type' => 'Organization',
            'name' => config('marketplace.name', 'K71'),
            'url' => url('/'),
        ],
    ];
    if ($storeLogo) {
        $storeSchema['image'] = $storeLogo;
        $storeSchema['logo'] = $storeLogo;
    } elseif ($storeBanner) {
        $storeSchema['image'] = $storeBanner;
    }

    $storeBreadcrumb = [
        '@type' => 'BreadcrumbList',
        'itemListElement' => [
            [
                '@type' => 'ListItem',
                'position' => 1,
                'name' => 'Home',
                'item' => url('/'),
            ],
            [
                '@type' => 'ListItem',
                'position' => 2,
                'name' => 'Boutiques',
                'item' => route('catalog.index'),
            ],
            [
                '@type' => 'ListItem',
                'position' => 3,
                'name' => $vendor->shop_name,
                'item' => $storeCanonicalUrl,
            ],
        ],
    ];

    $storeSchemaJson = [
        '@context' => 'https://schema.org',
        '@graph' => [
            $storeSchema,
            $storeBreadcrumb,
        ],
    ];
@endphp

<x-layouts.public 
    :title="$vendor->shop_name" 
    :canonicalUrl="$storeCanonicalUrl"
    :metaDescription="Str::limit(strip_tags($vendor->description), 160)" 
    :ogImage="$vendor->banner ?: $vendor->logo"
    :schemaJson="$storeSchemaJson"
>
    @php
        $social = is_array($vendor->social_links) ? $vendor->social_links : (is_string($vendor->social_links) ? (json_decode($vendor->social_links, true) ?: []) : []);
        $tagline = $social['tagline'] ?? null;
        $customBadge = $social['custom_badge'] ?? 'Verified Seller';
        $announcement = $social['announcement_bar'] ?? null;
        $announcementEnabled = !empty($social['announcement_bar_enabled']) && !empty($announcement);
        $announcementEmoji = $social['announcement_emoji'] ?? '✨';
        $announcementLink = $social['announcement_link'] ?? null;
        $craftSpecialty = $social['craft_specialty'] ?? 'Handcrafted Apparel & Couture';
        $establishedYear = $social['established_year'] ?? $vendor->created_at->format('Y');
        
        $showStatsStrip = !isset($social['show_stats_strip']) || !empty($social['show_stats_strip']);
        $showWhatsappButton = !isset($social['show_whatsapp_button']) || !empty($social['show_whatsapp_button']);
        $showSpotlight = !isset($social['show_spotlight']) || !empty($social['show_spotlight']);

        $craftHighlights = array_filter([
            $social['craft_highlight_1'] ?? '100% Handcrafted & Authentic Weaves',
            $social['craft_highlight_2'] ?? 'Direct Store Dispatch with Quality Seal',
            $social['craft_highlight_3'] ?? 'Eco-Conscious Packaging & 7-Day Exchange',
        ]);
        
        $instagram = $social['instagram'] ?? null;
        if ($instagram) {
            $instagramHandle = ltrim($instagram, '@');
            $instagramUrl = Str::startsWith($instagram, 'http') ? $instagram : 'https://instagram.com/' . $instagramHandle;
        } else {
            $instagramUrl = null;
        }

        $whatsapp = $social['whatsapp'] ?? $vendor->contact_phone;
        if ($whatsapp) {
            $whatsappClean = preg_replace('/[^0-9]/', '', $whatsapp);
            if (strlen($whatsappClean) === 10) { $whatsappClean = '91' . $whatsappClean; }
            $mktName = config('marketplace.name', 'K71');
            $waShopMsg = rawurlencode("Namaste {$vendor->shop_name}! I am browsing your collection on {$mktName}. Could you please help me with some questions / custom orders?");
            $whatsappUrl = "https://wa.me/{$whatsappClean}?text={$waShopMsg}";
        } else {
            $whatsappUrl = null;
        }

        $palette = $vendor->colour_palette ?? 'sage';
        if ($palette === 'default') { $palette = 'sage'; }
        $layout = $vendor->layout_preset ?? 'classic';
        $productPreset = $vendor->product_preset ?? 'gallery';

        // 3-3-3 Rich Theme Palette & Glassmorphic Tokens
        $theme = match($palette) {
            'sage' => [
                'accentText' => 'text-emerald-800 dark:text-emerald-300',
                'accentBg' => 'bg-emerald-800 dark:bg-emerald-600',
                'accentBorder' => 'border-emerald-300/60 dark:border-emerald-500/30',
                'accentHover' => 'hover:bg-emerald-900 dark:hover:bg-emerald-500',
                'pillActive' => 'bg-emerald-800 text-white shadow-md shadow-emerald-800/25',
                'badge' => 'bg-emerald-50/90 text-emerald-900 border-emerald-300/80 backdrop-blur-md',
                'badgeSecondary' => 'bg-stone-100/80 text-stone-800 border-stone-200/80 backdrop-blur-md',
                'announcementGradient' => 'from-emerald-950 via-emerald-800 to-teal-950',
                'heroFallback' => 'from-[#0A1A14] via-[#143428] to-[#0A1A14]',
                'ringColor' => 'ring-emerald-500/30',
                'buttonPrimary' => 'bg-gradient-to-r from-emerald-800 via-emerald-700 to-teal-800 hover:from-emerald-900 hover:to-teal-900 text-white shadow-lg shadow-emerald-800/25 border border-emerald-600/30',
                'glassCard' => 'backdrop-blur-2xl bg-white/80 dark:bg-[#07130E]/85 border border-emerald-200/60 dark:border-emerald-500/20 shadow-[0_16px_40px_rgba(6,78,59,0.08)]',
                'glowBadge' => 'bg-emerald-50/90 text-emerald-900 border-emerald-300 shadow-sm shadow-emerald-500/15',
            ],
            'midnight' => [
                'accentText' => 'text-indigo-400 dark:text-cyan-300',
                'accentBg' => 'bg-slate-900 dark:bg-indigo-600',
                'accentBorder' => 'border-indigo-400/30 dark:border-cyan-500/30',
                'accentHover' => 'hover:bg-black dark:hover:bg-indigo-500',
                'pillActive' => 'bg-gradient-to-r from-blue-600 to-indigo-600 text-white shadow-md shadow-blue-500/30',
                'badge' => 'bg-indigo-950/70 text-indigo-200 border-indigo-500/40 backdrop-blur-md',
                'badgeSecondary' => 'bg-slate-800/70 text-slate-200 border-slate-700/60 backdrop-blur-md',
                'announcementGradient' => 'from-[#030712] via-[#0F172A] to-[#1E1B4B]',
                'heroFallback' => 'from-[#020617] via-[#0F172A] to-[#1E1B4B]',
                'ringColor' => 'ring-cyan-500/30',
                'buttonPrimary' => 'bg-gradient-to-r from-blue-600 via-indigo-600 to-cyan-500 hover:from-blue-700 hover:to-cyan-600 text-white shadow-lg shadow-blue-500/30 border border-cyan-400/30',
                'glassCard' => 'backdrop-blur-2xl bg-slate-900/90 text-slate-100 border border-slate-700/60 shadow-[0_20px_50px_rgba(2,6,23,0.45)]',
                'glowBadge' => 'bg-indigo-950/80 text-cyan-300 border-cyan-500/40 shadow-sm shadow-cyan-500/20',
            ],
            'sunset' => [
                'accentText' => 'text-[#C85A32] dark:text-amber-300',
                'accentBg' => 'bg-[#C85A32] dark:bg-orange-600',
                'accentBorder' => 'border-amber-300/60 dark:border-amber-500/30',
                'accentHover' => 'hover:bg-[#AC4721] dark:hover:bg-orange-500',
                'pillActive' => 'bg-gradient-to-r from-orange-600 to-amber-600 text-white shadow-md shadow-orange-500/30',
                'badge' => 'bg-amber-50/90 text-amber-950 border-amber-300/80 backdrop-blur-md',
                'badgeSecondary' => 'bg-stone-100/80 text-stone-800 border-stone-200/80 backdrop-blur-md',
                'announcementGradient' => 'from-[#4D1C0D] via-[#C85A32] to-[#78350F]',
                'heroFallback' => 'from-[#2C1007] via-[#5C2310] to-[#2C1007]',
                'ringColor' => 'ring-amber-500/30',
                'buttonPrimary' => 'bg-gradient-to-r from-orange-600 via-amber-600 to-rose-600 hover:from-orange-700 hover:to-rose-700 text-white shadow-lg shadow-orange-600/25 border border-amber-400/30',
                'glassCard' => 'backdrop-blur-2xl bg-white/85 dark:bg-[#1A0C06]/85 border border-amber-200/70 dark:border-amber-500/20 shadow-[0_16px_40px_rgba(194,65,12,0.08)]',
                'glowBadge' => 'bg-amber-50/90 text-amber-950 border-amber-300 shadow-sm shadow-amber-500/15',
            ],
            default => [ // linen fallback
                'accentText' => 'text-[#2B3E36]',
                'accentBg' => 'bg-[#2B3E36]',
                'accentBorder' => 'border-[#2B3E36]/30',
                'accentHover' => 'hover:bg-[#1E2E28]',
                'pillActive' => 'bg-[#2B3E36] text-white',
                'badge' => 'bg-[#F2ECE1] text-[#2B3E36] border-[#DFD5C4]',
                'badgeSecondary' => 'bg-stone-100 text-stone-700 border-stone-200',
                'announcementGradient' => 'from-[#1A2621] via-[#2D433A] to-[#1A2621]',
                'heroFallback' => 'from-[#1A2621] via-[#2F483E] to-[#18231F]',
                'ringColor' => 'ring-[#2B3E36]/20',
                'buttonPrimary' => 'bg-[#2B3E36] hover:bg-[#1E2E28] text-white',
                'glassCard' => 'backdrop-blur-xl bg-white/80 border border-stone-200/80 shadow-md',
                'glowBadge' => 'bg-[#F2ECE1] text-[#2B3E36] border-[#DFD5C4]',
            ],
        };
    @endphp

    <div x-data="{ 
        storyModalOpen: false, 
        shareCopied: false,
        followed: localStorage.getItem('boutique_followed_{{ $vendor->id }}') === 'true',
        followerCount: {{ 140 + ($vendor->id * 11) }} + (localStorage.getItem('boutique_followed_{{ $vendor->id }}') === 'true' ? 1 : 0),
        toggleFollow() {
            this.followed = !this.followed;
            if (this.followed) {
                localStorage.setItem('boutique_followed_{{ $vendor->id }}', 'true');
                this.followerCount++;
            } else {
                localStorage.removeItem('boutique_followed_{{ $vendor->id }}');
                this.followerCount--;
            }
        },
        shareBoutique() {
            if (navigator.share) {
                navigator.share({
                    title: '{{ addslashes($vendor->shop_name) }}',
                    text: 'Explore the handcrafted couture collection at {{ addslashes($vendor->shop_name) }}',
                    url: window.location.href
                }).catch(() => {});
            } else {
                navigator.clipboard.writeText(window.location.href);
                this.shareCopied = true;
                setTimeout(() => this.shareCopied = false, 2500);
            }
        }
    }">

        <!-- ANNOUNCEMENT MARQUEE BAR -->
        @if($announcementEnabled)
            <div class="mb-4 -mt-2">
                @if($announcementLink)
                    <a href="{{ $announcementLink }}" class="block group">
                @endif
                <div class="rounded-2xl bg-gradient-to-r {{ $theme['announcementGradient'] }} text-white px-4 py-2.5 text-center shadow-xs flex items-center justify-center gap-2 overflow-hidden transition-all duration-300 hover:brightness-105">
                    <span class="inline-block animate-pulse text-amber-300 text-sm">{{ $announcementEmoji }}</span>
                    <span class="text-xs font-semibold tracking-wide truncate max-w-xl">{{ $announcement }}</span>
                    <span class="inline-block animate-pulse text-amber-300 text-sm">{{ $announcementEmoji }}</span>
                </div>
                @if($announcementLink)
                    </a>
                @endif
            </div>
        @endif

        <!-- TOAST ALERT FOR BOUTIQUE SHARE -->
        <div x-show="shareCopied" 
             x-transition 
             class="fixed top-20 right-6 z-50 bg-stone-900 text-white text-xs font-bold px-4 py-2.5 rounded-2xl shadow-xl flex items-center gap-2">
            <span>✓ Boutique link copied to clipboard!</span>
        </div>

        {{-- ========================================================================= --}}
        {{-- HERO LAYOUT 1: CLASSIC ATELIER MEDALLION --}}
        {{-- ========================================================================= --}}
        @if($layout === 'classic' || empty($layout))
            <div class="relative bg-white rounded-3xl border border-stone-200/80 shadow-xs overflow-hidden mb-10">
                <!-- Panoramic Banner (Full uncropped 3:1 aspect ratio with high-priority LCP preloading) -->
                <div class="relative w-full overflow-hidden bg-stone-100" style="aspect-ratio: 3 / 1; min-height: 120px;">
                    @if(!empty($vendor->banner))
                        <img src="{{ \App\Services\ImageService::url($vendor->banner, 1200) }}" 
                             alt="{{ $vendor->shop_name }} Banner" 
                             width="1200"
                             height="400"
                             fetchpriority="high"
                             loading="eager"
                             decoding="async"
                             onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';"
                             class="w-full h-full object-cover object-center">
                        <div style="display:none;" class="w-full h-full bg-gradient-to-tr {{ $theme['heroFallback'] }} flex items-center justify-center relative">
                            <div class="absolute inset-0 opacity-15 bg-[radial-gradient(#ffffff_1px,transparent_1px)] [background-size:16px_16px]"></div>
                            <span class="text-white/40 font-serif text-5xl sm:text-7xl md:text-8xl font-bold tracking-widest select-none uppercase text-center px-4">
                                {{ $vendor->shop_name }}
                            </span>
                        </div>
                    @else
                        <div class="w-full h-full bg-gradient-to-tr {{ $theme['heroFallback'] }} flex items-center justify-center relative min-h-[140px] sm:min-h-[220px]">
                            <div class="absolute inset-0 opacity-15 bg-[radial-gradient(#ffffff_1px,transparent_1px)] [background-size:16px_16px]"></div>
                            <span class="text-white/40 font-serif text-5xl sm:text-7xl md:text-8xl font-bold tracking-widest select-none uppercase text-center px-4">
                                {{ $vendor->shop_name }}
                            </span>
                        </div>
                    @endif

                    <!-- Floating Share Button -->
                    <button type="button" 
                            @click="shareBoutique()"
                            aria-label="Share this boutique"
                            class="absolute top-3.5 right-3.5 sm:top-4 sm:right-4 px-3 sm:px-3.5 py-1 sm:py-1.5 rounded-full bg-white/90 backdrop-blur-md text-stone-800 hover:text-black text-xs font-bold transition-all shadow-md flex items-center gap-1.5 hover:scale-105 active:scale-95">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"></path></svg>
                        <span>Share</span>
                    </button>
                </div>

                <!-- Profile Core & Actions (Positioned cleanly below banner, avoiding any collision) -->
                <div class="relative px-4 sm:px-8 pb-8 pt-5 sm:pt-6 z-10">
                    <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
                        
                        <!-- Left: Avatar Medallion + Titles -->
                        <div class="flex flex-col sm:flex-row items-center sm:items-center gap-4 sm:gap-6 text-center sm:text-left">
                            <!-- Circular Medallion Avatar -->
                            <div class="relative shrink-0 group">
                                <div style="width: 88px; height: 88px; min-width: 88px; min-height: 88px;" class="rounded-full p-1 bg-white shadow-lg ring-2 {{ $theme['ringColor'] }} overflow-hidden">
                                    <div class="w-full h-full rounded-full overflow-hidden bg-[#FAF8F5] flex items-center justify-center">
                                        @if(!empty($vendor->logo))
                                            <img src="{{ \App\Services\ImageService::url($vendor->logo, 200) }}" 
                                                 alt="{{ $vendor->shop_name }} Logo" 
                                                 width="88"
                                                 height="88"
                                                 loading="eager"
                                                 decoding="async"
                                                 onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';"
                                                 style="width: 100%; height: 100%; object-fit: cover;"
                                                 class="transition-transform duration-300 group-hover:scale-105">
                                            <span style="display:none;" class="w-full h-full items-center justify-center text-3xl sm:text-5xl font-serif font-bold {{ $theme['accentText'] }}">
                                                {{ strtoupper(substr($vendor->shop_name, 0, 1)) }}
                                            </span>
                                        @else
                                            <span class="w-full h-full flex items-center justify-center text-3xl sm:text-5xl font-serif font-bold {{ $theme['accentText'] }}">
                                                {{ strtoupper(substr($vendor->shop_name, 0, 1)) }}
                                            </span>
                                        @endif
                                    </div>
                                </div>
                                
                                <!-- Verified Badge Overlay -->
                                <div class="absolute bottom-0 right-0 w-7 h-7 sm:w-8 sm:h-8 rounded-full bg-emerald-600 text-white flex items-center justify-center shadow-md border-2 border-white"
                                     title="Certified Authentic Atelier">
                                    <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg>
                                </div>
                            </div>

                            <!-- Name, Motto & Badges -->
                            <div class="space-y-1.5 flex-1">
                                <div class="flex flex-wrap items-center justify-center sm:justify-start gap-2">
                                    <h1 class="text-2xl sm:text-3xl md:text-4xl font-serif font-bold text-stone-900 tracking-tight">
                                        {{ $vendor->shop_name }}
                                    </h1>
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full {{ $theme['badge'] }} text-[11px] font-bold border">
                                        <span>✨</span>
                                        <span>{{ $customBadge }}</span>
                                    </span>
                                </div>

                                @if($tagline)
                                    <p class="text-sm sm:text-base font-serif italic text-stone-600 leading-snug">
                                        "{{ $tagline }}"
                                    </p>
                                @endif

                                <div class="flex flex-wrap items-center justify-center sm:justify-start gap-2 pt-1 text-[11px] text-stone-500 font-medium">
                                    @if(!empty($vendor->pickup_address['city']))
                                        <span class="flex items-center gap-1 bg-stone-100 px-2.5 py-0.5 rounded-md">
                                            <span>📍</span> {{ $vendor->pickup_address['city'] }}@if(!empty($vendor->pickup_address['state'])), {{ $vendor->pickup_address['state'] }}@endif
                                        </span>
                                    @endif
                                    <span class="bg-stone-100 px-2.5 py-0.5 rounded-md">
                                        Est. {{ $establishedYear }}
                                    </span>
                                    <span class="bg-stone-100 px-2.5 py-0.5 rounded-md {{ $theme['accentText'] }} font-semibold">
                                        🧵 {{ $craftSpecialty }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Right: Actions Cluster (Follow, WhatsApp, Story) -->
                        <div class="flex flex-wrap items-center justify-center sm:justify-end gap-2.5 pt-2 md:pt-0">
                            <!-- Follow Boutique Interactive Button -->
                            <button type="button" 
                                    @click="toggleFollow()"
                                    class="px-5 py-2.5 rounded-full text-xs font-bold transition-all shadow-xs flex items-center gap-1.5 active:scale-95"
                                    :class="followed ? 'bg-stone-100 text-stone-800 border border-stone-300' : '{{ $theme['buttonPrimary'] }}'">
                                <span x-text="followed ? '✓ Following' : '+ Follow Boutique'"></span>
                                <span class="opacity-70 text-[10px]" x-text="'(' + followerCount + ')'"></span>
                            </button>

                            @if($showWhatsappButton && $whatsappUrl)
                                <a href="{{ $whatsappUrl }}" target="_blank"
                                   class="px-4 py-2.5 rounded-full bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold transition-all shadow-xs flex items-center gap-1.5 active:scale-95">
                                    <svg class="w-4 h-4 fill-white" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/></svg>
                                    <span>WhatsApp Stylist</span>
                                </a>
                            @endif

                            @if($instagramUrl)
                                <a href="{{ $instagramUrl }}" target="_blank"
                                   class="px-3.5 py-2.5 rounded-full bg-stone-100 hover:bg-stone-200 text-stone-800 text-xs font-bold transition-all flex items-center gap-1.5"
                                   title="Follow on Instagram">
                                    <span>📷</span>
                                    <span class="hidden sm:inline">Instagram</span>
                                </a>
                            @endif

                            <button type="button" 
                                    @click="storyModalOpen = true"
                                    class="px-4 py-2.5 rounded-full border border-stone-300 hover:border-black text-stone-800 text-xs font-bold transition-all flex items-center gap-1.5">
                                <span>📜 Craft Story</span>
                            </button>
                        </div>
                    </div>

                    <!-- Stylized Expandable Atelier Note Card -->
                    @if(!empty($vendor->description))
                        @php
                            $descLen = strlen(strip_tags($vendor->description));
                            $isLongDesc = $descLen > 150;
                        @endphp
                        <div class="mt-6 pt-5 border-t border-stone-100 max-w-3xl" x-data="{ descExpanded: false }">
                            <div class="rounded-2xl bg-[#FAF8F5] border border-stone-200/70 p-4 sm:p-5 shadow-2xs relative">
                                <!-- Card Header: Badge & Atelier Credit -->
                                <div class="flex items-center justify-between gap-2 mb-2.5 pb-2 border-b border-stone-200/50">
                                    <div class="flex items-center gap-1.5 text-stone-700">
                                        <svg class="w-3.5 h-3.5 text-stone-500 shrink-0" fill="currentColor" viewBox="0 0 24 24">
                                            <path d="M14.017 21v-7.391c0-5.704 3.731-9.57 8.983-10.609l.995 2.151c-2.432.917-3.995 3.638-3.995 5.849h4v10h-9.983zm-14.017 0v-7.391c0-5.704 3.748-9.57 9-10.609l.996 2.151c-2.433.917-3.996 3.638-3.996 5.849h3.983v10h-9.983z"/>
                                        </svg>
                                        <span class="text-[10px] font-bold uppercase tracking-widest text-stone-600">Store Overview</span>
                                    </div>
                                    <span class="text-[10px] font-medium text-stone-500">By {{ $vendor->shop_name }}</span>
                                </div>

                                <!-- Description Content with Dynamic Clamp & Soft Gradient Fade (CLS Fixed with static line-clamp) -->
                                <div class="relative">
                                    <p class="text-xs sm:text-sm text-stone-600 leading-relaxed font-sans transition-all duration-300 {{ $isLongDesc ? 'line-clamp-2 sm:line-clamp-3' : '' }}"
                                       :class="{ '!line-clamp-none': descExpanded }">
                                        {{ $vendor->description }}
                                    </p>
                                    @if($isLongDesc)
                                        <div x-show="!descExpanded" 
                                             class="absolute bottom-0 inset-x-0 h-6 bg-gradient-to-t from-[#FAF8F5] to-transparent pointer-events-none"></div>
                                    @endif
                                </div>

                                <!-- Card Footer: Inline Toggle & Craft Heritage Link -->
                                @if($isLongDesc)
                                    <div class="mt-3 pt-2.5 flex items-center justify-between border-t border-stone-200/50 text-xs">
                                        <button type="button" 
                                                @click="descExpanded = !descExpanded" 
                                                class="font-bold text-stone-900 hover:text-black flex items-center gap-1.5 transition-colors group">
                                            <span x-text="descExpanded ? 'Show Less' : 'Read Full Overview'"></span>
                                            <svg class="w-3.5 h-3.5 transition-transform duration-200 group-hover:translate-y-0.5" 
                                                 :class="descExpanded ? 'rotate-180' : ''" 
                                                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                            </svg>
                                        </button>
                                        <button type="button" 
                                                @click="storyModalOpen = true" 
                                                class="text-[11px] text-stone-600 hover:text-stone-900 font-medium transition-colors flex items-center gap-1">
                                            <span>Craft Philosophy</span>
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                        </button>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endif

                    <!-- Credentials Strip -->
                    @if($showStatsStrip)
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-2.5 pt-6 mt-6 border-t border-stone-100 text-center text-xs">
                            <div class="p-2.5 rounded-2xl bg-stone-50 border border-stone-100">
                                <span class="text-base sm:text-lg font-serif font-bold {{ $theme['accentText'] }} block">{{ $products->total() }}</span>
                                <span class="text-[10px] uppercase font-bold tracking-wider text-stone-500">Curated Pieces</span>
                            </div>
                            <div class="p-2.5 rounded-2xl bg-stone-50 border border-stone-100">
                                <span class="text-base sm:text-lg font-serif font-bold {{ $theme['accentText'] }} block">{{ $vendor->settings->processing_days ?? 2 }} Days</span>
                                <span class="text-[10px] uppercase font-bold tracking-wider text-stone-500">Store Dispatch</span>
                            </div>
                            <div class="p-2.5 rounded-2xl bg-stone-50 border border-stone-100">
                                <span class="text-base sm:text-lg font-serif font-bold {{ $theme['accentText'] }} block">100% Genuine</span>
                                <span class="text-[10px] uppercase font-bold tracking-wider text-stone-500">Authentic Weaves</span>
                            </div>
                            <div class="p-2.5 rounded-2xl bg-stone-50 border border-stone-100">
                                <span class="text-base sm:text-lg font-serif font-bold {{ $theme['accentText'] }} block">7 Days</span>
                                <span class="text-[10px] uppercase font-bold tracking-wider text-stone-500">Easy Returns</span>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

        {{-- ========================================================================= --}}
        {{-- HERO LAYOUT 2: EDITORIAL PANORAMIC SHOWCASE --}}
        {{-- ========================================================================= --}}
        @elseif($layout === 'editorial')
            <div class="relative bg-stone-900 rounded-3xl overflow-hidden mb-10 shadow-lg text-white">
                <!-- Panoramic Image Layer -->
                <div class="relative w-full min-h-[360px] sm:min-h-[420px] md:min-h-[460px] flex flex-col justify-between p-6 sm:p-10">
                    @if(!empty($vendor->banner))
                        <img src="{{ \App\Services\ImageService::url($vendor->banner, 1200) }}" 
                             alt="{{ $vendor->shop_name }} Banner" 
                             width="1200"
                             height="460"
                             fetchpriority="high"
                             loading="eager"
                             decoding="async"
                             onerror="this.style.display='none'; this.nextElementSibling.style.display='block';"
                             class="absolute inset-0 w-full h-full object-cover object-center filter brightness-[0.75] contrast-[1.1]">
                        <div style="display:none;" class="absolute inset-0 bg-gradient-to-tr {{ $theme['heroFallback'] }}"></div>
                    @else
                        <div class="absolute inset-0 bg-gradient-to-tr {{ $theme['heroFallback'] }}"></div>
                    @endif
                    <div class="absolute inset-0 bg-gradient-to-t from-black/90 via-black/40 to-black/20"></div>

                    <!-- Top Bar: Logo Seal + Share -->
                    <div class="relative z-10 flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-12 h-12 sm:w-14 sm:h-14 rounded-full p-1 bg-white/20 backdrop-blur-md border border-white/40 overflow-hidden shadow-md">
                                <div class="w-full h-full rounded-full overflow-hidden bg-stone-900 flex items-center justify-center">
                                    @if(!empty($vendor->logo))
                                        <img src="{{ \App\Services\ImageService::url($vendor->logo, 100) }}" 
                                             alt="{{ $vendor->shop_name }} Logo" 
                                             width="56"
                                             height="56"
                                             loading="eager"
                                             decoding="async"
                                             onerror="this.style.display='none'; this.nextElementSibling.style.display='inline';" 
                                             class="w-full h-full object-cover">
                                        <span style="display:none;" class="text-white font-serif font-bold text-lg">{{ strtoupper(substr($vendor->shop_name, 0, 1)) }}</span>
                                    @else
                                        <span class="text-white font-serif font-bold text-lg">{{ strtoupper(substr($vendor->shop_name, 0, 1)) }}</span>
                                    @endif
                                </div>
                            </div>
                            <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full bg-white/20 backdrop-blur-md border border-white/30 text-white text-[11px] font-bold">
                                <span>✨</span> {{ $customBadge }}
                            </span>
                        </div>

                        <button type="button" 
                                @click="shareBoutique()"
                                aria-label="Share this boutique"
                                class="px-3.5 py-1.5 rounded-full bg-white/20 backdrop-blur-md hover:bg-white hover:text-black text-white text-xs font-bold transition-all border border-white/30 flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"></path></svg>
                            <span>Share</span>
                        </button>
                    </div>

                    <!-- Bottom Hero Focus: Typography & Actions -->
                    <div class="relative z-10 pt-16">
                        <div class="max-w-2xl">
                            <div class="flex flex-wrap items-center gap-2 mb-2 text-xs text-white/80">
                                @if(!empty($vendor->pickup_address['city']))
                                    <span>📍 {{ $vendor->pickup_address['city'] }}</span>
                                    <span>•</span>
                                @endif
                                <span>Est. {{ $establishedYear }}</span>
                                <span>•</span>
                                <span>🧵 {{ $craftSpecialty }}</span>
                            </div>

                            <h1 class="text-4xl sm:text-5xl md:text-6xl font-serif font-bold text-white tracking-tight drop-shadow-md">
                                {{ $vendor->shop_name }}
                            </h1>

                            @if($tagline)
                                <p class="text-base sm:text-lg font-serif italic text-white/90 mt-2 max-w-xl">
                                    "{{ $tagline }}"
                                </p>
                            @endif

                            @if(!empty($vendor->description))
                                @php
                                    $descLen = strlen(strip_tags($vendor->description));
                                    $isLongDesc = $descLen > 140;
                                @endphp
                                <div class="mt-3 max-w-xl" x-data="{ descExpanded: false }">
                                    <p class="text-xs sm:text-sm text-white/85 leading-relaxed font-sans transition-all duration-300 {{ $isLongDesc ? 'line-clamp-2' : '' }}"
                                       :class="{ '!line-clamp-none': descExpanded }">
                                        {{ $vendor->description }}
                                    </p>
                                    @if($isLongDesc)
                                        <button type="button" 
                                                @click="descExpanded = !descExpanded" 
                                                class="mt-1.5 text-[11px] text-white/90 hover:text-white font-bold underline underline-offset-2 inline-flex items-center gap-1 transition-colors">
                                            <span x-text="descExpanded ? 'Show Less ↑' : 'Read Full Note ↓'"></span>
                                        </button>
                                    @endif
                                </div>
                            @endif
                        </div>

                        <!-- Action Controls in Editorial Glass -->
                        <div class="flex flex-wrap items-center gap-2.5 mt-6 pt-5 border-t border-white/20">
                            <button type="button" 
                                    @click="toggleFollow()"
                                    class="px-5 py-2 rounded-full text-xs font-bold transition-all shadow-sm flex items-center gap-1.5 active:scale-95 bg-white text-stone-900 hover:bg-stone-100">
                                <span x-text="followed ? '✓ Following' : '+ Follow Boutique'"></span>
                                <span class="opacity-70 text-[10px]" x-text="'(' + followerCount + ')'"></span>
                            </button>

                            @if($showWhatsappButton && $whatsappUrl)
                                <a href="{{ $whatsappUrl }}" target="_blank"
                                   class="px-4 py-2 rounded-full bg-emerald-500 hover:bg-emerald-600 text-white text-xs font-bold transition-all shadow-sm flex items-center gap-1.5">
                                    <span>WhatsApp Stylist</span>
                                </a>
                            @endif

                            @if($instagramUrl)
                                <a href="{{ $instagramUrl }}" target="_blank"
                                   class="px-4 py-2 rounded-full bg-white/20 hover:bg-white/30 backdrop-blur-md text-white text-xs font-bold transition-all border border-white/30">
                                    <span>📷 Instagram</span>
                                </a>
                            @endif

                            <button type="button" @click="storyModalOpen = true"
                                    class="px-4 py-2 rounded-full bg-white/20 hover:bg-white/30 backdrop-blur-md text-white text-xs font-bold transition-all border border-white/30">
                                <span>📜 Craft Story</span>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Editorial Bottom Strip -->
                @if($showStatsStrip)
                    <div class="bg-black/40 backdrop-blur-md border-t border-white/10 px-6 py-3 grid grid-cols-2 sm:grid-cols-4 gap-2 text-center text-xs">
                        <div>
                            <span class="font-serif font-bold text-white block">{{ $products->total() }}</span>
                            <span class="text-[9px] uppercase tracking-wider text-white/80">Pieces</span>
                        </div>
                        <div>
                            <span class="font-serif font-bold text-white block">{{ $vendor->settings->processing_days ?? 2 }} Days</span>
                            <span class="text-[9px] uppercase tracking-wider text-white/80">Dispatch</span>
                        </div>
                        <div>
                            <span class="font-serif font-bold text-white block">100% Genuine</span>
                            <span class="text-[9px] uppercase tracking-wider text-white/80">Craft Verified</span>
                        </div>
                        <div>
                            <span class="font-serif font-bold text-white block">7 Days</span>
                            <span class="text-[9px] uppercase tracking-wider text-white/80">Easy Returns</span>
                        </div>
                    </div>
                @endif
            </div>

        {{-- ========================================================================= --}}
        {{-- HERO LAYOUT 3: MODERN MINIMALIST STUDIO --}}
        {{-- ========================================================================= --}}
        @else
            <div class="bg-white rounded-3xl border border-stone-200/80 p-6 sm:p-8 mb-10 shadow-xs">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
                    <div class="flex items-center gap-4 sm:gap-6">
                        <div class="w-20 h-20 sm:w-24 sm:h-24 rounded-2xl border-2 border-stone-200 overflow-hidden bg-stone-50 shrink-0 shadow-xs flex items-center justify-center">
                            @if(!empty($vendor->logo))
                                <img src="{{ \App\Services\ImageService::url($vendor->logo, 200) }}" 
                                     alt="{{ $vendor->shop_name }} Logo" 
                                     width="96"
                                     height="96"
                                     loading="eager"
                                     decoding="async"
                                     onerror="this.style.display='none'; this.nextElementSibling.style.display='inline';" 
                                     class="w-full h-full object-cover">
                                <span style="display:none;" class="text-3xl font-serif font-bold {{ $theme['accentText'] }}">{{ strtoupper(substr($vendor->shop_name, 0, 1)) }}</span>
                            @else
                                <span class="text-3xl font-serif font-bold {{ $theme['accentText'] }}">{{ strtoupper(substr($vendor->shop_name, 0, 1)) }}</span>
                            @endif
                        </div>
                        <div class="space-y-1">
                            <div class="flex flex-wrap items-center gap-2">
                                <h1 class="text-2xl sm:text-3xl md:text-4xl font-serif font-bold text-stone-900 tracking-tight">{{ $vendor->shop_name }}</h1>
                                <span class="px-2.5 py-0.5 rounded-full {{ $theme['badge'] }} text-[10px] font-bold border">
                                    {{ $customBadge }}
                                </span>
                            </div>
                            @if($tagline)
                                <p class="text-xs sm:text-sm font-serif italic text-stone-600">"{{ $tagline }}"</p>
                            @endif
                            <p class="text-xs text-stone-500">
                                {{ $craftSpecialty }} • Est. {{ $establishedYear }}
                                @if(!empty($vendor->pickup_address['city'])) • {{ $vendor->pickup_address['city'] }} @endif
                            </p>
                        </div>
                    </div>

                    <div class="flex flex-wrap items-center gap-2 pt-2 md:pt-0">
                        <button type="button" 
                                @click="toggleFollow()"
                                class="px-4 py-2 rounded-full text-xs font-bold transition-all shadow-xs flex items-center gap-1.5 active:scale-95"
                                :class="followed ? 'bg-stone-100 text-stone-800 border border-stone-300' : '{{ $theme['buttonPrimary'] }}'">
                            <span x-text="followed ? '✓ Following' : '+ Follow'"></span>
                            <span class="opacity-70 text-[10px]" x-text="'(' + followerCount + ')'"></span>
                        </button>

                        @if($showWhatsappButton && $whatsappUrl)
                            <a href="{{ $whatsappUrl }}" target="_blank"
                               class="px-4 py-2 rounded-full bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold transition-all shadow-xs flex items-center gap-1.5">
                                <span>WhatsApp Stylist</span>
                            </a>
                        @endif

                        <button type="button" @click="storyModalOpen = true"
                                class="px-3.5 py-2 rounded-full border border-stone-300 text-xs font-bold text-stone-700 hover:border-black">
                            <span>Heritage</span>
                        </button>

                        <button type="button" 
                                @click="shareBoutique()"
                                aria-label="Share this boutique"
                                class="p-2 rounded-full border border-stone-200 text-stone-600 hover:text-black hover:border-black"
                                title="Share Store">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"></path></svg>
                        </button>
                    </div>
                </div>

                <!-- Stylized Expandable Atelier Note Card -->
                @if(!empty($vendor->description))
                    @php
                        $descLen = strlen(strip_tags($vendor->description));
                        $isLongDesc = $descLen > 150;
                    @endphp
                    <div class="mt-5 pt-4 border-t border-stone-100 max-w-3xl" x-data="{ descExpanded: false }">
                        <div class="rounded-2xl bg-stone-50/80 border border-stone-200/70 p-4 shadow-2xs relative">
                            <div class="flex items-center justify-between gap-2 mb-2 pb-1.5 border-b border-stone-200/50">
                                <span class="text-[10px] font-bold uppercase tracking-widest text-stone-600">Store Overview</span>
                                <span class="text-[10px] font-medium text-stone-500">By {{ $vendor->shop_name }}</span>
                            </div>
                            <div class="relative">
                                <p class="text-xs text-stone-600 leading-relaxed font-sans transition-all duration-300 {{ $isLongDesc ? 'line-clamp-2' : '' }}"
                                   :class="{ '!line-clamp-none': descExpanded }">
                                    {{ $vendor->description }}
                                </p>
                                @if($isLongDesc)
                                    <div x-show="!descExpanded" 
                                         class="absolute bottom-0 inset-x-0 h-5 bg-gradient-to-t from-stone-50 to-transparent pointer-events-none"></div>
                                @endif
                            </div>
                            @if($isLongDesc)
                                <div class="mt-2.5 pt-2 flex items-center justify-between border-t border-stone-200/50 text-xs">
                                    <button type="button" 
                                            @click="descExpanded = !descExpanded" 
                                            class="font-bold text-stone-900 hover:text-black flex items-center gap-1 transition-colors">
                                        <span x-text="descExpanded ? 'Show Less ↑' : 'Read Full Note ↓'"></span>
                                    </button>
                                    <button type="button" 
                                            @click="storyModalOpen = true" 
                                            class="text-[11px] text-stone-600 hover:text-stone-900 font-medium transition-colors">
                                        Craft Policies →
                                    </button>
                                </div>
                            @endif
                        </div>
                    </div>
                @endif
            </div>
        @endif

        {{-- ========================================================================= --}}
        {{-- ========================================================================= --}}
        <div x-data="{
            search: '{{ addslashes(request('search', '')) }}',
            category: '{{ addslashes(request('category', '')) }}',
            sort: '{{ request('sort', 'newest') }}',
            viewMode: '{{ ($vendor->layout_preset ?? 'classic') === 'editorial' ? 'editorial' : 'compact' }}',
            searchOpen: false,
            loading: false,
            abortController: null,
            categoryMap: {
                @if(isset($vendorCategories))
                    @foreach($vendorCategories as $c)
                        '{{ $c->slug }}': '{{ addslashes($c->name) }}',
                    @endforeach
                @endif
            },

            getCategoryTitle() {
                if (!this.category) return 'The Full Collection';
                if (this.categoryMap && this.categoryMap[this.category]) {
                    return 'Collection: ' + this.categoryMap[this.category];
                }
                const formatted = this.category.replace(/-/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
                return 'Collection: ' + formatted;
            },

            async filterLive(params = {}) {
                if ('search' in params) this.search = params.search;
                if ('category' in params) this.category = params.category;
                if ('sort' in params) this.sort = params.sort;

                if (this.abortController) {
                    this.abortController.abort();
                }
                this.abortController = new AbortController();

                this.loading = true;

                const urlParams = new URLSearchParams();
                if (this.category) urlParams.set('category', this.category);
                if (this.search && this.search.trim()) urlParams.set('search', this.search.trim());
                if (this.sort && this.sort !== 'newest') urlParams.set('sort', this.sort);

                const queryStr = urlParams.toString();
                const targetUrl = window.location.pathname + (queryStr ? '?' + queryStr : '');

                try {
                    const res = await fetch(targetUrl, {
                        signal: this.abortController.signal,
                        headers: { 'X-Requested-With': 'XMLHttpRequest' }
                    });

                    if (!res.ok) throw new Error('Fetch failed with status ' + res.status);

                    const html = await res.text();
                    const parser = new DOMParser();
                    const doc = parser.parseFromString(html, 'text/html');

                    const newGrid = doc.getElementById('live-products-grid');
                    const targetGrid = document.getElementById('live-products-grid');
                    if (newGrid && targetGrid) {
                        targetGrid.innerHTML = newGrid.innerHTML;
                        if (window.Alpine && window.Alpine.initTree) {
                            window.Alpine.initTree(targetGrid);
                        }
                    }

                    const newCounter = doc.getElementById('all-pieces-count');
                    const targetCounter = document.getElementById('all-pieces-count');
                    if (newCounter && targetCounter) {
                        targetCounter.innerHTML = newCounter.innerHTML;
                    }

                    window.history.replaceState({}, '', targetUrl);
                } catch (e) {
                    if (e.name !== 'AbortError') {
                        console.error('Live filter error:', e);
                    }
                } finally {
                    this.loading = false;
                }
            },

            clearFilters() {
                this.search = '';
                this.category = '';
                this.sort = 'newest';
                this.filterLive({ search: '', category: '', sort: 'newest' });
            },

            handlePagination(e) {
                const link = e.target.closest('a');
                if (link && link.href && (link.closest('.live-pagination-wrapper') || link.closest('nav[role=\'navigation\']'))) {
                    e.preventDefault();
                    this.fetchCustomUrl(link.href);
                    document.getElementById('collection-hub')?.scrollIntoView({ behavior: 'smooth' });
                }
            },

            async fetchCustomUrl(targetUrl) {
                if (this.abortController) this.abortController.abort();
                this.abortController = new AbortController();
                this.loading = true;

                try {
                    const parsed = new URL(targetUrl, window.location.origin);
                    const relativeUrl = parsed.pathname + parsed.search;

                    const res = await fetch(relativeUrl, {
                        signal: this.abortController.signal,
                        headers: { 'X-Requested-With': 'XMLHttpRequest' }
                    });
                    if (!res.ok) throw new Error('Fetch failed with status ' + res.status);

                    const html = await res.text();
                    const parser = new DOMParser();
                    const doc = parser.parseFromString(html, 'text/html');

                    const newGrid = doc.getElementById('live-products-grid');
                    const targetGrid = document.getElementById('live-products-grid');
                    if (newGrid && targetGrid) {
                        targetGrid.innerHTML = newGrid.innerHTML;
                        if (window.Alpine && window.Alpine.initTree) {
                            window.Alpine.initTree(targetGrid);
                        }
                    }

                    const newCounter = doc.getElementById('all-pieces-count');
                    const targetCounter = document.getElementById('all-pieces-count');
                    if (newCounter && targetCounter) {
                        targetCounter.innerHTML = newCounter.innerHTML;
                    }

                    window.history.replaceState({}, '', relativeUrl);
                } catch (e) {
                    if (e.name !== 'AbortError') console.error('Pagination error:', e);
                } finally {
                    this.loading = false;
                }
            }
        }">

            {{-- VENDOR VIDEO COMMERCE SHORTS RAIL (WP Dokan Store Tab Parity) --}}
            @if(isset($vendorShorts) && $vendorShorts->isNotEmpty())
                <div class="mb-10 bg-gradient-to-r from-stone-900 via-stone-800 to-stone-900 rounded-3xl p-5 sm:p-6 text-white border border-stone-800 shadow-lg">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <div class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-rose-500/20 text-rose-300 text-[10px] font-mono font-bold tracking-wider uppercase mb-1">
                                <span class="w-1.5 h-1.5 rounded-full bg-rose-500 animate-ping"></span>
                                In-Store Video Reels
                            </div>
                            <h3 class="text-base sm:text-lg font-serif font-bold text-white tracking-tight">
                                Watch & Shop Looks by {{ $vendor->shop_name }}
                            </h3>
                        </div>
                        <a href="{{ route('shorts.index') }}" class="text-xs font-bold text-rose-400 hover:text-rose-300 flex items-center gap-1 transition">
                            Explore Shorts →
                        </a>
                    </div>

                    <div class="flex items-center gap-3.5 overflow-x-auto no-scrollbar py-1">
                        @foreach($vendorShorts as $vShort)
                            <a href="{{ route('shorts.show', $vShort->id) }}" class="flex-none w-32 sm:w-36 aspect-[9/16] rounded-2xl relative overflow-hidden bg-stone-950 group border border-white/10 shadow-md hover:scale-102 transition duration-300">
                                <img src="{{ $vShort->thumb_url ?: \App\Models\Short::placeholderThumb($vShort->id, $vShort->title) }}" class="w-full h-full object-cover group-hover:scale-105 transition duration-300" alt="{{ $vShort->title }}">
                                <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-transparent to-transparent"></div>
                                <div class="absolute bottom-2 inset-x-2">
                                    <div class="text-[9px] font-mono text-white/80">▶ {{ \App\Models\Short::formatNumber($vShort->views) }} views</div>
                                    <h4 class="text-[11px] font-bold text-white line-clamp-1 leading-tight mt-0.5">{{ $vShort->title }}</h4>
                                </div>
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- 1. VISUAL "WARDROBE STORIES" BUBBLES RAIL (High-fashion instant discovery) --}}
            @if(isset($vendorCategories) && $vendorCategories->count() > 0)
                <div class="mb-8 pt-2">
                    <div class="flex items-center justify-between mb-3 px-1">
                        <span class="text-[11px] font-mono uppercase tracking-widest text-stone-500 font-bold flex items-center gap-1.5">
                            <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                            Shop by Collection
                        </span>
                        <span class="text-[11px] text-stone-400 font-medium">Swipe to explore →</span>
                    </div>

                    <div class="flex items-center gap-4 sm:gap-6 overflow-x-auto no-scrollbar py-2 px-1 scroll-smooth" id="live-category-bubbles">
                        <!-- "All Pieces" Story Bubble -->
                        <button type="button" 
                                @click="filterLive({ category: '' })" 
                                class="flex flex-col items-center gap-2 shrink-0 group focus:outline-none cursor-pointer">
                            <div class="w-16 h-16 sm:w-20 sm:h-20 rounded-full p-0.5 transition-all duration-300"
                                 :class="category === '' 
                                    ? 'bg-gradient-to-tr from-amber-500 via-stone-800 to-amber-600 shadow-md scale-105 ring-2 ring-amber-500/50' 
                                    : 'bg-stone-200 group-hover:bg-stone-300 group-hover:scale-102'">
                                <div class="w-full h-full rounded-full bg-white flex flex-col items-center justify-center p-1 overflow-hidden border border-white shadow-inner">
                                    <span class="text-base sm:text-xl">✨</span>
                                    <span class="text-[9px] font-bold uppercase tracking-wider text-stone-800 font-mono mt-0.5">All</span>
                                </div>
                            </div>
                            <span class="text-[11px] font-bold text-center leading-tight transition-colors"
                                  :class="category === '' ? 'text-stone-900 font-black' : 'text-stone-600 group-hover:text-stone-900'">
                                All Pieces
                            </span>
                            <span class="text-[9px] font-mono text-stone-400 -mt-1.5">
                                {{ $products->total() }} items
                            </span>
                        </button>

                        <!-- Individual Category Bubbles -->
                        @foreach($vendorCategories as $cat)
                            <button type="button" 
                                    @click="filterLive({ category: category === '{{ $cat->slug }}' ? '' : '{{ $cat->slug }}' })" 
                                    class="flex flex-col items-center gap-2 shrink-0 group focus:outline-none cursor-pointer">
                                <div class="w-16 h-16 sm:w-20 sm:h-20 rounded-full p-0.5 transition-all duration-300"
                                     :class="category === '{{ $cat->slug }}' 
                                        ? 'bg-gradient-to-tr from-amber-500 via-stone-800 to-amber-600 shadow-md scale-105 ring-2 ring-amber-500/50' 
                                        : 'bg-stone-200 group-hover:bg-stone-300 group-hover:scale-102'">
                                    <div class="w-full h-full rounded-full bg-stone-100 overflow-hidden relative border border-white">
                                        @if($cat->sample_image)
                                            <img src="{{ \App\Services\ImageService::url($cat->sample_image, 160) }}" 
                                                 alt="{{ $cat->name }}" 
                                                 width="80"
                                                 height="80"
                                                 loading="lazy"
                                                 decoding="async"
                                                 class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110"
                                                 onerror="this.onerror=null; this.src='{{ asset('images/product-placeholder.svg') }}';">
                                        @else
                                            <img src="{{ asset('images/product-placeholder.svg') }}" 
                                                 alt="{{ $cat->name }}" 
                                                 width="80"
                                                 height="80"
                                                 loading="lazy"
                                                 decoding="async"
                                                 class="w-full h-full object-cover">
                                        @endif
                                        <div class="absolute inset-0 bg-black/10 group-hover:bg-transparent transition-colors"></div>
                                    </div>
                                </div>
                                <span class="text-[11px] font-bold text-center leading-tight max-w-[76px] sm:max-w-[88px] truncate transition-colors"
                                      :class="category === '{{ $cat->slug }}' ? 'text-stone-900 font-black' : 'text-stone-600 group-hover:text-stone-900'">
                                    {{ $cat->name }}
                                </span>
                                <span class="text-[9px] font-mono text-stone-400 -mt-1.5">
                                    {{ $cat->products_count ?? 0 }} items
                                </span>
                            </button>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- 2. NEW IN ATELIER / SIGNATURE HIGHLIGHTS HORIZONTAL SHELF (Hides when search/category active) --}}
            @if(isset($featuredProducts) && $featuredProducts->count() > 0)
                <section class="mb-10" x-show="!search && !category" x-transition>
                    <div class="flex items-center justify-between mb-4 px-1">
                        <div>
                            <span class="text-[10px] font-mono uppercase tracking-widest text-amber-600 font-bold block leading-none">Curated Spotlight</span>
                            <h2 class="text-lg sm:text-2xl font-serif font-bold text-stone-900 mt-1">New Arrivals</h2>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="text-[11px] text-stone-400 font-medium hidden sm:inline">Handcrafted by {{ $vendor->shop_name }}</span>
                            <span class="text-[11px] text-amber-700 font-bold sm:hidden">Swipe →</span>
                        </div>
                    </div>
                    
                    <!-- Swipeable Snap Rail with Fixed Proportions -->
                    <div class="flex items-stretch gap-3.5 sm:gap-5 overflow-x-auto no-scrollbar snap-x snap-mandatory pb-3 px-1 scroll-smooth">
                        @foreach($featuredProducts as $fp)
                            <div class="shrink-0 snap-start w-44 sm:w-52 md:w-56" style="width: 175px; flex-shrink: 0;">
                                <x-product-card :product="$fp" />
                            </div>
                        @endforeach
                    </div>
                </section>
            @endif

            {{-- 3. STICKY SMART SUB-BAR (Category navigation, Density toggle, Live Search & Sort) --}}
            <div class="sticky top-14 sm:top-16 z-20 bg-stone-50/95 backdrop-blur-md py-3 -mx-4 px-4 sm:-mx-6 sm:px-6 md:-mx-8 md:px-8 border-y border-stone-200/80 mb-6 shadow-2xs" id="collection-hub">
                <div class="flex items-center justify-between gap-3 mb-2">
                    <!-- Title & Live Count -->
                    <div class="min-w-0">
                        <h3 class="text-sm sm:text-base font-serif font-bold text-stone-900 truncate" 
                            x-text="getCategoryTitle()">
                            The Full Collection
                        </h3>
                        <p class="text-[10px] font-mono text-stone-500">
                            Showing <span id="all-pieces-count">{{ $products->total() }}</span> handcrafted pieces
                        </p>
                    </div>

                    <!-- Right Controls: View Switcher, Sort Dropdown, Search Toggle -->
                    <div class="flex items-center gap-2 shrink-0">
                        <!-- Mobile View Switcher (2-Column vs 1-Column Editorial) -->
                        <div class="inline-flex items-center p-0.5 rounded-lg bg-stone-200/70 border border-stone-300/50">
                            <button type="button" 
                                    @click="viewMode = 'compact'" 
                                    :class="viewMode === 'compact' ? 'bg-white text-stone-900 shadow-xs' : 'text-stone-500 hover:text-stone-900'"
                                    class="p-1.5 rounded-md transition-all text-xs"
                                    title="2-Column Compact Grid"
                                    aria-label="2-Column Compact Grid">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
                            </button>
                            <button type="button" 
                                    @click="viewMode = 'editorial'" 
                                    :class="viewMode === 'editorial' ? 'bg-white text-stone-900 shadow-xs' : 'text-stone-500 hover:text-stone-900'"
                                    class="p-1.5 rounded-md transition-all text-xs"
                                    title="1-Column Editorial Showcase"
                                    aria-label="1-Column Editorial Showcase">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
                            </button>
                        </div>

                        <!-- Sort Dropdown -->
                        <select x-model="sort" 
                                @change="filterLive({ sort: $event.target.value })"
                                class="rounded-lg border border-stone-200 bg-white shadow-2xs text-[11px] font-semibold py-1.5 px-2.5 focus:ring-1 focus:ring-stone-400 outline-none text-stone-700 cursor-pointer">
                            <option value="newest">Newest</option>
                            <option value="price_low">Price: Low to High</option>
                            <option value="price_high">Price: High to Low</option>
                        </select>

                        <!-- Search Toggle Button -->
                        <button type="button" 
                                @click="searchOpen = !searchOpen" 
                                :class="searchOpen || search ? 'bg-stone-900 text-white' : 'bg-white text-stone-700 border border-stone-200'"
                                class="p-1.5 rounded-lg shadow-2xs text-xs transition-colors">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                        </button>
                    </div>
                </div>

                <!-- Collapsible Search Input -->
                <div x-show="searchOpen || search" x-transition class="mb-2">
                    <form @submit.prevent="filterLive({ search: search })" class="relative">
                        <input type="text" 
                                x-model="search"
                                @input.debounce.250ms="filterLive({ search: $event.target.value })"
                                placeholder="Search this boutique..."
                                class="w-full pl-8 pr-8 py-1.5 rounded-lg border border-stone-300 bg-white text-xs focus:ring-1 focus:ring-stone-500 outline-none">
                        <svg class="w-3.5 h-3.5 text-stone-400 absolute left-2.5 top-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                        <button type="button" 
                                x-show="search" 
                                @click="search = ''; filterLive({ search: '' })" 
                                class="absolute right-2.5 top-1.5 text-stone-400 hover:text-stone-800 text-xs">✕</button>
                    </form>
                </div>

                <!-- Live Category Filter Pills Rail (Sticky) -->
                @if(isset($vendorCategories) && $vendorCategories->count() > 0)
                    <div class="flex items-center gap-1.5 overflow-x-auto no-scrollbar py-0.5" id="live-category-pills">
                        <button type="button" 
                                @click="filterLive({ category: '' })"
                                :class="category === '' ? '{{ $theme['pillActive'] }} shadow-xs font-bold' : 'bg-white text-stone-600 border border-stone-200/80 hover:border-black'"
                                class="px-3 py-1 rounded-full text-[11px] font-medium whitespace-nowrap transition-all shadow-2xs cursor-pointer">
                            All Pieces
                        </button>

                        @foreach($vendorCategories as $cat)
                            <button type="button" 
                                   @click="filterLive({ category: category === '{{ $cat->slug }}' ? '' : '{{ $cat->slug }}' })"
                                   :class="category === '{{ $cat->slug }}' ? '{{ $theme['pillActive'] }} shadow-xs font-bold' : 'bg-white text-stone-600 border border-stone-200/80 hover:border-black'"
                                   class="px-3 py-1 rounded-full text-[11px] font-medium whitespace-nowrap transition-all shadow-2xs cursor-pointer">
                                {{ $cat->name }} ({{ $cat->products_count ?? 0 }})
                            </button>
                        @endforeach

                        <button type="button" 
                                x-show="search || category"
                                @click="clearFilters()"
                                class="px-2.5 py-1 rounded-full text-[11px] font-bold whitespace-nowrap text-rose-600 bg-rose-50 border border-rose-200 hover:bg-rose-100 transition-colors ml-auto flex items-center gap-1">
                            <span>✕ Clear</span>
                        </button>
                    </div>
                @endif
            </div>

            {{-- 4. MAIN COLLECTION GRID (LIVE UPDATED) --}}
            <div class="mb-16 relative">
                <!-- Live Loading Indicator Line -->
                <div x-show="loading" 
                     x-transition 
                     class="absolute -top-3 inset-x-0 h-1 bg-gradient-to-r from-transparent via-amber-600 to-transparent animate-pulse rounded-full z-10"></div>

                <div id="live-products-grid" 
                     @click="handlePagination($event)"
                     :class="loading ? 'opacity-40 transition-opacity duration-200 pointer-events-none' : 'transition-opacity duration-200'">
                    
                    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3.5 sm:gap-6"
                         :class="viewMode === 'editorial' 
                            ? 'grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6' 
                            : 'grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3.5 sm:gap-6'">
                        @forelse($products as $product)
                            <div class="scroll-reveal glass-tilt-card transition-all duration-300">
                                @if($productPreset === 'immersive')
                                    <!-- Immersive Full-Bleed Preset -->
                                    <div class="relative rounded-3xl overflow-hidden {{ $theme['glassCard'] }} group shadow-md hover:shadow-2xl transition-all duration-500">
                                        <x-product-card :product="$product" />
                                    </div>
                                @elseif($productPreset === 'showcase')
                                    <!-- Technical Showcase Preset -->
                                    <div class="relative rounded-3xl overflow-hidden {{ $theme['glassCard'] }} border-2 p-1.5 shadow-md hover:shadow-xl transition-all duration-300">
                                        <x-product-card :product="$product" />
                                    </div>
                                @else
                                    <!-- Gallery 3D Tilt Card Preset (Default) -->
                                    <div class="relative rounded-3xl overflow-hidden transition-all duration-300 hover:-translate-y-1.5">
                                        <x-product-card :product="$product" />
                                    </div>
                                @endif
                            </div>
                        @empty
                            <div class="col-span-full py-14 px-6 sm:px-12 rounded-3xl {{ $theme['glassCard'] }} text-center relative overflow-hidden my-4 border">
                                <!-- Ambient Glass Specular Glow -->
                                <div class="absolute -top-24 left-1/2 -translate-x-1/2 w-96 h-96 bg-gradient-to-b from-indigo-500/10 via-amber-500/10 to-transparent rounded-full blur-3xl pointer-events-none"></div>

                                <div class="relative z-10 max-w-xl mx-auto">
                                    <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full {{ $theme['glowBadge'] }} text-xs font-bold mb-4 tracking-wide border">
                                        <span class="animate-pulse">✨</span>
                                        <span>Bespoke Private Atelier • Collection Drop Imminent</span>
                                    </div>

                                    <h3 class="text-2xl sm:text-3xl font-serif font-bold text-stone-900 dark:text-white tracking-tight mb-3">
                                        Handcrafted Treasures in Curation
                                    </h3>

                                    <p class="text-xs sm:text-sm text-stone-600 dark:text-stone-300 leading-relaxed mb-8">
                                        <span x-show="search || category">No pieces match your active live filter. Reset filters to explore available collections.</span>
                                        <span x-show="!search && !category">{{ $vendor->shop_name }} is currently curating their next exclusive seasonal drop. Connect directly with the boutique master stylist for bespoke commissions, private catalog previews, or custom sizing!</span>
                                    </p>

                                    <!-- 3 Interactive Feature Tiles -->
                                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3.5 mb-8 text-left">
                                        <div class="p-4 rounded-2xl bg-white/70 dark:bg-white/5 border border-stone-200/60 dark:border-white/10 backdrop-blur-md shadow-2xs">
                                            <div class="text-xl mb-1.5">🧵</div>
                                            <div class="text-xs font-bold text-stone-900 dark:text-white">Made-To-Order</div>
                                            <div class="text-[11px] text-stone-500 dark:text-stone-400 mt-0.5 leading-snug">Bespoke sizing & tailored consultations.</div>
                                        </div>
                                        <div class="p-4 rounded-2xl bg-white/70 dark:bg-white/5 border border-stone-200/60 dark:border-white/10 backdrop-blur-md shadow-2xs">
                                            <div class="text-xl mb-1.5">🛡️</div>
                                            <div class="text-xs font-bold text-stone-900 dark:text-white">Authentic Artisan</div>
                                            <div class="text-[11px] text-stone-500 dark:text-stone-400 mt-0.5 leading-snug">Direct boutique dispatch & quality seal.</div>
                                        </div>
                                        <div class="p-4 rounded-2xl bg-white/70 dark:bg-white/5 border border-stone-200/60 dark:border-white/10 backdrop-blur-md shadow-2xs">
                                            <div class="text-xl mb-1.5">⚡</div>
                                            <div class="text-xs font-bold text-stone-900 dark:text-white">Priority Concierge</div>
                                            <div class="text-[11px] text-stone-500 dark:text-stone-400 mt-0.5 leading-snug">Direct WhatsApp response within hours.</div>
                                        </div>
                                    </div>

                                    <!-- Action CTAs -->
                                    <div class="flex flex-wrap items-center justify-center gap-3">
                                        @if($whatsappUrl)
                                            <a href="{{ $whatsappUrl }}" target="_blank" rel="noopener"
                                               class="px-6 py-3 rounded-full bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold transition-all shadow-lg shadow-emerald-600/30 flex items-center gap-2 hover:scale-105 active:scale-95">
                                                <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981z"/></svg>
                                                <span>Chat on WhatsApp</span>
                                            </a>
                                        @endif

                                        <a href="{{ route('messages.index', ['target_user_id' => $vendor->user_id]) }}"
                                           class="px-6 py-3 rounded-full {{ $theme['buttonPrimary'] }} text-xs font-bold transition-all flex items-center gap-2 hover:scale-105 active:scale-95">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                                            <span>Message Boutique Stylist</span>
                                        </a>

                                        <button type="button" 
                                                x-show="search || category"
                                                @click="clearFilters()" 
                                                class="px-5 py-3 rounded-full bg-stone-100 hover:bg-stone-200 text-stone-800 text-xs font-bold transition-colors">
                                            Reset All Filters
                                        </button>
                                    </div>
                                </div>
                            </div>
                        @endforelse
                    </div>

                    <!-- Live Pagination -->
                    <div class="mt-10 live-pagination-wrapper">
                        {{ $products->links() }}
                    </div>
                </div>
            </div>

        </div>

        {{-- ========================================================================= --}}
        {{-- ATELIER TRUST PILLARS --}}
        {{-- ========================================================================= --}}
        <div class="bg-white rounded-3xl border border-stone-200/80 p-6 sm:p-8 mb-16 shadow-xs">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 text-center sm:text-left">
                <div class="flex items-start gap-3">
                    <div class="w-10 h-10 rounded-2xl bg-amber-50 text-amber-700 flex items-center justify-center shrink-0 mx-auto sm:mx-0">
                        <span>🧵</span>
                    </div>
                    <div>
                        <h4 class="text-xs font-bold text-stone-900 uppercase tracking-wider">Artisan Authenticity</h4>
                        <p class="text-[11px] text-stone-500 mt-0.5 leading-relaxed">Handpicked genuine textiles directly from {{ $vendor->shop_name }}.</p>
                    </div>
                </div>

                <div class="flex items-start gap-3">
                    <div class="w-10 h-10 rounded-2xl bg-zinc-100 text-zinc-700 flex items-center justify-center shrink-0 mx-auto sm:mx-0">
                        <svg class="w-5 h-5 text-zinc-700" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h2m-8 0a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0"/>
                        </svg>
                    </div>
                    <div>
                        <h4 class="text-xs font-bold text-stone-900 uppercase tracking-wider">Direct Store Dispatch</h4>
                        <p class="text-[11px] text-stone-500 mt-0.5 leading-relaxed">Dispatches in {{ $vendor->settings->processing_days ?? 2 }} days with secure packaging.</p>
                    </div>
                </div>

                <div class="flex items-start gap-3">
                    <div class="w-10 h-10 rounded-2xl bg-zinc-100 text-zinc-700 flex items-center justify-center shrink-0 mx-auto sm:mx-0">
                        <svg class="w-5 h-5 text-zinc-700" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                        </svg>
                    </div>
                    <div>
                        <h4 class="text-xs font-bold text-stone-900 uppercase tracking-wider">7 Days Easy Return</h4>
                        <p class="text-[11px] text-stone-500 mt-0.5 leading-relaxed">Doorstep return and size exchange guarantee on eligible orders.</p>
                    </div>
                </div>

                <div class="flex items-start gap-3">
                    <div class="w-10 h-10 rounded-2xl bg-zinc-100 text-zinc-700 flex items-center justify-center shrink-0 mx-auto sm:mx-0">
                        <svg class="w-5 h-5 text-zinc-700" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                        </svg>
                    </div>
                    <div>
                        <h4 class="text-xs font-bold text-stone-900 uppercase tracking-wider">Direct Store Support</h4>
                        <p class="text-[11px] text-stone-500 mt-0.5 leading-relaxed">Direct WhatsApp assistance for sizing, fabric details, and real-time guidance.</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- ========================================================================= --}}
        {{-- CRAFT STORY & ATELIER SLIDEOVER / MODAL --}}
        {{-- ========================================================================= --}}
        <div x-show="storyModalOpen" 
             x-cloak 
             class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs"
             @keydown.escape.window="storyModalOpen = false">
            <div class="bg-white rounded-3xl p-6 sm:p-8 max-w-lg w-full shadow-2xl border border-stone-200" 
                 @click.away="storyModalOpen = false">
                <div class="flex items-center justify-between pb-4 border-b border-stone-100 mb-4">
                    <div class="flex items-center gap-2.5">
                        <div class="w-9 h-9 rounded-full bg-stone-100 flex items-center justify-center font-serif font-bold text-stone-800">
                            {{ strtoupper(substr($vendor->shop_name, 0, 1)) }}
                        </div>
                        <div>
                            <h3 class="text-lg font-serif font-bold text-stone-900">About {{ $vendor->shop_name }}</h3>
                            <p class="text-[11px] text-stone-500">Heritage, brand story & store policies</p>
                        </div>
                    </div>
                    <button type="button" @click="storyModalOpen = false" class="text-stone-400 hover:text-black font-bold text-lg p-1">✕</button>
                </div>

                <div class="space-y-4 text-xs leading-relaxed text-stone-600 max-h-[60vh] overflow-y-auto pr-1">
                    <!-- Highlights list -->
                    @if(count($craftHighlights) > 0)
                        <div class="bg-stone-50 p-3 rounded-2xl border border-stone-100 space-y-2">
                            @foreach($craftHighlights as $highlight)
                                <div class="flex items-center gap-2 text-stone-800 font-medium">
                                    <span class="text-emerald-600 text-sm">✓</span>
                                    <span>{{ $highlight }}</span>
                                </div>
                            @endforeach
                        </div>
                    @endif

                    @if($vendor->description)
                        <div>
                            <h4 class="font-bold text-stone-900 uppercase tracking-wider text-[10px] mb-1">Our Story & Mission</h4>
                            <p>{{ $vendor->description }}</p>
                        </div>
                    @endif

                    @if($vendor->store_policies)
                        <div>
                            <h4 class="font-bold text-stone-900 uppercase tracking-wider text-[10px] mb-1">Craft Standards & Workshop Policies</h4>
                            <div class="bg-stone-50 p-3 rounded-2xl border border-stone-100">
                                {!! nl2br(e($vendor->store_policies)) !!}
                            </div>
                        </div>
                    @endif

                    <!-- Official Credentials -->
                    <div class="grid grid-cols-2 gap-3 pt-2 text-[11px]">
                        @if(!empty($vendor->pickup_address['city']))
                            <div class="bg-stone-50 p-2.5 rounded-xl border border-stone-100">
                                <span class="text-stone-400 block text-[9px] uppercase font-bold">Store Origin</span>
                                <span class="font-bold text-stone-800">{{ $vendor->pickup_address['city'] }}@if(!empty($vendor->pickup_address['state'])), {{ $vendor->pickup_address['state'] }}@endif</span>
                            </div>
                        @endif

                        <div class="bg-stone-50 p-2.5 rounded-xl border border-stone-100">
                            <span class="text-stone-400 block text-[9px] uppercase font-bold">Established</span>
                            <span class="font-bold text-stone-800">Est. {{ $establishedYear }}</span>
                        </div>

                        @if($vendor->contact_email)
                            <div class="bg-stone-50 p-2.5 rounded-xl border border-stone-100">
                                <span class="text-stone-400 block text-[9px] uppercase font-bold">Email Support</span>
                                <a href="mailto:{{ $vendor->contact_email }}" class="font-bold text-stone-800 hover:underline truncate block">{{ $vendor->contact_email }}</a>
                            </div>
                        @endif

                        @if($vendor->contact_phone)
                            <div class="bg-stone-50 p-2.5 rounded-xl border border-stone-100">
                                <span class="text-stone-400 block text-[9px] uppercase font-bold">Phone Support</span>
                                <a href="tel:{{ $vendor->contact_phone }}" class="font-bold text-stone-800 hover:underline">{{ $vendor->contact_phone }}</a>
                            </div>
                        @endif

                        @if($vendor->gstin)
                            <div class="bg-stone-50 p-2.5 rounded-xl border border-stone-100 col-span-2">
                                <span class="text-stone-400 block text-[9px] uppercase font-bold">GSTIN Registration</span>
                                <span class="font-mono font-bold text-stone-800">{{ $vendor->gstin }}</span>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Footer with Close & Direct WhatsApp -->
                <div class="mt-6 pt-4 border-t border-stone-100 flex items-center gap-3">
                    @if($whatsappUrl)
                        <a href="{{ $whatsappUrl }}" target="_blank"
                           class="flex-1 py-2.5 rounded-full bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs text-center transition-colors">
                            Talk on WhatsApp
                        </a>
                    @endif
                    <button type="button" @click="storyModalOpen = false" 
                            class="py-2.5 px-6 rounded-full bg-stone-100 hover:bg-stone-200 text-stone-800 font-bold text-xs transition-colors">
                        Close
                    </button>
                </div>
            </div>
        <!-- Floating WhatsApp Concierge FAB with radar ripple pulses -->
        @if($whatsappUrl)
            <div class="fixed bottom-6 right-6 z-40">
                <a href="{{ $whatsappUrl }}" target="_blank" rel="noopener"
                   aria-label="Direct WhatsApp Concierge"
                   class="relative flex items-center justify-center w-14 h-14 rounded-full bg-emerald-600 hover:bg-emerald-500 text-white shadow-2xl transition-all duration-300 hover:scale-110 active:scale-95 group">
                    <!-- Radar Ping Waves -->
                    <span class="absolute inset-0 rounded-full bg-emerald-400 opacity-75 animate-ping pointer-events-none"></span>
                    <svg class="w-7 h-7 fill-current relative z-10" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981z"/></svg>
                    <!-- Floating Hover Tooltip -->
                    <span class="absolute right-16 top-1/2 -translate-y-1/2 px-3 py-1.5 rounded-xl bg-stone-900/90 text-white text-[11px] font-bold whitespace-nowrap opacity-0 group-hover:opacity-100 transition-opacity duration-200 pointer-events-none shadow-lg">
                        Chat with {{ $vendor->shop_name }}
                    </span>
                </a>
            </div>
        @endif

        <style>
            .scroll-reveal {
                opacity: 0;
                transform: translateY(22px);
                transition: opacity 0.5s cubic-bezier(0.16, 1, 0.3, 1), transform 0.5s cubic-bezier(0.16, 1, 0.3, 1);
                will-change: opacity, transform;
            }
            .scroll-reveal.is-visible {
                opacity: 1;
                transform: translateY(0);
            }
            .glass-tilt-card {
                position: relative;
                overflow: hidden;
                border-radius: 24px;
            }
            .glass-tilt-card::after {
                content: '';
                position: absolute;
                top: 0;
                left: -120%;
                width: 60%;
                height: 100%;
                background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.22), transparent);
                transform: skewX(-20deg);
                transition: left 0.8s ease;
                pointer-events: none;
                z-index: 2;
            }
            .glass-tilt-card:hover::after {
                left: 160%;
            }
        </style>

        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const observer = new IntersectionObserver((entries) => {
                    entries.forEach(entry => {
                        if (entry.isIntersecting) {
                            entry.target.classList.add('is-visible');
                            observer.unobserve(entry.target);
                        }
                    });
                }, { threshold: 0.08 });

                document.querySelectorAll('.scroll-reveal').forEach(el => observer.observe(el));
            });
        </script>

    </div>
</x-layouts.public>
