{{-- Fashion / Apparel Matrix Partial (Amazon / ASOS Pattern) --}}
@php
    $distinctColors = collect();
    $distinctSizes = collect();

    foreach ($product->variants as $v) {
        $color = $v->attributes_hash['color'] ?? null;
        $colorHex = $v->attributes_hash['color_hex'] ?? '#111827';
        if ($color && !$distinctColors->contains('name', $color)) {
            $distinctColors->push([
                'name' => $color,
                'hex' => $colorHex,
                'image_url' => $v->image_url ?? null
            ]);
        }
        $size = $v->attributes_hash['size'] ?? ($v->sku ?: 'Standard');
        if ($size && !$distinctSizes->contains($size)) {
            $distinctSizes->push($size);
        }
    }

    $firstInStock = $product->variants->firstWhere('stock', '>', 0);
    $initialColor = $distinctColors->first()['name'] ?? null;
    $initialSize = $firstInStock ? ($firstInStock->attributes_hash['size'] ?? $firstInStock->sku) : ($distinctSizes->first() ?? null);
@endphp

<div class="space-y-5" x-data="fashionVariantSelector()">
    <!-- 1. Color Swatches Selection -->
    @if($distinctColors->isNotEmpty())
        <div>
            <div class="flex items-center justify-between mb-2">
                <div class="flex items-center gap-1.5">
                    <span class="text-xs font-bold uppercase tracking-wider text-stone-800">Color:</span>
                    <span class="text-xs font-extrabold text-stone-900" x-text="selectedColor"></span>
                </div>
                <span class="text-[11px] text-stone-500 font-medium" x-text="selectedColorVariantCount + ' sizes available'"></span>
            </div>

            <div class="flex flex-wrap gap-2.5">
                <template x-for="c in colors" :key="c.name">
                    <button type="button" 
                            @click="selectColor(c)"
                            :title="c.name"
                            :class="selectedColor === c.name 
                                ? 'ring-2 ring-black ring-offset-2 scale-105 shadow-sm' 
                                : 'hover:scale-105 border-stone-200 opacity-90 hover:opacity-100'"
                            class="relative w-8 h-8 rounded-full border border-black/15 transition-all cursor-pointer flex items-center justify-center">
                        <span class="w-full h-full rounded-full shadow-inner" :style="`background-color: ${c.hex}`"></span>
                        <span x-show="selectedColor === c.name" class="absolute inset-0 flex items-center justify-center text-[10px] font-black"
                              :class="isColorDark(c.hex) ? 'text-white' : 'text-stone-900'">✓</span>
                    </button>
                </template>
            </div>
        </div>
    @endif

    <!-- 2. Size Selector with Stock Scarcity & Fit Guide -->
    <div x-ref="sizeSelector">
        <div class="flex items-center justify-between mb-2.5">
            <div class="flex items-center gap-1.5">
                <h3 class="text-xs font-bold uppercase tracking-wider text-stone-800">{{ $product->isClothing() ? 'Select Size' : 'Select Option' }}</h3>
                <span class="text-[11px] text-stone-400 font-normal">(Required)</span>
            </div>
            @if($product->isClothing() && !($product->size_chart && isset($product->size_chart['type']) && $product->size_chart['type'] === 'none'))
                <button type="button" @click="$dispatch('open-size-guide')" class="text-xs text-stone-800 hover:text-brand-sage flex items-center gap-1 font-bold transition-colors">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2zM7 7v4M11 7v2M15 7v4M19 7v2"/>
                    </svg>
                    <span class="underline decoration-1 underline-offset-2">Size Chart &amp; Fit</span>
                </button>
            @endif
        </div>

        <div class="flex flex-wrap gap-2">
            <template x-for="v in filteredVariants" :key="v.id">
                <button type="button"
                        @click="selectVariant(v)"
                        :disabled="v.stock <= 0"
                        :class="activeVariant && activeVariant.id === v.id
                            ? 'border-stone-900 bg-stone-900 text-white shadow-sm ring-1 ring-stone-900'
                            : (v.stock <= 0 
                                ? 'opacity-40 cursor-not-allowed border-dashed border-stone-200 bg-stone-100 text-stone-400 line-through' 
                                : 'border-stone-200 bg-white hover:border-stone-900 text-stone-800')"
                        class="min-w-[54px] px-3.5 py-2 rounded-xl text-xs font-bold text-center border transition-all cursor-pointer relative">
                    <span x-text="v.size || v.sku"></span>
                    <template x-if="v.stock > 0 && v.stock <= 3">
                        <span class="block text-[8px] font-black uppercase text-amber-500 leading-none mt-0.5" x-text="`${v.stock} left`"></span>
                    </template>
                    <template x-if="v.stock <= 0">
                        <span class="block text-[8px] font-bold text-rose-500 leading-none mt-0.5 no-underline">Sold</span>
                    </template>
                </button>
            </template>
        </div>
    </div>

    <!-- 3. Dynamic Price & SKU Feedback Strip -->
    <template x-if="activeVariant">
        <div class="flex items-center justify-between px-3 py-2 rounded-xl bg-stone-50 border border-stone-200/60 text-xs">
            <div class="flex items-center gap-1.5 font-mono text-[11px] text-stone-500">
                <span>SKU:</span>
                <span class="font-bold text-stone-800" x-text="activeVariant.sku"></span>
            </div>
            <div class="flex items-center gap-1.5 font-semibold text-emerald-700">
                <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                <span x-text="activeVariant.stock > 0 ? `${activeVariant.stock} In Stock` : 'Out of Stock'"></span>
            </div>
        </div>
    </template>
</div>

<script>
    function fashionVariantSelector() {
        return {
            colors: @json($distinctColors),
            selectedColor: @json($initialColor),
            activeVariant: null,
            allVariants: [],

            init() {
                const rawVariants = document.getElementById('product-variants-data');
                if (rawVariants) {
                    try {
                        this.allVariants = JSON.parse(rawVariants.textContent);
                    } catch (e) {
                        this.allVariants = [];
                    }
                }

                // Auto-select first in stock variant
                const first = this.allVariants.find(v => v.stock > 0) || this.allVariants[0];
                if (first) {
                    this.selectVariant(first);
                    if (first.attributes_hash && first.attributes_hash.color) {
                        this.selectedColor = first.attributes_hash.color;
                    }
                }
            },

            get filteredVariants() {
                if (!this.selectedColor || this.colors.length === 0) {
                    return this.allVariants;
                }
                const matches = this.allVariants.filter(v => 
                    (v.attributes_hash && v.attributes_hash.color === this.selectedColor) ||
                    (v.color && v.color === this.selectedColor)
                );
                return matches.length > 0 ? matches : this.allVariants;
            },

            get selectedColorVariantCount() {
                return this.filteredVariants.length;
            },

            selectColor(c) {
                this.selectedColor = c.name;
                const matches = this.filteredVariants;
                // If current variant is not in new color, pick first in-stock variant of this color
                if (!matches.some(v => this.activeVariant && v.id === this.activeVariant.id)) {
                    const next = matches.find(v => v.stock > 0) || matches[0];
                    if (next) this.selectVariant(next);
                }

                // Zero-reload hot-swap image if color has specific image
                if (c.image_url) {
                    window.dispatchEvent(new CustomEvent('swap-product-image', { detail: { url: c.image_url } }));
                }
            },

            selectVariant(v) {
                this.activeVariant = v;
                // Synchronize parent public show Alpine component
                window.dispatchEvent(new CustomEvent('variant-selected', { detail: v }));
                if (v.image_url) {
                    window.dispatchEvent(new CustomEvent('swap-product-image', { detail: { url: v.image_url } }));
                }
            },

            isColorDark(hex) {
                if (!hex || hex[0] !== '#') return false;
                const r = parseInt(hex.substr(1, 2), 16) || 0;
                const g = parseInt(hex.substr(3, 2), 16) || 0;
                const b = parseInt(hex.substr(5, 2), 16) || 0;
                const luminance = (0.299 * r + 0.587 * g + 0.114 * b) / 255;
                return luminance < 0.5;
            }
        };
    }
</script>
