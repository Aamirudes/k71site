<x-layouts.vendor title="Launch New Product • Vendor Studio">
@php
    $curVendor = auth()->user()?->vendor;
    $vendorType = $vendorType ?? ($curVendor?->vendor_type ?? 'clothing');
    $sec = $sectorConfig ?? ($curVendor ? $curVendor->getSectorConfig() : [
        'icon' => '🛍️',
        'name' => 'Marketplace Product',
        'label' => 'Marketplace',
        'badge' => 'Curated Retail',
        'item_singular' => 'Product',
        'catalog_name' => 'Store Catalog',
    ]);
    $targetVertical = $targetVertical ?? ($curVendor ? $curVendor->getVerticalKey() : 'marketplace');
@endphp

<div x-data="liquidProductUploader()" class="relative pb-24 max-w-7xl mx-auto px-3 sm:px-6 lg:px-8">

    <!-- Ambient Liquid Refraction Glow Blobs -->
    <div class="fixed inset-0 pointer-events-none overflow-hidden z-0">
        <div class="absolute top-20 left-10 w-96 h-96 rounded-full bg-indigo-500/10 blur-[100px]"></div>
        <div class="absolute bottom-40 right-10 w-96 h-96 rounded-full bg-cyan-500/10 blur-[120px]"></div>
    </div>

    <!-- Liquid Top Header & Action Bar -->
    <div class="relative z-10 pt-4 pb-6">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 p-4 sm:p-5 rounded-2xl bg-white/70 dark:bg-slate-900/70 backdrop-blur-xl border border-white/40 dark:border-white/10 shadow-lg shadow-black/5">
            <div class="flex items-center gap-3.5">
                <a href="{{ route('vendor.products.index') }}" class="w-10 h-10 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700 flex items-center justify-center transition-colors shadow-inner">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                </a>
                <div>
                    <div class="flex items-center gap-2">
                        <span class="text-xl">{{ $sec['icon'] ?? '⚡' }}</span>
                        <h1 class="text-lg sm:text-2xl font-black text-slate-900 dark:text-white tracking-tight">
                            Add New {{ $sec['item_singular'] ?? 'Product' }}
                        </h1>
                        <span class="text-[10px] uppercase font-bold tracking-wider px-2 py-0.5 rounded-full bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 border border-indigo-500/20">
                            {{ $sec['label'] ?? ucfirst($vendorType) }} Mode
                        </span>
                    </div>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                        Streamlined, responsive launch form tailored for your {{ $sec['catalog_name'] ?? 'store' }}.
                    </p>
                </div>
            </div>

            <!-- Header Action Controls -->
            <div class="flex items-center gap-2.5">
                <a href="{{ route('vendor.products.index') }}" class="px-4 py-2 text-xs font-semibold text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white transition-colors">
                    Cancel
                </a>
                <button type="button" @click="submitForm()" :disabled="submitting" class="px-5 py-2.5 rounded-xl bg-gradient-to-r from-indigo-600 via-purple-600 to-cyan-600 hover:from-indigo-500 hover:to-cyan-500 text-white font-bold text-xs sm:text-sm tracking-wide shadow-lg shadow-indigo-500/25 active:scale-95 transition-all flex items-center gap-2 cursor-pointer">
                    <svg x-show="!submitting" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                    <svg x-show="submitting" x-cloak class="animate-spin w-4 h-4 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                    <span x-text="submitting ? 'Publishing...' : 'Publish Product'"></span>
                </button>
            </div>
        </div>
    </div>

    <!-- ⚡ 1-Tap Fast-Track Category Presets Bar (Slider without Price) -->
    <div class="relative z-10 mb-6">
        <div class="p-4 sm:p-5 rounded-2xl bg-gradient-to-br from-indigo-950/40 via-slate-900/60 to-purple-950/40 backdrop-blur-xl border border-indigo-500/20 shadow-xl">
            <div class="flex items-center justify-between gap-3 mb-3 pb-2.5 border-b border-white/10">
                <div class="flex items-center gap-2">
                    <span class="w-6 h-6 rounded-lg bg-amber-400/20 text-amber-300 flex items-center justify-center text-xs font-black">⚡</span>
                    <span class="text-xs sm:text-sm font-bold text-white tracking-wide">
                        1-Tap AI Launch Presets
                    </span>
                    <span class="text-[10px] bg-amber-400/20 text-amber-300 px-2 py-0.5 rounded-full font-mono font-semibold">Magic Auto-Fill</span>
                </div>
                <template x-if="appliedPreset">
                    <div class="flex items-center gap-2 bg-emerald-500/20 text-emerald-300 border border-emerald-500/30 px-2.5 py-1 rounded-lg text-xs font-bold">
                        <span>Applied: <strong x-text="appliedPreset"></strong></span>
                        <button type="button" @click="resetForm()" class="text-rose-400 hover:text-rose-300 font-bold ml-1 cursor-pointer">✕</button>
                    </div>
                </template>
            </div>

            <!-- Category Filter Slider Bar -->
            <div class="flex items-center gap-1.5 overflow-x-auto no-scrollbar pb-2.5 mb-2.5 border-b border-white/5 scroll-smooth">
                <button type="button" @click="selectedPresetCategory = 'all'"
                        :class="selectedPresetCategory === 'all' ? 'bg-white text-zinc-950 font-bold' : 'bg-white/10 text-zinc-300 hover:bg-white/15'"
                        class="px-3 py-1 rounded-full text-xs whitespace-nowrap transition cursor-pointer">
                    All Categories
                </button>
                <button type="button" @click="selectedPresetCategory = 'clothing'"
                        :class="selectedPresetCategory === 'clothing' ? 'bg-white text-zinc-950 font-bold' : 'bg-white/10 text-zinc-300 hover:bg-white/15'"
                        class="px-3 py-1 rounded-full text-xs whitespace-nowrap transition cursor-pointer">
                    👗 Clothing & Couture
                </button>
                <button type="button" @click="selectedPresetCategory = 'food'"
                        :class="selectedPresetCategory === 'food' ? 'bg-white text-zinc-950 font-bold' : 'bg-white/10 text-zinc-300 hover:bg-white/15'"
                        class="px-3 py-1 rounded-full text-xs whitespace-nowrap transition cursor-pointer">
                    🍛 Food & Sweets
                </button>
                <button type="button" @click="selectedPresetCategory = 'groceries'"
                        :class="selectedPresetCategory === 'groceries' ? 'bg-white text-zinc-950 font-bold' : 'bg-white/10 text-zinc-300 hover:bg-white/15'"
                        class="px-3 py-1 rounded-full text-xs whitespace-nowrap transition cursor-pointer">
                    ⚡ Feasto & Daily Needs
                </button>
                <button type="button" @click="selectedPresetCategory = 'marble'"
                        :class="selectedPresetCategory === 'marble' ? 'bg-white text-zinc-950 font-bold' : 'bg-white/10 text-zinc-300 hover:bg-white/15'"
                        class="px-3 py-1 rounded-full text-xs whitespace-nowrap transition cursor-pointer">
                    🧱 Marble & Stone
                </button>
                <button type="button" @click="selectedPresetCategory = 'home_living'"
                        :class="selectedPresetCategory === 'home_living' ? 'bg-white text-zinc-950 font-bold' : 'bg-white/10 text-zinc-300 hover:bg-white/15'"
                        class="px-3 py-1 rounded-full text-xs whitespace-nowrap transition cursor-pointer">
                    🛋️ Home & Living
                </button>
                <button type="button" @click="selectedPresetCategory = 'real_estate'"
                        :class="selectedPresetCategory === 'real_estate' ? 'bg-white text-zinc-950 font-bold' : 'bg-white/10 text-zinc-300 hover:bg-white/15'"
                        class="px-3 py-1 rounded-full text-xs whitespace-nowrap transition cursor-pointer">
                    🏡 Real Estate
                </button>
                <button type="button" @click="selectedPresetCategory = 'automotive'"
                        :class="selectedPresetCategory === 'automotive' ? 'bg-white text-zinc-950 font-bold' : 'bg-white/10 text-zinc-300 hover:bg-white/15'"
                        class="px-3 py-1 rounded-full text-xs whitespace-nowrap transition cursor-pointer">
                    🚗 Automotive
                </button>
            </div>

            <!-- Scrollable Presets Slider (Without Price) -->
            <div class="flex items-stretch gap-3 overflow-x-auto no-scrollbar py-1 scroll-smooth">
                <template x-for="(preset, key) in currentSectorPresets" :key="key">
                    <button 
                        type="button" 
                        @click="applyPreset(preset)"
                        :class="appliedPreset === preset.title ? 'border-cyan-400 bg-cyan-500/20 text-white shadow-lg shadow-cyan-500/10' : 'border-white/10 bg-white/5 hover:bg-white/10 text-slate-200'"
                        class="min-w-[200px] max-w-[240px] p-3 rounded-xl border text-left transition-all group flex flex-col justify-between shrink-0 cursor-pointer"
                    >
                        <div class="flex items-center justify-between mb-1.5">
                            <span class="text-xl" x-text="preset.icon"></span>
                            <span class="text-[9px] font-bold uppercase tracking-wider text-cyan-300 bg-cyan-950/60 border border-cyan-800/40 px-1.5 py-0.5 rounded">1-Tap Fill</span>
                        </div>
                        <div class="text-xs font-bold truncate group-hover:text-cyan-300 transition-colors" x-text="preset.title"></div>
                        <div class="text-[10px] text-slate-400 truncate mt-1" x-text="preset.brand || 'Category Preset'"></div>
                    </button>
                </template>
            </div>
        </div>
    </div>

    @if ($errors->any())
        <div class="relative z-10 mb-6 p-4 rounded-2xl bg-rose-500/10 border border-rose-500/30 text-rose-200 backdrop-blur-md">
            <div class="flex items-center gap-2 font-bold text-xs uppercase tracking-wider text-rose-400 mb-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span>Please fix the following issues:</span>
            </div>
            <ul class="list-disc list-inside text-xs space-y-1">
                @foreach ($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Main Creation Form -->
    <form id="productForm" action="{{ route('vendor.products.store') }}" method="POST" enctype="multipart/form-data" class="relative z-10">
        @csrf
        <input type="hidden" name="vertical" :value="form.vertical">
        <input type="hidden" name="listing_type" :value="form.listing_type">
        <input type="hidden" name="status" :value="form.status">

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 sm:gap-8">
            
            <!-- LEFT 2 COLUMNS: Core Fields & Specifications -->
            <div class="lg:col-span-2 space-y-6">

                <!-- 1. Essential Information Glass Card -->
                <div class="p-5 sm:p-7 rounded-3xl bg-white/80 dark:bg-slate-900/80 backdrop-blur-2xl border border-white/40 dark:border-white/10 shadow-xl space-y-5">
                    <div class="flex items-center justify-between pb-3 border-b border-slate-200 dark:border-white/10">
                        <div class="flex items-center gap-2.5">
                            <span class="w-7 h-7 rounded-xl bg-indigo-500/10 text-indigo-500 flex items-center justify-center font-bold text-xs">1</span>
                            <h2 class="text-base font-bold text-slate-900 dark:text-white">Product Identification</h2>
                        </div>
                        <span class="text-[11px] text-slate-400">Required fields</span>
                    </div>

                    <!-- Title -->
                    <div>
                        <label for="p-title" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                            Product Title <span class="text-rose-500">*</span>
                        </label>
                        <input 
                            id="p-title" 
                            name="title" 
                            type="text" 
                            required 
                            x-model="form.title" 
                            placeholder="{{ $sec['title_placeholder'] ?? 'e.g. Product Title' }}" 
                            class="w-full px-4 py-3 rounded-xl bg-slate-50 dark:bg-slate-950/60 border border-slate-200 dark:border-white/10 text-slate-900 dark:text-white text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all shadow-inner"
                        >
                    </div>

                    <!-- Category & Brand Row -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="p-category" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                                Category <span class="text-rose-500">*</span>
                            </label>
                            <select 
                                id="p-category" 
                                name="category_id" 
                                required 
                                x-model="form.category_id" 
                                class="w-full px-4 py-3 rounded-xl bg-slate-50 dark:bg-slate-950/60 border border-slate-200 dark:border-white/10 text-slate-900 dark:text-white text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all shadow-inner"
                            >
                                <option value="">-- Choose Category --</option>
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->id }}">
                                        {{ $cat->parent ? $cat->parent->name . ' → ' : '' }}{{ $cat->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="p-brand" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                                {{ $sec['brand_label'] ?? 'Brand / Manufacturer' }}
                            </label>
                            <input 
                                id="p-brand" 
                                name="brand" 
                                type="text" 
                                x-model="form.brand" 
                                placeholder="{{ $sec['brand_placeholder'] ?? 'e.g. Brand Name' }}" 
                                class="w-full px-4 py-3 rounded-xl bg-slate-50 dark:bg-slate-950/60 border border-slate-200 dark:border-white/10 text-slate-900 dark:text-white text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all shadow-inner"
                            >
                        </div>
                    </div>

                    <!-- Full Description -->
                    <div>
                        <label for="p-description" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                            Description &amp; Highlights <span class="text-rose-500">*</span>
                        </label>
                        <textarea 
                            id="p-description" 
                            name="description" 
                            rows="4" 
                            required 
                            x-model="form.description" 
                            placeholder="{{ $sec['desc_placeholder'] ?? 'Detailed product overview, features, warranty terms...' }}" 
                            class="w-full px-4 py-3 rounded-xl bg-slate-50 dark:bg-slate-950/60 border border-slate-200 dark:border-white/10 text-slate-900 dark:text-white text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all shadow-inner leading-relaxed"
                        ></textarea>
                    </div>
                </div>

                <!-- 2. Dynamic Specifications (Tailored to Active Sector) -->
                <div class="p-5 sm:p-7 rounded-3xl bg-white/80 dark:bg-slate-900/80 backdrop-blur-2xl border border-white/40 dark:border-white/10 shadow-xl space-y-4">
                    <div class="flex items-center justify-between pb-3 border-b border-slate-200 dark:border-white/10">
                        <div class="flex items-center gap-2.5">
                            <span class="w-7 h-7 rounded-xl bg-cyan-500/10 text-cyan-500 flex items-center justify-center font-bold text-xs">2</span>
                            <div>
                                <h2 class="text-base font-bold text-slate-900 dark:text-white">Technical &amp; Product Specifications</h2>
                                <p class="text-xs text-slate-500 dark:text-slate-400">Structured parameters displayed cleanly on your storefront specification table.</p>
                            </div>
                        </div>
                        <button type="button" @click="addSpecRow()" class="text-xs font-bold text-indigo-500 hover:text-indigo-400 flex items-center gap-1 cursor-pointer">
                            <span>+ Add Spec</span>
                        </button>
                    </div>

                    <!-- Responsive Spec Key-Value Capsules -->
                    <div class="space-y-2.5">
                        <template x-for="(spec, index) in form.specs" :key="index">
                            <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2 p-2 sm:p-2.5 rounded-2xl bg-slate-50 dark:bg-slate-950/60 border border-slate-200 dark:border-white/10 transition-all shadow-inner">
                                <input 
                                    type="text" 
                                    x-model="spec.key" 
                                    :name="'specifications[' + index + '][name]'" 
                                    placeholder="Spec Key (e.g. RAM, Sizing, Fabric, Motor)" 
                                    class="w-full sm:w-1/3 px-3.5 py-2.5 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-white/10 text-slate-900 dark:text-white text-xs font-semibold focus:ring-2 focus:ring-cyan-500 shadow-inner"
                                >
                                <input 
                                    type="text" 
                                    x-model="spec.value" 
                                    :name="'specifications[' + index + '][value]'" 
                                    placeholder="Value (e.g. 16GB LPDDR5X, 100% Cotton, 415V Three Phase)" 
                                    class="flex-1 px-3.5 py-2.5 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-white/10 text-slate-900 dark:text-white text-xs focus:ring-2 focus:ring-cyan-500 shadow-inner"
                                >
                                <button type="button" @click="removeSpecRow(index)" class="p-2 self-end sm:self-center rounded-lg text-slate-400 hover:text-rose-500 transition-colors cursor-pointer" title="Remove specification">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </div>
                        </template>
                    </div>
                </div>

                <!-- 2.5 Category-Adaptive Product Variations & Options Studio -->
                <div class="p-5 sm:p-7 rounded-3xl bg-white/80 dark:bg-slate-900/80 backdrop-blur-2xl border border-white/40 dark:border-white/10 shadow-xl space-y-5">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-4 border-b border-slate-200 dark:border-white/10 gap-3">
                        <div class="flex items-center gap-2.5">
                            <span class="w-7 h-7 rounded-xl bg-indigo-500/10 text-indigo-500 flex items-center justify-center font-bold text-xs">2.5</span>
                            <div>
                                <h2 class="text-base font-bold text-slate-900 dark:text-white flex items-center gap-2">
                                    <span>Product Variations &amp; Options</span>
                                    <span class="text-[10px] font-black uppercase tracking-wider px-2 py-0.5 rounded-full bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 border border-indigo-500/20">
                                        Adaptive Studio
                                    </span>
                                </h2>
                                <p class="text-xs text-slate-500 dark:text-slate-400">Configure clothing sizes, footwear sizing, grocery pack units, hardware specs, or food portions.</p>
                            </div>
                        </div>

                        <!-- Category Selector Pills -->
                        <div class="flex items-center gap-1 p-1 bg-slate-100 dark:bg-slate-950 rounded-xl overflow-x-auto text-[11px] font-bold">
                            <button type="button" @click="activeVariantTab = 'clothing'" :class="activeVariantTab === 'clothing' ? 'bg-white dark:bg-slate-800 text-indigo-600 dark:text-indigo-400 shadow-sm' : 'text-slate-500 hover:text-slate-800'" class="px-2.5 py-1.5 rounded-lg transition-all shrink-0 cursor-pointer">👗 Clothing</button>
                            <button type="button" @click="activeVariantTab = 'footwear'" :class="activeVariantTab === 'footwear' ? 'bg-white dark:bg-slate-800 text-indigo-600 dark:text-indigo-400 shadow-sm' : 'text-slate-500 hover:text-slate-800'" class="px-2.5 py-1.5 rounded-lg transition-all shrink-0 cursor-pointer">👟 Footwear</button>
                            <button type="button" @click="activeVariantTab = 'kirana'" :class="activeVariantTab === 'kirana' ? 'bg-white dark:bg-slate-800 text-indigo-600 dark:text-indigo-400 shadow-sm' : 'text-slate-500 hover:text-slate-800'" class="px-2.5 py-1.5 rounded-lg transition-all shrink-0 cursor-pointer">🛒 Kirana</button>
                            <button type="button" @click="activeVariantTab = 'electronics'" :class="activeVariantTab === 'electronics' ? 'bg-white dark:bg-slate-800 text-indigo-600 dark:text-indigo-400 shadow-sm' : 'text-slate-500 hover:text-slate-800'" class="px-2.5 py-1.5 rounded-lg transition-all shrink-0 cursor-pointer">📱 Electronics</button>
                            <button type="button" @click="activeVariantTab = 'food'" :class="activeVariantTab === 'food' ? 'bg-white dark:bg-slate-800 text-indigo-600 dark:text-indigo-400 shadow-sm' : 'text-slate-500 hover:text-slate-800'" class="px-2.5 py-1.5 rounded-lg transition-all shrink-0 cursor-pointer">🍽️ Food</button>
                            <button type="button" @click="activeVariantTab = 'enquiry'" :class="activeVariantTab === 'enquiry' ? 'bg-white dark:bg-slate-800 text-indigo-600 dark:text-indigo-400 shadow-sm' : 'text-slate-500 hover:text-slate-800'" class="px-2.5 py-1.5 rounded-lg transition-all shrink-0 cursor-pointer">📋 Marble / B2B</button>
                        </div>
                    </div>

                    <!-- 1. CLOTHING VARIATIONS (Sizes XS-3XL & Colors) -->
                    <div x-show="activeVariantTab === 'clothing'" class="space-y-4">
                        <div class="p-4 rounded-2xl bg-indigo-50/50 dark:bg-indigo-950/20 border border-indigo-200/50 dark:border-indigo-800/30">
                            <!-- Color Swatches Selection -->
                            <div class="mb-4">
                                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-2">
                                    1. Pick Apparel Colors:
                                </label>
                                <div class="flex flex-wrap gap-2 mb-2">
                                    <template x-for="(col, cIdx) in availableColors" :key="col.name">
                                        <button type="button" 
                                                @click="toggleClothingColor(col)"
                                                :class="selectedColors.some(c => c.name === col.name) ? 'ring-2 ring-indigo-500 bg-white dark:bg-slate-800 text-slate-900 dark:text-white font-bold shadow-sm' : 'bg-white/60 dark:bg-slate-900/60 text-slate-600 dark:text-slate-400 border border-slate-200 dark:border-white/10'"
                                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs transition-all cursor-pointer">
                                            <span class="w-3.5 h-3.5 rounded-full border border-black/20 shrink-0" :style="'background-color:' + col.hex"></span>
                                            <span x-text="col.name"></span>
                                            <span x-show="selectedColors.some(c => c.name === col.name)" class="text-indigo-600 font-bold">✓</span>
                                        </button>
                                    </template>
                                </div>
                                <div class="flex items-center gap-2">
                                    <input type="text" x-model="customColorName" placeholder="Custom Color (e.g. Coral Peach)" class="text-xs px-3 py-1.5 rounded-xl border border-slate-200 dark:border-white/10 bg-white dark:bg-slate-900 text-slate-900 dark:text-white">
                                    <input type="color" x-model="customColorHex" class="w-7 h-7 rounded-lg cursor-pointer border-0 p-0">
                                    <button type="button" @click="addCustomClothingColor()" class="px-3 py-1.5 rounded-xl bg-slate-900 hover:bg-black text-white text-xs font-bold transition cursor-pointer">
                                        + Add Color
                                    </button>
                                </div>
                            </div>

                            <!-- Clothing Sizes Chips -->
                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-2">
                                    2. Pick Clothing Sizes:
                                </label>
                                <div class="flex flex-wrap gap-2 mb-2">
                                    <template x-for="sz in availableSizes" :key="sz">
                                        <button type="button" 
                                                @click="toggleClothingSize(sz)"
                                                :class="selectedSizes.includes(sz) ? 'bg-indigo-600 text-white font-bold shadow-sm ring-2 ring-indigo-400/40' : 'bg-white/60 dark:bg-slate-900/60 text-slate-600 dark:text-slate-400 border border-slate-200 dark:border-white/10'"
                                                class="px-3 py-1.5 rounded-xl text-xs transition-all cursor-pointer">
                                            <span x-text="sz"></span>
                                            <span x-show="selectedSizes.includes(sz)" class="ml-1">✓</span>
                                        </button>
                                    </template>
                                </div>
                                <div class="flex items-center gap-2">
                                    <input type="text" x-model="customSize" placeholder="Custom Size (e.g. 4XL or 38-R)" class="text-xs px-3 py-1.5 rounded-xl border border-slate-200 dark:border-white/10 bg-white dark:bg-slate-900 text-slate-900 dark:text-white">
                                    <button type="button" @click="addCustomClothingSize()" class="px-3 py-1.5 rounded-xl bg-slate-900 hover:bg-black text-white text-xs font-bold transition cursor-pointer">
                                        + Add Size
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Generated Variation Table -->
                        <div x-show="variants.length > 0" class="space-y-3">
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 p-3 bg-slate-100 dark:bg-slate-950 rounded-2xl">
                                <span class="text-xs font-bold text-slate-700 dark:text-slate-300" x-text="variants.length + ' variations generated (Color × Size Matrix)'"></span>
                                <div class="flex items-center gap-2">
                                    <input type="number" step="0.01" x-model="bulkPrice" placeholder="Bulk Price (₹)" class="w-28 px-2.5 py-1 text-xs rounded-lg border border-slate-300 dark:border-white/10 bg-white dark:bg-slate-900">
                                    <input type="number" x-model="bulkStock" placeholder="Bulk Stock" class="w-24 px-2.5 py-1 text-xs rounded-lg border border-slate-300 dark:border-white/10 bg-white dark:bg-slate-900">
                                    <button type="button" @click="applyBulkToClothing()" class="px-3 py-1 bg-slate-900 text-white rounded-lg text-xs font-bold hover:bg-black cursor-pointer">Apply All</button>
                                </div>
                            </div>

                            <div class="overflow-x-auto rounded-2xl border border-slate-200 dark:border-white/10">
                                <table class="min-w-full divide-y divide-slate-200 dark:divide-white/10 text-xs">
                                    <thead class="bg-slate-50 dark:bg-slate-950/60 font-bold uppercase text-slate-500">
                                        <tr>
                                            <th class="px-3 py-2.5 text-left">Color</th>
                                            <th class="px-3 py-2.5 text-left">Size</th>
                                            <th class="px-3 py-2.5 text-left">SKU</th>
                                            <th class="px-3 py-2.5 text-left">Price (₹)</th>
                                            <th class="px-3 py-2.5 text-left">Stock</th>
                                            <th class="px-3 py-2.5 text-right">Remove</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-100 dark:divide-white/5 bg-white dark:bg-slate-900">
                                        <template x-for="(v, idx) in variants" :key="idx">
                                            <tr>
                                                <td class="px-3 py-2 whitespace-nowrap">
                                                    <div class="flex items-center gap-1.5">
                                                        <span class="w-3 h-3 rounded-full border border-black/20" :style="'background-color:' + (v.color_hex || '#000')"></span>
                                                        <span class="font-bold" x-text="v.color || 'Standard'"></span>
                                                    </div>
                                                    <input type="hidden" :name="'variants[' + idx + '][name]'" :value="v.name">
                                                    <input type="hidden" :name="'variants[' + idx + '][color]'" :value="v.color">
                                                    <input type="hidden" :name="'variants[' + idx + '][color_hex]'" :value="v.color_hex">
                                                </td>
                                                <td class="px-3 py-2 whitespace-nowrap font-bold" x-text="v.size">
                                                    <input type="hidden" :name="'variants[' + idx + '][size]'" :value="v.size">
                                                </td>
                                                <td class="px-3 py-2 whitespace-nowrap">
                                                    <input type="text" :name="'variants[' + idx + '][sku]'" x-model="v.sku" class="w-28 px-2 py-1 text-xs rounded-lg border border-slate-200 dark:border-white/10 bg-slate-50 dark:bg-slate-950">
                                                </td>
                                                <td class="px-3 py-2 whitespace-nowrap">
                                                    <input type="number" step="0.01" :name="'variants[' + idx + '][price]'" x-model="v.price" class="w-24 px-2 py-1 text-xs font-bold rounded-lg border border-slate-200 dark:border-white/10 bg-slate-50 dark:bg-slate-950">
                                                </td>
                                                <td class="px-3 py-2 whitespace-nowrap">
                                                    <input type="number" :name="'variants[' + idx + '][stock]'" x-model="v.stock" class="w-20 px-2 py-1 text-xs font-bold rounded-lg border border-slate-200 dark:border-white/10 bg-slate-50 dark:bg-slate-950">
                                                </td>
                                                <td class="px-3 py-2 whitespace-nowrap text-right">
                                                    <button type="button" @click="variants.splice(idx, 1)" class="p-1 text-rose-500 hover:text-rose-700 cursor-pointer">✕</button>
                                                </td>
                                            </tr>
                                        </template>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- 2. FOOTWEAR VARIATIONS (Shoe Sizes UK 5 to UK 12) -->
                    <div x-show="activeVariantTab === 'footwear'" class="space-y-4">
                        <div class="p-4 rounded-2xl bg-amber-50/50 dark:bg-amber-950/20 border border-amber-200/50 dark:border-amber-800/30">
                            <!-- Footwear Colors Selection -->
                            <div class="mb-4">
                                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-2">
                                    1. Pick Shoe Colors:
                                </label>
                                <div class="flex flex-wrap gap-2">
                                    <template x-for="col in availableShoeColors" :key="col.name">
                                        <button type="button" 
                                                @click="toggleShoeColor(col)"
                                                :class="selectedShoeColors.some(c => c.name === col.name) ? 'ring-2 ring-amber-500 bg-white dark:bg-slate-800 text-slate-900 dark:text-white font-bold shadow-sm' : 'bg-white/60 dark:bg-slate-900/60 text-slate-600 dark:text-slate-400 border border-slate-200 dark:border-white/10'"
                                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs transition-all cursor-pointer">
                                            <span class="w-3.5 h-3.5 rounded-full border border-black/20 shrink-0" :style="'background-color:' + col.hex"></span>
                                            <span x-text="col.name"></span>
                                            <span x-show="selectedShoeColors.some(c => c.name === col.name)" class="text-amber-600 font-bold">✓</span>
                                        </button>
                                    </template>
                                </div>
                            </div>

                            <!-- UK Shoe Sizes Chips -->
                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-2">
                                    2. Pick UK/India Shoe Sizes:
                                </label>
                                <div class="flex flex-wrap gap-2">
                                    <template x-for="sz in availableShoeSizes" :key="sz">
                                        <button type="button" 
                                                @click="toggleShoeSize(sz)"
                                                :class="selectedShoeSizes.includes(sz) ? 'bg-amber-600 text-white font-bold shadow-sm ring-2 ring-amber-400/40' : 'bg-white/60 dark:bg-slate-900/60 text-slate-600 dark:text-slate-400 border border-slate-200 dark:border-white/10'"
                                                class="px-3 py-1.5 rounded-xl text-xs transition-all cursor-pointer">
                                            <span x-text="sz"></span>
                                            <span x-show="selectedShoeSizes.includes(sz)" class="ml-1">✓</span>
                                        </button>
                                    </template>
                                </div>
                            </div>
                        </div>

                        <!-- Footwear Matrix Table -->
                        <div x-show="footwearVariants.length > 0" class="space-y-3">
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 p-3 bg-slate-100 dark:bg-slate-950 rounded-2xl">
                                <span class="text-xs font-bold text-slate-700 dark:text-slate-300" x-text="footwearVariants.length + ' shoe variants generated'"></span>
                                <div class="flex items-center gap-2">
                                    <input type="number" step="0.01" x-model="bulkShoePrice" placeholder="Bulk Price (₹)" class="w-28 px-2.5 py-1 text-xs rounded-lg border border-slate-300 dark:border-white/10 bg-white dark:bg-slate-900">
                                    <input type="number" x-model="bulkShoeStock" placeholder="Bulk Stock" class="w-24 px-2.5 py-1 text-xs rounded-lg border border-slate-300 dark:border-white/10 bg-white dark:bg-slate-900">
                                    <button type="button" @click="applyBulkToFootwear()" class="px-3 py-1 bg-slate-900 text-white rounded-lg text-xs font-bold hover:bg-black cursor-pointer">Apply All</button>
                                </div>
                            </div>

                            <div class="overflow-x-auto rounded-2xl border border-slate-200 dark:border-white/10">
                                <table class="min-w-full divide-y divide-slate-200 dark:divide-white/10 text-xs">
                                    <thead class="bg-slate-50 dark:bg-slate-950/60 font-bold uppercase text-slate-500">
                                        <tr>
                                            <th class="px-3 py-2.5 text-left">Color</th>
                                            <th class="px-3 py-2.5 text-left">Shoe Size</th>
                                            <th class="px-3 py-2.5 text-left">SKU</th>
                                            <th class="px-3 py-2.5 text-left">Price (₹)</th>
                                            <th class="px-3 py-2.5 text-left">Stock</th>
                                            <th class="px-3 py-2.5 text-right">Remove</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-100 dark:divide-white/5 bg-white dark:bg-slate-900">
                                        <template x-for="(v, idx) in footwearVariants" :key="idx">
                                            <tr>
                                                <td class="px-3 py-2 whitespace-nowrap">
                                                    <div class="flex items-center gap-1.5">
                                                        <span class="w-3 h-3 rounded-full border border-black/20" :style="'background-color:' + (v.color_hex || '#000')"></span>
                                                        <span class="font-bold" x-text="v.color"></span>
                                                    </div>
                                                    <input type="hidden" :name="'variants[' + idx + '][name]'" :value="v.name">
                                                    <input type="hidden" :name="'variants[' + idx + '][color]'" :value="v.color">
                                                    <input type="hidden" :name="'variants[' + idx + '][color_hex]'" :value="v.color_hex">
                                                </td>
                                                <td class="px-3 py-2 whitespace-nowrap font-bold" x-text="v.size">
                                                    <input type="hidden" :name="'variants[' + idx + '][size]'" :value="v.size">
                                                </td>
                                                <td class="px-3 py-2 whitespace-nowrap">
                                                    <input type="text" :name="'variants[' + idx + '][sku]'" x-model="v.sku" class="w-28 px-2 py-1 text-xs rounded-lg border border-slate-200 dark:border-white/10 bg-slate-50 dark:bg-slate-950">
                                                </td>
                                                <td class="px-3 py-2 whitespace-nowrap">
                                                    <input type="number" step="0.01" :name="'variants[' + idx + '][price]'" x-model="v.price" class="w-24 px-2 py-1 text-xs font-bold rounded-lg border border-slate-200 dark:border-white/10 bg-slate-50 dark:bg-slate-950">
                                                </td>
                                                <td class="px-3 py-2 whitespace-nowrap">
                                                    <input type="number" :name="'variants[' + idx + '][stock]'" x-model="v.stock" class="w-20 px-2 py-1 text-xs font-bold rounded-lg border border-slate-200 dark:border-white/10 bg-slate-50 dark:bg-slate-950">
                                                </td>
                                                <td class="px-3 py-2 whitespace-nowrap text-right">
                                                    <button type="button" @click="footwearVariants.splice(idx, 1)" class="p-1 text-rose-500 hover:text-rose-700 cursor-pointer">✕</button>
                                                </td>
                                            </tr>
                                        </template>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- 3. KIRANA & GROCERY (100g, 250g, 500g, 1kg, 5kg pack sizes) -->
                    <div x-show="activeVariantTab === 'kirana'" class="space-y-4">
                        <div class="p-4 rounded-2xl bg-emerald-50/50 dark:bg-emerald-950/20 border border-emerald-200/50 dark:border-emerald-800/30">
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-2">
                                Quick Add Grocery Pack Sizes &amp; Units:
                            </label>
                            <div class="flex flex-wrap gap-2 mb-3">
                                <template x-for="unit in availableGroceryUnits" :key="unit">
                                    <button type="button" 
                                            @click="addGroceryPack(unit)"
                                            class="px-3 py-1.5 rounded-xl bg-white dark:bg-slate-900 border border-emerald-300 dark:border-emerald-800 text-emerald-800 dark:text-emerald-300 text-xs font-bold hover:bg-emerald-100 transition cursor-pointer flex items-center gap-1 shadow-2xs">
                                        <span>+</span>
                                        <span x-text="unit"></span>
                                    </button>
                                </template>
                            </div>
                            <p class="text-[11px] text-slate-500 dark:text-slate-400">
                                D-Mart / Reliance Mart style retail packing. Customers choose their exact pack size on the product page.
                            </p>
                        </div>

                        <!-- Grocery Variations Table -->
                        <div x-show="groceryVariants.length > 0" class="space-y-3">
                            <div class="overflow-x-auto rounded-2xl border border-slate-200 dark:border-white/10">
                                <table class="min-w-full divide-y divide-slate-200 dark:divide-white/10 text-xs">
                                    <thead class="bg-slate-50 dark:bg-slate-950/60 font-bold uppercase text-slate-500">
                                        <tr>
                                            <th class="px-3 py-2.5 text-left">Pack Size / Unit</th>
                                            <th class="px-3 py-2.5 text-left">Selling Price (₹)</th>
                                            <th class="px-3 py-2.5 text-left">Available Stock</th>
                                            <th class="px-3 py-2.5 text-left">Barcode / SKU</th>
                                            <th class="px-3 py-2.5 text-right">Remove</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-100 dark:divide-white/5 bg-white dark:bg-slate-900">
                                        <template x-for="(g, idx) in groceryVariants" :key="idx">
                                            <tr>
                                                <td class="px-3 py-2 whitespace-nowrap">
                                                    <input type="text" :name="'variants[' + idx + '][name]'" x-model="g.name" class="w-32 px-2 py-1 text-xs font-bold rounded-lg border border-slate-200 dark:border-white/10 bg-slate-50 dark:bg-slate-950">
                                                    <input type="hidden" :name="'variants[' + idx + '][size]'" :value="g.size">
                                                </td>
                                                <td class="px-3 py-2 whitespace-nowrap">
                                                    <input type="number" step="0.01" :name="'variants[' + idx + '][price]'" x-model="g.price" placeholder="99.00" class="w-28 px-2 py-1 text-xs font-bold rounded-lg border border-slate-200 dark:border-white/10 bg-slate-50 dark:bg-slate-950">
                                                </td>
                                                <td class="px-3 py-2 whitespace-nowrap">
                                                    <input type="number" :name="'variants[' + idx + '][stock]'" x-model="g.stock" placeholder="50" class="w-24 px-2 py-1 text-xs font-bold rounded-lg border border-slate-200 dark:border-white/10 bg-slate-50 dark:bg-slate-950">
                                                </td>
                                                <td class="px-3 py-2 whitespace-nowrap">
                                                    <input type="text" :name="'variants[' + idx + '][sku]'" x-model="g.sku" placeholder="EAN / SKU" class="w-32 px-2 py-1 text-xs rounded-lg border border-slate-200 dark:border-white/10 bg-slate-50 dark:bg-slate-950">
                                                </td>
                                                <td class="px-3 py-2 whitespace-nowrap text-right">
                                                    <button type="button" @click="groceryVariants.splice(idx, 1)" class="p-1 text-rose-500 hover:text-rose-700 cursor-pointer">✕</button>
                                                </td>
                                            </tr>
                                        </template>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- 4. ELECTRONICS & MOBILES (RAM + Storage + Colors) -->
                    <div x-show="activeVariantTab === 'electronics'" class="space-y-4">
                        <div class="p-4 rounded-2xl bg-cyan-50/50 dark:bg-cyan-950/20 border border-cyan-200/50 dark:border-cyan-800/30">
                            <!-- RAM Selection -->
                            <div class="mb-3">
                                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                                    1. Choose RAM Options:
                                </label>
                                <div class="flex flex-wrap gap-2">
                                    <template x-for="r in availableRams" :key="r">
                                        <button type="button" 
                                                @click="toggleElecRam(r)"
                                                :class="selectedRams.includes(r) ? 'bg-cyan-600 text-white font-bold shadow-sm' : 'bg-white dark:bg-slate-900 text-slate-600 dark:text-slate-400 border border-slate-200 dark:border-white/10'"
                                                class="px-3 py-1.5 rounded-xl text-xs transition cursor-pointer">
                                            <span x-text="r"></span>
                                            <span x-show="selectedRams.includes(r)">✓</span>
                                        </button>
                                    </template>
                                </div>
                            </div>

                            <!-- Storage Selection -->
                            <div class="mb-3">
                                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                                    2. Choose Internal Storage:
                                </label>
                                <div class="flex flex-wrap gap-2">
                                    <template x-for="st in availableStorages" :key="st">
                                        <button type="button" 
                                                @click="toggleElecStorage(st)"
                                                :class="selectedStorages.includes(st) ? 'bg-cyan-600 text-white font-bold shadow-sm' : 'bg-white dark:bg-slate-900 text-slate-600 dark:text-slate-400 border border-slate-200 dark:border-white/10'"
                                                class="px-3 py-1.5 rounded-xl text-xs transition cursor-pointer">
                                            <span x-text="st"></span>
                                            <span x-show="selectedStorages.includes(st)">✓</span>
                                        </button>
                                    </template>
                                </div>
                            </div>
                        </div>

                        <!-- Electronics Matrix Table -->
                        <div x-show="elecVariants.length > 0" class="space-y-3">
                            <div class="overflow-x-auto rounded-2xl border border-slate-200 dark:border-white/10">
                                <table class="min-w-full divide-y divide-slate-200 dark:divide-white/10 text-xs">
                                    <thead class="bg-slate-50 dark:bg-slate-950/60 font-bold uppercase text-slate-500">
                                        <tr>
                                            <th class="px-3 py-2.5 text-left">Configuration</th>
                                            <th class="px-3 py-2.5 text-left">SKU</th>
                                            <th class="px-3 py-2.5 text-left">Price (₹)</th>
                                            <th class="px-3 py-2.5 text-left">Stock</th>
                                            <th class="px-3 py-2.5 text-right">Remove</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-100 dark:divide-white/5 bg-white dark:bg-slate-900">
                                        <template x-for="(v, idx) in elecVariants" :key="idx">
                                            <tr>
                                                <td class="px-3 py-2 whitespace-nowrap font-bold">
                                                    <span x-text="v.name"></span>
                                                    <input type="hidden" :name="'variants[' + idx + '][name]'" :value="v.name">
                                                    <input type="hidden" :name="'variants[' + idx + '][size]'" :value="v.size">
                                                </td>
                                                <td class="px-3 py-2 whitespace-nowrap">
                                                    <input type="text" :name="'variants[' + idx + '][sku]'" x-model="v.sku" class="w-32 px-2 py-1 text-xs rounded-lg border border-slate-200 dark:border-white/10 bg-slate-50 dark:bg-slate-950">
                                                </td>
                                                <td class="px-3 py-2 whitespace-nowrap">
                                                    <input type="number" step="0.01" :name="'variants[' + idx + '][price]'" x-model="v.price" class="w-28 px-2 py-1 text-xs font-bold rounded-lg border border-slate-200 dark:border-white/10 bg-slate-50 dark:bg-slate-950">
                                                </td>
                                                <td class="px-3 py-2 whitespace-nowrap">
                                                    <input type="number" :name="'variants[' + idx + '][stock]'" x-model="v.stock" class="w-24 px-2 py-1 text-xs font-bold rounded-lg border border-slate-200 dark:border-white/10 bg-slate-50 dark:bg-slate-950">
                                                </td>
                                                <td class="px-3 py-2 whitespace-nowrap text-right">
                                                    <button type="button" @click="elecVariants.splice(idx, 1)" class="p-1 text-rose-500 hover:text-rose-700 cursor-pointer">✕</button>
                                                </td>
                                            </tr>
                                        </template>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- 5. FOOD & RESTAURANT PORTIONS & ADD-ONS -->
                    <div x-show="activeVariantTab === 'food'" class="space-y-5">
                        <!-- Portions Repeater -->
                        <div class="p-4 rounded-2xl bg-orange-50/50 dark:bg-orange-950/20 border border-orange-200/50 dark:border-orange-800/30">
                            <div class="flex items-center justify-between mb-3">
                                <label class="text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300">
                                    🍽️ 1. Serving Portions (Half / Full / 1 Plate):
                                </label>
                                <button type="button" @click="addFoodPortion('Custom Portion')" class="text-xs font-bold text-orange-600 hover:text-orange-700 cursor-pointer">+ Add Portion</button>
                            </div>

                            <div class="flex flex-wrap gap-2 mb-3">
                                <button type="button" @click="addFoodPortion('Full Portion')" class="px-2.5 py-1 rounded-lg bg-white dark:bg-slate-900 border border-slate-200 dark:border-white/10 text-xs font-semibold cursor-pointer">+ Full Portion</button>
                                <button type="button" @click="addFoodPortion('Half Portion')" class="px-2.5 py-1 rounded-lg bg-white dark:bg-slate-900 border border-slate-200 dark:border-white/10 text-xs font-semibold cursor-pointer">+ Half Portion</button>
                                <button type="button" @click="addFoodPortion('1 Plate (Single)')" class="px-2.5 py-1 rounded-lg bg-white dark:bg-slate-900 border border-slate-200 dark:border-white/10 text-xs font-semibold cursor-pointer">+ 1 Plate</button>
                                <button type="button" @click="addFoodPortion('2 Pieces')" class="px-2.5 py-1 rounded-lg bg-white dark:bg-slate-900 border border-slate-200 dark:border-white/10 text-xs font-semibold cursor-pointer">+ 2 Pcs</button>
                            </div>

                            <div class="space-y-2">
                                <template x-for="(p, pIdx) in foodPortions" :key="pIdx">
                                    <div class="flex items-center gap-2 p-2 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-white/10">
                                        <input type="text" :name="'food_portions[' + pIdx + '][name]'" x-model="p.name" placeholder="Portion Name" class="flex-1 px-3 py-1.5 text-xs font-bold rounded-lg border border-slate-200 dark:border-white/10 bg-slate-50 dark:bg-slate-950">
                                        <div class="flex items-center gap-1">
                                            <span class="text-xs text-slate-400">₹</span>
                                            <input type="number" step="0.01" :name="'food_portions[' + pIdx + '][price]'" x-model="p.price" placeholder="Price" class="w-24 px-2 py-1.5 text-xs font-bold rounded-lg border border-slate-200 dark:border-white/10 bg-slate-50 dark:bg-slate-950">
                                        </div>
                                        <input type="number" :name="'food_portions[' + pIdx + '][stock]'" x-model="p.stock" placeholder="Stock" class="w-20 px-2 py-1.5 text-xs font-bold rounded-lg border border-slate-200 dark:border-white/10 bg-slate-50 dark:bg-slate-950">
                                        <button type="button" @click="foodPortions.splice(pIdx, 1)" class="p-1 text-rose-500 hover:text-rose-700 cursor-pointer">✕</button>
                                    </div>
                                </template>
                            </div>
                        </div>

                        <!-- Add-ons / Modifiers Repeater (Extra Pav, Extra Chole, etc.) -->
                        <div class="p-4 rounded-2xl bg-amber-50/50 dark:bg-amber-950/20 border border-amber-200/50 dark:border-amber-800/30">
                            <div class="flex items-center justify-between mb-3">
                                <label class="text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300">
                                    ➕ 2. Optional Dish Add-ons &amp; Upgrades:
                                </label>
                                <button type="button" @click="addFoodModifier('Extra Add-on', 20)" class="text-xs font-bold text-amber-600 hover:text-amber-700 cursor-pointer">+ Add Custom Add-on</button>
                            </div>

                            <div class="flex flex-wrap gap-2 mb-3">
                                <button type="button" @click="addFoodModifier('Extra Pav', 20)" class="px-2.5 py-1 rounded-lg bg-white dark:bg-slate-900 border border-slate-200 dark:border-white/10 text-xs font-semibold cursor-pointer">+ Extra Pav (+₹20)</button>
                                <button type="button" @click="addFoodModifier('Extra Chole', 40)" class="px-2.5 py-1 rounded-lg bg-white dark:bg-slate-900 border border-slate-200 dark:border-white/10 text-xs font-semibold cursor-pointer">+ Extra Chole (+₹40)</button>
                                <button type="button" @click="addFoodModifier('Extra Cheese Slice', 30)" class="px-2.5 py-1 rounded-lg bg-white dark:bg-slate-900 border border-slate-200 dark:border-white/10 text-xs font-semibold cursor-pointer">+ Extra Cheese (+₹30)</button>
                                <button type="button" @click="addFoodModifier('Extra Butter', 15)" class="px-2.5 py-1 rounded-lg bg-white dark:bg-slate-900 border border-slate-200 dark:border-white/10 text-xs font-semibold cursor-pointer">+ Extra Butter (+₹15)</button>
                                <button type="button" @click="addFoodModifier('Extra Raita', 25)" class="px-2.5 py-1 rounded-lg bg-white dark:bg-slate-900 border border-slate-200 dark:border-white/10 text-xs font-semibold cursor-pointer">+ Extra Raita (+₹25)</button>
                                <button type="button" @click="addFoodModifier('Cold Beverage (Drink)', 40)" class="px-2.5 py-1 rounded-lg bg-white dark:bg-slate-900 border border-slate-200 dark:border-white/10 text-xs font-semibold cursor-pointer">+ Cold Drink (+₹40)</button>
                            </div>

                            <div class="space-y-2">
                                <template x-for="(m, mIdx) in foodModifiers" :key="mIdx">
                                    <div class="flex items-center gap-2 p-2 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-white/10">
                                        <input type="text" :name="'modifiers[' + mIdx + '][group_name]'" x-model="m.group_name" placeholder="Group (e.g. Add-ons)" class="w-32 px-3 py-1.5 text-xs font-medium rounded-lg border border-slate-200 dark:border-white/10 bg-slate-50 dark:bg-slate-950">
                                        <input type="text" :name="'modifiers[' + mIdx + '][modifier_name]'" x-model="m.modifier_name" placeholder="Modifier Name" class="flex-1 px-3 py-1.5 text-xs font-bold rounded-lg border border-slate-200 dark:border-white/10 bg-slate-50 dark:bg-slate-950">
                                        <div class="flex items-center gap-1">
                                            <span class="text-xs text-slate-400">+₹</span>
                                            <input type="number" step="0.01" :name="'modifiers[' + mIdx + '][price_delta]'" x-model="m.price_delta" placeholder="20" class="w-20 px-2 py-1.5 text-xs font-bold rounded-lg border border-slate-200 dark:border-white/10 bg-slate-50 dark:bg-slate-950">
                                        </div>
                                        <label class="flex items-center gap-1 text-[11px] text-slate-600 dark:text-slate-400">
                                            <input type="checkbox" :name="'modifiers[' + mIdx + '][is_required]'" value="1" x-model="m.is_required" class="rounded text-orange-600">
                                            <span>Req</span>
                                        </label>
                                        <button type="button" @click="foodModifiers.splice(mIdx, 1)" class="p-1 text-rose-500 hover:text-rose-700 cursor-pointer">✕</button>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>

                    <!-- 6. MARBLE, MACHINERY & B2B (Enquiry / RFQ Mode) -->
                    <div x-show="activeVariantTab === 'enquiry'" class="space-y-4">
                        <div class="p-5 rounded-2xl bg-blue-50/70 dark:bg-blue-950/30 border border-blue-200/80 dark:border-blue-800/40">
                            <div class="flex items-start justify-between gap-4">
                                <div class="space-y-1">
                                    <div class="flex items-center gap-2">
                                        <span class="text-xl">📋</span>
                                        <h3 class="text-sm font-bold text-blue-950 dark:text-blue-200">
                                            IndiaMart-Style Enquiry / Quotation (RFQ) Mode
                                        </h3>
                                    </div>
                                    <p class="text-xs text-blue-900/80 dark:text-blue-300/80 leading-relaxed">
                                        Marble, granite, heavy machinery, and industrial products require custom discussions. Standard cart checkout is disabled; interested buyers will submit their required quantity, delivery location/state, and project requirements directly to your vendor enquiry inbox.
                                    </p>
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer shrink-0 mt-1">
                                    <input type="checkbox" name="enable_rfq" value="1" x-model="enableRfq" class="sr-only peer">
                                    <div class="w-11 h-6 bg-slate-300 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                                </label>
                            </div>
                        </div>

                        <!-- Technical Machine / Marble Quick Spec Presets -->
                        <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-950/60 border border-slate-200 dark:border-white/10 space-y-3">
                            <span class="text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 block">
                                Quick Spec Presets (Click to insert into technical specs above):
                            </span>
                            <div class="flex flex-wrap gap-2 text-xs">
                                <button type="button" @click="insertSpec('Thickness', '16mm - 18mm Gangsaw Cut Slabs')" class="px-2.5 py-1 rounded-lg bg-white dark:bg-slate-900 border border-slate-200 dark:border-white/10 font-medium hover:border-blue-400 cursor-pointer">+ Marble Thickness (16-18mm)</button>
                                <button type="button" @click="insertSpec('Surface Polish', 'High Gloss Mirror Polish')" class="px-2.5 py-1 rounded-lg bg-white dark:bg-slate-900 border border-slate-200 dark:border-white/10 font-medium hover:border-blue-400 cursor-pointer">+ Mirror Polish</button>
                                <button type="button" @click="insertSpec('Motor Power', '10 HP (7.5 kW) 1440 RPM 3-Phase')" class="px-2.5 py-1 rounded-lg bg-white dark:bg-slate-900 border border-slate-200 dark:border-white/10 font-medium hover:border-blue-400 cursor-pointer">+ Motor Power (10 HP)</button>
                                <button type="button" @click="insertSpec('Operating Voltage', '415V Three Phase 50Hz Industrial')" class="px-2.5 py-1 rounded-lg bg-white dark:bg-slate-900 border border-slate-200 dark:border-white/10 font-medium hover:border-blue-400 cursor-pointer">+ 415V 3-Phase</button>
                                <button type="button" @click="insertSpec('Automation Grade', 'Automatic CNC Control')" class="px-2.5 py-1 rounded-lg bg-white dark:bg-slate-900 border border-slate-200 dark:border-white/10 font-medium hover:border-blue-400 cursor-pointer">+ Automatic CNC</button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 3. Drag & Drop Visual Media Uploader -->
                <div class="p-5 sm:p-7 rounded-3xl bg-white/80 dark:bg-slate-900/80 backdrop-blur-2xl border border-white/40 dark:border-white/10 shadow-xl space-y-4">
                    <div class="flex items-center justify-between pb-3 border-b border-slate-200 dark:border-white/10">
                        <div class="flex items-center gap-2.5">
                            <span class="w-7 h-7 rounded-xl bg-purple-500/10 text-purple-500 flex items-center justify-center font-bold text-xs">3</span>
                            <div>
                                <h2 class="text-base font-bold text-slate-900 dark:text-white">Product Gallery &amp; Photos</h2>
                                <p class="text-xs text-slate-400 mt-0.5">The first photo is your <strong>Primary Cover Image</strong>. Click "Set as Cover" on any photo to change.</p>
                            </div>
                        </div>
                        <span class="text-xs text-slate-500 font-medium">JPG, PNG, WEBP up to 5MB</span>
                    </div>

                    <input type="hidden" name="cover_image_index" value="0">

                    <!-- Dropzone -->
                    <div 
                        @dragover.prevent="dragOver = true" 
                        @dragleave.prevent="dragOver = false" 
                        @drop.prevent="handleDrop($event)"
                        :class="dragOver ? 'border-indigo-500 bg-indigo-500/10' : 'border-dashed border-slate-300 dark:border-white/20 bg-slate-50/50 dark:bg-slate-950/40 hover:border-indigo-400'"
                        class="border-2 rounded-2xl p-7 text-center transition-all cursor-pointer relative"
                        @click="$refs.fileInput.click()"
                    >
                        <input 
                            x-ref="fileInput" 
                            type="file" 
                            name="images[]" 
                            multiple 
                            accept="image/jpeg,image/png,image/webp,image/jpg" 
                            class="hidden" 
                            @change="handleFiles($event)"
                        >
                        <div class="flex flex-col items-center justify-center">
                            <div class="w-12 h-12 rounded-2xl bg-indigo-500/10 text-indigo-500 flex items-center justify-center mb-2.5">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            </div>
                            <p class="text-sm font-bold text-slate-800 dark:text-slate-200">
                                Click to select images or drag and drop here
                            </p>
                            <p class="text-xs text-slate-400 mt-1">
                                First image selected will be Cover. Additional images will show in catalog gallery.
                            </p>
                        </div>
                    </div>

                    <!-- Image Thumbnails Grid Preview with Cover Selector -->
                    <div x-show="previewImages.length > 0" class="grid grid-cols-2 sm:grid-cols-4 gap-3.5 pt-2">
                        <template x-for="(img, idx) in previewImages" :key="idx">
                            <div class="relative rounded-2xl overflow-hidden border-2 shadow-sm group bg-slate-950 flex flex-col transition-all"
                                 :class="idx === 0 ? 'border-amber-500 ring-2 ring-amber-400/40 shadow-md' : 'border-slate-200 dark:border-white/10'">
                                
                                <div class="relative aspect-square w-full overflow-hidden bg-slate-900">
                                    <img :src="img.url" class="w-full h-full object-cover">
                                    
                                    <!-- Badges -->
                                    <div class="absolute top-2 left-2 z-10">
                                        <template x-if="idx === 0">
                                            <span class="inline-flex items-center gap-1 bg-amber-500 text-white text-[10px] font-black px-2 py-0.5 rounded-md shadow uppercase tracking-wider">
                                                ★ Cover
                                            </span>
                                        </template>
                                        <template x-if="idx > 0">
                                            <span class="inline-flex items-center bg-black/75 backdrop-blur-sm text-white text-[10px] font-bold px-2 py-0.5 rounded-md" x-text="'#' + (idx + 1)"></span>
                                        </template>
                                    </div>

                                    <!-- Remove Button -->
                                    <button type="button" 
                                            @click.stop="removeImage(idx)" 
                                            class="absolute top-2 right-2 w-6 h-6 rounded-full bg-rose-600 hover:bg-rose-700 text-white flex items-center justify-center transition-colors shadow z-10 cursor-pointer"
                                            title="Remove Image">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                    </button>
                                </div>

                                <!-- Action Toolbar below thumbnail -->
                                <div class="p-2 bg-white dark:bg-slate-900 flex items-center justify-between border-t border-slate-100 dark:border-white/10 text-xs">
                                    <template x-if="idx === 0">
                                        <span class="text-[10px] font-bold text-amber-600 dark:text-amber-400 uppercase tracking-wider pl-1">Primary Cover</span>
                                    </template>
                                    <template x-if="idx > 0">
                                        <button type="button" 
                                                @click.stop="makeCover(idx)" 
                                                class="text-[10px] font-bold text-amber-700 dark:text-amber-300 bg-amber-50 dark:bg-amber-950/60 hover:bg-amber-100 px-2 py-1 rounded-md transition-colors cursor-pointer border border-amber-200 dark:border-amber-800"
                                                title="Make this photo the main product cover">
                                            Set as Cover
                                        </button>
                                    </template>

                                    <div class="flex items-center gap-1 ml-auto">
                                        <button type="button" 
                                                @click.stop="moveImage(idx, -1)" 
                                                :disabled="idx === 0"
                                                :class="idx === 0 ? 'opacity-25 cursor-not-allowed' : 'hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-300 cursor-pointer'"
                                                class="p-1 rounded transition-colors"
                                                title="Move left">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                                        </button>
                                        <button type="button" 
                                                @click.stop="moveImage(idx, 1)" 
                                                :disabled="idx === previewImages.length - 1"
                                                :class="idx === previewImages.length - 1 ? 'opacity-25 cursor-not-allowed' : 'hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-300 cursor-pointer'"
                                                class="p-1 rounded transition-colors"
                                                title="Move right">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>

                <!-- 3b. Product Video (Optional) -->
                <div class="p-5 sm:p-7 rounded-3xl bg-white/80 dark:bg-slate-900/80 backdrop-blur-2xl border border-white/40 dark:border-white/10 shadow-xl space-y-4">
                    <div class="flex items-center justify-between pb-3 border-b border-slate-200 dark:border-white/10">
                        <div class="flex items-center gap-2.5">
                            <span class="w-7 h-7 rounded-xl bg-rose-500/10 text-rose-500 flex items-center justify-center font-bold text-xs">🎬</span>
                            <h2 class="text-base font-bold text-slate-900 dark:text-white">Product Video <span class="text-xs font-normal text-slate-400 dark:text-slate-500">(Optional)</span></h2>
                        </div>
                        <span class="text-xs text-slate-500 font-medium">MP4, WEBM up to 50MB</span>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <!-- Video Upload -->
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                                Upload Video File
                            </label>
                            <input 
                                type="file" 
                                name="video" 
                                accept="video/mp4,video/webm,video/ogg,video/quicktime"
                                @change="handleVideoFile($event)"
                                class="w-full text-xs text-slate-500 dark:text-slate-400 file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-rose-50 dark:file:bg-rose-950/60 file:text-rose-700 dark:file:text-rose-300 hover:file:bg-rose-100 cursor-pointer"
                            >
                            <p class="text-[11px] text-slate-400 mt-1">Short clips (10-60s) showing the product in motion.</p>
                        </div>

                        <!-- External Video Link -->
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                                Or Video URL
                            </label>
                            <input 
                                type="url" 
                                name="video_url" 
                                x-model="videoUrl"
                                placeholder="https://youtube.com/watch?v=... or direct MP4 link" 
                                class="w-full px-4 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-950/60 border border-slate-200 dark:border-white/10 text-slate-900 dark:text-white text-xs focus:ring-2 focus:ring-rose-500 shadow-inner"
                            >
                            <p class="text-[11px] text-slate-400 mt-1">Direct video link or YouTube embed link.</p>
                        </div>
                    </div>

                    <!-- Video Preview if loaded -->
                    <div x-show="videoPreviewUrl" class="mt-3 rounded-2xl overflow-hidden border border-rose-200/50 bg-black max-w-md">
                        <video :src="videoPreviewUrl" controls class="w-full max-h-56 object-contain"></video>
                        <div class="p-2 bg-slate-900 flex items-center justify-between text-xs text-white">
                            <span class="truncate">Video Preview</span>
                            <button type="button" @click="clearVideo()" class="text-rose-400 hover:text-rose-300 font-bold cursor-pointer">Remove Video</button>
                        </div>
                    </div>
                </div>

            </div>

            <!-- RIGHT 1 COLUMN: Pricing, Tax, Stock & Status -->
            <div class="space-y-6">

                <!-- 4. Pricing & GST -->
                <div class="p-5 sm:p-7 rounded-3xl bg-white/80 dark:bg-slate-900/80 backdrop-blur-2xl border border-white/40 dark:border-white/10 shadow-xl space-y-4">
                    <div class="flex items-center gap-2.5 pb-3 border-b border-slate-200 dark:border-white/10">
                        <span class="w-7 h-7 rounded-xl bg-emerald-500/10 text-emerald-500 flex items-center justify-center font-bold text-xs">4</span>
                        <h2 class="text-base font-bold text-slate-900 dark:text-white">Pricing &amp; Taxation</h2>
                    </div>

                    <!-- Base Selling Price -->
                    <div>
                        <label for="p-price" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                            {{ $sec['price_label'] ?? 'Selling Price (₹)' }} <span class="text-rose-500">*</span>
                        </label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-slate-400 font-bold text-sm">₹</span>
                            <input 
                                id="p-price" 
                                name="base_price" 
                                type="number" 
                                step="0.01" 
                                min="0" 
                                required 
                                x-model="form.base_price" 
                                placeholder="{{ $sec['price_placeholder'] ?? '499.00' }}" 
                                class="w-full pl-8 pr-4 py-3 rounded-xl bg-slate-50 dark:bg-slate-950/60 border border-slate-200 dark:border-white/10 text-slate-900 dark:text-white text-base font-bold focus:ring-2 focus:ring-emerald-500 shadow-inner"
                            >
                        </div>
                    </div>

                    <!-- MRP / Compare-at Price -->
                    <div>
                        <label for="p-compare" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                            Original MRP / Standard Rate (₹) <span class="text-slate-400 font-normal">(Optional)</span>
                        </label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-slate-400 font-bold text-sm">₹</span>
                            <input 
                                id="p-compare" 
                                name="compare_at_price" 
                                type="number" 
                                step="0.01" 
                                min="0" 
                                x-model="form.compare_at_price" 
                                placeholder="999.00" 
                                class="w-full pl-8 pr-4 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-950/60 border border-slate-200 dark:border-white/10 text-slate-900 dark:text-white text-sm focus:ring-2 focus:ring-emerald-500 shadow-inner"
                            >
                        </div>
                    </div>

                    <!-- GST Tax Rate -->
                    <div>
                        <label for="p-gst" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                            Applicable GST Rate <span class="text-rose-500">*</span>
                        </label>
                        <select 
                            id="p-gst" 
                            name="gst_rate" 
                            required 
                            x-model="form.gst_rate" 
                            class="w-full px-4 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-950/60 border border-slate-200 dark:border-white/10 text-slate-900 dark:text-white text-sm focus:ring-2 focus:ring-emerald-500 shadow-inner font-semibold"
                        >
                            <option value="18">18% GST (Standard Electronics, Tech &amp; Services)</option>
                            <option value="5">5% GST (Real Estate, Apparel, Food &amp; Essentials)</option>
                            <option value="0">0% (Nil / Exempt / Second-Hand)</option>
                            <option value="12">12% GST (Processed Items / Hardware / Sports)</option>
                            <option value="28">28% GST (Luxury, Auto &amp; Heavy Spares)</option>
                        </select>
                    </div>

                    <!-- HSN Code -->
                    <div>
                        <label for="p-hsn" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                            HSN / SAC Code
                        </label>
                        <input 
                            id="p-hsn" 
                            name="hsn_code" 
                            type="text" 
                            maxlength="8" 
                            x-model="form.hsn_code" 
                            placeholder="e.g. 85171300 / 998311" 
                            class="w-full px-4 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-950/60 border border-slate-200 dark:border-white/10 text-slate-900 dark:text-white text-xs font-mono focus:ring-2 focus:ring-emerald-500 shadow-inner"
                        >
                    </div>
                </div>

                <!-- 5. Inventory & Shipping -->
                <div class="p-5 sm:p-7 rounded-3xl bg-white/80 dark:bg-slate-900/80 backdrop-blur-2xl border border-white/40 dark:border-white/10 shadow-xl space-y-4">
                    <div class="flex items-center gap-2.5 pb-3 border-b border-slate-200 dark:border-white/10">
                        <span class="w-7 h-7 rounded-xl bg-blue-500/10 text-blue-500 flex items-center justify-center font-bold text-xs">5</span>
                        <h2 class="text-base font-bold text-slate-900 dark:text-white">Inventory &amp; Specs</h2>
                    </div>

                    <!-- Total Stock Quantity -->
                    <div>
                        <label for="p-stock" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                            {{ $sec['stock_label'] ?? 'Available Stock Units' }} <span class="text-rose-500">*</span>
                        </label>
                        <input 
                            id="p-stock" 
                            name="total_stock" 
                            type="number" 
                            min="0" 
                            required 
                            x-model="form.total_stock" 
                            placeholder="50" 
                            class="w-full px-4 py-3 rounded-xl bg-slate-50 dark:bg-slate-950/60 border border-slate-200 dark:border-white/10 text-slate-900 dark:text-white text-base font-bold focus:ring-2 focus:ring-blue-500 shadow-inner"
                        >
                    </div>

                    <!-- SKU -->
                    <div>
                        <label for="p-sku" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                            SKU / Identifier Code
                        </label>
                        <input 
                            id="p-sku" 
                            name="sku" 
                            type="text" 
                            x-model="form.sku" 
                            placeholder="e.g. K71-CAT-001" 
                            class="w-full px-4 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-950/60 border border-slate-200 dark:border-white/10 text-slate-900 dark:text-white text-xs font-mono focus:ring-2 focus:ring-blue-500 shadow-inner"
                        >
                    </div>

                    <!-- Weight in Grams / Dimension Unit -->
                    <div>
                        <label for="p-weight" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                            {{ $sec['unit_label'] ?? 'Package Weight (Grams)' }}
                        </label>
                        <input 
                            id="p-weight" 
                            name="weight_grams" 
                            type="number" 
                            min="0" 
                            x-model="form.weight_grams" 
                            placeholder="450" 
                            class="w-full px-4 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-950/60 border border-slate-200 dark:border-white/10 text-slate-900 dark:text-white text-xs focus:ring-2 focus:ring-blue-500 shadow-inner"
                        >
                    </div>

                    <!-- Returns Policy -->
                    <div class="pt-2 border-t border-slate-200 dark:border-white/10">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="is_returnable" value="1" x-model="form.is_returnable" class="w-4 h-4 rounded text-indigo-600 focus:ring-indigo-500">
                            <span class="text-xs font-semibold text-slate-800 dark:text-slate-200">Customer Returns Accepted</span>
                        </label>
                    </div>
                </div>

                <!-- 6. Listing Status Toggle -->
                <div class="p-5 rounded-2xl bg-white/80 dark:bg-slate-900/80 backdrop-blur-xl border border-white/40 dark:border-white/10 shadow-lg flex items-center justify-between">
                    <div>
                        <span class="text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 block">Catalog Visibility</span>
                        <span class="text-[11px] text-slate-400" x-text="form.status === 'active' ? 'Live on storefront immediately' : 'Saved privately as draft'"></span>
                    </div>
                    <button 
                        type="button" 
                        @click="form.status = (form.status === 'active' ? 'draft' : 'active')"
                        :class="form.status === 'active' ? 'bg-emerald-500 text-white' : 'bg-slate-200 dark:bg-slate-800 text-slate-600 dark:text-slate-400'"
                        class="px-3.5 py-1.5 rounded-xl font-bold text-xs uppercase tracking-wider transition-colors shadow"
                    >
                        <span x-text="form.status === 'active' ? 'Active' : 'Draft'"></span>
                    </button>
                </div>

                <!-- Submit Button Glass Card -->
                <button 
                    type="button" 
                    @click="submitForm()" 
                    :disabled="submitting" 
                    class="w-full py-4 px-6 rounded-2xl bg-gradient-to-r from-indigo-500 via-purple-600 to-cyan-500 hover:from-indigo-400 hover:to-cyan-400 text-white font-black text-sm tracking-wide shadow-xl shadow-indigo-500/30 active:scale-[0.98] transition-all flex items-center justify-center gap-2 cursor-pointer"
                >
                    <svg x-show="!submitting" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                    <svg x-show="submitting" x-cloak class="animate-spin w-5 h-5 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                    <span x-text="submitting ? 'Publishing Product...' : 'Launch Product to Store'"></span>
                </button>

            </div>

        </div>
    </form>
</div>

<script>
function liquidProductUploader() {
    return {
        submitting: false,
        dragOver: false,
        appliedPreset: null,
        previewImages: [],
        videoPreviewUrl: '',
        videoUrl: '',
        activeVariantTab: '{{ in_array($vendorType, ["clothing"]) ? "clothing" : (in_array($vendorType, ["footwear"]) ? "footwear" : (in_array($vendorType, ["kirana", "groceries"]) ? "kirana" : (in_array($vendorType, ["electronics"]) ? "electronics" : (in_array($vendorType, ["food"]) ? "food" : (in_array($vendorType, ["marble", "machinery", "b2b"]) ? "enquiry" : "clothing"))))) }}',

        // 👗 Clothing Variations State (Default Empty - No Auto-Selection)
        selectedSizes: [],
        availableSizes: ['XS', 'S', 'M', 'L', 'XL', '2XL', '3XL', 'Free Size'],
        selectedColors: [],
        availableColors: [
            {name: 'Black', hex: '#000000'},
            {name: 'White', hex: '#FFFFFF'},
            {name: 'Navy Blue', hex: '#0B192C'},
            {name: 'Red', hex: '#E53E3E'},
            {name: 'Beige', hex: '#D4C4B5'},
            {name: 'Grey', hex: '#718096'},
            {name: 'Olive Green', hex: '#556B2F'},
            {name: 'Maroon', hex: '#800000'}
        ],
        customSize: '',
        customColorName: '',
        customColorHex: '#111827',
        variants: [],
        bulkPrice: '',
        bulkStock: '',

        // 👟 Footwear Variations State (Default Empty - No Auto-Selection)
        selectedShoeSizes: [],
        availableShoeSizes: ['UK 5', 'UK 6', 'UK 7', 'UK 8', 'UK 9', 'UK 10', 'UK 11', 'UK 12'],
        selectedShoeColors: [],
        availableShoeColors: [
            {name: 'Black', hex: '#000000'},
            {name: 'Brown', hex: '#8B4513'},
            {name: 'White', hex: '#FFFFFF'},
            {name: 'Tan', hex: '#D2B48C'},
            {name: 'Grey', hex: '#808080'},
            {name: 'Navy', hex: '#000080'}
        ],
        footwearVariants: [],
        bulkShoePrice: '',
        bulkShoeStock: '',

        // 🛒 Kirana & Grocery Pack Units State (D-Mart Style)
        availableGroceryUnits: ['100 g', '250 g', '500 g', '1 kg', '2 kg', '5 kg', '10 kg', '1 Packet', '1 Bottle', '1 Piece', 'Combo Pack'],
        groceryVariants: [],

        // 📱 Electronics & Mobiles Specs State (Default Empty - No Auto-Selection)
        availableRams: ['4 GB', '6 GB', '8 GB', '12 GB', '16 GB', '32 GB'],
        selectedRams: [],
        availableStorages: ['64 GB', '128 GB', '256 GB', '512 GB', '1 TB'],
        selectedStorages: [],
        elecVariants: [],

        // 🍽️ Food Portions & Modifiers State (Default Empty - No Auto-Selection)
        foodPortions: [],
        foodModifiers: [],

        // 📋 Marble / Machinery RFQ Mode
        enableRfq: {{ in_array($vendorType, ['marble', 'machinery', 'b2b']) ? 'true' : 'false' }},

        form: {
            title: '{{ old('title', '') }}',
            category_id: '{{ old('category_id', $categories->first()?->id ?? '') }}',
            vertical: '{{ $targetVertical }}',
            listing_type: '{{ in_array($vendorType, ['real_estate', 'machinery', 'b2b']) ? 'enquiry' : ($vendorType === 'services' ? 'booking' : ($vendorType === 'used' ? 'contact_seller' : 'purchase')) }}',
            brand: '{{ old('brand', '') }}',
            description: '{{ old('description', '') }}',
            base_price: '{{ old('base_price', '') }}',
            compare_at_price: '{{ old('compare_at_price', '') }}',
            gst_rate: '{{ old('gst_rate', $sec['gst_default'] ?? '18') }}',
            hsn_code: '{{ old('hsn_code', '') }}',
            total_stock: '{{ old('total_stock', '25') }}',
            sku: '{{ old('sku', '') }}',
            weight_grams: '{{ old('weight_grams', '500') }}',
            is_returnable: true,
            status: 'active',
            specs: @json(old('specifications', $sec['default_specs'] ?? [['key' => 'Specification', 'value' => 'Standard']]))
        },

        // Fast Presets by Sector
        presets: {
            real_estate: [
                {
                    icon: '🏢',
                    title: '3 BHK Luxury Gated High-Rise Apartment',
                    brand: 'DLF Regal Gardens / Godrej',
                    base_price: '8500000.00',
                    compare_at_price: '9200000.00',
                    gst_rate: '5',
                    hsn_code: '997211',
                    total_stock: '4',
                    sku: 'PROP-3BHK-801',
                    description: 'Sunlit East-facing 3 BHK luxury apartment on 8th floor with wrap-around balconies, modular German kitchen, clubhouse access, swimming pool, and 2 covered car parkings.',
                    specs: [
                        { key: 'Carpet Area', value: '1,650 Sq.Ft (Super Built-up 2,100 Sq.Ft)' },
                        { key: 'Configuration', value: '3 Bedrooms + 3 Bathrooms + 2 Balconies' },
                        { key: 'Furnishing Status', value: 'Semi-Furnished with Modular Kitchen & Wardrobes' },
                        { key: 'Facing & Floor', value: 'North-East Vastu Compliant • 8th Floor of 14' },
                        { key: 'Possession Status', value: 'Ready to Move In (OC & CC Received)' },
                        { key: 'RERA Registration No', value: 'RAJ/RERA/P/2023/1089' },
                        { key: 'Reserved Parking', value: '2 Covered Basement Slots' },
                    ]
                },
                {
                    icon: '🏡',
                    title: 'Independent 4 BHK Duplex Villa with Garden',
                    brand: 'Prestige Royal Woods / Local Builder',
                    base_price: '14500000.00',
                    compare_at_price: '16000000.00',
                    gst_rate: '5',
                    hsn_code: '997212',
                    total_stock: '2',
                    sku: 'PROP-VILLA-04',
                    description: 'Architect-designed independent 4 BHK villa with private front garden, Italian marble flooring throughout, double-height ceiling living hall, and private rooftop deck.',
                    specs: [
                        { key: 'Plot Area', value: '220 Sq.Yards (Built-up Area 3,200 Sq.Ft)' },
                        { key: 'Configuration', value: '4 BHK Duplex (4 Master Bedrooms with En-suite)' },
                        { key: 'Flooring', value: 'Pure Imported Italian Botticino Marble' },
                        { key: 'Private Amenities', value: 'Private Garden Lawn & Rooftop Terrace Deck' },
                        { key: 'Possession Status', value: 'Ready to Move In • Freehold Registry 90A' },
                    ]
                },
                {
                    icon: '🏬',
                    title: 'Commercial Corner Showroom on Main Road',
                    brand: 'City Center Plaza',
                    base_price: '6200000.00',
                    compare_at_price: '7000000.00',
                    gst_rate: '12',
                    hsn_code: '997212',
                    total_stock: '1',
                    sku: 'COMM-SHOP-G02',
                    description: 'Prime ground-floor corner commercial shop with massive 28-foot glass facade visibility directly facing main 100-foot arterial road. Ideal for jewelry, retail fashion, banking, or clinic.',
                    specs: [
                        { key: 'Carpet Area', value: '850 Sq.Ft (Clear 14ft Ceiling Height)' },
                        { key: 'Floor Position', value: 'Ground Floor Corner Double-Facing' },
                        { key: 'Frontage', value: '28 Feet Toughened Glass Display' },
                        { key: 'Power Backup', value: '100% DG Genset Backup Available' },
                        { key: 'Approvals', value: 'Commercial Sanctioned Map • Fire NOC Cleared' },
                    ]
                },
                {
                    icon: '📐',
                    title: 'Prime Gated Township Residential Plot',
                    brand: 'Royal Palms Enclave',
                    base_price: '3250000.00',
                    compare_at_price: '3600000.00',
                    gst_rate: '0',
                    hsn_code: '997211',
                    total_stock: '6',
                    sku: 'PLOT-200YD-E',
                    description: 'East-facing rectangular residential plot located inside premium gated township with 40-foot wide asphalt roads, underground electrification, rainwater harvesting, and landscaped park.',
                    specs: [
                        { key: 'Plot Dimensions', value: '30 ft Frontage x 60 ft Depth (200 Sq.Yards)' },
                        { key: 'Facing', value: 'East Facing Vastu Approved' },
                        { key: 'Road Width', value: '40 Feet Wide Road Frontage' },
                        { key: 'Legal Status', value: 'JDA / Municipal Approved 90A Freehold Patta' },
                    ]
                },
                {
                    icon: '🏭',
                    title: 'Industrial Factory Shed with Crane Girders',
                    brand: 'RIICO Industrial Zone',
                    base_price: '21000000.00',
                    compare_at_price: '23500000.00',
                    gst_rate: '18',
                    hsn_code: '997212',
                    total_stock: '1',
                    sku: 'IND-SHED-15K',
                    description: 'Heavy industrial factory setup on 15,000 sq ft land with 10,000 sq ft PEB shed, 10-Ton EOT crane gantry girders installed, 75 HP dedicated HT electric connection, and trailer turnaround space.',
                    specs: [
                        { key: 'Total Land Area', value: '15,000 Sq.Ft (Shed Area: 10,000 Sq.Ft)' },
                        { key: 'Clear Height', value: '28 Feet Center / 22 Feet Eaves' },
                        { key: 'Crane Facility', value: 'Provision for 10-Ton Overhead EOT Crane' },
                        { key: 'Power Sanctioned', value: '75 HP 3-Phase Industrial Power Connection' },
                        { key: 'Water & Waste', value: 'Borewell + Industrial Effluent Line Connection' },
                    ]
                },
                {
                    icon: '🔑',
                    title: 'Fully Furnished 2 BHK Rental Flat in Civil Lines',
                    brand: 'Silver Oak Residences',
                    base_price: '22000.00',
                    compare_at_price: '25000.00',
                    gst_rate: '0',
                    hsn_code: '997211',
                    total_stock: '2',
                    sku: 'RENT-2BHK-302',
                    description: 'Ready-to-move fully furnished 2 BHK apartment featuring dual inverter ACs, Smart LED TV, double-door refrigerator, automatic washing machine, and complete cutlery. Family or working pros preferred.',
                    specs: [
                        { key: 'Monthly Rent', value: '₹22,000 / Month (Maintenance ₹2,000 Included)' },
                        { key: 'Security Deposit', value: '2 Months Rent (Refundable)' },
                        { key: 'Carpet Area', value: '1,100 Sq.Ft (2 Bed + 2 Bath + Hall + Balcony)' },
                        { key: 'Furnishing Inclusions', value: 'Sofa, 2 King Beds, 2 ACs, Fridge, RO & Washing Machine' },
                    ]
                }
            ],
            automotive: [
                {
                    icon: '🛢️',
                    title: 'Synthetic Engine Oil 5W-40 (4 Litres)',
                    brand: 'Apex Lube',
                    base_price: '2499.00',
                    compare_at_price: '3200.00',
                    gst_rate: '18',
                    hsn_code: '27101980',
                    total_stock: '45',
                    sku: 'OIL-5W40-4L',
                    description: 'High-performance fully synthetic multi-grade engine oil designed for modern turbocharged petrol and diesel engines.',
                    specs: [
                        { key: 'Viscosity', value: '5W-40' },
                        { key: 'Volume', value: '4 Litres' },
                        { key: 'API Standard', value: 'API SP / ACEA A3/B4' },
                    ]
                },
                {
                    icon: '🛑',
                    title: 'Front Ceramic Brake Pads (Set)',
                    brand: 'BremTech',
                    base_price: '1699.00',
                    compare_at_price: '2200.00',
                    gst_rate: '18',
                    hsn_code: '87083000',
                    total_stock: '30',
                    sku: 'BRAKE-CER-01',
                    description: 'Low-dust, high-temperature ceramic front disc brake pad set with pre-attached anti-squeal shims.',
                    specs: [
                        { key: 'Material', value: 'Non-Asbestos Ceramic Compound' },
                        { key: 'Position', value: 'Front Axle' },
                        { key: 'Warranty', value: '1 Year / 20,000 KM' },
                    ]
                },
                {
                    icon: '💡',
                    title: 'High-Lumen LED Headlight H7/H4 Kit',
                    brand: 'LumenMax',
                    base_price: '2899.00',
                    compare_at_price: '3999.00',
                    gst_rate: '18',
                    hsn_code: '85122010',
                    total_stock: '25',
                    sku: 'LED-H7-16000LM',
                    description: 'High-intensity 16,000 Lumens plug-and-play LED headlight bulb conversion kit with 6000K crisp white beam and active cooling fan.',
                    specs: [
                        { key: 'Luminance', value: '16,000 Lumens (8,000 LM per bulb)' },
                        { key: 'Color Temp', value: '6000K Cool Diamond White' },
                        { key: 'Wattage', value: '72W per Pair (36W each)' },
                        { key: 'Waterproof Rating', value: 'IP68 Submersible' },
                    ]
                },
                {
                    icon: '🚗',
                    title: 'All-Weather Radial Tubeless Tyre 195/65 R15',
                    brand: 'Apex Grip',
                    base_price: '4250.00',
                    compare_at_price: '5100.00',
                    gst_rate: '28',
                    hsn_code: '40111010',
                    total_stock: '40',
                    sku: 'TYRE-195-65-R15',
                    description: 'Low rolling-resistance radial tubeless car tyre offering ultra-quiet ride, high wet-grip siping, and extended tread life.',
                    specs: [
                        { key: 'Tyre Size', value: '195/65 R15' },
                        { key: 'Speed Rating', value: 'H (Up to 210 km/h)' },
                        { key: 'Tread Warranty', value: '5 Years Unconditional Manufacturer Cover' },
                    ]
                },
                {
                    icon: '🔋',
                    title: 'Sealed Maintenance-Free Car Battery 45Ah',
                    brand: 'PowerCell',
                    base_price: '4899.00',
                    compare_at_price: '5800.00',
                    gst_rate: '28',
                    hsn_code: '85071000',
                    total_stock: '20',
                    sku: 'BATT-45AH-L',
                    description: 'Heavy-duty silver-calcium alloy battery with high cold-cranking performance and zero maintenance requirements.',
                    specs: [
                        { key: 'Capacity', value: '45 Ah' },
                        { key: 'Cold Cranking Amps', value: '420 CCA' },
                        { key: 'Warranty', value: '55 Months (30 Months Free + 25 Pro-rata)' },
                    ]
                },
                {
                    icon: '🏍️',
                    title: 'Motorcycle High-Tack Chain Lube & Cleaner Kit',
                    brand: 'RiderPro',
                    base_price: '699.00',
                    compare_at_price: '999.00',
                    gst_rate: '18',
                    hsn_code: '34031900',
                    total_stock: '60',
                    sku: 'LUBE-COMBO-01',
                    description: 'Dual can set including powerful degreasing chain cleaner spray (400ml) and synthetic PTFE high-adhesion anti-fling chain lube (400ml).',
                    specs: [
                        { key: 'Pack Contents', value: '400ml Cleaner + 400ml Synthetic Lube' },
                        { key: 'Compatibility', value: 'Safe on O-Ring, X-Ring and Z-Ring Chains' },
                    ]
                }
            ],
            clothing: [
                {
                    icon: '👕',
                    title: 'Heavyweight 240 GSM Oversized Cotton T-Shirt',
                    brand: 'UrbanCraft',
                    base_price: '899.00',
                    compare_at_price: '1499.00',
                    gst_rate: '5',
                    hsn_code: '61091000',
                    total_stock: '60',
                    sku: 'TEE-240GSM-BLK',
                    description: 'Relaxed oversized fit streetwear t-shirt crafted from 100% combed ring-spun cotton with bio-washed soft finish.',
                    specs: [
                        { key: 'Fabric', value: '100% Combed Cotton (240 GSM)' },
                        { key: 'Fit', value: 'Relaxed Oversized Fit' },
                        { key: 'Wash Care', value: 'Machine wash cold, inside out' },
                    ]
                },
                {
                    icon: '🥻',
                    title: 'Handloom Pure Banarasi Katan Silk Saree',
                    brand: 'Varanasi Weaves',
                    base_price: '4999.00',
                    compare_at_price: '7999.00',
                    gst_rate: '5',
                    hsn_code: '50072010',
                    total_stock: '15',
                    sku: 'SAREE-KATAN-MAR',
                    description: 'Master weaver handcrafted Banarasi Katan silk saree featuring intricate Kadwa gold zari floral jaal with unstitched blouse piece.',
                    specs: [
                        { key: 'Fabric', value: 'Pure Katan Silk with Metallic Gold Zari' },
                        { key: 'Length', value: '5.5 Meters Saree + 0.8 Meter Blouse' },
                        { key: 'Occasion', value: 'Bridal, Wedding Guest, Festive Ceremony' },
                    ]
                },
                {
                    icon: '👖',
                    title: 'Slim Straight Stretch Denim Jeans',
                    brand: 'DenimWorks',
                    base_price: '1599.00',
                    compare_at_price: '2499.00',
                    gst_rate: '5',
                    hsn_code: '62034200',
                    total_stock: '45',
                    sku: 'JEAN-STR-IND',
                    description: 'Premium 12 Oz comfort-stretch cotton denim jeans in deep indigo wash with brass rivets and reinforced bar-tack stitching.',
                    specs: [
                        { key: 'Composition', value: '98% Cotton / 2% Spandex (12 Oz)' },
                        { key: 'Fit', value: 'Slim Straight Mid-Rise' },
                    ]
                },
                {
                    icon: '👗',
                    title: 'Pure Chanderi Embroidered Kurta Pant Set',
                    brand: 'Riti Riwaaz',
                    base_price: '2499.00',
                    compare_at_price: '3499.00',
                    gst_rate: '5',
                    hsn_code: '62044220',
                    total_stock: '25',
                    sku: 'KURTA-CHAN-LAV',
                    description: 'Elegantly tailored pastel lavender Chanderi kurta set with handcrafted mirror and gotta patti work, paired with cigarette pants and organza dupatta.',
                    specs: [
                        { key: 'Set Inclusions', value: 'Kurta + Cigarette Pants + Organza Dupatta' },
                        { key: 'Fabric', value: 'Chanderi Silk with Mulmul Cotton Lining' },
                    ]
                },
                {
                    icon: '👟',
                    title: 'Ultra-Light Breathable Mesh Running Shoes',
                    brand: 'AeroStep',
                    base_price: '1799.00',
                    compare_at_price: '2699.00',
                    gst_rate: '12',
                    hsn_code: '64041100',
                    total_stock: '50',
                    sku: 'SHOE-AERO-01',
                    description: 'Lightweight ergonomic running shoes with breathable jacquard knit upper, high-rebound EVA phylon foam midsole, and anti-skid rubber grip.',
                    specs: [
                        { key: 'Upper Material', value: 'Engineered Jacquard Knit' },
                        { key: 'Sole Material', value: 'Shock-Absorbing Phylon EVA Midsole' },
                        { key: 'Weight', value: '260g per shoe' },
                    ]
                },
                {
                    icon: '👜',
                    title: 'Structured Vegan Leather Laptop Tote Bag',
                    brand: 'LuxeCarry',
                    base_price: '1899.00',
                    compare_at_price: '2799.00',
                    gst_rate: '12',
                    hsn_code: '42022290',
                    total_stock: '30',
                    sku: 'BAG-TOTE-BRN',
                    description: 'Sophisticated dual-tone vegan leather structured tote with padded 14" laptop partition, multiple interior organizer sleeves, and brass zipper.',
                    specs: [
                        { key: 'Material', value: 'Scratch-Proof PU Vegan Leather' },
                        { key: 'Capacity', value: 'Fits up to 14.2" Laptop + Tablet + Daily Essentials' },
                    ]
                }
            ],
            electronics: [
                {
                    icon: '📱',
                    title: '5G Smartphone 256GB Flagship',
                    brand: 'Apex Mobile',
                    base_price: '29999.00',
                    compare_at_price: '34999.00',
                    gst_rate: '18',
                    hsn_code: '85171300',
                    total_stock: '30',
                    sku: 'PHONE-5G-256',
                    description: 'Flagship 5G smartphone with 6.7" 120Hz Super AMOLED display, 50MP OIS triple camera, 5000mAh battery, and 67W Turbo Flash charging.',
                    specs: [
                        { key: 'RAM & Storage', value: '8GB RAM + 256GB Storage' },
                        { key: 'Processor', value: 'Octa-core 5G Chipset' },
                        { key: 'Display', value: '6.7-inch 120Hz AMOLED HDR10+' },
                        { key: 'Battery', value: '5000mAh with 67W Charger' },
                        { key: 'Warranty', value: '1 Year Brand Warranty' },
                    ]
                },
                {
                    icon: '💻',
                    title: 'Ultra-Slim 14" Laptop 16GB/512GB',
                    brand: 'NovaTech',
                    base_price: '54999.00',
                    compare_at_price: '64999.00',
                    gst_rate: '18',
                    hsn_code: '84713010',
                    total_stock: '15',
                    sku: 'LAP-14-16GB',
                    description: 'Ultra-portable aluminum body laptop with high-speed NVMe SSD, backlit keyboard, Wi-Fi 6E, and all-day 12-hour battery life.',
                    specs: [
                        { key: 'Processor', value: 'Core i7 / Ryzen 7 Multi-core' },
                        { key: 'RAM', value: '16GB LPDDR5X' },
                        { key: 'Storage', value: '512GB PCIe 4.0 NVMe SSD' },
                        { key: 'Display', value: '14" 2.8K IPS Anti-Glare' },
                        { key: 'Weight', value: '1.24 kg' },
                    ]
                },
                {
                    icon: '🎧',
                    title: 'Wireless Active Noise-Cancelling TWS',
                    brand: 'SonicWave',
                    base_price: '3499.00',
                    compare_at_price: '4999.00',
                    gst_rate: '18',
                    hsn_code: '85183000',
                    total_stock: '50',
                    sku: 'TWS-ANC-01',
                    description: 'True Wireless Stereo earbuds with 42dB hybrid Active Noise Cancellation, dual microphones for crystal-clear calls, and 36-hour battery case.',
                    specs: [
                        { key: 'Bluetooth', value: 'v5.3 Low Latency' },
                        { key: 'Noise Cancellation', value: '42dB Hybrid ANC' },
                        { key: 'Battery Life', value: '8h buds + 28h charging case' },
                        { key: 'Water Resistance', value: 'IPX5 Sweat & Splash Proof' },
                    ]
                },
                {
                    icon: '🔌',
                    title: '65W GaN Fast Wall Charger',
                    brand: 'VoltGrid',
                    base_price: '1899.00',
                    compare_at_price: '2499.00',
                    gst_rate: '18',
                    hsn_code: '85044090',
                    total_stock: '100',
                    sku: 'GAN-65W-BLK',
                    description: 'Compact Gallium Nitride (GaN) fast charger with dual USB-C Power Delivery ports and one USB-A port for laptop, tablet, and phone.',
                    specs: [
                        { key: 'Total Output', value: '65W Max Power Delivery' },
                        { key: 'Ports', value: '2x USB-C + 1x USB-A' },
                        { key: 'Technology', value: 'GaN III Semiconductor' },
                    ]
                },
                {
                    icon: '⌚',
                    title: 'Smartwatch with AMOLED & BT Calling',
                    brand: 'Krono',
                    base_price: '4499.00',
                    compare_at_price: '5999.00',
                    gst_rate: '18',
                    hsn_code: '85176290',
                    total_stock: '40',
                    sku: 'WATCH-AMOLED-01',
                    description: 'Sleek metal casing smartwatch featuring 1.43" always-on AMOLED display, heart rate, blood oxygen (SpO2), sleep tracker, and voice calling.',
                    specs: [
                        { key: 'Display', value: '1.43" Always-On AMOLED' },
                        { key: 'Battery', value: 'Up to 10 Days Typical Use' },
                        { key: 'Waterproof', value: 'IP68 Certified' },
                    ]
                },
                {
                    icon: '🖥️',
                    title: '27" 4K IPS Gaming Monitor 144Hz',
                    brand: 'SpectraView',
                    base_price: '24999.00',
                    compare_at_price: '29999.00',
                    gst_rate: '18',
                    hsn_code: '85285200',
                    total_stock: '20',
                    sku: 'MON-27-4K-144',
                    description: '27-inch UHD 3840x2160 IPS panel, 1ms response time, 144Hz refresh rate, AMD FreeSync Premium, HDR400, and HDMI 2.1 support.',
                    specs: [
                        { key: 'Resolution', value: '3840 x 2160 (4K UHD)' },
                        { key: 'Refresh Rate', value: '144Hz' },
                        { key: 'Panel Type', value: 'Fast IPS with 99% sRGB' },
                        { key: 'Ports', value: '2x HDMI 2.1, 1x DP 1.4, Audio Out' },
                    ]
                }
            ],
            beauty: [
                {
                    icon: '💧',
                    title: '10% Niacinamide & Zinc Clarifying Face Serum',
                    brand: 'GlowRx',
                    base_price: '599.00',
                    compare_at_price: '849.00',
                    gst_rate: '18',
                    hsn_code: '33049910',
                    total_stock: '80',
                    sku: 'SERUM-NIAC-30ML',
                    description: 'Clean clinical formulation face serum with 10% pure Niacinamide and 1% Zinc PCA to balance sebum, minimize pores, and fade blemishes.',
                    specs: [
                        { key: 'Active Ingredients', value: '10% Niacinamide + 1% Zinc PCA' },
                        { key: 'Skin Type', value: 'All Skin Types (Acne & Blemish Prone)' },
                        { key: 'Net Volume', value: '30 ml' },
                    ]
                },
                {
                    icon: '☀️',
                    title: 'SPF 50+ PA++++ Broad Spectrum Sunscreen Gel',
                    brand: 'DermaShield',
                    base_price: '499.00',
                    compare_at_price: '699.00',
                    gst_rate: '18',
                    hsn_code: '33049910',
                    total_stock: '100',
                    sku: 'SUN-SPF50-50G',
                    description: 'Ultra-light water gel sunscreen with zero white cast, certified SPF 50+ PA++++ protection, enriched with Hyaluronic Acid and Cica extract.',
                    specs: [
                        { key: 'Protection Factor', value: 'SPF 50+ Broad Spectrum PA++++' },
                        { key: 'Texture', value: 'Ultra-Light Non-Greasy Water Gel' },
                        { key: 'Net Weight', value: '50 Grams' },
                    ]
                },
                {
                    icon: '🌿',
                    title: '100% Cold-Pressed Organic Rosemary Hair Oil',
                    brand: 'HerbBotanica',
                    base_price: '449.00',
                    compare_at_price: '599.00',
                    gst_rate: '18',
                    hsn_code: '33059011',
                    total_stock: '60',
                    sku: 'OIL-ROSE-100ML',
                    description: 'Nourishing botanical hair elixir infused with cold-pressed rosemary leaf extract, golden jojoba, and amla to strengthen roots and promote thick hair growth.',
                    specs: [
                        { key: 'Extraction Method', value: 'Cold-Pressed Extraction (Unrefined)' },
                        { key: 'Free From', value: 'Mineral Oil, Silicones, Synthetic Fragrance' },
                        { key: 'Net Volume', value: '100 ml Glass Bottle with Dropper' },
                    ]
                },
                {
                    icon: '💄',
                    title: 'Velvet Long-Wear Matte Liquid Lipstick',
                    brand: 'BellaColor',
                    base_price: '399.00',
                    compare_at_price: '599.00',
                    gst_rate: '18',
                    hsn_code: '33041000',
                    total_stock: '75',
                    sku: 'LIP-VELVET-04',
                    description: 'Weightless transfer-proof velvet matte liquid lipstick enriched with Vitamin E and Jojoba oil that stays put comfortably for up to 12 hours.',
                    specs: [
                        { key: 'Finish', value: 'Transfer-Proof Non-Drying Velvet Matte' },
                        { key: 'Wear Time', value: 'Up to 12 Hours' },
                        { key: 'Shade', value: 'Desi Spice Nude' },
                    ]
                },
                {
                    icon: '🌸',
                    title: 'French Amber Luxury Eau De Parfum (100ml)',
                    brand: 'Maison Noir',
                    base_price: '1499.00',
                    compare_at_price: '2299.00',
                    gst_rate: '18',
                    hsn_code: '33030010',
                    total_stock: '30',
                    sku: 'PERF-AMBER-100',
                    description: 'Artisanal Eau De Parfum with intoxicating notes of Bergamot, warm Amber, Madagascar Vanilla, and Virginia Cedarwood.',
                    specs: [
                        { key: 'Concentration', value: '20% Eau De Parfum (EDP)' },
                        { key: 'Longevity', value: '8 - 10 Hours Long Lasting' },
                        { key: 'Volume', value: '100 ml Premium Glass Spray Bottle' },
                    ]
                },
                {
                    icon: '🧖',
                    title: 'Intense Keratin Damage Repair Hair Mask',
                    brand: 'SalonKera',
                    base_price: '649.00',
                    compare_at_price: '899.00',
                    gst_rate: '18',
                    hsn_code: '33059040',
                    total_stock: '50',
                    sku: 'MASK-KER-200G',
                    description: 'Deep conditioning salon-grade mask packed with hydrolyzed keratin protein and argan oil to revive chemically treated, colored, and heat-damaged strands.',
                    specs: [
                        { key: 'Treatment Type', value: '10-Minute Deep Conditioning Rinse-Off' },
                        { key: 'Hair Type', value: 'Dry, Frizzy & Chemically Treated Hair' },
                        { key: 'Net Weight', value: '200 Grams' },
                    ]
                }
            ],
            home_living: [
                {
                    icon: '🪑',
                    title: 'Solid Sheesham Wood 6-Seater Dining Set',
                    brand: 'HeritageWood',
                    base_price: '24999.00',
                    compare_at_price: '32000.00',
                    gst_rate: '18',
                    hsn_code: '94036000',
                    total_stock: '10',
                    sku: 'DINE-SHEESH-6S',
                    description: '100% seasoned Sheesham hardwood dining table with 6 ergonomically cushioned chairs finished in rich honey teak lacquer.',
                    specs: [
                        { key: 'Primary Wood', value: '100% Kiln-Seasoned Sheesham Wood' },
                        { key: 'Seating Capacity', value: '6 Persons (Includes 6 Chairs)' },
                        { key: 'Warranty', value: '3 Years Structural & Termite Warranty' },
                    ]
                },
                {
                    icon: '💺',
                    title: 'Ergonomic High-Back Breathable Mesh Office Chair',
                    brand: 'ErgoPro',
                    base_price: '6499.00',
                    compare_at_price: '8999.00',
                    gst_rate: '18',
                    hsn_code: '94013000',
                    total_stock: '25',
                    sku: 'CHAIR-ERGO-01',
                    description: 'Executive ergonomic office chair with breathable Korean mesh, 2D adjustable lumbar support, 3D armrests, and synchro-tilt lock mechanism.',
                    specs: [
                        { key: 'Mechanism', value: 'Synchro-Tilt Multi-Angle Recline Lock' },
                        { key: 'Gas Lift', value: 'Class-4 Hydraulic Heavy-Duty Gas Cylinder' },
                        { key: 'Max User Weight', value: '135 Kg Tested' },
                    ]
                },
                {
                    icon: '🛏️',
                    title: 'King Size Platform Bed with Hydraulic Storage',
                    brand: 'SleepCraft',
                    base_price: '28500.00',
                    compare_at_price: '36000.00',
                    gst_rate: '18',
                    hsn_code: '94035000',
                    total_stock: '8',
                    sku: 'BED-KING-HYD-01',
                    description: 'Modern minimalist king-size bed crafted with high-density engineered wood in warm walnut finish featuring easy-lift dual hydraulic storage pumps.',
                    specs: [
                        { key: 'Bed Size', value: 'King (78 x 72 inches mattress compatibility)' },
                        { key: 'Storage Mechanism', value: 'German Hydraulic Gas-Lift Storage Box' },
                        { key: 'Headboard', value: 'Padded Fabric Upholstery Cushion Headboard' },
                    ]
                },
                {
                    icon: '🛋️',
                    title: 'Velvet Luxury 3-Seater Chesterfield Sofa',
                    brand: 'RoyalLounge',
                    base_price: '19999.00',
                    compare_at_price: '26000.00',
                    gst_rate: '18',
                    hsn_code: '94014000',
                    total_stock: '12',
                    sku: 'SOFA-CHEST-3S',
                    description: 'Classic deep-button tufted Chesterfield sofa with solid Sal wood frame, high-resilience 32-density foam, and stain-resistant plush velvet fabric.',
                    specs: [
                        { key: 'Frame Material', value: 'Kiln-Dried Solid Sal Hardwood' },
                        { key: 'Fabric', value: '350 GSM Stain-Resistant Royal Velvet' },
                        { key: 'Dimensions', value: '84" Length x 34" Depth x 30" Height' },
                    ]
                },
                {
                    icon: '💡',
                    title: 'Modern Nordic Adjustable Arc Floor Lamp',
                    brand: 'Lumiere',
                    base_price: '3499.00',
                    compare_at_price: '4999.00',
                    gst_rate: '18',
                    hsn_code: '94052000',
                    total_stock: '30',
                    sku: 'LAMP-ARC-BLK',
                    description: 'Contemporary Scandinavian curved arc floor standing lamp with genuine heavy white marble base, matte black iron finish, and foot pedal switch.',
                    specs: [
                        { key: 'Base', value: 'Weighted Natural White Marble Slab Base' },
                        { key: 'Bulb Holder', value: 'E27 Standard (Warm White LED Bulb Included)' },
                        { key: 'Height', value: 'Adjustable 160cm - 195cm' },
                    ]
                },
                {
                    icon: '🧶',
                    title: 'Handwoven Natural Jute & Wool Area Rug (5x7 ft)',
                    brand: 'Kishangarh Rugs',
                    base_price: '2899.00',
                    compare_at_price: '4200.00',
                    gst_rate: '12',
                    hsn_code: '57023900',
                    total_stock: '25',
                    sku: 'RUG-JUTE-5X7',
                    description: 'Eco-friendly reversible hand-braided area rug made from natural golden jute and un-dyed wool fibers by traditional Rajasthani weavers.',
                    specs: [
                        { key: 'Dimensions', value: '5 Feet x 7 Feet (152cm x 213cm)' },
                        { key: 'Composition', value: '70% Golden Jute + 30% Pure Wool' },
                        { key: 'Weave', value: 'Dense Hand-Braided Reversible Weave' },
                    ]
                }
            ],
            groceries: [
                {
                    icon: '🌾',
                    title: 'Royal Aged Dum Basmati Rice (5kg Bag)',
                    brand: 'Kohinoor / Fortune',
                    base_price: '749.00',
                    compare_at_price: '920.00',
                    gst_rate: '5',
                    hsn_code: '10063020',
                    total_stock: '100',
                    sku: 'RICE-BAS-5KG',
                    description: 'Traditional extra-long grain aged Basmati rice with signature royal aroma that elongates to more than twice its size when cooked.',
                    specs: [
                        { key: 'Grain Length', value: '8.4mm Pre-Cooked (18mm Post-Cooked)' },
                        { key: 'Aging', value: '2 Years Naturally Aged in Silos' },
                        { key: 'FSSAI License', value: 'Certified Grade-A Purity' },
                    ]
                },
                {
                    icon: '🛢️',
                    title: 'Cold-Pressed Kachi Ghani Mustard Oil (5L Tin)',
                    brand: 'Dhara / Engine',
                    base_price: '899.00',
                    compare_at_price: '1050.00',
                    gst_rate: '5',
                    hsn_code: '15149110',
                    total_stock: '50',
                    sku: 'OIL-MUST-5L',
                    description: 'Authentic stone cold-pressed virgin mustard oil with sharp natural pungency, rich in Omega-3 and natural antioxidants, free from argemone.',
                    specs: [
                        { key: 'Extraction', value: 'Traditional Cold-Pressed Kachi Ghani' },
                        { key: 'Volume', value: '5 Litres Sealed Food-Grade Tin' },
                        { key: 'Additives', value: '100% Pure, Zero Chemicals / Preservatives' },
                    ]
                },
                {
                    icon: '🥜',
                    title: 'California Whole Raw Almonds (500g)',
                    brand: 'NutriDry',
                    base_price: '499.00',
                    compare_at_price: '650.00',
                    gst_rate: '5',
                    hsn_code: '08021200',
                    total_stock: '80',
                    sku: 'ALM-CAL-500G',
                    description: 'Handpicked Nonpareil extra-large whole raw almonds vacuum packed in nitrogen flushed resealable zipper pouch for maximum crunch and freshness.',
                    specs: [
                        { key: 'Grade', value: 'Nonpareil Extra No. 1 Supreme' },
                        { key: 'Net Weight', value: '500 Grams Resealable Pouch' },
                        { key: 'Origin', value: 'California, USA' },
                    ]
                },
                {
                    icon: '🍯',
                    title: '100% Pure Raw Wild Forest Honey (500g Jar)',
                    brand: 'ForestPure',
                    base_price: '399.00',
                    compare_at_price: '549.00',
                    gst_rate: '5',
                    hsn_code: '04090000',
                    total_stock: '60',
                    sku: 'HONEY-RAW-500',
                    description: 'Unprocessed, unpasteurized wild multi-flora raw forest honey NMR tested 100% pure with natural pollen and enzymes intact.',
                    specs: [
                        { key: 'Purity Check', value: 'NMR Laboratory Tested (Zero Sugar Adulteration)' },
                        { key: 'Processing', value: 'Cold-Filtered Raw & Unheated' },
                        { key: 'Packaging', value: '500g Glass Jar with Tamper Seal' },
                    ]
                },
                {
                    icon: '☕',
                    title: 'Darjeeling Second Flush Whole Leaf Tea (250g)',
                    brand: 'Makaibari / Glenburn',
                    base_price: '449.00',
                    compare_at_price: '600.00',
                    gst_rate: '5',
                    hsn_code: '09021020',
                    total_stock: '70',
                    sku: 'TEA-DARJ-250G',
                    description: 'Premium single estate Darjeeling black tea with golden tips yielding an exquisite amber liquor with distinct muscatel fruit and honey notes.',
                    specs: [
                        { key: 'Grade', value: 'FTGFOP-1 Muscatel Whole Leaf' },
                        { key: 'Harvest Flush', value: 'June Second Flush Peak Harvest' },
                        { key: 'Net Weight', value: '250 Grams Metal Caddy' },
                    ]
                },
                {
                    icon: '🫘',
                    title: 'Organic Unpolished Desi Toor Dal (1kg Pouch)',
                    brand: 'OrganicMandi',
                    base_price: '189.00',
                    compare_at_price: '230.00',
                    gst_rate: '0',
                    hsn_code: '07132000',
                    total_stock: '120',
                    sku: 'DAL-TOOR-1KG',
                    description: 'Natural unpolished pigeon pea pulses sourced directly from certified organic farms, retaining their natural nutrients and rich aromatic flavor.',
                    specs: [
                        { key: 'Polish Status', value: '100% Unpolished (Zero Oil / Water / Leather Polish)' },
                        { key: 'Protein Content', value: '22g Protein per 100g' },
                        { key: 'Net Weight', value: '1 Kg Vacuum Pouch' },
                    ]
                }
            ],
            toys_sports: [
                {
                    icon: '🏏',
                    title: 'English Willow Cricket Bat (Grade 1+)',
                    brand: 'SS / SG / Kookaburra',
                    base_price: '9999.00',
                    compare_at_price: '13500.00',
                    gst_rate: '12',
                    hsn_code: '95069920',
                    total_stock: '15',
                    sku: 'BAT-ENG-GR1',
                    description: 'Master craftsman pressed Grade 1+ English willow cricket bat with 8 straight grains, massive 40mm contoured edges, and balanced light pickup.',
                    specs: [
                        { key: 'Willow Grade', value: 'Grade 1+ Hand-Selected English Willow' },
                        { key: 'Edge Thickness', value: '38 - 40 mm Thick Contoured Edge' },
                        { key: 'Weight', value: '1,180 - 1,210 Grams (Light Balanced Pickup)' },
                    ]
                },
                {
                    icon: '🧩',
                    title: '1000-Piece Architectural Jigsaw Puzzle',
                    brand: 'MindCraft',
                    base_price: '799.00',
                    compare_at_price: '1199.00',
                    gst_rate: '12',
                    hsn_code: '95030090',
                    total_stock: '40',
                    sku: 'PUZ-1000P-CITY',
                    description: 'High-precision interlocking puzzle made of 100% recycled blue-core cardboard with glare-free linen finish depicting historic Indian architecture.',
                    specs: [
                        { key: 'Pieces Count', value: '1,000 Precision Interlocking Pieces' },
                        { key: 'Finished Dimensions', value: '70 cm x 50 cm' },
                        { key: 'Age Suitability', value: '12 Years and Above' },
                    ]
                },
                {
                    icon: '🤖',
                    title: '7-in-1 STEM Solar & Electric Robotics Kit',
                    brand: 'RoboSmart',
                    base_price: '1499.00',
                    compare_at_price: '2199.00',
                    gst_rate: '12',
                    hsn_code: '95030030',
                    total_stock: '50',
                    sku: 'ROBO-7IN1-KIT',
                    description: 'Hands-on educational engineering kit enabling kids to assemble 7 different robotic configurations powered by real solar photovoltaic panel or micro-battery.',
                    specs: [
                        { key: 'Learning Focus', value: 'Mechanical Gears, Renewable Solar Energy & Circuits' },
                        { key: 'Models Possible', value: '7 Working Transformers (Rover, Station, Robot)' },
                        { key: 'Age Group', value: '8 - 14 Years' },
                    ]
                },
                {
                    icon: '🧘',
                    title: 'Dual-Layer Eco TPE 6mm Yoga Mat with Strap',
                    brand: 'ZenFit',
                    base_price: '1199.00',
                    compare_at_price: '1899.00',
                    gst_rate: '12',
                    hsn_code: '95069190',
                    total_stock: '45',
                    sku: 'YOGA-MAT-6MM',
                    description: 'Odorless non-slip dual-texture eco-TPE exercise mat with body alignment lines, high-density 6mm joint cushioning, and complimentary carry strap.',
                    specs: [
                        { key: 'Material', value: 'Biodegradable Eco-TPE (Zero PVC / Toxic Glues)' },
                        { key: 'Thickness', value: '6 mm High Density Anti-Tear Cushioning' },
                        { key: 'Dimensions', value: '72" Length x 24" Width (183cm x 61cm)' },
                    ]
                },
                {
                    icon: '🛸',
                    title: '4K Dual-Camera Optical Flow RC Drone',
                    brand: 'SkyPhantom',
                    base_price: '3999.00',
                    compare_at_price: '5999.00',
                    gst_rate: '18',
                    hsn_code: '95030090',
                    total_stock: '25',
                    sku: 'DRONE-4K-WIFI',
                    description: 'Foldable quadcopter drone with 4K front camera, bottom optical flow positioning sensor, one-key return, altitude hold, and live smartphone FPV stream.',
                    specs: [
                        { key: 'Camera Resolution', value: '4K Ultra HD Main Lens + HD Bottom Flow Sensor' },
                        { key: 'Flight Time', value: '18 - 20 Minutes per Modular Battery' },
                        { key: 'Control Range', value: '150 Meters 2.4GHz Wi-Fi' },
                    ]
                },
                {
                    icon: '⚽',
                    title: 'FIFA Standard Hand-Stitched Size-5 Football',
                    brand: 'Nivia / Cosco',
                    base_price: '1099.00',
                    compare_at_price: '1599.00',
                    gst_rate: '12',
                    hsn_code: '95066210',
                    total_stock: '60',
                    sku: 'BALL-SOCC-SZ5',
                    description: 'Professional 32-panel match soccer ball made of micro-textured PU leather with reinforced multi-ply backing and latex air-retention bladder.',
                    specs: [
                        { key: 'Ball Size', value: 'Official Size 5 (Circumference 68-70cm)' },
                        { key: 'Weight', value: '420 - 440 Grams' },
                        { key: 'Surface Suitability', value: 'Natural Grass, Artificial Turf & Concrete' },
                    ]
                }
            ],
            digital_services: [
                {
                    icon: '🎨',
                    title: 'Custom High-Converting Figma UI/UX Landing Page Design',
                    brand: 'PixelCraft Studio',
                    base_price: '4999.00',
                    compare_at_price: '7500.00',
                    gst_rate: '18',
                    hsn_code: '998314',
                    total_stock: '10',
                    sku: 'GIG-UIUX-LANDING',
                    description: 'Pixel-perfect, modern responsive Figma landing page UI/UX design with complete auto-layout, mobile viewports, components library, and style guide.',
                    specs: [
                        { key: 'Deliverables', value: 'Complete Layered Figma File (Desktop + Tablet + Mobile)' },
                        { key: 'Turnaround Time', value: '3 Business Days' },
                        { key: 'Revisions', value: '3 Rounds of Revisions Included' },
                        { key: 'Commercial Rights', value: '100% Full Ownership Transfer' },
                    ]
                },
                {
                    icon: '💻',
                    title: 'Full-Stack Web App Development (Laravel / Next.js)',
                    brand: 'CodeForge Labs',
                    base_price: '24999.00',
                    compare_at_price: '35000.00',
                    gst_rate: '18',
                    hsn_code: '998313',
                    total_stock: '5',
                    sku: 'GIG-DEV-FULLSTACK',
                    description: 'End-to-end custom web application engineering with secure authentication, relational database, responsive Tailwind UI, payment gateway integration, and server deployment.',
                    specs: [
                        { key: 'Technology Stack', value: 'Laravel 11 / Next.js 14 + Tailwind CSS + MySQL/PostgreSQL' },
                        { key: 'Features Included', value: 'Admin Panel, User Auth, API Endpoints & Payment Gateway' },
                        { key: 'Deployment', value: 'Live Deployment on Hostinger VPS or Vercel Included' },
                    ]
                },
                {
                    icon: '🎬',
                    title: 'Viral 4K Instagram Reels & YouTube Shorts Video Editing',
                    brand: 'MotionPulse',
                    base_price: '1999.00',
                    compare_at_price: '3000.00',
                    gst_rate: '18',
                    hsn_code: '998319',
                    total_stock: '20',
                    sku: 'GIG-VIDEO-REELS',
                    description: 'High-retention short-form video editing with animated Alex Hormozi style captions, dynamic sound effects, punchy zooms, b-roll footage, and color grading.',
                    specs: [
                        { key: 'Video Length', value: 'Up to 60 Seconds per Reel (9:16 Vertical 4K)' },
                        { key: 'Turnaround Time', value: '24 - 48 Hours Fast Delivery' },
                        { key: 'Audio & Music', value: 'Trending Royalty-Free Sound Design & SFX' },
                    ]
                },
                {
                    icon: '🏷️',
                    title: 'Complete Modern Minimalist Brand Identity Kit',
                    brand: 'BrandMorph',
                    base_price: '3499.00',
                    compare_at_price: '5500.00',
                    gst_rate: '18',
                    hsn_code: '998314',
                    total_stock: '15',
                    sku: 'GIG-BRAND-KIT',
                    description: 'Distinctive brand identity design including 3 custom vector logo concepts, brand typography pairing, curated color palettes, social media templates, and brand book guidelines.',
                    specs: [
                        { key: 'Logo Concepts', value: '3 Unique Minimalist Design Concepts' },
                        { key: 'Deliverable Formats', value: 'Vector AI, SVG, EPS, Transparent PNG & Print PDF' },
                        { key: 'Brand Assets', value: 'Business Card, Letterhead & Social Media Profile Kit' },
                    ]
                },
                {
                    icon: '🔍',
                    title: 'Comprehensive Technical & On-Page SEO Growth Audit',
                    brand: 'RankMaster',
                    base_price: '3999.00',
                    compare_at_price: '6000.00',
                    gst_rate: '18',
                    hsn_code: '998315',
                    total_stock: '12',
                    sku: 'GIG-SEO-AUDIT',
                    description: 'In-depth 100+ checkpoint SEO audit identifying crawl errors, Core Web Vitals bottlenecks, keyword search intent opportunities, structured schema markup, and backlink action plan.',
                    specs: [
                        { key: 'Audit Scope', value: 'Full Technical, On-Page, Schema & Core Web Vitals Audit' },
                        { key: 'Deliverables', value: 'Detailed PDF Report + Actionable Spreadsheet Checklist' },
                        { key: 'Competitor Analysis', value: 'Top 3 Organic Ranking Competitors Dissected' },
                    ]
                },
                {
                    icon: '☁️',
                    title: 'Cloud VPS Server Hardening & Docker CI/CD Pipeline',
                    brand: 'DevOpsGrid',
                    base_price: '5499.00',
                    compare_at_price: '8500.00',
                    gst_rate: '18',
                    hsn_code: '998313',
                    total_stock: '8',
                    sku: 'GIG-DEVOPS-VPS',
                    description: 'Professional Linux VPS server setup (Ubuntu 22/24 LTS) with UFW firewall, Nginx reverse proxy, automatic free SSL certificates, Docker containers, and GitHub Actions CI/CD.',
                    specs: [
                        { key: 'Server Setup', value: 'Nginx, PHP 8.3/Node.js, MySQL/Redis & UFW Security' },
                        { key: 'Automation', value: 'GitHub Actions Auto-Deploy Pipeline on Git Push' },
                        { key: 'Monitoring', value: 'Automated Daily Database Backups to Cloud Storage' },
                    ]
                }
            ],
            machinery: [
                {
                    icon: '⚙️',
                    title: 'Automatic Marble & Granite Bridge Cutting Machine 15HP',
                    brand: 'K71 Heavy Machinery',
                    base_price: '485000.00',
                    compare_at_price: '550000.00',
                    gst_rate: '18',
                    hsn_code: '84641010',
                    total_stock: '3',
                    sku: 'MACH-BRIDGE-15HP',
                    description: 'Heavy cast-iron monoblock bridge cutting saw with microcomputer PLC touch-screen controller, laser alignment guide, and motorized hydraulic tilting table for stone slabs.',
                    specs: [
                        { key: 'Main Motor Power', value: '15 HP 3-Phase Pure Copper Induction Motor' },
                        { key: 'Blade Diameter', value: '350mm - 600mm Diamond Circular Saw' },
                        { key: 'Max Cutting Dimensions', value: '3200mm Length x 2000mm Width x 150mm Depth' },
                        { key: 'Control System', value: 'Microcomputer PLC with Color Touchscreen' },
                        { key: 'Warranty Coverage', value: '1 Year Comprehensive On-Site Warranty' },
                    ]
                },
                {
                    icon: '⚡',
                    title: 'Heavy Industrial 3-Phase 10HP Induction Motor',
                    brand: 'Siemens / Kirloskar / K71',
                    base_price: '28500.00',
                    compare_at_price: '34000.00',
                    gst_rate: '18',
                    hsn_code: '85015210',
                    total_stock: '15',
                    sku: 'MOT-10HP-1440',
                    description: 'Foot-mounted 10 HP (7.5 kW) 4-pole 1440 RPM totally enclosed fan-cooled (TEFC) industrial squirrel cage induction motor with class-F insulation.',
                    specs: [
                        { key: 'Rated Power', value: '10 HP (7.5 kW) 1440 RPM 4-Pole' },
                        { key: 'Voltage & Phase', value: '415V Three Phase 50Hz (Industrial)' },
                        { key: 'Enclosure Protection', value: 'Cast Iron TEFC IP55 Dust & Splash Proof' },
                        { key: 'Winding', value: '100% Super Enameled Pure Copper Wire' },
                    ]
                },
                {
                    icon: '🔬',
                    title: '3000W Raycus Fiber Laser Metal Sheet Cutting Machine',
                    brand: 'LaserCraft B2B',
                    base_price: '1450000.00',
                    compare_at_price: '1650000.00',
                    gst_rate: '18',
                    hsn_code: '84561100',
                    total_stock: '2',
                    sku: 'LASER-3000W-FIB',
                    description: 'High-speed CNC fiber laser sheet cutting machine with 3000W Raycus generator, heavy welded stress-relieved gantry bed, dual smoke exhaust, and CypCut control software.',
                    specs: [
                        { key: 'Laser Source', value: '3000W Raycus Continuous Fiber Laser' },
                        { key: 'Working Bed Area', value: '1500mm x 3000mm (5ft x 10ft)' },
                        { key: 'Cutting Thickness', value: 'Mild Steel up to 20mm, Stainless Steel up to 10mm' },
                        { key: 'Positioning Accuracy', value: '±0.03 mm Repositioning Accuracy' },
                    ]
                },
                {
                    icon: '🏗️',
                    title: 'High-Pressure Industrial 100-Ton Hydraulic Workshop Press',
                    brand: 'Apex Hydro',
                    base_price: '1650000.00',
                    compare_at_price: '1900000.00',
                    gst_rate: '18',
                    hsn_code: '84629100',
                    total_stock: '2',
                    sku: 'HYD-PRESS-100T',
                    description: 'Heavy H-frame 100-metric ton motorized hydraulic workshop press with adjustable working table height, hard-chrome ram cylinder, and overload relief valve.',
                    specs: [
                        { key: 'Pressing Capacity', value: '100 Metric Tons Maximum Force' },
                        { key: 'Cylinder Stroke', value: '300 mm Ram Travel' },
                        { key: 'Table Width', value: '1,000 mm Working Bed Clearance' },
                        { key: 'Pump Unit', value: 'Dual Speed Motorized Hydraulic Power Pack' },
                    ]
                },
                {
                    icon: '🧱',
                    title: 'Makrana Calacatta White Marble Gangsaw Slabs (Bulk Lot)',
                    brand: 'K71 Makrana Stone Quarries',
                    base_price: '320.00',
                    compare_at_price: '420.00',
                    gst_rate: '18',
                    hsn_code: '68022110',
                    total_stock: '5000',
                    sku: 'SLAB-MAK-CAL-18',
                    description: 'Pure natural Makrana Calacatta white marble gangsaw cut slabs with striking natural grey veining, high calcium purity, zero pinholes, and mirror polish.',
                    specs: [
                        { key: 'Slab Thickness', value: '16mm - 18mm Gangsaw Cut Slabs' },
                        { key: 'Surface Polish', value: 'High Gloss Mirror Polish / Unpolished Honed' },
                        { key: 'Minimum Order Qty (MOQ)', value: '1,000 Sq.Ft Slabs Lot' },
                        { key: 'Loading & Dispatch', value: 'Free Wooden Crate Packing & Crane Loading' },
                    ]
                },
                {
                    icon: '📦',
                    title: 'Fully Automatic Continuous Band Sealer Machine',
                    brand: 'PackTech Industrial',
                    base_price: '24500.00',
                    compare_at_price: '32000.00',
                    gst_rate: '18',
                    hsn_code: '84223000',
                    total_stock: '20',
                    sku: 'PACK-BAND-SEAL',
                    description: 'Continuous horizontal band pouch heat sealing machine with digital temperature controller, embossing date/batch coding wheel, and motorized conveyor.',
                    specs: [
                        { key: 'Sealing Speed', value: '0 - 12 Meters per Minute Continuous' },
                        { key: 'Sealing Width', value: '10 mm Hermetic Embossed Seal' },
                        { key: 'Temperature Range', value: '0 - 300°C Digital PID Control' },
                    ]
                }
            ],
            b2b: [
                {
                    icon: '🧱',
                    title: 'Makrana Calacatta White Marble Gangsaw Slabs Lot',
                    brand: 'K71 Stone Direct',
                    base_price: '320.00',
                    compare_at_price: '420.00',
                    gst_rate: '18',
                    hsn_code: '68022110',
                    total_stock: '5000',
                    sku: 'B2B-MAK-CAL-01',
                    description: 'Wholesale lot of pure natural white Makrana marble gangsaw slabs for luxury flooring and hotel lobbies.',
                    specs: [
                        { key: 'Slab Thickness', value: '16mm - 18mm Gangsaw Cut' },
                        { key: 'Minimum Order Quantity', value: '1,000 Sq.Ft Lot' },
                        { key: 'Finish', value: 'Mirror Polish High Gloss' },
                    ]
                }
            ],
            food: [
                {
                    icon: '🍛',
                    title: 'Royal Hyderabadi Dum Gosht Biryani (Single / Handi)',
                    brand: 'Bawarchi Grand Kitchen',
                    base_price: '380.00',
                    compare_at_price: '450.00',
                    gst_rate: '5',
                    hsn_code: '21069099',
                    total_stock: '40',
                    sku: 'DISH-BIRY-GOSHT',
                    description: 'Authentic Kachche Gosht ki Dum Biryani cooked with succulent tender mutton marinated in curd and secret potli spices, layered with aged Basmati rice and pure desi ghee.',
                    specs: [
                        { key: 'Portion Size', value: '1 - 2 Persons (650g Servings in Clay Handi)' },
                        { key: 'Dietary', value: 'Halal Certified Tender Mutton' },
                        { key: 'Spice Level', value: 'Authentic Desi Medium Spicy' },
                        { key: 'Accompaniments', value: 'Burani Raita + Mirchi Ka Salan Included' },
                    ]
                },
                {
                    icon: '🍕',
                    title: 'Wood-Fired Margherita Pizza (11-Inch Hand-Tossed)',
                    brand: 'Napoli Wood Ovens',
                    base_price: '349.00',
                    compare_at_price: '420.00',
                    gst_rate: '5',
                    hsn_code: '19059090',
                    total_stock: '50',
                    sku: 'DISH-PIZZA-MARG',
                    description: 'Classic Neapolitan pizza baked in 450°C wood-fired oven featuring San Marzano tomato sauce, fresh Fior Di Latte mozzarella, extra virgin olive oil, and sweet basil leaves.',
                    specs: [
                        { key: 'Pizza Size', value: '11-Inch Hand-Stretched Neapolitan' },
                        { key: 'Crust Type', value: '48-Hour Cold-Fermented Sourdough Crust' },
                        { key: 'Cheese', value: '100% Fresh Buffalo Mozzarella' },
                        { key: 'Dietary', value: 'Pure Vegetarian' },
                    ]
                },
                {
                    icon: '🍲',
                    title: 'Dal Makhani with 2 Tandoori Butter Naan Platter',
                    brand: 'Pind Punjab Dhaba',
                    base_price: '289.00',
                    compare_at_price: '350.00',
                    gst_rate: '5',
                    hsn_code: '21069099',
                    total_stock: '60',
                    sku: 'DISH-DAL-MAKH-COMBO',
                    description: '16-hour slow-simmered whole black urad lentils infused with smoked butter, rich cream, and Kashmiri red chillies, served with two hot charred tandoori butter naans.',
                    specs: [
                        { key: 'Combo Inclusions', value: '350ml Dal Makhani + 2 Tandoori Butter Naan' },
                        { key: 'Salad & Dips', value: 'Mint Coriander Chutney & Pickled Sirka Onions' },
                        { key: 'Dietary', value: 'Pure Vegetarian' },
                    ]
                },
                {
                    icon: '🧀',
                    title: 'Saffron Paneer Tikka Masala Bowl with Jeera Rice',
                    brand: 'Zaika Express',
                    base_price: '319.00',
                    compare_at_price: '380.00',
                    gst_rate: '5',
                    hsn_code: '21069099',
                    total_stock: '45',
                    sku: 'DISH-PANEER-BOWL',
                    description: 'Char-grilled fresh Malai paneer cubes simmered in a luscious cashew and saffron infused tomato makhani gravy, paired with fragrant cumin Basmati rice.',
                    specs: [
                        { key: 'Portion Size', value: 'Generous Single Meal (500g Total Bowl)' },
                        { key: 'Dietary', value: 'Pure Vegetarian' },
                        { key: 'Spice Level', value: 'Balanced Mild & Creamy' },
                    ]
                },
                {
                    icon: '🍰',
                    title: 'Belgian Dark Chocolate Truffle Pastry (120g Slice)',
                    brand: 'La Petite Patisserie',
                    base_price: '179.00',
                    compare_at_price: '220.00',
                    gst_rate: '5',
                    hsn_code: '19059010',
                    total_stock: '35',
                    sku: 'DISH-PASTRY-CHOC',
                    description: 'Decadent multi-layered Dutch cocoa sponge filled with silky 70% Callebaut Belgian dark chocolate ganache, topped with mirror glaze.',
                    specs: [
                        { key: 'Dietary', value: '100% Eggless Certified' },
                        { key: 'Chocolate Source', value: '70% Single Origin Callebaut Belgian Chocolate' },
                        { key: 'Serving Advice', value: 'Best served chilled or warm 10 seconds in microwave' },
                    ]
                },
                {
                    icon: '🥭',
                    title: 'Fresh Alphonso Mango Pulp Thick Shake (350ml)',
                    brand: 'JuiceNation',
                    base_price: '169.00',
                    compare_at_price: '210.00',
                    gst_rate: '5',
                    hsn_code: '22029990',
                    total_stock: '50',
                    sku: 'DISH-MANGO-SHAKE',
                    description: 'Thick gourmet milkshake blended with 100% natural Ratnagiri Alphonso mango pulp, full cream chilled milk, and a scoop of Madagascar vanilla ice cream.',
                    specs: [
                        { key: 'Volume', value: '350 ml Sealed Glass Bottle' },
                        { key: 'Fruit Content', value: 'Real Alphonso Mango Pulp (Zero Artificial Flavors)' },
                    ]
                }
            ],
            services: [
                {
                    icon: '🧹',
                    title: 'Complete Deep Home Cleaning & Sanitization (2/3 BHK)',
                    brand: 'CleanMaster Pro Care',
                    base_price: '2499.00',
                    compare_at_price: '3499.00',
                    gst_rate: '18',
                    hsn_code: '998533',
                    total_stock: '6',
                    sku: 'SERV-CLEAN-3BHK',
                    description: 'Comprehensive top-to-bottom deep cleaning by verified pros using industrial single-disc scrubbing machines, vacuum degreasing of kitchen chimneys, bathroom descaling, and floor buffing.',
                    specs: [
                        { key: 'Service Duration', value: '4 - 5 Hours (Team of 2 Verified Professionals)' },
                        { key: 'Scope of Work', value: 'Kitchen Cabinets & Chimney, 2 Bathrooms, Floor Buffing & Balconies' },
                        { key: 'Chemicals Used', value: 'Diversey Professional Non-Toxic Eco-Friendly Chemicals' },
                        { key: 'Guarantee', value: '100% Revisit Inspection Guarantee within 24 Hours' },
                    ]
                },
                {
                    icon: '❄️',
                    title: 'Split AC Master Jet-Wash Overhaul & Gas Check',
                    brand: 'CoolWave AC Solutions',
                    base_price: '799.00',
                    compare_at_price: '1200.00',
                    gst_rate: '18',
                    hsn_code: '998713',
                    total_stock: '12',
                    sku: 'SERV-AC-JETWASH',
                    description: 'Advanced high-pressure water jet cleaning with protective AC jacket apron for indoor cooling coils, blower fan, outdoor condenser fins, and digital refrigerant pressure diagnosis.',
                    specs: [
                        { key: 'Service Duration', value: '60 - 75 Minutes per AC Unit' },
                        { key: 'Equipment Deployed', value: 'High-Pressure Water Jet Pump + Foam Cleanser + Gauge Manifold' },
                        { key: 'Warranty Coverage', value: '30-Day Cooling Performance Warranty' },
                    ]
                },
                {
                    icon: '⚡',
                    title: 'Master Electrician Diagnostics & Wiring Repair',
                    brand: 'Apex Electricals',
                    base_price: '299.00',
                    compare_at_price: '450.00',
                    gst_rate: '18',
                    hsn_code: '998713',
                    total_stock: '15',
                    sku: 'SERV-ELEC-VISIT',
                    description: 'Licensed ITI electrician doorstep visit for short-circuit troubleshooting, MCB tripping resolution, appliance points installation, and whole house earthing tests.',
                    specs: [
                        { key: 'Visit Duration', value: '45 - 60 Minutes On-Site Inspection' },
                        { key: 'Tools Included', value: 'Digital Multi-Meter, Phase Sequence Tester & Insulated Tools' },
                        { key: 'Pricing Term', value: '₹299 Inspection Fee (Deducted if major work approved)' },
                    ]
                },
                {
                    icon: '🔧',
                    title: 'Precision Plumbing Leak Detection & Fixture Install',
                    brand: 'HydroFix Plumbing',
                    base_price: '349.00',
                    compare_at_price: '500.00',
                    gst_rate: '18',
                    hsn_code: '998713',
                    total_stock: '15',
                    sku: 'SERV-PLUMB-VISIT',
                    description: 'Expert plumber visit for resolving hidden concealed pipe leakages, low water pressure, dripping bathroom taps, flush tank overhaul, and sanitary fixture replacements.',
                    specs: [
                        { key: 'Typical Duration', value: '45 Minutes' },
                        { key: 'Workmanship Guarantee', value: '14 Days Free Revisit Warranty' },
                    ]
                },
                {
                    icon: '💆',
                    title: 'At-Home Organic Fruit Glow Facial & Salon Spa',
                    brand: 'GlamAtHome Salon',
                    base_price: '1499.00',
                    compare_at_price: '2200.00',
                    gst_rate: '18',
                    hsn_code: '999721',
                    total_stock: '8',
                    sku: 'SERV-SALON-FACIAL',
                    description: 'Relaxing luxury salon experience in the comfort of your home using single-use sealed kits: Cleansing, gentle enzymatic scrub, steam, blackhead vacuum extraction, and glowing fruit pack.',
                    specs: [
                        { key: 'Duration', value: '75 Minutes' },
                        { key: 'Hygiene Kit', value: '100% Disposable Biodegradable Towels, Sheet & Headband' },
                        { key: 'Products Brand', value: 'O3+ / Cheryl’s Cosmeceuticals Certified Kits' },
                    ]
                },
                {
                    icon: '🎨',
                    title: 'Full House Waterproof Wall Texture & Repaint',
                    brand: 'Apex ColorCraft',
                    base_price: '14999.00',
                    compare_at_price: '20000.00',
                    gst_rate: '18',
                    hsn_code: '995473',
                    total_stock: '4',
                    sku: 'SERV-PAINT-2BHK',
                    description: 'Turnkey interior house painting for 2 BHK apartment with computerized laser measurement, full masking of furniture, 2 coats acrylic putty, 1 coat primer, and 2 coats luxury emulsion.',
                    specs: [
                        { key: 'Scope', value: 'Interior 2 BHK Walls & Ceiling (~1,200 Sq.Ft Carpet)' },
                        { key: 'Paints Used', value: 'Asian Paints Royale Luxury Emulsion or Berger Silk' },
                        { key: 'Warranty', value: '3 Years Anti-Flaking & Peeling Warranty' },
                    ]
                }
            ],
            used: [
                {
                    icon: '📱',
                    title: 'iPhone 14 Pro Max 256GB Space Black (Mint 9/10)',
                    brand: 'Apple',
                    base_price: '72000.00',
                    compare_at_price: '85000.00',
                    gst_rate: '0',
                    hsn_code: '85171300',
                    total_stock: '1',
                    sku: 'USED-IPHONE-14PM',
                    description: 'Immaculately maintained iPhone 14 Pro Max in Space Black with zero scratches or dents. 89% original battery health, tested 100% working Face ID, Dynamic Island, and True Tone.',
                    specs: [
                        { key: 'Cosmetic Condition', value: 'Mint 9.5/10 (Always Used with Case & Screen Guard)' },
                        { key: 'Battery Health', value: '89% Maximum Capacity (Original Apple Battery)' },
                        { key: 'Inclusions', value: 'Original Box, Apple USB-C Braided Cable & Invoice' },
                        { key: 'Ownership Age', value: '14 Months Used by Single Owner' },
                    ]
                },
                {
                    icon: '💻',
                    title: 'MacBook Pro 14" M1 Pro 16GB / 512GB Space Grey',
                    brand: 'Apple',
                    base_price: '94000.00',
                    compare_at_price: '115000.00',
                    gst_rate: '0',
                    hsn_code: '84713010',
                    total_stock: '1',
                    sku: 'USED-MBP-14-M1',
                    description: 'Flawless condition MacBook Pro 14" with Liquid Retina XDR ProMotion 120Hz display, Apple M1 Pro chip, 16GB unified memory, and 512GB NVMe SSD. Only 142 battery cycles.',
                    specs: [
                        { key: 'Condition Grade', value: 'Grade A+ Spotless Body & Clean Screen Coating' },
                        { key: 'Battery Health', value: '94% Health (142 Total Charge Cycles)' },
                        { key: 'Charger & Box', value: 'Original 67W MagSafe 3 Fast Charger & Retail Box' },
                    ]
                },
                {
                    icon: '🛵',
                    title: 'Honda Activa 6G 110cc BS6 (Single Owner 2022)',
                    brand: 'Honda',
                    base_price: '58000.00',
                    compare_at_price: '68000.00',
                    gst_rate: '0',
                    hsn_code: '87112019',
                    total_stock: '1',
                    sku: 'USED-ACTIVA-6G',
                    description: 'Single owner registered 2022 Honda Activa 6G in metallic grey with low verified odometer reading. Regularly serviced at authorized Honda service center. Complete clean paperwork.',
                    specs: [
                        { key: 'Registration Year', value: 'November 2022 (Single Owner)' },
                        { key: 'Kilometers Driven', value: '14,200 KM Verified' },
                        { key: 'Insurance Status', value: 'Comprehensive Insurance Valid till Nov 2027' },
                        { key: 'NOC & Transfer', value: 'NOC Ready for Instant Transfer' },
                    ]
                },
                {
                    icon: '📷',
                    title: 'Canon EOS R6 Full-Frame Mirrorless Camera (Body Only)',
                    brand: 'Canon',
                    base_price: '118000.00',
                    compare_at_price: '140000.00',
                    gst_rate: '0',
                    hsn_code: '85258900',
                    total_stock: '1',
                    sku: 'USED-CANON-R6',
                    description: 'Studio-used Canon EOS R6 full-frame mirrorless body with in-body image stabilization (IBIS), 4K 60p 10-bit video, and dual SD card slots. Shutter actuations under 18,000.',
                    specs: [
                        { key: 'Shutter Actuations', value: 'Under 18,000 (Tested Shutter Life 300,000)' },
                        { key: 'Sensor Condition', value: 'Spotless 100% Clean Full-Frame Sensor' },
                        { key: 'Inclusions', value: 'Original LP-E6NH Battery, Charger, Neck Strap & Box' },
                    ]
                },
                {
                    icon: '🏍️',
                    title: 'Royal Enfield Classic 350 Reborn - Halcyon Black',
                    brand: 'Royal Enfield',
                    base_price: '155000.00',
                    compare_at_price: '185000.00',
                    gst_rate: '0',
                    hsn_code: '87112029',
                    total_stock: '1',
                    sku: 'USED-RE-CLASSIC',
                    description: 'Meticulously kept J-series engine Royal Enfield Classic 350 in Halcyon Black with dual-channel ABS, genuine RE touring seats, crash guard, and alloy wheels.',
                    specs: [
                        { key: 'Model Year', value: '2022 (BS6 Dual-Channel ABS Edition)' },
                        { key: 'KM Driven', value: '9,800 KM' },
                        { key: 'Accessories Fitted', value: 'Touring Rider Seat, Sump Guard, Crash Guard' },
                    ]
                },
                {
                    icon: '🪑',
                    title: 'Solid Teak Wood 4-Seater Dining Table Set (Pre-Owned)',
                    brand: 'Teak Heritage',
                    base_price: '11500.00',
                    compare_at_price: '18000.00',
                    gst_rate: '0',
                    hsn_code: '94036000',
                    total_stock: '1',
                    sku: 'USED-DINE-TEAK4',
                    description: 'Genuine solid CP teak wood 4-seater dining table recently buffed with natural polyurethane varnish, with 4 matching cushioned teak wood chairs.',
                    specs: [
                        { key: 'Condition', value: 'Excellent 9/10 (Re-Varnished Natural Teak)' },
                        { key: 'Dimensions', value: '48" Length x 36" Width x 30" Height' },
                        { key: 'Reason for Selling', value: 'Relocating to New City' },
                    ]
                }
            ],
            footwear: [
                {
                    icon: '👟',
                    title: 'Breathable Knit Sports Running Shoes',
                    brand: 'AeroStep Footwear',
                    base_price: '1899.00',
                    compare_at_price: '2999.00',
                    gst_rate: '12',
                    hsn_code: '64041100',
                    total_stock: '60',
                    sku: 'SHOE-AERO-RUN',
                    description: 'Ultralight running and gym trainer shoes featuring memory foam insole, breathable jacquard mesh upper, and shock-absorbing phylon sole.',
                    specs: [
                        { key: 'Upper Material', value: 'Breathable Jacquard Knit' },
                        { key: 'Sole Material', value: 'Phylon EVA High-Rebound Foam' },
                        { key: 'Sizing Standard', value: 'UK / India Standard Sizing' },
                        { key: 'Closure', value: 'Lace-Up with Padded Collar' }
                    ]
                },
                {
                    icon: '👞',
                    title: 'Handcrafted Genuine Leather Oxford Dress Shoes',
                    brand: 'Kingsman Leathercraft',
                    base_price: '3499.00',
                    compare_at_price: '4999.00',
                    gst_rate: '12',
                    hsn_code: '64035910',
                    total_stock: '30',
                    sku: 'SHOE-OXFORD-BRN',
                    description: 'Pure full-grain leather classic Oxford shoes with Goodyear welted construction, cushioned leather lining, and anti-skid rubber traction heel.',
                    specs: [
                        { key: 'Material', value: '100% Genuine Full-Grain Crust Leather' },
                        { key: 'Sole', value: 'Handcrafted Leather Sole with Rubber Insert' },
                        { key: 'Occasion', value: 'Formal, Wedding, Executive Business' }
                    ]
                }
            ],
            kirana: [
                {
                    icon: '🍚',
                    title: 'Aged Royal Daawat Basmati Rice (XXL Long Grain)',
                    brand: 'Daawat / Fortune',
                    base_price: '145.00',
                    compare_at_price: '180.00',
                    gst_rate: '5',
                    hsn_code: '10063020',
                    total_stock: '200',
                    sku: 'GROC-RICE-BASM',
                    description: '2-year aged Himalayan Basmati rice with extra-long slender grains that elongate up to 24mm upon cooking. Rich natural aroma.',
                    specs: [
                        { key: 'Grain Length', value: '8.4mm Raw (Elongates to 24mm cooked)' },
                        { key: 'Aging Period', value: '2 Years Traditional Aging' },
                        { key: 'Dietary', value: '100% Pure Vegetarian Grocery Staple' }
                    ]
                },
                {
                    icon: '🌾',
                    title: 'Chakki Fresh 100% Whole Wheat Atta',
                    brand: 'Aashirvaad / MP Sharbati',
                    base_price: '280.00',
                    compare_at_price: '340.00',
                    gst_rate: '0',
                    hsn_code: '11010000',
                    total_stock: '150',
                    sku: 'GROC-ATTA-WHEAT',
                    description: 'Stone-milled whole wheat flour made from premium golden Sharbati wheat grains from Madhya Pradesh. High fiber for ultra-soft rotis.',
                    specs: [
                        { key: 'Ingredient', value: '100% MP Sharbati Whole Wheat' },
                        { key: 'Milling Process', value: 'Traditional Stone Chakki (0% Maida)' },
                        { key: 'Shelf Life', value: '3 Months from Packaging Date' }
                    ]
                },
                {
                    icon: '🧈',
                    title: 'Pure Desi Cow Ghee (Bilona Vedic Method)',
                    brand: 'Gir Organic Farms',
                    base_price: '899.00',
                    compare_at_price: '1150.00',
                    gst_rate: '12',
                    hsn_code: '04059020',
                    total_stock: '80',
                    sku: 'GROC-COW-GHEE',
                    description: 'Traditional Vedic Bilona method A2 cow ghee prepared by churning cultured curd. Granular golden texture and authentic divine aroma.',
                    specs: [
                        { key: 'Milk Type', value: 'Pure Indigenous A2 Gir Cow Milk' },
                        { key: 'Method', value: 'Traditional Wooden Bilona Churning' },
                        { key: 'Packaging', value: 'Food-Grade Sealed Glass Jar' }
                    ]
                }
            ],
            groceries: [
                {
                    icon: '🍚',
                    title: 'Aged Royal Basmati Rice (XXL Long Grain)',
                    brand: 'Fortune / Daawat',
                    base_price: '145.00',
                    compare_at_price: '180.00',
                    gst_rate: '5',
                    hsn_code: '10063020',
                    total_stock: '200',
                    sku: 'GROC-RICE-BASM',
                    description: '2-year aged Basmati rice with long slender grains.',
                    specs: [
                        { key: 'Grain Length', value: '8.4mm Raw' },
                        { key: 'Dietary', value: '100% Pure Vegetarian' }
                    ]
                }
            ],
            marble: [
                {
                    icon: '🧱',
                    title: 'Makrana Pure White Marble Slabs Lot (Bulk RFQ)',
                    brand: 'K71 Makrana Stone Quarries',
                    base_price: '350.00',
                    compare_at_price: '450.00',
                    gst_rate: '18',
                    hsn_code: '68022110',
                    total_stock: '10000',
                    sku: 'MARBLE-MAK-WHT',
                    description: 'Pure natural Makrana white marble gangsaw slabs for luxury flooring, temples, and palaces. High calcium purity, zero pinholes, mirror finish.',
                    specs: [
                        { key: 'Thickness', value: '16mm - 18mm Gangsaw Slabs' },
                        { key: 'Surface Finish', value: 'Mirror Polished' },
                        { key: 'Origin', value: 'Makrana, Rajasthan' },
                        { key: 'Order Workflow', value: 'Enquiry / Custom Quote Request' }
                    ]
                }
            ]
        },

        selectedPresetCategory: 'all',

        get currentSectorPresets() {
            if (this.selectedPresetCategory && this.selectedPresetCategory !== 'all') {
                return this.presets[this.selectedPresetCategory] || [];
            }
            const vType = '{{ $vendorType }}';
            const primary = this.presets[vType] || this.presets['clothing'] || [];
            let others = [];
            Object.keys(this.presets).forEach(k => {
                if (k !== vType && Array.isArray(this.presets[k])) {
                    others = others.concat(this.presets[k]);
                }
            });
            return [...primary, ...others];
        },

        init() {
            this.generateClothingMatrix();
            this.generateFootwearMatrix();
            this.generateElecMatrix();
            this.$watch('form.base_price', (val) => {
                if (val && parseFloat(val) > 0) {
                    this.variants.forEach(v => {
                        if (!v.price || parseFloat(v.price) <= 10 || v._autoPrice) {
                            v.price = val;
                            v._autoPrice = true;
                        }
                    });
                    this.footwearVariants.forEach(v => {
                        if (!v.price || parseFloat(v.price) <= 10 || v._autoPrice) {
                            v.price = val;
                            v._autoPrice = true;
                        }
                    });
                    this.groceryVariants.forEach(g => {
                        if (!g.price || parseFloat(g.price) <= 10 || g._autoPrice) {
                            g.price = val;
                            g._autoPrice = true;
                        }
                    });
                    this.elecVariants.forEach(e => {
                        if (!e.price || parseFloat(e.price) <= 10 || e._autoPrice) {
                            e.price = val;
                            e._autoPrice = true;
                        }
                    });
                    this.foodPortions.forEach(p => {
                        if (!p.price || parseFloat(p.price) <= 10 || p._autoPrice) {
                            p.price = val;
                            p._autoPrice = true;
                        }
                    });
                }
            });
        },

        toggleClothingColor(col) {
            const idx = this.selectedColors.findIndex(c => c.name === col.name);
            if (idx > -1) {
                this.selectedColors.splice(idx, 1);
            } else {
                this.selectedColors.push(col);
            }
            this.generateClothingMatrix();
        },

        addCustomClothingColor() {
            if (!this.customColorName.trim()) return;
            const newCol = { name: this.customColorName.trim(), hex: this.customColorHex || '#111827' };
            this.availableColors.push(newCol);
            this.selectedColors.push(newCol);
            this.customColorName = '';
            this.generateClothingMatrix();
        },

        toggleClothingSize(sz) {
            const idx = this.selectedSizes.indexOf(sz);
            if (idx > -1) {
                this.selectedSizes.splice(idx, 1);
            } else {
                this.selectedSizes.push(sz);
            }
            this.generateClothingMatrix();
        },

        addCustomClothingSize() {
            if (!this.customSize.trim()) return;
            const sz = this.customSize.trim();
            if (!this.availableSizes.includes(sz)) this.availableSizes.push(sz);
            if (!this.selectedSizes.includes(sz)) this.selectedSizes.push(sz);
            this.customSize = '';
            this.generateClothingMatrix();
        },

        generateClothingMatrix() {
            if (!this.selectedColors.length || !this.selectedSizes.length) {
                this.variants = [];
                return;
            }
            const rows = [];
            const prefix = (this.form.title ? this.form.title.substring(0, 3).toUpperCase().replace(/[^A-Z]/g, '') : 'CLO') || 'CLO';
            this.selectedColors.forEach(col => {
                this.selectedSizes.forEach(sz => {
                    const cleanCol = col.name.substring(0, 3).toUpperCase().replace(/[^A-Z]/g, '');
                    const cleanSz = sz.replace(/[^A-Za-z0-9]/g, '').toUpperCase();
                    const sku = `${prefix}-${cleanCol}-${cleanSz}`;
                    const existing = this.variants.find(r => r.color === col.name && r.size === sz);
                    rows.push(existing || {
                        name: `${col.name} - ${sz}`,
                        color: col.name,
                        color_hex: col.hex,
                        size: sz,
                        sku: sku,
                        price: this.form.base_price || '',
                        stock: 25
                    });
                });
            });
            this.variants = rows;
        },

        applyBulkToClothing() {
            this.variants.forEach(v => {
                if (this.bulkPrice) v.price = this.bulkPrice;
                if (this.bulkStock !== '') v.stock = this.bulkStock;
            });
        },

        toggleShoeColor(col) {
            const idx = this.selectedShoeColors.findIndex(c => c.name === col.name);
            if (idx > -1) {
                this.selectedShoeColors.splice(idx, 1);
            } else {
                this.selectedShoeColors.push(col);
            }
            this.generateFootwearMatrix();
        },

        toggleShoeSize(sz) {
            const idx = this.selectedShoeSizes.indexOf(sz);
            if (idx > -1) {
                this.selectedShoeSizes.splice(idx, 1);
            } else {
                this.selectedShoeSizes.push(sz);
            }
            this.generateFootwearMatrix();
        },

        generateFootwearMatrix() {
            if (!this.selectedShoeColors.length || !this.selectedShoeSizes.length) {
                this.footwearVariants = [];
                return;
            }
            const rows = [];
            const prefix = (this.form.title ? this.form.title.substring(0, 3).toUpperCase().replace(/[^A-Z]/g, '') : 'SHOE') || 'SHOE';
            this.selectedShoeColors.forEach(col => {
                this.selectedShoeSizes.forEach(sz => {
                    const cleanCol = col.name.substring(0, 3).toUpperCase().replace(/[^A-Z]/g, '');
                    const cleanSz = sz.replace(/[^A-Za-z0-9]/g, '').toUpperCase();
                    const sku = `${prefix}-${cleanCol}-${cleanSz}`;
                    const existing = this.footwearVariants.find(r => r.color === col.name && r.size === sz);
                    rows.push(existing || {
                        name: `${col.name} - ${sz}`,
                        color: col.name,
                        color_hex: col.hex,
                        size: sz,
                        sku: sku,
                        price: this.form.base_price || '',
                        stock: 20
                    });
                });
            });
            this.footwearVariants = rows;
        },

        applyBulkToFootwear() {
            this.footwearVariants.forEach(v => {
                if (this.bulkShoePrice) v.price = this.bulkShoePrice;
                if (this.bulkShoeStock !== '') v.stock = this.bulkShoeStock;
            });
        },

        addGroceryPack(unit) {
            if (!this.groceryVariants.some(g => g.name === unit)) {
                this.groceryVariants.push({
                    name: unit,
                    size: unit,
                    price: this.form.base_price || '',
                    stock: 50,
                    sku: 'GROC-' + unit.replace(/[^A-Za-z0-9]/g, '').toUpperCase()
                });
            }
        },

        toggleElecRam(r) {
            const idx = this.selectedRams.indexOf(r);
            if (idx > -1) {
                this.selectedRams.splice(idx, 1);
            } else {
                this.selectedRams.push(r);
            }
            this.generateElecMatrix();
        },

        toggleElecStorage(st) {
            const idx = this.selectedStorages.indexOf(st);
            if (idx > -1) {
                this.selectedStorages.splice(idx, 1);
            } else {
                this.selectedStorages.push(st);
            }
            this.generateElecMatrix();
        },

        generateElecMatrix() {
            if (!this.selectedRams.length || !this.selectedStorages.length) {
                this.elecVariants = [];
                return;
            }
            const rows = [];
            const prefix = (this.form.title ? this.form.title.substring(0, 3).toUpperCase().replace(/[^A-Z]/g, '') : 'ELEC') || 'ELEC';
            this.selectedRams.forEach(r => {
                this.selectedStorages.forEach(st => {
                    const name = `${r} RAM + ${st} Storage`;
                    const cleanR = r.replace(/[^A-Za-z0-9]/g, '');
                    const cleanSt = st.replace(/[^A-Za-z0-9]/g, '');
                    const sku = `${prefix}-${cleanR}-${cleanSt}`;
                    const existing = this.elecVariants.find(e => e.name === name);
                    rows.push(existing || {
                        name: name,
                        size: name,
                        sku: sku,
                        price: this.form.base_price || '',
                        stock: 20
                    });
                });
            });
            this.elecVariants = rows;
        },

        addFoodPortion(name, price = '') {
            this.foodPortions.push({
                name: name || 'Portion',
                price: price || this.form.base_price || '',
                stock: 50
            });
        },

        addFoodModifier(name, delta = 20) {
            this.foodModifiers.push({
                group_name: 'Add-ons',
                modifier_name: name || 'Extra Item',
                price_delta: delta,
                is_required: false
            });
        },

        insertSpec(key, value) {
            const existing = this.form.specs.find(s => s.key === key);
            if (existing) {
                existing.value = value;
            } else {
                this.form.specs.push({ key, value });
            }
        },

        applyPreset(preset) {
            this.appliedPreset = preset.title;
            this.form.title = preset.title;
            this.form.brand = preset.brand;
            this.form.base_price = preset.base_price;
            this.form.compare_at_price = preset.compare_at_price;
            this.form.gst_rate = preset.gst_rate;
            this.form.hsn_code = preset.hsn_code;
            this.form.total_stock = preset.total_stock;
            this.form.sku = preset.sku;
            this.form.description = preset.description;
            if (preset.specs && preset.specs.length) {
                this.form.specs = JSON.parse(JSON.stringify(preset.specs));
            }
        },

        resetForm() {
            this.appliedPreset = null;
            this.form.title = '';
            this.form.brand = '';
            this.form.base_price = '';
            this.form.compare_at_price = '';
            this.form.description = '';
            this.form.specs = @json($sec['default_specs'] ?? [['key' => 'Specification', 'value' => 'Standard']]);
        },

        addSpecRow() {
            this.form.specs.push({ key: '', value: '' });
        },

        removeSpecRow(index) {
            this.form.specs.splice(index, 1);
        },

        handleVideoFile(event) {
            const file = event.target.files[0];
            if (!file) return;
            if (file.size > 50 * 1024 * 1024) {
                alert('Video must be under 50MB.');
                event.target.value = '';
                return;
            }
            this.videoPreviewUrl = URL.createObjectURL(file);
        },

        clearVideo() {
            this.videoPreviewUrl = '';
            this.videoUrl = '';
            const vidInput = document.querySelector('input[name="video"]');
            if (vidInput) vidInput.value = '';
        },

        handleFiles(event) {
            const files = event.target.files;
            this.processFiles(files);
        },

        handleDrop(event) {
            this.dragOver = false;
            const files = event.dataTransfer.files;
            this.processFiles(files);
        },

        processFiles(files) {
            if (!files || files.length === 0) return;
            for (let i = 0; i < files.length; i++) {
                const file = files[i];
                if (!file.type.match('image.*')) continue;
                if (file.size > 5 * 1024 * 1024) {
                    alert(`"${file.name}" is larger than 5MB. Please choose an image under 5MB.`);
                    continue;
                }
                this.previewImages.push({
                    file: file,
                    url: URL.createObjectURL(file),
                    name: file.name
                });
            }
            this.syncFiles();
        },

        makeCover(index) {
            if (index === 0 || !this.previewImages[index]) return;
            const item = this.previewImages.splice(index, 1)[0];
            this.previewImages.unshift(item);
            this.syncFiles();
        },

        moveImage(index, direction) {
            const newIndex = index + direction;
            if (newIndex < 0 || newIndex >= this.previewImages.length) return;
            const item = this.previewImages.splice(index, 1)[0];
            this.previewImages.splice(newIndex, 0, item);
            this.syncFiles();
        },

        removeImage(index) {
            this.previewImages.splice(index, 1);
            this.syncFiles();
        },

        syncFiles() {
            try {
                const dt = new DataTransfer();
                this.previewImages.forEach(img => {
                    if (img.file) {
                        dt.items.add(img.file);
                    }
                });
                if (this.$refs.fileInput) {
                    this.$refs.fileInput.files = dt.files;
                }
            } catch (e) {
                console.error("DataTransfer error", e);
            }
        },

        submitForm() {
            if (!this.form.title.trim()) {
                alert('Please enter a Product Title.');
                return;
            }
            if (!this.form.category_id) {
                alert('Please select a Category.');
                return;
            }
            if (!this.form.base_price || parseFloat(this.form.base_price) <= 0) {
                alert('Please enter a valid Selling Price.');
                return;
            }
            if (!this.form.description.trim()) {
                alert('Please enter a Product Description.');
                return;
            }

            // Fallback any empty or truncated variant price to form.base_price
            const baseP = parseFloat(this.form.base_price) || 0;
            if (this.variants && this.variants.length) {
                this.variants.forEach(v => {
                    if (!v.price || parseFloat(v.price) <= 0 || (parseFloat(v.price) <= 10 && baseP > 50)) {
                        v.price = this.form.base_price;
                    }
                });
            }
            if (this.footwearVariants && this.footwearVariants.length) {
                this.footwearVariants.forEach(v => {
                    if (!v.price || parseFloat(v.price) <= 0 || (parseFloat(v.price) <= 10 && baseP > 50)) {
                        v.price = this.form.base_price;
                    }
                });
            }

            if (this.activeVariantTab === 'enquiry') {
                this.form.listing_type = 'enquiry';
            }

            this.submitting = true;
            document.getElementById('productForm').submit();
        }
    };
}
</script>
</x-layouts.vendor>
