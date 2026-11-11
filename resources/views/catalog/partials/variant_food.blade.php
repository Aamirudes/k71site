{{-- Food & Cloud Kitchen Portion & Modifiers Partial (Swiggy / Zomato Pattern) --}}
@php
    $portions = $product->variants;
    $modifiers = $product->modifiers;
    $firstPortion = $portions->firstWhere('stock', '>', 0) ?? $portions->first();
@endphp

<div class="space-y-6" x-data="foodPortionSelector()">
    <!-- 1. Mutually Exclusive Portion Sizes -->
    <div>
        <div class="flex items-center justify-between mb-3">
            <h3 class="text-xs font-semibold uppercase tracking-[0.12em] text-[#1A1018] flex items-center gap-1.5">
                <span>🍛 Select Serving Portion</span>
                <span class="text-[10px] text-[#9A3412] bg-[#FFF4ED] font-semibold px-2 py-0.5 rounded-full border border-[#FED7AA]">Required</span>
            </h3>
            <span class="text-[11px] text-[#1A1018]/55 font-medium">Choose 1 option</span>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
            <template x-for="p in portions" :key="p.id">
                <label class="relative flex items-center justify-between p-3.5 rounded-[16px] border transition-all cursor-pointer"
                       :class="selectedPortion && selectedPortion.id === p.id 
                            ? 'border-[#1A1018] bg-[#F8EFFA] shadow-xs' 
                            : 'border-[#E6E2E8] bg-[#FAFAF8] hover:border-[#1A1018]/35'">
                    
                    <div class="flex items-center gap-3">
                        <div class="w-4 h-4 rounded-full border-2 flex items-center justify-center transition-all"
                             :class="selectedPortion && selectedPortion.id === p.id 
                                ? 'border-[#1A1018] bg-[#1A1018]' 
                                : 'border-[#1A1018]/30 bg-white'">
                            <span x-show="selectedPortion && selectedPortion.id === p.id" class="w-1.5 h-1.5 rounded-full bg-[#ECD2F3]"></span>
                        </div>
                        <div>
                            <span class="text-xs font-semibold text-[#1A1018] block" x-text="p.size || p.sku"></span>
                            <span class="text-[10px] text-[#1A1018]/55" x-text="p.stock > 0 ? 'Freshly Prepared' : 'Sold Out'"></span>
                        </div>
                    </div>

                    <div class="text-right">
                        <span class="text-sm font-bold text-[#1A1018]" x-text="`₹${p.price}`"></span>
                    </div>

                    <input type="radio" name="food_portion_id" :value="p.id" class="sr-only" @change="selectPortion(p)">
                </label>
            </template>
        </div>
    </div>

    <!-- 2. Optional Add-ons & Modifiers -->
    @if($modifiers->isNotEmpty())
        <div class="pt-4 border-t border-[#E6E2E8]">
            <div class="flex items-center justify-between mb-3">
                <h3 class="text-xs font-semibold uppercase tracking-[0.12em] text-[#1A1018] flex items-center gap-1.5">
                    <span>➕ Customize with Add-ons</span>
                    <span class="text-[10px] text-[#1A1018]/50 font-medium">(Optional)</span>
                </h3>
                <span class="text-[11px] text-[#9A3412] font-semibold" x-text="selectedModifiers.length + ' added'"></span>
            </div>

            <div class="space-y-2">
                @foreach($modifiers as $mod)
                    <label class="flex items-center justify-between p-3 rounded-[14px] border border-[#E6E2E8] bg-[#FAFAF8] hover:border-[#1A1018]/35 transition cursor-pointer">
                        <div class="flex items-center gap-2.5">
                            <input type="checkbox" 
                                   value="{{ $mod->id }}"
                                   data-name="{{ $mod->modifier_name }}"
                                   data-price="{{ (float)$mod->price_delta }}"
                                   @change="toggleModifier($event, {{ $mod->id }}, '{{ addslashes($mod->modifier_name) }}', {{ (float)$mod->price_delta }})"
                                   class="w-4 h-4 rounded text-[#1A1018] focus:ring-[#ECD2F3] border-[#E6E2E8]">
                            <div>
                                <span class="text-xs font-semibold text-[#1A1018] block leading-tight">{{ $mod->modifier_name }}</span>
                                @if($mod->group_name)
                                    <span class="text-[10px] text-[#1A1018]/50">{{ $mod->group_name }}</span>
                                @endif
                            </div>
                        </div>
                        <span class="text-xs font-semibold text-[#1A1018] bg-[#ECD2F3]/60 px-2.5 py-0.5 rounded-full border border-[#ECD2F3]">
                            +₹{{ number_format((float)$mod->price_delta, 2) }}
                        </span>
                    </label>
                @endforeach
            </div>
        </div>
    @endif

    <!-- 3. Real-Time Computed Total Box -->
    <div class="p-4 rounded-[18px] bg-[#161616] text-white border border-white/10 shadow-sm flex items-center justify-between">
        <div>
            <span class="text-[10px] font-semibold uppercase tracking-[0.16em] text-[#C9A24A] block">Computed Meal Total</span>
            <span class="text-[11px] text-white/75" x-text="computedBreakdownText"></span>
        </div>
        <div class="text-right">
            <span class="text-xl font-semibold text-[#ECD2F3] tabular-nums" style="font-family: var(--font-grail-serif);" x-text="`₹${computedTotal}`"></span>
        </div>
    </div>
</div>

<script>
    function foodPortionSelector() {
        return {
            portions: [],
            selectedPortion: null,
            selectedModifiers: [],

            init() {
                const rawVariants = document.getElementById('product-variants-data');
                if (rawVariants) {
                    try {
                        this.portions = JSON.parse(rawVariants.textContent);
                    } catch (e) {
                        this.portions = [];
                    }
                }

                if (this.portions.length > 0) {
                    const first = this.portions.find(p => p.stock > 0) || this.portions[0];
                    this.selectPortion(first);
                }
            },

            selectPortion(p) {
                this.selectedPortion = p;
                this.syncParentShow();
            },

            toggleModifier(e, id, name, delta) {
                if (e.target.checked) {
                    this.selectedModifiers.push({ id, name, delta });
                } else {
                    this.selectedModifiers = this.selectedModifiers.filter(m => m.id !== id);
                }
                this.syncParentShow();
            },

            get computedTotal() {
                let base = this.selectedPortion ? parseFloat(this.selectedPortion.price) || 0 : 0;
                let mods = this.selectedModifiers.reduce((acc, m) => acc + (parseFloat(m.delta) || 0), 0);
                return (base + mods).toFixed(2);
            },

            get computedBreakdownText() {
                if (!this.selectedPortion) return 'Select a portion above';
                if (this.selectedModifiers.length === 0) {
                    return `${this.selectedPortion.size || this.selectedPortion.sku} (No Add-ons)`;
                }
                return `${this.selectedPortion.size || this.selectedPortion.sku} + ${this.selectedModifiers.length} Add-on(s)`;
            },

            syncParentShow() {
                if (!this.selectedPortion) return;
                window.dispatchEvent(new CustomEvent('food-selection-updated', {
                    detail: {
                        portion: this.selectedPortion,
                        modifiers: this.selectedModifiers,
                        totalPrice: this.computedTotal
                    }
                }));
            }
        };
    }
</script>
