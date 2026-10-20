{{-- Pre-Owned & Classified Condition Rating Partial (OLX / eBay Pattern) --}}
@php
    $specs = $product->specifications_json ?? [];
    $condition = $specs['condition'] ?? ($product->getCustomField('condition') ?? 'Verified Pre-Owned (Grade A)');
    $purchaseYear = $specs['purchase_year'] ?? ($product->getCustomField('purchase_year') ?? null);
    $usage = $specs['usage'] ?? ($product->getCustomField('usage') ?? null);
    $warranty = $specs['warranty'] ?? ($product->getCustomField('warranty') ?? 'Seller 7-Day Testing Guarantee');
    $sellerPhone = preg_replace('/[^0-9]/', '', $product->vendor->contact_phone ?? '');
    if (strlen($sellerPhone) === 10) { $sellerPhone = '91' . $sellerPhone; }
@endphp

<div class="space-y-5" x-data="{ showOfferModal: false, offerPrice: '{{ round($product->base_price / 100 * 0.9) }}', buyerName: '', buyerPhone: '' }">
    <!-- 1. Strict Unique Single Stock Badge -->
    <div class="flex items-center justify-between p-3.5 rounded-2xl bg-cyan-50/70 border border-cyan-200/80 text-xs">
        <div class="flex items-center gap-2.5">
            <span class="text-lg">🔒</span>
            <div>
                <span class="text-[10px] font-black uppercase tracking-wider text-cyan-900 block">Unique Verified Asset</span>
                <span class="font-bold text-cyan-950">Only 1 Unique Stock Unit Available</span>
            </div>
        </div>
        <span class="px-2.5 py-1 rounded-full bg-cyan-600 text-white text-[10px] font-black shadow-2xs">
            100% Inspected
        </span>
    </div>

    <!-- 2. Diagnostic Inspection Checklist Grid -->
    <div class="rounded-2xl border border-stone-200 bg-white p-4 space-y-3">
        <div class="flex items-center justify-between pb-2 border-b border-stone-100">
            <h3 class="text-xs font-bold uppercase tracking-wider text-stone-800 flex items-center gap-1.5">
                <span>🔍 Diagnostic Inspection Report</span>
            </h3>
            <span class="text-[10px] text-emerald-700 font-bold bg-emerald-50 px-2 py-0.5 rounded-full border border-emerald-200">
                Verified Genuine
            </span>
        </div>

        <div class="grid grid-cols-2 gap-2 text-xs">
            <div class="p-2.5 rounded-xl bg-stone-50 border border-stone-100">
                <span class="text-[10px] text-stone-400 block font-semibold">Physical Condition</span>
                <span class="font-bold text-stone-900">{{ $condition }}</span>
            </div>
            @if(!empty($purchaseYear))
                <div class="p-2.5 rounded-xl bg-stone-50 border border-stone-100">
                    <span class="text-[10px] text-stone-400 block font-semibold">Year of Purchase</span>
                    <span class="font-bold text-stone-900">{{ $purchaseYear }}</span>
                </div>
            @endif
            @if(!empty($usage))
                <div class="p-2.5 rounded-xl bg-stone-50 border border-stone-100">
                    <span class="text-[10px] text-stone-400 block font-semibold">Usage / Battery / KM</span>
                    <span class="font-bold text-stone-900">{{ $usage }}</span>
                </div>
            @endif
            <div class="p-2.5 rounded-xl bg-stone-50 border border-stone-100">
                <span class="text-[10px] text-stone-400 block font-semibold">Warranty Coverage</span>
                <span class="font-bold text-emerald-800">{{ $warranty }}</span>
            </div>
        </div>
    </div>

    <!-- 3. Dual Action CTAs: Instant Checkout & "Make an Offer" -->
    <div class="space-y-2.5">
        <button type="button" 
                @click="showOfferModal = true"
                class="w-full py-3.5 px-6 rounded-full bg-cyan-600 hover:bg-cyan-700 text-white text-xs sm:text-sm font-bold tracking-wide transition-all shadow-md flex items-center justify-center gap-2 cursor-pointer active:scale-95">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            <span>Make a Counter-Offer / Propose Price</span>
        </button>

        @if(!empty($sellerPhone))
            <a href="https://wa.me/{{ $sellerPhone }}?text={{ rawurlencode('Hello! I am inquiring about pre-owned: ' . $product->title . ' (₹' . number_format($product->base_price / 100, 2) . ') on K71. Can you share video/photos?') }}" 
               target="_blank"
               class="w-full py-2.5 px-5 rounded-full bg-stone-100 hover:bg-stone-200 text-stone-800 text-xs font-bold transition flex items-center justify-center gap-2">
                <span>💬 WhatsApp Seller for Live Video Proof</span>
            </a>
        @endif
    </div>

    <!-- 4. Counter-Offer Negotiation Modal Popup -->
    <div x-show="showOfferModal" 
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-sm"
         style="display: none;"
         @keydown.escape.window="showOfferModal = false">
        
        <div @click.away="showOfferModal = false"
             class="w-full max-w-lg bg-white rounded-3xl p-6 sm:p-7 shadow-2xl text-left text-stone-900 relative">
            
            <button type="button" @click="showOfferModal = false" 
                    class="absolute top-4 right-4 text-stone-400 hover:text-stone-700 p-2 rounded-full hover:bg-stone-100 transition">
                ✕
            </button>

            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 rounded-2xl bg-cyan-50 text-cyan-700 flex items-center justify-center text-xl font-bold border border-cyan-200">
                    🤝
                </div>
                <div>
                    <h3 class="text-base font-bold text-stone-900">Make an Offer to Seller</h3>
                    <p class="text-xs text-stone-500">Listed at ₹{{ number_format($product->base_price / 100, 2) }}</p>
                </div>
            </div>

            <form action="{{ route('classifieds.offer', $product->id) }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-stone-700 mb-1">Your Proposed Offer Amount (₹) *</label>
                    <div class="relative">
                        <span class="absolute left-3.5 top-2.5 text-stone-400 font-bold text-sm">₹</span>
                        <input type="number" step="1" name="offer_amount" x-model="offerPrice" required
                               class="w-full pl-8 pr-3 py-2.5 rounded-xl border border-stone-300 text-sm font-black text-stone-900">
                    </div>
                    <p class="text-[10px] text-stone-400 mt-1">Sellers typically respond to offers within 10-15% of the listed price.</p>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-stone-700 mb-1">Your Name *</label>
                        <input type="text" name="buyer_name" required value="{{ auth()->user()?->name ?? '' }}" placeholder="Your name" class="w-full px-3 py-2 rounded-xl border border-stone-300 text-xs">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-stone-700 mb-1">Phone Number *</label>
                        <input type="tel" name="buyer_phone" required value="{{ auth()->user()?->phone ?? '' }}" placeholder="10-digit mobile" class="w-full px-3 py-2 rounded-xl border border-stone-300 text-xs">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-stone-700 mb-1">Message for Seller (Optional)</label>
                    <textarea name="message" rows="2" placeholder="e.g. Ready for instant pickup or payment today..." class="w-full px-3 py-2 rounded-xl border border-stone-300 text-xs"></textarea>
                </div>

                <div class="pt-3 border-t border-stone-100 flex items-center justify-between">
                    <span class="text-xs text-stone-500">Direct notification sent to seller</span>
                    <button type="submit" class="px-6 py-2.5 rounded-full bg-cyan-600 hover:bg-cyan-700 text-white text-xs font-bold transition shadow-md active:scale-95 cursor-pointer">
                        Send Offer
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
