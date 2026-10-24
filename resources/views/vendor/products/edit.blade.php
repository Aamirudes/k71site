<x-layouts.vendor title="Edit Product">
    @php
        $sec = $sectorConfig ?? ($vendor ? $vendor->getSectorConfig() : []);
        $resolvedVerticalType = old('vertical_type', $product->category?->vertical_type ?? match ($targetVertical) {
            'marketplace' => 'fashion_matrix',
            'food' => 'food_portion',
            'services' => 'service_booking',
            'used' => 'classified_asset',
            default => 'standard_retail',
        });
    @endphp
    <form action="{{ route('vendor.products.update', $product) }}" method="POST" enctype="multipart/form-data" class="relative" x-data="variantEngine()">
        @csrf
        @method('PUT')

        <!-- Sticky Save Bar -->
        <div class="sticky top-0 z-40 bg-white/95 backdrop-blur-md border-b border-gray-200 px-4 sm:px-6 py-3 sm:py-4 flex items-center justify-between gap-3 mb-6 sm:mb-8 shadow-sm -mx-4 sm:-mx-6 lg:-mx-8">
            <div class="flex items-center gap-2.5 sm:gap-4 min-w-0">
                <a href="{{ route('vendor.products.index') }}" class="w-8 h-8 shrink-0 flex items-center justify-center rounded-full bg-gray-100 text-gray-500 hover:bg-gray-200 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                </a>
                <h1 class="text-base sm:text-2xl font-bold text-gray-900 tracking-tight truncate">Edit Product: {{ $product->title }}</h1>
            </div>
            <div class="flex items-center gap-2 sm:gap-3 shrink-0">
                <a href="{{ route('vendor.products.index') }}" class="text-xs sm:text-sm font-medium text-gray-600 hover:text-gray-900 px-2 sm:px-4 py-2">Discard</a>
                <button type="submit" class="px-4 sm:px-6 py-2 sm:py-2.5 bg-brand-text text-white text-xs sm:text-sm font-bold rounded-xl shadow hover:bg-black transition-colors flex items-center gap-1.5 sm:gap-2 active:scale-95 cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    <span>Update Product</span>
                </button>
            </div>
        </div>

        <div class="max-w-6xl mx-auto grid grid-cols-1 lg:grid-cols-3 gap-8 pb-24">
            
            <!-- LEFT COLUMN: Heavy Content -->
            <div class="lg:col-span-2 space-y-8">
                
                <!-- 0. K71 Marketplace Vertical & Listing Workflow -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 sm:p-8 space-y-6">
                    <div class="flex items-center justify-between pb-4 border-b border-gray-100">
                        <div class="flex items-center gap-2.5">
                            <div class="w-8 h-8 rounded-xl bg-rose-50 text-rose-600 border border-rose-200/60 flex items-center justify-center font-black text-sm">
                                0
                            </div>
                            <div>
                                <h2 class="text-base sm:text-lg font-bold text-gray-900 tracking-tight">K71 Vertical & Listing Workflow</h2>
                                <p class="text-xs text-gray-500">Pick which vertical experience this listing belongs to and its transaction mode.</p>
                            </div>
                        </div>
                        <span class="text-[10px] font-bold text-rose-700 bg-rose-50 px-2.5 py-1 rounded-full border border-rose-200 uppercase tracking-wider">Locked Store Type</span>
                    </div>

                    <!-- Step 0.1: Locked Store Vertical -->
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <label class="block text-xs font-black uppercase tracking-wider text-stone-700">
                                Store Business Type <span class="text-stone-400 font-normal">(Locked to Store Profile)</span>
                            </label>
                            <span class="text-[10px] text-brand-sage font-bold">100% Isolated Experience</span>
                        </div>
                        <input type="hidden" name="vertical" value="{{ $targetVertical }}">

                        @php
                            $vBadges = [
                                'food' => ['icon' => '🍽️', 'name' => 'Cloud Kitchen & Digital Menu', 'badge' => 'Food & Dining', 'desc' => 'Dish menus, kitchen orders, tamper-proof packaging, pure veg/non-veg, and fast local dispatch.'],
                                'services' => ['icon' => '🛠️', 'name' => 'Doorstep Services & Repairs', 'badge' => 'Home & Commercial', 'desc' => 'Doorstep appointments, hourly & BHK service tiers, verified technicians, and satisfaction guarantees.'],
                                'used' => ['icon' => '♻️', 'name' => 'Pre-Owned & Certified Refurbished', 'badge' => 'Second-Hand / P2P', 'desc' => 'Condition grading, inspection checklist, original accessories, and buyer testing warranty.'],
                                'marketplace' => ['icon' => '🛍️', 'name' => 'Clothing, Apparel & Lifestyle Retail', 'badge' => 'Fashion & Goods', 'desc' => 'Apparel size matrices, fabric compositions, wash care guides, and courier logistics.'],
                            ];
                            $currV = $vBadges[$targetVertical] ?? $vBadges['marketplace'];
                        @endphp

                        <div class="p-4 rounded-xl border-2 border-stone-800 bg-stone-900 text-white flex items-center justify-between gap-4 shadow-sm">
                            <div class="flex items-center gap-3.5">
                                <span class="text-3xl">{{ $currV['icon'] }}</span>
                                <div>
                                    <div class="flex items-center gap-2">
                                        <h4 class="text-sm font-bold text-white tracking-tight">{{ $currV['name'] }}</h4>
                                        <span class="text-[10px] font-black uppercase px-2 py-0.5 rounded bg-white/20 text-white">{{ $currV['badge'] }}</span>
                                    </div>
                                    <p class="text-xs text-stone-300 mt-0.5">{{ $currV['desc'] }}</p>
                                </div>
                            </div>
                            <span class="text-[11px] font-bold text-emerald-400 bg-emerald-950/80 px-2.5 py-1 rounded-full border border-emerald-500/30 shrink-0">
                                Active Storefront
                            </span>
                        </div>
                    </div>

                    <!-- Step 0.2: Listing Type Selector -->
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <label class="block text-xs font-black uppercase tracking-wider text-stone-700">
                                Permitted Transaction / Listing Type <span class="text-red-500">*</span>
                            </label>
                            <span class="text-[10px] text-stone-500">Enforced strictly on cart & checkout</span>
                        </div>
                        <input type="hidden" name="listing_type" :value="listingType">
                        <div class="grid grid-cols-1 sm:grid-cols-5 gap-2">
                            <template x-for="(ltInfo, ltKey) in listingTypesList" :key="ltKey">
                                <button type="button" @click="listingType = ltKey"
                                        :class="listingType === ltKey ? 'bg-rose-600 text-white shadow-sm ring-2 ring-rose-600/20 font-bold' : 'bg-stone-50 hover:bg-stone-100 text-stone-700 border border-stone-200'"
                                        class="p-2.5 rounded-xl text-left transition-all cursor-pointer">
                                    <div class="flex items-center justify-between gap-1 mb-1">
                                        <span class="text-xs font-bold" x-text="ltInfo.title"></span>
                                        <span class="text-[8px] uppercase font-black px-1.5 py-0.5 rounded"
                                              :class="listingType === ltKey ? 'bg-white/20 text-white' : 'bg-stone-200 text-stone-600'" x-text="ltInfo.badge"></span>
                                    </div>
                                    <div class="text-[9px] opacity-80 leading-tight" x-text="ltInfo.description"></div>
                                </button>
                            </template>
                        </div>
                    </div>

                    <!-- Step 0.3: Dynamic Category Fields for Vertical -->
                    <div x-show="currentFields.length > 0" class="pt-2 border-t border-gray-100">
                        <div class="flex items-center gap-2 mb-3">
                            <span class="text-sm">⚙️</span>
                            <h3 class="text-xs font-black uppercase tracking-wider text-stone-800">
                                <span class="capitalize" x-text="vertical"></span> Specifications & Category Attributes
                            </h3>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 bg-stone-50/70 p-4 rounded-xl border border-stone-200/60">
                            <template x-for="f in currentFields" :key="f.name">
                                <div :class="f.field_type === 'textarea' ? 'sm:col-span-2' : ''">
                                    <label class="block text-xs font-bold text-stone-700 mb-1">
                                        <span x-text="f.label"></span>
                                        <span x-show="f.is_required" class="text-red-500">*</span>
                                    </label>
                                    
                                    <!-- Select Field -->
                                    <template x-if="f.field_type === 'select'">
                                        <select :name="'custom_fields[' + f.name + ']'"
                                                x-model="customFields[f.name]"
                                                :required="f.is_required"
                                                class="block w-full rounded-xl border-gray-200 shadow-xs focus:border-brand-sage focus:ring-brand-sage text-xs py-2 px-3 font-medium bg-white">
                                            <option value="">-- Select option --</option>
                                            <template x-for="opt in (typeof f.options === 'string' ? JSON.parse(f.options) : f.options)" :key="opt">
                                                <option :value="opt" x-text="opt" :selected="customFields[f.name] == opt"></option>
                                            </template>
                                        </select>
                                    </template>

                                    <!-- Text Field -->
                                    <template x-if="f.field_type === 'text'">
                                        <input type="text"
                                               :name="'custom_fields[' + f.name + ']'"
                                               x-model="customFields[f.name]"
                                               :placeholder="f.placeholder || ''"
                                               :required="f.is_required"
                                               class="block w-full rounded-xl border-gray-200 shadow-xs focus:border-brand-sage focus:ring-brand-sage text-xs py-2 px-3 font-medium bg-white">
                                    </template>

                                    <!-- Number Field -->
                                    <template x-if="f.field_type === 'number'">
                                        <input type="number"
                                               :name="'custom_fields[' + f.name + ']'"
                                               x-model="customFields[f.name]"
                                               :placeholder="f.placeholder || ''"
                                               :required="f.is_required"
                                               class="block w-full rounded-xl border-gray-200 shadow-xs focus:border-brand-sage focus:ring-brand-sage text-xs py-2 px-3 font-medium bg-white">
                                    </template>

                                    <!-- Textarea Field -->
                                    <template x-if="f.field_type === 'textarea'">
                                        <textarea :name="'custom_fields[' + f.name + ']'"
                                                  x-model="customFields[f.name]"
                                                  :placeholder="f.placeholder || ''"
                                                  :required="f.is_required"
                                                  rows="2"
                                                  class="block w-full rounded-xl border-gray-200 shadow-xs focus:border-brand-sage focus:ring-brand-sage text-xs py-2 px-3 font-medium bg-white"></textarea>
                                    </template>

                                    <p x-show="f.help_text" class="text-[10px] text-stone-500 mt-1" x-text="f.help_text"></p>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>

                <!-- 1. Basic Information -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 sm:p-8">
                    <div class="flex items-center gap-2 mb-6">
                        <div class="w-8 h-8 rounded-lg bg-blue-50 flex items-center justify-center text-blue-600">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                        </div>
                        <h2 class="text-lg font-bold text-gray-900">Basic Details</h2>
                    </div>

                    <div class="space-y-5">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">{{ $sec['title_label'] ?? 'Product Title' }} *</label>
                            <input type="text" name="title" value="{{ old('title', $product->title) }}" placeholder="{{ $sec['title_placeholder'] ?? 'e.g. Premium Item Title' }}" required class="block w-full rounded-xl border-gray-200 shadow-sm focus:border-brand-sage focus:ring-brand-sage sm:text-sm py-2.5 px-3">
                            @error('title') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <div class="flex items-center justify-between mb-1">
                                <label class="block text-sm font-semibold text-gray-700">Description *</label>
                                <span class="text-xs text-gray-400">Include key selling points</span>
                            </div>
                            <textarea name="description" rows="6" placeholder="{{ $sec['desc_placeholder'] ?? 'Describe key features, specifications, and details...' }}" required class="block w-full rounded-xl border-gray-200 shadow-sm focus:border-brand-sage focus:ring-brand-sage sm:text-sm p-3">{{ old('description', $product->description) }}</textarea>
                            @error('description') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>

                <!-- 2. Vertical-Tailored Attributes & Specifications -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 sm:p-8">
                    <div class="flex items-center gap-2 mb-6">
                        <div class="w-8 h-8 rounded-lg bg-emerald-50 flex items-center justify-center text-emerald-600">
                            @if($targetVertical === 'food')
                                <span class="text-base">🍽️</span>
                            @elseif($targetVertical === 'services')
                                <span class="text-base">🛠️</span>
                            @elseif($targetVertical === 'used')
                                <span class="text-base">♻️</span>
                            @else
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                            @endif
                        </div>
                        <div>
                            <h2 class="text-lg font-bold text-gray-900">
                                @if($targetVertical === 'food')
                                    Culinary Profile, Dietary & Ingredients
                                @elseif($targetVertical === 'services')
                                    Service Scope, Tools & Client Prerequisites
                                @elseif($targetVertical === 'used')
                                    Cosmetic Condition, Testing & Inclusions
                                @else
                                    Fabric, Craft & Accordion Details
                                @endif
                            </h2>
                            <p class="text-xs text-gray-500">
                                @if($targetVertical === 'food')
                                    Ingredients, dietary profile, and freshness guidance for customers.
                                @elseif($targetVertical === 'services')
                                    Tools, technician qualifications, and preparation advice.
                                @elseif($targetVertical === 'used')
                                    Condition grading, testing checklist, and accessories included.
                                @else
                                    These details display directly in the customer product page accordions.
                                @endif
                            </p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">
                                @if($targetVertical === 'food')
                                    Key Ingredients & Dietary Profile
                                @elseif($targetVertical === 'services')
                                    Tools & Equipment Deployed
                                @elseif($targetVertical === 'used')
                                    Cosmetic Grade & Condition
                                @else
                                    Fabric / Textile Material
                                @endif
                            </label>
                            <input type="text" id="edit_material" name="material" value="{{ old('material', $product->material) }}" 
                                   placeholder="@if($targetVertical === 'food') e.g. Aged Basmati Rice, Desi Ghee, Saffron, Star Anise @elseif($targetVertical === 'services') e.g. Insulated Hand Tools, Digital Multi-Meter, Drill @elseif($targetVertical === 'used') e.g. Pristine Mint 9/10, Minor Bezel Scratches @else e.g. Pure Chanderi Silk, Handloom Cotton, Georgette @endif" 
                                   class="block w-full rounded-xl border-gray-200 shadow-sm focus:border-brand-sage focus:ring-brand-sage sm:text-sm py-2.5 px-3">
                            <p class="text-[11px] text-gray-400 mt-1">Displays in product specification accordions.</p>
                            <div class="mt-2 flex flex-wrap gap-1.5">
                                <span class="text-[10px] uppercase font-bold text-gray-400 block w-full">Quick Pick:</span>
                                @if($targetVertical === 'food')
                                    @foreach(['Pure Desi Ghee', 'Farm Fresh Paneer', 'Aged Basmati Rice', 'Saffron Aroma', 'Low Oil / Healthy', 'Zero Preservatives'] as $fp)
                                        <button type="button" onclick="document.getElementById('edit_material').value = '{{ $fp }}'" class="text-[11px] px-2 py-0.5 rounded-lg bg-gray-100 hover:bg-gray-200 text-gray-700 transition-colors">{{ $fp }}</button>
                                    @endforeach
                                @elseif($targetVertical === 'services')
                                    @foreach(['Insulated Hand Tools', 'Rotary Scrubbing Machine', 'Safe Eco-Friendly Chemicals', 'Precision Multi-Meter'] as $fp)
                                        <button type="button" onclick="document.getElementById('edit_material').value = '{{ $fp }}'" class="text-[11px] px-2 py-0.5 rounded-lg bg-gray-100 hover:bg-gray-200 text-gray-700 transition-colors">{{ $fp }}</button>
                                    @endforeach
                                @elseif($targetVertical === 'used')
                                    @foreach(['Pristine / Like New', 'Minor Signs of Use', 'Tested Grade A', 'Original Box Included'] as $fp)
                                        <button type="button" onclick="document.getElementById('edit_material').value = '{{ $fp }}'" class="text-[11px] px-2 py-0.5 rounded-lg bg-gray-100 hover:bg-gray-200 text-gray-700 transition-colors">{{ $fp }}</button>
                                    @endforeach
                                @else
                                    @foreach(['Pure Cotton', 'Chanderi Silk', 'Banarasi Silk', 'Georgette', 'Rayon', 'Linen', 'Denim', 'Mulmul'] as $fp)
                                        <button type="button" onclick="document.getElementById('edit_material').value = '{{ $fp }}'" class="text-[11px] px-2 py-0.5 rounded-lg bg-gray-100 hover:bg-gray-200 text-gray-700 transition-colors">{{ $fp }}</button>
                                    @endforeach
                                @endif
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">
                                @if($targetVertical === 'food')
                                    Storage, Shelf Life & Serving Advice
                                @elseif($targetVertical === 'services')
                                    Client Prerequisites & Advice
                                @elseif($targetVertical === 'used')
                                    Testing & Warranty Advice
                                @else
                                    Wash Care & Maintenance
                                @endif
                            </label>
                            <input type="text" id="edit_care" name="care_instructions" value="{{ old('care_instructions', $product->care_instructions) }}" 
                                   placeholder="@if($targetVertical === 'food') e.g. Best served hot upon arrival. Consume within 4 hours. @elseif($targetVertical === 'services') e.g. Ensure technician has access to power and main switch. @elseif($targetVertical === 'used') e.g. 3-day return testing window. Clean serial number verified. @else e.g. Dry clean recommended. Store in muslin cloth. @endif" 
                                   class="block w-full rounded-xl border-gray-200 shadow-sm focus:border-brand-sage focus:ring-brand-sage sm:text-sm py-2.5 px-3">
                            <p class="text-[11px] text-gray-400 mt-1">Customer guidance and recommendations.</p>
                            <div class="mt-2 flex flex-wrap gap-1.5">
                                <span class="text-[10px] uppercase font-bold text-gray-400 block w-full">Quick Pick:</span>
                                @if($targetVertical === 'food')
                                    @foreach(['Best served hot', 'Consume within 4 hours', 'Keep refrigerated', 'Microwave safe bowl', 'Tamper evident seal'] as $cp)
                                        <button type="button" onclick="document.getElementById('edit_care').value = '{{ $cp }}'" class="text-[11px] px-2 py-0.5 rounded-lg bg-gray-100 hover:bg-gray-200 text-gray-700 transition-colors">{{ $cp }}</button>
                                    @endforeach
                                @elseif($targetVertical === 'services')
                                    @foreach(['Clear work area before arrival', 'Mains power access required', 'Spares charged at actuals', '30-day labor warranty'] as $cp)
                                        <button type="button" onclick="document.getElementById('edit_care').value = '{{ $cp }}'" class="text-[11px] px-2 py-0.5 rounded-lg bg-gray-100 hover:bg-gray-200 text-gray-700 transition-colors">{{ $cp }}</button>
                                    @endforeach
                                @elseif($targetVertical === 'used')
                                    @foreach(['32-point inspection passed', '3-day testing warranty', 'Clean IMEI & iCloud reset', 'Original charger included'] as $cp)
                                        <button type="button" onclick="document.getElementById('edit_care').value = '{{ $cp }}'" class="text-[11px] px-2 py-0.5 rounded-lg bg-gray-100 hover:bg-gray-200 text-gray-700 transition-colors">{{ $cp }}</button>
                                    @endforeach
                                @else
                                    @foreach(['Dry clean recommended', 'Machine wash cold', 'Gentle hand wash', 'Store in muslin cloth'] as $cp)
                                        <button type="button" onclick="document.getElementById('edit_care').value = '{{ $cp }}'" class="text-[11px] px-2 py-0.5 rounded-lg bg-gray-100 hover:bg-gray-200 text-gray-700 transition-colors">{{ $cp }}</button>
                                    @endforeach
                                @endif
                            </div>
                        </div>

                        @if($targetVertical === 'marketplace')
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Gender / Silhouette</label>
                            <select name="gender" class="block w-full rounded-xl border-gray-200 shadow-sm focus:border-brand-sage focus:ring-brand-sage sm:text-sm py-2.5 px-3">
                                <option value="women" {{ old('gender', $product->gender) === 'women' ? 'selected' : '' }}>Women</option>
                                <option value="men" {{ old('gender', $product->gender) === 'men' ? 'selected' : '' }}>Men</option>
                                <option value="unisex" {{ old('gender', $product->gender) === 'unisex' ? 'selected' : '' }}>Unisex</option>
                                <option value="kids" {{ old('gender', $product->gender) === 'kids' ? 'selected' : '' }}>Kids</option>
                            </select>
                        </div>
                        @endif

                        <div class="{{ $targetVertical === 'marketplace' ? '' : 'sm:col-span-2' }}">
                            <label class="block text-sm font-semibold text-gray-700 mb-1">
                                {{ $sec['brand_label'] ?? 'Brand / Manufacturer' }}
                            </label>
                            <input type="text" name="brand" value="{{ old('brand', $product->brand) }}" 
                                   placeholder="{{ $sec['brand_placeholder'] ?? 'e.g. Brand, Builder, or Studio' }}" 
                                   class="block w-full rounded-xl border-gray-200 shadow-sm focus:border-brand-sage focus:ring-brand-sage sm:text-sm py-2.5 px-3">
                        </div>
                    </div>
                </div>

                @php
                    $initialImages = $product->images->sortBy('sort_order')->values()->map(function($img) {
                        return [
                            'id' => $img->id,
                            'isExisting' => true,
                            'url' => asset('storage/' . $img->path),
                            'name' => basename($img->path),
                        ];
                    });
                @endphp

                <!-- 2. Interactive Media Gallery -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 sm:p-8" x-data="editMediaGalleryEngine({{ json_encode($initialImages) }})">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center gap-2">
                            <div class="w-8 h-8 rounded-lg bg-purple-50 flex items-center justify-center text-purple-600">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                            </div>
                            <div>
                                <h2 class="text-lg font-bold text-gray-900">Media Gallery</h2>
                                <p class="text-xs text-gray-500">First photo will be your <strong>Primary Storefront & Catalog Cover</strong>.</p>
                            </div>
                        </div>
                        <template x-if="items.length > 0">
                            <span class="text-xs font-semibold px-2.5 py-1 rounded-full bg-purple-50 text-purple-700 border border-purple-100">
                                <span x-text="items.length"></span> Photos Active
                            </span>
                        </template>
                    </div>

                    <!-- Hidden Real File Input for New Uploads -->
                    <input id="images-input" name="images[]" type="file" multiple class="sr-only" accept="image/png, image/jpeg, image/webp"
                           @change="handleFiles($event.target.files)">

                    <!-- Tracking Deleted Existing Images -->
                    <template x-for="delId in deletedIds" :key="delId">
                        <input type="hidden" name="deleted_image_ids[]" :value="delId">
                    </template>

                    <!-- Tracking Overall Reordered Sequence -->
                    <template x-for="(item, idx) in items" :key="item.id">
                        <input type="hidden" name="overall_image_order[]" :value="item.isExisting ? ('existing:' + item.id) : ('new:' + item.newIndex)">
                    </template>

                    <!-- Drag & Drop Zone (shown when empty) -->
                    <div x-show="items.length === 0"
                         @dragover.prevent="isDragging = true"
                         @dragleave.prevent="isDragging = false"
                         @drop.prevent="isDragging = false; handleFiles($event.dataTransfer.files)"
                         :class="isDragging ? 'border-brand-sage bg-emerald-50/40' : 'border-gray-300 bg-gray-50/50'"
                         class="mt-1 flex justify-center px-6 pt-8 pb-10 border-2 border-dashed rounded-xl hover:border-brand-sage transition-all">
                        <div class="space-y-3 text-center">
                            <div class="w-14 h-14 mx-auto rounded-full bg-purple-50 text-purple-600 flex items-center justify-center">
                                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                            </div>
                            <div class="flex text-sm text-gray-600 justify-center">
                                <label for="images-input" class="relative cursor-pointer bg-brand-text text-white rounded-xl font-bold text-xs px-4 py-2 shadow-xs hover:bg-black transition-transform active:scale-95">
                                    <span>Browse Photos</span>
                                </label>
                            </div>
                            <p class="text-xs text-gray-500 font-medium">Drag & drop product photos or click Browse (PNG, JPG, WEBP up to 5MB)</p>
                        </div>
                    </div>

                    <!-- Interactive Photos Preview & Reordering Grid -->
                    <div x-show="items.length > 0" class="space-y-4">
                        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3.5 pt-2">
                            <template x-for="(item, idx) in items" :key="item.id">
                                <div class="relative group rounded-xl overflow-hidden border-2 transition-all duration-200 bg-stone-100 flex flex-col"
                                     :class="idx === 0 ? 'border-amber-500 ring-2 ring-amber-400/30 shadow-md' : 'border-gray-200 hover:border-gray-400 shadow-2xs'">
                                    
                                    <!-- Image Preview Thumbnail -->
                                    <div class="relative aspect-[3/4] w-full bg-stone-50 overflow-hidden">
                                        <img :src="item.url" :alt="item.name" class="w-full h-full object-cover">
                                        
                                        <!-- Order Badge -->
                                        <div class="absolute top-2 left-2 z-10 flex items-center gap-1">
                                            <template x-if="idx === 0">
                                                <span class="inline-flex items-center gap-1 bg-amber-500 text-white text-[10px] font-black px-2 py-0.5 rounded-md shadow-sm uppercase tracking-wider">
                                                    ★ Cover
                                                </span>
                                            </template>
                                            <template x-if="idx > 0">
                                                <span class="inline-flex items-center bg-black/70 backdrop-blur-sm text-white text-[10px] font-bold px-2 py-0.5 rounded-md" x-text="'#' + (idx + 1)"></span>
                                            </template>
                                            <template x-if="!item.isExisting">
                                                <span class="inline-flex items-center bg-emerald-600 text-white text-[9px] font-bold px-1.5 py-0.5 rounded-md">NEW</span>
                                            </template>
                                        </div>

                                        <!-- Remove Button -->
                                        <button type="button" 
                                                @click="remove(idx)"
                                                class="absolute top-2 right-2 w-7 h-7 rounded-full bg-white/90 text-rose-600 hover:bg-rose-600 hover:text-white flex items-center justify-center transition-colors shadow-sm z-10 cursor-pointer"
                                                title="Remove Photo">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                        </button>
                                    </div>

                                    <!-- Bottom Action Controls: Reorder & Make Cover -->
                                    <div class="p-2 bg-white flex items-center justify-between gap-1 border-t border-gray-100 text-xs">
                                        <template x-if="idx > 0">
                                            <button type="button" 
                                                    @click="makeCover(idx)" 
                                                    class="text-[11px] font-bold text-amber-700 hover:text-amber-900 bg-amber-50 hover:bg-amber-100 px-2 py-1 rounded-md transition-colors truncate"
                                                    title="Set as First Photo">
                                                Set Cover
                                            </button>
                                        </template>
                                        <template x-if="idx === 0">
                                            <span class="text-[10px] font-bold text-amber-600 uppercase tracking-wider pl-1">Primary</span>
                                        </template>

                                        <div class="flex items-center gap-1 ml-auto">
                                            <button type="button" 
                                                    @click="moveLeft(idx)" 
                                                    :disabled="idx === 0"
                                                    :class="idx === 0 ? 'opacity-25 cursor-not-allowed' : 'hover:bg-gray-100 text-gray-700'"
                                                    class="p-1 rounded-md transition-colors"
                                                    title="Move Left">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
                                            </button>
                                            <button type="button" 
                                                    @click="moveRight(idx)" 
                                                    :disabled="idx === items.length - 1"
                                                    :class="idx === items.length - 1 ? 'opacity-25 cursor-not-allowed' : 'hover:bg-gray-100 text-gray-700'"
                                                    class="p-1 rounded-md transition-colors"
                                                    title="Move Right">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </div>

                        <!-- Add More Photos Bar & Helper Note -->
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pt-3 border-t border-gray-100">
                            <label for="images-input" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-800 text-xs font-bold cursor-pointer transition-colors w-fit">
                                <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                                <span>+ Add More Photos</span>
                            </label>
                            <p class="text-[11px] text-gray-500">
                                💡 <strong>Tip:</strong> Use the arrows or <strong>Set Cover</strong> to control the photo order. The cover image appears first on your storefront and searches.
                            </p>
                    </div>

                    @error('images.*') <p class="text-xs text-red-600 mt-2">{{ $message }}</p> @enderror
                </div>

                <!-- 3. Universal Dynamic Variant & Attribute Engine -->
                @include('vendor.products.partials.dynamic_variant_engine')


                @if($targetVertical === 'marketplace')
                <!-- 4. Garment Fit & Size Guide Builder -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 sm:p-8">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between mb-6 gap-4">
                        <div class="flex items-center gap-2">
                            <div class="w-8 h-8 rounded-lg bg-teal-50 flex items-center justify-center text-teal-600">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                            </div>
                            <div>
                                <h2 class="text-lg font-bold text-gray-900">Garment Fit & Size Guide</h2>
                                <p class="text-xs text-gray-500">Customize the measurement table shown to customers for this specific product</p>
                            </div>
                        </div>
                        
                        <span class="text-xs font-bold px-3 py-1 rounded-full bg-teal-50 text-teal-700 border border-teal-200" x-text="sizeGuide.type === 'none' ? 'No Size Guide' : 'Active: ' + sizeGuide.title"></span>
                    </div>

                    <input type="hidden" name="size_chart" :value="JSON.stringify(sizeGuide)">

                    <!-- Preset Template Buttons -->
                    <div class="mb-6">
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">Select Garment Category Preset:</label>
                        <div class="flex flex-wrap gap-2">
                            <button type="button" @click="applyPreset('shirt')" 
                                    :class="sizeGuide.type === 'shirt' ? 'bg-brand-sage text-white shadow-xs' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'"
                                    class="px-3 py-1.5 rounded-xl text-xs font-bold transition-all flex items-center gap-1.5">
                                <span>👔</span> Shirts, Kurtas & Tops
                            </button>
                            <button type="button" @click="applyPreset('jeans')" 
                                    :class="sizeGuide.type === 'jeans' ? 'bg-brand-sage text-white shadow-xs' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'"
                                    class="px-3 py-1.5 rounded-xl text-xs font-bold transition-all flex items-center gap-1.5">
                                <span>👖</span> Jeans & Bottoms
                            </button>
                            <button type="button" @click="applyPreset('kurti')" 
                                    :class="sizeGuide.type === 'kurti' ? 'bg-brand-sage text-white shadow-xs' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'"
                                    class="px-3 py-1.5 rounded-xl text-xs font-bold transition-all flex items-center gap-1.5">
                                <span>🥻</span> Kurtis & Ethnic Wear
                            </button>
                            <button type="button" @click="applyPreset('saree')" 
                                    :class="sizeGuide.type === 'saree' ? 'bg-brand-sage text-white shadow-xs' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'"
                                    class="px-3 py-1.5 rounded-xl text-xs font-bold transition-all flex items-center gap-1.5">
                                <span>🧵</span> Sarees & Drapes (Free Size)
                            </button>
                            <button type="button" @click="applyPreset('kids')" 
                                    :class="sizeGuide.type === 'kids' ? 'bg-brand-sage text-white shadow-xs' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'"
                                    class="px-3 py-1.5 rounded-xl text-xs font-bold transition-all flex items-center gap-1.5">
                                <span>👶</span> Kids Apparel
                            </button>
                            <button type="button" @click="applyPreset('none')" 
                                    :class="sizeGuide.type === 'none' ? 'bg-red-600 text-white shadow-xs' : 'bg-gray-100 text-gray-500 hover:bg-gray-200'"
                                    class="px-3 py-1.5 rounded-xl text-xs font-bold transition-all flex items-center gap-1.5">
                                <span>🚫</span> No Guide (Accessories)
                            </button>
                        </div>
                    </div>

                    <!-- Size Guide Live Editor -->
                    <div x-show="sizeGuide.type !== 'none'" class="space-y-4">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-gray-700 mb-1">Guide Modal Title</label>
                                <input type="text" x-model="sizeGuide.title" class="block w-full rounded-xl border-gray-200 shadow-sm focus:border-brand-sage text-xs py-2 px-3 font-semibold">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-700 mb-1">Fit & Silhouette Advice Tip</label>
                                <input type="text" x-model="sizeGuide.tip" placeholder="e.g. Regular fit. If between sizes, choose one size up." class="block w-full rounded-xl border-gray-200 shadow-sm focus:border-brand-sage text-xs py-2 px-3">
                            </div>
                        </div>

                        <div class="flex items-center justify-between pt-2">
                            <span class="text-xs font-bold uppercase tracking-wider text-gray-500">Size Chart Matrix:</span>
                            <div class="flex items-center gap-2">
                                <button type="button" @click="addColumn()" class="text-xs font-bold text-brand-sage hover:underline flex items-center gap-1">
                                    + Add Measurement Column
                                </button>
                                <span class="text-gray-300">|</span>
                                <button type="button" @click="addRow()" class="text-xs font-bold text-brand-sage hover:underline flex items-center gap-1">
                                    + Add Size Row
                                </button>
                            </div>
                        </div>

                        <div class="overflow-x-auto rounded-xl border border-gray-200 shadow-2xs">
                            <table class="min-w-full divide-y divide-gray-200 text-xs">
                                <thead class="bg-stone-50">
                                    <tr>
                                        <template x-for="(col, colIdx) in sizeGuide.columns" :key="colIdx">
                                             <th class="px-3 py-2 text-left font-bold text-gray-700 relative group">
                                                <div class="flex items-center justify-between gap-1">
                                                    <input type="text" 
                                                           :value="col" 
                                                           @change="renameColumn(colIdx, $event.target.value)" 
                                                           class="bg-transparent font-bold text-gray-800 border-0 focus:ring-1 focus:ring-brand-sage p-1 rounded text-xs w-24 sm:w-28">
                                                    <button type="button" 
                                                            @click="removeColumn(colIdx)" 
                                                            x-show="sizeGuide.columns.length > 1" 
                                                            class="text-gray-400 hover:text-red-500 text-xs font-bold opacity-0 group-hover:opacity-100 transition-opacity" 
                                                            title="Delete Column">✕</button>
                                                </div>
                                            </th>
                                        </template>
                                        <th class="px-3 py-2 text-right font-bold text-gray-400 w-12">Action</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-100">
                                    <template x-for="(row, rowIdx) in sizeGuide.rows" :key="rowIdx">
                                        <tr class="hover:bg-stone-50/50">
                                            <template x-for="(col, colIdx) in sizeGuide.columns" :key="colIdx">
                                                <td class="px-3 py-1.5 whitespace-nowrap">
                                                    <input type="text" 
                                                           x-model="row[col]" 
                                                           :class="colIdx === 0 ? 'font-bold text-gray-900 bg-stone-50/50' : 'text-gray-700'"
                                                           class="w-full text-xs rounded-lg border-gray-200 py-1 px-2 focus:border-brand-sage focus:ring-1 focus:ring-brand-sage">
                                                </td>
                                            </template>
                                            <td class="px-3 py-1.5 text-right whitespace-nowrap">
                                                <button type="button" @click="removeRow(rowIdx)" class="text-gray-400 hover:text-red-600 transition-colors p-1" title="Delete Row">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                                </button>
                                            </td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                        </div>

                        <p class="text-[11px] text-gray-400 flex items-center gap-1">
                            <span>💡</span> Click on any column header or row cell to edit measurements directly.
                        </p>
                    </div>

                    <div x-show="sizeGuide.type === 'none'" class="p-6 bg-stone-50 rounded-xl text-center border border-dashed border-stone-200">
                        <p class="text-xs text-gray-500 font-semibold">No size guide button will be displayed on the product page (ideal for accessories, jewelry, fragrances, etc.).</p>
                    </div>
                </div>
                @else
                <input type="hidden" name="size_chart" value="">
                @endif

            </div>

            <!-- RIGHT COLUMN: Settings & Logistics -->
            <div class="space-y-6">
                
                <!-- Publishing & Org -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                    <h3 class="text-sm font-bold text-gray-900 uppercase tracking-wider mb-4 border-b border-gray-100 pb-2">Publishing</h3>
                    
                    <div class="space-y-4">
                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1">Status</label>
                            <select name="status" class="block w-full rounded-lg border-gray-200 shadow-sm focus:border-brand-sage text-sm font-medium">
                                <option value="active">Active (Visible)</option>
                                <option value="draft">Draft (Hidden)</option>
                            </select>
                        </div>
                        
                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1">Category *</label>
                            <select name="category_id" x-model="categoryId" required class="block w-full rounded-lg border-gray-200 shadow-sm focus:border-brand-sage text-sm">
                                <option value="">Select Category</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}" {{ old('category_id', $product->category_id) == $category->id ? 'selected' : '' }}>
                                        {{ $category->parent ? $category->parent->name . ' > ' : '' }}{{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1">Search Tags</label>
                            <input type="text" name="tags" placeholder="summer, casual, party (comma separated)" value="{{ old('tags', $product->tags) }}" class="block w-full rounded-lg border-gray-200 shadow-sm focus:border-brand-sage text-sm">
                        </div>
                    </div>
                </div>

                <!-- Pricing & Tax -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                    <h3 class="text-sm font-bold text-gray-900 uppercase tracking-wider mb-4 border-b border-gray-100 pb-2">Pricing & Tax</h3>
                    
                    <div class="space-y-4">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-gray-700 mb-1">{{ $sec['price_label'] ?? 'Base Selling Price' }} (₹) *</label>
                                <input type="number" step="0.01" min="0" name="base_price" x-model="basePrice" required class="block w-full rounded-lg border-gray-200 shadow-sm focus:border-brand-sage text-sm font-bold text-green-600">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-700 mb-1">MRP (₹)</label>
                                <input type="number" step="0.01" min="0" name="compare_at_price" value="{{ old('compare_at_price', $product->compare_at_price ? $product->compare_at_price / 100 : '') }}" placeholder="Crossed out" class="block w-full rounded-lg border-gray-200 shadow-sm focus:border-brand-sage text-sm text-gray-500 line-through">
                            </div>
                        </div>
                        
                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1">{{ $sec['stock_label'] ?? 'Base Available Quantity' }} *</label>
                            <input type="number" min="0" name="total_stock" value="{{ old('total_stock', 10) }}" required class="block w-full rounded-lg border-gray-200 shadow-sm focus:border-brand-sage text-sm">
                        </div>
                        
                        <div class="grid grid-cols-2 gap-4 pt-2">
                            <div>
                                <label class="block text-xs font-bold text-gray-700 mb-1">GST Rate *</label>
                                <select name="gst_rate" class="block w-full rounded-lg border-gray-200 shadow-sm focus:border-brand-sage text-sm">
                                    <option value="5" selected>5% (Apparel < 1000)</option>
                                    <option value="12">12% (Apparel > 1000)</option>
                                    <option value="18">18%</option>
                                    <option value="0">0%</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-700 mb-1">HSN Code</label>
                                <input type="text" name="hsn_code" placeholder="e.g. 6109" value="{{ old('hsn_code', $product->hsn_code) }}" class="block w-full rounded-lg border-gray-200 shadow-sm focus:border-brand-sage text-sm">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Logistics & Shipping -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                    <h3 class="text-sm font-bold text-gray-900 uppercase tracking-wider mb-4 border-b border-gray-100 pb-2">
                        @if($targetVertical === 'food')
                            Packaging & Fresh Dispatch
                        @elseif($targetVertical === 'services')
                            Service Logistics & Assurance
                        @elseif($targetVertical === 'used')
                            Packaging & Testing Guarantee
                        @else
                            Logistics & Shipping
                        @endif
                    </h3>
                    
                    <div class="space-y-4">
                        <div>
                            <div class="flex items-center justify-between mb-1">
                                <label class="block text-xs font-bold text-gray-700">
                                    @if($targetVertical === 'food')
                                        Net Serving / Dish Weight
                                    @elseif($targetVertical === 'services')
                                        Tools / Materials Weight (Optional)
                                    @elseif($targetVertical === 'used')
                                        Packaged Device Weight
                                    @else
                                        Package Weight (Per Piece)
                                    @endif
                                </label>
                                <span class="text-[10px] font-semibold text-amber-700 bg-amber-50 px-2 py-0.5 rounded">
                                    @if($targetVertical === 'food')
                                        Portion Size
                                    @elseif($targetVertical === 'services')
                                        Equipment
                                    @elseif($targetVertical === 'used')
                                        Insured Box
                                    @else
                                        Per garment
                                    @endif
                                </span>
                            </div>
                            <div class="relative">
                                <input type="number" name="weight_grams" placeholder="e.g. 250" value="{{ old('weight_grams', $product->weight_grams) }}" min="0" max="50000" class="block w-full rounded-lg border-gray-200 shadow-sm focus:border-brand-sage text-sm pr-14">
                                <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                    <span class="text-gray-400 text-xs font-bold">grams</span>
                                </div>
                            </div>
                            <p class="text-[11px] text-gray-400 mt-1">
                                @if($targetVertical === 'food')
                                    Approximate fresh portion weight (e.g. 500g). Delivered via direct local hot runner.
                                @elseif($targetVertical === 'services')
                                    Estimated weight of diagnostic tools or replacement spares carried by technician.
                                @elseif($targetVertical === 'used')
                                    Weight of boxed device or gadget for insured transit shipping.
                                @else
                                    Weight of single packaged garment piece (e.g. 250g). Used for courier manifests & shipping labels.
                                @endif
                            </p>
                        </div>
                        
                        @if($targetVertical === 'food')
                            <div class="p-3 bg-amber-50/70 border border-amber-200/60 rounded-xl text-xs space-y-1">
                                <div class="font-bold text-amber-900 flex items-center gap-1.5">
                                    <span>🛡️</span> Fresh Food Quality Guarantee
                                </div>
                                <p class="text-[11px] text-amber-800">
                                    Perishable food is freshly prepared upon order and is non-returnable once safely delivered under FSSAI hygiene guidelines.
                                </p>
                            </div>
                            <input type="hidden" name="is_returnable" value="0">
                            <input type="hidden" name="return_window_days" value="0">
                        @else
                            <div class="pt-2">
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="checkbox" name="is_returnable" value="1" {{ old('is_returnable', $product->is_returnable) ? 'checked' : '' }} class="rounded border-gray-300 text-brand-sage focus:ring-brand-sage w-4 h-4">
                                    <span class="text-sm font-bold text-gray-700">
                                        @if($targetVertical === 'services')
                                            Provide 48-Hour Service Re-work Assurance
                                        @elseif($targetVertical === 'used')
                                            Allow Buyer Testing Warranty Return
                                        @else
                                            Allow Returns for this product
                                        @endif
                                    </span>
                                </label>
                            </div>
                            
                            <div>
                                <label class="block text-xs font-bold text-gray-700 mb-1">
                                    @if($targetVertical === 'services')
                                        Assurance Window (Days)
                                    @elseif($targetVertical === 'used')
                                        Testing Warranty Window (Days)
                                    @else
                                        Return Window (Days)
                                    @endif
                                </label>
                                <input type="number" name="return_window_days" value="{{ old('return_window_days', $product->return_window_days ?? 7) }}" class="block w-full rounded-lg border-gray-200 shadow-sm focus:border-brand-sage text-sm">
                            </div>
                        @endif

                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1">Country of Origin</label>
                            <input type="text" name="country_of_origin" value="{{ old('country_of_origin', $product->country_of_origin ?? 'India') }}" class="block w-full rounded-lg border-gray-200 shadow-sm focus:border-brand-sage text-sm">
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <!-- Sticky Bottom Mobile Action Bar -->
        <div class="sticky bottom-4 z-40 bg-white/95 backdrop-blur-md p-4 rounded-2xl border border-gray-200 shadow-xl flex items-center justify-between gap-3 mt-6">
            <a href="{{ route('vendor.products.index') }}" class="text-xs sm:text-sm font-semibold text-gray-500 hover:text-gray-900 px-3 py-2">
                Discard
            </a>
            <button type="submit" class="px-6 py-3 bg-brand-text text-white text-xs sm:text-sm font-bold rounded-xl shadow-md hover:bg-black transition-colors flex items-center gap-2 active:scale-95 cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                <span>Update Product</span>
            </button>
        </div>
    </form>
    
    <script>
        function variantEngine() {
            const initialSizeGuide = @json($product->size_chart ?? null);

            return {
                vertical: @json(old('vertical', $product->vertical ?: 'marketplace')),
                listingType: @json(old('listing_type', $product->listing_type ?: 'purchase')),
                customFields: @json(old('custom_fields', $product->custom_fields ?: (object)[])),
                verticalsList: @json($verticals ?? []),
                listingTypesList: @json($listingTypes ?? []),
                allCategoryFields: @json($categoryFields ?? []),

                get currentFields() {
                    return this.allCategoryFields[this.vertical] || [];
                },

                updateVertical(v) {
                    this.vertical = v;
                    if (this.verticalsList[v] && this.verticalsList[v].default_listing) {
                        this.listingType = this.verticalsList[v].default_listing;
                    }
                },

                categoryId: '{{ old("category_id", $product->category_id) }}',
                basePrice: '{{ old("base_price", $product->base_price ? $product->base_price / 100 : "") }}',
                options: [],
                variants: @json($existingVariants ?? []),
                bulkPrice: '',
                bulkStock: '',

                verticalType: @json($resolvedVerticalType),
                isLoadingSchema: false,
                availableColors: [
                    { name: 'Jet Black', hex: '#111827' },
                    { name: 'Pure White', hex: '#FFFFFF' },
                    { name: 'Navy Blue', hex: '#1E3A8A' },
                    { name: 'Ruby Crimson', hex: '#DC2626' },
                    { name: 'Forest Green', hex: '#15803D' },
                    { name: 'Maroon / Wine', hex: '#831843' },
                    { name: 'Mustard Yellow', hex: '#CA8A04' },
                    { name: 'Soft Blush Pink', hex: '#F472B6' },
                    { name: 'Slate Gray', hex: '#64748B' },
                    { name: 'Earthy Olive', hex: '#65A30D' }
                ],
                selectedColors: [],
                customColorName: '',
                customColorHex: '#4F46E5',
                availableSizesList: ['XS', 'S', 'M', 'L', 'XL', 'XXL', '3XL', 'Free Size'],
                selectedSizes: [],
                customSizeName: '',
                fashionMatrixRows: (@json($existingVariants ?? [])).map(v => ({
                    color: v.color || (v.attributes_hash && v.attributes_hash.color) || 'Default',
                    color_hex: v.color_hex || (v.attributes_hash && v.attributes_hash.color_hex) || '#000000',
                    size: v.size || (v.attributes_hash && v.attributes_hash.size) || 'Standard',
                    sku: v.sku || '',
                    price: v.price || '',
                    stock: v.stock !== undefined ? v.stock : 10,
                    image_url: v.image_url || ''
                })),
                foodPortions: (@json($existingVariants ?? [])).length > 0
                    ? (@json($existingVariants ?? [])).map(v => ({
                        name: v.size || v.name || 'Portion',
                        price: v.price || '',
                        stock: v.stock !== undefined ? v.stock : 50
                    }))
                    : [
                        { name: '1 Plate (Standard)', price: '', stock: 50 },
                        { name: '2 Plates (Family Pack)', price: '', stock: 30 }
                    ],
                foodModifiers: (@json($existingModifiers ?? [])).length > 0
                    ? @json($existingModifiers ?? [])
                    : [
                        { group_name: 'Add-ons', modifier_name: 'Extra Portion', price_delta: '40.00', is_required: false }
                    ],
                gigPackages: Object.assign({
                    basic: { tier: 'basic', title: 'Starter Concept', description: 'Core starter deliverable with basic asset package and essential setup.', delivery_days: 3, revisions: 1, features: { source_file: false, commercial_rights: false, high_resolution: true }, price: '' },
                    standard: { tier: 'standard', title: 'Standard Commercial', description: 'Recommended comprehensive deliverable with source files and standard revisions.', delivery_days: 5, revisions: 3, features: { source_file: true, commercial_rights: true, high_resolution: true }, price: '' },
                    premium: { tier: 'premium', title: 'Complete VIP Enterprise', description: 'Turnkey enterprise package with unlimited revisions, priority support, and full source assets.', delivery_days: 7, revisions: 99, features: { source_file: true, commercial_rights: true, high_resolution: true }, price: '' }
                }, @json($existingGigPackages ?? [])),
                enableRfq: true,
                specs: Object.assign({
                    motor_hp: '',
                    cutting_thickness: '',
                    voltage: '415V Three Phase',
                    rpm: '',
                    automation_grade: 'Automatic / CNC',
                    weight_kg: '',
                    condition: 'Like New (Mint 10/10 Condition)',
                    purchase_year: '2023',
                    usage: '',
                    warranty: 'Active Brand Warranty'
                }, @json($existingSpecs ?? [])),
                b2bTiers: (@json($existingB2bTiers ?? [])).length > 0
                    ? @json($existingB2bTiers ?? [])
                    : [
                        { min_qty: 1, max_qty: 5, unit_price: '' },
                        { min_qty: 6, max_qty: 25, unit_price: '' }
                    ],
                serviceConfig: Object.assign({
                    duration_minutes: '60',
                    daily_slot_capacity: 6,
                    inclusions: ['Complete on-site inspection & diagnostic check', 'Standard service execution & clean handover'],
                    exclusions: ['Replacement hardware or consumable spares billed at actuals']
                }, @json($existingServiceConfig ?? [])),
                isNegotiable: true,

                get verticalTypeLabel() {
                    const labels = {
                        fashion_matrix: 'Fashion Matrix (Color × Size)',
                        food_portion: 'Food & Portion Pricing',
                        digital_gig: '3-Tier Digital Gig Packages',
                        b2b_machinery: 'B2B Machinery & Tiered RFQ',
                        service_booking: 'On-Demand Service Booking',
                        classified_asset: 'Pre-Owned & Condition Rating',
                        standard_retail: 'Standard Catalog Inventory'
                    };
                    return labels[this.verticalType] || 'Standard Inventory';
                },

                get verticalTypeDescription() {
                    const descs = {
                        fashion_matrix: 'Cartesian multi-axis matrix with zero-reload color hot-swapping and child SKUs.',
                        food_portion: 'Mutually exclusive portion sizes with optional customize add-on modifiers.',
                        digital_gig: 'Milestone tier breakdown (Basic, Standard, Premium) with turnaround timers.',
                        b2b_machinery: 'Tiered volume discount slabs (MOQ) and direct enterprise RFQ inquiry modal.',
                        service_booking: 'Transparent work inclusions, exclusions, duration, and daily customer slots.',
                        classified_asset: 'Strict single-unit inventory disclosure with buyer counter-offer negotiation.',
                        standard_retail: 'Standard SKU, inventory quantity, and default retail attributes.'
                    };
                    return descs[this.verticalType] || 'Standard product inventory and catalog attributes.';
                },


                sizeGuide: (initialSizeGuide && initialSizeGuide.type) ? initialSizeGuide : (
                    {!! json_encode($targetVertical === 'marketplace' ? [
                        'type' => 'shirt',
                        'title' => 'Shirt, Kurta & Top Size Guide',
                        'tip' => 'Measure around the fullest part of your chest. Length is measured from the shoulder point downwards.',
                        'columns' => ['Size', 'Chest (in)', 'Length (in)', 'Shoulder (in)', 'Sleeve (in)'],
                        'rows' => [
                            ['Size' => 'S (38)', 'Chest (in)' => '38', 'Length (in)' => '28', 'Shoulder (in)' => '17.5', 'Sleeve (in)' => '24.5'],
                            ['Size' => 'M (40)', 'Chest (in)' => '40', 'Length (in)' => '29', 'Shoulder (in)' => '18.0', 'Sleeve (in)' => '25.0'],
                            ['Size' => 'L (42)', 'Chest (in)' => '42', 'Length (in)' => '30', 'Shoulder (in)' => '18.5', 'Sleeve (in)' => '25.5'],
                            ['Size' => 'XL (44)', 'Chest (in)' => '44', 'Length (in)' => '31', 'Shoulder (in)' => '19.0', 'Sleeve (in)' => '26.0'],
                            ['Size' => 'XXL (46)', 'Chest (in)' => '46', 'Length (in)' => '31.5', 'Shoulder (in)' => '19.5', 'Sleeve (in)' => '26.5']
                        ]
                    ] : [
                        'type' => 'none',
                        'title' => '',
                        'tip' => '',
                        'columns' => [],
                        'rows' => []
                    ]) !!}
                ),

                presets: {
                    shirt: {
                        type: 'shirt',
                        title: 'Shirt, Kurta & Top Size Guide',
                        tip: 'Measure around the fullest part of your chest. Length is measured from the shoulder point downwards.',
                        columns: ['Size', 'Chest (in)', 'Length (in)', 'Shoulder (in)', 'Sleeve (in)'],
                        rows: [
                            { 'Size': 'S (38)', 'Chest (in)': '38', 'Length (in)': '28', 'Shoulder (in)': '17.5', 'Sleeve (in)': '24.5' },
                            { 'Size': 'M (40)', 'Chest (in)': '40', 'Length (in)': '29', 'Shoulder (in)': '18.0', 'Sleeve (in)': '25.0' },
                            { 'Size': 'L (42)', 'Chest (in)': '42', 'Length (in)': '30', 'Shoulder (in)': '18.5', 'Sleeve (in)': '25.5' },
                            { 'Size': 'XL (44)', 'Chest (in)': '44', 'Length (in)': '31', 'Shoulder (in)': '19.0', 'Sleeve (in)': '26.0' },
                            { 'Size': 'XXL (46)', 'Chest (in)': '46', 'Length (in)': '31.5', 'Shoulder (in)': '19.5', 'Sleeve (in)': '26.5' }
                        ]
                    },
                    jeans: {
                        type: 'jeans',
                        title: 'Jeans, Trousers & Pants Fit Guide',
                        tip: 'Measure around your natural waistline where your waistband sits. Inseam is from crotch to bottom hem.',
                        columns: ['Size', 'Waist (in)', 'Hip (in)', 'Length (in)', 'Thigh (in)'],
                        rows: [
                            { 'Size': '28', 'Waist (in)': '28', 'Hip (in)': '36', 'Length (in)': '39', 'Thigh (in)': '21' },
                            { 'Size': '30', 'Waist (in)': '30', 'Hip (in)': '38', 'Length (in)': '40', 'Thigh (in)': '22' },
                            { 'Size': '32', 'Waist (in)': '32', 'Hip (in)': '40', 'Length (in)': '40.5', 'Thigh (in)': '23' },
                            { 'Size': '34', 'Waist (in)': '34', 'Hip (in)': '42', 'Length (in)': '41', 'Thigh (in)': '24' },
                            { 'Size': '36', 'Waist (in)': '36', 'Hip (in)': '44', 'Length (in)': '41.5', 'Thigh (in)': '25' }
                        ]
                    },
                    kurti: {
                        type: 'kurti',
                        title: 'Kurti, Anarkali & Ethnic Wear Size Guide',
                        tip: 'For straight kurtis, select by bust measurement. If you prefer a looser drape, pick one size larger.',
                        columns: ['Size', 'Bust (in)', 'Waist (in)', 'Hip (in)', 'Length (in)'],
                        rows: [
                            { 'Size': 'S (36)', 'Bust (in)': '36', 'Waist (in)': '32', 'Hip (in)': '38', 'Length (in)': '42' },
                            { 'Size': 'M (38)', 'Bust (in)': '38', 'Waist (in)': '34', 'Hip (in)': '40', 'Length (in)': '43' },
                            { 'Size': 'L (40)', 'Bust (in)': '40', 'Waist (in)': '36', 'Hip (in)': '42', 'Length (in)': '43.5' },
                            { 'Size': 'XL (42)', 'Bust (in)': '42', 'Waist (in)': '38', 'Hip (in)': '44', 'Length (in)': '44' },
                            { 'Size': 'XXL (44)', 'Bust (in)': '44', 'Waist (in)': '40', 'Hip (in)': '46', 'Length (in)': '44' }
                        ]
                    },
                    saree: {
                        type: 'saree',
                        title: 'Saree & Drape Specification Guide',
                        tip: 'Free Size accommodates all heights and silhouettes. Unstitched blouse fabric can be tailored as desired.',
                        columns: ['Component', 'Length / Dimension', 'Specification'],
                        rows: [
                            { 'Component': 'Saree Length', 'Length / Dimension': '5.50 Meters', 'Specification': 'Full traditional drape' },
                            { 'Component': 'Blouse Piece', 'Length / Dimension': '0.80 Meters', 'Specification': 'Unstitched matching fabric' },
                            { 'Component': 'Saree Width', 'Length / Dimension': '44 Inches (1.1m)', 'Specification': 'Standard border fall width' }
                        ]
                    },
                    kids: {
                        type: 'kids',
                        title: 'Kids Apparel Age & Fit Guide',
                        tip: 'Children grow quickly; if your child is between age brackets, we recommend sizing up.',
                        columns: ['Age / Size', 'Chest (in)', 'Waist (in)', 'Height (cm)'],
                        rows: [
                            { 'Age / Size': '2-3 Years', 'Chest (in)': '21', 'Waist (in)': '20', 'Height (cm)': '92-98' },
                            { 'Age / Size': '4-5 Years', 'Chest (in)': '23', 'Waist (in)': '21', 'Height (cm)': '104-110' },
                            { 'Age / Size': '6-7 Years', 'Chest (in)': '25', 'Waist (in)': '22', 'Height (cm)': '116-122' },
                            { 'Age / Size': '8-9 Years', 'Chest (in)': '27', 'Waist (in)': '23.5', 'Height (cm)': '128-134' }
                        ]
                    },
                    none: {
                        type: 'none',
                        title: '',
                        tip: '',
                        columns: [],
                        rows: []
                    }
                },

                applyPreset(type) {
                    if (type === 'none') {
                        this.sizeGuide = { type: 'none', title: '', tip: '', columns: [], rows: [] };
                        return;
                    }
                    const preset = this.presets[type];
                    if (preset) {
                        this.sizeGuide = JSON.parse(JSON.stringify(preset));
                    }
                },

                addRow() {
                    const newRow = {};
                    this.sizeGuide.columns.forEach((col, idx) => {
                        newRow[col] = idx === 0 ? 'Custom' : '';
                    });
                    this.sizeGuide.rows.push(newRow);
                },

                removeRow(idx) {
                    this.sizeGuide.rows.splice(idx, 1);
                },

                addColumn() {
                    const colName = prompt('Enter measurement column name (e.g. Shoulder, Inseam, Sleeve):');
                    if (colName && colName.trim()) {
                        const name = colName.trim();
                        if (!this.sizeGuide.columns.includes(name)) {
                            this.sizeGuide.columns.push(name);
                            this.sizeGuide.rows.forEach(r => {
                                r[name] = '';
                            });
                        }
                    }
                },

                renameColumn(colIdx, newName) {
                    if (!newName || !newName.trim()) return;
                    const oldName = this.sizeGuide.columns[colIdx];
                    this.sizeGuide.columns[colIdx] = newName.trim();
                    this.sizeGuide.rows.forEach(r => {
                        r[newName.trim()] = r[oldName] || '';
                        delete r[oldName];
                    });
                },

                removeColumn(colIdx) {
                    if (this.sizeGuide.columns.length <= 1) return;
                    const colName = this.sizeGuide.columns[colIdx];
                    this.sizeGuide.columns.splice(colIdx, 1);
                    this.sizeGuide.rows.forEach(r => {
                        delete r[colName];
                    });
                },
                
                addTemplate(type) {
                    if (type === 'food_portions') {
                        this.options.push({ name: 'Portion', valuesString: '500g Handi, 1kg Family Handi, 2.5kg Party Pot', values: ['500g Handi', '1kg Family Handi', '2.5kg Party Pot'] });
                    } else if (type === 'food_plates') {
                        this.options.push({ name: 'Serving', valuesString: 'Half Plate, Full Plate', values: ['Half Plate', 'Full Plate'] });
                    } else if (type === 'food_combos') {
                        this.options.push({ name: 'Meal Set', valuesString: 'A La Carte, Combo with Drink & Fries, Executive Deluxe Thali', values: ['A La Carte', 'Combo with Drink & Fries', 'Executive Deluxe Thali'] });
                    } else if (type === 'food_spice') {
                        this.options.push({ name: 'Spice Level', valuesString: 'Mild, Medium Spicy, Extra Hot Awadhi', values: ['Mild', 'Medium Spicy', 'Extra Hot Awadhi'] });
                    } else if (type === 'service_bhk') {
                        this.options.push({ name: 'Home Size', valuesString: '1 BHK, 2 BHK, 3 BHK, 4 BHK / Villa', values: ['1 BHK', '2 BHK', '3 BHK', '4 BHK / Villa'] });
                    } else if (type === 'service_tier') {
                        this.options.push({ name: 'Service Plan', valuesString: 'Standard Clean, Deep Intensive Clean, Premium Sanitization', values: ['Standard Clean', 'Deep Intensive Clean', 'Premium Sanitization'] });
                    } else if (type === 'storage_sizes') {
                        this.options.push({ name: 'Storage / Spec', valuesString: '128 GB, 256 GB, 512 GB, 1 TB', values: ['128 GB', '256 GB', '512 GB', '1 TB'] });
                    } else if (type === 'adult_sizes') {
                        this.options.push({ name: 'Size', valuesString: 'S, M, L, XL, XXL', values: ['S', 'M', 'L', 'XL', 'XXL'] });
                    } else if (type === 'kids_sizes') {
                        this.options.push({ name: 'Age', valuesString: '2-3Y, 4-5Y, 6-7Y, 8-9Y', values: ['2-3Y', '4-5Y', '6-7Y', '8-9Y'] });
                    } else if (type === 'colors') {
                        this.options.push({ name: 'Color', valuesString: 'Red, Blue, Black', values: ['Red', 'Blue', 'Black'] });
                    }
                    this.generateVariants();
                },

                addOption() {
                    if (this.options.length < 3) {
                        this.options.push({ name: '', valuesString: '', values: [] });
                    }
                },
                removeOption(index) {
                    this.options.splice(index, 1);
                    this.generateVariants();
                },
                updateValues(index) {
                    this.options[index].values = this.options[index].valuesString
                        .split(',')
                        .map(v => v.trim())
                        .filter(v => v.length > 0);
                },
                generateVariants() {
                    const validOptions = this.options.filter(o => o.name.trim() !== '' && o.values.length > 0);
                    if (validOptions.length === 0) {
                        this.variants = [];
                        return;
                    }
                    
                    // Generate cartesian product
                    const combinations = validOptions.reduce((acc, curr) => {
                        if (acc.length === 0) return curr.values.map(v => [v]);
                        const newAcc = [];
                        acc.forEach(a => {
                            curr.values.forEach(v => {
                                newAcc.push([...a, v]);
                            });
                        });
                        return newAcc;
                    }, []);
                    
                    this.variants = combinations.map((combo, idx) => {
                        const name = combo.join(' / ');
                        // Preserve existing variants if they match name
                        const existing = this.variants.find(v => v.name === name);
                        if (existing) return existing;
                        
                        return {
                            name: name,
                            price: '', 
                            stock: 10,
                            sku: ''
                        };
                    });
                },
                removeVariant(index) {
                    this.variants.splice(index, 1);
                },
                applyBulk() {
                    this.variants.forEach(v => {
                        if (this.bulkPrice) v.price = this.bulkPrice;
                        if (this.bulkStock) v.stock = this.bulkStock;
                    });
                },

                init() {
                    if (this.fashionMatrixRows.length > 0) {
                        const colors = [];
                        const sizes = [];
                        this.fashionMatrixRows.forEach(r => {
                            if (r.color && !colors.some(c => c.name === r.color)) {
                                colors.push({ name: r.color, hex: r.color_hex || '#111827' });
                            }
                            if (r.size && !sizes.includes(r.size)) {
                                sizes.push(r.size);
                            }
                        });
                        this.selectedColors = colors;
                        this.selectedSizes = sizes;
                        colors.forEach(c => {
                            if (!this.availableColors.some(ac => ac.name === c.name)) {
                                this.availableColors.push(c);
                            }
                        });
                        sizes.forEach(s => {
                            if (!this.availableSizesList.includes(s)) {
                                this.availableSizesList.push(s);
                            }
                        });
                    }

                    this.$watch('categoryId', val => {
                        if (val) this.fetchCategoryBlueprint(val);
                    });
                },

                async fetchCategoryBlueprint(catId) {
                    if (!catId) return;
                    this.isLoadingSchema = true;
                    try {
                        const res = await fetch(`/api/vendor/category-attributes?category_id=${catId}`);
                        if (res.ok) {
                            const data = await res.json();
                            if (data.vertical_type) {
                                this.verticalType = data.vertical_type;
                            }
                            if (data.default_packages && (!this.gigPackages.basic.price || this.gigPackages.basic.price === '')) {
                                this.gigPackages = data.default_packages;
                            }
                            if (data.default_modifiers && this.foodModifiers.length <= 1) {
                                this.foodModifiers = data.default_modifiers;
                            }
                        }
                    } catch (e) {
                        console.error('Error fetching category blueprint:', e);
                    } finally {
                        this.isLoadingSchema = false;
                    }
                },

                toggleColor(col) {
                    const idx = this.selectedColors.findIndex(c => c.name.toLowerCase() === col.name.toLowerCase());
                    if (idx > -1) {
                        this.selectedColors.splice(idx, 1);
                    } else {
                        this.selectedColors.push({ name: col.name, hex: col.hex });
                    }
                    this.generateFashionMatrix();
                },

                isColorSelected(name) {
                    return this.selectedColors.some(c => c.name.toLowerCase() === name.toLowerCase());
                },

                addCustomColor() {
                    if (!this.customColorName || !this.customColorName.trim()) return;
                    const name = this.customColorName.trim();
                    const hex = this.customColorHex || '#111827';
                    if (!this.availableColors.some(c => c.name.toLowerCase() === name.toLowerCase())) {
                        this.availableColors.push({ name, hex });
                    }
                    if (!this.isColorSelected(name)) {
                        this.selectedColors.push({ name, hex });
                    }
                    this.customColorName = '';
                    this.generateFashionMatrix();
                },

                toggleSize(sz) {
                    const idx = this.selectedSizes.indexOf(sz);
                    if (idx > -1) {
                        this.selectedSizes.splice(idx, 1);
                    } else {
                        this.selectedSizes.push(sz);
                    }
                    this.generateFashionMatrix();
                },

                addCustomSize() {
                    if (!this.customSizeName || !this.customSizeName.trim()) return;
                    const sz = this.customSizeName.trim();
                    if (!this.availableSizesList.includes(sz)) {
                        this.availableSizesList.push(sz);
                    }
                    if (!this.selectedSizes.includes(sz)) {
                        this.selectedSizes.push(sz);
                    }
                    this.customSizeName = '';
                    this.generateFashionMatrix();
                },

                generateFashionMatrix() {
                    if (this.selectedColors.length === 0 || this.selectedSizes.length === 0) {
                        this.fashionMatrixRows = [];
                        return;
                    }

                    const rows = [];
                    const prefix = (this.productTitle ? this.productTitle.substring(0, 3).toUpperCase().replace(/[^A-Z]/g, '') : 'PRD') || 'PRD';

                    this.selectedColors.forEach(col => {
                        this.selectedSizes.forEach(sz => {
                            const cleanCol = col.name.substring(0, 3).toUpperCase().replace(/[^A-Z]/g, '');
                            const cleanSz = sz.replace(/[^A-Za-z0-9]/g, '').toUpperCase();
                            const sku = `${prefix}-${cleanCol}-${cleanSz}`;

                            const existing = this.fashionMatrixRows.find(r => r.color === col.name && r.size === sz);
                            rows.push(existing || {
                                color: col.name,
                                color_hex: col.hex,
                                size: sz,
                                sku: sku,
                                price: this.basePrice || '',
                                stock: 10,
                                image_url: ''
                            });
                        });
                    });

                    this.fashionMatrixRows = rows;
                },

                applyFashionBulk() {
                    this.fashionMatrixRows.forEach(r => {
                        if (this.bulkPrice) r.price = this.bulkPrice;
                        if (this.bulkStock !== '') r.stock = this.bulkStock;
                    });
                },

                removeFashionRow(idx) {
                    this.fashionMatrixRows.splice(idx, 1);
                },

                addFoodPortion() {
                    this.foodPortions.push({ name: '', price: '', stock: 50 });
                },

                removeFoodPortion(idx) {
                    if (this.foodPortions.length > 1) {
                        this.foodPortions.splice(idx, 1);
                    }
                },

                addFoodModifier() {
                    this.foodModifiers.push({ group_name: 'Add-ons', modifier_name: '', price_delta: '', is_required: false });
                },

                removeFoodModifier(idx) {
                    this.foodModifiers.splice(idx, 1);
                },

                addB2bTier() {
                    const last = this.b2bTiers[this.b2bTiers.length - 1];
                    const min = last ? (parseInt(last.max_qty) || 1) + 1 : 1;
                    const max = min + 50;
                    this.b2bTiers.push({ min_qty: min, max_qty: max, unit_price: '' });
                },

                removeB2bTier(idx) {
                    if (this.b2bTiers.length > 1) {
                        this.b2bTiers.splice(idx, 1);
                    }
                },

                addInclusion() {
                    this.serviceConfig.inclusions.push('');
                },

                removeInclusion(idx) {
                    this.serviceConfig.inclusions.splice(idx, 1);
                },

                addExclusion() {
                    this.serviceConfig.exclusions.push('');
                },

                removeExclusion(idx) {
                    this.serviceConfig.exclusions.splice(idx, 1);
                }
            }
        }

        function editMediaGalleryEngine(initialImages = []) {
            return {
                items: (initialImages || []).map(img => ({
                    id: img.id,
                    isExisting: true,
                    url: img.url,
                    name: img.name || 'Photo'
                })),
                deletedIds: [],
                isDragging: false,

                init() {
                    this.syncFiles();
                },

                handleFiles(fileList) {
                    if (!fileList || fileList.length === 0) return;
                    for (let i = 0; i < fileList.length; i++) {
                        const file = fileList[i];
                        if (!file.type.match('image.*')) continue;
                        if (file.size > 5 * 1024 * 1024) {
                            alert(`"${file.name}" is larger than 5MB. Please choose an image under 5MB.`);
                            continue;
                        }
                        const url = URL.createObjectURL(file);
                        this.items.push({
                            id: 'new_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9),
                            isExisting: false,
                            file: file,
                            url: url,
                            name: file.name
                        });
                    }
                    this.syncFiles();
                },

                remove(index) {
                    const item = this.items[index];
                    if (item.isExisting) {
                        this.deletedIds.push(item.id);
                    }
                    this.items.splice(index, 1);
                    this.syncFiles();
                },

                moveLeft(index) {
                    if (index <= 0) return;
                    const item = this.items.splice(index, 1)[0];
                    this.items.splice(index - 1, 0, item);
                    this.syncFiles();
                },

                moveRight(index) {
                    if (index >= this.items.length - 1) return;
                    const item = this.items.splice(index, 1)[0];
                    this.items.splice(index + 1, 0, item);
                    this.syncFiles();
                },

                makeCover(index) {
                    if (index === 0) return;
                    const item = this.items.splice(index, 1)[0];
                    this.items.unshift(item);
                    this.syncFiles();
                },

                syncFiles() {
                    const dt = new DataTransfer();
                    let newCounter = 0;
                    this.items.forEach(item => {
                        if (!item.isExisting && item.file) {
                            item.newIndex = newCounter++;
                            dt.items.add(item.file);
                        }
                    });
                    const input = document.getElementById('images-input');
                    if (input) {
                        input.files = dt.files;
                    }
                }
            }
        }
    </script>
</x-layouts.vendor>
