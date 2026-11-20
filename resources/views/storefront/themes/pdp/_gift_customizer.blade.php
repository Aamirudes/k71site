{{-- K71 Custom Gift Storefront Interactive Personalization Engine --}}
@php
    $cfg = $product->getGiftCustomizationConfig();
    $textLimit = (int) ($cfg['text_char_limit'] ?? 40);
    $textPricePaise = (int) ($cfg['text_price_paise'] ?? 0);
    $photoPricePaise = (int) ($cfg['photo_price_paise'] ?? 0);
    $giftWrapPricePaise = (int) ($cfg['gift_wrap_price_paise'] ?? 4900);
    $greetingCardPricePaise = (int) ($cfg['greeting_card_price_paise'] ?? 2900);
    $productionDays = (int) ($cfg['production_days'] ?? 2);
    $fonts = (array) ($cfg['available_fonts'] ?? ['Classic Serif', 'Modern Script', 'Bold Minimal', 'Vintage Engraving']);
    $colors = (array) ($cfg['available_colors'] ?? ['Gold Foil', 'Silver Shimmer', 'Midnight Black', 'Rose Gold', 'Emerald Green']);
    $defaultFont = $fonts[0] ?? 'Classic Serif';
    $defaultColor = $colors[0] ?? 'Gold Foil';

    $colorMap = [
        'Gold Foil' => ['hex' => '#D4AF37', 'bg' => 'bg-[#D4AF37]', 'text' => '#1A1018'],
        'Silver Shimmer' => ['hex' => '#C0C0C0', 'bg' => 'bg-[#C0C0C0]', 'text' => '#1A1018'],
        'Midnight Black' => ['hex' => '#18181B', 'bg' => 'bg-[#18181B]', 'text' => '#FFFFFF'],
        'Rose Gold' => ['hex' => '#B76E79', 'bg' => 'bg-[#B76E79]', 'text' => '#FFFFFF'],
        'Emerald Green' => ['hex' => '#047857', 'bg' => 'bg-[#047857]', 'text' => '#FFFFFF'],
        'Deep Crimson' => ['hex' => '#881337', 'bg' => 'bg-[#881337]', 'text' => '#FFFFFF'],
    ];

    $fontFamilyMap = [
        'Classic Serif' => "font-family: 'Cormorant Garamond', Georgia, serif;",
        'Modern Script' => "font-family: 'Playfair Display', cursive;",
        'Bold Minimal' => "font-family: 'Space Grotesk', sans-serif; font-weight: 700;",
        'Vintage Engraving' => "font-family: 'Cinzel', Georgia, serif; letter-spacing: 0.08em; text-transform: uppercase;",
        'Casual Handwritten' => "font-family: 'Dancing Script', cursive;",
    ];
@endphp

<div x-data="k71GiftCustomizer({
        basePricePaise: {{ (int) ($pricePaise ?? $product->base_price ?? 0) }},
        textPricePaise: {{ $textPricePaise }},
        photoPricePaise: {{ $photoPricePaise }},
        giftWrapPricePaise: {{ $giftWrapPricePaise }},
        greetingCardPricePaise: {{ $greetingCardPricePaise }},
        charLimit: {{ $textLimit }},
        defaultFont: '{{ addslashes($defaultFont) }}',
        defaultColor: '{{ addslashes($defaultColor) }}'
     })"
     class="p-4 sm:p-5 rounded-2xl space-y-4 text-xs transition-all border"
     style="background-color: var(--t-surface, #ffffff); border-color: var(--t-border, rgba(0,0,0,0.1)); color: var(--t-text, #18181b);">

    <!-- Header Badge & Guarantee -->
    <div class="flex items-center justify-between pb-3 border-b" style="border-color: var(--t-border, rgba(0,0,0,0.08));">
        <div class="flex items-center gap-2">
            <span class="w-6 h-6 rounded-lg flex items-center justify-center text-sm shadow-xs" style="background-color: var(--t-accent); color: var(--t-accent-contrast);">🎁</span>
            <div>
                <h4 class="font-bold uppercase tracking-wider text-[11px]" style="color: var(--t-text);">Custom Gift Personalization</h4>
                <p class="text-[10px]" style="color: var(--t-muted);">Handcrafted &amp; made-to-order</p>
            </div>
        </div>
        <span class="text-[10px] font-mono px-2 py-0.5 rounded-full border font-semibold" style="border-color: var(--t-border); color: var(--t-muted);">
            ⏳ Ships in {{ $productionDays }} {{ \Illuminate\Support\Str::plural('day', $productionDays) }}
        </span>
    </div>

    <!-- Live Interactive Preview Canvas -->
    <div class="p-3.5 rounded-xl border relative overflow-hidden transition-all shadow-inner"
         style="background: linear-gradient(135deg, rgba(0,0,0,0.03), rgba(0,0,0,0.07)); border-color: var(--t-border);">
        <div class="flex items-center justify-between text-[10px] uppercase font-bold tracking-wider mb-2" style="color: var(--t-muted);">
            <span>Interactive Live Mockup</span>
            <span x-text="customText ? 'Live Preview Active' : 'Enter Text Below'"></span>
        </div>

        <div class="min-h-[64px] flex flex-col items-center justify-center text-center p-3 rounded-lg bg-black/5 backdrop-blur-xs border border-black/5 relative">
            <template x-if="photoPreviewUrl">
                <div class="mb-2 w-12 h-12 rounded-lg overflow-hidden border-2 shadow-xs shrink-0" style="border-color: var(--t-accent);">
                    <img :src="photoPreviewUrl" alt="Photo Preview" class="w-full h-full object-cover">
                </div>
            </template>
            <div class="text-base sm:text-lg transition-all duration-300 break-all select-none px-2"
                 :style="previewStyle"
                 x-text="customText || '{{ addslashes($cfg['text_prompt'] ?? 'Your Custom Text Here') }}'">
            </div>
            <div class="text-[10px] mt-1 font-mono tracking-tight opacity-75" style="color: var(--t-muted);">
                <span x-text="selectedFont"></span> · <span x-text="selectedColor"></span>
            </div>
        </div>

        <!-- Live Price Calculation Bar -->
        <div class="mt-2.5 pt-2 flex items-center justify-between border-t text-[11px]" style="border-color: var(--t-border);">
            <span style="color: var(--t-muted);">Calculated Piece Total:</span>
            <span class="font-bold font-mono text-sm" style="color: var(--t-text);" x-text="formattedTotal"></span>
        </div>
    </div>

    <!-- Custom Text Field -->
    @if(!empty($cfg['allow_custom_text']))
        <div class="space-y-1.5">
            <div class="flex items-center justify-between text-[11px]">
                <label for="k71-gift-text" class="font-bold flex items-center gap-1.5">
                    <span>{{ $cfg['text_prompt'] ?? 'Personalized Name or Message' }}</span>
                    @if($textPricePaise > 0)
                        <span class="font-normal font-mono text-[10px] opacity-75">(+₹{{ number_format($textPricePaise / 100, 0) }})</span>
                    @endif
                </label>
                <span class="font-mono text-[10px]" :class="customText.length > {{ $textLimit }} ? 'text-rose-600 font-bold' : ''" style="color: var(--t-muted);">
                    <span x-text="customText.length"></span>/{{ $textLimit }}
                </span>
            </div>
            <input type="text"
                   id="k71-gift-text"
                   name="customization[custom_text]"
                   x-model="customText"
                   maxlength="{{ $textLimit }}"
                   placeholder="e.g. Happy Birthday Sophia ❤️"
                   class="w-full px-3 py-2 rounded-lg text-xs font-medium focus:outline-none transition border"
                   style="background-color: var(--t-bg); border-color: var(--t-border); color: var(--t-text);"
                   {{ !empty($cfg['is_text_required']) ? 'required' : '' }}>
        </div>
    @endif

    <!-- Font / Script Selector -->
    @if(!empty($cfg['allow_font_selection']) && count($fonts) > 1)
        <div class="space-y-1.5">
            <label class="font-bold text-[11px] block">Select Font / Engraving Style:</label>
            <div class="grid grid-cols-2 gap-1.5" role="radiogroup" aria-label="Font style">
                @foreach($fonts as $f)
                    @php
                        $fontStyle = $fontFamilyMap[$f] ?? '';
                    @endphp
                    <button type="button"
                            @click="selectedFont = '{{ addslashes($f) }}'"
                            class="px-2.5 py-1.5 rounded-lg border text-left transition cursor-pointer flex items-center justify-between text-xs"
                            :style="selectedFont === '{{ addslashes($f) }}' ? 'border-color: var(--t-accent); background-color: var(--t-accent); color: var(--t-accent-contrast); font-weight: 700;' : 'border-color: var(--t-border); background-color: transparent; color: var(--t-text);'">
                        <span style="{{ $fontStyle }}">{{ $f }}</span>
                        <span x-show="selectedFont === '{{ addslashes($f) }}'" class="text-[10px]">✓</span>
                    </button>
                @endforeach
            </div>
            <input type="hidden" name="customization[font]" :value="selectedFont">
        </div>
    @else
        <input type="hidden" name="customization[font]" value="{{ $defaultFont }}">
    @endif

    <!-- Color / Foil Swatches -->
    @if(!empty($cfg['allow_color_selection']) && count($colors) > 1)
        <div class="space-y-1.5">
            <div class="flex items-center justify-between text-[11px]">
                <label class="font-bold">Engraving / Foil Color:</label>
                <span class="font-mono text-[10px]" style="color: var(--t-muted);" x-text="selectedColor"></span>
            </div>
            <div class="flex flex-wrap gap-2 items-center" role="radiogroup" aria-label="Engraving color">
                @foreach($colors as $c)
                    @php
                        $meta = $colorMap[$c] ?? ['hex' => '#18181B', 'bg' => 'bg-black', 'text' => '#fff'];
                    @endphp
                    <button type="button"
                            @click="selectedColor = '{{ addslashes($c) }}'; selectedColorHex = '{{ $meta['hex'] }}'"
                            class="w-7 h-7 rounded-full border-2 transition-transform cursor-pointer relative shadow-xs flex items-center justify-center hover:scale-110"
                            :class="selectedColor === '{{ addslashes($c) }}' ? 'scale-110 ring-2 ring-offset-2' : 'opacity-85'"
                            style="background-color: {{ $meta['hex'] }}; border-color: rgba(255,255,255,0.7);"
                            title="{{ $c }}">
                        <span x-show="selectedColor === '{{ addslashes($c) }}'" class="text-[10px] font-black" style="color: {{ $meta['text'] }};">✓</span>
                    </button>
                @endforeach
            </div>
            <input type="hidden" name="customization[color]" :value="selectedColor">
        </div>
    @else
        <input type="hidden" name="customization[color]" value="{{ $defaultColor }}">
    @endif

    <!-- Photo Upload Option -->
    @if(!empty($cfg['allow_photo_upload']))
        <div class="pt-2 border-t space-y-2" style="border-color: var(--t-border);">
            <div class="flex items-center justify-between text-[11px]">
                <label class="font-bold flex items-center gap-1.5">
                    <span>{{ $cfg['photo_prompt'] ?? 'Upload Photo for Personalization' }}</span>
                    @if($photoPricePaise > 0)
                        <span class="font-normal font-mono text-[10px] opacity-75">(+₹{{ number_format($photoPricePaise / 100, 0) }})</span>
                    @endif
                </label>
            </div>
            <div class="flex items-center gap-3">
                <label for="k71-gift-photo-input"
                       class="flex-1 px-3 py-2.5 rounded-lg border border-dashed flex items-center justify-center gap-2 cursor-pointer transition hover:opacity-80"
                       style="border-color: var(--t-border); background-color: var(--t-bg);">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"/><circle cx="12" cy="13" r="4"/></svg>
                    <span x-text="photoFileName ? photoFileName : 'Click to Upload High-Res Image (Max 10MB)'"></span>
                </label>
                <template x-if="photoPreviewUrl">
                    <button type="button" @click="clearPhoto()" class="text-rose-600 font-bold p-1 hover:underline cursor-pointer" title="Remove Photo">✕</button>
                </template>
            </div>
            <input type="file"
                   id="k71-gift-photo-input"
                   name="customization_photo"
                   accept="image/jpeg,image/png,image/webp,image/jpg"
                   @change="handlePhotoChange($event)"
                   class="sr-only">
        </div>
    @endif

    <!-- Add-on Toggles: Premium Gift Wrap & Greeting Card -->
    <div class="pt-2 border-t space-y-2 text-[11px]" style="border-color: var(--t-border);">
        @if(!empty($cfg['allow_gift_wrap']))
            <label class="flex items-center justify-between p-2 rounded-lg cursor-pointer transition select-none hover:bg-black/5">
                <span class="flex items-center gap-2 font-medium">
                    <input type="checkbox"
                           name="customization[gift_wrap]"
                           value="1"
                           x-model="giftWrap"
                           class="rounded text-purple-600 focus:ring-purple-500 cursor-pointer">
                    <span>🎀 Add Premium Velvet Ribbon Gift Wrap</span>
                </span>
                <span class="font-mono font-bold" style="color: var(--t-muted);">+₹{{ number_format($giftWrapPricePaise / 100, 0) }}</span>
            </label>
        @endif

        @if(!empty($cfg['allow_greeting_card']))
            <div class="space-y-1.5">
                <label class="flex items-center justify-between p-2 rounded-lg cursor-pointer transition select-none hover:bg-black/5">
                    <span class="flex items-center gap-2 font-medium">
                        <input type="checkbox"
                               name="customization[greeting_card]"
                               value="1"
                               x-model="greetingCard"
                               class="rounded text-purple-600 focus:ring-purple-500 cursor-pointer">
                        <span>💌 Include Handwritten Greeting Card</span>
                    </span>
                    <span class="font-mono font-bold" style="color: var(--t-muted);">+₹{{ number_format($greetingCardPricePaise / 100, 0) }}</span>
                </label>
                <div x-show="greetingCard" x-collapse class="pl-6 pr-2 pb-1 space-y-1">
                    <textarea name="customization[greeting_card_message]"
                              x-model="greetingCardMessage"
                              rows="2"
                              maxlength="300"
                              placeholder="Write your greeting card message..."
                              class="w-full p-2 text-xs rounded-lg border focus:outline-none"
                              style="background-color: var(--t-bg); border-color: var(--t-border); color: var(--t-text);"></textarea>
                    <p class="text-[10px]" style="color: var(--t-muted);">Will be penned inside a wax-sealed card.</p>
                </div>
            </div>
        @endif
    </div>
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
        basePricePaise: params.basePricePaise || 0,
        textPricePaise: params.textPricePaise || 0,
        photoPricePaise: params.photoPricePaise || 0,
        giftWrapPricePaise: params.giftWrapPricePaise || 0,
        greetingCardPricePaise: params.greetingCardPricePaise || 0,
        charLimit: params.charLimit || 40,

        customText: '',
        selectedFont: params.defaultFont || 'Classic Serif',
        selectedColor: params.defaultColor || 'Gold Foil',
        selectedColorHex: colorHexes[params.defaultColor] || '#D4AF37',
        photoFileName: '',
        photoPreviewUrl: null,
        giftWrap: false,
        greetingCard: false,
        greetingCardMessage: '',

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
