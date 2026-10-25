{{-- Universal Dynamic Variant, Attribute & UX Engine (Vendor Dashboard) --}}
<div class="space-y-6" x-cloak>
    <!-- Dynamic Category Vertical Engine Header -->
    <div class="bg-gradient-to-r from-stone-900 via-stone-800 to-stone-900 text-white rounded-2xl p-5 sm:p-6 shadow-md border border-stone-700/50">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-white/10 flex items-center justify-center text-xl shrink-0 backdrop-blur-sm border border-white/20">
                    <template x-if="verticalType === 'fashion_matrix'"><span>👗</span></template>
                    <template x-if="verticalType === 'food_portion'"><span>🍲</span></template>
                    <template x-if="verticalType === 'digital_gig'"><span>💻</span></template>
                    <template x-if="verticalType === 'b2b_machinery'"><span>⚙️</span></template>
                    <template x-if="verticalType === 'service_booking'"><span>🛠️</span></template>
                    <template x-if="verticalType === 'classified_asset'"><span>🏷️</span></template>
                    <template x-if="verticalType === 'standard_retail' || !verticalType"><span>📦</span></template>
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <h2 class="text-base sm:text-lg font-bold tracking-tight text-white">Dynamic Blueprint Engine</h2>
                        <span class="text-[10px] font-black uppercase tracking-wider px-2 py-0.5 rounded-full bg-emerald-500/20 text-emerald-300 border border-emerald-500/30" x-text="verticalTypeLabel"></span>
                    </div>
                    <p class="text-xs text-stone-300 mt-0.5" x-text="verticalTypeDescription"></p>
                </div>
            </div>

            <!-- Asynchronous Schema Sync Indicator -->
            <div class="flex items-center gap-2 self-start sm:self-auto">
                <template x-if="isLoadingSchema">
                    <span class="inline-flex items-center gap-1.5 text-xs text-amber-300 font-semibold bg-amber-500/20 px-3 py-1.5 rounded-xl border border-amber-500/30">
                        <svg class="animate-spin h-3.5 w-3.5" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                        <span>Syncing Blueprint...</span>
                    </span>
                </template>
                <template x-if="!isLoadingSchema && categoryId">
                    <span class="inline-flex items-center gap-1.5 text-xs text-emerald-300 font-bold bg-emerald-500/20 px-3 py-1.5 rounded-xl border border-emerald-500/30">
                        <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
                        <span>Schema Verified</span>
                    </span>
                </template>
            </div>
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- 1. FASHION & RETAIL MATRIX BUILDER (fashion_matrix)                        -->
    <!-- ========================================================================= -->
    <div x-show="verticalType === 'fashion_matrix'" class="space-y-6">
        <div class="bg-white rounded-2xl shadow-sm border border-stone-200/80 p-5 sm:p-7">
            <div class="flex items-center justify-between pb-4 border-b border-stone-100 mb-5">
                <div>
                    <h3 class="text-sm font-bold text-stone-900 tracking-tight flex items-center gap-2">
                        <span>Color &amp; Size Matrix Engine</span>
                        <span class="text-[10px] bg-rose-50 text-rose-700 font-bold px-2 py-0.5 rounded-md border border-rose-200">Amazon / ASOS Pattern</span>
                    </h3>
                    <p class="text-xs text-stone-500 mt-0.5">Select colors and sizes to instantly generate the Cartesian SKU combination matrix with prices and stock.</p>
                </div>
            </div>

            <!-- Color Swatch Selector -->
            <div class="mb-6 p-4 rounded-xl bg-stone-50 border border-stone-200/60">
                <div class="flex items-center justify-between mb-3">
                    <label class="text-xs font-bold uppercase tracking-wider text-stone-700 flex items-center gap-1.5">
                        <span>🎨 1. Choose Color Swatches</span>
                        <span class="text-stone-400 font-normal">(&amp; hex codes)</span>
                    </label>
                    <span class="text-[11px] font-semibold text-brand-sage" x-text="selectedColors.length + ' colors selected'"></span>
                </div>
                
                <div class="flex flex-wrap gap-2.5 mb-3">
                    <template x-for="(col, cIdx) in availableColors" :key="col.name">
                        <button type="button" 
                                @click="toggleColor(col)"
                                :class="isColorSelected(col.name) 
                                    ? 'ring-2 ring-black ring-offset-2 bg-stone-900 text-white font-bold' 
                                    : 'bg-white hover:bg-stone-100 text-stone-700 border border-stone-200'"
                                class="inline-flex items-center gap-2 px-3 py-1.5 rounded-xl text-xs transition-all shadow-2xs cursor-pointer">
                            <span class="w-3.5 h-3.5 rounded-full border border-black/20 shrink-0 shadow-inner" :style="`background-color: ${col.hex}`"></span>
                            <span x-text="col.name"></span>
                            <span x-show="isColorSelected(col.name)" class="text-[10px] font-black">✓</span>
                        </button>
                    </template>
                </div>

                <!-- Custom Color Inserter -->
                <div class="flex items-center gap-2 pt-2 border-t border-stone-200/50">
                    <input type="text" x-model="customColorName" placeholder="New color name (e.g. Lavender Blush)" class="text-xs px-3 py-1.5 rounded-lg border-stone-300 w-48 bg-white">
                    <input type="color" x-model="customColorHex" class="w-8 h-8 rounded-lg cursor-pointer border-0 p-0">
                    <button type="button" @click="addCustomColor()" class="px-3 py-1.5 rounded-lg bg-stone-800 hover:bg-black text-white text-xs font-bold cursor-pointer transition">
                        + Add Color Swatch
                    </button>
                </div>
            </div>

            <!-- Size Tags Selector -->
            <div class="mb-6 p-4 rounded-xl bg-stone-50 border border-stone-200/60">
                <div class="flex items-center justify-between mb-3">
                    <label class="text-xs font-bold uppercase tracking-wider text-stone-700 flex items-center gap-1.5">
                        <span>🏷️ 2. Choose Sizing / Pack / Spec Options</span>
                        <span class="text-stone-400 font-normal">(Clothing, Shoes, Kirana, or Hardware)</span>
                    </label>
                    <span class="text-[11px] font-semibold text-brand-sage" x-text="selectedSizes.length + ' options selected'"></span>
                </div>

                <!-- Quick Category Presets Chips -->
                <div class="flex flex-wrap gap-1.5 mb-3 pb-2 border-b border-stone-200/50">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-stone-400 py-1">Quick Presets:</span>
                    <button type="button" @click="availableSizesList = ['XS', 'S', 'M', 'L', 'XL', 'XXL', '3XL', 'Free Size']; selectedSizes = ['M', 'L', 'XL']; generateFashionMatrix();" class="text-[11px] px-2.5 py-1 rounded-lg bg-indigo-50 hover:bg-indigo-100 text-indigo-700 font-bold transition">👗 Apparel (XS-3XL)</button>
                    <button type="button" @click="availableSizesList = ['UK 5', 'UK 6', 'UK 7', 'UK 8', 'UK 9', 'UK 10', 'UK 11', 'UK 12']; selectedSizes = ['UK 7', 'UK 8', 'UK 9']; generateFashionMatrix();" class="text-[11px] px-2.5 py-1 rounded-lg bg-amber-50 hover:bg-amber-100 text-amber-800 font-bold transition">👟 Shoes (UK 5-12)</button>
                    <button type="button" @click="availableSizesList = ['100 g', '250 g', '500 g', '1 kg', '2 kg', '5 kg', '10 kg', '1 Packet', '1 Bottle', '1 Piece']; selectedSizes = ['500 g', '1 kg']; generateFashionMatrix();" class="text-[11px] px-2.5 py-1 rounded-lg bg-emerald-50 hover:bg-emerald-100 text-emerald-800 font-bold transition">🛒 Kirana Packs</button>
                    <button type="button" @click="availableSizesList = ['4GB/64GB', '6GB/128GB', '8GB/128GB', '8GB/256GB', '12GB/256GB', '16GB/512GB', '16GB/1TB']; selectedSizes = ['8GB/128GB', '8GB/256GB']; generateFashionMatrix();" class="text-[11px] px-2.5 py-1 rounded-lg bg-cyan-50 hover:bg-cyan-100 text-cyan-800 font-bold transition">📱 Electronics RAM/Storage</button>
                </div>

                <div class="flex flex-wrap gap-2 mb-3">
                    <template x-for="sz in availableSizesList" :key="sz">
                        <button type="button" 
                                @click="toggleSize(sz)"
                                :class="selectedSizes.includes(sz) 
                                    ? 'bg-brand-sage text-white font-bold ring-2 ring-emerald-600/30 shadow-xs' 
                                    : 'bg-white hover:bg-stone-100 text-stone-700 border border-stone-200'"
                                class="px-3.5 py-1.5 rounded-xl text-xs transition-all cursor-pointer flex items-center gap-1.5">
                            <span x-text="sz"></span>
                            <span x-show="selectedSizes.includes(sz)" class="text-[10px] font-black">✓</span>
                        </button>
                    </template>
                </div>

                <!-- Custom Size Inserter -->
                <div class="flex items-center gap-2 pt-2 border-t border-stone-200/50">
                    <input type="text" x-model="customSizeName" placeholder="Custom size (e.g. 2-4Y or 42 Regular)" class="text-xs px-3 py-1.5 rounded-lg border-stone-300 w-48 bg-white">
                    <button type="button" @click="addCustomSize()" class="px-3 py-1.5 rounded-lg bg-stone-800 hover:bg-black text-white text-xs font-bold cursor-pointer transition">
                        + Add Size Tag
                    </button>
                </div>
            </div>

            <!-- Cartesian Matrix Combinations Table -->
            <div x-show="fashionMatrixRows.length > 0" class="pt-4 border-t border-stone-100">
                <!-- Bulk Apply Bar -->
                <div class="bg-amber-50/80 border border-amber-200/70 rounded-xl p-3 sm:p-4 mb-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <div class="flex items-center gap-2">
                        <span class="text-sm">⚡</span>
                        <div>
                            <span class="text-xs font-bold uppercase tracking-wider text-amber-950">Bulk Apply All Rows:</span>
                            <span class="text-[11px] text-amber-800 block sm:inline">Set default price &amp; inventory across entire matrix in 1-click</span>
                        </div>
                    </div>
                    <div class="flex items-center gap-2 flex-wrap">
                        <input type="number" step="0.01" x-model="bulkPrice" placeholder="Price (₹)" class="w-28 text-xs rounded-lg border-amber-300 bg-white py-1.5 px-2.5">
                        <input type="number" x-model="bulkStock" placeholder="Stock Qty" class="w-24 text-xs rounded-lg border-amber-300 bg-white py-1.5 px-2.5">
                        <button type="button" @click="applyFashionBulk()" class="text-xs font-bold bg-amber-950 text-white px-3.5 py-1.5 rounded-lg hover:bg-black shadow-xs cursor-pointer transition">
                            Apply to All Rows
                        </button>
                    </div>
                </div>

                <div class="overflow-x-auto rounded-xl border border-stone-200 shadow-sm">
                    <table class="min-w-full divide-y divide-stone-200 text-xs">
                        <thead class="bg-stone-50">
                            <tr>
                                <th scope="col" class="px-3 py-2.5 text-left font-bold text-stone-600 uppercase">Color</th>
                                <th scope="col" class="px-3 py-2.5 text-left font-bold text-stone-600 uppercase">Size</th>
                                <th scope="col" class="px-3 py-2.5 text-left font-bold text-stone-600 uppercase">Child SKU</th>
                                <th scope="col" class="px-3 py-2.5 text-left font-bold text-stone-600 uppercase">Price (₹)</th>
                                <th scope="col" class="px-3 py-2.5 text-left font-bold text-stone-600 uppercase">Stock Qty</th>
                                <th scope="col" class="px-3 py-2.5 text-left font-bold text-stone-600 uppercase">Variant Image URL</th>
                                <th scope="col" class="px-3 py-2.5 text-right font-bold text-stone-600 uppercase">Action</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-stone-100">
                            <template x-for="(row, idx) in fashionMatrixRows" :key="row.sku">
                                <tr class="hover:bg-stone-50/70 transition-colors">
                                    <td class="px-3 py-2.5 whitespace-nowrap font-medium text-stone-900">
                                        <div class="flex items-center gap-2">
                                            <span class="w-3.5 h-3.5 rounded-full border border-black/20 shrink-0 shadow-inner" :style="`background-color: ${row.color_hex}`"></span>
                                            <span x-text="row.color"></span>
                                            <input type="hidden" :name="`variants[${idx}][color]`" :value="row.color">
                                            <input type="hidden" :name="`variants[${idx}][color_hex]`" :value="row.color_hex">
                                        </div>
                                    </td>
                                    <td class="px-3 py-2.5 whitespace-nowrap">
                                        <span class="px-2 py-0.5 rounded-md bg-stone-100 font-bold text-stone-800" x-text="row.size"></span>
                                        <input type="hidden" :name="`variants[${idx}][size]`" :value="row.size">
                                        <input type="hidden" :name="`variants[${idx}][name]`" :value="`${row.color} / ${row.size}`">
                                    </td>
                                    <td class="px-3 py-2.5 whitespace-nowrap">
                                        <input type="text" :name="`variants[${idx}][sku]`" x-model="row.sku" class="w-28 text-xs font-mono rounded-lg border-stone-200 py-1 px-2">
                                    </td>
                                    <td class="px-3 py-2.5 whitespace-nowrap">
                                        <input type="number" step="0.01" min="0" :name="`variants[${idx}][price]`" x-model="row.price" :placeholder="basePrice || 'Base'" class="w-24 text-xs font-bold rounded-lg border-stone-200 py-1 px-2">
                                    </td>
                                    <td class="px-3 py-2.5 whitespace-nowrap">
                                        <input type="number" min="0" :name="`variants[${idx}][stock]`" x-model="row.stock" class="w-20 text-xs font-bold rounded-lg border-stone-200 py-1 px-2">
                                    </td>
                                    <td class="px-3 py-2.5 whitespace-nowrap">
                                        <input type="text" :name="`variants[${idx}][image_url]`" x-model="row.image_url" placeholder="Optional Image URL" class="w-36 text-xs rounded-lg border-stone-200 py-1 px-2">
                                    </td>
                                    <td class="px-3 py-2.5 text-right whitespace-nowrap">
                                        <button type="button" @click="removeFashionRow(idx)" class="text-rose-600 hover:text-rose-800 text-xs font-bold cursor-pointer">✕</button>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- 2. FOOD & CLOUD KITCHEN (food_portion)                                     -->
    <!-- ========================================================================= -->
    <div x-show="verticalType === 'food_portion'" class="space-y-6">
        <div class="bg-white rounded-2xl shadow-sm border border-stone-200/80 p-5 sm:p-7">
            <div class="flex items-center justify-between pb-4 border-b border-stone-100 mb-5">
                <div>
                    <h3 class="text-sm font-bold text-stone-900 tracking-tight flex items-center gap-2">
                        <span>Portion Pricing &amp; Add-on Modifiers</span>
                        <span class="text-[10px] bg-amber-50 text-amber-800 font-bold px-2 py-0.5 rounded-md border border-amber-200">Swiggy / Zomato Pattern</span>
                    </h3>
                    <p class="text-xs text-stone-500 mt-0.5">Configure portion pricing tiers and nested customize add-ons with real-time price increments.</p>
                </div>
            </div>

            <!-- Portion Pricing Table -->
            <div class="mb-6">
                <div class="flex items-center justify-between mb-3">
                    <label class="text-xs font-bold uppercase tracking-wider text-stone-800 flex items-center gap-1.5">
                        <span>🍛 1. Mutually Exclusive Portion Pricing</span>
                    </label>
                    <button type="button" @click="addFoodPortion()" class="text-xs font-bold text-amber-700 hover:text-amber-900 cursor-pointer">+ Add Another Portion</button>
                </div>

                <div class="overflow-x-auto rounded-xl border border-stone-200 shadow-xs mb-3">
                    <table class="min-w-full divide-y divide-stone-200 text-xs">
                        <thead class="bg-stone-50">
                            <tr>
                                <th class="px-3 py-2.5 text-left font-bold text-stone-600">Portion Name (e.g. 1 Plate, 2 Plates, Half, Full)</th>
                                <th class="px-3 py-2.5 text-left font-bold text-stone-600">Base Price (₹)</th>
                                <th class="px-3 py-2.5 text-left font-bold text-stone-600">Portion Stock Qty</th>
                                <th class="px-3 py-2.5 text-right font-bold text-stone-600">Action</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-stone-100">
                            <template x-for="(p, pIdx) in foodPortions" :key="pIdx">
                                <tr>
                                    <td class="px-3 py-2.5">
                                        <input type="text" :name="`food_portions[${pIdx}][name]`" x-model="p.name" placeholder="e.g. 1 Plate (Standard)" class="w-full text-xs font-semibold rounded-lg border-stone-200 py-1.5 px-2.5">
                                    </td>
                                    <td class="px-3 py-2.5">
                                        <input type="number" step="0.01" min="0" :name="`food_portions[${pIdx}][price]`" x-model="p.price" placeholder="90.00" class="w-32 text-xs font-bold rounded-lg border-stone-200 py-1.5 px-2.5">
                                    </td>
                                    <td class="px-3 py-2.5">
                                        <input type="number" min="0" :name="`food_portions[${pIdx}][stock]`" x-model="p.stock" placeholder="50" class="w-24 text-xs font-bold rounded-lg border-stone-200 py-1.5 px-2.5">
                                    </td>
                                    <td class="px-3 py-2.5 text-right">
                                        <button type="button" @click="removeFoodPortion(pIdx)" class="text-rose-600 hover:text-rose-800 text-xs font-bold cursor-pointer" :disabled="foodPortions.length <= 1">✕</button>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Add-on Modifiers Repeater -->
            <div class="pt-4 border-t border-stone-100">
                <div class="flex items-center justify-between mb-3">
                    <label class="text-xs font-bold uppercase tracking-wider text-stone-800 flex items-center gap-1.5">
                        <span>➕ 2. Optional Add-ons &amp; Customization Modifiers</span>
                    </label>
                    <button type="button" @click="addFoodModifier()" class="text-xs font-bold text-amber-700 hover:text-amber-900 cursor-pointer">+ Add Add-on Item</button>
                </div>

                <div class="space-y-2.5">
                    <template x-for="(m, mIdx) in foodModifiers" :key="mIdx">
                        <div class="flex flex-col sm:flex-row sm:items-center gap-2.5 p-3 rounded-xl bg-stone-50 border border-stone-200/70">
                            <input type="text" :name="`modifiers[${mIdx}][group_name]`" x-model="m.group_name" placeholder="Group (e.g. Add-ons / Upgrades)" class="w-full sm:w-44 text-xs font-medium rounded-lg border-stone-200 py-1.5 px-2.5 bg-white">
                            <input type="text" :name="`modifiers[${mIdx}][modifier_name]`" x-model="m.modifier_name" placeholder="Add-on Name (e.g. Extra Bhatura)" class="flex-1 text-xs font-semibold rounded-lg border-stone-200 py-1.5 px-2.5 bg-white">
                            <div class="flex items-center gap-1.5">
                                <span class="text-xs font-bold text-stone-500">+₹</span>
                                <input type="number" step="0.01" min="0" :name="`modifiers[${mIdx}][price_delta]`" x-model="m.price_delta" placeholder="35.00" class="w-24 text-xs font-bold rounded-lg border-stone-200 py-1.5 px-2 bg-white">
                            </div>
                            <label class="inline-flex items-center gap-1.5 text-xs text-stone-700 cursor-pointer shrink-0">
                                <input type="checkbox" :name="`modifiers[${mIdx}][is_required]`" value="1" x-model="m.is_required" class="rounded text-amber-600 focus:ring-amber-500">
                                <span>Required</span>
                            </label>
                            <button type="button" @click="removeFoodModifier(mIdx)" class="text-rose-600 hover:text-rose-800 text-xs font-bold px-2 py-1 cursor-pointer">✕</button>
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- 3. DIGITAL GIGS & FREELANCE PACKAGES (digital_gig)                        -->
    <!-- ========================================================================= -->
    <div x-show="verticalType === 'digital_gig'" class="space-y-6">
        <div class="bg-white rounded-2xl shadow-sm border border-stone-200/80 p-5 sm:p-7">
            <div class="flex items-center justify-between pb-4 border-b border-stone-100 mb-5">
                <div>
                    <h3 class="text-sm font-bold text-stone-900 tracking-tight flex items-center gap-2">
                        <span>3-Tier Comparative Package Builder</span>
                        <span class="text-[10px] bg-indigo-50 text-indigo-700 font-bold px-2 py-0.5 rounded-md border border-indigo-200">Fiverr Model</span>
                    </h3>
                    <p class="text-xs text-stone-500 mt-0.5">Offer 3 distinct milestone packages (Basic, Standard, Premium) with feature deliverables and turnaround timers.</p>
                </div>
            </div>

            <!-- 3-Column Package Cards Grid -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <template x-for="tierKey in ['basic', 'standard', 'premium']" :key="tierKey">
                    <div class="rounded-2xl border-2 p-4 flex flex-col justify-between transition-all"
                         :class="tierKey === 'standard' ? 'border-indigo-500 bg-indigo-50/20 shadow-md ring-2 ring-indigo-500/20' : 'border-stone-200 bg-white'">
                        
                        <div>
                            <!-- Tier Header Badge -->
                            <div class="flex items-center justify-between mb-3">
                                <span class="text-xs font-black uppercase tracking-wider px-2.5 py-1 rounded-lg"
                                      :class="tierKey === 'basic' ? 'bg-stone-100 text-stone-800' : (tierKey === 'standard' ? 'bg-indigo-600 text-white' : 'bg-amber-500 text-white')"
                                      x-text="tierKey.toUpperCase()">
                                </span>
                                <span x-show="tierKey === 'standard'" class="text-[10px] font-bold text-indigo-600 bg-indigo-100 px-2 py-0.5 rounded-full">Most Popular</span>
                            </div>

                            <input type="hidden" :name="`gig_packages[${tierKey}][tier]`" :value="tierKey">

                            <!-- Title & Description -->
                            <div class="space-y-2 mb-3">
                                <div>
                                    <label class="block text-[11px] font-bold text-stone-600 uppercase mb-0.5">Package Title</label>
                                    <input type="text" :name="`gig_packages[${tierKey}][title]`" x-model="gigPackages[tierKey].title" placeholder="e.g. Starter Concept" class="w-full text-xs font-bold rounded-lg border-stone-200 py-1.5 px-2.5 bg-white">
                                </div>
                                <div>
                                    <label class="block text-[11px] font-bold text-stone-600 uppercase mb-0.5">Scope Description</label>
                                    <textarea :name="`gig_packages[${tierKey}][description]`" x-model="gigPackages[tierKey].description" rows="2" placeholder="Describe inclusions..." class="w-full text-xs rounded-lg border-stone-200 py-1.5 px-2.5 bg-white"></textarea>
                                </div>
                            </div>

                            <!-- Delivery Days, Revisions, Price -->
                            <div class="grid grid-cols-2 gap-2 mb-3">
                                <div>
                                    <label class="block text-[10px] font-bold text-stone-600 uppercase mb-0.5">Delivery (Days)</label>
                                    <input type="number" min="1" :name="`gig_packages[${tierKey}][delivery_days]`" x-model="gigPackages[tierKey].delivery_days" class="w-full text-xs font-bold rounded-lg border-stone-200 py-1 px-2 bg-white">
                                </div>
                                <div>
                                    <label class="block text-[10px] font-bold text-stone-600 uppercase mb-0.5">Revisions</label>
                                    <input type="number" min="0" :name="`gig_packages[${tierKey}][revisions]`" x-model="gigPackages[tierKey].revisions" class="w-full text-xs font-bold rounded-lg border-stone-200 py-1 px-2 bg-white">
                                </div>
                            </div>

                            <!-- Deliverable Checkboxes -->
                            <div class="pt-2 border-t border-stone-100 space-y-1.5 text-xs text-stone-700 mb-3">
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="checkbox" :name="`gig_packages[${tierKey}][features][source_file]`" value="1" x-model="gigPackages[tierKey].features.source_file" class="rounded text-indigo-600">
                                    <span>Source File Included</span>
                                </label>
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="checkbox" :name="`gig_packages[${tierKey}][features][commercial_rights]`" value="1" x-model="gigPackages[tierKey].features.commercial_rights" class="rounded text-indigo-600">
                                    <span>Commercial Rights</span>
                                </label>
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="checkbox" :name="`gig_packages[${tierKey}][features][high_resolution]`" value="1" x-model="gigPackages[tierKey].features.high_resolution" class="rounded text-indigo-600">
                                    <span>High-Res (300 DPI)</span>
                                </label>
                            </div>
                        </div>

                        <!-- Price Field -->
                        <div class="pt-3 border-t border-stone-100">
                            <label class="block text-[10px] font-bold text-stone-600 uppercase mb-0.5">Tier Price (₹)</label>
                            <div class="relative">
                                <span class="absolute left-2.5 top-1.5 text-xs font-bold text-stone-500">₹</span>
                                <input type="number" step="0.01" min="0" :name="`gig_packages[${tierKey}][price]`" x-model="gigPackages[tierKey].price" class="w-full pl-6 pr-3 py-1.5 text-sm font-extrabold rounded-lg border-stone-300 bg-white">
                            </div>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- 4. MACHINERY & B2B RFQ BUILDER (b2b_machinery)                             -->
    <!-- ========================================================================= -->
    <div x-show="verticalType === 'b2b_machinery'" class="space-y-6">
        <div class="bg-white rounded-2xl shadow-sm border border-stone-200/80 p-5 sm:p-7">
            <div class="flex items-center justify-between pb-4 border-b border-stone-100 mb-5">
                <div>
                    <h3 class="text-sm font-bold text-stone-900 tracking-tight flex items-center gap-2">
                        <span>Tiered MOQ Volume Pricing &amp; Technical Specs</span>
                        <span class="text-[10px] bg-blue-50 text-blue-700 font-bold px-2 py-0.5 rounded-md border border-blue-200">IndiaMART / Alibaba Pattern</span>
                    </h3>
                    <p class="text-xs text-stone-500 mt-0.5">Configure tiered volume discount slabs and key technical machine parameters for enterprise buyers.</p>
                </div>
            </div>

            <!-- RFQ Toggle Switch -->
            <div class="mb-6 p-4 rounded-xl bg-blue-50/70 border border-blue-200/80 flex items-center justify-between">
                <div>
                    <span class="text-xs font-bold text-blue-950 uppercase tracking-wide block">Direct B2B RFQ (Request for Quote) Inquiry</span>
                    <span class="text-[11px] text-blue-800">Display prominent "Request Custom RFQ" button with modal messaging on the product page.</span>
                </div>
                <label class="relative inline-flex items-center cursor-pointer">
                    <input type="checkbox" name="enable_rfq" value="1" x-model="enableRfq" class="sr-only peer">
                    <div class="w-11 h-6 bg-stone-300 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-stone-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                </label>
            </div>

            <!-- Key-Value Technical Specification Inputs -->
            <div class="mb-6">
                <label class="text-xs font-bold uppercase tracking-wider text-stone-800 block mb-3">
                    ⚙️ Technical Machine Specifications
                </label>
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3">
                    <div>
                        <label class="block text-[11px] font-semibold text-stone-600 mb-1">Motor Power (HP / kW)</label>
                        <input type="text" name="specifications[motor_hp]" x-model="specs.motor_hp" placeholder="e.g. 5 HP / 3.7 kW" class="w-full text-xs rounded-lg border-stone-200 py-1.5 px-2.5">
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold text-stone-600 mb-1">Cutting / Working Thickness (mm)</label>
                        <input type="text" name="specifications[cutting_thickness]" x-model="specs.cutting_thickness" placeholder="e.g. 20 - 50 mm" class="w-full text-xs rounded-lg border-stone-200 py-1.5 px-2.5">
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold text-stone-600 mb-1">Operating Voltage</label>
                        <select name="specifications[voltage]" x-model="specs.voltage" class="w-full text-xs rounded-lg border-stone-200 py-1.5 px-2.5">
                            <option value="415V Three Phase">415V Three Phase (Industrial)</option>
                            <option value="220V Single Phase">220V Single Phase</option>
                            <option value="Dual Voltage 220V/440V">Dual Voltage 220V/440V</option>
                            <option value="Solar DC">Solar DC</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold text-stone-600 mb-1">Speed / RPM</label>
                        <input type="text" name="specifications[rpm]" x-model="specs.rpm" placeholder="e.g. 1440 RPM" class="w-full text-xs rounded-lg border-stone-200 py-1.5 px-2.5">
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold text-stone-600 mb-1">Automation Grade</label>
                        <select name="specifications[automation_grade]" x-model="specs.automation_grade" class="w-full text-xs rounded-lg border-stone-200 py-1.5 px-2.5">
                            <option value="Automatic / CNC">Automatic / CNC</option>
                            <option value="Semi-Automatic">Semi-Automatic</option>
                            <option value="Manual Heavy Duty">Manual Heavy Duty</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold text-stone-600 mb-1">Net Weight (KG)</label>
                        <input type="text" name="specifications[weight_kg]" x-model="specs.weight_kg" placeholder="e.g. 450 KG" class="w-full text-xs rounded-lg border-stone-200 py-1.5 px-2.5">
                    </div>
                </div>
            </div>

            <!-- Tiered Volume/MOQ Pricing Builder -->
            <div class="pt-4 border-t border-stone-100">
                <div class="flex items-center justify-between mb-3">
                    <label class="text-xs font-bold uppercase tracking-wider text-stone-800 flex items-center gap-1.5">
                        <span>📦 Tiered Volume Price Slabs (MOQ)</span>
                    </label>
                    <button type="button" @click="addB2bTier()" class="text-xs font-bold text-blue-700 hover:text-blue-900 cursor-pointer">+ Add Volume Tier</button>
                </div>

                <div class="space-y-2.5">
                    <template x-for="(tier, tIdx) in b2bTiers" :key="tIdx">
                        <div class="flex items-center gap-3 p-3 rounded-xl bg-stone-50 border border-stone-200/70">
                            <span class="text-xs font-bold text-stone-500" x-text="`Tier #${tIdx + 1}`"></span>
                            <div class="flex items-center gap-1.5">
                                <span class="text-xs text-stone-500">Min:</span>
                                <input type="number" min="1" :name="`b2b_tiers[${tIdx}][min_qty]`" x-model="tier.min_qty" placeholder="1" class="w-20 text-xs font-bold rounded-lg border-stone-200 py-1.5 px-2 bg-white">
                            </div>
                            <div class="flex items-center gap-1.5">
                                <span class="text-xs text-stone-500">Max:</span>
                                <input type="number" min="1" :name="`b2b_tiers[${tIdx}][max_qty]`" x-model="tier.max_qty" placeholder="5" class="w-20 text-xs font-bold rounded-lg border-stone-200 py-1.5 px-2 bg-white">
                            </div>
                            <div class="flex items-center gap-1.5 ml-auto">
                                <span class="text-xs font-bold text-stone-500">Unit Price: ₹</span>
                                <input type="number" step="0.01" min="0" :name="`b2b_tiers[${tIdx}][unit_price]`" x-model="tier.unit_price" placeholder="150000" class="w-32 text-xs font-extrabold rounded-lg border-stone-200 py-1.5 px-2 bg-white">
                            </div>
                            <button type="button" @click="removeB2bTier(tIdx)" class="text-rose-600 hover:text-rose-800 text-xs font-bold px-2 py-1 cursor-pointer" :disabled="b2bTiers.length <= 1">✕</button>
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- 5. ON-DEMAND SERVICES (service_booking)                                    -->
    <!-- ========================================================================= -->
    <div x-show="verticalType === 'service_booking'" class="space-y-6">
        <div class="bg-white rounded-2xl shadow-sm border border-stone-200/80 p-5 sm:p-7">
            <div class="flex items-center justify-between pb-4 border-b border-stone-100 mb-5">
                <div>
                    <h3 class="text-sm font-bold text-stone-900 tracking-tight flex items-center gap-2">
                        <span>Service Scope &amp; Slot Scheduling</span>
                        <span class="text-[10px] bg-teal-50 text-teal-700 font-bold px-2 py-0.5 rounded-md border border-teal-200">Urban Company Pattern</span>
                    </h3>
                    <p class="text-xs text-stone-500 mt-0.5">Specify service deliverables, transparent exclusions, duration, and daily customer slot capacity.</p>
                </div>
            </div>

            <!-- Duration & Capacity -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-stone-700 mb-1">Estimated Service Duration</label>
                    <select name="service_config[duration_minutes]" x-model="serviceConfig.duration_minutes" class="w-full text-xs font-semibold rounded-xl border-stone-200 py-2 px-3">
                        <option value="30">30 Minutes Quick Visit</option>
                        <option value="60">60 Minutes (1 Hour)</option>
                        <option value="90">90 Minutes Standard</option>
                        <option value="120">2 Hours Comprehensive</option>
                        <option value="240">Half Day (4 Hours)</option>
                        <option value="480">Full Day Project (8 Hours)</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-stone-700 mb-1">Daily Booking Slot Capacity</label>
                    <input type="number" min="1" max="50" name="service_config[daily_slot_capacity]" x-model="serviceConfig.daily_slot_capacity" placeholder="e.g. 5 appointments/day" class="w-full text-xs font-bold rounded-xl border-stone-200 py-2 px-3">
                </div>
            </div>

            <!-- Inclusions & Exclusions Scope Builder -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-4 border-t border-stone-100">
                <!-- Inclusions -->
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <label class="text-xs font-bold text-emerald-800 uppercase flex items-center gap-1">
                            <span>✓ Inclusions (Scope of Work)</span>
                        </label>
                        <button type="button" @click="addInclusion()" class="text-xs text-emerald-700 font-bold cursor-pointer">+ Add</button>
                    </div>
                    <div class="space-y-1.5">
                        <template x-for="(inc, iIdx) in serviceConfig.inclusions" :key="iIdx">
                            <div class="flex items-center gap-1.5">
                                <input type="text" :name="`service_config[inclusions][${iIdx}]`" x-model="serviceConfig.inclusions[iIdx]" placeholder="e.g. Chemical deep scrub included" class="w-full text-xs rounded-lg border-stone-200 py-1 px-2.5 bg-emerald-50/30">
                                <button type="button" @click="removeInclusion(iIdx)" class="text-rose-600 font-bold px-1.5 cursor-pointer">✕</button>
                            </div>
                        </template>
                    </div>
                </div>

                <!-- Exclusions -->
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <label class="text-xs font-bold text-rose-800 uppercase flex items-center gap-1">
                            <span>✕ Exclusions (Not Included)</span>
                        </label>
                        <button type="button" @click="addExclusion()" class="text-xs text-rose-700 font-bold cursor-pointer">+ Add</button>
                    </div>
                    <div class="space-y-1.5">
                        <template x-for="(exc, eIdx) in serviceConfig.exclusions" :key="eIdx">
                            <div class="flex items-center gap-1.5">
                                <input type="text" :name="`service_config[exclusions][${eIdx}]`" x-model="serviceConfig.exclusions[eIdx]" placeholder="e.g. Replacement spare parts charged at actuals" class="w-full text-xs rounded-lg border-stone-200 py-1 px-2.5 bg-rose-50/30">
                                <button type="button" @click="removeExclusion(eIdx)" class="text-rose-600 font-bold px-1.5 cursor-pointer">✕</button>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- 6. PRE-OWNED & CLASSIFIEDS (classified_asset)                             -->
    <!-- ========================================================================= -->
    <div x-show="verticalType === 'classified_asset'" class="space-y-6">
        <div class="bg-white rounded-2xl shadow-sm border border-stone-200/80 p-5 sm:p-7">
            <div class="flex items-center justify-between pb-4 border-b border-stone-100 mb-5">
                <div>
                    <h3 class="text-sm font-bold text-stone-900 tracking-tight flex items-center gap-2">
                        <span>Pre-Owned Inspection Checklist &amp; Counter-Offers</span>
                        <span class="text-[10px] bg-cyan-50 text-cyan-800 font-bold px-2 py-0.5 rounded-md border border-cyan-200">OLX Pattern</span>
                    </h3>
                    <p class="text-xs text-stone-500 mt-0.5">Strict single-inventory disclosure with buyer negotiation counter-offer functionality.</p>
                </div>
            </div>

            <!-- Single Stock Locked Alert & Offer Toggle -->
            <div class="mb-5 p-4 rounded-xl bg-cyan-50/60 border border-cyan-200/70 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div class="flex items-center gap-2.5">
                    <span class="text-lg">🔒</span>
                    <div>
                        <span class="text-xs font-bold text-cyan-950 uppercase tracking-wide">Unique Single Stock Unit</span>
                        <span class="text-[11px] text-cyan-800 block">Inventory is automatically locked to 1 piece for classified assets.</span>
                    </div>
                </div>
                <label class="inline-flex items-center gap-2 cursor-pointer bg-white px-3 py-1.5 rounded-xl border border-cyan-300 shadow-2xs">
                    <input type="checkbox" name="is_negotiable" value="1" x-model="isNegotiable" class="rounded text-cyan-600 focus:ring-cyan-500">
                    <span class="text-xs font-bold text-stone-800">Enable "Make an Offer / Bid"</span>
                </label>
            </div>

            <!-- Pre-owned Specifications Checklist -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-stone-700 mb-1">Condition Rating *</label>
                    <select name="specifications[condition]" x-model="specs.condition" class="w-full text-xs font-semibold rounded-xl border-stone-200 py-2 px-3">
                        <option value="Brand New (Sealed Box / Unused)">Brand New (Sealed Box / Unused)</option>
                        <option value="Like New (Mint 10/10 Condition)">Like New (Mint 10/10 Condition)</option>
                        <option value="Gently Used (Minor Signs of Wear)">Gently Used (Minor Signs of Wear)</option>
                        <option value="Used (Fully Functional)">Used (Fully Functional)</option>
                        <option value="For Parts / Needs Repair">For Parts / Needs Repair</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-stone-700 mb-1">Year of Purchase / Manufacturing</label>
                    <input type="number" name="specifications[purchase_year]" x-model="specs.purchase_year" placeholder="e.g. 2023" class="w-full text-xs font-semibold rounded-xl border-stone-200 py-2 px-3">
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-stone-700 mb-1">Usage (KM Driven / Battery Cycles / Hours)</label>
                    <input type="text" name="specifications[usage]" x-model="specs.usage" placeholder="e.g. 14,500 KM or 88% Battery" class="w-full text-xs font-semibold rounded-xl border-stone-200 py-2 px-3">
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-stone-700 mb-1">Warranty Status</label>
                    <select name="specifications[warranty]" x-model="specs.warranty" class="w-full text-xs font-semibold rounded-xl border-stone-200 py-2 px-3">
                        <option value="Active Brand Warranty">Active Brand Warranty</option>
                        <option value="Seller 7-Day Testing Warranty">Seller 7-Day Testing Warranty</option>
                        <option value="Out of Warranty">Out of Warranty</option>
                    </select>
                </div>
            </div>
        </div>
    </div>
</div>
