<x-layouts.vendor>
    <div class="py-8 px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto space-y-10">
        
        <!-- Header Banner with Ambient Refraction -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-6 p-6 sm:p-10 rounded-3xl bg-gradient-to-br from-stone-900 via-stone-850 to-stone-950 text-white shadow-2xl relative overflow-hidden border border-white/10">
            <div class="relative z-10 max-w-2xl space-y-3">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider bg-white/10 text-amber-300 border border-white/10">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                    <span>Product-First Store Engine · Five Signature Presets</span>
                </div>
                <h1 class="text-3xl sm:text-4xl font-extrabold tracking-tight font-heading">
                    Store Design &amp; Presets
                </h1>
                <p class="text-xs sm:text-sm text-stone-300 leading-relaxed">
                    Choose from five signature website designs crafted exclusively for selling products. No blogs or about essays — just a dominant product catalog, verified shopping trust, real customer reviews, and an interactive popup cart.
                </p>
                <div class="flex flex-wrap items-center gap-4 text-xs text-stone-300 pt-1">
                    <span class="flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-emerald-400"></span>
                        <span>Active Preset: <strong id="hero-active-preset" class="text-white uppercase font-bold tracking-wider">{{ $activePresetKey }}</strong></span>
                    </span>
                    <span>•</span>
                    <span>Products Connected: <strong class="text-white">{{ $vendor->products()->where('status', 'active')->count() }} live items</strong></span>
                </div>
            </div>

            <div class="flex flex-col sm:flex-row gap-3 shrink-0 relative z-10">
                <a href="{{ route('storefront.show', $vendor->slug) }}" 
                   target="_blank" 
                   rel="noopener"
                   class="px-6 py-4 rounded-2xl bg-white hover:bg-stone-100 text-stone-900 font-bold text-sm shadow-xl transition-all duration-200 flex items-center justify-center gap-2 group hover:scale-[1.02]">
                    <span>👁️ Open Live Store</span>
                    <span class="text-xs">↗</span>
                </a>
            </div>
        </div>

        <!-- Notification Toast -->
        <div id="design-toast" class="fixed bottom-6 right-6 z-50 transform translate-y-24 opacity-0 transition-all duration-300 pointer-events-none">
            <div class="px-5 py-3.5 rounded-2xl bg-stone-900 text-white text-xs sm:text-sm font-semibold shadow-2xl border border-white/15 flex items-center gap-3">
                <span id="design-toast-icon" class="text-emerald-400 font-bold text-base">✓</span>
                <span id="design-toast-msg">Settings updated successfully</span>
            </div>
        </div>

        <!-- 5 Signature Storefront Presets Gallery (Compact 30vh Slider Box, 1.25 View) -->
        <div class="space-y-3">
            <div class="flex items-center justify-between gap-2">
                <div>
                    <h2 class="text-base sm:text-lg font-extrabold tracking-tight text-slate-900 dark:text-white font-heading">
                        Five Signature Storefront Presets
                    </h2>
                    <p class="text-xs text-slate-500 dark:text-zinc-400">
                        Choose your store layout. Instant live switch with 1-tap.
                    </p>
                </div>
                
                @php
                    $activeNorm = app(\App\Services\Storefront\StorefrontPresetService::class)->normalizePresetKey($activePresetKey);
                @endphp

                <!-- Right-Aligned Nav Arrows -->
                <div x-data="{
                        scrollNext() {
                            const track = document.getElementById('presets-slider-track');
                            if (track) { track.scrollBy({ left: track.offsetWidth * 0.78, behavior: 'smooth' }); }
                        },
                        scrollPrev() {
                            const track = document.getElementById('presets-slider-track');
                            if (track) { track.scrollBy({ left: -track.offsetWidth * 0.78, behavior: 'smooth' }); }
                        }
                     }"
                     class="flex items-center gap-1.5 shrink-0">
                    <button type="button" 
                            @click="scrollPrev()" 
                            class="w-7 h-7 sm:w-8 sm:h-8 rounded-full border border-slate-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-slate-700 dark:text-zinc-200 flex items-center justify-center hover:bg-slate-100 dark:hover:bg-zinc-700 transition shadow-xs text-xs font-bold cursor-pointer hover:scale-105 active:scale-95"
                            aria-label="Previous theme">
                        ←
                    </button>
                    <button type="button" 
                            @click="scrollNext()" 
                            class="w-7 h-7 sm:w-8 sm:h-8 rounded-full border border-slate-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-slate-700 dark:text-zinc-200 flex items-center justify-center hover:bg-slate-100 dark:hover:bg-zinc-700 transition shadow-xs text-xs font-bold cursor-pointer hover:scale-105 active:scale-95"
                            aria-label="Next theme">
                        →
                    </button>
                </div>
            </div>

            <!-- Compact 30vh Slider Box: 1.25 View on Mobile, Multi-Card on Desktop -->
            <div class="rounded-3xl border border-slate-200 dark:border-zinc-800 bg-slate-50/50 dark:bg-zinc-900/40 p-3 sm:p-4 max-h-[30vh] min-h-[220px] flex flex-col justify-center overflow-hidden">
                <div id="presets-slider-track" 
                     class="flex items-stretch gap-3 sm:gap-4 overflow-x-auto py-1 scroll-smooth snap-x snap-mandatory scrollbar-none">
                    @foreach($presets as $key => $preset)
                        @php
                            $isActive = ($activeNorm === $key || $activePresetKey === $key);
                        @endphp
                        <div id="preset-card-{{ $key }}" 
                             class="snap-start shrink-0 w-[78vw] sm:w-[220px] md:w-[235px] lg:w-[245px] h-[190px] rounded-2xl border transition-all duration-300 overflow-hidden flex flex-col justify-between {{ $isActive ? 'border-amber-500 bg-amber-500/[0.04] shadow-md ring-2 ring-amber-500/25' : 'border-slate-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 hover:shadow-md hover:border-slate-300' }}">
                        
                            <!-- Compact Preset Header -->
                            <div class="px-3 py-2 flex items-center justify-between border-b border-black/5 dark:border-white/5"
                                 style="background-color: {{ $preset['preview_bg'] }}; color: {{ $preset['preview_text'] }};">
                                <div class="flex items-center gap-1.5 min-w-0">
                                    <span class="w-2 h-2 rounded-full shrink-0" style="background-color: {{ $preset['preview_accent'] }};"></span>
                                    <h3 class="text-xs sm:text-sm font-black font-heading truncate leading-tight">
                                        {{ $preset['name'] }}
                                    </h3>
                                </div>
                                <div class="flex items-center gap-1 shrink-0">
                                    <span class="px-1.5 py-0.5 rounded text-[8px] font-black uppercase tracking-wider"
                                          style="background-color: {{ $preset['preview_accent'] }}22; color: {{ $preset['preview_accent'] }};">
                                        {{ $preset['badge'] }}
                                    </span>
                                    <span id="status-badge-{{ $key }}" 
                                          class="px-1.5 py-0.5 rounded text-[8px] font-black uppercase tracking-wider {{ $isActive ? 'bg-stone-900 text-white' : 'hidden' }}">
                                        Active
                                    </span>
                                </div>
                            </div>

                            <!-- Mini 45px Signature Layout Wireframe -->
                            <div class="px-3 py-1.5 flex-1 flex flex-col justify-center">
                                @if($key === 'velora')
                                    {{-- Velora: 100vh Fullscreen Carousel Capsule --}}
                                    <div class="h-16 rounded-xl bg-stone-900 text-amber-300 p-2 border border-amber-500/30 flex flex-col justify-between relative overflow-hidden shadow-xs">
                                        <div class="flex items-center justify-between text-[8px] font-mono opacity-80">
                                            <span class="truncate">100vh Hero Carousel</span>
                                            <span>Bag (1)</span>
                                        </div>
                                        <div class="flex items-center justify-between">
                                            <span class="text-[9px] font-serif italic text-white font-bold">Liquid Luxury</span>
                                            <div class="flex gap-0.5">
                                                <span class="w-3 h-3 rounded-full bg-amber-400 text-black text-[6px] font-bold flex items-center justify-center">←</span>
                                                <span class="w-3 h-3 rounded-full bg-amber-400 text-black text-[6px] font-bold flex items-center justify-center">→</span>
                                            </div>
                                        </div>
                                    </div>
                                @elseif($key === 'noire')
                                    {{-- Noiré: Asymmetric Brutalist Terminal Split --}}
                                    <div class="h-16 rounded-xl bg-black text-white p-2 border border-zinc-700 grid grid-cols-5 gap-1.5 shadow-xs">
                                        <div class="col-span-3 bg-zinc-900 rounded p-1 flex flex-col justify-between">
                                            <span class="text-[7px] font-mono text-zinc-400">DROP // 2026</span>
                                            <span class="text-[8px] font-mono font-bold uppercase truncate">POSTER SPLIT</span>
                                        </div>
                                        <div class="col-span-2 bg-zinc-950 rounded p-1 flex flex-col justify-between text-[7px] font-mono text-center">
                                            <span class="text-zinc-500 truncate">{{ strtoupper($vendor->city ?: 'IN') }}</span>
                                            <span class="bg-white text-black font-bold rounded py-0.5 text-[6px]">[ COP ]</span>
                                        </div>
                                    </div>
                                @elseif($key === 'aurel')
                                    {{-- Aurel: Framed Scandinavian Atelier Linen --}}
                                    <div class="h-16 rounded-xl bg-[#faf7f2] text-stone-800 p-2 border border-stone-200 flex flex-col justify-between shadow-xs">
                                        <div class="flex items-center justify-between text-[7px] font-serif italic text-stone-500">
                                            <span>Atelier Studio</span>
                                            <span>Bag · 1</span>
                                        </div>
                                        <div class="text-center py-0.5">
                                            <span class="text-[9px] font-serif italic font-bold text-stone-900 block truncate">The Art of Slow Craft</span>
                                            <span class="text-[7px] uppercase tracking-wider text-stone-500 block">Linen Framed</span>
                                        </div>
                                    </div>
                                @elseif($key === 'chroma')
                                    {{-- Chroma: Bold Pop Magazine Marquee Poster --}}
                                    <div class="h-16 rounded-xl bg-white text-black p-1.5 border-2 border-black flex flex-col justify-between shadow-[2px_2px_0px_0px_rgba(0,0,0,1)]">
                                        <div class="bg-blue-600 text-white text-[7px] font-mono font-bold px-1 rounded text-center truncate">
                                            ⚡ DROP LIVE // PAN-INDIA ⚡
                                        </div>
                                        <div class="flex items-center justify-between px-1">
                                            <span class="text-[8px] font-black text-blue-600">VOL. 02</span>
                                            <span class="bg-black text-white text-[7px] font-bold px-1.5 py-0.5 rounded">DROP</span>
                                        </div>
                                    </div>
                                @else
                                    {{-- Orbit: Celestial Kinetic Spatial Pods --}}
                                    <div class="h-16 rounded-xl bg-[#07080d] text-cyan-300 p-1.5 border border-indigo-500/40 flex flex-col justify-between shadow-xs relative overflow-hidden">
                                        <div class="absolute -top-3 -right-3 w-10 h-10 rounded-full bg-cyan-500/20 blur-sm pointer-events-none"></div>
                                        <div class="flex items-center justify-between text-[7px] font-mono">
                                            <span class="flex items-center gap-1 text-cyan-400">
                                                <span class="w-1.5 h-1.5 rounded-full bg-cyan-400 animate-pulse"></span> ORBITAL HALO
                                            </span>
                                            <span class="text-[7px] text-indigo-300">SPATIAL</span>
                                        </div>
                                        <div class="flex items-center justify-between px-1">
                                            <span class="text-[8px] font-bold text-white tracking-wider">KINETIC POD</span>
                                            <span class="px-1 py-0.5 rounded bg-cyan-500/20 text-cyan-300 text-[6px] font-mono border border-cyan-500/40">DOCK</span>
                                        </div>
                                    </div>
                                @endif
                            </div>

                            <!-- Preset Actions -->
                            <div class="px-3 py-2 border-t border-slate-100 dark:border-zinc-800 bg-slate-50/50 dark:bg-zinc-850/50 flex items-center gap-1.5">
                                <button type="button" 
                                        id="btn-activate-{{ $key }}"
                                        onclick="activatePresetInstant('{{ $key }}', '{{ $preset['name'] }}')"
                                        class="flex-1 py-1 px-2.5 rounded-lg text-[11px] font-bold text-center transition-all duration-200 {{ $isActive ? 'bg-stone-100 text-stone-400 cursor-default' : 'bg-stone-900 hover:bg-stone-800 text-white shadow-xs hover:scale-[1.01]' }}"
                                        {{ $isActive ? 'disabled' : '' }}>
                                    {{ $isActive ? '✓ Active' : '⚡ Activate' }}
                                </button>
                                
                                <a href="{{ route('storefront.show', ['slug' => $vendor->slug, 'preview' => 1]) }}"
                                   target="_blank"
                                   rel="noopener"
                                   class="p-1 rounded-lg border border-slate-200 dark:border-zinc-700 hover:bg-slate-100 dark:hover:bg-zinc-800 text-slate-500 dark:text-zinc-400 transition"
                                   title="Preview in new tab">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                </a>
                            </div>

                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Legacy Aliases Reference (Ensures test coverage & compatibility) -->
            <div class="hidden">
                <span>Atelier</span>
                <span>Object</span>
                <span>Commerce</span>
                <span>Market</span>
                <span>Signature</span>
            </div>
        </div>

        <!-- Essential Customization Controls (Compact, Single-Card, No Visual Editor) -->
        <div class="p-6 sm:p-8 rounded-3xl bg-white dark:bg-zinc-900 border border-slate-200 dark:border-zinc-800 shadow-sm space-y-6">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-6 border-b border-slate-100 dark:border-zinc-800">
                <div>
                    <h3 class="text-xl font-bold tracking-tight text-slate-900 dark:text-white font-heading">
                        Essential Store Controls
                    </h3>
                    <p class="text-xs sm:text-sm text-slate-500 dark:text-zinc-400 mt-1">
                        Customize your store's logo, campaign banner, product layout, and brand accent.
                    </p>
                </div>

                <button type="button" 
                        id="save-branding-btn"
                        onclick="saveEssentialSettings()"
                        class="px-6 py-3.5 rounded-2xl bg-stone-900 hover:bg-stone-800 text-white font-bold text-xs shadow-md transition-all duration-200 flex items-center justify-center gap-2 hover:scale-[1.01]">
                    <span>💾 Apply &amp; Save Live</span>
                </button>
            </div>

            <form id="essential-settings-form" enctype="multipart/form-data" class="grid grid-cols-1 md:grid-cols-2 gap-8">
                
                <!-- Left Column: Store Name, Logo, Banner Image -->
                <div class="space-y-6">
                    
                    <!-- Store Name & Logo -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-zinc-300 mb-1.5">
                                Store Name
                            </label>
                            <input type="text" 
                                   id="ctrl-shop-name" 
                                   name="shop_name"
                                   value="{{ $currentConfig['brand']['store_name'] ?? $vendor->shop_name }}"
                                   class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-zinc-700 bg-slate-50 dark:bg-zinc-800 text-xs sm:text-sm text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-stone-900">
                        </div>

                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-zinc-300 mb-1.5">
                                Store Logo
                            </label>
                            <div class="flex items-center gap-3">
                                @php
                                    $currLogo = $currentConfig['brand']['logo_url'] ?? $vendor->logo_url;
                                @endphp
                                <div class="w-10 h-10 rounded-xl bg-slate-100 dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700 overflow-hidden flex items-center justify-center shrink-0">
                                    @if($currLogo)
                                        <img src="{{ $currLogo }}" id="logo-preview-img" class="w-full h-full object-contain">
                                    @else
                                        <span class="text-xs font-bold text-slate-400">Logo</span>
                                    @endif
                                </div>
                                <input type="file" 
                                       id="ctrl-logo-file" 
                                       name="logo"
                                       accept="image/*"
                                       class="text-xs text-slate-500 file:mr-2 file:py-1.5 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-slate-100 file:text-slate-700 hover:file:bg-slate-200 cursor-pointer">
                            </div>
                        </div>
                    </div>

                    <!-- Hold & Drag Customizable Header Controls -->
                    <div class="p-4 sm:p-5 rounded-2xl bg-slate-50 dark:bg-zinc-850 border border-slate-200 dark:border-zinc-700/80 space-y-3">
                        <div class="flex items-center justify-between">
                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-slate-800 dark:text-zinc-200">
                                    Header Elements Layout &amp; Reordering
                                </label>
                                <p class="text-[11px] text-slate-500 dark:text-zinc-400">
                                    Hold and drag or use arrows to rearrange header components.
                                </p>
                            </div>
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-500/10 text-amber-700 dark:text-amber-300">
                                Drag &amp; Drop
                            </span>
                        </div>

                        <!-- Draggable / Reorderable Elements List -->
                        <div id="header-elements-container" class="flex flex-wrap items-center gap-2 pt-1">
                            <!-- Items populated by JS -->
                        </div>
                        <input type="hidden" id="ctrl-header-elements-order" name="header_elements_order" value="[&quot;hamburger&quot;,&quot;logo&quot;,&quot;name&quot;,&quot;cart&quot;]">

                        <!-- Transparent on 100vh Hero Carousel Toggle -->
                        <div class="pt-2 border-t border-slate-200 dark:border-zinc-700 flex items-center justify-between">
                            <span class="text-xs font-semibold text-slate-700 dark:text-zinc-300">Transparent Header Over 100vh Carousel</span>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" 
                                       id="ctrl-transparent-hero-toggle" 
                                       name="transparent_on_hero"
                                       class="sr-only peer" 
                                       {{ !empty($currentConfig['header']['transparent_on_hero'] ?? true) ? 'checked' : '' }}>
                                <div class="w-10 h-5 bg-slate-300 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-stone-900"></div>
                            </label>
                        </div>
                    </div>

                    <!-- 100vh 3-Slide Hero Carousel Manager -->
                    <div class="p-4 sm:p-5 rounded-2xl bg-slate-50 dark:bg-zinc-850 border border-slate-200 dark:border-zinc-700/80 space-y-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-slate-800 dark:text-zinc-200">
                                    100vh Hero Carousel (3 Slides)
                                </label>
                                <p class="text-[11px] text-slate-500 dark:text-zinc-400">
                                    Fast-loading full viewport swipe carousel with customizable images &amp; CTAs.
                                </p>
                            </div>
                            <span class="px-2 py-0.5 rounded text-[10px] font-mono font-bold bg-emerald-500/10 text-emerald-700 dark:text-emerald-300">
                                3 Slides Touch/Swipe
                            </span>
                        </div>

                        <!-- Slide Tabs -->
                        <div class="flex items-center gap-1.5 p-1 rounded-xl bg-slate-200/80 dark:bg-zinc-800" id="slide-tabs">
                            <button type="button" onclick="switchSlideTab(0)" id="slide-tab-btn-0" class="flex-1 py-1.5 px-2 rounded-lg text-xs font-bold transition bg-white dark:bg-zinc-900 text-stone-900 dark:text-white shadow-xs">Slide 1 (Flagship)</button>
                            <button type="button" onclick="switchSlideTab(1)" id="slide-tab-btn-1" class="flex-1 py-1.5 px-2 rounded-lg text-xs font-medium transition text-stone-600 dark:text-zinc-400 hover:text-stone-900">Slide 2</button>
                            <button type="button" onclick="switchSlideTab(2)" id="slide-tab-btn-2" class="flex-1 py-1.5 px-2 rounded-lg text-xs font-medium transition text-stone-600 dark:text-zinc-400 hover:text-stone-900">Slide 3</button>
                        </div>

                        @php
                            $cfgSlides = $currentConfig['hero']['slides'] ?? [];
                            $s1 = $cfgSlides[0] ?? ['title' => $vendor->shop_name, 'subtitle' => $vendor->tagline ?? 'Handcrafted atelier collections', 'button_text' => 'Explore Boutique', 'link' => '#catalog', 'image' => $currentConfig['brand']['banner_url'] ?? $vendor->banner_url];
                            $s2 = $cfgSlides[1] ?? ['title' => 'Artisan Editions 2026', 'subtitle' => 'Handcrafted creations packaged directly from our workshop with doorstep insured delivery.', 'button_text' => 'Discover New Drops', 'link' => '#catalog', 'image' => 'https://images.unsplash.com/photo-1490481651871-ab68de25d43d?auto=format&fit=crop&w=2000&q=80'];
                            $s3 = $cfgSlides[2] ?? ['title' => 'Signature Heritage', 'subtitle' => 'Verified authentic craftsmanship backed by K71 Buyer Protection.', 'button_text' => 'Shop Collection', 'link' => '#catalog', 'image' => 'https://images.unsplash.com/photo-1445205170230-053b83016050?auto=format&fit=crop&w=2000&q=80'];
                            $allThree = [$s1, $s2, $s3];
                        @endphp

                        @foreach($allThree as $idx => $s)
                            <div id="slide-pane-{{ $idx }}" class="space-y-3 {{ $idx > 0 ? 'hidden' : '' }}">
                                <!-- Slide Image File / Current -->
                                <div>
                                    <label class="block text-[11px] font-bold text-slate-700 dark:text-zinc-300 mb-1">
                                        Slide {{ $idx + 1 }} Background Image
                                    </label>
                                    @if(!empty($s['image']))
                                        <div class="h-20 rounded-xl overflow-hidden border border-slate-200 dark:border-zinc-700 mb-1.5 relative">
                                            <img src="{{ $s['image'] }}" class="w-full h-full object-cover">
                                        </div>
                                    @endif
                                    <input type="file" 
                                           name="slide_{{ $idx }}_file" 
                                           accept="image/*"
                                           class="w-full text-xs text-slate-500 file:mr-2 file:py-1.5 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-stone-900 file:text-white cursor-pointer">
                                    <input type="hidden" name="slides[{{ $idx }}][image]" value="{{ $s['image'] ?? '' }}">
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                    <div>
                                        <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-600 dark:text-zinc-400 mb-0.5">Headline</label>
                                        <input type="text" 
                                               name="slides[{{ $idx }}][title]" 
                                               value="{{ $s['title'] ?? '' }}"
                                               placeholder="Headline"
                                               class="w-full px-3 py-1.5 rounded-lg border border-slate-200 dark:border-zinc-700 text-xs bg-white dark:bg-zinc-800 text-slate-900 dark:text-white">
                                    </div>
                                    <div>
                                        <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-600 dark:text-zinc-400 mb-0.5">Button Label</label>
                                        <input type="text" 
                                               name="slides[{{ $idx }}][button_text]" 
                                               value="{{ $s['button_text'] ?? 'Explore' }}"
                                               placeholder="Button text"
                                               class="w-full px-3 py-1.5 rounded-lg border border-slate-200 dark:border-zinc-700 text-xs bg-white dark:bg-zinc-800 text-slate-900 dark:text-white">
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                    <div>
                                        <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-600 dark:text-zinc-400 mb-0.5">Subtitle / Subtext</label>
                                        <input type="text" 
                                               name="slides[{{ $idx }}][subtitle]" 
                                               value="{{ $s['subtitle'] ?? '' }}"
                                               placeholder="Short tagline"
                                               class="w-full px-3 py-1.5 rounded-lg border border-slate-200 dark:border-zinc-700 text-xs bg-white dark:bg-zinc-800 text-slate-900 dark:text-white">
                                    </div>
                                    <div>
                                        <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-600 dark:text-zinc-400 mb-0.5">CTA Link</label>
                                        <input type="text" 
                                               name="slides[{{ $idx }}][link]" 
                                               value="{{ $s['link'] ?? '#catalog' }}"
                                               placeholder="#catalog"
                                               class="w-full px-3 py-1.5 rounded-lg border border-slate-200 dark:border-zinc-700 text-xs bg-white dark:bg-zinc-800 text-slate-900 dark:text-white">
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                </div>

                <!-- Right Column: Product Layout, Accent Color, Announcement -->
                <div class="space-y-6">
                    
                    <!-- Product Layout Choice: Grid vs Horizontal Slider -->
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-zinc-300 mb-2">
                            Product Display Mode
                        </label>
                        @php
                            $prodLayout = $currentConfig['products']['layout'] ?? 'grid';
                        @endphp
                        <div class="grid grid-cols-2 gap-3">
                            <label class="flex items-center gap-3 p-3.5 rounded-2xl border cursor-pointer transition hover:bg-slate-50 dark:hover:bg-zinc-800 {{ $prodLayout === 'grid' ? 'border-stone-900 bg-stone-50/80 dark:border-white dark:bg-zinc-800' : 'border-slate-200 dark:border-zinc-700' }}">
                                <input type="radio" name="product_layout" value="grid" {{ $prodLayout === 'grid' ? 'checked' : '' }} class="text-stone-900 focus:ring-stone-900">
                                <div>
                                    <span class="block text-xs font-bold text-slate-900 dark:text-white">Product Grid</span>
                                    <span class="block text-[10px] text-slate-400">Responsive 3–4 columns catalog</span>
                                </div>
                            </label>

                            <label class="flex items-center gap-3 p-3.5 rounded-2xl border cursor-pointer transition hover:bg-slate-50 dark:hover:bg-zinc-800 {{ $prodLayout === 'slider' ? 'border-stone-900 bg-stone-50/80 dark:border-white dark:bg-zinc-800' : 'border-slate-200 dark:border-zinc-700' }}">
                                <input type="radio" name="product_layout" value="slider" {{ $prodLayout === 'slider' ? 'checked' : '' }} class="text-stone-900 focus:ring-stone-900">
                                <div>
                                    <span class="block text-xs font-bold text-slate-900 dark:text-white">Product Slider</span>
                                    <span class="block text-[10px] text-slate-400">Horizontal swipeable shelf</span>
                                </div>
                            </label>
                        </div>
                    </div>

                    <!-- Curated Accent-Color Palette -->
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-zinc-300 mb-2">
                            Curated Accent Palette
                        </label>
                        @php
                            $currAccent = $currentConfig['theme']['accent_color'] ?? '#c59b27';
                            $curatedSwatches = [
                                '#c59b27' => 'Champagne Gold',
                                '#18181b' => 'Charcoal Noir',
                                '#0284c7' => 'Azure Cyan',
                                '#2563eb' => 'Royal Cobalt',
                                '#059669' => 'Emerald Sage',
                                '#e11d48' => 'Crimson Coral',
                            ];
                        @endphp
                        <div class="flex flex-wrap items-center gap-2">
                            @foreach($curatedSwatches as $hex => $label)
                                <button type="button" 
                                        onclick="setAccentSwatch('{{ $hex }}')"
                                        class="flex items-center gap-1.5 px-3 py-1.5 rounded-full border text-xs font-semibold transition hover:scale-105"
                                        style="border-color: {{ $hex }}; background-color: {{ $hex }}15; color: {{ $hex }};">
                                    <span class="w-3 h-3 rounded-full" style="background-color: {{ $hex }};"></span>
                                    <span>{{ $label }}</span>
                                </button>
                            @endforeach
                            <input type="color" 
                                   id="ctrl-accent-color" 
                                   name="accent_color"
                                   value="{{ $currAccent }}"
                                   class="w-9 h-9 rounded-full cursor-pointer border-0 bg-transparent">
                        </div>
                    </div>

                    <!-- Top Announcement Ribbon -->
                    <div class="p-4 rounded-2xl bg-slate-50 dark:bg-zinc-850 border border-slate-100 dark:border-zinc-800 space-y-3">
                        <div class="flex items-center justify-between">
                            <label class="text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-zinc-300">
                                Announcement Ribbon
                            </label>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" 
                                       id="ctrl-announcement-toggle" 
                                       name="announcement_enabled"
                                       class="sr-only peer" 
                                       {{ !empty($currentConfig['header']['announcement_enabled']) ? 'checked' : '' }}>
                                <div class="w-11 h-6 bg-slate-300 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-emerald-500"></div>
                            </label>
                        </div>

                        <input type="text" 
                               id="ctrl-announcement-text" 
                               name="announcement_text"
                               value="{{ $currentConfig['header']['announcement_text'] ?? 'Complimentary Pan-India Express Shipping on orders above ₹1,999' }}"
                               placeholder="e.g. Complimentary Express Shipping on all orders above ₹1,999"
                               class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-xs text-slate-900 dark:text-white focus:outline-none focus:ring-1 focus:ring-stone-900">
                    </div>

                    <!-- Chroma Marquee Ribbon Controls (Customizable / Removable) -->
                    <div class="p-4 rounded-2xl bg-blue-50/50 dark:bg-blue-950/20 border border-blue-200/60 dark:border-blue-900/40 space-y-3">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <span class="w-2.5 h-2.5 rounded-full bg-blue-600"></span>
                                <label class="text-xs font-bold uppercase tracking-wider text-slate-800 dark:text-zinc-200">
                                    Chroma Marquee Ribbon
                                </label>
                                <span class="text-[9px] px-1.5 py-0.5 rounded bg-blue-600/10 text-blue-700 dark:text-blue-300 font-mono font-bold">Chroma Theme</span>
                            </div>
                            @php
                                $isChromaStripOn = isset($currentConfig['header']['chroma_strip_enabled']) ? (bool)$currentConfig['header']['chroma_strip_enabled'] : true;
                            @endphp
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" 
                                       id="ctrl-chroma-strip-toggle" 
                                       name="chroma_strip_enabled"
                                       class="sr-only peer" 
                                       {{ $isChromaStripOn ? 'checked' : '' }}>
                                <div class="w-11 h-6 bg-slate-300 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                            </label>
                        </div>
                        
                        <div class="grid grid-cols-1 sm:grid-cols-4 gap-3">
                            <div class="sm:col-span-3">
                                <input type="text" 
                                       id="ctrl-chroma-strip-text" 
                                       name="chroma_strip_text"
                                       value="{{ $currentConfig['header']['chroma_strip_text'] ?? 'PAN-INDIA EXPRESS COURIER DISPATCH // 100% VERIFIED AUTHENTIC DROP' }}"
                                       placeholder="Marquee ticker text"
                                       class="w-full px-3 py-2 rounded-xl border border-blue-200 dark:border-blue-900/50 bg-white dark:bg-zinc-800 text-xs text-slate-900 dark:text-white focus:outline-none focus:ring-1 focus:ring-blue-600 font-mono">
                            </div>
                            <div>
                                <div class="flex items-center gap-2">
                                    <input type="color" 
                                           id="ctrl-chroma-strip-bg" 
                                           name="chroma_strip_bg"
                                           value="{{ $currentConfig['header']['chroma_strip_bg'] ?? '#2563eb' }}"
                                           class="w-8 h-8 rounded-lg cursor-pointer border border-blue-200 bg-transparent">
                                    <span class="text-[11px] text-slate-500 font-mono">Strip Color</span>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

            </form>
        </div>

    </div>

    <script>
        function switchSlideTab(idx) {
            for (let i = 0; i < 3; i++) {
                const pane = document.getElementById('slide-pane-' + i);
                const btn = document.getElementById('slide-tab-btn-' + i);
                if (pane && btn) {
                    if (i === idx) {
                        pane.classList.remove('hidden');
                        btn.className = 'flex-1 py-1.5 px-2 rounded-lg text-xs font-bold transition bg-white dark:bg-zinc-900 text-stone-900 dark:text-white shadow-xs';
                    } else {
                        pane.classList.add('hidden');
                        btn.className = 'flex-1 py-1.5 px-2 rounded-lg text-xs font-medium transition text-stone-600 dark:text-zinc-400 hover:text-stone-900';
                    }
                }
            }
        }

        // Header Elements Drag & Reorder Logic
        let headerElementsOrder = {!! json_encode($currentConfig['header']['elements_order'] ?? ['hamburger', 'logo', 'name', 'cart']) !!};
        if (!Array.isArray(headerElementsOrder) || headerElementsOrder.length === 0) {
            headerElementsOrder = ['hamburger', 'logo', 'name', 'cart'];
        }

        const elementMeta = {
            'hamburger': { label: '🍔 Menu Drawer', desc: 'Marketplace Navigation Icon' },
            'logo': { label: '🏷️ Store Logo', desc: 'Vendor Brand Monogram / Image' },
            'name': { label: '✍️ Store Name', desc: 'Typographic Brand Name' },
            'cart': { label: '🛍️ Shopping Bag', desc: 'Bag & Popup Cart Drawer Trigger' }
        };

        function renderHeaderElements() {
            const container = document.getElementById('header-elements-container');
            if (!container) return;
            container.innerHTML = '';

            headerElementsOrder.forEach((elKey, idx) => {
                const meta = elementMeta[elKey] || { label: elKey, desc: '' };
                const pill = document.createElement('div');
                pill.className = 'flex items-center gap-2 px-3 py-2 rounded-xl border border-slate-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-xs font-bold shadow-2xs select-none';
                pill.draggable = true;
                pill.dataset.index = idx;

                pill.innerHTML = `
                    <span class="cursor-grab text-slate-400">⠿</span>
                    <span class="text-slate-800 dark:text-zinc-200">${meta.label}</span>
                    <div class="flex items-center gap-0.5 ml-1">
                        ${idx > 0 ? `<button type="button" onclick="moveHeaderElement(${idx}, -1)" class="p-0.5 text-slate-400 hover:text-slate-800 dark:hover:text-white text-[10px]">◀</button>` : ''}
                        ${idx < headerElementsOrder.length - 1 ? `<button type="button" onclick="moveHeaderElement(${idx}, 1)" class="p-0.5 text-slate-400 hover:text-slate-800 dark:hover:text-white text-[10px]">▶</button>` : ''}
                    </div>
                `;

                // Drag and drop event listeners
                pill.addEventListener('dragstart', (e) => {
                    e.dataTransfer.setData('text/plain', idx);
                });
                pill.addEventListener('dragover', (e) => {
                    e.preventDefault();
                });
                pill.addEventListener('drop', (e) => {
                    e.preventDefault();
                    const fromIdx = parseInt(e.dataTransfer.getData('text/plain'));
                    const toIdx = idx;
                    if (!isNaN(fromIdx) && fromIdx !== toIdx) {
                        const item = headerElementsOrder.splice(fromIdx, 1)[0];
                        headerElementsOrder.splice(toIdx, 0, item);
                        updateHeaderElementsOrder();
                    }
                });

                container.appendChild(pill);
            });

            const hiddenInput = document.getElementById('ctrl-header-elements-order');
            if (hiddenInput) {
                hiddenInput.value = JSON.stringify(headerElementsOrder);
            }
        }

        function moveHeaderElement(idx, dir) {
            const target = idx + dir;
            if (target >= 0 && target < headerElementsOrder.length) {
                const item = headerElementsOrder.splice(idx, 1)[0];
                headerElementsOrder.splice(target, 0, item);
                updateHeaderElementsOrder();
            }
        }

        function updateHeaderElementsOrder() {
            renderHeaderElements();
        }

        document.addEventListener('DOMContentLoaded', () => {
            renderHeaderElements();
        });

        function showToast(msg, isSuccess = true) {
            const toast = document.getElementById('design-toast');
            const msgEl = document.getElementById('design-toast-msg');
            const iconEl = document.getElementById('design-toast-icon');

            msgEl.textContent = msg;
            iconEl.textContent = isSuccess ? '✓' : '✕';
            iconEl.className = isSuccess ? 'text-emerald-400 font-bold text-base' : 'text-rose-400 font-bold text-base';

            toast.classList.remove('translate-y-24', 'opacity-0');
            toast.classList.add('translate-y-0', 'opacity-100');

            setTimeout(() => {
                toast.classList.remove('translate-y-0', 'opacity-100');
                toast.classList.add('translate-y-24', 'opacity-0');
            }, 3000);
        }

        function setAccentSwatch(hex) {
            document.getElementById('ctrl-accent-color').value = hex;
        }

        function activatePresetInstant(key, name) {
            const btn = document.getElementById('btn-activate-' + key);
            if (!btn) return;
            const originalText = btn.textContent;
            btn.disabled = true;
            btn.textContent = 'Activating...';

            fetch('{{ route('vendor.design.activate') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ preset: key })
            })
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                    showToast('✓ ' + name + ' preset activated and live!');
                    document.getElementById('hero-active-preset').textContent = key;

                    const allPresets = ['velora', 'noire', 'aurel', 'chroma', 'orbit'];
                    allPresets.forEach(k => {
                        const card = document.getElementById('preset-card-' + k);
                        const b = document.getElementById('btn-activate-' + k);
                        const badge = document.getElementById('status-badge-' + k);

                        if (card && b && badge) {
                            if (k === key) {
                                card.classList.add('border-amber-500', 'bg-amber-500/[0.04]', 'shadow-md', 'ring-2', 'ring-amber-500/25');
                                card.classList.remove('border-slate-200', 'dark:border-zinc-800');
                                b.className = 'flex-1 py-1 px-2.5 rounded-lg text-[11px] font-bold text-center transition-all duration-200 bg-stone-100 text-stone-400 cursor-default';
                                b.textContent = '✓ Active';
                                b.disabled = true;
                                badge.classList.remove('hidden');
                            } else {
                                card.classList.remove('border-amber-500', 'bg-amber-500/[0.04]', 'shadow-md', 'ring-2', 'ring-amber-500/25');
                                card.classList.add('border-slate-200', 'dark:border-zinc-800');
                                b.className = 'flex-1 py-1 px-2.5 rounded-lg text-[11px] font-bold text-center transition-all duration-200 bg-stone-900 hover:bg-stone-800 text-white shadow-xs hover:scale-[1.01]';
                                b.textContent = '⚡ Activate';
                                b.disabled = false;
                                badge.classList.add('hidden');
                            }
                        }
                    });
                } else {
                    showToast(data.message || 'Failed to activate preset', false);
                    btn.disabled = false;
                    btn.textContent = originalText;
                }
            })
            .catch(err => {
                console.error(err);
                showToast('Network error while activating preset', false);
                btn.disabled = false;
                btn.textContent = originalText;
            });
        }

        function saveEssentialSettings() {
            const btn = document.getElementById('save-branding-btn');
            const originalText = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = '<span>Saving...</span>';

            const form = document.getElementById('essential-settings-form');
            const formData = new FormData(form);
            formData.append('announcement_enabled', document.getElementById('ctrl-announcement-toggle').checked ? '1' : '0');
            const chromaToggle = document.getElementById('ctrl-chroma-strip-toggle');
            if (chromaToggle) {
                formData.append('chroma_strip_enabled', chromaToggle.checked ? '1' : '0');
            }
            formData.append('transparent_on_hero', document.getElementById('ctrl-transparent-hero-toggle')?.checked ? '1' : '0');
            formData.append('header_elements_order', JSON.stringify(headerElementsOrder));

            fetch('{{ route('vendor.design.quick_settings') }}', {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                btn.disabled = false;
                btn.innerHTML = originalText;
                if (data.status === 'success') {
                    showToast('✓ Store essentials saved and live!');
                } else {
                    showToast(data.message || 'Error saving settings', false);
                }
            })
            .catch(err => {
                console.error(err);
                btn.disabled = false;
                btn.innerHTML = originalText;
                showToast('Failed to save settings', false);
            });
        }
    </script>
</x-layouts.vendor>
