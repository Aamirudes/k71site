<x-layouts.vendor title="Buyer Offers & Negotiations">
    <div class="mb-8 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h1 class="text-2xl font-serif font-bold text-gray-900">Buyer Offers & Negotiations</h1>
            <p class="text-sm text-gray-500 mt-1">Review, accept, counter or decline price offers from buyers on your pre-owned items.</p>
        </div>
        <div>
            <a href="{{ route('vendor.products.create') }}" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-gray-900 hover:bg-black text-white text-xs font-bold transition shadow-sm">
                <span>+ List Pre-Owned Item</span>
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="mb-6 p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-semibold flex items-center gap-2">
            <span>✅</span>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if(session('error'))
        <div class="mb-6 p-4 rounded-xl bg-red-50 border border-red-200 text-red-800 text-xs font-semibold flex items-center gap-2">
            <span>⚠️</span>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    <!-- Status Tabs -->
    <div class="mb-6 flex items-center gap-2 overflow-x-auto no-scrollbar border-b border-gray-200 pb-3">
        @foreach(['all' => 'All Offers', 'pending' => 'Pending', 'accepted' => 'Accepted', 'countered' => 'Countered', 'rejected' => 'Declined'] as $tabKey => $tabLabel)
            <a href="{{ route('vendor.offers.index', ['status' => $tabKey]) }}" 
               class="px-4 py-2 rounded-xl text-xs font-bold transition whitespace-nowrap flex items-center gap-1.5 {{ $status === $tabKey ? 'bg-gray-900 text-white shadow-sm' : 'bg-white hover:bg-gray-50 text-gray-700 border border-gray-200' }}">
                <span>{{ $tabLabel }}</span>
                <span class="px-1.5 py-0.2 rounded-full text-[10px] {{ $status === $tabKey ? 'bg-white/20 text-white' : 'bg-gray-100 text-gray-600' }}">
                    {{ $counts[$tabKey] ?? 0 }}
                </span>
            </a>
        @endforeach
    </div>

    @if($offers->isEmpty())
        <div class="p-12 text-center rounded-2xl bg-white border border-gray-200">
            <div class="text-4xl mb-3">🤝</div>
            <h3 class="text-base font-bold text-gray-900 mb-1">No offers in this view</h3>
            <p class="text-xs text-gray-500">When interested buyers submit price offers on your listings, they will appear here instantly.</p>
        </div>
    @else
        <div class="space-y-4">
            @foreach($offers as $offer)
                @php
                    $product = $offer->product;
                    $askingPaise = $product?->base_price ?? 0;
                    $offerPaise = $offer->offer_amount;
                    $diffPct = $askingPaise > 0 ? round((($askingPaise - $offerPaise) / $askingPaise) * 100) : 0;
                @endphp
                <div class="bg-white rounded-2xl border border-gray-200 p-5 shadow-xs transition hover:border-gray-300">
                    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-5">
                        
                        <!-- Left: Item & Buyer info -->
                        <div class="flex items-start gap-4">
                            <!-- Thumbnail -->
                            <div class="w-16 h-16 sm:w-20 sm:h-20 rounded-xl bg-gray-100 border border-gray-200 overflow-hidden shrink-0">
                                @if($product)
                                    <img src="{{ $product->primary_image_url }}" alt="{{ $product->title }}" class="w-full h-full object-cover" onerror="this.onerror=null; this.src='{{ asset('images/product-placeholder.svg') }}';">
                                @else
                                    <div class="w-full h-full flex items-center justify-center text-2xl text-gray-400">🏷️</div>
                                @endif
                            </div>

                            <div class="space-y-1">
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="text-xs font-bold text-gray-500 uppercase tracking-wider">Offer #{{ $offer->id }}</span>
                                    @if($offer->isPending())
                                        <span class="px-2 py-0.5 rounded-full bg-amber-100 text-amber-800 text-[10px] font-bold">⏳ Awaiting Decision</span>
                                    @elseif($offer->isAccepted())
                                        <span class="px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-800 text-[10px] font-bold">✓ Accepted</span>
                                    @elseif($offer->isCountered())
                                        <span class="px-2 py-0.5 rounded-full bg-blue-100 text-blue-800 text-[10px] font-bold">⚡ Countered</span>
                                    @elseif($offer->isRejected())
                                        <span class="px-2 py-0.5 rounded-full bg-red-100 text-red-800 text-[10px] font-bold">✕ Declined</span>
                                    @endif

                                    @if($product && $product->isSold())
                                        <span class="px-2 py-0.5 rounded-full bg-red-600 text-white text-[10px] font-bold">ITEM SOLD</span>
                                    @endif
                                </div>

                                <h3 class="text-base font-bold text-gray-900 leading-snug">
                                    @if($product)
                                        <a href="{{ route('product.show', $product->slug) }}" target="_blank" class="hover:underline">
                                            {{ $product->title }}
                                        </a>
                                    @else
                                        <span>Product no longer available</span>
                                    @endif
                                </h3>

                                <!-- Buyer contact row -->
                                <div class="text-xs text-gray-600 flex flex-wrap items-center gap-y-1 gap-x-3 pt-1">
                                    <span class="font-semibold text-gray-800">👤 {{ $offer->buyer_name }}</span>
                                    <span>•</span>
                                    <a href="tel:{{ $offer->buyer_phone }}" class="text-gray-700 hover:text-black">📞 {{ $offer->buyer_phone }}</a>
                                    <span>•</span>
                                    <a href="https://wa.me/91{{ preg_replace('/[^0-9]/', '', $offer->buyer_phone) }}?text={{ rawurlencode('Namaste ' . $offer->buyer_name . '! Regarding your offer of ' . $offer->formattedOfferAmount() . ' for ' . ($product?->title ?? 'item') . ' on K71...') }}" 
                                       target="_blank" class="text-emerald-600 hover:text-emerald-700 font-bold flex items-center gap-1">
                                        <span>💬 WhatsApp</span>
                                    </a>
                                </div>

                                @if($offer->message)
                                    <p class="text-xs text-gray-600 italic bg-gray-50 p-2.5 rounded-lg border border-gray-100 mt-2">
                                        "{{ $offer->message }}"
                                    </p>
                                @endif
                            </div>
                        </div>

                        <!-- Right: Pricing Comparison & Action Forms -->
                        <div class="flex flex-col sm:flex-row lg:flex-col items-start lg:items-end justify-between gap-4 shrink-0 border-t lg:border-t-0 pt-3 lg:pt-0">
                            <!-- Price numbers -->
                            <div class="text-left lg:text-right">
                                <div class="text-[11px] text-gray-500">
                                    Asking: <span class="line-through">₹{{ number_format($askingPaise / 100, 2) }}</span>
                                    @if($diffPct > 0)
                                        <span class="text-rose-600 font-bold">({{ $diffPct }}% off)</span>
                                    @endif
                                </div>
                                <div class="text-2xl font-extrabold text-gray-900 font-mono">
                                    {{ $offer->formattedOfferAmount() }}
                                </div>
                                @if($offer->isCountered())
                                    <div class="text-xs text-blue-700 font-bold mt-0.5">
                                        Your Counter: {{ $offer->formattedCounterAmount() }}
                                    </div>
                                @endif
                            </div>

                            <!-- Decision Actions -->
                            @if($offer->isPending())
                                <div class="flex flex-wrap items-center gap-2" x-data="{ showCounterInput: false }">
                                    <!-- Accept -->
                                    <form action="{{ route('vendor.offers.respond', $offer) }}" method="POST">
                                        @csrf
                                        @method('PUT')
                                        <input type="hidden" name="action" value="accept">
                                        <button type="submit" 
                                                class="px-3.5 py-1.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-xs transition active:scale-95 cursor-pointer">
                                            ✓ Accept Offer
                                        </button>
                                    </form>

                                    <!-- Counter Toggle -->
                                    <button type="button" @click="showCounterInput = !showCounterInput" 
                                            class="px-3.5 py-1.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs shadow-xs transition active:scale-95 cursor-pointer">
                                        ⚡ Counter Offer
                                    </button>

                                    <!-- Reject -->
                                    <form action="{{ route('vendor.offers.respond', $offer) }}" method="POST">
                                        @csrf
                                        @method('PUT')
                                        <input type="hidden" name="action" value="reject">
                                        <button type="submit" 
                                                onclick="return confirm('Decline this offer?');"
                                                class="px-3 py-1.5 rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold text-xs transition active:scale-95 cursor-pointer">
                                            Decline
                                        </button>
                                    </form>

                                    <!-- Counter form dropdown -->
                                    <div x-show="showCounterInput" class="w-full mt-2 p-3 bg-blue-50 rounded-xl border border-blue-200" style="display: none;">
                                        <form action="{{ route('vendor.offers.respond', $offer) }}" method="POST" class="flex items-center gap-2">
                                            @csrf
                                            @method('PUT')
                                            <input type="hidden" name="action" value="counter">
                                            <input type="number" step="1" min="1" name="counter_amount" required 
                                                   placeholder="Counter amount ₹"
                                                   class="px-2.5 py-1.5 rounded-lg border border-gray-300 text-xs font-mono font-bold w-36 bg-white">
                                            <button type="submit" class="px-3 py-1.5 rounded-lg bg-blue-600 text-white text-xs font-bold hover:bg-blue-700">
                                                Send Counter
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            @elseif($product && !$product->isSold() && $offer->isAccepted())
                                <!-- Mark Item Sold CTA -->
                                <form action="{{ route('vendor.products.mark_sold', $product) }}" method="POST">
                                    @csrf
                                    <button type="submit" onclick="return confirm('Mark this product as SOLD? This will close all active offers.');"
                                            class="px-3 py-1.5 rounded-xl bg-gray-900 hover:bg-black text-white text-xs font-bold shadow-xs transition">
                                        Mark Item as Sold
                                    </button>
                                </form>
                            @endif

                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="mt-6">
            {{ $offers->links() }}
        </div>
    @endif
</x-layouts.vendor>
