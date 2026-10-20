<x-layouts.vendor title="Create Product">
    <form action="{{ route('vendor.products.store') }}" method="POST" enctype="multipart/form-data" class="relative" x-data="variantEngine()">
        @csrf

        <!-- Sticky Save Bar -->
        <div class="sticky top-0 z-40 bg-white/95 backdrop-blur-md border-b border-gray-200 px-4 sm:px-6 py-3 sm:py-4 flex items-center justify-between gap-3 mb-6 sm:mb-8 shadow-sm -mx-4 sm:-mx-6 lg:-mx-8">
            <div class="flex items-center gap-2.5 sm:gap-4 min-w-0">
                <a href="{{ route('vendor.products.index') }}" class="w-8 h-8 shrink-0 flex items-center justify-center rounded-full bg-gray-100 text-gray-500 hover:bg-gray-200 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                </a>
                <h1 class="text-base sm:text-2xl font-bold text-gray-900 tracking-tight truncate">Create New Product</h1>
            </div>
            <div class="flex items-center gap-2 sm:gap-3 shrink-0">
                <a href="{{ route('vendor.products.index') }}" class="text-xs sm:text-sm font-medium text-gray-600 hover:text-gray-900 px-2 sm:px-4 py-2">Discard</a>
                <button type="submit" class="px-4 sm:px-6 py-2 sm:py-2.5 bg-brand-text text-white text-xs sm:text-sm font-bold rounded-xl shadow hover:bg-black transition-colors flex items-center gap-1.5 sm:gap-2 active:scale-95 cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    <span>Save Product</span>
                </button>
            </div>
        </div>

        <!-- ⚡ 1-Tap Category Master Presets Bar -->
        <style>
            .preset-cards-grid {
                display: grid !important;
                grid-template-columns: repeat(5, minmax(0, 1fr)) !important;
                gap: 6px !important;
            }
            @media (min-width: 768px) {
                .preset-cards-grid {
                    grid-template-columns: repeat(7, minmax(0, 1fr)) !important;
                    gap: 6px !important;
                }
            }
            @media (min-width: 1024px) {
                .preset-cards-grid {
                    grid-template-columns: repeat(9, minmax(0, 1fr)) !important;
                    gap: 8px !important;
                }
            }
            .preset-tab-btn {
                white-space: nowrap;
                transition: all 0.15s ease-in-out;
            }
        </style>
        <div class="max-w-6xl mx-auto mb-5 sm:mb-6">
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-3 sm:p-5 transition-all">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2.5 pb-2.5 sm:pb-3 border-b border-gray-100 mb-2.5 sm:mb-3">
                    <div class="flex items-center gap-2.5">
                        <div class="w-7 h-7 sm:w-8 sm:h-8 rounded-xl bg-amber-50 text-amber-700 border border-amber-200/60 flex items-center justify-center text-sm sm:text-base font-bold shrink-0">
                            ⚡
                        </div>
                        <div>
                            <div class="flex items-center gap-2">
                                <h2 class="text-xs sm:text-sm font-bold text-gray-900 tracking-tight">1-Tap {{ ucfirst($targetVertical) }} Presets</h2>
                                <span class="text-[9px] bg-stone-100 text-stone-700 font-bold px-1.5 py-0.5 rounded-full uppercase tracking-wider border border-stone-200">Fast Track</span>
                            </div>
                            <p class="text-[10px] sm:text-xs text-gray-500 leading-tight">Instant pre-fills tailored exclusively for your {{ $verticals[$targetVertical]['name'] ?? $targetVertical }} store catalog.</p>
                        </div>
                    </div>
                    <template x-if="activePreset">
                        <div class="inline-flex items-center gap-1.5 bg-emerald-50 text-emerald-800 text-[11px] font-bold px-2.5 py-1 rounded-lg border border-emerald-200 w-fit shrink-0">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                            <span>Applied:</span>
                            <span class="font-extrabold" x-text="categoryPresets[activePreset]?.name"></span>
                            <button type="button" @click="activePreset = null" class="text-emerald-600 hover:text-emerald-900 ml-1 font-bold text-xs cursor-pointer" title="Clear selection">✕</button>
                        </div>
                    </template>
                </div>

                <!-- Variety Filter Tabs -->
                @if($targetVertical === 'food')
                    <div class="flex items-center gap-1.5 overflow-x-auto pb-2 mb-2.5 no-scrollbar" style="-webkit-overflow-scrolling: touch;">
                        <button type="button" @click="presetTab = 'all'"
                                :class="presetTab === 'all' ? 'bg-stone-900 text-white shadow-2xs font-bold' : 'bg-stone-100 text-stone-600 hover:bg-stone-200 font-medium'"
                                class="preset-tab-btn text-[11px] sm:text-xs px-2.5 py-1 rounded-lg shrink-0 cursor-pointer">
                            ✨ All Dishes (8)
                        </button>
                        <button type="button" @click="presetTab = 'biryani'"
                                :class="presetTab === 'biryani' ? 'bg-stone-900 text-white shadow-2xs font-bold' : 'bg-stone-100 text-stone-600 hover:bg-stone-200 font-medium'"
                                class="preset-tab-btn text-[11px] sm:text-xs px-2.5 py-1 rounded-lg shrink-0 cursor-pointer">
                            🍲 Biryani & Rice
                        </button>
                        <button type="button" @click="presetTab = 'fast_food'"
                                :class="presetTab === 'fast_food' ? 'bg-stone-900 text-white shadow-2xs font-bold' : 'bg-stone-100 text-stone-600 hover:bg-stone-200 font-medium'"
                                class="preset-tab-btn text-[11px] sm:text-xs px-2.5 py-1 rounded-lg shrink-0 cursor-pointer">
                            🍕 Fast Food & Grills
                        </button>
                        <button type="button" @click="presetTab = 'meals'"
                                :class="presetTab === 'meals' ? 'bg-stone-900 text-white shadow-2xs font-bold' : 'bg-stone-100 text-stone-600 hover:bg-stone-200 font-medium'"
                                class="preset-tab-btn text-[11px] sm:text-xs px-2.5 py-1 rounded-lg shrink-0 cursor-pointer">
                            🍛 Royal Thalis
                        </button>
                        <button type="button" @click="presetTab = 'desserts'"
                                :class="presetTab === 'desserts' ? 'bg-stone-900 text-white shadow-2xs font-bold' : 'bg-stone-100 text-stone-600 hover:bg-stone-200 font-medium'"
                                class="preset-tab-btn text-[11px] sm:text-xs px-2.5 py-1 rounded-lg shrink-0 cursor-pointer">
                            🍮 Sweets & Coolers
                        </button>
                    </div>
                @elseif($targetVertical === 'services')
                    <div class="flex items-center gap-1.5 overflow-x-auto pb-2 mb-2.5 no-scrollbar" style="-webkit-overflow-scrolling: touch;">
                        <button type="button" @click="presetTab = 'all'"
                                :class="presetTab === 'all' ? 'bg-stone-900 text-white shadow-2xs font-bold' : 'bg-stone-100 text-stone-600 hover:bg-stone-200 font-medium'"
                                class="preset-tab-btn text-[11px] sm:text-xs px-2.5 py-1 rounded-lg shrink-0 cursor-pointer">
                            ✨ All Services (5)
                        </button>
                        <button type="button" @click="presetTab = 'repairs'"
                                :class="presetTab === 'repairs' ? 'bg-stone-900 text-white shadow-2xs font-bold' : 'bg-stone-100 text-stone-600 hover:bg-stone-200 font-medium'"
                                class="preset-tab-btn text-[11px] sm:text-xs px-2.5 py-1 rounded-lg shrink-0 cursor-pointer">
                            🔧 Appliance & Electrician
                        </button>
                        <button type="button" @click="presetTab = 'cleaning'"
                                :class="presetTab === 'cleaning' ? 'bg-stone-900 text-white shadow-2xs font-bold' : 'bg-stone-100 text-stone-600 hover:bg-stone-200 font-medium'"
                                class="preset-tab-btn text-[11px] sm:text-xs px-2.5 py-1 rounded-lg shrink-0 cursor-pointer">
                            🧹 Deep Cleaning
                        </button>
                        <button type="button" @click="presetTab = 'tailoring'"
                                :class="presetTab === 'tailoring' ? 'bg-stone-900 text-white shadow-2xs font-bold' : 'bg-stone-100 text-stone-600 hover:bg-stone-200 font-medium'"
                                class="preset-tab-btn text-[11px] sm:text-xs px-2.5 py-1 rounded-lg shrink-0 cursor-pointer">
                            ✂️ Tailoring & Salon
                        </button>
                    </div>
                @elseif($targetVertical === 'used')
                    <div class="flex items-center gap-1.5 overflow-x-auto pb-2 mb-2.5 no-scrollbar" style="-webkit-overflow-scrolling: touch;">
                        <button type="button" @click="presetTab = 'all'"
                                :class="presetTab === 'all' ? 'bg-stone-900 text-white shadow-2xs font-bold' : 'bg-stone-100 text-stone-600 hover:bg-stone-200 font-medium'"
                                class="preset-tab-btn text-[11px] sm:text-xs px-2.5 py-1 rounded-lg shrink-0 cursor-pointer">
                            ✨ All Pre-Owned (5)
                        </button>
                        <button type="button" @click="presetTab = 'phones'"
                                :class="presetTab === 'phones' ? 'bg-stone-900 text-white shadow-2xs font-bold' : 'bg-stone-100 text-stone-600 hover:bg-stone-200 font-medium'"
                                class="preset-tab-btn text-[11px] sm:text-xs px-2.5 py-1 rounded-lg shrink-0 cursor-pointer">
                            📱 Phones & Mobiles
                        </button>
                        <button type="button" @click="presetTab = 'laptops'"
                                :class="presetTab === 'laptops' ? 'bg-stone-900 text-white shadow-2xs font-bold' : 'bg-stone-100 text-stone-600 hover:bg-stone-200 font-medium'"
                                class="preset-tab-btn text-[11px] sm:text-xs px-2.5 py-1 rounded-lg shrink-0 cursor-pointer">
                            💻 Laptops & Cameras
                        </button>
                        <button type="button" @click="presetTab = 'luxury'"
                                :class="presetTab === 'luxury' ? 'bg-stone-900 text-white shadow-2xs font-bold' : 'bg-stone-100 text-stone-600 hover:bg-stone-200 font-medium'"
                                class="preset-tab-btn text-[11px] sm:text-xs px-2.5 py-1 rounded-lg shrink-0 cursor-pointer">
                            ⌚ Watches & Bikes
                        </button>
                    </div>
                @else
                    <div class="flex items-center gap-1.5 overflow-x-auto pb-2 mb-2.5 no-scrollbar" style="-webkit-overflow-scrolling: touch;">
                        <button type="button" @click="presetTab = 'all'"
                                :class="presetTab === 'all' ? 'bg-stone-900 text-white shadow-2xs font-bold' : 'bg-stone-100 text-stone-600 hover:bg-stone-200 font-medium'"
                                class="preset-tab-btn text-[11px] sm:text-xs px-2.5 py-1 rounded-lg shrink-0 cursor-pointer">
                            ✨ All Varieties (30)
                        </button>
                        <button type="button" @click="presetTab = 'footwear'"
                                :class="presetTab === 'footwear' ? 'bg-stone-900 text-white shadow-2xs font-bold' : 'bg-stone-100 text-stone-600 hover:bg-stone-200 font-medium'"
                                class="preset-tab-btn text-[11px] sm:text-xs px-2.5 py-1 rounded-lg shrink-0 cursor-pointer">
                            👟 Footwear (11)
                        </button>
                        <button type="button" @click="presetTab = 'women_ethnic'"
                                :class="presetTab === 'women_ethnic' ? 'bg-stone-900 text-white shadow-2xs font-bold' : 'bg-stone-100 text-stone-600 hover:bg-stone-200 font-medium'"
                                class="preset-tab-btn text-[11px] sm:text-xs px-2.5 py-1 rounded-lg shrink-0 cursor-pointer">
                            🥻 Women Ethnic (8)
                        </button>
                        <button type="button" @click="presetTab = 'western'"
                                :class="presetTab === 'western' ? 'bg-stone-900 text-white shadow-2xs font-bold' : 'bg-stone-100 text-stone-600 hover:bg-stone-200 font-medium'"
                                class="preset-tab-btn text-[11px] sm:text-xs px-2.5 py-1 rounded-lg shrink-0 cursor-pointer">
                            👗 Western & Casual (4)
                        </button>
                        <button type="button" @click="presetTab = 'men'"
                                :class="presetTab === 'men' ? 'bg-stone-900 text-white shadow-2xs font-bold' : 'bg-stone-100 text-stone-600 hover:bg-stone-200 font-medium'"
                                class="preset-tab-btn text-[11px] sm:text-xs px-2.5 py-1 rounded-lg shrink-0 cursor-pointer">
                            👔 Men's Fashion (5)
                        </button>
                        <button type="button" @click="presetTab = 'kids_acc'"
                                :class="presetTab === 'kids_acc' ? 'bg-stone-900 text-white shadow-2xs font-bold' : 'bg-stone-100 text-stone-600 hover:bg-stone-200 font-medium'"
                                class="preset-tab-btn text-[11px] sm:text-xs px-2.5 py-1 rounded-lg shrink-0 cursor-pointer">
                            👶 Kids & Bags (2)
                        </button>
                    </div>
                @endif

                <!-- Presets Grid -->
                <div class="preset-cards-grid" style="display: grid; grid-template-columns: repeat(5, minmax(0, 1fr)); gap: 6px;">
                    <template x-for="(p, key) in filteredPresets" :key="key">
                        <button type="button" 
                                @click="applyCategoryPreset(key)"
                                :class="activePreset === key 
                                    ? 'bg-stone-900 text-white border-stone-900 shadow-sm ring-2 ring-stone-900/10' 
                                    : 'bg-stone-50 hover:bg-stone-100 text-gray-800 border border-gray-200/80 hover:border-gray-300 hover:shadow-2xs'"
                                class="flex flex-col items-center justify-center py-2 px-1 rounded-xl transition-all duration-150 text-center group cursor-pointer active:scale-95"
                                style="min-width: 0; width: 100%; box-sizing: border-box;">
                            <span class="text-lg sm:text-2xl mb-1 group-hover:scale-110 transition-transform" x-text="p.icon"></span>
                            <span class="text-[10px] sm:text-xs font-bold leading-tight truncate w-full text-center px-0.5" :class="activePreset === key ? 'text-white' : 'text-gray-900'" x-text="p.shortName"></span>
                            <span class="text-[8px] sm:text-[9px] mt-0.5 leading-none truncate w-full text-center font-medium opacity-75" :class="activePreset === key ? 'text-stone-300' : 'text-gray-500'" x-text="p.badge"></span>
                            <template x-if="activePreset === key">
                                <span class="mt-1 text-[7px] sm:text-[8px] font-black uppercase tracking-wider bg-emerald-500/20 text-emerald-300 border border-emerald-400/30 px-1 py-0.2 rounded-full leading-none">✓ On</span>
                            </template>
                        </button>
                    </template>
                </div>
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
                        <span class="text-[10px] font-bold text-emerald-700 bg-emerald-50 px-2.5 py-1 rounded-full border border-emerald-200 uppercase tracking-wider flex items-center gap-1">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                            Verified Store Vertical
                        </span>
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

                <!-- 1. Smart Guided Product Identity -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 sm:p-8 space-y-6">
                    <div class="flex items-center justify-between pb-4 border-b border-gray-100">
                        <div class="flex items-center gap-2.5">
                            <div class="w-8 h-8 rounded-xl bg-amber-50 text-amber-700 border border-amber-200/60 flex items-center justify-center font-black text-sm">
                                1
                            </div>
                            <div>
                                <h2 class="text-base sm:text-lg font-bold text-gray-900 tracking-tight">Product Identity & Smart SEO</h2>
                                <p class="text-xs text-gray-500">System-guided setup for maximum Google Search & shopping discoverability.</p>
                            </div>
                        </div>
                        <template x-if="activePreset">
                            <button type="button" @click="applyCategoryPreset(activePreset)" class="text-xs font-bold text-amber-800 hover:text-amber-950 bg-amber-50 hover:bg-amber-100 border border-amber-200/80 px-2.5 py-1 rounded-lg transition-colors flex items-center gap-1 cursor-pointer">
                                <span>↻ Re-apply Preset</span>
                            </button>
                        </template>
                    </div>

                    @if($targetVertical === 'marketplace')
                    <!-- Step 1.1: Target Audience (Gender) FIRST -->
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <label class="block text-xs font-black uppercase tracking-wider text-stone-700">
                                Target Audience (Gender) <span class="text-red-500">*</span>
                            </label>
                            <span class="text-[10px] font-bold text-emerald-800 bg-emerald-50 px-2 py-0.5 rounded-full border border-emerald-200" x-text="gender ? gender.toUpperCase() : 'SELECT'"></span>
                        </div>
                        <input type="hidden" name="gender" :value="gender">
                        <div class="grid grid-cols-4 gap-2">
                            <button type="button" @click="setGender('men')" 
                                    :class="gender === 'men' ? 'bg-stone-900 text-white shadow-sm ring-2 ring-stone-900/20 font-bold scale-[1.02]' : 'bg-stone-50 hover:bg-stone-100 text-stone-700 border border-stone-200 font-medium'"
                                    class="py-2.5 px-2 rounded-xl text-xs flex flex-col sm:flex-row items-center justify-center gap-1.5 transition-all cursor-pointer">
                                <span class="text-base sm:text-lg">👨</span>
                                <span>Men</span>
                            </button>
                            <button type="button" @click="setGender('women')" 
                                    :class="gender === 'women' ? 'bg-stone-900 text-white shadow-sm ring-2 ring-stone-900/20 font-bold scale-[1.02]' : 'bg-stone-50 hover:bg-stone-100 text-stone-700 border border-stone-200 font-medium'"
                                    class="py-2.5 px-2 rounded-xl text-xs flex flex-col sm:flex-row items-center justify-center gap-1.5 transition-all cursor-pointer">
                                <span class="text-base sm:text-lg">👩</span>
                                <span>Women</span>
                            </button>
                            <button type="button" @click="setGender('kids')" 
                                    :class="gender === 'kids' ? 'bg-stone-900 text-white shadow-sm ring-2 ring-stone-900/20 font-bold scale-[1.02]' : 'bg-stone-50 hover:bg-stone-100 text-stone-700 border border-stone-200 font-medium'"
                                    class="py-2.5 px-2 rounded-xl text-xs flex flex-col sm:flex-row items-center justify-center gap-1.5 transition-all cursor-pointer">
                                <span class="text-base sm:text-lg">👶</span>
                                <span>Kids</span>
                            </button>
                            <button type="button" @click="setGender('unisex')" 
                                    :class="gender === 'unisex' ? 'bg-stone-900 text-white shadow-sm ring-2 ring-stone-900/20 font-bold scale-[1.02]' : 'bg-stone-50 hover:bg-stone-100 text-stone-700 border border-stone-200 font-medium'"
                                    class="py-2.5 px-2 rounded-xl text-xs flex flex-col sm:flex-row items-center justify-center gap-1.5 transition-all cursor-pointer">
                                <span class="text-base sm:text-lg">⚧</span>
                                <span>Unisex</span>
                            </button>
                        </div>
                    </div>
                    @else
                        <input type="hidden" name="gender" value="unisex">
                    @endif

                    <!-- Step 1.2: Marketplace Category -->
                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <label class="block text-xs font-black uppercase tracking-wider text-stone-700">
                                Marketplace Category <span class="text-red-500">*</span>
                            </label>
                            <span class="text-[10px] text-stone-400">Or pick from 1-Tap Presets above</span>
                        </div>
                        <select name="category_id" x-model="categoryId" required class="block w-full rounded-xl border-gray-200 shadow-xs focus:border-brand-sage focus:ring-brand-sage text-sm py-2.5 px-3 font-semibold text-stone-800 bg-stone-50/50">
                            <option value="">Select Category</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                    {{ $category->parent ? $category->parent->name . ' > ' : '' }}{{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('category_id') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <!-- Step 1.3: Product Title + Live Google SEO Recommender -->
                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <label class="block text-xs font-black uppercase tracking-wider text-stone-700">
                                Product Title <span class="text-red-500">*</span>
                            </label>
                            <span class="text-[10px] text-amber-700 bg-amber-50 border border-amber-200 font-bold px-2 py-0.5 rounded-full">Google SEO Ready</span>
                        </div>
                        <input type="text" name="title" x-model="title" placeholder="e.g. Chelsea Boots, Cotton Casual Shirt, Anarkali Kurti" required class="block w-full rounded-xl border-gray-200 shadow-xs focus:border-brand-sage focus:ring-brand-sage text-sm sm:text-base py-3 px-3.5 font-bold text-stone-900">
                        @error('title') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror

                        <!-- Live Smart Google Title Recommender Card -->
                        <div x-show="smartTitleSuggestion && smartTitleSuggestion.toLowerCase() !== title.toLowerCase()" 
                             x-transition:enter="transition ease-out duration-200"
                             x-transition:enter-start="opacity-0 -translate-y-2"
                             x-transition:enter-end="opacity-100 translate-y-0"
                             class="mt-3 p-3.5 rounded-2xl bg-gradient-to-r from-amber-50 via-orange-50 to-amber-50/70 border border-amber-300 shadow-xs flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                            <div class="flex items-start gap-2.5 min-w-0">
                                <span class="text-xl sm:text-2xl shrink-0">💡</span>
                                <div class="min-w-0">
                                    <div class="flex items-center gap-1.5">
                                        <span class="text-[10px] font-black uppercase tracking-wider text-amber-900 bg-amber-200/80 px-1.5 py-0.5 rounded">High-Ranking Google Title</span>
                                        <span class="text-[10px] text-amber-800 font-semibold hidden sm:inline">• Automated Keyword Synthesis</span>
                                    </div>
                                    <div class="text-xs sm:text-sm font-black text-stone-900 mt-1 leading-snug break-words" x-text="smartTitleSuggestion"></div>
                                    <p class="text-[10px] text-stone-500 mt-0.5">Synthesized from your audience, craft fabric & footwear/apparel style.</p>
                                </div>
                            </div>
                            <button type="button" @click="applySmartTitle()" class="shrink-0 px-3.5 py-2 bg-stone-900 hover:bg-black text-amber-300 text-xs font-bold rounded-xl transition-all shadow-sm flex items-center justify-center gap-1.5 cursor-pointer active:scale-95 group">
                                <span>✨ 1-Click Apply</span>
                                <span class="text-stone-400 group-hover:translate-x-0.5 transition-transform">→</span>
                            </button>
                        </div>
                    </div>

                    <!-- Step 1.4: Material / Ingredients & Brand Credit -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-1">
                        <div>
                            @if($targetVertical === 'food')
                                <label class="block text-xs font-black uppercase tracking-wider text-stone-700 mb-1">Key Ingredients & Dietary Profile</label>
                                <input type="text" name="material" x-model="material" placeholder="e.g. Aged Basmati, Pure Desi Ghee, Saffron, Fresh Paneer" class="block w-full rounded-xl border-gray-200 shadow-xs focus:border-brand-sage focus:ring-brand-sage text-sm py-2.5 px-3 font-semibold text-stone-800">
                            @elseif($targetVertical === 'services')
                                <label class="block text-xs font-black uppercase tracking-wider text-stone-700 mb-1">Tools, Equipment & Brand Spares</label>
                                <input type="text" name="material" x-model="material" placeholder="e.g. Jet Pressure Washer, Daikin OEM Spares, HD Makeup Kit" class="block w-full rounded-xl border-gray-200 shadow-xs focus:border-brand-sage focus:ring-brand-sage text-sm py-2.5 px-3 font-semibold text-stone-800">
                            @elseif($targetVertical === 'used')
                                <label class="block text-xs font-black uppercase tracking-wider text-stone-700 mb-1">Cosmetic Condition & Finish</label>
                                <input type="text" name="material" x-model="material" placeholder="e.g. Pristine Screen, 100% Original Chassis, Space Gray" class="block w-full rounded-xl border-gray-200 shadow-xs focus:border-brand-sage focus:ring-brand-sage text-sm py-2.5 px-3 font-semibold text-stone-800">
                            @else
                                <label class="block text-xs font-black uppercase tracking-wider text-stone-700 mb-1">Fabric / Material</label>
                                <input type="text" name="material" x-model="material" placeholder="e.g. Full-Grain Genuine Leather, Pure Linen, Chanderi" class="block w-full rounded-xl border-gray-200 shadow-xs focus:border-brand-sage focus:ring-brand-sage text-sm py-2.5 px-3 font-semibold text-stone-800">
                            @endif
                            
                            <!-- Dynamic Fabric / Ingredient Quick Pills -->
                            <template x-if="fabricPills.length > 0">
                                <div class="mt-2 pt-1.5 border-t border-gray-100">
                                    <span class="text-[9px] uppercase font-bold text-stone-400 block mb-1">
                                        @if($targetVertical === 'food') 1-Tap Ingredient Pick: @else 1-Tap Material Pick: @endif
                                    </span>
                                    <div class="flex flex-wrap gap-1">
                                        <template x-for="fp in fabricPills" :key="fp">
                                            <button type="button" @click="material = fp" 
                                                    :class="material === fp ? 'bg-stone-900 text-white font-bold shadow-xs' : 'bg-stone-100 text-stone-700 hover:bg-stone-200'"
                                                    class="text-[10px] px-2 py-0.5 rounded-lg transition-colors cursor-pointer" x-text="fp"></button>
                                        </template>
                                    </div>
                                </div>
                            </template>
                        </div>

                        <div>
                            @if($targetVertical === 'food')
                                <label class="block text-xs font-black uppercase tracking-wider text-stone-700 mb-1">Kitchen / Chef Brand</label>
                                <input type="text" name="brand" x-model="brand" placeholder="e.g. Dum Pukht Royal Kitchen, Oven Fresh Bakery" class="block w-full rounded-xl border-gray-200 shadow-xs focus:border-brand-sage focus:ring-brand-sage text-sm py-2.5 px-3 font-semibold text-stone-800">
                                <p class="text-[10px] text-stone-400 mt-1">Displays as the chef / kitchen brand on the menu.</p>
                            @elseif($targetVertical === 'services')
                                <label class="block text-xs font-black uppercase tracking-wider text-stone-700 mb-1">Service Provider / Agency</label>
                                <input type="text" name="brand" x-model="brand" placeholder="e.g. Urban Clean Pro, Master Tailors Studio" class="block w-full rounded-xl border-gray-200 shadow-xs focus:border-brand-sage focus:ring-brand-sage text-sm py-2.5 px-3 font-semibold text-stone-800">
                                <p class="text-[10px] text-stone-400 mt-1">Certified service organization or master specialist.</p>
                            @elseif($targetVertical === 'used')
                                <label class="block text-xs font-black uppercase tracking-wider text-stone-700 mb-1">Original Manufacturer Brand</label>
                                <input type="text" name="brand" x-model="brand" placeholder="e.g. Apple, Sony, Dell, Seiko" class="block w-full rounded-xl border-gray-200 shadow-xs focus:border-brand-sage focus:ring-brand-sage text-sm py-2.5 px-3 font-semibold text-stone-800">
                                <p class="text-[10px] text-stone-400 mt-1">Original maker of the item.</p>
                            @else
                                <label class="block text-xs font-black uppercase tracking-wider text-stone-700 mb-1">Brand / Craft Collection</label>
                                <input type="text" name="brand" x-model="brand" placeholder="e.g. Heritage Studio, OGfootwear" class="block w-full rounded-xl border-gray-200 shadow-xs focus:border-brand-sage focus:ring-brand-sage text-sm py-2.5 px-3 font-semibold text-stone-800">
                                <p class="text-[10px] text-stone-400 mt-1">Displays as the maker/atelier credit on the product page.</p>
                            @endif
                        </div>
                    </div>

                    <!-- Step 1.5: Product Story & Guided AI Description -->
                    <div class="pt-2">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 mb-2">
                            <div>
                                <label class="block text-xs font-black uppercase tracking-wider text-stone-700">
                                    Product Story & Description <span class="text-red-500">*</span>
                                </label>
                                <span class="text-[11px] text-stone-500">Engaging narrative with highlights, specifications & care tips.</span>
                            </div>
                            <button type="button" @click="polishDescription()" class="px-3.5 py-1.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold transition-all shadow-xs flex items-center justify-center gap-1.5 cursor-pointer active:scale-95 shrink-0">
                                <span>✨ Magic AI Generate Story</span>
                            </button>
                        </div>
                        <textarea name="description" x-model="description" rows="6" placeholder="Describe the craft, fit, styling, and care..." required class="block w-full rounded-xl border-gray-200 shadow-xs focus:border-brand-sage focus:ring-brand-sage text-xs sm:text-sm p-3.5 leading-relaxed font-normal"></textarea>
                        @error('description') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror

                        <!-- Guided AI Prompt Banner when description is empty or short -->
                        <div x-show="!description || description.length < 40" class="mt-2.5 p-3 rounded-xl bg-emerald-50/80 border border-emerald-200 flex flex-col sm:flex-row sm:items-center justify-between gap-2 text-xs text-emerald-950">
                            <div class="flex items-center gap-2">
                                <span class="text-base">🪄</span>
                                <span>Don't know what to write? Click <strong>Magic AI Generate Story</strong> to auto-craft an authentic artisan description tailored to your category!</span>
                            </div>
                            <button type="button" @click="polishDescription()" class="text-xs font-black text-emerald-800 underline hover:text-emerald-950 shrink-0 cursor-pointer">Generate Now →</button>
                        </div>
                    </div>

                    <!-- Step 1.6: Wash Care / Storage / Service Guarantee -->
                    <div>
                        @if($targetVertical === 'food')
                            <label class="block text-xs font-black uppercase tracking-wider text-stone-700 mb-1">Storage, Shelf Life & Serving Advice</label>
                            <input type="text" name="care_instructions" x-model="careInstructions" placeholder="e.g. Best consumed hot within 2 hours of delivery. Keep refrigerated below 4°C. Microwave safe container." class="block w-full rounded-xl border-gray-200 shadow-xs focus:border-brand-sage focus:ring-brand-sage text-sm py-2.5 px-3 font-semibold text-stone-800">
                        @elseif($targetVertical === 'services')
                            <label class="block text-xs font-black uppercase tracking-wider text-stone-700 mb-1">Customer Prerequisites & Site Access Notes</label>
                            <input type="text" name="care_instructions" x-model="careInstructions" placeholder="e.g. Customer to provide 15A power plug and running water supply. Elevator access required." class="block w-full rounded-xl border-gray-200 shadow-xs focus:border-brand-sage focus:ring-brand-sage text-sm py-2.5 px-3 font-semibold text-stone-800">
                        @elseif($targetVertical === 'used')
                            <label class="block text-xs font-black uppercase tracking-wider text-stone-700 mb-1">Testing, Inspection & Battery Health Notes</label>
                            <input type="text" name="care_instructions" x-model="careInstructions" placeholder="e.g. 100% battery health, all ports & sensors verified functional, clean iCloud/Google reset." class="block w-full rounded-xl border-gray-200 shadow-xs focus:border-brand-sage focus:ring-brand-sage text-sm py-2.5 px-3 font-semibold text-stone-800">
                        @else
                            <label class="block text-xs font-black uppercase tracking-wider text-stone-700 mb-1">Wash Care & Maintenance</label>
                            <input type="text" name="care_instructions" x-model="careInstructions" placeholder="e.g. Wipe clean with damp cloth. Polish regularly with leather cream." class="block w-full rounded-xl border-gray-200 shadow-xs focus:border-brand-sage focus:ring-brand-sage text-sm py-2.5 px-3 font-semibold text-stone-800">
                        @endif
                        <template x-if="carePills.length > 0">
                            <div class="mt-2 pt-1.5 border-t border-gray-100">
                                <span class="text-[9px] uppercase font-bold text-stone-400 block mb-1">1-Tap Quick Pick:</span>
                                <div class="flex flex-wrap gap-1">
                                    <template x-for="cp in carePills" :key="cp">
                                        <button type="button" @click="careInstructions = cp" 
                                                :class="careInstructions === cp ? 'bg-stone-900 text-white font-bold shadow-xs' : 'bg-stone-100 text-stone-700 hover:bg-stone-200'"
                                                class="text-[10px] px-2 py-0.5 rounded-lg transition-colors cursor-pointer" x-text="cp"></button>
                                    </template>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>

                <!-- 2. Interactive Media Gallery -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 sm:p-8" x-data="mediaGalleryEngine()">
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
                                <span x-text="items.length"></span> Photos Selected
                            </span>
                        </template>
                    </div>

                    <!-- Hidden Real File Input -->
                    <input id="images-input" name="images[]" type="file" multiple class="sr-only" accept="image/png, image/jpeg, image/webp"
                           @change="handleFiles($event.target.files)">

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
                                        <div class="absolute top-2 left-2 z-10">
                                            <template x-if="idx === 0">
                                                <span class="inline-flex items-center gap-1 bg-amber-500 text-white text-[10px] font-black px-2 py-0.5 rounded-md shadow-sm uppercase tracking-wider">
                                                    ★ Cover
                                                </span>
                                            </template>
                                            <template x-if="idx > 0">
                                                <span class="inline-flex items-center bg-black/70 backdrop-blur-sm text-white text-[10px] font-bold px-2 py-0.5 rounded-md" x-text="'#' + (idx + 1)"></span>
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
                                <span>👔</span> Shirts & Kurtas
                            </button>
                            <button type="button" @click="applyPreset('dress')" 
                                    :class="sizeGuide.type === 'dress' ? 'bg-brand-sage text-white shadow-xs' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'"
                                    class="px-3 py-1.5 rounded-xl text-xs font-bold transition-all flex items-center gap-1.5">
                                <span>💃</span> Western Dresses
                            </button>
                            <button type="button" @click="applyPreset('jeans')" 
                                    :class="sizeGuide.type === 'jeans' ? 'bg-brand-sage text-white shadow-xs' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'"
                                    class="px-3 py-1.5 rounded-xl text-xs font-bold transition-all flex items-center gap-1.5">
                                <span>👖</span> Jeans & Bottoms
                            </button>
                            <button type="button" @click="applyPreset('kurti')" 
                                    :class="sizeGuide.type === 'kurti' ? 'bg-brand-sage text-white shadow-xs' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'"
                                    class="px-3 py-1.5 rounded-xl text-xs font-bold transition-all flex items-center gap-1.5">
                                <span>🥻</span> Kurtis & Suits
                            </button>
                            <button type="button" @click="applyPreset('saree')" 
                                    :class="sizeGuide.type === 'saree' ? 'bg-brand-sage text-white shadow-xs' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'"
                                    class="px-3 py-1.5 rounded-xl text-xs font-bold transition-all flex items-center gap-1.5">
                                <span>🧵</span> Sarees & Drapes
                            </button>
                            <button type="button" @click="applyPreset('kids')" 
                                    :class="sizeGuide.type === 'kids' ? 'bg-brand-sage text-white shadow-xs' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'"
                                    class="px-3 py-1.5 rounded-xl text-xs font-bold transition-all flex items-center gap-1.5">
                                <span>👶</span> Kids Apparel
                            </button>
                            <button type="button" @click="applyPreset('footwear')" 
                                    :class="sizeGuide.type === 'footwear' ? 'bg-brand-sage text-white shadow-xs' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'"
                                    class="px-3 py-1.5 rounded-xl text-xs font-bold transition-all flex items-center gap-1.5">
                                <span>👟</span> Footwear & Shoes
                            </button>
                            <button type="button" @click="applyPreset('none')" 
                                    :class="sizeGuide.type === 'none' ? 'bg-red-600 text-white shadow-xs' : 'bg-gray-100 text-gray-500 hover:bg-gray-200'"
                                    class="px-3 py-1.5 rounded-xl text-xs font-bold transition-all flex items-center gap-1.5">
                                <span>🚫</span> No Guide (Bags / Accs)
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
                            <select name="status" x-model="status" class="block w-full rounded-lg border-gray-200 shadow-sm focus:border-brand-sage text-sm font-medium">
                                <option value="active">Active (Visible)</option>
                                <option value="draft">Draft (Hidden)</option>
                            </select>
                        </div>
                        
                        <div class="pt-1 border-t border-gray-100">
                            <label class="block text-xs font-bold text-gray-700 mb-1">Selected Category</label>
                            <div class="px-3 py-2 bg-stone-50 border border-stone-200 rounded-xl text-xs font-semibold text-stone-800">
                                <span x-text="categoryId ? (document.querySelector('select[name=category_id] option:checked')?.textContent.trim() || 'Category Selected') : 'None Selected (Choose in Section 1)'"></span>
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1">Search Tags</label>
                            <input type="text" name="tags" x-model="tags" placeholder="summer, casual, party (comma separated)" class="block w-full rounded-lg border-gray-200 shadow-sm focus:border-brand-sage text-sm">
                        </div>
                    </div>
                </div>

                <!-- Pricing & Tax -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                    <h3 class="text-sm font-bold text-gray-900 uppercase tracking-wider mb-4 border-b border-gray-100 pb-2">Pricing & Tax</h3>
                    
                    <div class="space-y-4">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-gray-700 mb-1">Base Selling Price (₹) *</label>
                                <input type="number" step="0.01" min="0" name="base_price" x-model="basePrice" required class="block w-full rounded-lg border-gray-200 shadow-sm focus:border-brand-sage text-sm font-bold text-green-600">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-700 mb-1">MRP (₹)</label>
                                <input type="number" step="0.01" min="0" name="compare_at_price" x-model="compareAtPrice" placeholder="Crossed out" class="block w-full rounded-lg border-gray-200 shadow-sm focus:border-brand-sage text-sm text-gray-500 line-through">
                            </div>
                        </div>
                        
                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1">Base Inventory (If no variants) *</label>
                            <input type="number" min="0" name="total_stock" x-model="totalStock" required class="block w-full rounded-lg border-gray-200 shadow-sm focus:border-brand-sage text-sm">
                        </div>
                        
                        <div class="grid grid-cols-2 gap-4 pt-2">
                            <div>
                                <label class="block text-xs font-bold text-gray-700 mb-1">GST Rate *</label>
                                <select name="gst_rate" x-model="gstRate" class="block w-full rounded-lg border-gray-200 shadow-sm focus:border-brand-sage text-sm font-medium">
                                    <option value="5">5% (Apparel < 1000)</option>
                                    <option value="12">12% (Apparel > 1000)</option>
                                    <option value="18">18%</option>
                                    <option value="0">0%</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-700 mb-1">HSN Code</label>
                                <input type="text" name="hsn_code" x-model="hsnCode" placeholder="e.g. 6109" class="block w-full rounded-lg border-gray-200 shadow-sm focus:border-brand-sage text-sm font-mono">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Logistics & Fulfillment -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                    <h3 class="text-sm font-bold text-gray-900 uppercase tracking-wider mb-4 border-b border-gray-100 pb-2">
                        @if($targetVertical === 'food')
                            Kitchen Logistics & Packaging
                        @elseif($targetVertical === 'services')
                            Service Delivery & Assurance
                        @elseif($targetVertical === 'used')
                            Shipping & Inspection
                        @else
                            Logistics & Shipping
                        @endif
                    </h3>
                    
                    <div class="space-y-4">
                        @if($targetVertical === 'food')
                            <div>
                                <label class="block text-xs font-bold text-gray-700 mb-1">Serving / Portion Weight</label>
                                <div class="relative">
                                    <input type="number" name="weight_grams" x-model="weightGrams" placeholder="e.g. 500" min="1" max="50000" class="block w-full rounded-lg border-gray-200 shadow-sm focus:border-brand-sage text-sm pr-14 font-medium">
                                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                        <span class="text-gray-400 text-xs font-bold">grams</span>
                                    </div>
                                </div>
                                <p class="text-[11px] text-gray-400 mt-1">Net food weight per serving container (e.g. 500g Handi, 10-inch pizza).</p>
                            </div>

                            <div class="p-3 rounded-xl bg-emerald-50 border border-emerald-200 text-xs text-emerald-950 font-medium leading-relaxed">
                                🥗 <strong>Fresh Food Quality Guarantee:</strong> Perishable culinary items are non-returnable once opened. Immediate kitchen replacement if seal is tampered or food arrives compromised.
                            </div>
                            <input type="hidden" name="is_returnable" value="0">
                            <input type="hidden" name="return_window_days" value="0">
                            <input type="hidden" name="country_of_origin" value="India">
                        @elseif($targetVertical === 'services')
                            <div class="p-3 rounded-xl bg-blue-50 border border-blue-200 text-xs text-blue-950 font-medium leading-relaxed">
                                🛡️ <strong>Service Satisfaction Assurance:</strong> Free revisit & rework within 48 hours if customer is not 100% satisfied with workmanship.
                            </div>
                            <input type="hidden" name="weight_grams" value="0">
                            <input type="hidden" name="is_returnable" value="0">
                            <input type="hidden" name="return_window_days" value="0">
                            <input type="hidden" name="country_of_origin" value="India">
                        @elseif($targetVertical === 'used')
                            <div>
                                <div class="flex items-center justify-between mb-1">
                                    <label class="block text-xs font-bold text-gray-700">Item Shipping Weight</label>
                                    <span class="text-[10px] font-semibold text-amber-700 bg-amber-50 px-2 py-0.5 rounded">Per unit</span>
                                </div>
                                <div class="relative">
                                    <input type="number" name="weight_grams" x-model="weightGrams" placeholder="e.g. 800" min="1" max="50000" class="block w-full rounded-lg border-gray-200 shadow-sm focus:border-brand-sage text-sm pr-14 font-medium">
                                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                        <span class="text-gray-400 text-xs font-bold">grams</span>
                                    </div>
                                </div>
                            </div>
                            <div class="pt-1">
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="checkbox" name="is_returnable" value="1" checked class="rounded border-gray-300 text-brand-sage focus:ring-brand-sage w-4 h-4">
                                    <span class="text-xs font-bold text-gray-700">Buyer Protection Inspection Window</span>
                                </label>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-700 mb-1">Inspection Window (Days)</label>
                                <input type="number" name="return_window_days" x-model="returnWindowDays" value="3" class="block w-full rounded-lg border-gray-200 shadow-sm focus:border-brand-sage text-sm">
                            </div>
                            <input type="hidden" name="country_of_origin" value="India">
                        @else
                            <div>
                                <div class="flex items-center justify-between mb-1">
                                    <label class="block text-xs font-bold text-gray-700">Package Weight (Per Piece)</label>
                                    <span class="text-[10px] font-semibold text-amber-700 bg-amber-50 px-2 py-0.5 rounded">Per garment</span>
                                </div>
                                <div class="relative">
                                    <input type="number" name="weight_grams" x-model="weightGrams" placeholder="e.g. 250" min="1" max="50000" class="block w-full rounded-lg border-gray-200 shadow-sm focus:border-brand-sage text-sm pr-14 font-medium">
                                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                        <span class="text-gray-400 text-xs font-bold">grams</span>
                                    </div>
                                </div>
                                <p class="text-[11px] text-gray-400 mt-1">Weight of single packaged garment piece (e.g. 250g). Used for courier manifests & shipping labels.</p>
                            </div>
                            
                            <div class="pt-2">
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="checkbox" name="is_returnable" value="1" checked class="rounded border-gray-300 text-brand-sage focus:ring-brand-sage w-4 h-4">
                                    <span class="text-sm font-bold text-gray-700">Allow Returns for this product</span>
                                </label>
                            </div>
                            
                            <div>
                                <label class="block text-xs font-bold text-gray-700 mb-1">Return Window (Days)</label>
                                <input type="number" name="return_window_days" x-model="returnWindowDays" class="block w-full rounded-lg border-gray-200 shadow-sm focus:border-brand-sage text-sm">
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-gray-700 mb-1">Country of Origin</label>
                                <input type="text" name="country_of_origin" x-model="countryOfOrigin" class="block w-full rounded-lg border-gray-200 shadow-sm focus:border-brand-sage text-sm">
                            </div>
                        @endif
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
                <span>Save Product</span>
            </button>
        </div>

        <!-- Floating Live Toast Feedback -->
        <div x-show="showToast" 
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-y-4 scale-95"
             x-transition:enter-end="opacity-100 translate-y-0 scale-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-y-0 scale-100"
             x-transition:leave-end="opacity-0 translate-y-4 scale-95"
             class="fixed bottom-20 right-4 sm:right-8 z-50 max-w-md bg-stone-900 text-white p-4 rounded-2xl shadow-2xl border border-amber-400/50 flex items-start gap-3"
             style="display: none;">
            <div class="w-8 h-8 rounded-xl bg-amber-400 text-stone-950 flex items-center justify-center font-bold text-base shrink-0">⚡</div>
            <div class="flex-1 pr-2">
                <h4 class="text-xs font-black uppercase tracking-wider text-amber-400">Preset Applied to Fullest Extent</h4>
                <p class="text-xs text-stone-200 mt-0.5 leading-snug" x-text="toastMessage"></p>
            </div>
            <button type="button" @click="showToast = false" class="text-stone-400 hover:text-white text-xs p-1 cursor-pointer">✕</button>
        </div>
    </form>
    
    <script>
        function variantEngine() {
            return {
                vertical: @json(old('vertical', $targetVertical)),
                listingType: @json(old('listing_type', $targetVertical === 'services' ? 'booking' : ($targetVertical === 'used' ? 'contact_seller' : 'purchase'))),
                customFields: @json(old('custom_fields', (object)[])),
                verticalsList: @json($verticals),
                listingTypesList: @json($listingTypes),
                allCategoryFields: @json($categoryFields),

                get currentFields() {
                    return this.allCategoryFields[this.vertical] || [];
                },

                updateVertical(v) {
                    this.vertical = v;
                    if (this.verticalsList[v] && this.verticalsList[v].default_listing) {
                        this.listingType = this.verticalsList[v].default_listing;
                    }
                },

                title: @json(old('title', '')),
                description: @json(old('description', '')),
                categoryId: @json(old('category_id', '')),
                material: @json(old('material', '')),
                careInstructions: @json(old('care_instructions', '')),
                gender: @json(old('gender', 'women')),
                brand: @json(old('brand', '')),
                tags: @json(old('tags', '')),
                basePrice: @json(old('base_price', '')),
                compareAtPrice: @json(old('compare_at_price', '')),
                totalStock: @json(old('total_stock', 10)),
                gstRate: @json(old('gst_rate', '5')),
                hsnCode: @json(old('hsn_code', '')),
                weightGrams: @json(old('weight_grams', '')),
                returnWindowDays: @json(old('return_window_days', 7)),
                countryOfOrigin: @json(old('country_of_origin', 'India')),
                status: @json(old('status', 'active')),

                activePreset: null,
                presetTab: 'all',
                categorySlugMap: @json($categories->pluck('id', 'slug')),
                toastMessage: '',
                showToast: false,

                fabricPills: [],
                carePills: [],
                availableSizes: [],
                selectedSizes: [],

                options: [],
                variants: [],
                bulkPrice: '',
                bulkStock: '',

                // Dynamic Blueprint Engine State
                verticalType: @json(old('vertical_type', $targetVertical === 'marketplace' ? 'fashion_matrix' : ($targetVertical === 'food' ? 'food_portion' : ($targetVertical === 'services' ? 'service_booking' : ($targetVertical === 'used' ? 'classified_asset' : 'standard_retail'))))),
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
                selectedColors: [
                    { name: 'Jet Black', hex: '#111827' },
                    { name: 'Navy Blue', hex: '#1E3A8A' }
                ],
                customColorName: '',
                customColorHex: '#4F46E5',
                availableSizesList: ['XS', 'S', 'M', 'L', 'XL', 'XXL', '3XL', 'Free Size'],
                selectedSizes: ['M', 'L', 'XL'],
                customSizeName: '',
                fashionMatrixRows: [],
                foodPortions: [
                    { name: '1 Plate (Standard)', price: '', stock: 50 },
                    { name: '2 Plates (Family Pack)', price: '', stock: 30 }
                ],
                foodModifiers: [
                    { group_name: 'Add-ons', modifier_name: 'Extra Portion', price_delta: '40.00', is_required: false }
                ],
                gigPackages: {
                    basic: {
                        tier: 'basic',
                        title: 'Starter Concept',
                        description: 'Core starter deliverable with basic asset package and essential setup.',
                        delivery_days: 3,
                        revisions: 1,
                        features: { source_file: false, commercial_rights: false, high_resolution: true },
                        price: ''
                    },
                    standard: {
                        tier: 'standard',
                        title: 'Standard Commercial',
                        description: 'Recommended comprehensive deliverable with source files and standard revisions.',
                        delivery_days: 5,
                        revisions: 3,
                        features: { source_file: true, commercial_rights: true, high_resolution: true },
                        price: ''
                    },
                    premium: {
                        tier: 'premium',
                        title: 'Complete VIP Enterprise',
                        description: 'Turnkey enterprise package with unlimited revisions, priority support, and full source assets.',
                        delivery_days: 7,
                        revisions: 99,
                        features: { source_file: true, commercial_rights: true, high_resolution: true },
                        price: ''
                    }
                },
                enableRfq: true,
                specs: {
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
                },
                b2bTiers: [
                    { min_qty: 1, max_qty: 5, unit_price: '' },
                    { min_qty: 6, max_qty: 25, unit_price: '' },
                    { min_qty: 26, max_qty: 100, unit_price: '' }
                ],
                serviceConfig: {
                    duration_minutes: '60',
                    daily_slot_capacity: 6,
                    inclusions: ['Complete on-site inspection & diagnostic check', 'Standard service execution & clean handover'],
                    exclusions: ['Replacement hardware or consumable spares billed at actuals']
                },
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

                get filteredPresets() {
                    if (this.presetTab === 'all') {
                        return this.categoryPresets;
                    }
                    const res = {};
                    for (const [k, v] of Object.entries(this.categoryPresets)) {
                        if (v.tab === this.presetTab) {
                            res[k] = v;
                        }
                    }
                    return res;
                },

                categoryPresets: {
                    @if($targetVertical === 'food')
                    biryani_dum: {
                        name: "Royal Awadhi Dum Biryani (Clay Handi)",
                        shortName: 'Dum Biryani',
                        tab: 'biryani',
                        icon: '🍲',
                        badge: '500g Handi • Sealed',
                        categorySlug: 'food-biryani-rice',
                        categoryId: 4,
                        basePrice: '380',
                        compareAtPrice: '450',
                        totalStock: 50,
                        skuPrefix: 'BRY',
                        title: 'Authentic Royal Awadhi Dum Biryani with Fragrant Saffron & Handi Sealed Pot',
                        description: `Slow-cooked to perfection using traditional Dum Pukht techniques. Fragrant long-grain aged Basmati rice layered with rich spices, fresh marinated cuts, brown onions, pure desi ghee, and saffron milk, sealed in an earthen clay handi with dough to lock in the aroma. Served with creamy burani raita and mirchi ka salan.`,
                        material: 'Aged Basmati Rice, Pure Desi Ghee, Saffron, Shahi Jeera, Star Anise',
                        fabricPills: ['Pure Desi Ghee', 'Farm Fresh Paneer', 'Aged Basmati Rice', 'Saffron Aroma', 'Kashmiri Chili', 'Low Oil / Healthy'],
                        careInstructions: 'Best enjoyed fresh and piping hot upon delivery. If reheating, microwave in container for 90 seconds or warm on a tawa.',
                        carePills: ['Best served hot', 'Consume within 4 hours', 'Keep refrigerated', 'Microwave safe bowl', 'Tamper evident seal'],
                        weightGrams: 550,
                        hsnCode: '2106',
                        gstRate: '5',
                        tags: 'biryani, dum biryani, handi biryani, royal biryani, raita, lunch, dinner',
                        status: 'active',
                        sizeGuidePreset: 'none',
                        availableSizes: ['Single Portion (500g)', 'Couple Handi (1kg)', 'Party Handi (2.5kg)'],
                        selectedSizes: ['Single Portion (500g)', 'Couple Handi (1kg)']
                    },
                    pizza_artisan: {
                        name: "Wood-Fired Artisan Pizza",
                        shortName: 'Artisan Pizza',
                        tab: 'fast_food',
                        icon: '🍕',
                        badge: '10" Fresh Sourdough',
                        categorySlug: 'food-fast-food-snacks',
                        categoryId: 5,
                        basePrice: '320',
                        compareAtPrice: '399',
                        totalStock: 40,
                        skuPrefix: 'PIZ',
                        title: 'Artisan Wood-Fired Sourdough Pizza with San Marzano Tomatoes & Fresh Mozzarella',
                        description: `Handcrafted 48-hour fermented sourdough crust baked in a high-temperature stone oven. Layered with authentic San Marzano plum tomato sauce, creamy buffalo mozzarella, extra virgin olive oil, and fragrant sweet basil leaves.`,
                        material: 'Fermented Sourdough Crust, San Marzano Sauce, Fresh Fior di Latte Mozzarella, Extra Virgin Olive Oil',
                        fabricPills: ['Fresh Mozzarella', 'Fermented Sourdough', 'San Marzano Sauce', 'Extra Virgin Olive Oil', 'Sweet Basil'],
                        careInstructions: 'Eat immediately for crisp crust. Reheat on a hot dry pan for 2 minutes to restore crunchiness.',
                        carePills: ['Eat within 45 mins', 'Crisp pan reheat', 'Do not microwave crust', 'Fresh cheese guarantee'],
                        weightGrams: 420,
                        hsnCode: '2106',
                        gstRate: '5',
                        tags: 'pizza, sourdough, wood fired, italian, margherita, fast food, cheese',
                        status: 'active',
                        sizeGuidePreset: 'none',
                        availableSizes: ['Regular 10-inch', 'Large 12-inch', 'Family 14-inch'],
                        selectedSizes: ['Regular 10-inch', 'Large 12-inch']
                    },
                    gourmet_burger: {
                        name: "Gourmet Brioche Smash Burger",
                        shortName: 'Brioche Burger',
                        tab: 'fast_food',
                        icon: '🍔',
                        badge: 'Brioche Bun',
                        categorySlug: 'food-fast-food-snacks',
                        categoryId: 5,
                        basePrice: '210',
                        compareAtPrice: '280',
                        totalStock: 35,
                        skuPrefix: 'BGR',
                        title: 'Smoked Jalapeno & Cheddar Gourmet Brioche Burger with Hand-Cut Fries',
                        description: `Toasted butter-glazed brioche bun stacked with a juicy flame-grilled patty, melted aged yellow cheddar, caramelized balsamic onions, gherkins, and signature secret truffle aioli sauce. Served with seasoned potato wedges.`,
                        material: 'Butter Brioche, Aged Cheddar Cheese, Gherkins, Signature Truffle Aioli',
                        fabricPills: ['Butter Brioche', 'Aged Cheddar', 'Caramelized Onions', 'Zero Palm Oil', 'Handmade Patty'],
                        careInstructions: 'Served warm with crisp fries. Consume within 2 hours.',
                        carePills: ['Eat fresh', 'Warm 30s in microwave if desired', 'Fries best crisp'],
                        weightGrams: 360,
                        hsnCode: '2106',
                        gstRate: '5',
                        tags: 'burger, smash burger, brioche, cheese burger, fries, fast food',
                        status: 'active',
                        sizeGuidePreset: 'none',
                        availableSizes: ['Single Stack', 'Double Stack (Heavy)', 'Meal with Fries & Drink'],
                        selectedSizes: ['Single Stack', 'Meal with Fries & Drink']
                    },
                    kathi_roll: {
                        name: "Kolkata Double Egg / Paneer Kathi Roll",
                        shortName: 'Kathi Roll',
                        tab: 'fast_food',
                        icon: '🌯',
                        badge: 'Flaky Paratha',
                        categorySlug: 'food-fast-food-snacks',
                        categoryId: 5,
                        basePrice: '160',
                        compareAtPrice: '200',
                        totalStock: 60,
                        skuPrefix: 'ROL',
                        title: 'Signature Charcoal Grilled Tikka Kathi Roll in Flaky Laccha Paratha',
                        description: `Freshly made crisp multilayered laccha paratha wrapped around succulent smoky charcoal-grilled filling, slivered red onions, fresh green chillies, mint coriander chutney, and a squeeze of fresh lime.`,
                        material: 'Handmade Laccha Paratha, Tandoori Marinade, Fresh Mint Chutney, Lemon Pepper',
                        fabricPills: ['Laccha Paratha', 'Tandoori Tikka', 'Mint Chutney', 'Low Oil Tawa Roast'],
                        careInstructions: 'Hot roll wrapped in food-grade foil. Enjoy hot.',
                        carePills: ['Consume within 3 hours', 'Foil wrapped warm', 'No preservatives'],
                        weightGrams: 280,
                        hsnCode: '2106',
                        gstRate: '5',
                        tags: 'roll, kathi roll, paneer roll, chicken roll, kolkata roll, street food, snacks',
                        status: 'active',
                        sizeGuidePreset: 'none',
                        availableSizes: ['Single Roll', 'Jumbo Double Roll', 'Combo of 2 Rolls'],
                        selectedSizes: ['Single Roll', 'Jumbo Double Roll']
                    },
                    maharaja_thali: {
                        name: "Royal Maharaja Special Deluxe Thali",
                        shortName: 'Deluxe Thali',
                        tab: 'meals',
                        icon: '🍛',
                        badge: 'Grand 8-Dish Platter',
                        categorySlug: 'food-north-indian',
                        categoryId: 2,
                        basePrice: '320',
                        compareAtPrice: '420',
                        totalStock: 30,
                        skuPrefix: 'THL',
                        title: 'Grand Maharaja Deluxe Thali with Dal Makhani, Paneer Tikka Masala, Pulao & Breads',
                        description: `Complete regal dining experience packed in a 5-compartment eco-seal tray. Includes Slow-Cooked Dal Makhani, Paneer Lababdar / Butter Chicken, Jeera Pulao, 2 Butter Phulkas / Naan, Boondi Raita, Salad, Pickle, and Warm Gulab Jamun.`,
                        material: 'Pure Desi Ghee Dal Makhani, Fresh Paneer, Saffron Jeera Rice, Artisanal Naan',
                        fabricPills: ['Pure Desi Ghee', 'Fresh Paneer', 'Zero Preservatives', '5-Section Seal Pack'],
                        careInstructions: 'Each item is individually sealed. Remove lid and microwave container safely for 90 seconds.',
                        carePills: ['Microwave safe tray', 'Best consumed within 3h', 'Complete meal solution'],
                        weightGrams: 750,
                        hsnCode: '2106',
                        gstRate: '5',
                        tags: 'thali, deluxe thali, north indian, lunch meal, dinner meal, dal makhani, paneer',
                        status: 'active',
                        sizeGuidePreset: 'none',
                        availableSizes: ['Standard Deluxe Thali', 'Executive Premium Thali (with Sweet & Lassi)'],
                        selectedSizes: ['Standard Deluxe Thali', 'Executive Premium Thali (with Sweet & Lassi)']
                    },
                    tandoori_starters: {
                        name: "Smoked Tandoori Seekh & Tikka Platter",
                        shortName: 'Tandoori Platter',
                        tab: 'fast_food',
                        icon: '🍢',
                        badge: '8 Pcs • Clay Oven',
                        categorySlug: 'food-fast-food-snacks',
                        categoryId: 5,
                        basePrice: '280',
                        compareAtPrice: '350',
                        totalStock: 40,
                        skuPrefix: 'TND',
                        title: 'Charcoal Smoked Tandoori Malai Tikka & Seekh Kebab Platter with Chutney',
                        description: `Marinated overnight in hung curd, ginger garlic paste, green cardamom, and aromatic ground spices, then skewered and charred to perfection in a traditional clay tandoor. Served with lachha onions and spicy mint chutney.`,
                        material: 'Clay Tandoor Roast, Hung Curd Marinade, Green Cardamom, Fresh Mint Chutney',
                        fabricPills: ['Clay Oven Tandoor', 'Hung Curd Marinade', 'Smoky Charcoal Flavour'],
                        careInstructions: 'Enjoy piping hot with lemon juice and pickled onions.',
                        carePills: ['Best served hot', 'Consume within 2 hours', 'Foil sealed container'],
                        weightGrams: 350,
                        hsnCode: '2106',
                        gstRate: '5',
                        tags: 'kebab, seekh kebab, tikka, tandoori, starters, appetiser, bbq',
                        status: 'active',
                        sizeGuidePreset: 'none',
                        availableSizes: ['Half (4 Pcs)', 'Full (8 Pcs Platter)'],
                        selectedSizes: ['Half (4 Pcs)', 'Full (8 Pcs Platter)']
                    },
                    dessert_fusion: {
                        name: "Warm Shahi Tukda with Pistachio Rabri",
                        shortName: 'Shahi Tukda',
                        tab: 'desserts',
                        icon: '🍮',
                        badge: 'Traditional Clay Pot',
                        categorySlug: 'food-desserts-sweets',
                        categoryId: 6,
                        basePrice: '140',
                        compareAtPrice: '180',
                        totalStock: 45,
                        skuPrefix: 'SWT',
                        title: 'Traditional Awadhi Shahi Tukda with Saffron Infused Malai Rabri & Pistachios',
                        description: `Classic Nawabi dessert made of crisp golden ghee-fried bread triangles soaked in fragrant saffron sugar syrup, blanketed with slow-simmered thick creamy rabri, and garnished with slivered pistachios and silver vark.`,
                        material: 'Pure Desi Ghee, Saffron Strands, Condensed Milk Rabri, California Pistachios',
                        fabricPills: ['Pure Desi Ghee', 'Saffron Infusion', 'Slow Cooked Rabri', 'Silver Vark'],
                        careInstructions: 'Store refrigerated below 4°C. Consume within 24 hours. Can be enjoyed chilled or warm.',
                        carePills: ['Keep refrigerated', 'Consume within 24h', 'Food grade dessert tub'],
                        weightGrams: 220,
                        hsnCode: '2106',
                        gstRate: '5',
                        tags: 'dessert, shahi tukda, rabri, sweets, mithai, festival sweet',
                        status: 'active',
                        sizeGuidePreset: 'none',
                        availableSizes: ['Single Serving (2 Pcs)', 'Double Bowl (4 Pcs)'],
                        selectedSizes: ['Single Serving (2 Pcs)', 'Double Bowl (4 Pcs)']
                    },
                    beverage_cooler: {
                        name: "Kulhad Kesar Badam Malai Lassi",
                        shortName: 'Kulhad Lassi',
                        tab: 'desserts',
                        icon: '🥤',
                        badge: 'Clay Kulhad 350ml',
                        categorySlug: 'food-beverages-juices',
                        categoryId: 7,
                        basePrice: '90',
                        compareAtPrice: '120',
                        totalStock: 50,
                        skuPrefix: 'BEV',
                        title: 'Thick Hand-Churned Punjabi Kesar Badam Lassi in Clay Kulhad with Malai Top',
                        description: `Traditional probiotic curd hand-churned with Kashmiri saffron syrup, crushed cardamom, and sweet khandsari sugar. Topped with a thick dollop of clotted fresh malai and chopped almonds. Served in an earthy terracotta kulhad.`,
                        material: 'Whole Milk Curd, Saffron Extract, Fresh Dairy Malai, Almond Flakes',
                        fabricPills: ['Hand Churned Curd', 'Terracotta Kulhad', 'Rich Malai Layer', 'Kashmiri Saffron'],
                        careInstructions: 'Serve chilled. Shake gently or stir before drinking.',
                        carePills: ['Serve chilled', 'Consume within 24 hours', 'Keep refrigerated'],
                        weightGrams: 380,
                        hsnCode: '2202',
                        gstRate: '5',
                        tags: 'lassi, cooler, beverage, punjabi lassi, kesar badam, curd drink, drink',
                        status: 'active',
                        sizeGuidePreset: 'none',
                        availableSizes: ['Regular Kulhad (350ml)', 'Jumbo Kulhad (500ml)'],
                        selectedSizes: ['Regular Kulhad (350ml)']
                    },
                    @elseif($targetVertical === 'services')
                    ac_jet_service: {
                        name: "Master Split AC Deep Jet Wash & Gas Check",
                        shortName: 'AC Jet Service',
                        tab: 'repairs',
                        icon: '❄️',
                        badge: '60 Mins • Jet Pump',
                        categorySlug: 'services-appliance-repair',
                        categoryId: 37,
                        basePrice: '599',
                        compareAtPrice: '999',
                        totalStock: 50,
                        skuPrefix: 'AC',
                        title: 'Master Split AC Indoor & Outdoor High-Pressure Jet Wash with Gas Leakage Inspection',
                        description: `Comprehensive 2-technician air conditioner deep cleaning service. Includes indoor cooling coil deep jet cleaning using anti-fungal foam, blower and drain tray sanitization, outdoor condenser coil power wash, and refrigerant pressure check.`,
                        material: 'High-Pressure Jet Pump, Anti-Fungal Foam, Pressure Gauges, Multi-Meter',
                        fabricPills: ['High-Pressure Jet Wash', 'Anti-Fungal Coil Spray', 'Drain Tray Sanitization', 'Refrigerant Pressure Check'],
                        careInstructions: 'Customer to provide continuous running water and 15A electrical outlet. Free parking on premise preferred.',
                        carePills: ['Customer supplies water', '15A power socket', '30-day service warranty', 'Background verified'],
                        weightGrams: 0,
                        hsnCode: '9987',
                        gstRate: '18',
                        tags: 'ac service, ac cleaning, split ac, ac repair, jet wash, appliance',
                        status: 'active',
                        sizeGuidePreset: 'none',
                        availableSizes: ['1 Unit Split AC', '2 Units Combo (Discounted)', '3 Units Full Home'],
                        selectedSizes: ['1 Unit Split AC', '2 Units Combo (Discounted)']
                    },
                    deep_home_clean: {
                        name: "Full Home Deep Sanitization & Kitchen Scrub",
                        shortName: 'Deep Cleaning',
                        tab: 'cleaning',
                        icon: '🧹',
                        badge: '3-4 Hours • Team of 3',
                        categorySlug: 'services-cleaning-deep-sanitization',
                        categoryId: 11,
                        basePrice: '1999',
                        compareAtPrice: '2999',
                        totalStock: 20,
                        skuPrefix: 'CLN',
                        title: 'Complete 3BHK Full Home Deep Sanitization, Machine Floor Scrubbing & Degreasing',
                        description: `Intensive top-to-bottom sanitization executed by a 3-member certified crew. Covers single-disc machine floor buffing, kitchen chimney & tile oil degreasing, bathroom descaling & disinfection, balcony pressure wash, and ceiling web removal.`,
                        material: 'Taski Chemical Sanitizers, Single-Disc Scrubbing Machine, Commercial Wet Vacuum',
                        fabricPills: ['Taski Professional Chemicals', 'Single-Disc Floor Buffer', 'Oil Degreaser', 'Steam Disinfection'],
                        careInstructions: 'Property must have uninterrupted electricity and water. Please keep personal valuables locked.',
                        carePills: ['Electricity & water required', 'Lock valuables', 'Free rework warranty'],
                        weightGrams: 0,
                        hsnCode: '9985',
                        gstRate: '18',
                        tags: 'cleaning, deep clean, home cleaning, kitchen cleaning, floor scrub, sanitization',
                        status: 'active',
                        sizeGuidePreset: 'none',
                        availableSizes: ['1 BHK Complete', '2 BHK Complete', '3 BHK Complete', '4 BHK / Villa'],
                        selectedSizes: ['2 BHK Complete', '3 BHK Complete']
                    },
                    tailor_blouse: {
                        name: "Designer Blouse / Kurti Bespoke Tailoring",
                        shortName: 'Bespoke Tailoring',
                        tab: 'tailoring',
                        icon: '✂️',
                        badge: 'Doorstep Pickup',
                        categorySlug: 'services-tailoring-alterations',
                        categoryId: 13,
                        basePrice: '750',
                        compareAtPrice: '1100',
                        totalStock: 30,
                        skuPrefix: 'TLR',
                        title: 'Custom Designer Blouse / Kurti Tailoring with Doorstep Measurement & Free Alterations',
                        description: `Our master cutter visits your home for precision measurements and style consultation. Choose from sweetheart, princess-cut, boat-neck, or padded backless designs. Includes premium inner lining and piping finish. Delivered to your doorstep within 4 days.`,
                        material: 'Pure Cotton Inner Voile, Concealed YKK Zippers, Canvas Padding, Silk Dori',
                        fabricPills: ['Cotton Voile Lining', 'Concealed YKK Zip', 'Princess Cut Pattern', 'Double Interlock Stitching'],
                        careInstructions: 'Provide fabric along with sample fitting garment during master technician visit.',
                        carePills: ['Keep sample fitting blouse ready', 'Free alteration pickup', '4-day fast delivery'],
                        weightGrams: 0,
                        hsnCode: '9988',
                        gstRate: '5',
                        tags: 'tailoring, blouse tailoring, kurti stitching, doorstep tailor, custom fit, alterations',
                        status: 'active',
                        sizeGuidePreset: 'none',
                        availableSizes: ['Simple Straight Cut', 'Princess Cut with Pads', 'Heavy Bridal Embroidery Blouse'],
                        selectedSizes: ['Princess Cut with Pads', 'Heavy Bridal Embroidery Blouse']
                    },
                    electrician_repair: {
                        name: "On-Demand Electrician & Circuit Fix",
                        shortName: 'Electrician Fix',
                        tab: 'repairs',
                        icon: '⚡',
                        badge: 'Doorstep • 45 Mins',
                        categorySlug: 'services-home-repairs-electrician',
                        categoryId: 10,
                        basePrice: '249',
                        compareAtPrice: '399',
                        totalStock: 50,
                        skuPrefix: 'ELC',
                        title: 'Emergency On-Demand Electrician for MCB Tripping, Short Circuit & Wiring Fix',
                        description: `Licensed electrician arriving with complete diagnostic toolkits. Diagnoses MCB trips, short circuits, switchboard replacements, ceiling fan installations, and inverter earthing connections safely.`,
                        material: 'Insulated Hand Tools, Digital Multi-Meter, Wire Strippers, Heavy-Duty Drill',
                        fabricPills: ['Licensed Electrician', 'ISI Marked Tools', 'Diagnostic Testing', 'Safety Protocols'],
                        careInstructions: 'Notify building security of technician arrival. Mains supply may be briefly disconnected.',
                        carePills: ['Main switch access required', 'Spares charged at actuals', '30-day labor warranty'],
                        weightGrams: 0,
                        hsnCode: '9987',
                        gstRate: '18',
                        tags: 'electrician, short circuit, mcb trip, wiring, switchboard, home repair',
                        status: 'active',
                        sizeGuidePreset: 'none',
                        availableSizes: ['30 Mins Inspection & Quick Fix', 'Up to 2 Hours Heavy Wiring Work'],
                        selectedSizes: ['30 Mins Inspection & Quick Fix']
                    },
                    salon_bridal: {
                        name: "HD Bridal Makeover & Hair Styling Package",
                        shortName: 'HD Bridal Makeup',
                        tab: 'tailoring',
                        icon: '💄',
                        badge: 'MAC / Huda Pro',
                        categorySlug: 'services-salon-grooming',
                        categoryId: 12,
                        basePrice: '4999',
                        compareAtPrice: '7500',
                        totalStock: 15,
                        skuPrefix: 'MKP',
                        title: 'Luxury HD Bridal Makeover with Airbrush Glow, False Lashes & Saree Draping',
                        description: `Full luxury bridal styling session using authenticated international cosmetics (MAC, Huda Beauty, NARS). Includes HD base contouring, eye makeup with premium mink lashes, floral hair styling, and ceremonial saree/lehenga draping.`,
                        material: 'MAC Pro, Huda Beauty, Charlotte Tilbury, NARS, Real Techniques Brushes',
                        fabricPills: ['100% Genuine Luxury Brands', 'Mink Lashes Included', 'Setting Spray 16h Lock', 'Disposable Applicators'],
                        careInstructions: 'Cleanse and moisturize skin prior to appointment. Client to keep jewelry and attire accessible.',
                        carePills: ['Well lit room required', 'Full face prep', 'Jewelry ready on site'],
                        weightGrams: 0,
                        hsnCode: '9997',
                        gstRate: '18',
                        tags: 'makeup, bridal makeup, salon at home, hd makeup, party makeup, hairstyle, draping',
                        status: 'active',
                        sizeGuidePreset: 'none',
                        availableSizes: ['Party Glow Makeover', 'Engagement / Sangeet HD', 'Full Bridal Grand Package'],
                        selectedSizes: ['Engagement / Sangeet HD', 'Full Bridal Grand Package']
                    },
                    @elseif($targetVertical === 'used')
                    iphone_mint: {
                        name: "Apple iPhone 15 Pro (256GB, Natural Titanium)",
                        shortName: 'iPhone 15 Pro',
                        tab: 'phones',
                        icon: '📱',
                        badge: '98% Battery • Mint',
                        categorySlug: 'used-mobiles-tablets',
                        categoryId: 29,
                        basePrice: '78000',
                        compareAtPrice: '134900',
                        totalStock: 3,
                        skuPrefix: 'IPH',
                        title: 'Pre-Owned Apple iPhone 15 Pro (256GB, Natural Titanium) - Pristine Like New with Box',
                        description: `Mint condition flagship iPhone in Natural Titanium finish. No scratches on screen or back glass. 98% battery health, completely unlocked for all Indian carriers. Clean iCloud, fully tested on 32 hardware inspection points. Comes with original braided USB-C cable and bill.`,
                        material: 'Natural Titanium Chassis, Ceramic Shield Glass, A17 Pro Bionic',
                        fabricPills: ['98% Battery Health', 'Zero Scratches on Display', 'Original Box & Braided Cable', 'Indian Carrier Unlocked'],
                        careInstructions: 'Handle with protective case. Charge with certified 20W+ USB-PD adapter.',
                        carePills: ['32-point inspection passed', '3-day testing warranty', 'Clean IMEI & iCloud reset'],
                        weightGrams: 450,
                        hsnCode: '8517',
                        gstRate: '18',
                        tags: 'used iphone, pre owned iphone, iphone 15 pro, apple, mobile, smartphone, used phone',
                        status: 'active',
                        sizeGuidePreset: 'none',
                        availableSizes: ['128 GB', '256 GB', '512 GB'],
                        selectedSizes: ['256 GB']
                    },
                    macbook_m2: {
                        name: "Apple MacBook Pro 14-inch (M2 Pro, 16GB / 512GB)",
                        shortName: 'MacBook Pro M2',
                        tab: 'laptops',
                        icon: '💻',
                        badge: 'Cycle: 38 • Like New',
                        categorySlug: 'used-laptops-electronics',
                        categoryId: 30,
                        basePrice: '115000',
                        compareAtPrice: '199900',
                        totalStock: 2,
                        skuPrefix: 'MBP',
                        title: 'Pre-Owned Apple MacBook Pro 14" (Apple M2 Pro Chip, 16GB Unified RAM, 512GB SSD) - Space Gray',
                        description: `Superb workstation powerhouse with only 38 battery charge cycles (100% capacity). Mini-LED Liquid Retina XDR display with zero dead pixels. Keyboard and trackpad are completely pristine. Includes original 67W MagSafe 3 power adapter and retail box.`,
                        material: 'Recycled Aluminum Unibody, Liquid Retina XDR Display, MagSafe 3',
                        fabricPills: ['Battery Cycle: 38 (100%)', 'Liquid Retina XDR Display', 'MagSafe 3 & Box Included', 'Zero Dead Pixels'],
                        careInstructions: 'Clean display only with dry microfiber cloth. Keep vents unobstructed.',
                        carePills: ['Original 67W charger', 'Diagnostics verified', '3-day return policy'],
                        weightGrams: 2100,
                        hsnCode: '8471',
                        gstRate: '18',
                        tags: 'macbook pro, used macbook, apple laptop, m2 pro, laptop, pre owned electronics',
                        status: 'active',
                        sizeGuidePreset: 'none',
                        availableSizes: ['16GB RAM / 512GB SSD', '32GB RAM / 1TB SSD'],
                        selectedSizes: ['16GB RAM / 512GB SSD']
                    },
                    vintage_diver: {
                        name: "Seiko Automatic Diver 200m Watch (SKX007)",
                        shortName: 'Seiko Diver Watch',
                        tab: 'luxury',
                        icon: '⌚',
                        badge: 'Automatic • 7S26',
                        categorySlug: 'used-vintage-collectibles',
                        categoryId: 33,
                        basePrice: '22000',
                        compareAtPrice: '38000',
                        totalStock: 1,
                        skuPrefix: 'WTC',
                        title: 'Vintage Seiko SKX007 Automatic Diver 200m Watch with Original Jubilee Bracelet',
                        description: `Iconic discontinued Seiko SKX007 dive watch powered by the legendary 7S26 21-jewel automatic movement. Crisp 120-click unidirectional bezel, bright Lumibrite hands, screw-down crown, and original solid-link Jubilee bracelet. Clean crystal with minimal age-appropriate desk marks on clasp.`,
                        material: '316L Stainless Steel, Hardlex Crystal, Lumibrite Dial, Jubilee Bracelet',
                        fabricPills: ['Legendary 7S26 Automatic', '120-Click Diver Bezel', 'Original Jubilee Bracelet', 'Collector Vintage Condition'],
                        careInstructions: 'Automatic mechanical watch powered by wrist movement. Do not wind backwards.',
                        carePills: ['Automatic mechanical', 'Serviced & timed on timegrapher', 'Box included'],
                        weightGrams: 350,
                        hsnCode: '9102',
                        gstRate: '18',
                        tags: 'vintage watch, seiko diver, skx007, automatic watch, pre owned watch, horology',
                        status: 'active',
                        sizeGuidePreset: 'none',
                        availableSizes: ['Original Steel Bracelet (Fits up to 7.8" wrist)'],
                        selectedSizes: ['Original Steel Bracelet (Fits up to 7.8" wrist)']
                    },
                    mirrorless_cam: {
                        name: "Sony Alpha A7 III 4K Full-Frame Camera Body",
                        shortName: 'Sony A7 III',
                        tab: 'laptops',
                        icon: '📷',
                        badge: 'Shutter: 5,400',
                        categorySlug: 'used-laptops-electronics',
                        categoryId: 30,
                        basePrice: '84000',
                        compareAtPrice: '145000',
                        totalStock: 2,
                        skuPrefix: 'CAM',
                        title: 'Pre-Owned Sony Alpha A7 III 24.2MP Full-Frame Mirrorless 4K Camera Body (Low Shutter)',
                        description: `Outstanding full-frame camera with only 5,400 mechanical shutter actuations (tested for 200,000 rating). Sensor is spotless with no dust or scratches. 5-axis image stabilization works flawlessly. Includes original Sony NP-FZ100 battery, charger, strap, and body cap.`,
                        material: 'Magnesium Alloy Body, 24.2MP Full-Frame Exmor R CMOS Sensor',
                        fabricPills: ['Shutter Actuations: 5,400', 'Clean Full-Frame Sensor', '5-Axis In-Body Stabilization', 'Sony NP-FZ100 Battery'],
                        careInstructions: 'Keep sensor covered with body cap when changing lenses. Use blower for dust.',
                        carePills: ['Low shutter count', 'Tested EVF & Sensor', 'Original strap & charger'],
                        weightGrams: 950,
                        hsnCode: '8525',
                        gstRate: '18',
                        tags: 'sony a7iii, mirrorless camera, used camera, full frame, 4k camera, photography gear',
                        status: 'active',
                        sizeGuidePreset: 'none',
                        availableSizes: ['Body Only (With Cap & Strap)', 'Body + 28-70mm OSS Kit Lens'],
                        selectedSizes: ['Body Only (With Cap & Strap)']
                    },
                    bike_commuter: {
                        name: "Royal Enfield Classic 350 (Single Owner)",
                        shortName: 'Classic 350',
                        tab: 'luxury',
                        icon: '🛵',
                        badge: '18,500 KM • Single Owner',
                        categorySlug: 'used-vintage-collectibles',
                        categoryId: 31,
                        basePrice: '145000',
                        compareAtPrice: '215000',
                        totalStock: 1,
                        skuPrefix: 'BIK',
                        title: '2021 Royal Enfield Classic 350 Halcyon Black (Single Owner, Valid Insurance)',
                        description: `Immaculately maintained single-owner Royal Enfield Classic 350 in Halcyon Black. Driven 18,500 KM with complete showroom service records at authorized dealership. Dual-channel ABS, pristine chrome, alloy wheels with tubeless tires, zero accident history. Clear RC card and active insurance.`,
                        material: 'Heavy Steel Frame, 349cc J-Series Engine, Dual-Channel ABS',
                        fabricPills: ['Single Owner RC', 'Dual-Channel ABS', 'Complete Service Records', 'Zero Accident History'],
                        careInstructions: 'Test rides available at seller premises with valid driving license.',
                        carePills: ['Original RC & Insurance', 'Showroom maintained', 'Clean engine sound'],
                        weightGrams: 50000,
                        hsnCode: '8711',
                        gstRate: '18',
                        tags: 'royal enfield, classic 350, used bike, two wheeler, bullet, pre owned vehicle',
                        status: 'active',
                        sizeGuidePreset: 'none',
                        availableSizes: ['Standard Stock Setup'],
                        selectedSizes: ['Standard Stock Setup']
                    },
                    @else
                    suit_stitched: {
                        name: "Women's 3-Piece Stitched Suit Sets",
                        shortName: '3-Pc Suit',
                        tab: 'women_ethnic',
                        icon: '🥻',
                        badge: '650g • S-3XL',
                        categorySlug: 'women-suit-sets',
                        categoryId: 29,
                        basePrice: '1899',
                        compareAtPrice: '3799',
                        totalStock: 25,
                        skuPrefix: 'SUIT',
                        title: 'Handcrafted Lucknowi Chikankari Embroidered Georgette 3-Piece Suit Set with Nazneen Dupatta',
                        description: `Exquisite 3-piece festive ethnic suit set tailored for celebrations, weddings, and festive gatherings. Features intricate hand embroidery with tonal thread work across the kurta yoke and sleeves, paired with a matching comfort-fit straight pant and a lightweight dupatta finished with border lace.

Key Highlights:
• Set Inclusions: 1 Stitched Kurta (Top) + 1 Matching Straight Trouser/Pant + 1 Full-Length Dupatta (2.25m)
• Embroidery Craft: Authentic Lucknowi Chikankari Hand Embroidery with Mukaish/Badla Work
• Neckline & Sleeves: Round Neck with Ornate Yoke • 3/4th Sleeves with Scalloped Lace
• Pant Waist: Semi-Elasticated Comfort Waistband with In-Seam Pocket
• Occasion: Festivals, Wedding Sangeet, Family Receptions & Pooja Ceremonies`,
                        material: 'Pure Viscose Georgette & Breathable Cotton Inner Lining',
                        fabricPills: ['Lucknowi Chikankari', 'Hand Zardozi Work', 'Gota Patti Embroidery', 'Pure Chanderi Silk', 'Pure Cotton Cambric', 'Georgette Silk', 'Modal Satin', 'Banarasi Brocade'],
                        careInstructions: 'Dry clean recommended for first wash. Subsequent gentle cold hand wash using mild liquid detergent. Dry flat in shade. Medium warm iron on reverse side.',
                        carePills: ['Dry clean recommended', 'Gentle cold hand wash', 'Iron on reverse side', 'Do not wring or bleach', 'Store wrapped in muslin cloth'],
                        gender: 'women',
                        brand: 'Noorani Ethnic Studio',
                        weightGrams: 650,
                        hsnCode: '6204',
                        gstRate: '5',
                        tags: 'suit, suit set, salwar suit, chikankari suit, handwork suit, 3 piece suit, dupatta set, anarkali suit, wedding ethnic',
                        returnWindowDays: 7,
                        countryOfOrigin: 'India',
                        status: 'active',
                        sizeGuidePreset: 'kurti',
                        availableSizes: ['S', 'M', 'L', 'XL', 'XXL', '3XL'],
                        selectedSizes: ['S', 'M', 'L', 'XL', 'XXL', '3XL']
                    },
                    suit_unstitched: {
                        name: 'Unstitched Suits & Dress Materials',
                        shortName: 'Dress Mat',
                        tab: 'women_ethnic',
                        icon: '🧵',
                        badge: '600g • Cut',
                        categorySlug: 'women-unstitched-suits',
                        categoryId: 30,
                        basePrice: '1299',
                        compareAtPrice: '2499',
                        totalStock: 30,
                        skuPrefix: 'DMAT',
                        title: 'Unstitched Pure Cotton Jacquard Embroidered Suit Dress Material with Malmal Dupatta',
                        description: `Premium 3-piece unstitched salwar kameez fabric set, giving you complete freedom to custom tailor the neckline, sleeve length, and bottom silhouette (Churidar, Salwar, Palazzo or Cigarette Pants) to your exact measurements.

Cut & Fabric Specifications:
• Top (Kurta Fabric): 2.50 Metres (Embroidered Front Neckline & Floral Boti Motifs)
• Bottom (Salwar/Pant Fabric): 2.50 Metres (Solid Heavy Dyed Cotton Fabric)
• Dupatta: 2.25 Metres (Full-Length Printed/Woven Border Dupatta)
• Craft: Fine Thread Embroidery with Zari Highlights
• Stitching Type: Completely Unstitched Fabric Cut
• Occasion: Daily Office Wear, Festive Casuals & Gifting`,
                        material: '100% Pure Premium Cotton Cambric (60x60)',
                        fabricPills: ['Pure Cotton Cambric', 'Lucknowi Chikankari Fabric', 'Chanderi Jacquard Silk', 'Banarasi Brocade Silk', 'Tussar Silk Blend', 'Pure Linen Fabric', 'Crepe Silk'],
                        careInstructions: 'First wash dry clean recommended. Subsequent cold gentle machine wash or hand wash. Dry in shade. Warm iron.',
                        carePills: ['Gentle hand wash cold', 'Dry in shade', 'Warm iron', 'Pre-wash before stitching'],
                        gender: 'women',
                        brand: 'Avani Handlooms & Fabrics',
                        weightGrams: 600,
                        hsnCode: '5208',
                        gstRate: '5',
                        tags: 'dress material, unstitched suit, salwar suit fabric, cotton dress material, chikankari unstitched, handwork fabric, suit cut',
                        returnWindowDays: 7,
                        countryOfOrigin: 'India',
                        status: 'active',
                        sizeGuidePreset: 'none',
                        availableSizes: ['Unstitched (2.5m Set)'],
                        selectedSizes: ['Unstitched (2.5m Set)']
                    },
                    kurti: {
                        name: 'Kurtas, Kurtis & Tunics',
                        shortName: 'Kurti',
                        tab: 'women_ethnic',
                        icon: '👗',
                        badge: '300g • XS-XXL',
                        categorySlug: 'women-kurtas-kurtis',
                        categoryId: 31,
                        basePrice: '799',
                        compareAtPrice: '1599',
                        totalStock: 35,
                        skuPrefix: 'KUR',
                        title: 'Handcrafted Embroidered A-Line Calf-Length Kurti with Bell Sleeves',
                        description: `Graceful and breezy single-piece kurti designed for effortless daily elegance. Cut from breathable natural fibers featuring subtle yoke embroidery, side slits, and a relaxed silhouette that pairs easily with leggings, denims, or palazzos.

Key Highlights:
• Length: 44 Inches (Calf Length) • Silhouette: Straight A-Line with Side Slits
• Sleeves: 3/4th Bell Sleeves with Delicate Hem Trim
• Occasion: Daily Wear, College, Office Casuals`,
                        material: '100% Breathable Rayon Slub & Pure Cotton',
                        fabricPills: ['Pure Cotton 60s', 'Rayon Slub', 'Chanderi Cotton', 'Mulmul Cotton', 'Georgette', 'Modal Silk', 'Linen Blend'],
                        careInstructions: 'Machine wash cold on gentle cycle. Wash dark colors separately. Medium iron on reverse.',
                        carePills: ['Machine wash cold gentle', 'Wash darks separately', 'Dry inside out in shade', 'Medium iron reverse'],
                        gender: 'women',
                        brand: 'Utsav Ethnic Couture',
                        weightGrams: 300,
                        hsnCode: '6204',
                        gstRate: '5',
                        tags: 'kurti, kurta, women kurti, daily kurti, cotton kurti, anarkali kurti, office kurti',
                        returnWindowDays: 7,
                        countryOfOrigin: 'India',
                        status: 'active',
                        sizeGuidePreset: 'kurti',
                        availableSizes: ['XS', 'S', 'M', 'L', 'XL', 'XXL'],
                        selectedSizes: ['XS', 'S', 'M', 'L', 'XL', 'XXL']
                    },
                    saree: {
                        name: 'Sarees & Ethnic Drapes',
                        shortName: 'Saree',
                        tab: 'women_ethnic',
                        icon: '🥻',
                        badge: '650g • Free',
                        categorySlug: 'women-sarees',
                        categoryId: 13,
                        basePrice: '1499',
                        compareAtPrice: '2999',
                        totalStock: 25,
                        skuPrefix: 'SAR',
                        title: 'Pure Banarasi Chanderi Silk Zari Woven Saree with Matching Blouse Piece',
                        description: `Crafted with heritage handloom mastery, this magnificent saree features a lustrous body accented by intricate floral zari motifs, a rich woven temple border, and an elaborate pallu design. Includes unstitched matching blouse fabric.

Key Highlights:
• Length: 5.50 Metres Saree Body + 0.80 Metre Unstitched Blouse Piece
• Weave: Authentic Banarasi Jacquard Zari Handloom
• Occasion: Weddings, Festive Celebrations, Pooja & Cultural Receptions`,
                        material: 'Pure Handloom Chanderi Silk & Zari',
                        fabricPills: ['Pure Silk', 'Chanderi Silk', 'Banarasi Zari', 'Georgette', 'Kanjivaram Silk', 'Organza', 'Mulmul Cotton', 'Tussar Silk'],
                        careInstructions: 'Dry clean recommended for initial washes to preserve metallic zari lustre. Store wrapped in a breathable muslin cloth.',
                        carePills: ['Dry clean recommended', 'Store wrapped in muslin cloth', 'Do not iron zari directly', 'Gentle hand wash cold'],
                        gender: 'women',
                        brand: 'Heritage Weaves & Silks',
                        weightGrams: 650,
                        hsnCode: '5208',
                        gstRate: '5',
                        tags: 'saree, silk saree, wedding wear, festive, banarasi, chanderi, zari border, ethnic drape',
                        returnWindowDays: 7,
                        countryOfOrigin: 'India',
                        status: 'active',
                        sizeGuidePreset: 'saree',
                        availableSizes: ['Free Size'],
                        selectedSizes: ['Free Size']
                    },
                    lehenga: {
                        name: 'Lehenga Choli & Bridal Sets',
                        shortName: 'Lehenga',
                        tab: 'women_ethnic',
                        icon: '💃',
                        badge: '1150g • Semi',
                        categorySlug: 'women-lehenga-choli',
                        categoryId: 32,
                        basePrice: '3499',
                        compareAtPrice: '6999',
                        totalStock: 15,
                        skuPrefix: 'LHG',
                        title: 'Festive Heavy Embroidered Art Silk Semi-Stitched Lehenga Choli with Can-Can & Dupatta',
                        description: `Opulent festive and wedding bridal lehenga set crafted with heavy dori, thread, and micro-sequins embroidery across all panels. Comes with double-layer can-can net inner lining for royal flared volume, an unstitched matching blouse piece, and an embroidered border dupatta.

Key Highlights:
• Lehenga Flare: 3.5 Metres Gher with Double Inner Can-Can Net
• Waist Fit: Semi-Stitched (Customizable up to 42 Inches Waist & 42 Inches Length)
• Blouse: 1.0 Metre Unstitched Heavy Embroidered Fabric
• Dupatta: 2.30 Metres Soft Net with Scalloped Embroidered Border
• Occasion: Weddings, Reception, Sangeet & Grand Festivals`,
                        material: 'Heavy Art Silk, Velvet & Net with Inner Micro Can-Can',
                        fabricPills: ['Heavy Art Silk', 'Velvet Zardozi', 'Georgette Sequins', 'Raw Silk', 'Net with Mukaish', 'Organza Silk', 'Brocade Jacquard'],
                        careInstructions: 'Strictly dry clean only. Store in garment protective zip bag away from direct sunlight.',
                        carePills: ['Strictly dry clean only', 'Store in garment bag', 'Do not iron embroidery directly', 'Keep away from moisture'],
                        gender: 'women',
                        brand: 'Rajwada Bridal Couture',
                        weightGrams: 1150,
                        hsnCode: '6204',
                        gstRate: '12',
                        tags: 'lehenga, lehenga choli, bridal lehenga, wedding wear, semi stitched lehenga, sangeet lehenga, partywear lehenga',
                        returnWindowDays: 7,
                        countryOfOrigin: 'India',
                        status: 'active',
                        sizeGuidePreset: 'kurti',
                        availableSizes: ['Semi-Stitched (Up to 42" Waist)'],
                        selectedSizes: ['Semi-Stitched (Up to 42" Waist)']
                    },
                    coord_set: {
                        name: 'Co-ord Sets & 2-Piece Ensembles',
                        shortName: 'Co-ord',
                        tab: 'women_ethnic',
                        icon: '🪔',
                        badge: '420g • S-XXL',
                        categorySlug: 'women-co-ord-sets',
                        categoryId: 33,
                        basePrice: '1399',
                        compareAtPrice: '2699',
                        totalStock: 20,
                        skuPrefix: 'CRD',
                        title: 'Modern Ethnic Block Printed Cotton 2-Piece Peplum Top & Tapered Trouser Co-ord Set',
                        description: `Contemporary matching 2-piece co-ord set merging traditional aesthetics with modern tailored silhouettes. Features a flattering peplum-style tunic top with coconut shell buttons paired with matching ankle-length cigarette pants with functional pockets.

Key Highlights:
• Set Inclusions: 1 Co-ord Tunic Top + 1 Matching Straight Trouser
• Pant Fit: High-Rise Elasticated Back with Side Zip & Pockets
• Occasion: Smart Casuals, Festive Brunches, Holiday Resort Wear & Office Wear`,
                        material: '100% Breathable Cotton & Linen Slub',
                        fabricPills: ['Pure Cotton Linen', 'Rayon Slub', 'Pleated Satin', 'Crepe Silk', 'Viscose Georgette', 'Ribbed Cotton'],
                        careInstructions: 'Machine wash cold with similar colors. Line dry in shade. Warm iron.',
                        carePills: ['Machine wash cold gentle', 'Line dry in shade', 'Warm iron', 'Do not bleach'],
                        gender: 'women',
                        brand: 'Urban Drape Studio',
                        weightGrams: 420,
                        hsnCode: '6204',
                        gstRate: '5',
                        tags: 'co ord set, 2 piece set, matching set, ethnic co ord, western co ord, pant set, vacation wear',
                        returnWindowDays: 7,
                        countryOfOrigin: 'India',
                        status: 'active',
                        sizeGuidePreset: 'kurti',
                        availableSizes: ['S', 'M', 'L', 'XL', 'XXL'],
                        selectedSizes: ['S', 'M', 'L', 'XL', 'XXL']
                    },
                    ethnic_bottom: {
                        name: 'Ethnic Bottoms, Palazzos & Pants',
                        shortName: 'Bottoms',
                        tab: 'women_ethnic',
                        icon: '👖',
                        badge: '240g • Free',
                        categorySlug: 'women-ethnic-bottoms',
                        categoryId: 34,
                        basePrice: '599',
                        compareAtPrice: '1199',
                        totalStock: 40,
                        skuPrefix: 'BTM',
                        title: 'Solid Flared Rayon Palazzo Pant with Lace Hem & Drawstring Waist',
                        description: `Ultra-comfortable wide-leg flared palazzo pant tailored from super-soft high-drape fabric. Pairs seamlessly with kurtis, tunics, and crop tops. Features an elasticated waistband with an adjustable drawstring and a deep side pocket.

Key Highlights:
• Silhouette: Wide-Leg Relaxed Flare with Scalloped Lace Hem
• Waistband: Comfort Elastic with Functional Drawstring
• Pocket: 1 Right In-Seam Deep Mobile Pocket
• Occasion: Daily Wear, Festive Pairings, Lounge & Travel`,
                        material: 'High-Drape Viscose Rayon (140 GSM)',
                        fabricPills: ['Heavy Viscose Rayon', 'Pure Cotton Cambric', 'Chanderi Silk Blend', 'Lycra Cotton Stretch', 'Georgette Plisse'],
                        careInstructions: 'Gentle machine wash cold. Wash dark colors separately. Medium iron.',
                        carePills: ['Gentle machine wash', 'Wash darks separately', 'Medium iron', 'Line dry'],
                        gender: 'women',
                        brand: 'Comfort Fit Ethnic',
                        weightGrams: 240,
                        hsnCode: '6204',
                        gstRate: '5',
                        tags: 'palazzo, ethnic pant, salwar, sharara, cigarette pant, kurti bottom, cotton pant',
                        returnWindowDays: 7,
                        countryOfOrigin: 'India',
                        status: 'active',
                        sizeGuidePreset: 'jeans',
                        availableSizes: ['Free Size (28-38)', 'Plus Size (38-46)'],
                        selectedSizes: ['Free Size (28-38)', 'Plus Size (38-46)']
                    },
                    dupatta: {
                        name: 'Dupattas, Stoles & Shawls',
                        shortName: 'Dupatta',
                        tab: 'women_ethnic',
                        icon: '🧣',
                        badge: '220g • Free',
                        categorySlug: 'women-dupattas-shawls',
                        categoryId: 35,
                        basePrice: '499',
                        compareAtPrice: '999',
                        totalStock: 35,
                        skuPrefix: 'DUP',
                        title: 'Banarasi Art Silk Zari Woven Border Festive Dupatta with Tassels',
                        description: `Lustrous statement dupatta featuring ornate woven gold zari floral borders and hand-knotted edge tassels. Elevate any plain suit, kurti, or lehenga instantly with this royal drape.

Key Highlights:
• Dimensions: Length 2.30 Metres • Width 36 Inches
• Edges: Finished with Hand-Knotted Silk Tassels
• Occasion: Festivals, Weddings, Pooja Ceremonies & Parties`,
                        material: 'Banarasi Art Silk & Metallic Zari',
                        fabricPills: ['Banarasi Art Silk', 'Pure Chiffon Bandhani', 'Phulkari Hand Embroidered', 'Organza Zari', 'Kashmiri Pashmina', 'Chanderi Silk'],
                        careInstructions: 'Dry clean recommended to preserve zari sheen. Gentle cold hand wash if needed.',
                        carePills: ['Dry clean recommended', 'Do not wring', 'Iron on reverse', 'Store wrapped'],
                        gender: 'women',
                        brand: 'Virasat Drapes',
                        weightGrams: 220,
                        hsnCode: '6214',
                        gstRate: '5',
                        tags: 'dupatta, banarasi dupatta, phulkari, chiffon dupatta, organza dupatta, ethnic drape, stole, shawl',
                        returnWindowDays: 7,
                        countryOfOrigin: 'India',
                        status: 'active',
                        sizeGuidePreset: 'none',
                        availableSizes: ['Free Size (2.3m)'],
                        selectedSizes: ['Free Size (2.3m)']
                    },
                    dress: {
                        name: "Women's Western Dresses & Tops",
                        shortName: 'Dress',
                        tab: 'western',
                        icon: '💃',
                        badge: '280g • XS-XL',
                        categorySlug: 'women-dresses',
                        categoryId: 9,
                        basePrice: '999',
                        compareAtPrice: '1999',
                        totalStock: 25,
                        skuPrefix: 'DRS',
                        title: 'Floral Print Tiered Midi Fit & Flare Dress with Puff Sleeves',
                        description: `Effortlessly stylish and breezy, this tiered midi dress features a vibrant botanical floral print, sweet square neckline, elasticated puff sleeves, and a defined waistline that falls into a playful tiered skirt. Designed with high-drape fabric for lightweight all-day comfort.

Key Highlights:
• Length: Midi Calf Length with Tiered Ruffles
• Silhouette: Fit & Flare with Gentle Waist Shaping
• Sleeves: Elasticated Comfort Puff Sleeves
• Lining: Fully Lined Inner Cotton Voile for zero see-through
• Occasion: Brunch, Summer Holidays, Weekend Getaways & Casual Dinners`,
                        material: 'Lightweight Viscose Rayon Georgette',
                        fabricPills: ['Viscose Rayon', 'Pure Cotton Mulmul', 'Poly Georgette', 'Linen Blend', 'Crepe', 'Satin Silk', 'Cotton Cambric'],
                        careInstructions: 'Machine wash cold on gentle cycle with like colors. Line dry in shade. Do not tumble dry. Use low to medium heat iron on reverse side.',
                        carePills: ['Machine wash cold gentle', 'Line dry in shade', 'Do not tumble dry', 'Low heat iron on reverse', 'Do not bleach'],
                        gender: 'women',
                        brand: 'Bloom & Meadow Studio',
                        weightGrams: 280,
                        hsnCode: '6204',
                        gstRate: '5',
                        tags: 'dress, midi dress, floral dress, western wear, casual dress, summer dress, puff sleeve, vacation wear',
                        returnWindowDays: 7,
                        countryOfOrigin: 'India',
                        status: 'active',
                        sizeGuidePreset: 'dress',
                        availableSizes: ['XS', 'S', 'M', 'L', 'XL'],
                        selectedSizes: ['XS', 'S', 'M', 'L', 'XL']
                    },
                    top_women: {
                        name: 'Women Tops, Shirts & Tunics',
                        shortName: 'Tops',
                        tab: 'western',
                        icon: '👚',
                        badge: '180g • XS-XL',
                        categorySlug: 'women-tops',
                        categoryId: 10,
                        basePrice: '599',
                        compareAtPrice: '1199',
                        totalStock: 40,
                        skuPrefix: 'TOP',
                        title: 'Floral Print Ruffle Neck Puff Sleeve Peplum Top for Women',
                        description: `Chic and versatile western casual top featuring a delicate floral motif, flattering smocked bodice, and elasticated puff sleeves. Pairs effortlessly with jeans, skirts, or trousers.

Key Highlights:
• Silhouette: Relaxed Fit Peplum with Smocked Waist
• Neckline: Ruffled High Neck with Tie-Up Tassels
• Occasion: College, Work Casuals, Coffee Dates & Weekend Outings`,
                        material: 'Lightweight Poly-Georgette with Soft Cotton Lining',
                        fabricPills: ['Cotton Poplin', 'Viscose Rayon', 'Poly Georgette', 'Chiffon', 'Linen Cotton', 'Satin'],
                        careInstructions: 'Machine wash cold gentle. Line dry in shade. Low iron.',
                        carePills: ['Machine wash cold', 'Line dry', 'Low heat iron'],
                        gender: 'women',
                        brand: 'Aura Style Co.',
                        weightGrams: 180,
                        hsnCode: '6206',
                        gstRate: '5',
                        tags: 'top, women top, crop top, blouse, floral top, casual top, office shirt',
                        returnWindowDays: 7,
                        countryOfOrigin: 'India',
                        status: 'active',
                        sizeGuidePreset: 'shirt',
                        availableSizes: ['XS', 'S', 'M', 'L', 'XL'],
                        selectedSizes: ['XS', 'S', 'M', 'L', 'XL']
                    },
                    jeans_women: {
                        name: "Women's High-Rise Denim Jeans",
                        shortName: 'W-Jeans',
                        tab: 'western',
                        icon: '👖',
                        badge: '480g • 26-36',
                        categorySlug: 'women-jeans',
                        categoryId: 11,
                        basePrice: '1199',
                        compareAtPrice: '2399',
                        totalStock: 30,
                        skuPrefix: 'WJN',
                        title: 'High-Rise Ankle Length Wide Leg Stretch Denim Jeans for Women',
                        description: `Ultra-flattering high-rise denim crafted from breathable stretch denim fabric that sculpts your silhouette while offering all-day comfort. Features classic 5-pocket styling and clean hems.`,
                        material: '98% Cotton Denim, 2% Spandex Elastane',
                        fabricPills: ['Stretch Denim (11oz)', 'Pure Cotton Rigid Denim', 'Soft Twill Denim', 'Tencel Denim Blend'],
                        careInstructions: 'Machine wash cold inside out. Wash darks separately.',
                        carePills: ['Wash inside out', 'Machine wash cold', 'Line dry in shade', 'Do not bleach'],
                        gender: 'women',
                        brand: 'Indigo Atelier',
                        weightGrams: 480,
                        hsnCode: '6204',
                        gstRate: '5',
                        tags: 'women jeans, high rise jeans, stretch denim, wide leg jeans, skinny jeans, mom jeans',
                        returnWindowDays: 7,
                        countryOfOrigin: 'India',
                        status: 'active',
                        sizeGuidePreset: 'jeans',
                        availableSizes: ['26', '28', '30', '32', '34', '36'],
                        selectedSizes: ['26', '28', '30', '32', '34', '36']
                    },
                    nightwear: {
                        name: 'Sleepwear, Nighties & Loungewear',
                        shortName: 'Nightwear',
                        tab: 'western',
                        icon: '🌙',
                        badge: '280g • Free',
                        categorySlug: 'women-nightwear',
                        categoryId: 37,
                        basePrice: '699',
                        compareAtPrice: '1399',
                        totalStock: 30,
                        skuPrefix: 'NGT',
                        title: '100% Pure Alpine Cotton Breathable Printed Front-Open Nighty with Pocket',
                        description: `Ultra-soft and breathable cotton nighty designed for tranquil sleep and relaxed all-day lounging. Features front buttons for easy wear and a functional side pocket.`,
                        material: '100% Super-Combed Alpine Cotton',
                        fabricPills: ['100% Alpine Cotton', 'Rayon Hosiery', 'Satin Silk', 'Modal Cotton', 'Flannel Cotton'],
                        careInstructions: 'Machine wash cold. Wash darks separately.',
                        carePills: ['Machine wash cold', 'Wash darks separately', 'Dry in shade'],
                        gender: 'women',
                        brand: 'Serene Dreams Loungewear',
                        weightGrams: 280,
                        hsnCode: '6208',
                        gstRate: '5',
                        tags: 'nighty, night suit, nightwear, loungewear, cotton nighty, pajama set, sleepwear',
                        returnWindowDays: 7,
                        countryOfOrigin: 'India',
                        status: 'active',
                        sizeGuidePreset: 'none',
                        availableSizes: ['Free Size (Upto 44")', 'Plus Size (46-50")'],
                        selectedSizes: ['Free Size (Upto 44")', 'Plus Size (46-50")']
                    },
                    men_kurta: {
                        name: "Men's Ethnic Kurtas & Pyjama Sets",
                        shortName: 'M-Kurta',
                        tab: 'men',
                        icon: '🪔',
                        badge: '450g • 38-46',
                        categorySlug: 'men-ethnic-wear',
                        categoryId: 6,
                        basePrice: '1299',
                        compareAtPrice: '2499',
                        totalStock: 25,
                        skuPrefix: 'ETH',
                        title: 'Festive Jacquard Art Silk Men Long Kurta & Churidar Pajama Set',
                        description: `Celebrate festive moments and weddings with royal distinction. This long kurta features a tailored mandarin collar, side slits, functional in-seam pockets, and delicate tonal jacquard weaving. Comes paired with a comfortable matching cotton-silk drawstring churidar pajama.

Key Highlights:
• Silhouette: Straight Cut Long Kurta with Mandarin Bandhgala Collar
• Buttons: Designer Metal & Enamel Buttons
• Pockets: 2 Concealed Deep Side Pockets
• Pajama: Free Size Churidar with Cotton Drawstring Waist
• Occasion: Diwali, Eid, Haldi Ceremonies, Wedding Receptions, Sangeet`,
                        material: 'Jacquard Art Silk & Pure Cotton Lining',
                        fabricPills: ['Jacquard Art Silk', 'Pure Cotton Kurta Fabric', 'Dupion Silk Blend', 'Linen Cotton', 'Khadi Silk', 'Chanderi Silk'],
                        careInstructions: 'Dry clean recommended for first wash. Subsequent gentle hand wash in cold water using mild shampoo or silk detergent. Warm iron on reverse.',
                        carePills: ['Dry clean recommended', 'Gentle hand wash cold', 'Iron on reverse side', 'Do not wring', 'Store in dry cotton bag'],
                        gender: 'men',
                        brand: 'Rajwada Royal Ethnic',
                        weightGrams: 450,
                        hsnCode: '6203',
                        gstRate: '5',
                        tags: 'men kurta, ethnic wear, kurta pajama, wedding wear, festive, haldi, diwali, sherwani, nehru jacket',
                        returnWindowDays: 7,
                        countryOfOrigin: 'India',
                        status: 'active',
                        sizeGuidePreset: 'shirt',
                        availableSizes: ['38 (S)', '40 (M)', '42 (L)', '44 (XL)', '46 (XXL)'],
                        selectedSizes: ['38 (S)', '40 (M)', '42 (L)', '44 (XL)', '46 (XXL)']
                    },
                    men_nehru: {
                        name: "Men's Nehru Jackets & Waistcoats",
                        shortName: 'Vest',
                        tab: 'men',
                        icon: '🦺',
                        badge: '320g • 38-46',
                        categorySlug: 'men-nehru-jackets',
                        categoryId: 39,
                        basePrice: '1199',
                        compareAtPrice: '2499',
                        totalStock: 25,
                        skuPrefix: 'NHR',
                        title: 'Tailored Jute Silk Bandhgala Sleeveless Nehru Jacket / Modi Waistcoat',
                        description: `Classic festive waistcoat tailored with a structured mandarin collar, welt chest pocket, and ornamental metal buttons. Adds instant royal refinement over any kurta set or formal shirt.`,
                        material: 'Textured Jute Silk Blend & Satin Inner Lining',
                        fabricPills: ['Jute Silk', 'Jacquard Brocade', 'Raw Silk', 'Wool Blend', 'Linen Cotton', 'Velvet'],
                        careInstructions: 'Dry clean only. Store on structured hanger.',
                        carePills: ['Dry clean only', 'Store on structured hanger', 'Steam iron only'],
                        gender: 'men',
                        brand: 'Royal Heritage Vests',
                        weightGrams: 320,
                        hsnCode: '6203',
                        gstRate: '5',
                        tags: 'nehru jacket, modi vest, waistcoat, men ethnic jacket, bandhgala, wedding jacket',
                        returnWindowDays: 7,
                        countryOfOrigin: 'India',
                        status: 'active',
                        sizeGuidePreset: 'shirt',
                        availableSizes: ['38 (S)', '40 (M)', '42 (L)', '44 (XL)', '46 (XXL)'],
                        selectedSizes: ['38 (S)', '40 (M)', '42 (L)', '44 (XL)', '46 (XXL)']
                    },
                    shirt: {
                        name: "Men's Casual & Formal Shirts",
                        shortName: 'Shirt',
                        tab: 'men',
                        icon: '👔',
                        badge: '240g • S-XXL',
                        categorySlug: 'men-shirts',
                        categoryId: 3,
                        basePrice: '899',
                        compareAtPrice: '1699',
                        totalStock: 30,
                        skuPrefix: 'SHT',
                        title: 'Pure Linen Cotton Breathable Solid Spread Collar Casual Shirt',
                        description: `Meticulously tailored from premium long-staple cotton and airy linen, this shirt delivers the perfect balance of crisp structure and breathable casual ease. Features a curved hem designed to stay tucked or look great untucked, tonal pearlized buttons, and a clean spread collar.

Key Highlights:
• Fit: Modern Slim Fit with Box Pleat for Shoulder Mobility
• Collar: Semi-Spread Collar with Stiffened Interlining
• Hem: Curved Shirttail Hem
• Pre-Washed: Enzyme Washed for Zero Shrinkage and Supreme Softness
• Occasion: Smart Casual Fridays, Beach Holidays, Dinner Dates & Summer Outings`,
                        material: '60% Pure European Linen, 40% Long-Staple Combed Cotton',
                        fabricPills: ['Pure Linen', '100% Giza Cotton', 'Oxford Cotton Weave', 'Poplin Cotton', 'Chambray', 'Cotton Twill', 'Bamboo Cotton'],
                        careInstructions: 'Machine wash cold with like colors on gentle cycle. Line dry in shade. Warm steam iron while fabric is slightly damp for best crisp finish.',
                        carePills: ['Machine wash cold', 'Line dry in shade', 'Steam iron medium', 'Do not bleach', 'Wash darks separately'],
                        gender: 'men',
                        brand: 'Northgate Tailors',
                        weightGrams: 240,
                        hsnCode: '6205',
                        gstRate: '5',
                        tags: 'shirt, men shirt, linen shirt, formal shirt, casual shirt, cotton shirt, spread collar, summer shirt',
                        returnWindowDays: 7,
                        countryOfOrigin: 'India',
                        status: 'active',
                        sizeGuidePreset: 'shirt',
                        availableSizes: ['S (38)', 'M (40)', 'L (42)', 'XL (44)', 'XXL (46)'],
                        selectedSizes: ['S (38)', 'M (40)', 'L (42)', 'XL (44)', 'XXL (46)']
                    },
                    tshirt: {
                        name: "Men's Casual T-Shirts & Polos",
                        shortName: 'T-Shirt',
                        tab: 'men',
                        icon: '👕',
                        badge: '200g • S-XXL',
                        categorySlug: 'men-t-shirts',
                        categoryId: 2,
                        basePrice: '499',
                        compareAtPrice: '999',
                        totalStock: 40,
                        skuPrefix: 'TEE',
                        title: 'Heavyweight 240 GSM Pure Combed Cotton Oversized Fit Drop Shoulder T-Shirt',
                        description: `Engineered for everyday durability and modern street aesthetic, this heavyweight tee is crafted from 240 GSM super-combed bio-washed cotton. Features a relaxed drop-shoulder cut, reinforced twin-needle stitching, and a ribbed crew neck collar with lycra to maintain its shape wash after wash.

Key Highlights:
• Fabric Weight: 240 GSM Heavyweight Dense Knit
• Silhouette: Relaxed Oversized Drop Shoulder Fit
• Collar: 1-Inch Ribbed Crew Neck with Shape Retention
• Finish: Bio-Washed & Silicon Softened for Lint-Free Handfeel
• Occasion: Daily Streetwear, Lounging, Gym Warmup & College`,
                        material: '100% Super-Combed Bio-Washed Cotton (240 GSM)',
                        fabricPills: ['240 GSM Heavy Cotton', '180 GSM Bio-Washed Cotton', 'Cotton Pique (Polo)', 'French Terry', 'Cotton Spandex', 'Slub Jersey'],
                        careInstructions: 'Machine wash cold on gentle cycle. Wash dark colors separately. Do not iron directly on graphics or chest embroidery. Tumble dry low.',
                        carePills: ['Machine wash cold', 'Wash darks separately', 'Do not iron on print', 'Tumble dry low', 'Do not dry clean'],
                        gender: 'men',
                        brand: 'Aero Club Streetwear',
                        weightGrams: 200,
                        hsnCode: '6109',
                        gstRate: '5',
                        tags: 'tshirt, tee, oversized tshirt, graphic tee, plain tshirt, round neck, drop shoulder, streetwear',
                        returnWindowDays: 7,
                        countryOfOrigin: 'India',
                        status: 'active',
                        sizeGuidePreset: 'shirt',
                        availableSizes: ['S', 'M', 'L', 'XL', 'XXL'],
                        selectedSizes: ['S', 'M', 'L', 'XL', 'XXL']
                    },
                    jeans_men: {
                        name: "Men's Denim Jeans & Chinos",
                        shortName: 'M-Jeans',
                        tab: 'men',
                        icon: '👖',
                        badge: '480g • 28-38',
                        categorySlug: 'men-jeans',
                        categoryId: 4,
                        basePrice: '1199',
                        compareAtPrice: '2299',
                        totalStock: 30,
                        skuPrefix: 'JNS',
                        title: 'Mid-Rise Slim Tapered Stretch Denim Jeans in Vintage Indigo Wash',
                        description: `Engineered with high-recovery stretch denim offering comfort in every movement without bagging at the knees. Features authentic hand-whiskering, antique brass rivets, durable chain stitching, and five-pocket utility.

Key Highlights:
• Fit: Slim Tapered (Roomy around thighs, tailored from knee to ankle)
• Rise: Comfortable Mid-Rise Waistline
• Fabric: 12 Oz Medium-Weight Cotton Rich Denim with 2% Elastane Stretch
• Hardware: Heavy Duty YKK Brass Zipper & Reinforced Belt Loops
• Occasion: Daily Casuals, Weekend Hangouts, Travel & Bike Rides`,
                        material: '98% Premium Ring-Spun Cotton Denim, 2% Elastane',
                        fabricPills: ['12oz Stretch Denim', '100% Rigid Selvedge', 'Cotton Twill Chino', 'Knitted Denim', 'Power Stretch Lycra'],
                        careInstructions: 'Machine wash cold inside out with dark colors. Line dry in shade. Avoid tumble drying to maximize stretch lifespan.',
                        carePills: ['Wash inside out', 'Machine wash cold', 'Line dry in shade', 'Do not tumble dry', 'Warm iron if needed'],
                        gender: 'men',
                        brand: 'Rugged Iron Denim',
                        weightGrams: 480,
                        hsnCode: '6203',
                        gstRate: '5',
                        tags: 'jeans, denim, stretch jeans, slim fit, trousers, casual pants, rugged, vintage wash',
                        returnWindowDays: 7,
                        countryOfOrigin: 'India',
                        status: 'active',
                        sizeGuidePreset: 'jeans',
                        availableSizes: ['28', '30', '32', '34', '36', '38'],
                        selectedSizes: ['28', '30', '32', '34', '36', '38']
                    },
                    kids: {
                        name: 'Kids Apparel & Play Sets',
                        shortName: 'Kids',
                        tab: 'kids_acc',
                        icon: '👶',
                        badge: '180g • 2-11Y',
                        categorySlug: 'kids-wear',
                        categoryId: 42,
                        basePrice: '599',
                        compareAtPrice: '1199',
                        totalStock: 30,
                        skuPrefix: 'KID',
                        title: 'Organic Cotton Unisex Kids Graphic T-Shirt & French Terry Shorts Play Set',
                        description: `Engineered specifically for active toddlers and growing kids, this 2-piece outfit is crafted from 100% GOTS certified hypoallergenic organic cotton. Features an ultra-soft ribbed neckband, flatlock interior seams to prevent skin irritation, and an elasticated waistband with a non-pinch internal drawcord.

Key Highlights:
• Material: Hypoallergenic 100% Pure Organic Cotton
• Waist: Non-Pinch Soft Elastic with Cotton Drawstring
• Snaps: Nickle-Free Baby-Safe Poppers (for infant sizes)
• Occasion: Everyday Playtime, Kindergarten, Daycare & Birthday Gifting`,
                        material: '100% Certified Organic Cotton & Baby-Safe French Terry',
                        fabricPills: ['Organic Cotton', 'French Terry', 'Cotton Hosiery', 'Muslin Mulmul', 'Bamboo Viscose', 'Cotton Fleece'],
                        careInstructions: 'Gentle machine wash with baby-safe mild detergent. Wash inside out. Tumble dry low or line dry in shade. Warm iron if necessary.',
                        carePills: ['Machine wash gentle', 'Baby safe detergent', 'Tumble dry low', 'Warm iron', 'Do not bleach'],
                        gender: 'kids',
                        brand: 'Little Sprout Organics',
                        weightGrams: 180,
                        hsnCode: '6209',
                        gstRate: '5',
                        tags: 'kids wear, boys wear, girls clothing, baby clothes, cotton set, shorts set, organic kids, playwear',
                        returnWindowDays: 7,
                        countryOfOrigin: 'India',
                        status: 'active',
                        sizeGuidePreset: 'kids',
                        availableSizes: ['2-3Y', '4-5Y', '6-7Y', '8-9Y', '10-11Y'],
                        selectedSizes: ['2-3Y', '4-5Y', '6-7Y', '8-9Y', '10-11Y']
                    },
                    shoes_bags: {
                        name: 'Handbags, Footwear & Accessories',
                        shortName: 'Bags/Juttis',
                        tab: 'kids_acc',
                        icon: '👜',
                        badge: '420g • Free',
                        categorySlug: 'accessories-bags',
                        categoryId: 20,
                        basePrice: '1199',
                        compareAtPrice: '2299',
                        totalStock: 20,
                        skuPrefix: 'ACC',
                        title: 'Textured Vegan Leather Structured Shoulder Bag & Ethnic Mojari Collection',
                        description: `Exquisitely crafted from premium scratch-resistant pebbled vegan leather with lustrous gold-tone hardware, secure top zipper, and adjustable detachable shoulder strap.

Key Highlights:
• Compartments: 2 Spacious Main Sections + 1 Center Zip Pocket + 2 Slip Card Slots
• Hardware: Premium Anti-Tarnish Gold-Tone Metal Zippers & Clasps
• Handles: Dual Structured Grab Handles + Detachable Shoulder Sling
• Occasion: Daily Office, College, Weekend Shopping, Travel & Dinners`,
                        material: 'Premium Textured Pebbled Vegan Leather & Polyester Lining',
                        fabricPills: ['Pebbled Vegan Leather', 'Full Grain Leather', 'Heavy Canvas', 'Embroidered Jutti Velvet', 'Jacquard Fabric'],
                        careInstructions: 'Wipe gently with a clean, damp cloth. Do not use chemical solvents.',
                        carePills: ['Wipe clean with soft damp cloth', 'Store in protective dust bag', 'Keep away from moisture'],
                        gender: 'women',
                        brand: 'Atelier Luxe Accessories',
                        weightGrams: 420,
                        hsnCode: '4202',
                        gstRate: '12',
                        tags: 'handbag, tote bag, shoulder bag, vegan leather, ethnic jutti, accessories',
                        returnWindowDays: 7,
                        countryOfOrigin: 'India',
                        status: 'active',
                        sizeGuidePreset: 'none',
                        availableSizes: ['Standard (One Size)'],
                        selectedSizes: ['Standard (One Size)']
                    },
                    fw_sports: {
                        name: "Sports Running Shoes & Athletic Sneakers",
                        shortName: 'Sports Shoes',
                        tab: 'footwear',
                        icon: '👟',
                        badge: '750g • UK 6-11',
                        categorySlug: 'footwear-sports-running',
                        categoryId: 45,
                        basePrice: '1499',
                        compareAtPrice: '2999',
                        totalStock: 40,
                        skuPrefix: 'SHOE',
                        title: 'Ultra-Lightweight Breathable Knit Mesh Sports Running & Daily Gym Sneakers',
                        description: `Engineered for high-intensity training, morning runs, and active casual wear. Features a flexible breathable engineered mesh upper that allows air circulation, a high-rebound shock-absorbing EVA midsole for all-day cushioning, and an anti-skid rubber traction outsole with multi-directional tread.

Key Highlights:
• Upper: High-Density Breathable Engineered Knit Fabric (Sweat-Wicking)
• Midsole & Sole: Shock-Absorbing EVA Midsole + Anti-Skid Rubber Grip Pods
• Insole: Removable Memory Foam Arch Cushioning
• Closure: Traditional Lace-Up with Reinforced Eyelets
• Occasion: Running, Gym Workouts, Walking, Sports & Daily Athleisure`,
                        material: 'Breathable Knit Mesh Upper, Shock-Absorbing EVA Midsole & Rubber Outsole',
                        fabricPills: ['Breathable Mesh Fabric', 'Lightweight EVA Sole', 'Memory Foam Insole', 'Anti-Slip Rubber Grip', 'Padded Ankle Collar', 'TPU Heel Support'],
                        careInstructions: 'Wipe surface dirt with a clean damp cloth or soft brush. Do not machine wash or soak. Air dry naturally away from direct hot sunlight.',
                        carePills: ['Wipe clean with damp cloth', 'Air dry in shade', 'Do not machine wash', 'Use soft bristle brush for sole', 'Deodorize with silica pouch'],
                        gender: 'men',
                        brand: 'AcroSport Athletics',
                        weightGrams: 750,
                        hsnCode: '6404',
                        gstRate: '12',
                        tags: 'shoes, sports shoes, sneakers, running shoes, gym shoes, footwear, athletic trainers',
                        returnWindowDays: 7,
                        countryOfOrigin: 'India',
                        status: 'active',
                        sizeGuidePreset: 'footwear',
                        availableSizes: ['UK 6', 'UK 7', 'UK 8', 'UK 9', 'UK 10', 'UK 11'],
                        selectedSizes: ['UK 6', 'UK 7', 'UK 8', 'UK 9', 'UK 10', 'UK 11']
                    },
                    fw_mens_formal: {
                        name: "Men's Formal Dress Shoes, Oxfords & Derby",
                        shortName: 'Formal Shoes',
                        tab: 'footwear',
                        icon: '👞',
                        badge: '850g • UK 6-11',
                        categorySlug: 'footwear-formal-shoes',
                        categoryId: 26,
                        basePrice: '1899',
                        compareAtPrice: '3699',
                        totalStock: 30,
                        skuPrefix: 'FORM',
                        title: 'Handcrafted Classic Italian-Style Leather Formal Lace-Up Oxford Shoes',
                        description: `Timeless formal lace-up Oxford / Derby shoes crafted for boardroom meetings, formal gatherings, weddings, and black-tie celebrations. Features a hand-burnished polished faux/genuine leather finish, reinforced toe cap, cushioned memory-foam arch support insole, and durable non-slip TPR dress sole.

Key Highlights:
• Silhouette: Classic Oxford Lace-Up with Clean Toe Cap Detailing
• Insole: Dual-Density Memory Foam Cushion with Anatomical Arch Support
• Outsole: High-Grade Anti-Skid Thermo Plastic Rubber (TPR) Sole
• Lining: Breathable Sweat-Resistant Perforated Inner Lining
• Occasion: Corporate Office, Business Meetings, Weddings & Formal Evenings`,
                        material: 'Premium Burnished Synthetic/Genuine Leather, Cushioned PU Insole & TPR Sole',
                        fabricPills: ['Burnished Finish Leather', 'Premium Synthetic PU Leather', 'Memory Foam Insole', 'High-Traction TPR Sole', 'Padded Arch Support'],
                        careInstructions: 'Clean with a soft horsehair brush. Apply matching shoe polish or neutral wax cream to restore luster. Store in shoe dust bags with shoe trees.',
                        carePills: ['Polish regularly with wax cream', 'Wipe with microfiber cloth', 'Keep away from rain/water', 'Store with shoe tree or paper stuffing'],
                        gender: 'men',
                        brand: 'Kingsley Heritage Footwear',
                        weightGrams: 850,
                        hsnCode: '6403',
                        gstRate: '12',
                        tags: 'formal shoes, oxford shoes, derby shoes, office shoes, leather shoes, wedding shoes, men footwear',
                        returnWindowDays: 7,
                        countryOfOrigin: 'India',
                        status: 'active',
                        sizeGuidePreset: 'footwear',
                        availableSizes: ['UK 6', 'UK 7', 'UK 8', 'UK 9', 'UK 10', 'UK 11'],
                        selectedSizes: ['UK 6', 'UK 7', 'UK 8', 'UK 9', 'UK 10', 'UK 11']
                    },
                    fw_loafers: {
                        name: "Men's Casual Loafers, Moccasins & Driving Shoes",
                        shortName: 'Loafers',
                        tab: 'footwear',
                        icon: '🥾',
                        badge: '650g • UK 6-10',
                        categorySlug: 'footwear-loafers',
                        categoryId: 46,
                        basePrice: '1299',
                        compareAtPrice: '2499',
                        totalStock: 35,
                        skuPrefix: 'LOAF',
                        title: 'Penny Loafer Slip-On Casual Driving Shoes with Cushioned Memory Foam',
                        description: `Effortless slip-on comfort meets contemporary casual elegance. Designed with flexible moccasin stitch construction, penny strap detail, ultra-soft breathable lining, and segmented rubber driving sole that flexes naturally with every step.

Key Highlights:
• Closure: Slip-On Easy Wear with Elasticated Fit Webbing
• Construction: Hand-Stitched Moccasin Apron Toe
• Outsole: Segmented Anti-Slip Rubber Driving Sole for Natural Flexibility
• Insole: Cushioned Footbed with Antimicrobial Odor-Free Lining
• Occasion: Smart Casuals, Weekend Brunches, Travel & Friday Workwear`,
                        material: 'Textured Suede / PU Leather Finish, Soft Foam Insole & Flexible Segmented Rubber Sole',
                        fabricPills: ['Soft Suede Finish', 'Breathable PU Leather', 'Flexible Rubber Sole', 'Slip-on Moccasin Stitch', 'Memory Cushion Footbed'],
                        careInstructions: 'Use a suede eraser or dry soft cloth. Avoid soaking in water. Apply waterproofing spray before outdoor use.',
                        carePills: ['Clean with suede brush', 'Spot clean only', 'Do not soak in water', 'Store in dry ventilated space'],
                        gender: 'men',
                        brand: 'Veloce Italian Loafers',
                        weightGrams: 650,
                        hsnCode: '6403',
                        gstRate: '12',
                        tags: 'loafers, casual shoes, moccasins, driving shoes, slip on shoes, penny loafers',
                        returnWindowDays: 7,
                        countryOfOrigin: 'India',
                        status: 'active',
                        sizeGuidePreset: 'footwear',
                        availableSizes: ['UK 6', 'UK 7', 'UK 8', 'UK 9', 'UK 10'],
                        selectedSizes: ['UK 6', 'UK 7', 'UK 8', 'UK 9', 'UK 10']
                    },
                    fw_slippers_slides: {
                        name: "Slippers, Flip-Flops, Chappals & Slides",
                        shortName: 'Slippers/Slides',
                        tab: 'footwear',
                        icon: '🩴',
                        badge: '320g • UK 5-10',
                        categorySlug: 'footwear-slippers-flip-flops',
                        categoryId: 48,
                        basePrice: '499',
                        compareAtPrice: '999',
                        totalStock: 60,
                        skuPrefix: 'SLIP',
                        title: 'Waterproof Ultra-Cushioned Daily Comfort EVA Slides & Casual Flip-Flop Chappals',
                        description: `Ultra-lightweight, waterproof, and slip-resistant daily wear slides / flip-flops. Molded from high-density compression EVA foam with deep heel cup, orthopedic footbed contours, and anti-skid wave tread pattern for shower, home, beach, and casual daily errands.

Key Highlights:
• Material: 100% One-Piece Compression Molded EVA (Zero Joint Splitting)
• Footbed: Ergonomic Arch Support with Textured Grip Surface
• Outsole: Anti-Skid Wave Ripple Tread for Wet Floor Stability
• Features: Quick-Drying, Odor-Resistant, Washable & Featherlight
• Occasion: Daily Home Wear, Bathroom Shower, Poolside, Beach & Casual Outings`,
                        material: '100% High-Density Compression Molded EVA Foam & Soft Padded Strap',
                        fabricPills: ['Compression Molded EVA', 'Waterproof Rubber', 'Anti-Slip Textured Tread', 'Orthopedic Arch Contours', 'Quick-Drying Material'],
                        careInstructions: 'Wash directly with cold water and mild soap. Wipe dry or air dry in shade. Avoid prolonged exposure to boiling heat or direct hot sun.',
                        carePills: ['Washable with water & soap', 'Air dry in shade', 'Do not leave in hot car/sun', '100% Waterproof'],
                        gender: 'unisex',
                        brand: 'CloudWalk Everyday',
                        weightGrams: 320,
                        hsnCode: '6402',
                        gstRate: '12',
                        tags: 'slippers, flip flops, chappal, slides, bathroom slippers, daily wear, waterproof slides, beach slippers',
                        returnWindowDays: 7,
                        countryOfOrigin: 'India',
                        status: 'active',
                        sizeGuidePreset: 'footwear',
                        availableSizes: ['UK 5', 'UK 6', 'UK 7', 'UK 8', 'UK 9', 'UK 10'],
                        selectedSizes: ['UK 5', 'UK 6', 'UK 7', 'UK 8', 'UK 9', 'UK 10']
                    },
                    fw_sandals: {
                        name: "Casual Sandals & Outdoor Floaters",
                        shortName: 'Sandals/Floaters',
                        tab: 'footwear',
                        icon: '👡',
                        badge: '520g • UK 6-10',
                        categorySlug: 'footwear-sandals-floaters',
                        categoryId: 47,
                        basePrice: '899',
                        compareAtPrice: '1799',
                        totalStock: 45,
                        skuPrefix: 'SAND',
                        title: 'Rugged All-Terrain Adjustable Velcro Strap Outdoor Walking Floater Sandals',
                        description: `Durable outdoor walking floater sandals built for active daily travel and monsoon wear. Equipped with quick-dry nylon webbing straps, triple adjustable velcro closures for custom foot fit, cushioned EVA footbed, and deep grooved non-slip rubber grip sole.

Key Highlights:
• Straps: Triple Adjustable Industrial Velcro Straps (Front, Ankle & Heel)
• Footbed: Soft-Padded Waterproof EVA Foam Footbed with Drainage Channels
• Sole: Rugged High-Grip Rubber Outsole with Multi-Directional Lugs
• Comfort: Padded Neoprene Back-Strap Lining to Prevent Blisters
• Occasion: Daily Walking, Rainy Season / Monsoon Wear, Outdoor Treks & Casuals`,
                        material: 'Heavy-Duty Quick-Dry Nylon Webbing, Cushioned EVA Footbed & Rugged Rubber Sole',
                        fabricPills: ['Nylon Webbing Straps', 'Adjustable Velcro Closures', 'Shock-Absorbing EVA Midsole', 'Rugged All-Terrain Rubber Tread', 'Quick-Drying Fabric'],
                        careInstructions: 'Wipe clean with a wet cloth or wash under running tap with mild soap. Dry in a shaded well-ventilated area.',
                        carePills: ['Hand wash with tap water', 'Air dry in shade', 'Clean velcro lint regularly', 'Quick-drying materials'],
                        gender: 'men',
                        brand: 'TrailMaster Gear',
                        weightGrams: 520,
                        hsnCode: '6402',
                        gstRate: '12',
                        tags: 'sandals, floaters, outdoor sandals, velcro sandals, casual sandals, monsoon footwear',
                        returnWindowDays: 7,
                        countryOfOrigin: 'India',
                        status: 'active',
                        sizeGuidePreset: 'footwear',
                        availableSizes: ['UK 6', 'UK 7', 'UK 8', 'UK 9', 'UK 10'],
                        selectedSizes: ['UK 6', 'UK 7', 'UK 8', 'UK 9', 'UK 10']
                    },
                    fw_womens_heels: {
                        name: "Women's Party Heels, Stilettos & Block Heels",
                        shortName: 'Heels/Wedges',
                        tab: 'footwear',
                        icon: '👠',
                        badge: '480g • UK 3-8',
                        categorySlug: 'footwear-heels',
                        categoryId: 27,
                        basePrice: '1499',
                        compareAtPrice: '2899',
                        totalStock: 30,
                        skuPrefix: 'HEEL',
                        title: 'Embellished Ankle-Strap Block Heel Stilettos with Dual-Layer Comfort Cushioning',
                        description: `Dazzling festive and party block heels designed to elevate ethnic sarees, lehengas, and western gowns. Handcrafted with an elegant criss-cross ankle strap, sturdy 2.5-inch block heel for steady balanced posture, and dual-density memory foam padding in the ball of the foot.

Key Highlights:
• Heel Type & Height: 2.5 Inch Sturdy Block Heel (No Wobble Stability)
• Closure: Adjustable Metal Buckle Ankle Strap with Secure Elastic Gusset
• Insole: Double-Padded Memory Foam Footbed for Pain-Free Standing
• Outsole: Anti-Skid Ribbed Resin Sheet Sheet Sole
• Occasion: Weddings, Cocktail Receptions, Sangeet Parties & Festive Dinners`,
                        material: 'Metallic Shimmer / Patent Faux Leather Upper, Dual-Layer Cushion Footbed & Non-Slip Resin Sole',
                        fabricPills: ['Metallic Shimmer Finish', 'Soft Vegan Leather', 'Sturdy Block Heel (2.5 Inch)', 'Memory Cushion Insole', 'Anti-Skid Resin Sheet Sole'],
                        careInstructions: 'Wipe gently with a soft dry cloth. Keep rhinestones or embellishments safe in a velvet dust pouch. Do not expose to moisture.',
                        carePills: ['Wipe clean with dry microfiber', 'Store in individual shoe dust bag', 'Keep heel caps checked', 'Do not soak'],
                        gender: 'women',
                        brand: 'Belleza Stiletto Studio',
                        weightGrams: 480,
                        hsnCode: '6404',
                        gstRate: '12',
                        tags: 'heels, block heels, party heels, bridal heels, stilettos, wedges, women footwear, ethnic heels',
                        returnWindowDays: 7,
                        countryOfOrigin: 'India',
                        status: 'active',
                        sizeGuidePreset: 'footwear',
                        availableSizes: ['UK 3', 'UK 4', 'UK 5', 'UK 6', 'UK 7', 'UK 8'],
                        selectedSizes: ['UK 3', 'UK 4', 'UK 5', 'UK 6', 'UK 7', 'UK 8']
                    },
                    fw_womens_juttis: {
                        name: "Ethnic Juttis, Mojaris & Kolhapuri Chappals",
                        shortName: 'Ethnic Jutti',
                        tab: 'footwear',
                        icon: '👡',
                        badge: '360g • UK 3-8',
                        categorySlug: 'footwear-ethnic-juttis',
                        categoryId: 44,
                        basePrice: '1199',
                        compareAtPrice: '2399',
                        totalStock: 35,
                        skuPrefix: 'JUTT',
                        title: 'Handcrafted Royal Zari & Pearl Embroidered Velvet Punjabi Jutti / Mojari',
                        description: `Royal handcrafted Punjabi Jutti finished with exquisite Dabka, Zari, and faux pearl embroidery over a lush velvet base. Crafted on a 100% genuine vegetable-tanned leather sole with double-cushioned bite-free memory foam padding to keep your feet blister-free all day during weddings and pooja festivities.

Key Highlights:
• Craft: Authentic Artisan Zari, Moti, Dabka & Sequence Hand Embroidery
• Insole: Double-Padded 100% Bite-Free Orthopedic Memory Foam Cushion
• Sole: Genuine Vegetable-Tanned Leather Sole (Flexible & Durable)
• Fit: Molds to the Shape of Your Feet Naturally After 1-2 Wears
• Occasion: Bridal, Sangeet, Karwa Chauth, Diwali Pooja & Festive Family Gatherings`,
                        material: 'Rich Micro-Velvet Upper, Hand Zari Threadwork & 100% Genuine Leather Sole with Double Padding',
                        fabricPills: ['Micro-Velvet Embroidered', 'Hand Zari & Moti Work', 'Pure Leather Sole', 'Double Bite-Free Cushioning', 'Dabka & Zardozi Handwork'],
                        careInstructions: 'Dry clean or spot clean embroidery using a soft brush. Keep wrapped in a clean muslin or cotton bag with silica gel. Avoid stepping into water.',
                        carePills: ['Bite-free double padded', 'Store wrapped in muslin cloth', 'Avoid exposure to water', 'Gentle spot clean only'],
                        gender: 'women',
                        brand: 'Patiala Shahi Juttis',
                        weightGrams: 360,
                        hsnCode: '6403',
                        gstRate: '12',
                        tags: 'jutti, mojari, punjabi jutti, kolhapuri, ethnic shoes, wedding jutti, leather jutti, bridal footwear',
                        returnWindowDays: 7,
                        countryOfOrigin: 'India',
                        status: 'active',
                        sizeGuidePreset: 'footwear',
                        availableSizes: ['UK 3', 'UK 4', 'UK 5', 'UK 6', 'UK 7', 'UK 8'],
                        selectedSizes: ['UK 3', 'UK 4', 'UK 5', 'UK 6', 'UK 7', 'UK 8']
                    },
                    fw_womens_flats: {
                        name: "Women's Flats, Bellies & Ballerinas",
                        shortName: 'Flats/Bellies',
                        tab: 'footwear',
                        icon: '🩰',
                        badge: '320g • UK 3-8',
                        categorySlug: 'footwear-flats',
                        categoryId: 28,
                        basePrice: '799',
                        compareAtPrice: '1599',
                        totalStock: 40,
                        skuPrefix: 'FLAT',
                        title: 'Pointed-Toe Slip-On Cushioned Ballerina Bellies for Office & Daily Casuals',
                        description: `Chic, versatile slip-on pointed-toe ballerina flats designed for effortless 9-to-9 daily comfort. Features a soft matte faux leather upper, breathable lining, high-density arch cushion footbed, and ultra-flexible slip-resistant TPR rubber sole.

Key Highlights:
• Toe Style: Sleek Pointed Toe with Subtle Bow / Metal Hardware
• Insole: High-Density Ergonomic Arch Cushioning to Prevent Foot Fatigue
• Sole: 360-Degree Flexible Slip-Resistant Honeycomb TPR Sole
• Lining: Sweat-Absorbing Microfiber Fabric Lining
• Occasion: Corporate Workwear, College, Shopping, Casual Outings & Daily Commute`,
                        material: 'Soft Matte Vegan Leather Upper, Cushion Arch Footbed & Flexible Non-Slip TPR Rubber Sole',
                        fabricPills: ['Soft Matte Vegan Leather', 'Breathable Cushion Insole', 'Pointed Toe Silhouette', 'Ultra-Flexible Rubber Sole', 'Slip-On Easy Fit'],
                        careInstructions: 'Wipe with a soft damp cloth. Allow to air dry completely. Do not machine wash.',
                        carePills: ['Wipe clean with damp cloth', 'Air dry in shade', 'Do not soak', 'Store in dry place'],
                        gender: 'women',
                        brand: 'SoleComfort London',
                        weightGrams: 320,
                        hsnCode: '6404',
                        gstRate: '12',
                        tags: 'flats, bellies, ballerinas, pointed flats, slip on shoes, office flats, women casual shoes',
                        returnWindowDays: 7,
                        countryOfOrigin: 'India',
                        status: 'active',
                        sizeGuidePreset: 'footwear',
                        availableSizes: ['UK 3', 'UK 4', 'UK 5', 'UK 6', 'UK 7', 'UK 8'],
                        selectedSizes: ['UK 3', 'UK 4', 'UK 5', 'UK 6', 'UK 7', 'UK 8']
                    },
                    fw_boots: {
                        name: "Ankle Boots, Chelsea Boots & High-Tops",
                        shortName: 'Boots',
                        tab: 'footwear',
                        icon: '👢',
                        badge: '900g • UK 6-11',
                        categorySlug: 'footwear-boots',
                        categoryId: 49,
                        basePrice: '2499',
                        compareAtPrice: '4999',
                        totalStock: 25,
                        skuPrefix: 'BOOT',
                        title: 'Premium Leather Chelsea Ankle Boots with Elastic Gusset & Rugged Commando Sole',
                        description: `Classic Chelsea ankle boots tailored from heavy-gauge polished leather with stretchable side elastic gusset for swift slip-on wearing. Mounted on a heavy-duty lugged commando rubber sole that ensures unbeatable grip and rugged outdoor styling.

Key Highlights:
• Silhouette: Ankle-Length Chelsea Boot with Dual Pull Tabs
• Gusset: Premium High-Tension Ribbed Elastic Side Panels
• Outsole: Deep-Grooved All-Weather Lugged Commando Rubber Sole
• Insole: Molded Shock-Absorbing Arch Support Footbed
• Occasion: Winter Styling, Biker Rides, Travel, Concerts & Casual Streetwear`,
                        material: 'Full-Grain / Heavy PU Leather Upper, Stretchy Elastic Side Webbing & Deep Lugged Rubber Commando Sole',
                        fabricPills: ['Full-Grain Leather', 'Elastic Stretch Gusset', 'Lugged Commando Sole', 'Pull-Up Back Loop', 'Shock-Absorbing Heel'],
                        careInstructions: 'Clean off mud with a damp cloth. Polish regularly with leather balm. Store upright in a cool, dry closet.',
                        carePills: ['Wipe mud with damp cloth', 'Condition with leather balm', 'Store upright with boot shapers', 'Avoid moisture'],
                        gender: 'unisex',
                        brand: 'Highland Rugged Boots',
                        weightGrams: 900,
                        hsnCode: '6403',
                        gstRate: '12',
                        tags: 'boots, chelsea boots, ankle boots, leather boots, winter boots, high top shoes, combat boots',
                        returnWindowDays: 7,
                        countryOfOrigin: 'India',
                        status: 'active',
                        sizeGuidePreset: 'footwear',
                        availableSizes: ['UK 6', 'UK 7', 'UK 8', 'UK 9', 'UK 10', 'UK 11'],
                        selectedSizes: ['UK 6', 'UK 7', 'UK 8', 'UK 9', 'UK 10', 'UK 11']
                    },
                    fw_kids_shoes: {
                        name: "Kids & School Shoes, Light-Up Sneakers & Sandals",
                        shortName: 'Kids Shoes',
                        tab: 'footwear',
                        icon: '👟',
                        badge: '380g • Kids Sizes',
                        categorySlug: 'footwear-kids-footwear',
                        categoryId: 51,
                        basePrice: '799',
                        compareAtPrice: '1499',
                        totalStock: 45,
                        skuPrefix: 'KSHOE',
                        title: 'Kids Light-Up Cartoon LED Sneakers with Dual Velcro Straps & Anti-Skid Soles',
                        description: `Joyful, lightweight sneakers tailored specifically for energetic children and toddlers. Features dual adjustable velcro straps for easy self-wearing, fun motion-activated LED light-up soles, cushioned ankle support, and wide toe-box to protect growing young feet.

Key Highlights:
• Closure: Quick Dual Velcro Straps (Kid-Friendly Self-Wearing)
• Soles: Motion-Activated Rainbow LED Flashing Lights in Midsole
• Toe Box: Reinforced Round Toe Bumper Against Scuffs and Bumps
• Weight: Featherlight Construction to Prevent Tripping & Ankle Strain
• Occasion: School Activities, Playgrounds, Birthday Parties & Casual Outings`,
                        material: 'Breathable Mesh & Synthetic Leather, Soft Padded Collar & Anti-Skid LED Rubber Sole',
                        fabricPills: ['Breathable Mesh Fabric', 'Dual Velcro Straps (Easy Wear)', 'LED Light-Up Soles', 'Anti-Skid Non-Marking Rubber', 'Extra Toe-Box Protection'],
                        careInstructions: 'Wipe with a soft damp cloth. Do not immerse in water to preserve battery-powered LED lights.',
                        carePills: ['Wipe clean with damp cloth', 'Do not submerge in water', 'Air dry in shade', 'Check velcro clean'],
                        gender: 'kids',
                        brand: 'TinySteps Playwear',
                        weightGrams: 380,
                        hsnCode: '6404',
                        gstRate: '12',
                        tags: 'kids shoes, boys shoes, girls shoes, school shoes, light up sneakers, velcro shoes, toddler footwear',
                        returnWindowDays: 7,
                        countryOfOrigin: 'India',
                        status: 'active',
                        sizeGuidePreset: 'footwear',
                        availableSizes: ['UK 7C', 'UK 8C', 'UK 9C', 'UK 10C', 'UK 11C', 'UK 12C', 'UK 13C', 'UK 1', 'UK 2', 'UK 3'],
                        selectedSizes: ['UK 8C', 'UK 9C', 'UK 10C', 'UK 11C', 'UK 12C', 'UK 13C', 'UK 1', 'UK 2']
                    },
                    fw_baby_booties: {
                        name: "Newborn & Infant Baby Booties / Soft Crib Shoes",
                        shortName: 'Baby Booties',
                        tab: 'footwear',
                        icon: '🧦',
                        badge: '120g • 0-12M',
                        categorySlug: 'footwear-baby-booties',
                        categoryId: 52,
                        basePrice: '399',
                        compareAtPrice: '799',
                        totalStock: 50,
                        skuPrefix: 'BABY',
                        title: 'Ultra-Soft Pure Cotton Fleece Pre-Walker Baby Booties with Anti-Slip Grip Dots',
                        description: `Gentle, featherlight first-step crib shoes and booties specially designed for newborns and infants (0-12 Months). Handcrafted from hypoallergenic 100% organic cotton fleece with soft elasticated ankle cuff that stays securely on active baby feet without slipping off, and silicone anti-skid bottom dots.

Key Highlights:
• Age Group: 0 to 12 Months (Pre-Walker & Crawling Stage)
• Ankle Fit: Soft Elasticated Ankle Cuff (Won't Kick Off or Pinch Delicate Skin)
• Bottom Sole: Non-Slip Silicone Dotted Fabric (Safe for Crawlers & Standing)
• Inner Seams: Zero-Chafing Seamless Soft Lining (100% Baby-Safe)
• Occasion: Daily Newborn Wear, Hospital Discharge, Baby Photoshoots & AC Rooms`,
                        material: '100% Organic Breathable Cotton Fleece, Soft Velour Lining & Silicone Anti-Skid Gripper Dots',
                        fabricPills: ['100% Organic Cotton Fleece', 'Hypoallergenic Soft Velour', 'Gentle Elastic Ankle Grip (Stay-On)', 'Silicone Non-Slip Bottom Dots', 'Zero Chafing Seams'],
                        careInstructions: 'Gentle hand wash or delicate machine wash in laundry bag using mild baby detergent. Air dry flat.',
                        carePills: ['Gentle hand wash cold', 'Use baby-safe liquid detergent', 'Air dry flat in shade', 'Do not tumble dry'],
                        gender: 'kids',
                        brand: 'SweetCuddle Baby Studio',
                        weightGrams: 120,
                        hsnCode: '6405',
                        gstRate: '12',
                        tags: 'baby booties, newborn shoes, infant shoes, pre walker, crib shoes, soft sole shoes, baby socks booties',
                        returnWindowDays: 7,
                        countryOfOrigin: 'India',
                        status: 'active',
                        sizeGuidePreset: 'none',
                        availableSizes: ['0-3 Months', '3-6 Months', '6-9 Months', '9-12 Months'],
                        selectedSizes: ['0-3 Months', '3-6 Months', '6-9 Months', '9-12 Months']
                    }
                    @endif
                },

                    @if($targetVertical === 'marketplace')
                    sizeGuide: {
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
                        dress: {
                            type: 'dress',
                            title: "Women's Western Dresses & Tops Size Guide",
                            tip: 'Measure around fullest part of bust and natural waistline. For a relaxed fit, choose one size up.',
                            columns: ['Size', 'Bust (in)', 'Waist (in)', 'Hip (in)', 'Length (in)'],
                            rows: [
                                { 'Size': 'XS (32)', 'Bust (in)': '32', 'Waist (in)': '26', 'Hip (in)': '35', 'Length (in)': '43' },
                                { 'Size': 'S (34)', 'Bust (in)': '34', 'Waist (in)': '28', 'Hip (in)': '37', 'Length (in)': '44' },
                                { 'Size': 'M (36)', 'Bust (in)': '36', 'Waist (in)': '30', 'Hip (in)': '39', 'Length (in)': '44.5' },
                                { 'Size': 'L (38)', 'Bust (in)': '38', 'Waist (in)': '32', 'Hip (in)': '41', 'Length (in)': '45' },
                                { 'Size': 'XL (40)', 'Bust (in)': '40', 'Waist (in)': '34', 'Hip (in)': '43', 'Length (in)': '45.5' }
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
                                { 'Component': 'Saree Width', 'Length / Dimension': '44 Inches (1.1m)', 'Specification': 'Standard border fall width' },
                                { 'Component': 'Pallu Weave', 'Length / Dimension': '1.00 Meter', 'Specification': 'Heavy Jacquard Zari weave' }
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
                                { 'Age / Size': '8-9 Years', 'Chest (in)': '27', 'Waist (in)': '23.5', 'Height (cm)': '128-134' },
                                { 'Age / Size': '10-11 Years', 'Chest (in)': '29', 'Waist (in)': '25', 'Height (cm)': '140-146' }
                            ]
                        },
                        footwear: {
                            type: 'footwear',
                            title: 'Footwear & Shoe Size Conversion Guide',
                            tip: 'Measure foot length from heel to tip of longest toe in cm. If you have broad feet or fall between sizes, pick one size larger.',
                            columns: ['UK / IND', 'US Size', 'EU Size', 'Foot Length (cm)', 'Foot Length (in)'],
                            rows: [
                                { 'UK / IND': 'UK 3', 'US Size': 'US 4', 'EU Size': 'EU 36', 'Foot Length (cm)': '22.0', 'Foot Length (in)': '8.7' },
                                { 'UK / IND': 'UK 4', 'US Size': 'US 5', 'EU Size': 'EU 37', 'Foot Length (cm)': '22.9', 'Foot Length (in)': '9.0' },
                                { 'UK / IND': 'UK 5', 'US Size': 'US 6', 'EU Size': 'EU 38', 'Foot Length (cm)': '23.7', 'Foot Length (in)': '9.3' },
                                { 'UK / IND': 'UK 6', 'US Size': 'US 7', 'EU Size': 'EU 40', 'Foot Length (cm)': '24.5', 'Foot Length (in)': '9.6' },
                                { 'UK / IND': 'UK 7', 'US Size': 'US 8', 'EU Size': 'EU 41', 'Foot Length (cm)': '25.4', 'Foot Length (in)': '10.0' },
                                { 'UK / IND': 'UK 8', 'US Size': 'US 9', 'EU Size': 'EU 42', 'Foot Length (cm)': '26.0', 'Foot Length (in)': '10.2' },
                                { 'UK / IND': 'UK 9', 'US Size': 'US 10', 'EU Size': 'EU 43', 'Foot Length (cm)': '27.0', 'Foot Length (in)': '10.6' },
                                { 'UK / IND': 'UK 10', 'US Size': 'US 11', 'EU Size': 'EU 44', 'Foot Length (cm)': '27.9', 'Foot Length (in)': '11.0' },
                                { 'UK / IND': 'UK 11', 'US Size': 'US 12', 'EU Size': 'EU 45', 'Foot Length (cm)': '28.8', 'Foot Length (in)': '11.3' }
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
                    @else
                    sizeGuide: {
                        type: 'none',
                        title: '',
                        tip: '',
                        columns: [],
                        rows: []
                    },

                    presets: {},
                    @endif

                applyCategoryPreset(key) {
                    const p = this.categoryPresets[key];
                    if (!p) return;
                    this.activePreset = key;
                    this.title = p.title;
                    this.description = p.description;
                    this.categoryId = (this.categorySlugMap && this.categorySlugMap[p.categorySlug]) ? this.categorySlugMap[p.categorySlug] : p.categoryId;
                    this.basePrice = p.basePrice || '';
                    this.compareAtPrice = p.compareAtPrice || '';
                    this.totalStock = p.totalStock || 25;
                    this.material = p.material;
                    this.careInstructions = p.careInstructions;
                    this.gender = p.gender;
                    this.brand = p.brand;
                    this.tags = p.tags;
                    this.weightGrams = p.weightGrams;
                    this.hsnCode = p.hsnCode;
                    this.gstRate = p.gstRate;
                    this.returnWindowDays = p.returnWindowDays || 7;
                    this.countryOfOrigin = p.countryOfOrigin || 'India';
                    this.status = p.status || 'active';
                    this.fabricPills = p.fabricPills || [];
                    this.carePills = p.carePills || [];
                    this.availableSizes = p.availableSizes || [];
                    this.selectedSizes = [...(p.selectedSizes || [])];

                    if (p.sizeGuidePreset && this.presets[p.sizeGuidePreset]) {
                        this.applyPreset(p.sizeGuidePreset);
                    } else if (p.sizeGuidePreset === 'none') {
                        this.applyPreset('none');
                    }

                    this.syncVariantsFromSelectedSizes(p.skuPrefix);

                    this.notify(`⚡ ${p.name} loaded to utmost extent! Specs, Price ₹${p.basePrice}, MRP ₹${p.compareAtPrice}, HSN ${p.hsnCode}, ${p.weightGrams}g, size charts & ${this.variants.length} variants pre-filled.`);
                },

                toggleSize(sz) {
                    const idx = this.selectedSizes.indexOf(sz);
                    if (idx > -1) {
                        this.selectedSizes.splice(idx, 1);
                    } else {
                        this.selectedSizes.push(sz);
                    }
                    this.syncVariantsFromSelectedSizes();
                },

                syncVariantsFromSelectedSizes(skuPrefix) {
                    if (this.selectedSizes.length === 0) {
                        this.options = [];
                        this.variants = [];
                        return;
                    }

                    this.options = [{
                        name: 'Size',
                        valuesString: this.selectedSizes.join(', '),
                        values: [...this.selectedSizes]
                    }];

                    const prefix = skuPrefix || (this.activePreset && this.categoryPresets[this.activePreset]?.skuPrefix) || 'PRD';

                    this.variants = this.selectedSizes.map(sz => {
                        const cleanSz = sz.replace(/[^A-Za-z0-9]/g, '').toUpperCase();
                        const existing = this.variants.find(v => v.name === sz);
                        return existing || {
                            name: sz,
                            price: '',
                            stock: 10,
                            sku: `${prefix}-${cleanSz}`
                        };
                    });
                },

                setGender(g) {
                    this.gender = g;
                    // Footwear dynamic size adaptation
                    if (this.isFootwear) {
                        if (g === 'men') {
                            this.availableSizes = ['UK 6', 'UK 7', 'UK 8', 'UK 9', 'UK 10', 'UK 11'];
                            this.selectedSizes = ['UK 6', 'UK 7', 'UK 8', 'UK 9', 'UK 10', 'UK 11'];
                        } else if (g === 'women') {
                            this.availableSizes = ['UK 3', 'UK 4', 'UK 5', 'UK 6', 'UK 7', 'UK 8'];
                            this.selectedSizes = ['UK 3', 'UK 4', 'UK 5', 'UK 6', 'UK 7', 'UK 8'];
                        } else if (g === 'kids') {
                            this.availableSizes = ['Kids 1', 'Kids 2', 'Kids 3', 'Kids 4', 'Kids 5'];
                            this.selectedSizes = ['Kids 1', 'Kids 2', 'Kids 3', 'Kids 4', 'Kids 5'];
                        } else {
                            this.availableSizes = ['UK 4', 'UK 5', 'UK 6', 'UK 7', 'UK 8', 'UK 9', 'UK 10', 'UK 11'];
                            this.selectedSizes = ['UK 5', 'UK 6', 'UK 7', 'UK 8', 'UK 9', 'UK 10'];
                        }
                        this.syncVariantsFromSelectedSizes();
                    }
                    this.notify(`Target Audience set to ${g.toUpperCase()}! Title & suggestions updated.`);
                },

                get isFootwear() {
                    const t = (this.title || '').toLowerCase();
                    const c = (this.activePreset || '').toLowerCase();
                    return c.includes('boot') || c.includes('footwear') || c.includes('shoe') ||
                           t.includes('boot') || t.includes('shoe') || t.includes('loafer') || 
                           t.includes('jutti') || t.includes('sneaker') || t.includes('sandal') || t.includes('heel');
                },

                get smartTitleSuggestion() {
                    if (!this.title || this.title.trim().length < 2) return '';
                    const raw = this.title.trim();
                    @if($targetVertical === 'food')
                    let candidate = raw.replace(/\b\w/g, l => l.toUpperCase());
                    const fLow = candidate.toLowerCase();
                    if (!fLow.includes('fresh') && !fLow.includes('authentic') && !fLow.includes('royal') && !fLow.includes('special')) {
                        candidate = 'Fresh ' + candidate;
                    }
                    return (candidate.toLowerCase() !== raw.toLowerCase()) ? candidate : '';
                    @elseif($targetVertical === 'services')
                    let candidate = raw.replace(/\b\w/g, l => l.toUpperCase());
                    const sLow = candidate.toLowerCase();
                    if (!sLow.includes('service') && !sLow.includes('repair') && !sLow.includes('package') && !sLow.includes('cleaning')) {
                        candidate = candidate + ' Service';
                    }
                    return (candidate.toLowerCase() !== raw.toLowerCase()) ? candidate : '';
                    @elseif($targetVertical === 'used')
                    let candidate = raw.replace(/\b\w/g, l => l.toUpperCase());
                    const uLow = candidate.toLowerCase();
                    if (!uLow.includes('pre-owned') && !uLow.includes('refurbished') && !uLow.includes('tested')) {
                        candidate = 'Pre-Owned ' + candidate;
                    }
                    return (candidate.toLowerCase() !== raw.toLowerCase()) ? candidate : '';
                    @else
                    let base = raw.replace(/\b\w/g, l => l.toUpperCase());
                    const lower = base.toLowerCase();

                    let genderPrefix = '';
                    if (this.gender === 'men' && !lower.includes('men')) {
                        genderPrefix = "Men's ";
                    } else if (this.gender === 'women' && !lower.includes('women')) {
                        genderPrefix = "Women's ";
                    } else if (this.gender === 'kids' && !lower.includes('kid') && !lower.includes('child')) {
                        genderPrefix = "Kids' ";
                    }

                    let materialWord = '';
                    if (this.material && this.material.trim().length > 1) {
                        const mat = this.material.split(/[,&/]/)[0].trim().replace(/\b\w/g, l => l.toUpperCase());
                        if (!lower.includes(mat.toLowerCase())) {
                            materialWord = mat + ' ';
                        }
                    }

                    let craftWord = '';
                    if (base.split(' ').length <= 2) {
                        craftWord = 'Handcrafted ';
                    }

                    let candidate = `${genderPrefix}${craftWord}${materialWord}${base}`.replace(/\s+/g, ' ').trim();
                    
                    if (lower === 'boots' || lower === 'chelsea boots' || lower === 'chelsea boot') {
                        const gLabel = this.gender === 'women' ? "Women's" : (this.gender === 'kids' ? "Kids'" : "Men's");
                        const matLabel = this.material || 'Genuine Leather';
                        candidate = `${gLabel} Handcrafted ${matLabel} Chelsea Ankle Boots with Elastic Gusset`;
                    }

                    return (candidate.toLowerCase() !== raw.toLowerCase()) ? candidate : '';
                    @endif
                },

                applySmartTitle() {
                    if (!this.smartTitleSuggestion) return;
                    this.title = this.smartTitleSuggestion;
                    this.notify('✨ High-Ranking Title applied!');
                },

                polishDescription() {
                    @if($targetVertical === 'food')
                    const ingredients = this.material || 'Farm-Fresh Spices, Fresh Herbs & Artisanal Ingredients';
                    const dish = this.title || 'Specialty Delicacy';
                    const storage = this.careInstructions || 'Consume hot within 3-4 hours. Tamper-evident packaging. Reheat container lid-off for 90 seconds if desired.';
                    this.description = `Indulge in our chef-prepared ${dish}, slow-cooked to culinary perfection using premium ingredients. Every portion is freshly prepared upon order to preserve rich aromas, authentic regional flavors, and exquisite texture.

Key Culinary Highlights:
• Signature Ingredients: ${ingredients}
• Preparation: Freshly prepared in clean, hygienic certified kitchen facilities
• Taste Profile: Balanced, deeply aromatic, and authentic homestyle richness
• Packaging: 100% Food-grade, tamper-proof, spill-resistant eco containers
• Ideal For: Hearty Meals, Family Dinners, Celebrations & Quick Bites

Serving & Storage Advice:
${storage}`;
                    @elseif($targetVertical === 'services')
                    const tools = this.material || 'Commercial-Grade Professional Diagnostic Toolkits';
                    const serviceName = this.title || 'Professional Doorstep Service';
                    const advice = this.careInstructions || 'Keep clear access to work area. Spares or structural parts charged at actuals with transparent pricing.';
                    this.description = `Experience certified, hassle-free doorstep assistance with our professional ${serviceName}. Performed by background-verified, licensed technicians adhering to strict service protocols and satisfaction standards.

Service Scope & Highlights:
• Equipment & Standards: Standardized tools (${tools}) with safe handling
• Technicians: Background-verified, certified, and experienced professionals
• Quality Guarantee: High-standard workmanship with post-service inspection
• Transparency: Upfront quotation, zero hidden charges, clean work-area handover

Client Prerequisites & Advice:
${advice}`;
                    @elseif($targetVertical === 'used')
                    const specs = this.material || 'Original Chassis, Tested Components & Accessories';
                    const itemName = this.title || 'Certified Pre-Owned Item';
                    const testing = this.careInstructions || 'Fully factory reset. Clean IMEI / serial numbers. Covered under testing warranty.';
                    this.description = `Verified and certified pre-owned ${itemName} offered in exceptional working condition. Inspected on our multi-point diagnostic protocol to ensure total reliability, battery health, and peak performance.

Inspection & Product Highlights:
• Condition & Build: ${specs}
• Diagnostic Status: 100% fully functional, cleaned, sanitized, and reset
• Authenticity: Guaranteed genuine with clean hardware history
• Inclusions: As listed in package, securely boxed for safe handover

Testing Advice & Warranty:
${testing}`;
                    @else
                    const fabric = this.material || (this.isFootwear ? 'Full-Grain Genuine Leather' : 'Artisan Handcrafted Fabric');
                    const title = this.title || (this.isFootwear ? 'Boots' : 'Apparel Piece');
                    const care = this.careInstructions || (this.isFootwear ? 'Wipe clean with a damp cloth. Nourish periodically with leather conditioner.' : 'Gentle cold wash recommended.');
                    const genderLabel = this.gender === 'men' ? "Men's" : (this.gender === 'women' ? "Women's" : (this.gender === 'kids' ? "Kids'" : "Unisex"));
                    
                    if (this.isFootwear) {
                        this.description = `Meticulously crafted ${genderLabel} ${title}, engineered for commanding style and enduring all-day comfort. Shaped from select ${fabric}, each pair features an ergonomic cushioned insole, reinforced stress points, and an anti-skid durable all-weather traction sole.

Key Highlights:
• Construction: Hand-finished artisan silhouette with reinforced stitching
• Comfort: High-density shock-absorbing arch support footbed
• Sole: Anti-skid rugged all-weather traction sole
• Insole Lining: Breathable sweat-wicking comfort lining
• Occasion: Daily Wear, Evening Outings, Travel & Statement Styling

Care Instructions:
${care}`;
                    } else if (this.title && (this.title.toLowerCase().includes('saree') || this.title.toLowerCase().includes('dupatta'))) {
                        this.description = `Grace every festive celebration with this exquisite ${genderLabel} ${title}. Handcrafted from authentic ${fabric}, it features a magnificent fluid drape, intricate artisan borders, and rich ceremonial luster.

Key Highlights:
• Fabric & Weave: Authentic ${fabric} with intricate artisan finish
• Drape: Lightweight, graceful, and easy to pleat and style
• Texture: Soft, breathable, and skin-friendly weave
• Occasion: Weddings, Festivals, Family Celebrations & Festive Poojas

Care Instructions:
${care}`;
                    } else {
                        this.description = `Elevate your everyday wardrobe with this modern ${genderLabel} ${title}. Tailored from premium ${fabric}, it provides effortless breathability, sharp structured silhouette, and all-day ease.

Key Highlights:
• Fabric: Breathable, long-staple ${fabric} designed for all-day comfort
• Fit: Tailored modern silhouette for flattering structure and ease of movement
• Craftsmanship: Precision twin-needle stitching and durable trims
• Occasion: Smart Casuals, Festive Gatherings, Workwear & Weekend Outings

Care Instructions:
${care}`;
                    }
                    @endif
                    
                    this.notify('✨ Smart category-aware description generated!');
                },

                notify(msg) {
                    this.toastMessage = msg;
                    this.showToast = true;
                    setTimeout(() => { this.showToast = false; }, 4500);
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
                    if (this.selectedColors.length > 0 && this.selectedSizes.length > 0) {
                        this.generateFashionMatrix();
                    }
                    this.$watch('categoryId', val => {
                        if (val) this.fetchCategoryBlueprint(val);
                    });
                    if (this.categoryId) {
                        this.fetchCategoryBlueprint(this.categoryId);
                    }
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
                    const prefix = (this.title ? this.title.substring(0, 3).toUpperCase().replace(/[^A-Z]/g, '') : 'PRD') || 'PRD';

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
                    this.notify('⚡ Applied bulk price & stock to all variant rows!');
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

        function mediaGalleryEngine() {
            return {
                items: [],
                isDragging: false,

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
                            file: file,
                            url: url,
                            name: file.name
                        });
                    }
                    this.syncFiles();
                },

                remove(index) {
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
                    this.items.forEach(item => {
                        if (item.file) {
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
