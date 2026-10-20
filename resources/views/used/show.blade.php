<x-layouts.public 
    :title="$product->title . ' — ₹ ' . number_format($product->base_price / 100) . ' in ' . ($product->custom_fields['location'] ?? 'Kishangarh, Rajasthan')"
    :metaDescription="Str::limit($product->description, 160)"
>
    @php
        $custom = $product->custom_fields ?? [];
        $badge = $custom['badge'] ?? 'ELITE';
        $location = $custom['location'] ?? ($product->vendor->city ?? 'Kishangarh, Rajasthan');
        $adId = $custom['ad_id'] ?? ('1857' . $product->id);
        $views = $custom['views_past_hour'] ?? 6;
        $specs = $custom['specs'] ?? [];
        $sellerName = $product->vendor->shop_name ?? 'Yusuf khan';
        $sellerPhone = $product->vendor->phone ?? '+919549311580';
        $dealLabel = $custom['deal_label'] ?? 'Fair Deal';
    @endphp

    <div x-data="{
        galleryIndex: 0,
        offerModal: false,
        offerAmount: '{{ round(($product->base_price / 100) * 0.9) }}',
        chatDrawer: false,
        chatMessages: [
            { sender: 'seller', text: 'Namaste! Yes, this {{ $product->title }} is available for immediate inspection in {{ $location }}.', time: '10:45 AM' }
        ],
        newChatMessage: '',
        sendChatMessage() {
            if (!this.newChatMessage.trim()) return;
            this.chatMessages.push({ sender: 'buyer', text: this.newChatMessage, time: 'Just now' });
            this.newChatMessage = '';
            setTimeout(() => {
                this.chatMessages.push({ sender: 'seller', text: 'Price is negotiable on table. Please give me a call at {{ $sellerPhone }}.', time: 'Just now' });
            }, 800);
        }
    }" class="max-w-2xl mx-auto px-2 sm:px-4 pb-28 pt-2">

        <!-- Top Navigation Strip: Back + Share -->
        <div class="flex items-center justify-between mb-3 px-1">
            <a href="{{ route('used.index') }}" class="p-2 -ml-2 rounded-full hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-200">
                &larr; Back to Classifieds
            </a>
            <button type="button" @click="navigator.clipboard.writeText(window.location.href); if(window.showToast) window.showToast('Link copied!');" class="p-2 rounded-full hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-200">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"/></svg>
            </button>
        </div>

        <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800 shadow-xl overflow-hidden">
            <!-- Image Carousel -->
            <div class="relative w-full aspect-[4/3] bg-black">
                @if($product->images->count() > 0)
                    <img :src="'{{ $product->images->first()->path }}'" alt="{{ $product->title }}" class="w-full h-full object-contain">
                @else
                    <div class="w-full h-full flex items-center justify-center text-slate-500">No Image</div>
                @endif
                <div class="absolute bottom-3 right-3 px-2.5 py-1 rounded-md bg-black/60 text-white text-xs font-mono font-bold">
                    1/{{ max($product->images->count(), 1) }}
                </div>
            </div>

            <!-- View Count Banner -->
            <div class="bg-slate-900 text-white px-4 py-2 flex items-center gap-2 text-xs font-medium">
                <span>👁</span>
                <span><strong>{{ $views }}</strong> views in past hour - message the seller now!</span>
            </div>

            <!-- Header Info -->
            <div class="p-4 border-b border-slate-100 dark:border-slate-800 space-y-2">
                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded bg-[#0600c2] text-white text-[10px] font-black uppercase tracking-wider">
                    👑 {{ $badge }}
                </span>
                <h1 class="text-lg sm:text-xl font-black text-slate-950 dark:text-white">{{ $product->title }}</h1>
                <div class="flex items-center gap-3 pt-1">
                    <div class="text-2xl font-black text-slate-950 dark:text-white">₹ {{ number_format($product->base_price / 100) }}</div>
                    <span class="px-2.5 py-0.5 rounded-full bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300 font-extrabold text-xs">₹ {{ $dealLabel }} ℹ️</span>
                </div>
                <div class="flex items-center justify-between text-xs text-slate-500 pt-1">
                    <span>📍 {{ $location }}</span>
                    <span>Posted On 20 Oct '26</span>
                </div>
            </div>

            <!-- Product Specs -->
            @if(!empty($specs))
                <div class="p-4 border-b border-slate-100 dark:border-slate-800">
                    <h3 class="text-xs font-extrabold text-slate-900 dark:text-white mb-2">Product Information</h3>
                    <div class="grid grid-cols-2 gap-2 bg-slate-50 dark:bg-slate-800/50 p-3 rounded-2xl text-xs">
                        @foreach($specs as $k => $v)
                            <div>
                                <span class="text-slate-400 font-medium">{{ $k }}</span>
                                <div class="font-bold text-slate-800 dark:text-slate-200">{{ $v }}</div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Description -->
            <div class="p-4 border-b border-slate-100 dark:border-slate-800">
                <h3 class="text-xs font-extrabold text-slate-900 dark:text-white mb-1.5">Description</h3>
                <p class="text-xs text-slate-600 dark:text-slate-300 whitespace-pre-line leading-relaxed">{{ $product->description }}</p>
            </div>

            <!-- Seller Card -->
            <div class="p-4 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 rounded-full bg-blue-600 text-white font-black text-sm flex items-center justify-center">
                        {{ strtoupper(substr($sellerName, 0, 2)) }}
                    </div>
                    <div>
                        <div class="text-[10px] text-slate-400">Posted by</div>
                        <div class="text-sm font-black text-slate-900 dark:text-white">{{ $sellerName }}</div>
                        <div class="text-[10px] text-blue-600 font-bold">👑 ELITE SELLER ℹ️</div>
                    </div>
                </div>
                <div class="text-right text-xs">
                    <div class="font-black text-slate-900 dark:text-white">8748</div>
                    <div class="text-[10px] text-slate-400">Items listed</div>
                </div>
            </div>

            <!-- Ad ID -->
            <div class="p-4 flex items-center justify-between text-xs text-slate-400">
                <span>AD ID: <strong class="text-slate-700 dark:text-slate-300">{{ $adId }}</strong></span>
                <button type="button" @click="if(window.showToast) window.showToast('Report submitted')" class="text-red-500 font-bold">Report AD</button>
            </div>
        </div>

        <!-- Floating Bottom Action Bar -->
        <div class="fixed bottom-0 inset-x-0 z-30 max-w-2xl mx-auto bg-white dark:bg-slate-950 border-t border-slate-200 dark:border-slate-800 p-3 shadow-2xl">
            <div class="text-center text-[11px] font-bold text-amber-600 dark:text-amber-400 mb-2">
                📞 Call directly to the Elite Seller of this product
            </div>
            <div class="grid grid-cols-2 gap-3">
                <a href="tel:{{ $sellerPhone }}" class="py-3 rounded-xl bg-[#0052cc] text-white font-extrabold text-xs flex items-center justify-center gap-2 shadow-md">
                    <span>📞 Call Seller</span>
                </a>
                <a href="https://wa.me/919549311580?text={{ urlencode('Hi Yusuf, I am interested in your ' . $product->title . ' on K71 Used.') }}" target="_blank" class="py-3 rounded-xl bg-emerald-600 text-white font-extrabold text-xs flex items-center justify-center gap-2 shadow-md">
                    <span>💬 WhatsApp</span>
                </a>
            </div>
        </div>

    </div>
</x-layouts.public>
