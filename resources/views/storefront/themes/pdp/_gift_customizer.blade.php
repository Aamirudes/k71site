{{-- K71 Custom Gift Storefront Interactive Personalization Engine with Modal Popup --}}
@php
    $cfg = $product->getGiftCustomizationConfig();
    $textLimit = (int) ($cfg['text_char_limit'] ?? 40);
    $textPricePaise = (int) ($cfg['text_price_paise'] ?? 0);
    $photoPricePaise = (int) ($cfg['photo_price_paise'] ?? 0);
    $giftWrapPricePaise = (int) ($cfg['gift_wrap_price_paise'] ?? 4900);
    $greetingCardPricePaise = (int) ($cfg['greeting_card_price_paise'] ?? 2900);
    $productionDays = (int) ($cfg['production_days'] ?? 2);
    $isTextRequired = !empty($cfg['is_text_required']);
    $fonts = (array) ($cfg['available_fonts'] ?? ['Classic Serif', 'Modern Script', 'Bold Minimal', 'Vintage Engraving']);
    $colors = (array) ($cfg['available_colors'] ?? ['Gold Foil', 'Silver Shimmer', 'Midnight Black', 'Rose Gold', 'Emerald Green']);
    $defaultFont = $fonts[0] ?? 'Classic Serif';
    $defaultColor = $colors[0] ?? 'Gold Foil';

    $colorMap = [
        'Gold Foil' => ['hex' => '#D4AF37', 'text' => '#1A1018'],
        'Silver Shimmer' => ['hex' => '#C0C0C0', 'text' => '#1A1018'],
        'Midnight Black' => ['hex' => '#18181B', 'text' => '#FFFFFF'],
        'Rose Gold' => ['hex' => '#B76E79', 'text' => '#FFFFFF'],
        'Emerald Green' => ['hex' => '#047857', 'text' => '#FFFFFF'],
        'Deep Crimson' => ['hex' => '#881337', 'text' => '#FFFFFF'],
    ];
@endphp

<div x-data="k71GiftCustomizer({
        basePricePaise: {{ (int) ($pricePaise ?? $product->base_price ?? 0) }},
        textPricePaise: {{ $textPricePaise }},
        photoPricePaise: {{ $photoPricePaise }},
        giftWrapPricePaise: {{ $giftWrapPricePaise }},
        greetingCardPricePaise: {{ $greetingCardPricePaise }},
        charLimit: {{ $textLimit }},
        isTextRequired: {{ $isTextRequired ? 'true' : 'false' }},
        defaultFont: '{{ addslashes($defaultFont) }}',
        defaultColor: '{{ addslashes($defaultColor) }}'
     })"
     @k71-open-gift-modal.window="openModal()"
     class="space-y-3">

    <!-- 1. Sleek In-Page Summary Card (Clean, Non-Cluttered Trigger) -->
    <div class="p-3.5 sm:p-4 rounded-2xl border transition-all shadow-xs"
         style="background-color: var(--t-surface, #ffffff); border-color: var(--t-border, rgba(0,0,0,0.1)); color: var(--t-text, #18181b);">
        
        <div class="flex items-center justify-between gap-3">
            <div class="flex items-center gap-2.5 min-w-0">
                <span class="w-8 h-8 rounded-xl flex items-center justify-center text-base shadow-xs shrink-0" 
                      style="background-color: var(--t-accent, #7c3aed); color: var(--t-accent-contrast, #ffffff);">
                    🎁
                </span>
                <div class="min-w-0">
                    <div class="flex items-center gap-2 flex-wrap">
                        <h4 class="font-bold text-xs uppercase tracking-wider" style="color: var(--t-text, #18181b);">
                            Personalized Gift
                        </h4>
                        @if($isTextRequired)
                            <span class="text-[9px] font-black uppercase tracking-wider px-1.5 py-0.5 rounded-md bg-rose-500/10 text-rose-600 border border-rose-500/20">
                                Required
                            </span>
                        @else
                            <span class="text-[9px] font-black uppercase tracking-wider px-1.5 py-0.5 rounded-md bg-emerald-500/10 text-emerald-600 border border-emerald-500/20">
                                Optional
                            </span>
                        @endif
                    </div>
                    <p class="text-[11px] truncate mt-0.5" style="color: var(--t-muted, #71717a);">
                        <template x-if="hasCustomization">
                            <span class="font-medium text-emerald-600 dark:text-emerald-400">
                                ✓ Customization saved: "<span x-text="customText || 'Photo/Options selected'"></span>"
                            </span>
                        </template>
                        <template x-if="!hasCustomization">
                            <span>Laser text engraving, custom fonts, photos &amp; gift wrap</span>
                        </template>
                    </p>
                </div>
            </div>

            <!-- Modal Trigger Button -->
            <button type="button" 
                    @click="openModal()" 
                    class="shrink-0 px-3.5 py-2 rounded-xl font-bold text-xs flex items-center gap-1.5 transition-all shadow-sm active:scale-95 cursor-pointer"
                    :class="hasCustomization ? 'bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-800 dark:text-slate-200' : 'bg-gradient-to-r from-purple-600 to-indigo-600 hover:from-purple-500 hover:to-indigo-500 text-white shadow-purple-500/20'">
                <span x-text="hasCustomization ? '✏️ Edit Specs' : '✨ Personalize'"></span>
            </button>
        </div>

        <!-- Confirmed Specs Summary Pills (Shown when configured) -->
        <template x-if="hasCustomization">
            <div class="mt-2.5 pt-2.5 border-t flex flex-wrap items-center gap-1.5 text-[10px] font-mono" style="border-color: var(--t-border, rgba(0,0,0,0.08));">
                <template x-if="customText">
                    <span class="px-2 py-0.5 rounded-md bg-purple-50 dark:bg-purple-950/60 border border-purple-200 dark:border-purple-800 text-purple-700 dark:text-purple-300 font-bold">
                        Text: "<span x-text="customText"></span>"
                    </span>
                </template>
                <span class="px-2 py-0.5 rounded-md bg-black/5 dark:bg-white/5 border border-black/10 dark:border-white/10" x-text="selectedFont"></span>
                <span class="px-2 py-0.5 rounded-md bg-black/5 dark:bg-white/5 border border-black/10 dark:border-white/10" x-text="selectedColor"></span>
                <template x-if="photoFileName">
                    <span class="px-2 py-0.5 rounded-md bg-cyan-50 dark:bg-cyan-950/60 border border-cyan-200 dark:border-cyan-800 text-cyan-700 dark:text-cyan-300">
                        📸 Photo Attached
                    </span>
                </template>
                <template x-if="giftWrap">
                    <span class="px-2 py-0.5 rounded-md bg-amber-50 dark:bg-amber-950/60 border border-amber-200 dark:border-amber-800 text-amber-700 dark:text-amber-300">
                        🎀 Gift Wrapped
                    </span>
                </template>
                <template x-if="greetingCard">
                    <span class="px-2 py-0.5 rounded-md bg-rose-50 dark:bg-rose-950/60 border border-rose-200 dark:border-rose-800 text-rose-700 dark:text-rose-300">
                        💌 Card Included
                    </span>
                </template>
                <span class="ml-auto font-bold text-xs" style="color: var(--t-text, #18181b);" x-text="formattedTotal"></span>
            </div>
        </template>
    </div>

    <!-- Hidden Inputs Bound to Form for Direct Submission -->
    <input type="hidden" name="customization[custom_text]" :value="customText">
    <input type="hidden" name="customization[font]" :value="selectedFont">
    <input type="hidden" name="customization[color]" :value="selectedColor">
    <input type="hidden" name="customization[gift_wrap]" :value="giftWrap ? '1' : ''">
    <input type="hidden" name="customization[greeting_card]" :value="greetingCard ? '1' : ''">
    <input type="hidden" name="customization[greeting_card_message]" :value="greetingCardMessage">
    <input type="file"
           id="k71-gift-photo-input"
           name="customization_photo"
           accept="image/jpeg,image/png,image/webp,image/jpg"
           @change="handlePhotoChange($event)"
           class="sr-only">

    <!-- 2. LUXURY RESPONSIVE PERSONALIZATION POPUP MODAL -->
    <template x-teleport="body">
        <div x-show="isModalOpen" 
             x-cloak
             @keydown.escape.window="closeModal()"
             class="fixed inset-0 z-[9999] overflow-y-auto flex items-center justify-center p-3 sm:p-5"
             style="display: none;">
            
            <!-- Backdrop Blur Overlay -->
            <div x-show="isModalOpen"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 @click="closeModal()"
                 class="fixed inset-0 bg-black/75 backdrop-blur-md"></div>

            <!-- Modal Dialog Container -->
            <div x-show="isModalOpen"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 scale-95 translate-y-4"
                 x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                 x-transition:leave-end="opacity-0 scale-95 translate-y-4"
                 class="relative w-full max-w-2xl bg-white dark:bg-slate-900 rounded-3xl shadow-2xl border border-slate-200 dark:border-white/10 overflow-hidden flex flex-col max-h-[92vh] z-10 text-slate-900 dark:text-white">
                
                <!-- Modal Header -->
                <div class="px-5 py-4 border-b border-slate-100 dark:border-white/10 flex items-center justify-between bg-slate-50/50 dark:bg-slate-950/40">
                    <div class="flex items-center gap-2.5">
                        <span class="w-8 h-8 rounded-xl bg-purple-500/10 text-purple-600 dark:text-purple-400 flex items-center justify-center text-base">🎁</span>
                        <div>
                            <h3 class="text-sm font-bold uppercase tracking-wider text-slate-900 dark:text-white">Personalization Studio</h3>
                            <p class="text-[11px] text-slate-500 dark:text-slate-400">Configure text engraving, fonts, photo upload &amp; gift wrap</p>
                        </div>
                    </div>
                    <button type="button" 
                            @click="closeModal()" 
                            class="w-8 h-8 rounded-full bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-500 hover:text-slate-900 dark:hover:text-white flex items-center justify-center text-sm transition cursor-pointer">
                        ✕
                    </button>
                </div>

                <!-- Modal Body (Scrollable) -->
                <div class="p-5 sm:p-6 space-y-5 overflow-y-auto flex-1">
                    
                    <!-- Live Interactive Mockup Canvas -->
                    <div class="p-4 rounded-2xl border border-slate-200 dark:border-white/10 bg-gradient-to-br from-slate-50 to-slate-100 dark:from-slate-950/60 dark:to-slate-900 shadow-inner">
                        <div class="flex items-center justify-between text-[10px] uppercase font-bold tracking-wider text-slate-400 mb-2">
                            <span>Live Mockup Preview</span>
                            <span class="font-mono text-purple-600 dark:text-purple-400" x-text="customText ? 'Preview Active' : 'Enter Text Below'"></span>
                        </div>

                        <div class="min-h-[85px] flex flex-col items-center justify-center text-center p-4 rounded-xl bg-white/80 dark:bg-slate-900/80 backdrop-blur-xs border border-black/5 dark:border-white/5 relative shadow-xs">
                            <template x-if="photoPreviewUrl">
                                <div class="mb-2 w-14 h-14 rounded-xl overflow-hidden border-2 border-purple-500 shadow-sm shrink-0">
                                    <img :src="photoPreviewUrl" alt="Photo Preview" class="w-full h-full object-cover">
                                </div>
                            </template>
                            <div class="text-lg sm:text-2xl transition-all duration-300 break-all select-none px-3"
                                 :style="previewStyle"
                                 x-text="customText || '{{ addslashes($cfg['text_prompt'] ?? 'Your Name / Message Here') }}'">
                            </div>
                            <div class="text-[11px] mt-1.5 font-mono opacity-80 text-slate-500 dark:text-slate-400">
                                <span x-text="selectedFont"></span> · <span x-text="selectedColor"></span>
                            </div>
                        </div>

                        <!-- Turnaround Badge -->
                        <div class="mt-3 flex items-center justify-between text-[11px] font-medium text-slate-500 dark:text-slate-400">
                            <span>⏳ Turnaround: {{ $productionDays }} {{ \Illuminate\Support\Str::plural('day', $productionDays) }} crafting</span>
                            <span class="font-bold text-slate-900 dark:text-white" x-text="'Item Total: ' + formattedTotal"></span>
                        </div>
                    </div>

                    <!-- Custom Text Input -->
                    @if(!empty($cfg['allow_custom_text']))
                        <div class="space-y-1.5">
                            <div class="flex items-center justify-between text-xs">
                                <label for="modal-k71-text" class="font-bold text-slate-800 dark:text-slate-200">
                                    {{ $cfg['text_prompt'] ?? 'Personalized Name or Message' }}
                                    @if($isTextRequired)
                                        <span class="text-rose-500">*</span>
                                    @endif
                                    @if($textPricePaise > 0)
                                        <span class="font-normal font-mono text-[11px] text-slate-500">(+₹{{ number_format($textPricePaise / 100, 0) }})</span>
                                    @else
                                        <span class="font-normal font-mono text-[11px] text-emerald-600 font-bold">(Free Engraving)</span>
                                    @endif
                                </label>
                                <span class="font-mono text-[11px]" :class="customText.length > {{ $textLimit }} ? 'text-rose-600 font-bold' : 'text-slate-400'">
                                    <span x-text="customText.length"></span>/{{ $textLimit }}
                                </span>
                            </div>
                            <input type="text"
                                   id="modal-k71-text"
                                   x-model="customText"
                                   maxlength="{{ $textLimit }}"
                                   placeholder="e.g. Alexander &amp; Sophia"
                                   class="w-full px-4 py-2.5 text-sm rounded-xl border border-slate-300 dark:border-white/10 bg-slate-50 dark:bg-slate-950 text-slate-900 dark:text-white focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition shadow-inner">
                        </div>
                    @endif

                    <!-- Font Style Selection -->
                    @if(!empty($cfg['allow_font_selection']))
                        <div class="space-y-1.5">
                            <label class="block text-xs font-bold text-slate-800 dark:text-slate-200">Choose Typography &amp; Engraving Font</label>
                            <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
                                @foreach($fonts as $f)
                                    <button type="button"
                                            @click="selectedFont = '{{ addslashes($f) }}'"
                                            class="p-2.5 rounded-xl border text-center transition cursor-pointer text-xs"
                                            :class="selectedFont === '{{ addslashes($f) }}' ? 'border-purple-600 bg-purple-50 dark:bg-purple-950/60 font-bold text-purple-700 dark:text-purple-300 ring-2 ring-purple-500/20 shadow-sm' : 'border-slate-200 dark:border-white/10 bg-slate-50 dark:bg-slate-950/40 text-slate-700 dark:text-slate-300 hover:border-slate-300'">
                                        <div class="text-sm tracking-tight truncate">{{ $f }}</div>
                                    </button>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <!-- Foil / Color Swatches -->
                    @if(!empty($cfg['allow_color_selection']))
                        <div class="space-y-1.5">
                            <div class="flex items-center justify-between text-xs">
                                <label class="font-bold text-slate-800 dark:text-slate-200">Select Print / Foil Color</label>
                                <span class="font-mono text-[11px] text-slate-400" x-text="selectedColor"></span>
                            </div>
                            <div class="flex flex-wrap items-center gap-3">
                                @foreach($colors as $c)
                                    @php
                                        $meta = $colorMap[$c] ?? ['hex' => '#18181B', 'text' => '#fff'];
                                    @endphp
                                    <button type="button"
                                            @click="selectedColor = '{{ addslashes($c) }}'; selectedColorHex = '{{ $meta['hex'] }}'"
                                            class="w-8 h-8 rounded-full border-2 transition-transform cursor-pointer relative shadow-sm flex items-center justify-center hover:scale-110"
                                            :class="selectedColor === '{{ addslashes($c) }}' ? 'scale-110 ring-2 ring-purple-500 ring-offset-2' : 'opacity-85'"
                                            style="background-color: {{ $meta['hex'] }}; border-color: rgba(255,255,255,0.7);"
                                            title="{{ $c }}">
                                        <span x-show="selectedColor === '{{ addslashes($c) }}'" class="text-[10px] font-black" style="color: {{ $meta['text'] }};">✓</span>
                                    </button>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <!-- Photo Upload Section -->
                    @if(!empty($cfg['allow_photo_upload']))
                        <div class="pt-3 border-t border-slate-100 dark:border-white/10 space-y-2">
                            <div class="flex items-center justify-between text-xs font-bold text-slate-800 dark:text-slate-200">
                                <span>{{ $cfg['photo_prompt'] ?? 'Upload Photo or Custom Monogram' }}</span>
                                @if($photoPricePaise > 0)
                                    <span class="font-normal font-mono text-[11px] text-slate-500">(+₹{{ number_format($photoPricePaise / 100, 0) }})</span>
                                @endif
                            </div>
                            <div class="flex items-center gap-3">
                                <label for="modal-photo-trigger"
                                       class="flex-1 px-4 py-3 rounded-xl border border-dashed border-slate-300 dark:border-white/20 bg-slate-50 dark:bg-slate-950 flex items-center justify-center gap-2 cursor-pointer transition hover:border-purple-500 text-xs text-slate-600 dark:text-slate-400">
                                    <svg class="w-4 h-4 text-purple-600 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"/><circle cx="12" cy="13" r="4"/></svg>
                                    <span x-text="photoFileName ? photoFileName : 'Click to Upload High-Res Photo (JPEG, PNG, WEBP)'"></span>
                                </label>
                                <template x-if="photoPreviewUrl">
                                    <button type="button" @click="clearPhoto()" class="text-rose-600 font-bold p-2 hover:bg-rose-50 dark:hover:bg-rose-950/40 rounded-xl transition cursor-pointer" title="Remove Photo">✕</button>
                                </template>
                            </div>
                            <button type="button" @click="document.getElementById('k71-gift-photo-input').click()" class="hidden" id="modal-photo-trigger"></button>
                        </div>
                    @endif

                    <!-- Packaging & Greeting Card Toggles -->
                    <div class="pt-3 border-t border-slate-100 dark:border-white/10 space-y-2.5 text-xs">
                        @if(!empty($cfg['allow_gift_wrap']))
                            <label class="flex items-center justify-between p-3 rounded-xl bg-slate-50 dark:bg-slate-950/60 border border-slate-200 dark:border-white/10 cursor-pointer transition select-none hover:border-slate-300">
                                <span class="flex items-center gap-2.5 font-medium text-slate-800 dark:text-slate-200">
                                    <input type="checkbox"
                                           x-model="giftWrap"
                                           class="w-4 h-4 rounded text-purple-600 focus:ring-purple-500 cursor-pointer">
                                    <span>🎀 Add Luxury Velvet Ribbon Gift Wrapping</span>
                                </span>
                                <span class="font-mono font-bold text-slate-600 dark:text-slate-400">+₹{{ number_format($giftWrapPricePaise / 100, 0) }}</span>
                            </label>
                        @endif

                        @if(!empty($cfg['allow_greeting_card']))
                            <div class="space-y-2">
                                <label class="flex items-center justify-between p-3 rounded-xl bg-slate-50 dark:bg-slate-950/60 border border-slate-200 dark:border-white/10 cursor-pointer transition select-none hover:border-slate-300">
                                    <span class="flex items-center gap-2.5 font-medium text-slate-800 dark:text-slate-200">
                                        <input type="checkbox"
                                               x-model="greetingCard"
                                               class="w-4 h-4 rounded text-purple-600 focus:ring-purple-500 cursor-pointer">
                                        <span>💌 Include Handwritten Wax-Sealed Greeting Card</span>
                                    </span>
                                    <span class="font-mono font-bold text-slate-600 dark:text-slate-400">+₹{{ number_format($greetingCardPricePaise / 100, 0) }}</span>
                                </label>
                                <div x-show="greetingCard" x-collapse class="pl-2 space-y-1">
                                    <textarea x-model="greetingCardMessage"
                                              rows="2"
                                              maxlength="300"
                                              placeholder="Write your greeting card dedication message..."
                                              class="w-full p-3 text-xs rounded-xl border border-slate-300 dark:border-white/10 bg-white dark:bg-slate-900 text-slate-900 dark:text-white focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition shadow-inner"></textarea>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Modal Footer -->
                <div class="px-5 py-4 border-t border-slate-100 dark:border-white/10 bg-slate-50/50 dark:bg-slate-950/40 flex items-center justify-between gap-3">
                    <div>
                        <span class="text-[10px] uppercase font-bold text-slate-400 block tracking-wider">Calculated Total</span>
                        <span class="text-base font-black font-mono text-slate-900 dark:text-white" x-text="formattedTotal"></span>
                    </div>

                    <div class="flex items-center gap-2">
                        <button type="button" 
                                @click="closeModal()" 
                                class="px-3.5 py-2 rounded-xl font-bold text-xs text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 transition cursor-pointer">
                            Cancel
                        </button>
                        <button type="button" 
                                @click="confirmCustomization(false)" 
                                class="px-3.5 py-2 rounded-xl font-bold text-xs bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-800 dark:text-slate-200 transition cursor-pointer">
                            ✓ Save Specs
                        </button>
                        <button type="button" 
                                @click="confirmCustomization(true)" 
                                class="px-4 py-2 rounded-xl font-black text-xs bg-gradient-to-r from-purple-600 to-indigo-600 hover:from-purple-500 hover:to-indigo-500 text-white shadow-lg shadow-purple-500/20 active:scale-95 transition cursor-pointer flex items-center gap-1.5">
                            <span>✨ Apply &amp; Add to Bag</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </template>
</div>

<script>
function k71GiftCustomizer(params) {
    const fontStyles = {
        'Classic Serif': "font-family: 'Cormorant Garamond', Georgia, serif;",
        'Modern Script': "font-family: 'Playfair Display', cursive;",
        'Bold Minimal': "font-family: 'Space Grotesk', sans-serif; font-weight: 700;",
        'Vintage Engraving': "font-family: 'Cinzel', Georgia, serif; letter-spacing: 0.08em; text-transform: uppercase;",
        'Casual Handwritten': "font-family: 'Dancing Script', cursive;"
    };

    const colorHexes = {
        'Gold Foil': '#D4AF37',
        'Silver Shimmer': '#C0C0C0',
        'Midnight Black': '#18181B',
        'Rose Gold': '#B76E79',
        'Emerald Green': '#047857',
        'Deep Crimson': '#881337'
    };

    return {
        isModalOpen: false,
        basePricePaise: params.basePricePaise || 0,
        textPricePaise: params.textPricePaise || 0,
        photoPricePaise: params.photoPricePaise || 0,
        giftWrapPricePaise: params.giftWrapPricePaise || 0,
        greetingCardPricePaise: params.greetingCardPricePaise || 0,
        charLimit: params.charLimit || 40,
        isTextRequired: !!params.isTextRequired,

        customText: '',
        selectedFont: params.defaultFont || 'Classic Serif',
        selectedColor: params.defaultColor || 'Gold Foil',
        selectedColorHex: colorHexes[params.defaultColor] || '#D4AF37',
        photoFileName: '',
        photoPreviewUrl: null,
        giftWrap: false,
        greetingCard: false,
        greetingCardMessage: '',

        get hasCustomization() {
            return this.customText.trim() !== '' 
                || !!this.photoFileName 
                || this.giftWrap 
                || this.greetingCard;
        },

        get previewStyle() {
            const fontCss = fontStyles[this.selectedFont] || '';
            const colorCss = `color: ${this.selectedColorHex};`;
            return `${fontCss} ${colorCss}`;
        },

        get calculatedTotalPaise() {
            let total = this.basePricePaise;
            if (this.customText.trim() !== '') {
                total += this.textPricePaise;
            }
            if (this.photoPreviewUrl) {
                total += this.photoPricePaise;
            }
            if (this.giftWrap) {
                total += this.giftWrapPricePaise;
            }
            if (this.greetingCard) {
                total += this.greetingCardPricePaise;
            }
            return total;
        },

        get formattedTotal() {
            const rupees = Math.round(this.calculatedTotalPaise / 100);
            return '₹' + rupees.toLocaleString('en-IN');
        },

        openModal() {
            this.isModalOpen = true;
        },

        closeModal() {
            this.isModalOpen = false;
        },

        confirmCustomization(andSubmit = false) {
            if (this.isTextRequired && this.customText.trim() === '') {
                alert('Please enter your personalized text before saving.');
                return;
            }
            this.isModalOpen = false;
            if (andSubmit) {
                this.$nextTick(() => {
                    const form = this.$el.closest('form') || document.querySelector('form[data-k71-cart-form]');
                    if (form) {
                        const btn = form.querySelector('button[type="submit"]');
                        if (btn) btn.click();
                        else form.submit();
                    }
                });
            }
        },

        handlePhotoChange(event) {
            const file = event.target.files?.[0];
            if (!file) return;
            this.photoFileName = file.name;
            if (file.type.startsWith('image/')) {
                this.photoPreviewUrl = URL.createObjectURL(file);
            }
        },

        clearPhoto() {
            this.photoFileName = '';
            this.photoPreviewUrl = null;
            const input = document.getElementById('k71-gift-photo-input');
            if (input) input.value = '';
        }
    };
}
</script>
