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
                    <p class="text-[10px] sm:text-xs text-gray-500 leading-tight">Instant pre-fills tailored exclusively for your {{ (isset($verticals) && isset($verticals[$targetVertical]['name'])) ? $verticals[$targetVertical]['name'] : ucfirst($targetVertical ?? 'Store') }} catalog.</p>
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
        @elseif($targetVertical === 'enquiry')
            <div class="flex items-center gap-1.5 overflow-x-auto pb-2 mb-2.5 no-scrollbar" style="-webkit-overflow-scrolling: touch;">
                <button type="button" @click="presetTab = 'all'"
                        :class="presetTab === 'all' ? 'bg-stone-900 text-white shadow-2xs font-bold' : 'bg-stone-100 text-stone-600 hover:bg-stone-200 font-medium'"
                        class="preset-tab-btn text-[11px] sm:text-xs px-2.5 py-1 rounded-lg shrink-0 cursor-pointer">
                    ✨ All Industrial (4)
                </button>
                <button type="button" @click="presetTab = 'stone'"
                        :class="presetTab === 'stone' ? 'bg-stone-900 text-white shadow-2xs font-bold' : 'bg-stone-100 text-stone-600 hover:bg-stone-200 font-medium'"
                        class="preset-tab-btn text-[11px] sm:text-xs px-2.5 py-1 rounded-lg shrink-0 cursor-pointer">
                    🏛️ Marble & Granite
                </button>
                <button type="button" @click="presetTab = 'machinery'"
                        :class="presetTab === 'machinery' ? 'bg-stone-900 text-white shadow-2xs font-bold' : 'bg-stone-100 text-stone-600 hover:bg-stone-200 font-medium'"
                        class="preset-tab-btn text-[11px] sm:text-xs px-2.5 py-1 rounded-lg shrink-0 cursor-pointer">
                    ⚙️ Machinery & CNC
                </button>
                <button type="button" @click="presetTab = 'packaging'"
                        :class="presetTab === 'packaging' ? 'bg-stone-900 text-white shadow-2xs font-bold' : 'bg-stone-100 text-stone-600 hover:bg-stone-200 font-medium'"
                        class="preset-tab-btn text-[11px] sm:text-xs px-2.5 py-1 rounded-lg shrink-0 cursor-pointer">
                    📦 Packaging & Wholesale
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
