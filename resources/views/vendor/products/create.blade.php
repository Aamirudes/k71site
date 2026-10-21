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

    <!-- ⚡ 1-Tap Fast-Track Category Presets Bar -->
    <div class="relative z-10 mb-6">
        <div class="p-4 rounded-2xl bg-gradient-to-br from-indigo-950/40 via-slate-900/60 to-purple-950/40 backdrop-blur-xl border border-indigo-500/20 shadow-xl">
            <div class="flex items-center justify-between gap-3 mb-3 pb-2.5 border-b border-white/10">
                <div class="flex items-center gap-2">
                    <span class="w-6 h-6 rounded-lg bg-amber-400/20 text-amber-300 flex items-center justify-center text-xs font-black">⚡</span>
                    <span class="text-xs sm:text-sm font-bold text-white tracking-wide">
                        1-Tap {{ $sec['label'] ?? ucfirst($vendorType) }} Launch Presets
                    </span>
                    <span class="text-[10px] text-amber-400 font-mono font-semibold">Auto-Fill</span>
                </div>
                <template x-if="appliedPreset">
                    <div class="flex items-center gap-2 bg-emerald-500/20 text-emerald-300 border border-emerald-500/30 px-2.5 py-1 rounded-lg text-xs font-bold">
                        <span>Applied: <strong x-text="appliedPreset"></strong></span>
                        <button type="button" @click="resetForm()" class="text-rose-400 hover:text-rose-300 font-bold ml-1">✕</button>
                    </div>
                </template>
            </div>

            <!-- Scrollable Presets Grid -->
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-2.5">
                <template x-for="(preset, key) in currentSectorPresets" :key="key">
                    <button 
                        type="button" 
                        @click="applyPreset(preset)"
                        :class="appliedPreset === preset.title ? 'border-cyan-400 bg-cyan-500/20 text-white' : 'border-white/10 bg-white/5 hover:bg-white/10 text-slate-200'"
                        class="p-2.5 rounded-xl border text-left transition-all group flex flex-col justify-between cursor-pointer"
                    >
                        <div class="flex items-center justify-between mb-1.5">
                            <span class="text-base" x-text="preset.icon"></span>
                            <span class="text-[10px] font-mono font-bold text-cyan-300" x-text="'₹' + preset.base_price"></span>
                        </div>
                        <div class="text-xs font-bold truncate group-hover:text-cyan-300 transition-colors" x-text="preset.title"></div>
                        <div class="text-[10px] text-slate-400 truncate mt-0.5" x-text="preset.brand || 'Quick Preset'"></div>
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
                            placeholder="e.g. Apple iPhone 15 Pro 256GB Natural Titanium" 
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
                                Brand / Manufacturer
                            </label>
                            <input 
                                id="p-brand" 
                                name="brand" 
                                type="text" 
                                x-model="form.brand" 
                                placeholder="e.g. Apple, Bosch, Sony, Puma" 
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
                            placeholder="Detailed technical overview, features, what's inside the box, warranty terms..." 
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
                                <p class="text-xs text-slate-500 dark:text-slate-400">Structured specs displayed in the product specification table.</p>
                            </div>
                        </div>
                        <button type="button" @click="addSpecRow()" class="text-xs font-bold text-indigo-500 hover:text-indigo-400 flex items-center gap-1 cursor-pointer">
                            <span>+ Add Spec</span>
                        </button>
                    </div>

                    <!-- Dynamic Spec Key-Value Rows -->
                    <div class="space-y-2.5">
                        <template x-for="(spec, index) in form.specs" :key="index">
                            <div class="flex items-center gap-2.5">
                                <input 
                                    type="text" 
                                    x-model="spec.key" 
                                    :name="'specifications[' + index + '][name]'" 
                                    placeholder="Spec Key (e.g. RAM, Engine, Size)" 
                                    class="w-1/3 px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-950/60 border border-slate-200 dark:border-white/10 text-slate-900 dark:text-white text-xs font-semibold focus:ring-2 focus:ring-cyan-500 shadow-inner"
                                >
                                <input 
                                    type="text" 
                                    x-model="spec.value" 
                                    :name="'specifications[' + index + '][value]'" 
                                    placeholder="Value (e.g. 16GB LPDDR5X, 1.5L Turbo, XL)" 
                                    class="flex-1 px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-950/60 border border-slate-200 dark:border-white/10 text-slate-900 dark:text-white text-xs focus:ring-2 focus:ring-cyan-500 shadow-inner"
                                >
                                <button type="button" @click="removeSpecRow(index)" class="p-2 rounded-lg text-slate-400 hover:text-rose-500 transition-colors cursor-pointer" title="Remove specification">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </div>
                        </template>
                    </div>
                </div>

                <!-- 3. Drag & Drop Visual Media Uploader -->
                <div class="p-5 sm:p-7 rounded-3xl bg-white/80 dark:bg-slate-900/80 backdrop-blur-2xl border border-white/40 dark:border-white/10 shadow-xl space-y-4">
                    <div class="flex items-center justify-between pb-3 border-b border-slate-200 dark:border-white/10">
                        <div class="flex items-center gap-2.5">
                            <span class="w-7 h-7 rounded-xl bg-purple-500/10 text-purple-500 flex items-center justify-center font-bold text-xs">3</span>
                            <h2 class="text-base font-bold text-slate-900 dark:text-white">Product Gallery &amp; Photos</h2>
                        </div>
                        <span class="text-xs text-slate-500">JPG, PNG, WEBP up to 5MB</span>
                    </div>

                    <!-- Dropzone -->
                    <div 
                        @dragover.prevent="dragOver = true" 
                        @dragleave.prevent="dragOver = false" 
                        @drop.prevent="handleDrop($event)"
                        :class="dragOver ? 'border-indigo-500 bg-indigo-500/10' : 'border-dashed border-slate-300 dark:border-white/20 bg-slate-50/50 dark:bg-slate-950/40 hover:border-indigo-400'"
                        class="border-2 rounded-2xl p-8 text-center transition-all cursor-pointer relative"
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
                            <div class="w-12 h-12 rounded-2xl bg-indigo-500/10 text-indigo-500 flex items-center justify-center mb-3">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            </div>
                            <p class="text-sm font-bold text-slate-800 dark:text-slate-200">
                                Click to select images or drag and drop here
                            </p>
                            <p class="text-xs text-slate-400 mt-1">
                                High-resolution images showcase your item best on mobile and desktop.
                            </p>
                        </div>
                    </div>

                    <!-- Image Thumbnails Grid Preview -->
                    <div x-show="previewImages.length > 0" class="grid grid-cols-2 sm:grid-cols-4 gap-3 pt-2">
                        <template x-for="(img, idx) in previewImages" :key="idx">
                            <div class="relative rounded-xl overflow-hidden border border-white/20 shadow-md group aspect-square bg-slate-950">
                                <img :src="img.url" class="w-full h-full object-cover">
                                <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                                    <button type="button" @click.stop="removeImage(idx)" class="p-1.5 rounded-full bg-rose-600 text-white hover:bg-rose-500 transition-colors shadow">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                    </button>
                                </div>
                                <span x-show="idx === 0" class="absolute bottom-1.5 left-1.5 px-2 py-0.5 rounded bg-indigo-600/90 text-white text-[9px] font-bold uppercase tracking-wider">
                                    Cover
                                </span>
                            </div>
                        </template>
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
                            Selling Price (₹) <span class="text-rose-500">*</span>
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
                                placeholder="499.00" 
                                class="w-full pl-8 pr-4 py-3 rounded-xl bg-slate-50 dark:bg-slate-950/60 border border-slate-200 dark:border-white/10 text-slate-900 dark:text-white text-base font-bold focus:ring-2 focus:ring-emerald-500 shadow-inner"
                            >
                        </div>
                    </div>

                    <!-- MRP / Compare-at Price -->
                    <div>
                        <label for="p-compare" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                            Original MRP (₹) <span class="text-slate-400 font-normal">(Optional)</span>
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
                            <option value="18">18% GST (Standard Electronics &amp; Goods)</option>
                            <option value="0">0% (Nil / Exempt)</option>
                            <option value="5">5% GST (Essential Commodities / Apparel &lt;1000)</option>
                            <option value="12">12% GST (Processed Items / Select Hardware)</option>
                            <option value="28">28% GST (Luxury &amp; Auto Accessories)</option>
                        </select>
                    </div>

                    <!-- HSN Code -->
                    <div>
                        <label for="p-hsn" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                            HSN Code
                        </label>
                        <input 
                            id="p-hsn" 
                            name="hsn_code" 
                            type="text" 
                            maxlength="8" 
                            x-model="form.hsn_code" 
                            placeholder="e.g. 85171300" 
                            class="w-full px-4 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-950/60 border border-slate-200 dark:border-white/10 text-slate-900 dark:text-white text-xs font-mono focus:ring-2 focus:ring-emerald-500 shadow-inner"
                        >
                    </div>
                </div>

                <!-- 5. Inventory & Shipping -->
                <div class="p-5 sm:p-7 rounded-3xl bg-white/80 dark:bg-slate-900/80 backdrop-blur-2xl border border-white/40 dark:border-white/10 shadow-xl space-y-4">
                    <div class="flex items-center gap-2.5 pb-3 border-b border-slate-200 dark:border-white/10">
                        <span class="w-7 h-7 rounded-xl bg-blue-500/10 text-blue-500 flex items-center justify-center font-bold text-xs">5</span>
                        <h2 class="text-base font-bold text-slate-900 dark:text-white">Inventory &amp; Shipping</h2>
                    </div>

                    <!-- Total Stock Quantity -->
                    <div>
                        <label for="p-stock" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                            Available Stock Units <span class="text-rose-500">*</span>
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
                            SKU Code
                        </label>
                        <input 
                            id="p-sku" 
                            name="sku" 
                            type="text" 
                            x-model="form.sku" 
                            placeholder="PROD-TECH-001" 
                            class="w-full px-4 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-950/60 border border-slate-200 dark:border-white/10 text-slate-900 dark:text-white text-xs font-mono focus:ring-2 focus:ring-blue-500 shadow-inner"
                        >
                    </div>

                    <!-- Weight in Grams -->
                    <div>
                        <label for="p-weight" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                            Package Weight (Grams)
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
        form: {
            title: '{{ old('title', '') }}',
            category_id: '{{ old('category_id', $categories->first()?->id ?? '') }}',
            vertical: '{{ $targetVertical }}',
            listing_type: 'purchase',
            brand: '{{ old('brand', '') }}',
            description: '{{ old('description', '') }}',
            base_price: '{{ old('base_price', '') }}',
            compare_at_price: '{{ old('compare_at_price', '') }}',
            gst_rate: '{{ old('gst_rate', '18') }}',
            hsn_code: '{{ old('hsn_code', '') }}',
            total_stock: '{{ old('total_stock', '25') }}',
            sku: '{{ old('sku', '') }}',
            weight_grams: '{{ old('weight_grams', '500') }}',
            is_returnable: true,
            status: 'active',
            specs: [
                { key: 'Warranty', value: '1 Year Manufacturer Warranty' },
                { key: 'Condition', value: 'Brand New (Sealed Box)' },
            ]
        },

        // Fast Presets by Vertical
        presets: {
            electronics: [
                {
                    icon: '📱',
                    title: '5G Smartphone 256GB',
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
                }
            ],
            clothing: [
                {
                    icon: '👕',
                    title: 'Heavyweight 240 GSM Cotton T-Shirt',
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
                }
            ]
        },

        get currentSectorPresets() {
            const vType = '{{ $vendorType }}';
            return this.presets[vType] || this.presets['electronics'] || this.presets['clothing'];
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
            this.form.specs = [
                { key: 'Warranty', value: '1 Year Manufacturer Warranty' }
            ];
        },

        addSpecRow() {
            this.form.specs.push({ key: '', value: '' });
        },

        removeSpecRow(index) {
            this.form.specs.splice(index, 1);
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
            for (let i = 0; i < files.length; i++) {
                const file = files[i];
                if (!file.type.match('image.*')) continue;
                const reader = new FileReader();
                reader.onload = (e) => {
                    this.previewImages.push({
                        url: e.target.result,
                        name: file.name
                    });
                };
                reader.readAsDataURL(file);
            }
        },

        removeImage(index) {
            this.previewImages.splice(index, 1);
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

            this.submitting = true;
            document.getElementById('productForm').submit();
        }
    };
}
</script>
</x-layouts.vendor>
