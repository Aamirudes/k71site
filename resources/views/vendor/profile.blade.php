<x-layouts.vendor title="Storefront Customization Studio">
    @php
        $errors = $errors ?? new \Illuminate\Support\ViewErrorBag();
        $social = is_array($vendor->social_links) ? $vendor->social_links : (is_string($vendor->social_links) ? (json_decode($vendor->social_links, true) ?: []) : []);
        $currentPalette = old('colour_palette', $vendor->colour_palette ?? 'sage');
        if ($currentPalette === 'default') { $currentPalette = 'sage'; }
        $currentLayout = old('layout_preset', $vendor->layout_preset ?? 'classic');
        $currentProductPreset = old('product_preset', $vendor->product_preset ?? 'gallery');
        $currentTextPreset = old('text_preset', $social['text_preset'] ?? 'obsidian');
    @endphp

    <div class="mb-8">
        <div class="flex items-center justify-between flex-wrap gap-4">
            <div>
                <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 tracking-tight">Storefront Customization Studio</h1>
                <p class="mt-1 text-sm text-gray-500">Personalize your brand atmosphere, color palettes, hero layout, and craft heritage story.</p>
            </div>
            <div class="flex items-center gap-2.5">
                <a href="{{ $vendor->storefront_url }}" target="_blank" 
                   class="inline-flex items-center gap-1.5 px-3.5 py-2.5 rounded-xl bg-stone-100 hover:bg-stone-200 text-stone-800 text-xs font-semibold transition-all">
                    <span>View Live</span>
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
                </a>
                <button type="submit" form="profile-form" 
                        class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-stone-900 hover:bg-black text-white text-xs font-bold transition-all shadow-sm active:scale-95 cursor-pointer">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    <span>Save Changes</span>
                </button>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="mb-6 p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm font-semibold flex items-center gap-2">
            <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    <form id="profile-form" action="{{ route('vendor.profile.update') }}" method="POST" enctype="multipart/form-data" class="space-y-8">
        @csrf
        @method('PUT')

        <!-- SECTION 1: Boutique Identity & Tagline -->
        <div class="bg-white rounded-2xl shadow-sm border border-stone-200/80 p-6 sm:p-8">
            <div class="flex items-center gap-3 pb-4 border-b border-stone-100 mb-6">
                <div class="w-9 h-9 rounded-xl bg-amber-50 text-amber-700 flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                </div>
                <div>
                    <h2 class="text-lg font-bold text-gray-900">Boutique Brand Identity</h2>
                    <p class="text-xs text-gray-500">Your public store name, signature tagline, and artisan specialty.</p>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">Boutique / Shop Name *</label>
                    <input type="text" name="shop_name" value="{{ old('shop_name', $vendor->shop_name) }}" required
                           class="block w-full rounded-xl border-gray-200 shadow-sm focus:border-brand-sage focus:ring-brand-sage text-sm py-2.5 px-3">
                    @error('shop_name') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">Custom Atelier Credential Badge</label>
                    <input type="text" name="custom_badge" value="{{ old('custom_badge', $social['custom_badge'] ?? 'Verified Atelier') }}" 
                           placeholder="e.g. Verified Atelier, Master Weaver, Heritage Studio"
                           class="block w-full rounded-xl border-gray-200 shadow-sm focus:border-brand-sage focus:ring-brand-sage text-sm py-2.5 px-3">
                    <p class="text-[11px] text-gray-400 mt-1">Badge that appears next to your store name on your storefront hero.</p>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">Boutique Signature Tagline</label>
                    <input type="text" name="tagline" value="{{ old('tagline', $social['tagline'] ?? '') }}" 
                           placeholder="e.g. Handcrafted Heritage & Timeless Silhouettes"
                           class="block w-full rounded-xl border-gray-200 shadow-sm focus:border-brand-sage focus:ring-brand-sage text-sm py-2.5 px-3">
                    <p class="text-[11px] text-gray-400 mt-1">Displayed in elegant italic font below your boutique name.</p>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">Craft Specialty / Textile Signature</label>
                    <input type="text" name="craft_specialty" value="{{ old('craft_specialty', $social['craft_specialty'] ?? '') }}" 
                           placeholder="e.g. Pure Chanderi Silk, Banarasi Brocades & Handloom"
                           class="block w-full rounded-xl border-gray-200 shadow-sm focus:border-brand-sage focus:ring-brand-sage text-sm py-2.5 px-3">
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">Established Year</label>
                    <input type="number" min="1900" max="2099" name="established_year" value="{{ old('established_year', $social['established_year'] ?? date('Y')) }}" 
                           placeholder="e.g. 2021"
                           class="block w-full rounded-xl border-gray-200 shadow-sm focus:border-brand-sage focus:ring-brand-sage text-sm py-2.5 px-3">
                </div>

                <div class="sm:col-span-2">
                    <label class="block text-xs font-bold text-gray-700 mb-1">About the Atelier / Store Description</label>
                    <textarea name="description" rows="3" 
                              placeholder="Tell customers what makes your boutique unique, your fabrics, weave methods, and craft philosophy..."
                              class="block w-full rounded-xl border-gray-200 shadow-sm focus:border-brand-sage focus:ring-brand-sage text-sm p-3">{{ old('description', $vendor->description) }}</textarea>
                </div>
            </div>
        </div>

        <!-- SECTION 2: Aesthetic Theme Palette, Text Colors & Layout Presets with Down AJAX Preview -->
        <div class="bg-white rounded-2xl shadow-sm border border-stone-200/80 p-6 sm:p-8" 
             x-data="{ 
                palette: '{{ $currentPalette }}', 
                layout: '{{ $currentLayout }}', 
                productPreset: '{{ $currentProductPreset }}',
                textPreset: '{{ $currentTextPreset }}',
                previewTab: 'storefront',
                previewDevice: 'desktop',
                ajaxStatus: 'Live Synced',
                isAjaxLoading: false,
                syncPreviewAjax() {
                    this.isAjaxLoading = true;
                    this.ajaxStatus = 'Syncing via AJAX...';
                    fetch('{{ route('vendor.profile.preview_ajax') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            palette: this.palette,
                            layout: this.layout,
                            product_preset: this.productPreset,
                            text_preset: this.textPreset
                        })
                    })
                    .then(r => r.json())
                    .then(data => {
                        this.isAjaxLoading = false;
                        this.ajaxStatus = 'Live Synced at ' + new Date().toLocaleTimeString();
                    })
                    .catch(() => {
                        this.isAjaxLoading = false;
                        this.ajaxStatus = 'Live Synced';
                    });
                }
             }">
            <div class="flex items-center gap-3 pb-4 border-b border-stone-100 mb-6">
                <div class="w-9 h-9 rounded-xl bg-purple-50 text-purple-700 flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"></path></svg>
                </div>
                <div>
                    <h2 class="text-lg font-bold text-gray-900">Visual Atmosphere, Text Colors &amp; Layout Presets</h2>
                    <p class="text-xs text-gray-500">Pick an artisanal color palette, visible text color tone, storefront presentation layout, and preview live down below.</p>
                </div>
            </div>

            <!-- Color Palette Selector -->
            <div class="mb-8">
                <div class="flex items-center justify-between mb-3">
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider">1. Colour Palette Preset (3 Core)</label>
                    <span class="text-[11px] text-gray-400">Tokens applied dynamically to storefront</span>
                </div>
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3.5">
                    
                    <!-- Earthy Sage (K71 Default) -->
                    <label class="relative cursor-pointer">
                        <input type="radio" name="colour_palette" value="sage" x-model="palette" class="peer sr-only">
                        <div class="p-3.5 rounded-2xl border-2 transition-all flex flex-col gap-2.5 peer-checked:border-gray-900 peer-checked:shadow-sm hover:border-stone-400 bg-[#F8FAF8]">
                            <div class="flex items-center gap-1.5">
                                <span class="w-4 h-4 rounded-full bg-[#2D4A3E] shadow-2xs"></span>
                                <span class="w-4 h-4 rounded-full bg-[#8FA396] shadow-2xs"></span>
                                <span class="w-4 h-4 rounded-full bg-[#EFE9DF] shadow-2xs"></span>
                            </div>
                            <div>
                                <span class="text-xs font-bold text-gray-900 block leading-tight">Earthy Sage</span>
                                <span class="text-[10px] text-emerald-700 font-medium">K71 Botanical Default</span>
                            </div>
                        </div>
                    </label>

                    <!-- Midnight Indigo -->
                    <label class="relative cursor-pointer">
                        <input type="radio" name="colour_palette" value="midnight" x-model="palette" class="peer sr-only">
                        <div class="p-3.5 rounded-2xl border-2 transition-all flex flex-col gap-2.5 peer-checked:border-gray-900 peer-checked:shadow-sm hover:border-stone-400 bg-[#F8FAFC]">
                            <div class="flex items-center gap-1.5">
                                <span class="w-4 h-4 rounded-full bg-[#1E293B] shadow-2xs"></span>
                                <span class="w-4 h-4 rounded-full bg-[#38BDF8] shadow-2xs"></span>
                                <span class="w-4 h-4 rounded-full bg-[#E2E8F0] shadow-2xs"></span>
                            </div>
                            <div>
                                <span class="text-xs font-bold text-gray-900 block leading-tight">Midnight Indigo</span>
                                <span class="text-[10px] text-sky-700 font-medium">Tech-Luxury Slate</span>
                            </div>
                        </div>
                    </label>

                    <!-- Warm Terracotta / Sunset -->
                    <label class="relative cursor-pointer">
                        <input type="radio" name="colour_palette" value="sunset" x-model="palette" class="peer sr-only">
                        <div class="p-3.5 rounded-2xl border-2 transition-all flex flex-col gap-2.5 peer-checked:border-gray-900 peer-checked:shadow-sm hover:border-stone-400 bg-[#FFFDF9]">
                            <div class="flex items-center gap-1.5">
                                <span class="w-4 h-4 rounded-full bg-[#C85A32] shadow-2xs"></span>
                                <span class="w-4 h-4 rounded-full bg-[#FDE047] shadow-2xs"></span>
                                <span class="w-4 h-4 rounded-full bg-[#FBF1E6] shadow-2xs"></span>
                            </div>
                            <div>
                                <span class="text-xs font-bold text-gray-900 block leading-tight">Warm Terracotta</span>
                                <span class="text-[10px] text-amber-700 font-medium">Artisanal Sunset</span>
                            </div>
                        </div>
                    </label>

                    <!-- Linen & Sage (Classic) -->
                    <label class="relative cursor-pointer">
                        <input type="radio" name="colour_palette" value="linen" x-model="palette" class="peer sr-only">
                        <div class="p-3.5 rounded-2xl border-2 transition-all flex flex-col gap-2.5 peer-checked:border-gray-900 peer-checked:shadow-sm hover:border-stone-400 bg-[#FAF7F2]">
                            <div class="flex items-center gap-1.5">
                                <span class="w-4 h-4 rounded-full bg-[#2B3E36] shadow-2xs"></span>
                                <span class="w-4 h-4 rounded-full bg-[#8FA396] shadow-2xs"></span>
                                <span class="w-4 h-4 rounded-full bg-[#EFE9DF] shadow-2xs"></span>
                            </div>
                            <div>
                                <span class="text-xs font-bold text-gray-900 block leading-tight">Linen Ivory</span>
                                <span class="text-[10px] text-gray-500">Classic Atelier</span>
                            </div>
                        </div>
                    </label>

                </div>
            </div>

            <!-- Hero Layout Preset Selector -->
            <div class="mb-8">
                <div class="flex items-center justify-between mb-3">
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider">2. Storefront Layout Preset (3 Core)</label>
                    <span class="text-[11px] text-gray-400">Controls boutique homepage</span>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    
                    <label class="relative cursor-pointer">
                        <input type="radio" name="layout_preset" value="classic" x-model="layout" class="peer sr-only">
                        <div class="p-4 rounded-2xl border-2 transition-all peer-checked:border-gray-900 peer-checked:bg-stone-50 hover:border-stone-400">
                            <span class="text-xs font-bold text-gray-900 block mb-1">Classic Marketplace</span>
                            <p class="text-[11px] text-gray-500 leading-relaxed">Hero banner, featured category pills, and high-density product grid for quick discovery.</p>
                        </div>
                    </label>

                    <label class="relative cursor-pointer">
                        <input type="radio" name="layout_preset" value="editorial" x-model="layout" class="peer sr-only">
                        <div class="p-4 rounded-2xl border-2 transition-all peer-checked:border-gray-900 peer-checked:bg-stone-50 hover:border-stone-400">
                            <span class="text-xs font-bold text-gray-900 block mb-1">Editorial Boutique</span>
                            <p class="text-[11px] text-gray-500 leading-relaxed">Story-driven layout with large typography, curated lookbooks, and atelier heritage narrative.</p>
                        </div>
                    </label>

                    <label class="relative cursor-pointer">
                        <input type="radio" name="layout_preset" value="minimal" x-model="layout" class="peer sr-only">
                        <div class="p-4 rounded-2xl border-2 transition-all peer-checked:border-gray-900 peer-checked:bg-stone-50 hover:border-stone-400">
                            <span class="text-xs font-bold text-gray-900 block mb-1">Modern Minimalist</span>
                            <p class="text-[11px] text-gray-500 leading-relaxed">Clean whitespace, borderless product cards, and sleek focus on product photography.</p>
                        </div>
                    </label>

                </div>
            </div>

            <!-- Product Page Layout Preset Selector -->
            <div class="mb-8">
                <div class="flex items-center justify-between mb-3">
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider">3. Product Page Layout Preset (3 Core)</label>
                    <span class="text-[11px] text-gray-400">Controls product detail pages</span>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    
                    <label class="relative cursor-pointer">
                        <input type="radio" name="product_preset" value="gallery" x-model="productPreset" @change="syncPreviewAjax()" class="peer sr-only">
                        <div class="p-4 rounded-2xl border-2 transition-all peer-checked:border-gray-900 peer-checked:bg-stone-50 hover:border-stone-400">
                            <span class="text-xs font-bold text-gray-900 block mb-1">Thumbnails & Gallery Strip</span>
                            <p class="text-[11px] text-gray-500 leading-relaxed">Interactive carousel with thumbnail strip, variant pills, and instant slide-over cart drawer.</p>
                        </div>
                    </label>

                    <label class="relative cursor-pointer">
                        <input type="radio" name="product_preset" value="immersive" x-model="productPreset" @change="syncPreviewAjax()" class="peer sr-only">
                        <div class="p-4 rounded-2xl border-2 transition-all peer-checked:border-gray-900 peer-checked:bg-stone-50 hover:border-stone-400">
                            <span class="text-xs font-bold text-gray-900 block mb-1">Full-Bleed Immersive</span>
                            <p class="text-[11px] text-gray-500 leading-relaxed">Edge-to-edge media showcase with floating sticky checkout bar and cinematic imagery.</p>
                        </div>
                    </label>

                    <label class="relative cursor-pointer">
                        <input type="radio" name="product_preset" value="showcase" x-model="productPreset" @change="syncPreviewAjax()" class="peer sr-only">
                        <div class="p-4 rounded-2xl border-2 transition-all peer-checked:border-gray-900 peer-checked:bg-stone-50 hover:border-stone-400">
                            <span class="text-xs font-bold text-gray-900 block mb-1">Specification Showcase</span>
                            <p class="text-[11px] text-gray-500 leading-relaxed">Split-screen layout emphasizing technical specifications, fabric care, dimensions, and trust seals.</p>
                        </div>
                    </label>

                </div>
            </div>

            <!-- Visible Text Colors Preset (High Legibility) -->
            <div class="mb-8 pt-6 border-t border-stone-100">
                <div class="flex items-center justify-between mb-3">
                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider">4. Visible Text Color Tone Presets</label>
                        <p class="text-[11px] text-gray-500">Fine-tune font contrast for effortless readability across mobile &amp; desktop screens.</p>
                    </div>
                    <span class="text-[10px] text-emerald-600 font-mono font-bold bg-emerald-50 px-2 py-0.5 rounded-full">Responsive Contrast</span>
                </div>
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                    <label class="relative cursor-pointer">
                        <input type="radio" name="text_preset" value="obsidian" x-model="textPreset" @change="syncPreviewAjax()" class="peer sr-only">
                        <div class="p-3.5 rounded-2xl border-2 transition-all peer-checked:border-gray-900 peer-checked:bg-stone-50 hover:border-stone-400 flex items-center gap-3">
                            <span class="w-6 h-6 rounded-full bg-[#09090b] border border-stone-300 shrink-0"></span>
                            <div>
                                <span class="text-xs font-bold text-gray-900 block leading-tight">Obsidian Deep</span>
                                <span class="text-[10px] text-gray-500">High Contrast Black</span>
                            </div>
                        </div>
                    </label>
                    <label class="relative cursor-pointer">
                        <input type="radio" name="text_preset" value="deep_navy" x-model="textPreset" @change="syncPreviewAjax()" class="peer sr-only">
                        <div class="p-3.5 rounded-2xl border-2 transition-all peer-checked:border-gray-900 peer-checked:bg-stone-50 hover:border-stone-400 flex items-center gap-3">
                            <span class="w-6 h-6 rounded-full bg-[#0f172a] border border-stone-300 shrink-0"></span>
                            <div>
                                <span class="text-xs font-bold text-gray-900 block leading-tight">Slate Navy</span>
                                <span class="text-[10px] text-gray-500">Executive Deep Blue</span>
                            </div>
                        </div>
                    </label>
                    <label class="relative cursor-pointer">
                        <input type="radio" name="text_preset" value="espresso" x-model="textPreset" @change="syncPreviewAjax()" class="peer sr-only">
                        <div class="p-3.5 rounded-2xl border-2 transition-all peer-checked:border-gray-900 peer-checked:bg-stone-50 hover:border-stone-400 flex items-center gap-3">
                            <span class="w-6 h-6 rounded-full bg-[#291811] border border-stone-300 shrink-0"></span>
                            <div>
                                <span class="text-xs font-bold text-gray-900 block leading-tight">Warm Espresso</span>
                                <span class="text-[10px] text-gray-500">Artisanal Earth Tone</span>
                            </div>
                        </div>
                    </label>
                    <label class="relative cursor-pointer">
                        <input type="radio" name="text_preset" value="monochrome" x-model="textPreset" @change="syncPreviewAjax()" class="peer sr-only">
                        <div class="p-3.5 rounded-2xl border-2 transition-all peer-checked:border-gray-900 peer-checked:bg-stone-50 hover:border-stone-400 flex items-center gap-3">
                            <span class="w-6 h-6 rounded-full bg-[#000000] border border-stone-300 shrink-0"></span>
                            <div>
                                <span class="text-xs font-bold text-gray-900 block leading-tight">Pure Monochrome</span>
                                <span class="text-[10px] text-gray-500">Clean Minimalist</span>
                            </div>
                        </div>
                    </label>
                </div>
            </div>

            <!-- DOWN AJAX LIVE VISUAL RESULT PREVIEW CANVAS -->
            <div class="pt-6 border-t-2 border-dashed border-stone-200">
                <div class="flex items-center justify-between flex-wrap gap-3 mb-4">
                    <div class="flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse"></span>
                        <h3 class="text-sm font-bold text-gray-900 uppercase tracking-wide">Live Down AJAX Visual Result Preview</h3>
                        <span class="text-[10px] text-stone-500 font-mono bg-stone-100 px-2 py-0.5 rounded" x-text="ajaxStatus"></span>
                    </div>
                    <div class="flex items-center gap-2">
                        <!-- Storefront vs Product Tab -->
                        <div class="inline-flex rounded-xl bg-stone-100 p-1">
                            <button type="button" @click="previewTab = 'storefront'" 
                                    :class="previewTab === 'storefront' ? 'bg-white shadow-xs font-bold text-stone-900' : 'text-stone-600 hover:text-stone-900'"
                                    class="px-3 py-1 rounded-lg text-xs transition cursor-pointer">
                                🏪 Storefront
                            </button>
                            <button type="button" @click="previewTab = 'product'" 
                                    :class="previewTab === 'product' ? 'bg-white shadow-xs font-bold text-stone-900' : 'text-stone-600 hover:text-stone-900'"
                                    class="px-3 py-1 rounded-lg text-xs transition cursor-pointer">
                                🛍️ Product View
                            </button>
                        </div>
                        <!-- Responsive Desktop / Mobile Switcher -->
                        <div class="inline-flex rounded-xl bg-stone-100 p-1">
                            <button type="button" @click="previewDevice = 'desktop'" 
                                    :class="previewDevice === 'desktop' ? 'bg-white shadow-xs text-stone-900 font-bold' : 'text-stone-600'"
                                    class="px-2.5 py-1 rounded-lg text-xs transition cursor-pointer" title="Desktop View">
                                💻
                            </button>
                            <button type="button" @click="previewDevice = 'mobile'" 
                                    :class="previewDevice === 'mobile' ? 'bg-white shadow-xs text-stone-900 font-bold' : 'text-stone-600'"
                                    class="px-2.5 py-1 rounded-lg text-xs transition cursor-pointer" title="Mobile View">
                                📱
                            </button>
                        </div>
                        <!-- Re-sync AJAX button -->
                        <button type="button" @click="syncPreviewAjax()" class="p-1.5 rounded-lg bg-stone-100 hover:bg-stone-200 text-stone-700 text-xs transition cursor-pointer" title="Refresh Live AJAX Preview">
                            <svg :class="isAjaxLoading ? 'animate-spin' : ''" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                        </button>
                    </div>
                </div>

                <!-- Preview Shell Frame -->
                <div class="p-3 sm:p-5 rounded-3xl bg-stone-900 border border-stone-800 shadow-2xl overflow-hidden flex justify-center">
                    <div :class="previewDevice === 'mobile' ? 'max-w-sm w-full rounded-2xl border-4 border-stone-800 shadow-2xl' : 'w-full rounded-xl'" 
                         class="bg-white overflow-hidden transition-all duration-300 relative text-left">
                        
                        <!-- Top Mini Utility Bar -->
                        <div class="bg-black text-white text-[9px] py-1 px-3 flex items-center justify-between">
                            <span class="text-stone-400">← Back to Marketplace</span>
                            <span class="text-emerald-400 font-bold">● Official Store</span>
                        </div>

                        <!-- Store Header Simulation -->
                        <div class="px-4 py-3 border-b border-stone-100 flex items-center justify-between"
                             :class="{
                                'bg-[#F8FAF8]': palette === 'sage',
                                'bg-[#F8FAFC]': palette === 'midnight',
                                'bg-[#FFFDF9]': palette === 'sunset',
                                'bg-[#FAF7F2]': palette === 'linen'
                             }">
                            <div class="flex items-center gap-2">
                                <div class="w-7 h-7 rounded-lg bg-stone-900 text-white font-serif font-black flex items-center justify-center text-xs">
                                    {{ strtoupper(substr($vendor->shop_name, 0, 1)) }}
                                </div>
                                <div>
                                    <h4 class="text-xs font-black leading-none"
                                        :class="{
                                            'text-[#09090b]': textPreset === 'obsidian',
                                            'text-[#0f172a]': textPreset === 'deep_navy',
                                            'text-[#291811]': textPreset === 'espresso',
                                            'text-black': textPreset === 'monochrome'
                                        }">{{ $vendor->shop_name }}</h4>
                                    <span class="text-[8px] font-bold uppercase tracking-wider text-emerald-600">Verified Seller</span>
                                </div>
                            </div>
                            <div class="flex items-center gap-1.5 text-[9px]">
                                <span class="px-2 py-0.5 rounded-full font-bold text-white text-[9px]"
                                      :class="{
                                        'bg-emerald-800': palette === 'sage',
                                        'bg-slate-900': palette === 'midnight',
                                        'bg-amber-800': palette === 'sunset',
                                        'bg-stone-800': palette === 'linen'
                                      }">Cart (0)</span>
                            </div>
                        </div>

                        <!-- TAB 1: STOREFRONT PREVIEW -->
                        <div x-show="previewTab === 'storefront'" class="p-4 space-y-4">
                            <!-- Hero Layout Simulation -->
                            <div class="p-5 rounded-2xl text-white relative overflow-hidden"
                                 :class="{
                                    'bg-gradient-to-r from-emerald-950 via-emerald-900 to-teal-950': palette === 'sage',
                                    'bg-gradient-to-r from-slate-950 via-slate-900 to-indigo-950': palette === 'midnight',
                                    'bg-gradient-to-r from-stone-900 via-amber-950 to-orange-950': palette === 'sunset',
                                    'bg-gradient-to-r from-stone-950 via-stone-900 to-emerald-950': palette === 'linen'
                                 }">
                                <div class="relative z-10">
                                    <span class="text-[9px] font-bold uppercase tracking-widest text-amber-300">Layout: <span x-text="layout"></span></span>
                                    <h3 class="text-base sm:text-lg font-serif font-bold mt-1 text-white">Curated Collection</h3>
                                    <p class="text-[10px] text-stone-300 mt-1 max-w-xs leading-relaxed">
                                        Handcrafted weaves &amp; direct artisan dispatches delivered across India.
                                    </p>
                                </div>
                            </div>

                            <!-- Layout-specific product cards preview -->
                            <div>
                                <div class="flex items-center justify-between mb-2">
                                    <span class="text-[10px] font-bold uppercase tracking-wider text-stone-500">Featured Releases</span>
                                    <span class="text-[9px] text-stone-400">3 Products</span>
                                </div>

                                <div class="grid gap-2.5"
                                     :class="layout === 'minimal' ? 'grid-cols-3' : (layout === 'editorial' ? 'grid-cols-2' : 'grid-cols-3')">
                                    <div class="p-2.5 rounded-xl border border-stone-200/80 bg-stone-50/50">
                                        <div class="aspect-square rounded-lg bg-stone-200 mb-2 flex items-center justify-center text-base">👗</div>
                                        <div class="text-[10px] font-bold truncate"
                                             :class="{
                                                'text-[#09090b]': textPreset === 'obsidian',
                                                'text-[#0f172a]': textPreset === 'deep_navy',
                                                'text-[#291811]': textPreset === 'espresso',
                                                'text-black': textPreset === 'monochrome'
                                             }">Pure Chanderi Silk Saree</div>
                                        <div class="text-[9px] font-mono font-bold text-stone-900 mt-0.5">₹3,499</div>
                                    </div>
                                    <div class="p-2.5 rounded-xl border border-stone-200/80 bg-stone-50/50">
                                        <div class="aspect-square rounded-lg bg-stone-200 mb-2 flex items-center justify-center text-base">✨</div>
                                        <div class="text-[10px] font-bold truncate"
                                             :class="{
                                                'text-[#09090b]': textPreset === 'obsidian',
                                                'text-[#0f172a]': textPreset === 'deep_navy',
                                                'text-[#291811]': textPreset === 'espresso',
                                                'text-black': textPreset === 'monochrome'
                                             }">Handblock Kurta Set</div>
                                        <div class="text-[9px] font-mono font-bold text-stone-900 mt-0.5">₹1,899</div>
                                    </div>
                                    <div class="p-2.5 rounded-xl border border-stone-200/80 bg-stone-50/50" x-show="layout !== 'editorial'">
                                        <div class="aspect-square rounded-lg bg-stone-200 mb-2 flex items-center justify-center text-base">🧵</div>
                                        <div class="text-[10px] font-bold truncate"
                                             :class="{
                                                'text-[#09090b]': textPreset === 'obsidian',
                                                'text-[#0f172a]': textPreset === 'deep_navy',
                                                'text-[#291811]': textPreset === 'espresso',
                                                'text-black': textPreset === 'monochrome'
                                             }">Zari Organza Dupatta</div>
                                        <div class="text-[9px] font-mono font-bold text-stone-900 mt-0.5">₹999</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- TAB 2: PRODUCT PAGE PREVIEW -->
                        <div x-show="previewTab === 'product'" class="p-4 space-y-4">
                            <div class="p-3 rounded-2xl bg-stone-50 border border-stone-200">
                                <span class="text-[9px] font-bold uppercase tracking-widest text-emerald-700 block mb-1">
                                    Product Template: <span x-text="productPreset"></span>
                                </span>
                                
                                <!-- Gallery Strip Mockup -->
                                <template x-if="productPreset === 'gallery'">
                                    <div class="space-y-2">
                                        <div class="aspect-video rounded-xl bg-stone-200 flex items-center justify-center text-2xl">📸 Main Carousel</div>
                                        <div class="flex gap-1.5">
                                            <span class="w-8 h-8 rounded-lg bg-stone-300 inline-block border border-stone-400"></span>
                                            <span class="w-8 h-8 rounded-lg bg-stone-200 inline-block"></span>
                                            <span class="w-8 h-8 rounded-lg bg-stone-200 inline-block"></span>
                                        </div>
                                    </div>
                                </template>

                                <!-- Full-Bleed Immersive Mockup -->
                                <template x-if="productPreset === 'immersive'">
                                    <div class="space-y-2">
                                        <div class="h-44 rounded-xl bg-stone-800 text-white flex items-center justify-center text-sm font-bold">
                                            Cinematic Full-Bleed Imagery
                                        </div>
                                        <div class="flex justify-between items-center text-[10px] text-stone-500">
                                            <span>Floating Quick-Buy Bar</span>
                                            <span class="font-bold text-stone-900">Sticky Bottom</span>
                                        </div>
                                    </div>
                                </template>

                                <!-- Specification Showcase Mockup -->
                                <template x-if="productPreset === 'showcase'">
                                    <div class="grid grid-cols-2 gap-2 text-[9px]">
                                        <div class="aspect-square rounded-xl bg-stone-200 flex items-center justify-center">📐 Product</div>
                                        <div class="p-2 rounded-xl bg-white border border-stone-200 space-y-1">
                                            <div class="font-bold text-stone-900">Specs &amp; Care</div>
                                            <div class="text-stone-500">• 100% Pure Mulberry</div>
                                            <div class="text-stone-500">• Dry Clean Only</div>
                                            <div class="text-stone-500">• 7-Day Exchange</div>
                                        </div>
                                    </div>
                                </template>

                                <div class="mt-3 pt-2 border-t border-stone-200 flex items-center justify-between">
                                    <div>
                                        <h4 class="text-xs font-black"
                                            :class="{
                                                'text-[#09090b]': textPreset === 'obsidian',
                                                'text-[#0f172a]': textPreset === 'deep_navy',
                                                'text-[#291811]': textPreset === 'espresso',
                                                'text-black': textPreset === 'monochrome'
                                            }">Kanjeevaram Bridal Saree</h4>
                                        <div class="text-[10px] font-mono font-bold text-stone-900">₹8,999</div>
                                    </div>
                                    <button type="button" class="px-3 py-1.5 rounded-lg text-white text-[10px] font-bold"
                                            :class="{
                                                'bg-emerald-800': palette === 'sage',
                                                'bg-slate-900': palette === 'midnight',
                                                'bg-amber-800': palette === 'sunset',
                                                'bg-stone-800': palette === 'linen'
                                            }">
                                        Buy Now
                                    </button>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>

        <!-- SECTION 3: Storefront Announcement Marquee -->
        <div class="bg-white rounded-2xl shadow-sm border border-stone-200/80 p-6 sm:p-8" x-data="{ announcementEnabled: {{ !empty($social['announcement_bar_enabled']) ? 'true' : 'false' }} }">
            <div class="flex items-center gap-3 pb-4 border-b border-stone-100 mb-6">
                <div class="w-9 h-9 rounded-xl bg-rose-50 text-rose-700 flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"></path></svg>
                </div>
                <div>
                    <h2 class="text-lg font-bold text-gray-900">Announcement Marquee Bar</h2>
                    <p class="text-xs text-gray-500">Highlight active promotions, festive drops, or free delivery thresholds at the top of your store.</p>
                </div>
            </div>

            <div class="space-y-4">
                <label class="flex items-center gap-2.5 cursor-pointer">
                    <input type="checkbox" name="announcement_bar_enabled" value="1" x-model="announcementEnabled" 
                           class="w-4 h-4 rounded border-gray-300 text-stone-900 focus:ring-stone-400">
                    <span class="text-sm font-bold text-gray-800">Enable Announcement Bar on Storefront</span>
                </label>

                <div x-show="announcementEnabled" x-transition class="space-y-4 pt-2">
                    <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
                        <div class="sm:col-span-1">
                            <label class="block text-xs font-bold text-gray-700 mb-1">Marquee Icon / Emoji</label>
                            <input type="text" name="announcement_emoji" value="{{ old('announcement_emoji', $social['announcement_emoji'] ?? '✨') }}"
                                   placeholder="✨"
                                   class="block w-full rounded-xl border-gray-200 shadow-sm focus:border-brand-sage focus:ring-brand-sage text-sm py-2.5 px-3 text-center">
                        </div>

                        <div class="sm:col-span-3">
                            <label class="block text-xs font-bold text-gray-700 mb-1">Announcement Message</label>
                            <input type="text" name="announcement_bar" value="{{ old('announcement_bar', $social['announcement_bar'] ?? '') }}"
                                   placeholder="e.g. Festive Handloom Edit: Complimentary express shipping across India on orders above ₹1,499"
                                   class="block w-full rounded-xl border-gray-200 shadow-sm focus:border-brand-sage focus:ring-brand-sage text-sm py-2.5 px-3">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1">Announcement Clickable Link (Optional)</label>
                        <input type="text" name="announcement_link" value="{{ old('announcement_link', $social['announcement_link'] ?? '') }}"
                               placeholder="e.g. https://... or leave blank"
                               class="block w-full rounded-xl border-gray-200 shadow-sm focus:border-brand-sage focus:ring-brand-sage text-sm py-2.5 px-3">
                    </div>
                </div>
            </div>
        </div>

        <!-- SECTION 4: Branding Images (Logo & Banner) -->
        <div class="bg-white rounded-2xl shadow-sm border border-stone-200/80 p-6 sm:p-8">
            <div class="flex items-center gap-3 pb-4 border-b border-stone-100 mb-6">
                <div class="w-9 h-9 rounded-xl bg-blue-50 text-blue-700 flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                </div>
                <div>
                    <h2 class="text-lg font-bold text-gray-900">Boutique Imagery & Artwork</h2>
                    <p class="text-xs text-gray-500">High-resolution logo monogram and panoramic hero artwork.</p>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-8">
                <!-- Logo -->
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-2">Boutique Logo / Medallion</label>
                    <div class="flex items-center gap-4">
                        <div class="w-20 h-20 rounded-full border-2 border-stone-200 overflow-hidden bg-stone-100 shrink-0 shadow-inner flex items-center justify-center">
                            @if($vendor->logo)
                                <img src="{{ asset('storage/' . $vendor->logo) }}" alt="Logo" class="w-full h-full object-cover">
                            @else
                                <span class="text-2xl font-serif font-bold text-stone-400">{{ strtoupper(substr($vendor->shop_name, 0, 1)) }}</span>
                            @endif
                        </div>
                        <div class="flex-1">
                            <input type="file" name="logo" accept="image/*" class="text-xs text-gray-500 file:mr-3 file:py-2 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-stone-100 file:text-stone-700 hover:file:bg-stone-200">
                            <p class="text-[11px] text-gray-400 mt-1">Square format 500x500px (PNG or JPG)</p>
                        </div>
                    </div>
                </div>

                <!-- Banner -->
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-2">Panoramic Hero Banner</label>
                    <div class="flex flex-col gap-3">
                        @if($vendor->banner)
                            <div class="w-full h-24 rounded-xl overflow-hidden border border-stone-200 shadow-inner">
                                <img src="{{ asset('storage/' . $vendor->banner) }}" alt="Banner" class="w-full h-full object-cover">
                            </div>
                        @endif
                        <input type="file" name="banner" accept="image/*" class="text-xs text-gray-500 file:mr-3 file:py-2 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-stone-100 file:text-stone-700 hover:file:bg-stone-200">
                        <p class="text-[11px] text-gray-400">High-resolution landscape 1600x600px (WebP or JPG)</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- SECTION 5: Feature Toggles & Credentials -->
        <div class="bg-white rounded-2xl shadow-sm border border-stone-200/80 p-6 sm:p-8">
            <div class="flex items-center gap-3 pb-4 border-b border-stone-100 mb-6">
                <div class="w-9 h-9 rounded-xl bg-teal-50 text-teal-700 flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
                <div>
                    <h2 class="text-lg font-bold text-gray-900">Storefront Section Toggles</h2>
                    <p class="text-xs text-gray-500">Control which interactive modules are displayed on your public storefront.</p>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
                <label class="flex items-start gap-3 p-3.5 rounded-xl border border-stone-200/80 hover:bg-stone-50 cursor-pointer">
                    <input type="checkbox" name="show_whatsapp_button" value="1" 
                           {{ (!isset($social['show_whatsapp_button']) || !empty($social['show_whatsapp_button'])) ? 'checked' : '' }}
                           class="w-4 h-4 mt-0.5 rounded border-gray-300 text-stone-900 focus:ring-stone-400">
                    <div>
                        <span class="text-xs font-bold text-gray-900 block">WhatsApp Stylist Button</span>
                        <span class="text-[11px] text-gray-500">Show 1-click WhatsApp concierge in header</span>
                    </div>
                </label>

                <label class="flex items-start gap-3 p-3.5 rounded-xl border border-stone-200/80 hover:bg-stone-50 cursor-pointer">
                    <input type="checkbox" name="show_stats_strip" value="1" 
                           {{ (!isset($social['show_stats_strip']) || !empty($social['show_stats_strip'])) ? 'checked' : '' }}
                           class="w-4 h-4 mt-0.5 rounded border-gray-300 text-stone-900 focus:ring-stone-400">
                    <div>
                        <span class="text-xs font-bold text-gray-900 block">Atelier Credentials Strip</span>
                        <span class="text-[11px] text-gray-500">Curated count, dispatch speed & authenticity</span>
                    </div>
                </label>

                <label class="flex items-start gap-3 p-3.5 rounded-xl border border-stone-200/80 hover:bg-stone-50 cursor-pointer">
                    <input type="checkbox" name="show_spotlight" value="1" 
                           {{ (!isset($social['show_spotlight']) || !empty($social['show_spotlight'])) ? 'checked' : '' }}
                           class="w-4 h-4 mt-0.5 rounded border-gray-300 text-stone-900 focus:ring-stone-400">
                    <div>
                        <span class="text-xs font-bold text-gray-900 block">Curated Spotlight Section</span>
                        <span class="text-[11px] text-gray-500">Highlight signature pieces above catalog</span>
                    </div>
                </label>
            </div>
        </div>

        <!-- SECTION 6: Social Media & Direct Customer Inquiries -->
        <div class="bg-white rounded-2xl shadow-sm border border-stone-200/80 p-6 sm:p-8">
            <div class="flex items-center gap-3 pb-4 border-b border-stone-100 mb-6">
                <div class="w-9 h-9 rounded-xl bg-emerald-50 text-emerald-700 flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path></svg>
                </div>
                <div>
                    <h2 class="text-lg font-bold text-gray-900">Customer Hotline & Social Profiles</h2>
                    <p class="text-xs text-gray-500">Direct contact channels where shoppers can consult you about custom tailoring or sizing.</p>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">WhatsApp Direct Hotline</label>
                    <div class="relative">
                        <input type="text" name="whatsapp" value="{{ old('whatsapp', $social['whatsapp'] ?? $vendor->contact_phone) }}" 
                               placeholder="e.g. 9876543210 (10 digits)"
                               class="block w-full rounded-xl border-gray-200 shadow-sm focus:border-brand-sage focus:ring-brand-sage text-sm py-2.5 px-3 pl-8">
                        <span class="absolute left-3 top-3 text-xs text-gray-400">💬</span>
                    </div>
                    <p class="text-[11px] text-gray-400 mt-1">Powers the direct 'WhatsApp Stylist' consultation button.</p>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">Instagram Handle / Profile URL</label>
                    <div class="relative">
                        <input type="text" name="instagram" value="{{ old('instagram', $social['instagram'] ?? '') }}" 
                               placeholder="e.g. @my_handloom_couture"
                               class="block w-full rounded-xl border-gray-200 shadow-sm focus:border-brand-sage focus:ring-brand-sage text-sm py-2.5 px-3 pl-8">
                        <span class="absolute left-3 top-3 text-xs text-gray-400">📷</span>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">Public Support Email</label>
                    <input type="email" name="contact_email" value="{{ old('contact_email', $vendor->contact_email) }}" 
                           placeholder="contact@boutique.com"
                           class="block w-full rounded-xl border-gray-200 shadow-sm focus:border-brand-sage focus:ring-brand-sage text-sm py-2.5 px-3">
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">Public Support Phone</label>
                    <input type="text" name="contact_phone" value="{{ old('contact_phone', $vendor->contact_phone) }}" 
                           placeholder="e.g. +91 9876543210"
                           class="block w-full rounded-xl border-gray-200 shadow-sm focus:border-brand-sage focus:ring-brand-sage text-sm py-2.5 px-3">
                </div>

                <div class="sm:col-span-2">
                    <label class="block text-xs font-bold text-gray-700 mb-1">Official Website / Portfolio Link (Optional)</label>
                    <input type="text" name="website" value="{{ old('website', $social['website'] ?? '') }}" 
                           placeholder="https://..."
                           class="block w-full rounded-xl border-gray-200 shadow-sm focus:border-brand-sage focus:ring-brand-sage text-sm py-2.5 px-3">
                </div>
            </div>
        </div>

        <!-- SECTION 7: Atelier Origin & Craft Heritage Story -->
        <div class="bg-white rounded-2xl shadow-sm border border-stone-200/80 p-6 sm:p-8">
            <div class="flex items-center gap-3 pb-4 border-b border-stone-100 mb-6">
                <div class="w-9 h-9 rounded-xl bg-stone-100 text-stone-700 flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                </div>
                <div>
                    <h2 class="text-lg font-bold text-gray-900">Craft Story & Workshop Heritage</h2>
                    <p class="text-xs text-gray-500">Official business location and artisan credentials displayed in your 'Craft Story' modal.</p>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div class="sm:col-span-2">
                    <label class="block text-xs font-bold text-gray-700 mb-1">
                        Physical Shop / Workshop Building Address <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="pickup_address[line_1]" value="{{ old('pickup_address.line_1', $vendor->pickup_address['line_1'] ?? '') }}" 
                           placeholder="Shop No., Market / Complex, Street Name"
                           class="block w-full rounded-xl border-gray-200 shadow-sm focus:border-brand-sage focus:ring-brand-sage text-sm py-2.5 px-3">
                </div>

                <div class="sm:col-span-2">
                    <label class="block text-xs font-bold text-gray-700 mb-1">Landmark / Area (Optional)</label>
                    <input type="text" name="pickup_address[line_2]" value="{{ old('pickup_address.line_2', $vendor->pickup_address['line_2'] ?? '') }}" 
                           placeholder="e.g. Near Hawa Mahal, Johari Bazaar"
                           class="block w-full rounded-xl border-gray-200 shadow-sm focus:border-brand-sage focus:ring-brand-sage text-sm py-2.5 px-3">
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">Atelier City / Origin <span class="text-red-500">*</span></label>
                    <input type="text" name="pickup_address[city]" value="{{ old('pickup_address.city', $vendor->pickup_address['city'] ?? '') }}" 
                           placeholder="e.g. Jaipur, Varanasi, Kolkata, Surat"
                           class="block w-full rounded-xl border-gray-200 shadow-sm focus:border-brand-sage focus:ring-brand-sage text-sm py-2.5 px-3">
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">State / Province <span class="text-red-500">*</span></label>
                    <input type="text" name="pickup_address[state]" value="{{ old('pickup_address.state', $vendor->pickup_address['state'] ?? '') }}" 
                           placeholder="e.g. Rajasthan, Uttar Pradesh, West Bengal"
                           class="block w-full rounded-xl border-gray-200 shadow-sm focus:border-brand-sage focus:ring-brand-sage text-sm py-2.5 px-3">
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">6-Digit Pickup Pincode <span class="text-red-500">*</span></label>
                    <input type="text" name="pickup_address[pincode]" value="{{ old('pickup_address.pincode', $vendor->pickup_address['pincode'] ?? '') }}" 
                           pattern="[0-9]{6}" maxlength="6"
                           placeholder="e.g. 302001"
                           class="block w-full rounded-xl border-gray-200 shadow-sm focus:border-brand-sage focus:ring-brand-sage text-sm py-2.5 px-3 font-mono font-bold">
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">Pickup Contact Phone</label>
                    <input type="tel" name="pickup_address[contact_phone]" value="{{ old('pickup_address.contact_phone', $vendor->pickup_address['contact_phone'] ?? $vendor->contact_phone ?? '') }}" 
                           placeholder="e.g. 9876543210"
                           class="block w-full rounded-xl border-gray-200 shadow-sm focus:border-brand-sage focus:ring-brand-sage text-sm py-2.5 px-3">
                </div>

                <div class="sm:col-span-2">
                    <label class="block text-xs font-bold text-gray-700 mb-2">3 Craft Heritage Highlights (Displayed in Story Modal)</label>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <input type="text" name="craft_highlight_1" value="{{ old('craft_highlight_1', $social['craft_highlight_1'] ?? '100% Handcrafted & Authentic Weaves') }}" 
                               placeholder="Highlight 1"
                               class="block w-full rounded-xl border-gray-200 shadow-sm focus:border-brand-sage text-xs py-2 px-3">
                        <input type="text" name="craft_highlight_2" value="{{ old('craft_highlight_2', $social['craft_highlight_2'] ?? 'Direct Atelier Dispatch with Quality Seal') }}" 
                               placeholder="Highlight 2"
                               class="block w-full rounded-xl border-gray-200 shadow-sm focus:border-brand-sage text-xs py-2 px-3">
                        <input type="text" name="craft_highlight_3" value="{{ old('craft_highlight_3', $social['craft_highlight_3'] ?? 'Eco-Conscious Packaging & 7-Day Exchange') }}" 
                               placeholder="Highlight 3"
                               class="block w-full rounded-xl border-gray-200 shadow-sm focus:border-brand-sage text-xs py-2 px-3">
                    </div>
                </div>

                <div class="sm:col-span-2">
                    <label class="block text-xs font-bold text-gray-700 mb-1">GSTIN Number (Optional)</label>
                    <input type="text" name="gstin" value="{{ old('gstin', $vendor->gstin) }}" 
                           placeholder="e.g. 08AAAAA0000A1Z5"
                           class="block w-full rounded-xl border-gray-200 shadow-sm focus:border-brand-sage focus:ring-brand-sage text-sm py-2.5 px-3">
                </div>

                <div class="sm:col-span-2">
                    <label class="block text-xs font-bold text-gray-700 mb-1">Craft Heritage Philosophy & Workshop Policies</label>
                    <textarea name="store_policies" rows="4" 
                              placeholder="Detail your handloom traditions, packaging standards, tailoring timelines, and return conditions..."
                              class="block w-full rounded-xl border-gray-200 shadow-sm focus:border-brand-sage focus:ring-brand-sage text-sm p-3">{{ old('store_policies', $vendor->store_policies) }}</textarea>
                </div>
            </div>
        </div>

        <!-- Sticky Bottom Mobile Action Bar -->
        <div class="sticky bottom-4 z-30 bg-white/95 backdrop-blur-md p-4 sm:p-5 rounded-2xl border border-stone-200/90 shadow-xl flex flex-col sm:flex-row items-center justify-between gap-4 mt-8">
            <div class="text-xs text-stone-500 hidden sm:block">
                All changes are previewed live on your boutique storefront.
            </div>
            <div class="w-full sm:w-auto flex items-center justify-end">
                <button type="submit" 
                        class="w-full sm:w-auto px-8 py-3.5 rounded-full bg-stone-900 hover:bg-black text-white text-sm font-bold tracking-wide transition-all shadow-md flex items-center justify-center gap-2 active:scale-95 cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    <span>Save & Publish Storefront</span>
                </button>
            </div>
        </div>
    </form>
</x-layouts.vendor>
