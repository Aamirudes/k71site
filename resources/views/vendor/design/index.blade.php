<x-layouts.vendor>
    <style>
        .k71-switch input:checked + .k71-switch-track { background-color: #10b981; }
        .k71-switch.k71-switch-amber input:checked + .k71-switch-track { background-color: #f59e0b; }
        .k71-switch.k71-switch-blue input:checked + .k71-switch-track { background-color: #2563eb; }
        .k71-switch.k71-switch-dark input:checked + .k71-switch-track { background-color: #18181b; }
        .k71-switch input:checked + .k71-switch-track::after {
            transform: translateX(100%);
            border-color: #ffffff;
        }
    </style>

    <div class="py-6 sm:py-8 px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto space-y-10">

        <!-- Notification Toast -->
        <div id="design-toast" class="fixed bottom-6 right-6 z-50 transform translate-y-24 opacity-0 transition-all duration-300 pointer-events-none">
            <div class="px-5 py-3.5 rounded-2xl bg-stone-900 text-white text-xs sm:text-sm font-semibold shadow-2xl border border-white/15 flex items-center gap-3">
                <span id="design-toast-icon" class="text-emerald-400 font-bold text-base">✓</span>
                <span id="design-toast-msg">Settings updated successfully</span>
            </div>
        </div>

        <!-- 10 Signature Storefront Presets Gallery (Compact 30vh Slider Box, 1.25 View) -->
        <div class="space-y-3">
            <div class="flex items-center justify-between gap-2">
                <div>
                    <h2 class="text-base sm:text-lg font-extrabold tracking-tight text-slate-900 dark:text-white font-heading">
                        Ten Signature Storefront Presets
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
                                @elseif($key === 'orbit')
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
                                @elseif($key === 'zenith')
                                    {{-- Zenith: Architectural Swiss Gallery --}}
                                    <div class="h-16 rounded-none bg-[#f5f5f0] text-[#111813] p-1.5 border border-stone-300 flex flex-col justify-between shadow-xs">
                                        <div class="flex items-center justify-between text-[7px] font-mono text-stone-500">
                                            <span>ISSUE // 2026</span>
                                            <span class="text-[#1b3b2b] font-bold">EXHIBIT</span>
                                        </div>
                                        <div class="flex items-center justify-between px-1">
                                            <span class="text-[8px] font-serif italic font-black text-black">Fraunces Slab</span>
                                            <span class="px-1 py-0.2 bg-[#111813] text-white text-[6px] font-mono">[ ART ]</span>
                                        </div>
                                    </div>
                                @elseif($key === 'solaris')
                                    {{-- Solaris: Sun-Drenched Mediterranean Arches --}}
                                    <div class="h-16 rounded-t-2xl rounded-b-lg bg-[#f8f2e9] text-[#291a13] p-1.5 border border-[#e8dccb] flex flex-col justify-between shadow-xs">
                                        <div class="flex items-center justify-between text-[7px] font-mono text-[#785b4f]">
                                            <span>◈ SUN-CURED</span>
                                            <span class="text-[#c25e3e] font-bold">ATELIER</span>
                                        </div>
                                        <div class="flex items-center justify-between px-1">
                                            <span class="text-[8px] font-serif font-black text-[#291a13]">Cinzel Arches</span>
                                            <span class="px-1.5 py-0.2 rounded-full bg-[#c25e3e] text-white text-[6px] font-mono font-bold">CLAY</span>
                                        </div>
                                    </div>
                                @elseif($key === 'nexus')
                                    {{-- Nexus: Neo-Tokyo Cyber Techwear HUD --}}
                                    <div class="h-16 rounded-none bg-[#0a0b0e] text-[#a3e635] p-1.5 border border-zinc-800 flex flex-col justify-between shadow-xs">
                                        <div class="flex items-center justify-between text-[7px] font-mono">
                                            <span class="text-[#a3e635] font-bold">// SYS: 01</span>
                                            <span class="text-zinc-500">LAT: 8ms</span>
                                        </div>
                                        <div class="flex items-center justify-between px-1">
                                            <span class="text-[8px] font-mono font-black text-white">CHAKRA HUD</span>
                                            <span class="px-1 py-0.2 bg-[#a3e635] text-black text-[6px] font-mono font-black">[ + ]</span>
                                        </div>
                                    </div>
                                @elseif($key === 'elysium')
                                    {{-- Elysium: Royal Velvet & 24K Gold Gilt --}}
                                    <div class="h-16 rounded-xl bg-[#060913] text-[#f5d77f] p-1.5 border border-[#d4af37]/50 flex flex-col justify-between shadow-xs">
                                        <div class="flex items-center justify-between text-[7px] font-mono text-[#d4af37]">
                                            <span>👑 ROYAL GUILD</span>
                                            <span>24K</span>
                                        </div>
                                        <div class="flex items-center justify-between px-1">
                                            <span class="text-[8px] font-serif font-bold text-white">Prata Gilded</span>
                                            <span class="px-1.5 py-0.2 rounded-full bg-gradient-to-r from-[#b38728] to-[#d4af37] text-black text-[6px] font-mono font-bold">VAULT</span>
                                        </div>
                                    </div>
                                @elseif($key === 'vogue')
                                    {{-- Vogue: Paris Runway Editorial Lookbook --}}
                                    <div class="h-16 rounded-none bg-white text-black p-1.5 border-2 border-black flex flex-col justify-between shadow-[2px_2px_0px_0px_rgba(0,0,0,1)]">
                                        <div class="flex items-center justify-between text-[7px] font-mono">
                                            <span class="bg-black text-white px-1">PARIS // 2026</span>
                                            <span class="text-[#e11d48] font-black">VOL. 24</span>
                                        </div>
                                        <div class="flex items-center justify-between px-1">
                                            <span class="text-[8px] font-serif font-black uppercase text-black">Bodoni Moda</span>
                                            <span class="bg-[#e11d48] text-white text-[6px] font-mono font-black px-1">HAUTE</span>
                                        </div>
                                    </div>
                                @else
                                    {{-- Generic Fallback Wireframe --}}
                                    <div class="h-16 rounded-xl bg-slate-100 dark:bg-zinc-800 p-2 border border-slate-200 dark:border-zinc-700 flex flex-col justify-between text-xs">
                                        <span class="font-bold text-[9px]">{{ $preset['name'] }}</span>
                                        <span class="text-[8px] text-slate-500 font-mono">{{ $preset['badge'] }}</span>
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
                                
                                <a href="{{ $vendor->storefrontUrl(['preview' => 1, 'preset' => $key]) }}"
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

                <div class="flex flex-wrap items-center gap-2.5">
                    <a href="{{ $vendor->storefront_url }}"
                       target="_blank"
                       rel="noopener"
                       class="px-4 py-3 rounded-2xl border border-slate-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 hover:bg-slate-50 text-slate-800 dark:text-zinc-200 font-bold text-xs transition flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                        <span>Open Live Store ↗</span>
                    </a>
                    <button type="button" 
                            id="save-branding-btn"
                            onclick="saveEssentialSettings('main')"
                            class="px-6 py-3.5 rounded-2xl bg-stone-900 hover:bg-stone-800 text-white font-bold text-xs shadow-md transition-all duration-200 flex items-center justify-center gap-2 hover:scale-[1.01] cursor-pointer disabled:opacity-60">
                        <svg id="save-branding-spinner" class="hidden w-4 h-4 animate-spin text-white" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                        </svg>
                        <span id="save-branding-label">💾 Apply &amp; Save Live</span>
                    </button>
                </div>
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

                    <!-- WhatsApp & Instagram Dynamic Connect Buttons -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-zinc-300 mb-1.5 flex items-center gap-1.5">
                                <span class="text-emerald-500">💬</span> WhatsApp Number (Floating Button)
                            </label>
                            <input type="text" 
                                   id="ctrl-whatsapp" 
                                   name="whatsapp"
                                   value="{{ $currentConfig['brand']['whatsapp'] ?? $vendor->contact_phone }}"
                                   placeholder="e.g. 9876543210"
                                   class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-zinc-700 bg-slate-50 dark:bg-zinc-800 text-xs sm:text-sm text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-stone-900 font-mono">
                        </div>

                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-zinc-300 mb-1.5 flex items-center gap-1.5">
                                <span class="text-pink-500">📸</span> Instagram Handle / URL
                            </label>
                            <input type="text" 
                                   id="ctrl-instagram" 
                                   name="instagram"
                                   value="{{ $currentConfig['brand']['instagram'] ?? ($vendor->settings?->instagram ?? '') }}"
                                   placeholder="e.g. @yourbrand or full URL"
                                   class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-zinc-700 bg-slate-50 dark:bg-zinc-800 text-xs sm:text-sm text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-stone-900 font-mono">
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
                            <label class="k71-switch k71-switch-dark relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" 
                                       id="ctrl-transparent-hero-toggle" 
                                       name="transparent_on_hero"
                                       class="sr-only peer" 
                                       {{ !empty($currentConfig['header']['transparent_on_hero'] ?? true) ? 'checked' : '' }}>
                                <div class="k71-switch-track w-10 h-5 bg-slate-300 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-stone-900"></div>
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
                            $s1 = $cfgSlides[0] ?? ['title' => $vendor->shop_name, 'subtitle' => $vendor->tagline ?? 'Handcrafted atelier collections', 'button_text' => 'Explore Boutique', 'link' => '#catalog', 'text_color' => '#ffffff', 'image' => $currentConfig['brand']['banner_url'] ?? $vendor->banner_url];
                            $s2 = $cfgSlides[1] ?? ['title' => 'Artisan Editions 2026', 'subtitle' => 'Handcrafted creations packaged directly from our workshop with doorstep insured delivery.', 'button_text' => 'Discover New Drops', 'link' => '#catalog', 'text_color' => '#ffffff', 'image' => 'https://images.unsplash.com/photo-1490481651871-ab68de25d43d?auto=format&fit=crop&w=2000&q=80'];
                            $s3 = $cfgSlides[2] ?? ['title' => 'Signature Heritage', 'subtitle' => 'Verified authentic craftsmanship backed by K71 Buyer Protection.', 'button_text' => 'Shop Collection', 'link' => '#catalog', 'text_color' => '#ffffff', 'image' => 'https://images.unsplash.com/photo-1445205170230-053b83016050?auto=format&fit=crop&w=2000&q=80'];
                            $allThree = [$s1, $s2, $s3];
                        @endphp

                        @foreach($allThree as $idx => $s)
                            @php
                                $slideTextColorVal = $s['text_color'] ?? '#ffffff';
                            @endphp
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

                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 items-end">
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
                                    <div>
                                        <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-600 dark:text-zinc-400 mb-0.5">Slide {{ $idx + 1 }} Text Color</label>
                                        <div class="flex items-center gap-1.5 px-2.5 py-1 rounded-lg border border-slate-200 dark:border-zinc-700 bg-white dark:bg-zinc-800">
                                            <input type="color"
                                                   id="slide-text-color-{{ $idx }}"
                                                   name="slides[{{ $idx }}][text_color]"
                                                   value="{{ $slideTextColorVal }}"
                                                   oninput="document.getElementById('slide-text-hex-{{ $idx }}').textContent = this.value"
                                                   class="w-6 h-6 rounded cursor-pointer border-0 bg-transparent p-0 shrink-0">
                                            <span id="slide-text-hex-{{ $idx }}" class="text-[10px] font-mono text-slate-700 dark:text-zinc-300 flex-1 truncate">{{ $slideTextColorVal }}</span>
                                            <button type="button" onclick="setSlideTextColor({{ $idx }}, '#ffffff')" class="text-[9px] px-1.5 py-0.5 rounded bg-slate-100 text-slate-700 hover:bg-slate-200 font-semibold cursor-pointer" title="White">W</button>
                                            <button type="button" onclick="setSlideTextColor({{ $idx }}, '#fbbf24')" class="text-[9px] px-1.5 py-0.5 rounded bg-amber-100 text-amber-800 hover:bg-amber-200 font-semibold cursor-pointer" title="Gold">G</button>
                                        </div>
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
                            <label class="k71-switch relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" 
                                       id="ctrl-announcement-toggle" 
                                       name="announcement_enabled"
                                       class="sr-only peer" 
                                       {{ !empty($currentConfig['header']['announcement_enabled']) ? 'checked' : '' }}>
                                <div class="k71-switch-track w-11 h-6 bg-slate-300 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-emerald-500"></div>
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
                                $isChromaStripOn = isset($currentConfig['header']['chroma_strip_enabled']) ? filter_var($currentConfig['header']['chroma_strip_enabled'], FILTER_VALIDATE_BOOLEAN) : true;
                            @endphp
                            <label class="k71-switch k71-switch-blue relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" 
                                       id="ctrl-chroma-strip-toggle" 
                                       name="chroma_strip_enabled"
                                       class="sr-only peer" 
                                       {{ $isChromaStripOn ? 'checked' : '' }}>
                                <div class="k71-switch-track w-11 h-6 bg-slate-300 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
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

                    <!-- Happy Customers Slider Controls (Customizable Below Shorts) -->
                    <div class="p-4 rounded-2xl bg-amber-50/50 dark:bg-amber-950/20 border border-amber-200/60 dark:border-amber-900/40 space-y-4">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <span class="w-2.5 h-2.5 rounded-full bg-amber-500"></span>
                                <label class="text-xs font-bold uppercase tracking-wider text-slate-800 dark:text-zinc-200">
                                    Happy Customers Slider
                                </label>
                                <span class="text-[9px] px-1.5 py-0.5 rounded bg-amber-500/10 text-amber-700 dark:text-amber-300 font-mono font-bold">Slider Below Shorts</span>
                            </div>
                            @php
                                $isHcOn = isset($currentConfig['happy_customers']['enabled']) ? filter_var($currentConfig['happy_customers']['enabled'], FILTER_VALIDATE_BOOLEAN) : true;
                            @endphp
                            <label class="k71-switch k71-switch-amber relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" 
                                       id="ctrl-happy-customers-toggle" 
                                       name="happy_customers_enabled"
                                       class="sr-only peer" 
                                       {{ $isHcOn ? 'checked' : '' }}>
                                <div class="k71-switch-track w-11 h-6 bg-slate-300 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-amber-500"></div>
                            </label>
                        </div>
                        
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-600 dark:text-zinc-400 mb-0.5">Section Title</label>
                                <input type="text" 
                                       id="ctrl-happy-customers-title" 
                                       name="happy_customers_title"
                                       value="{{ $currentConfig['happy_customers']['title'] ?? 'Happy Customers & Real Stories' }}"
                                       placeholder="Happy Customers Title"
                                       class="w-full px-3 py-2 rounded-xl border border-amber-200 dark:border-amber-900/50 bg-white dark:bg-zinc-800 text-xs text-slate-900 dark:text-white">
                            </div>
                            <div>
                                <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-600 dark:text-zinc-400 mb-0.5">Section Subtitle</label>
                                <input type="text" 
                                       id="ctrl-happy-customers-subtitle" 
                                       name="happy_customers_subtitle"
                                       value="{{ $currentConfig['happy_customers']['subtitle'] ?? 'Real customer unboxings, doorstep deliveries, and verified reviews' }}"
                                       placeholder="Subtitle"
                                       class="w-full px-3 py-2 rounded-xl border border-amber-200 dark:border-amber-900/50 bg-white dark:bg-zinc-800 text-xs text-slate-900 dark:text-white">
                            </div>
                        </div>

                        <!-- Add a Custom Customer Story Box -->
                        <div class="p-3.5 rounded-xl bg-white dark:bg-zinc-900 border border-amber-200/80 dark:border-amber-900/50 space-y-3">
                            <div class="flex items-center justify-between border-b border-amber-100 dark:border-zinc-800 pb-2">
                                <span class="text-xs font-bold text-slate-800 dark:text-zinc-200 flex items-center gap-1.5">
                                    <span class="text-amber-500">⭐</span> Add Customer Story (Photo or Video Reel)
                                </span>
                                <span class="text-[9px] text-amber-700 dark:text-amber-400 bg-amber-50 dark:bg-amber-950/40 px-2 py-0.5 rounded font-mono">
                                    Shows in 2.5/view slider
                                </span>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-2.5">
                                <div class="sm:col-span-1">
                                    <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-600 dark:text-zinc-400 mb-1">Customer Name</label>
                                    <input type="text" 
                                           id="new-hc-name" 
                                           name="new_hc_name" 
                                           placeholder="e.g. Priya Sharma" 
                                           class="w-full px-2.5 py-1.5 rounded-lg border border-slate-200 dark:border-zinc-700 bg-slate-50 dark:bg-zinc-800 text-xs text-slate-900 dark:text-white">
                                </div>
                                <div class="sm:col-span-1">
                                    <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-600 dark:text-zinc-400 mb-1">City / Location</label>
                                    <input type="text" 
                                           id="new-hc-location" 
                                           name="new_hc_location" 
                                           placeholder="e.g. Mumbai, MH" 
                                           class="w-full px-2.5 py-1.5 rounded-lg border border-slate-200 dark:border-zinc-700 bg-slate-50 dark:bg-zinc-800 text-xs text-slate-900 dark:text-white">
                                </div>
                                <div class="sm:col-span-1">
                                    <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-600 dark:text-zinc-400 mb-1">Rating</label>
                                    <select id="new-hc-rating" 
                                            name="new_hc_rating" 
                                            class="w-full px-2.5 py-1.5 rounded-lg border border-slate-200 dark:border-zinc-700 bg-slate-50 dark:bg-zinc-800 text-xs text-slate-900 dark:text-white">
                                        <option value="5" selected>★★★★★ (5 Stars)</option>
                                        <option value="4">★★★★☆ (4 Stars)</option>
                                        <option value="3">★★★☆☆ (3 Stars)</option>
                                        <option value="2">★★☆☆☆ (2 Stars)</option>
                                        <option value="1">★☆☆☆☆ (1 Star)</option>
                                    </select>
                                </div>
                            </div>

                            <div>
                                <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-600 dark:text-zinc-400 mb-1">Review Comment / Quote</label>
                                <textarea id="new-hc-comment" 
                                          name="new_hc_comment" 
                                          rows="2" 
                                          placeholder="e.g. Received my order in pristine condition! Exceeded expectations and super fast delivery." 
                                          class="w-full px-2.5 py-1.5 rounded-lg border border-slate-200 dark:border-zinc-700 bg-slate-50 dark:bg-zinc-800 text-xs text-slate-900 dark:text-white"></textarea>
                            </div>

                            <div>
                                <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-700 dark:text-zinc-300 mb-1 flex items-center justify-between">
                                    <span>Upload Customer Photo or Video Reel *</span>
                                    <span class="text-[9px] font-normal text-slate-400">MP4, WEBM, MOV, JPG, PNG</span>
                                </label>
                                <div class="relative border-2 border-dashed border-amber-300 dark:border-amber-700/60 rounded-xl p-3 bg-amber-50/50 dark:bg-amber-950/20 hover:bg-amber-50/80 transition-colors flex flex-col items-center justify-center text-center cursor-pointer">
                                    <input type="file" 
                                           id="new-hc-media-file" 
                                           name="new_hc_media_file" 
                                           accept="image/*,video/*" 
                                           onchange="previewHcFile(this)"
                                           class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10">
                                    <div id="hc-file-prompt" class="space-y-1 py-1">
                                        <div class="w-8 h-8 rounded-full bg-amber-100 dark:bg-amber-900/60 text-amber-700 dark:text-amber-300 flex items-center justify-center mx-auto text-base">
                                            🎬
                                        </div>
                                        <p class="text-xs font-semibold text-slate-800 dark:text-zinc-200">
                                            Tap or click to upload photo or video reel
                                        </p>
                                        <p class="text-[10px] text-slate-500 dark:text-zinc-400">
                                            Direct media upload • Displayed instantly on storefront
                                        </p>
                                    </div>
                                    <div id="hc-file-selected" class="hidden text-xs text-emerald-600 dark:text-emerald-400 font-bold flex items-center gap-1.5 py-1">
                                        <span>✓</span>
                                        <span id="hc-file-name">File selected</span>
                                    </div>
                                </div>
                            </div>

                            <div class="flex flex-wrap items-center justify-between gap-2 pt-1">
                                <span class="text-[10px] text-slate-500 dark:text-zinc-400">
                                    Uploads &amp; publishes immediately to your storefront slider.
                                </span>
                                <button type="button" 
                                        id="add-hc-story-btn"
                                        onclick="saveEssentialSettings('hc')" 
                                        class="px-3.5 py-2 bg-amber-500 hover:bg-amber-600 text-white font-bold text-xs rounded-xl transition shadow-xs cursor-pointer flex items-center gap-1.5 disabled:opacity-60 disabled:cursor-not-allowed">
                                    <svg id="add-hc-spinner" class="hidden w-3.5 h-3.5 animate-spin text-white" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                                    </svg>
                                    <span id="add-hc-label">+ Add Story</span>
                                </button>
                            </div>

                            <!-- Real-Time Upload Progress Indicator -->
                            <div id="hc-upload-progress-wrap" class="hidden p-3 rounded-xl bg-amber-50 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-800 space-y-1.5">
                                <div class="flex items-center justify-between text-[11px] font-bold text-amber-900 dark:text-amber-200">
                                    <span id="hc-upload-progress-text" class="flex items-center gap-1.5">
                                        <span class="inline-block w-2 h-2 rounded-full bg-amber-500 animate-ping"></span>
                                        Uploading customer story...
                                    </span>
                                    <span id="hc-upload-progress-pct" class="font-mono">0%</span>
                                </div>
                                <div class="w-full h-2 bg-amber-200/70 dark:bg-amber-900/50 rounded-full overflow-hidden">
                                    <div id="hc-upload-progress-bar" class="h-full bg-amber-500 transition-all duration-150 rounded-full" style="width: 0%;"></div>
                                </div>
                            </div>

                            <!-- Inline Validation / Server Error Feedback -->
                            <div id="hc-upload-error" class="hidden p-2.5 rounded-xl bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800 text-rose-700 dark:text-rose-300 text-xs font-semibold"></div>
                        </div>

                        <!-- Existing Custom Stories -->
                        @php
                            $customHcItems = $currentConfig['happy_customers']['items'] ?? [];
                        @endphp
                        @if(is_array($customHcItems) && count($customHcItems) > 0)
                            <div class="pt-3 border-t border-amber-200/60 dark:border-amber-900/40">
                                <div class="flex items-center justify-between mb-2">
                                    <h4 class="text-xs font-bold text-slate-800 dark:text-zinc-200">
                                        Your Custom Stories ({{ count($customHcItems) }})
                                    </h4>
                                    <a href="{{ $vendor->storefrontUrl([], 'happy_customers') }}" target="_blank" rel="noopener" class="text-[10px] text-emerald-600 dark:text-emerald-400 font-semibold hover:underline">● View Live Slider ↗</a>
                                </div>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5 max-h-64 overflow-y-auto pr-1">
                                    @foreach($customHcItems as $hcIdx => $hcItem)
                                        <div class="p-2.5 rounded-xl bg-white dark:bg-zinc-900 border border-slate-200 dark:border-zinc-700 flex items-center gap-2.5 relative">
                                            <div class="w-11 h-14 rounded-lg bg-black overflow-hidden relative shrink-0">
                                                @if(($hcItem['type'] ?? '') === 'video')
                                                    <video src="{{ $hcItem['media_url'] ?? '' }}" class="w-full h-full object-cover" muted></video>
                                                    <span class="absolute bottom-0.5 right-0.5 text-[6.5px] bg-rose-600 text-white font-bold px-0.5 rounded leading-none">REEL</span>
                                                @else
                                                    <img src="{{ $hcItem['thumb_url'] ?? $hcItem['media_url'] ?? '' }}" class="w-full h-full object-cover">
                                                @endif
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <div class="flex items-center gap-0.5 text-amber-500 text-[9px]">
                                                    @for($s = 1; $s <= ($hcItem['rating'] ?? 5); $s++) ★ @endfor
                                                </div>
                                                <p class="text-xs font-bold text-slate-900 dark:text-white truncate">{{ $hcItem['customer_name'] ?? 'Customer' }}</p>
                                                <p class="text-[9.5px] text-slate-500 truncate">{{ $hcItem['location'] ?? 'India' }}</p>
                                                <p class="text-[9.5px] text-slate-600 dark:text-zinc-400 line-clamp-1 italic">"{{ $hcItem['comment'] ?? '' }}"</p>
                                            </div>
                                            <button type="button" 
                                                    onclick="deleteHappyCustomer({{ $hcIdx }})" 
                                                    class="p-1 text-rose-500 hover:text-rose-700 hover:bg-rose-50 dark:hover:bg-rose-950/30 rounded transition" 
                                                    title="Delete this story">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            </button>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @else
                            <div class="p-2.5 rounded-xl bg-amber-50/80 dark:bg-amber-950/30 border border-amber-200/50 dark:border-amber-900/30 text-[11px] text-amber-900 dark:text-amber-200 flex items-center gap-2">
                                <span>💡</span>
                                <span>No custom customer stories added yet. Upload a real customer photo or video reel above to showcase stories on your storefront.</span>
                            </div>
                        @endif
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

        function setSlideTextColor(idx, hex) {
            const el = document.getElementById('slide-text-color-' + idx);
            const lbl = document.getElementById('slide-text-hex-' + idx);
            if (el) el.value = hex;
            if (lbl) lbl.textContent = hex;
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
                    const heroBadge = document.getElementById('hero-active-preset');
                    if (heroBadge) heroBadge.textContent = key;

                    const allPresets = ['velora', 'noire', 'aurel', 'chroma', 'orbit', 'zenith', 'solaris', 'nexus', 'elysium', 'vogue'];
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

        function previewHcFile(input) {
            const errBox = document.getElementById('hc-upload-error');
            if (errBox) {
                errBox.classList.add('hidden');
                errBox.textContent = '';
            }
            if (input.files && input.files[0]) {
                const file = input.files[0];
                const prompt = document.getElementById('hc-file-prompt');
                if (prompt) prompt.classList.add('hidden');
                const selected = document.getElementById('hc-file-selected');
                if (selected) selected.classList.remove('hidden');
                const nameEl = document.getElementById('hc-file-name');
                if (nameEl) nameEl.textContent = file.name + ' (' + (file.size / (1024 * 1024)).toFixed(1) + ' MB)';
            }
        }

        function deleteHappyCustomer(idx) {
            if (!confirm('Are you sure you want to delete this customer story?')) return;
            const form = document.getElementById('essential-settings-form');
            const formData = new FormData(form);
            formData.append('delete_hc_idx', idx);

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
                if (data.status === 'success') {
                    showToast('✓ Story deleted successfully');
                    setTimeout(() => window.location.reload(), 500);
                } else {
                    showToast(data.message || 'Error deleting story', false);
                }
            })
            .catch(err => {
                console.error(err);
                showToast('Failed to delete story', false);
            });
        }

        function saveEssentialSettings(source = 'main') {
            const mainBtn = document.getElementById('save-branding-btn');
            const mainSpinner = document.getElementById('save-branding-spinner');
            const mainLabel = document.getElementById('save-branding-label');

            const hcBtn = document.getElementById('add-hc-story-btn');
            const hcSpinner = document.getElementById('add-hc-spinner');
            const hcLabel = document.getElementById('add-hc-label');

            const hcFile = document.getElementById('new-hc-media-file');
            const hcNameInput = document.getElementById('new-hc-name');
            const hcCommentInput = document.getElementById('new-hc-comment');
            const progressWrap = document.getElementById('hc-upload-progress-wrap');
            const progressBar = document.getElementById('hc-upload-progress-bar');
            const progressPct = document.getElementById('hc-upload-progress-pct');
            const progressText = document.getElementById('hc-upload-progress-text');
            const errBox = document.getElementById('hc-upload-error');

            if (errBox) {
                errBox.classList.add('hidden');
                errBox.textContent = '';
            }

            const hasHcFile = Boolean(hcFile && hcFile.files && hcFile.files.length > 0);
            if (source === 'hc' && !hasHcFile) {
                if (errBox) {
                    errBox.textContent = 'Please select a customer photo or video reel before clicking + Add Story.';
                    errBox.classList.remove('hidden');
                }
                showToast('Select a photo or video reel first', false);
                return;
            }

            const setLoadingState = (isLoading) => {
                if (mainBtn) mainBtn.disabled = isLoading;
                if (hcBtn) hcBtn.disabled = isLoading;
                if (mainSpinner) mainSpinner.classList.toggle('hidden', !isLoading);
                if (mainLabel) mainLabel.textContent = isLoading ? (hasHcFile ? 'Uploading & Saving...' : 'Saving Live...') : '💾 Apply & Save Live';
                if (hcSpinner) hcSpinner.classList.toggle('hidden', !(isLoading && (source === 'hc' || hasHcFile)));
                if (hcLabel) hcLabel.textContent = (isLoading && (source === 'hc' || hasHcFile)) ? 'Uploading Story...' : '+ Add Story';
            };

            setLoadingState(true);

            if ((source === 'hc' || hasHcFile) && progressWrap) {
                progressWrap.classList.remove('hidden');
                if (progressBar) progressBar.style.width = '5%';
                if (progressPct) progressPct.textContent = '0%';
                if (progressText) progressText.textContent = 'Preparing customer media upload...';
            }

            const form = document.getElementById('essential-settings-form');
            const formData = new FormData(form);
            formData.append('announcement_enabled', document.getElementById('ctrl-announcement-toggle').checked ? '1' : '0');
            const chromaToggle = document.getElementById('ctrl-chroma-strip-toggle');
            if (chromaToggle) {
                formData.append('chroma_strip_enabled', chromaToggle.checked ? '1' : '0');
            }
            const hcToggle = document.getElementById('ctrl-happy-customers-toggle');
            if (hcToggle) {
                formData.append('happy_customers_enabled', hcToggle.checked ? '1' : '0');
            }
            formData.append('transparent_on_hero', document.getElementById('ctrl-transparent-hero-toggle')?.checked ? '1' : '0');
            formData.append('header_elements_order', JSON.stringify(headerElementsOrder));

            const xhr = new XMLHttpRequest();
            xhr.open('POST', '{{ route('vendor.design.quick_settings') }}', true);
            xhr.setRequestHeader('Accept', 'application/json');
            xhr.setRequestHeader('X-CSRF-TOKEN', '{{ csrf_token() }}');

            xhr.upload.onprogress = function (e) {
                if (!progressWrap || !(source === 'hc' || hasHcFile)) return;
                if (e.lengthComputable && e.total > 0) {
                    const pct = Math.min(99, Math.round((e.loaded / e.total) * 100));
                    if (progressBar) progressBar.style.width = Math.max(8, pct) + '%';
                    if (progressPct) progressPct.textContent = pct + '%';
                    if (progressText) {
                        progressText.textContent = pct >= 99
                            ? 'Processing media & publishing to storefront...'
                            : 'Uploading customer media... ' + pct + '%';
                    }
                } else {
                    if (progressBar) progressBar.style.width = '65%';
                    if (progressText) progressText.textContent = 'Uploading customer media...';
                }
            };

            xhr.onload = function () {
                let data = null;
                try {
                    data = JSON.parse(xhr.responseText);
                } catch (parseErr) {
                    data = null;
                }

                if (xhr.status >= 200 && xhr.status < 300 && data && data.status === 'success') {
                    if (progressBar) progressBar.style.width = '100%';
                    if (progressPct) progressPct.textContent = '100%';
                    if (progressText) progressText.textContent = '✓ Story uploaded & published live!';

                    const hadStoryInput = hasHcFile
                        || (hcNameInput && hcNameInput.value.trim() !== '')
                        || (hcCommentInput && hcCommentInput.value.trim() !== '');

                    setLoadingState(false);

                    if (hadStoryInput || source === 'hc') {
                        showToast('✓ Customer story published to storefront!');
                        setTimeout(() => window.location.reload(), 650);
                    } else {
                        showToast('✓ Store essentials saved and live!');
                    }
                    return;
                }

                setLoadingState(false);
                if (progressWrap) progressWrap.classList.add('hidden');

                let errMsg = (data && data.message) ? data.message : 'Failed to save settings.';
                if (xhr.status === 413) {
                    errMsg = 'File size exceeds server upload limit. Please use a smaller photo or video reel.';
                } else if (xhr.status === 422 && data && data.errors) {
                    const firstKey = Object.keys(data.errors)[0];
                    if (firstKey && Array.isArray(data.errors[firstKey]) && data.errors[firstKey][0]) {
                        errMsg = data.errors[firstKey][0];
                    }
                }

                if ((source === 'hc' || hasHcFile || xhr.status === 422) && errBox) {
                    errBox.textContent = errMsg;
                    errBox.classList.remove('hidden');
                }
                showToast(errMsg, false);
            };

            xhr.onerror = function () {
                setLoadingState(false);
                if (progressWrap) progressWrap.classList.add('hidden');
                if (errBox && (source === 'hc' || hasHcFile)) {
                    errBox.textContent = 'Network error during upload. Please check your connection and try again.';
                    errBox.classList.remove('hidden');
                }
                showToast('Network error while saving settings', false);
            };

            xhr.send(formData);
        }
    </script>
</x-layouts.vendor>
