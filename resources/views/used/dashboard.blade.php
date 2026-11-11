<x-layouts.used
    title="Used Seller & Buyer Dashboard | K71 Used"
    robotsContent="noindex,nofollow"
>
    @php
        $tabs = [
            'overview' => 'Overview',
            'listings' => 'Listings (' . $stats['total_listings'] . ')',
            'offers' => 'Offers (' . ($receivedOffers->count() + $sentOffers->count()) . ')',
            'messages' => 'Enquiries (' . ($sellerThreads->count() + $buyerThreads->count()) . ')',
            'transactions' => 'Handovers (' . ($sellerTransactions->count() + $buyerTransactions->count()) . ')',
            'analytics' => 'Analytics',
            'buyer' => 'Saved & Alerts (' . ($savedListings->count() + $savedSearches->count()) . ')',
            'settings' => 'Profile & Privacy',
        ];
    @endphp

    <div class="bg-black text-[#EFE9F2]">
        <!-- Top Dashboard Header -->
        <section class="grail-hero-band py-7 px-3 sm:px-6 lg:px-8 border-b border-white/10">
            <div class="max-w-7xl mx-auto space-y-5">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div>
                        <span class="grail-eyebrow block mb-1">Private Workspace • {{ '@' . $profile->handle }}</span>
                        <h1 class="text-2xl sm:text-4xl font-light text-white" style="font-family: var(--font-grail-display);">
                            K71 Used Dashboard
                        </h1>
                        <p class="text-xs text-[#EFE9F2]/65 mt-1">
                            Manage listings, negotiate offers, coordinate handovers, and configure seller privacy.
                        </p>
                    </div>
                    <div class="flex flex-wrap items-center gap-2 shrink-0">
                        <a href="{{ route('used.seller.profile', $profile->handle) }}" class="px-4 py-2.5 rounded-full bg-white/10 hover:bg-white/15 border border-white/15 text-xs font-semibold text-white transition">
                            👤 Public Seller Profile ↗
                        </a>
                        <a href="{{ route('used.sell') }}" class="px-5 py-2.5 grail-cta-pill text-xs font-bold">
                            + Sell an Item
                        </a>
                    </div>
                </div>

                <!-- Navigation Tabs -->
                <div class="flex items-center gap-1.5 overflow-x-auto no-scrollbar pb-1">
                    @foreach($tabs as $tKey => $tLabel)
                        <a href="{{ route('used.dashboard', ['tab' => $tKey]) }}"
                           class="px-4 py-2 rounded-full text-xs font-bold transition shrink-0 {{ $tab === $tKey ? 'bg-[#ECD2F3] text-[#1A1018]' : 'bg-white/[0.06] text-[#EFE9F2]/80 hover:bg-white/[0.14] border border-white/15' }}">
                            {{ $tLabel }}
                        </a>
                    @endforeach
                </div>
            </div>
        </section>

        <!-- Content Sheet -->
        <div class="grail-sheet bg-[#F6F6F6] text-[#1A1018] rounded-t-[28px] sm:rounded-t-[36px] py-7 px-3 sm:px-6 lg:px-8 min-h-screen shadow-[0_-18px_50px_rgba(0,0,0,0.45)]">
            <div class="max-w-7xl mx-auto space-y-6">

                @if(session('success'))
                    <div class="p-3.5 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-900 text-xs font-semibold">{{ session('success') }}</div>
                @endif
                @if(session('error'))
                    <div class="p-3.5 rounded-2xl bg-rose-50 border border-rose-200 text-rose-900 text-xs font-semibold">{{ session('error') }}</div>
                @endif
                @if($errors->any())
                    <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-xs">
                        <ul class="list-disc list-inside">
                            @foreach($errors->all() as $err)
                                <li>{{ $err }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <!-- ================= TAB 1: OVERVIEW ================= -->
                @if($tab === 'overview')
                    <div class="grid grid-cols-2 md:grid-cols-5 gap-3 sm:gap-4">
                        <div class="grail-card bg-white p-4 rounded-2xl border border-[#E6E2E8]">
                            <div class="text-[11px] uppercase font-semibold text-[#1A1018]/55">Active Ads</div>
                            <div class="text-2xl font-bold text-[#1A1018] mt-1">{{ $stats['active_listings'] }}</div>
                            <div class="text-[10px] text-[#1A1018]/50">{{ $stats['draft_listings'] }} drafts • {{ $stats['reserved_listings'] }} reserved</div>
                        </div>
                        <div class="grail-card bg-white p-4 rounded-2xl border border-[#E6E2E8]">
                            <div class="text-[11px] uppercase font-semibold text-[#1A1018]/55">Pending Offers</div>
                            <div class="text-2xl font-bold text-[#623562] mt-1">{{ $stats['pending_offers'] }}</div>
                            <div class="text-[10px] text-[#1A1018]/50">Awaiting response</div>
                        </div>
                        <div class="grail-card bg-white p-4 rounded-2xl border border-[#E6E2E8]">
                            <div class="text-[11px] uppercase font-semibold text-[#1A1018]/55">Unread Messages</div>
                            <div class="text-2xl font-bold text-[#1A1018] mt-1">{{ $stats['unread_messages'] }}</div>
                            <div class="text-[10px] text-[#1A1018]/50">{{ $stats['total_enquiries'] }} buyer threads</div>
                        </div>
                        <div class="grail-card bg-white p-4 rounded-2xl border border-[#E6E2E8]">
                            <div class="text-[11px] uppercase font-semibold text-[#1A1018]/55">Genuine Views</div>
                            <div class="text-2xl font-bold text-[#1A1018] mt-1">{{ $stats['total_views'] }}</div>
                            <div class="text-[10px] text-[#1A1018]/50">{{ $stats['total_saves'] }} buyer saves</div>
                        </div>
                        <div class="grail-card bg-white p-4 rounded-2xl border border-[#E6E2E8]">
                            <div class="text-[11px] uppercase font-semibold text-[#1A1018]/55">Completed Deals</div>
                            <div class="text-2xl font-bold text-emerald-700 mt-1">{{ $profile->sold_count }}</div>
                            <div class="text-[10px] text-[#1A1018]/50">{{ $profile->reviews_count > 0 ? '★ ' . number_format($profile->avg_rating, 1) . ' avg rating' : 'No ratings yet' }}</div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <div class="grail-card bg-white p-5 rounded-3xl border border-[#E6E2E8] space-y-3">
                            <div class="flex items-center justify-between">
                                <h2 class="text-lg font-medium text-[#1A1018]" style="font-family: var(--font-grail-display);">Recent Incoming Offers</h2>
                                <a href="{{ route('used.dashboard', ['tab' => 'offers']) }}" class="text-xs font-bold text-[#623562] hover:underline">Manage All →</a>
                            </div>
                            @forelse($receivedOffers->take(4) as $off)
                                <div class="p-3 rounded-2xl bg-[#FAFAF8] border border-black/8 flex items-center justify-between gap-2 text-xs">
                                    <div class="min-w-0">
                                        <div class="font-bold truncate">{{ $off->product?->title }}</div>
                                        <div class="text-[#1A1018]/65">{{ $off->buyer_name }} offered <strong class="text-[#623562]">{{ $off->formattedOfferAmount() }}</strong></div>
                                    </div>
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase bg-[#F8EFFA] text-[#623562] shrink-0">{{ $off->status }}</span>
                                </div>
                            @empty
                                <p class="text-xs text-[#1A1018]/55 py-4">No incoming buyer offers yet.</p>
                            @endforelse
                        </div>

                        <div class="grail-card bg-white p-5 rounded-3xl border border-[#E6E2E8] space-y-3">
                            <div class="flex items-center justify-between">
                                <h2 class="text-lg font-medium text-[#1A1018]" style="font-family: var(--font-grail-display);">Active Handovers &amp; Enquiries</h2>
                                <a href="{{ route('used.dashboard', ['tab' => 'transactions']) }}" class="text-xs font-bold text-[#623562] hover:underline">View Handovers →</a>
                            </div>
                            @forelse($sellerTransactions->merge($buyerTransactions)->take(4) as $tx)
                                <div class="p-3 rounded-2xl bg-[#FAFAF8] border border-black/8 flex items-center justify-between gap-2 text-xs">
                                    <div class="min-w-0">
                                        <div class="font-bold truncate">{{ $tx->product?->title }}</div>
                                        <div class="text-[#1A1018]/65">{{ $tx->transaction_number }} • {{ $tx->formatted_price }}</div>
                                    </div>
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase {{ $tx->isCompleted() ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }} shrink-0">{{ $tx->status }}</span>
                                </div>
                            @empty
                                <p class="text-xs text-[#1A1018]/55 py-4">No reserved or completed handovers yet.</p>
                            @endforelse
                        </div>
                    </div>

                <!-- ================= TAB 2: LISTINGS MANAGER ================= -->
                @elseif($tab === 'listings')
                    <div class="grail-card bg-white p-5 sm:p-6 rounded-3xl border border-[#E6E2E8] space-y-5">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                            <div class="flex flex-wrap items-center gap-1.5 text-xs">
                                @foreach(['all' => 'All', 'active' => 'Active', 'draft' => 'Drafts', 'paused' => 'Paused', 'reserved' => 'Reserved', 'sold' => 'Sold'] as $sKey => $sLabel)
                                    <a href="{{ route('used.dashboard', ['tab' => 'listings', 'status' => $sKey]) }}"
                                       class="px-3.5 py-1.5 rounded-full font-bold transition {{ $statusFilter === $sKey ? 'bg-[#1A1018] text-[#ECD2F3]' : 'bg-[#FAFAF8] border border-black/10 text-[#1A1018]/75 hover:bg-[#F8EFFA]' }}">
                                        {{ $sLabel }}
                                    </a>
                                @endforeach
                            </div>
                            <a href="{{ route('used.sell') }}" class="px-4 py-2 grail-btn-obsidian text-xs font-bold shrink-0">+ New Listing</a>
                        </div>

                        @if($listings->isEmpty())
                            <div class="py-12 text-center text-xs text-[#1A1018]/60">No listings found under this filter.</div>
                        @else
                            <div class="space-y-3">
                                @foreach($listings as $item)
                                    @php $eff = $item->effectiveUsedStatus(); @endphp
                                    <div class="p-4 rounded-2xl border border-black/10 bg-[#FAFAF8] flex flex-col lg:flex-row lg:items-center justify-between gap-4">
                                        <div class="flex items-center gap-3.5 min-w-0">
                                            <div class="w-16 h-16 rounded-xl bg-[#141115] overflow-hidden shrink-0 flex items-center justify-center">
                                                @if($item->hasUploadedImages())
                                                    <img src="{{ $item->images->first()->url }}" alt="{{ $item->title }}" class="w-full h-full object-cover">
                                                @else
                                                    <span class="text-lg opacity-60">📷</span>
                                                @endif
                                            </div>
                                            <div class="min-w-0">
                                                <div class="flex flex-wrap items-center gap-1.5">
                                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-black uppercase {{ $eff === 'sold' ? 'bg-rose-100 text-rose-800' : ($eff === 'reserved' ? 'bg-amber-100 text-amber-800' : ($eff === 'active' ? 'bg-emerald-100 text-emerald-800' : 'bg-black/10 text-[#1A1018]')) }}">
                                                        {{ strtoupper($eff) }}
                                                    </span>
                                                    <span class="text-[11px] text-[#1A1018]/55">{{ $item->category?->name }}</span>
                                                </div>
                                                <a href="{{ route('used.show', $item->slug) }}" class="text-sm font-bold text-[#1A1018] hover:text-[#623562] truncate block mt-0.5">
                                                    {{ $item->title }}
                                                </a>
                                                <div class="text-xs text-[#623562] font-black mt-0.5">
                                                    ₹ {{ number_format($item->base_price / 100) }}
                                                    <span class="text-[11px] font-normal text-[#1A1018]/55 ml-2">
                                                        👁 {{ $item->views_count }} views • 🔖 {{ $item->saves_count }} saves • 🤝 {{ $item->pending_offers_count ?? 0 }} offers • 💬 {{ $item->enquiries_count ?? 0 }} chats
                                                    </span>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="flex flex-wrap items-center gap-2 shrink-0 text-xs">
                                            <a href="{{ route('used.edit', $item->id) }}" class="px-3.5 py-1.5 rounded-full bg-white border border-black/15 font-bold hover:bg-[#F8EFFA]">Edit</a>

                                            @if(in_array($eff, ['draft', 'paused', 'reserved'], true))
                                                <form action="{{ route('used.status', $item->id) }}" method="POST">
                                                    @csrf
                                                    <input type="hidden" name="action" value="publish">
                                                    <button type="submit" class="px-3.5 py-1.5 rounded-full bg-emerald-600 text-white font-bold cursor-pointer">
                                                        {{ $eff === 'draft' ? 'Publish' : 'Activate' }}
                                                    </button>
                                                </form>
                                            @endif

                                            @if($eff === 'active')
                                                <form action="{{ route('used.status', $item->id) }}" method="POST">
                                                    @csrf
                                                    <input type="hidden" name="action" value="bump">
                                                    <button type="submit" class="px-3.5 py-1.5 rounded-full bg-white border border-black/15 font-bold hover:bg-[#F8EFFA] cursor-pointer">↑ Refresh</button>
                                                </form>
                                                <form action="{{ route('used.status', $item->id) }}" method="POST">
                                                    @csrf
                                                    <input type="hidden" name="action" value="pause">
                                                    <button type="submit" class="px-3.5 py-1.5 rounded-full bg-amber-50 border border-amber-200 text-amber-900 font-bold cursor-pointer">Pause</button>
                                                </form>
                                            @endif

                                            @if($eff !== 'sold')
                                                <form action="{{ route('used.status', $item->id) }}" method="POST">
                                                    @csrf
                                                    <input type="hidden" name="action" value="mark_sold">
                                                    <button type="submit" class="px-3.5 py-1.5 rounded-full bg-[#1A1018] text-[#ECD2F3] font-bold cursor-pointer">Mark Sold</button>
                                                </form>
                                            @endif

                                            <form action="{{ route('used.delete', $item->id) }}" method="POST" onsubmit="return confirm('Archive and remove this listing? Conversation history stays preserved.');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="px-3 py-1.5 rounded-full bg-rose-50 border border-rose-200 text-rose-700 font-bold cursor-pointer">Delete</button>
                                            </form>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>

                <!-- ================= TAB 3: OFFERS & NEGOTIATIONS ================= -->
                @elseif($tab === 'offers')
                    <div class="space-y-6">
                        <!-- Seller Received Offers -->
                        <div class="grail-card bg-white p-5 sm:p-6 rounded-3xl border border-[#E6E2E8] space-y-4">
                            <h2 class="text-xl font-medium text-[#1A1018]" style="font-family: var(--font-grail-display);">Offers Received on Your Items ({{ $receivedOffers->count() }})</h2>
                            @forelse($receivedOffers as $offer)
                                <div class="p-4 rounded-2xl border border-black/10 bg-[#FAFAF8] space-y-3 text-xs">
                                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                                        <div>
                                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase {{ $offer->isAccepted() ? 'bg-emerald-100 text-emerald-800' : ($offer->isCountered() ? 'bg-amber-100 text-amber-800' : 'bg-black/10 text-[#1A1018]') }}">
                                                {{ strtoupper($offer->status) }}
                                            </span>
                                            <a href="{{ $offer->product ? route('used.show', $offer->product->slug) : '#' }}" class="font-bold text-sm text-[#1A1018] ml-2 hover:text-[#623562]">
                                                {{ $offer->product?->title }}
                                            </a>
                                        </div>
                                        <div class="text-[#1A1018]/60">{{ $offer->created_at?->diffForHumans() }}</div>
                                    </div>

                                    <div class="flex flex-wrap items-center gap-4">
                                        <div>Buyer: <strong>{{ $offer->buyer_name }}</strong></div>
                                        <div>Offer: <strong class="text-sm text-[#623562]">{{ $offer->formattedOfferAmount() }}</strong></div>
                                        @if($offer->product)
                                            <div>Asking: ₹{{ number_format($offer->product->base_price / 100) }}</div>
                                        @endif
                                        @if($offer->counter_amount)
                                            <div>Your Counter: <strong>₹{{ number_format($offer->counter_amount / 100, 2) }}</strong></div>
                                        @endif
                                    </div>

                                    @if($offer->message)
                                        <p class="p-2.5 rounded-xl bg-white border border-black/5 text-[#1A1018]/80">"{{ $offer->message }}"</p>
                                    @endif

                                    @if(in_array($offer->status, ['pending', 'countered'], true))
                                        <div class="pt-2 border-t border-black/8 flex flex-wrap items-end gap-2">
                                            <form action="{{ route('used.offers.seller_respond', $offer) }}" method="POST">
                                                @csrf
                                                <input type="hidden" name="action" value="accept">
                                                <button type="submit" class="px-4 py-2 rounded-full bg-emerald-600 hover:bg-emerald-700 text-white font-bold cursor-pointer">
                                                    ✓ Accept &amp; Reserve Item
                                                </button>
                                            </form>

                                            <form action="{{ route('used.offers.seller_respond', $offer) }}" method="POST" class="flex flex-wrap items-center gap-1.5">
                                                @csrf
                                                <input type="hidden" name="action" value="counter">
                                                <input type="number" name="counter_amount" required min="1" placeholder="Counter ₹" class="grail-input-light w-28 px-2.5 py-1.5 text-xs font-bold">
                                                <input type="text" name="vendor_notes" placeholder="Note (optional)" class="grail-input-light w-36 px-2.5 py-1.5 text-xs">
                                                <button type="submit" class="px-3.5 py-2 rounded-full bg-[#1A1018] text-[#ECD2F3] font-bold cursor-pointer">Counter</button>
                                            </form>

                                            <form action="{{ route('used.offers.seller_respond', $offer) }}" method="POST">
                                                @csrf
                                                <input type="hidden" name="action" value="reject">
                                                <button type="submit" class="px-3.5 py-2 rounded-full bg-rose-50 text-rose-700 border border-rose-200 font-bold cursor-pointer">Decline</button>
                                            </form>
                                        </div>
                                    @endif
                                </div>
                            @empty
                                <p class="text-xs text-[#1A1018]/60 py-4">No offers received yet.</p>
                            @endforelse
                        </div>

                        <!-- Buyer Sent Offers -->
                        <div class="grail-card bg-white p-5 sm:p-6 rounded-3xl border border-[#E6E2E8] space-y-4">
                            <h2 class="text-xl font-medium text-[#1A1018]" style="font-family: var(--font-grail-display);">Offers You Made ({{ $sentOffers->count() }})</h2>
                            @forelse($sentOffers as $sOffer)
                                <div class="p-4 rounded-2xl border border-black/10 bg-[#FAFAF8] flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-xs">
                                    <div>
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase bg-[#F8EFFA] text-[#623562]">{{ strtoupper($sOffer->status) }}</span>
                                        <a href="{{ $sOffer->product ? route('used.show', $sOffer->product->slug) : '#' }}" class="font-bold text-sm text-[#1A1018] ml-1.5 hover:text-[#623562]">
                                            {{ $sOffer->product?->title }}
                                        </a>
                                        <div class="mt-1 text-[#1A1018]/75">
                                            Your offer: <strong>{{ $sOffer->formattedOfferAmount() }}</strong>
                                            @if($sOffer->counter_amount)
                                                • Seller Countered: <strong class="text-[#623562]">₹{{ number_format($sOffer->counter_amount / 100, 2) }}</strong>
                                            @endif
                                        </div>
                                        @if($sOffer->vendor_notes)
                                            <div class="text-[#1A1018]/65 mt-0.5">Seller note: "{{ $sOffer->vendor_notes }}"</div>
                                        @endif
                                    </div>
                                    <div class="flex flex-wrap items-center gap-2 shrink-0">
                                        @if($sOffer->isCountered())
                                            <form action="{{ route('used.offers.respond_counter', $sOffer) }}" method="POST">
                                                @csrf
                                                <input type="hidden" name="action" value="accept_counter">
                                                <button type="submit" class="px-3.5 py-1.5 rounded-full bg-emerald-600 text-white font-bold cursor-pointer">Accept Counter</button>
                                            </form>
                                            <form action="{{ route('used.offers.respond_counter', $sOffer) }}" method="POST">
                                                @csrf
                                                <input type="hidden" name="action" value="reject_counter">
                                                <button type="submit" class="px-3.5 py-1.5 rounded-full bg-white border border-black/15 font-bold cursor-pointer">Decline</button>
                                            </form>
                                        @endif
                                        @if(in_array($sOffer->status, ['pending', 'countered'], true))
                                            <form action="{{ route('used.offers.withdraw', $sOffer) }}" method="POST">
                                                @csrf
                                                <button type="submit" class="px-3.5 py-1.5 rounded-full bg-rose-50 text-rose-700 border border-rose-200 font-bold cursor-pointer">Withdraw</button>
                                            </form>
                                        @endif
                                    </div>
                                </div>
                            @empty
                                <p class="text-xs text-[#1A1018]/60 py-4">You haven't made any offers yet.</p>
                            @endforelse
                        </div>
                    </div>

                <!-- ================= TAB 4: MESSAGES & ENQUIRIES ================= -->
                @elseif($tab === 'messages')
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <div class="grail-card bg-white p-5 rounded-3xl border border-[#E6E2E8] space-y-3">
                            <h2 class="text-lg font-medium text-[#1A1018]" style="font-family: var(--font-grail-display);">Buyer Enquiries on Your Listings ({{ $sellerThreads->count() }})</h2>
                            @forelse($sellerThreads as $th)
                                @php $last = $th->messages->last(); @endphp
                                <a href="{{ route('used.threads.show', $th) }}" class="block p-3.5 rounded-2xl bg-[#FAFAF8] hover:bg-[#F8EFFA] border border-black/10 transition text-xs space-y-1">
                                    <div class="flex items-center justify-between gap-2">
                                        <span class="font-bold text-[#1A1018]">{{ $th->buyer?->name ?? 'Buyer' }}</span>
                                        @if($th->unread_count > 0)
                                            <span class="px-2 py-0.5 rounded-full bg-[#623562] text-white text-[10px] font-bold">{{ $th->unread_count }} new</span>
                                        @endif
                                    </div>
                                    <div class="font-semibold text-[#623562] truncate">{{ $th->product?->title }}</div>
                                    @if($last)
                                        <p class="text-[#1A1018]/65 truncate">{{ $last->body }}</p>
                                    @endif
                                </a>
                            @empty
                                <p class="text-xs text-[#1A1018]/55 py-4">No buyer enquiries on your listings yet.</p>
                            @endforelse
                        </div>

                        <div class="grail-card bg-white p-5 rounded-3xl border border-[#E6E2E8] space-y-3">
                            <h2 class="text-lg font-medium text-[#1A1018]" style="font-family: var(--font-grail-display);">Your Enquiries to Sellers ({{ $buyerThreads->count() }})</h2>
                            @forelse($buyerThreads as $bTh)
                                @php $bLast = $bTh->messages->last(); @endphp
                                <a href="{{ route('used.threads.show', $bTh) }}" class="block p-3.5 rounded-2xl bg-[#FAFAF8] hover:bg-[#F8EFFA] border border-black/10 transition text-xs space-y-1">
                                    <div class="flex items-center justify-between gap-2">
                                        <span class="font-bold text-[#1A1018]">{{ $bTh->seller?->usedSellerProfile?->display_name ?? $bTh->seller?->name ?? 'Seller' }}</span>
                                        @if($bTh->unread_count > 0)
                                            <span class="px-2 py-0.5 rounded-full bg-[#623562] text-white text-[10px] font-bold">{{ $bTh->unread_count }} new</span>
                                        @endif
                                    </div>
                                    <div class="font-semibold text-[#623562] truncate">{{ $bTh->product?->title }}</div>
                                    @if($bLast)
                                        <p class="text-[#1A1018]/65 truncate">{{ $bLast->body }}</p>
                                    @endif
                                </a>
                            @empty
                                <p class="text-xs text-[#1A1018]/55 py-4">You haven't contacted any sellers yet.</p>
                            @endforelse
                        </div>
                    </div>

                <!-- ================= TAB 5: HANDOVERS & TRANSACTIONS ================= -->
                @elseif($tab === 'transactions')
                    <div class="space-y-6">
                        <div class="grail-card bg-white p-5 sm:p-6 rounded-3xl border border-[#E6E2E8] space-y-4">
                            <div>
                                <h2 class="text-xl font-medium text-[#1A1018]" style="font-family: var(--font-grail-display);">Seller Handovers ({{ $sellerTransactions->count() }})</h2>
                                <p class="text-xs text-[#1A1018]/60">Direct peer handover tracker (no automated escrow or shipping charges). Mark complete once item &amp; payment change hands.</p>
                            </div>
                            @forelse($sellerTransactions as $tx)
                                <div class="p-4 rounded-2xl bg-[#FAFAF8] border border-black/10 flex flex-col md:flex-row md:items-center justify-between gap-3 text-xs">
                                    <div class="space-y-1">
                                        <div class="font-bold text-sm text-[#1A1018]">
                                            {{ $tx->product?->title }}
                                            <span class="ml-2 px-2 py-0.5 rounded-full text-[10px] uppercase {{ $tx->isCompleted() ? 'bg-emerald-100 text-emerald-800' : ($tx->isCancelled() ? 'bg-rose-100 text-rose-800' : 'bg-amber-100 text-amber-800') }}">{{ strtoupper($tx->status) }}</span>
                                        </div>
                                        <div>Ref: <strong>{{ $tx->transaction_number }}</strong> • Buyer: <strong>{{ $tx->buyer?->name }}</strong> • Agreed Price: <strong class="text-[#623562]">{{ $tx->formatted_price }}</strong></div>
                                    </div>
                                    @if($tx->isReserved())
                                        <div class="flex flex-wrap items-center gap-2 shrink-0">
                                            <form action="{{ route('used.transactions.complete', $tx) }}" method="POST">
                                                @csrf
                                                <button type="submit" class="px-4 py-2 rounded-full bg-emerald-600 text-white font-bold cursor-pointer">✓ Complete Handover &amp; Mark Sold</button>
                                            </form>
                                            <form action="{{ route('used.transactions.cancel', $tx) }}" method="POST">
                                                @csrf
                                                <button type="submit" class="px-3.5 py-2 rounded-full bg-white border border-black/15 font-bold cursor-pointer">Cancel Reservation</button>
                                            </form>
                                        </div>
                                    @endif
                                </div>
                            @empty
                                <p class="text-xs text-[#1A1018]/55">No seller transactions recorded yet.</p>
                            @endforelse
                        </div>

                        <div class="grail-card bg-white p-5 sm:p-6 rounded-3xl border border-[#E6E2E8] space-y-4">
                            <h2 class="text-xl font-medium text-[#1A1018]" style="font-family: var(--font-grail-display);">Your Purchases &amp; Reservations ({{ $buyerTransactions->count() }})</h2>
                            @forelse($buyerTransactions as $bTx)
                                <div class="p-4 rounded-2xl bg-[#FAFAF8] border border-black/10 space-y-3 text-xs">
                                    <div class="flex flex-col md:flex-row md:items-center justify-between gap-2">
                                        <div>
                                            <div class="font-bold text-sm text-[#1A1018]">
                                                {{ $bTx->product?->title }}
                                                <span class="ml-2 px-2 py-0.5 rounded-full text-[10px] uppercase {{ $bTx->isCompleted() ? 'bg-emerald-100 text-emerald-800' : ($bTx->isCancelled() ? 'bg-rose-100 text-rose-800' : 'bg-amber-100 text-amber-800') }}">{{ strtoupper($bTx->status) }}</span>
                                            </div>
                                            <div>Ref: <strong>{{ $bTx->transaction_number }}</strong> • Seller: <strong>{{ $bTx->seller?->usedSellerProfile?->display_name ?? $bTx->seller?->name }}</strong> • Agreed Price: <strong>{{ $bTx->formatted_price }}</strong></div>
                                            @if($bTx->private_pickup_location)
                                                <div class="mt-1 text-emerald-900 font-semibold">📍 Private Pickup Area: {{ $bTx->private_pickup_location }}</div>
                                            @endif
                                        </div>
                                        @if($bTx->isReserved())
                                            <form action="{{ route('used.transactions.cancel', $bTx) }}" method="POST">
                                                @csrf
                                                <button type="submit" class="px-3.5 py-2 rounded-full bg-white border border-black/15 font-bold cursor-pointer">Cancel Reservation</button>
                                            </form>
                                        @endif
                                    </div>

                                    @if($bTx->isCompleted() && !$bTx->review)
                                        <form action="{{ route('used.transactions.review', $bTx) }}" method="POST" class="p-4 rounded-2xl bg-white border border-[#ECD2F3] space-y-2.5">
                                            @csrf
                                            <div class="font-bold text-[#623562]">Rate This Completed Handover</div>
                                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                                <select name="rating" required class="grail-input-light px-3 py-2 text-xs font-bold">
                                                    <option value="5">★★★★★ 5 — Excellent Experience</option>
                                                    <option value="4">★★★★ 4 — Good</option>
                                                    <option value="3">★★★ 3 — Average</option>
                                                    <option value="2">★★ 2 — Poor</option>
                                                    <option value="1">★ 1 — Bad</option>
                                                </select>
                                                <input type="text" name="title" placeholder="Short headline (optional)" class="grail-input-light px-3 py-2 text-xs">
                                            </div>
                                            <textarea name="body" rows="2" required minlength="5" placeholder="Describe item accuracy and handover punctuality..." class="grail-input-light w-full px-3 py-2 text-xs resize-none"></textarea>
                                            <button type="submit" class="px-4 py-2 grail-btn-obsidian text-xs font-bold cursor-pointer">Submit Verified Review</button>
                                        </form>
                                    @elseif($bTx->review)
                                        <div class="p-3 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-950">
                                            You rated this handover <strong>{{ $bTx->review->rating }}/5 ★</strong>: "{{ $bTx->review->body }}"
                                        </div>
                                    @endif
                                </div>
                            @empty
                                <p class="text-xs text-[#1A1018]/55">No purchases or reservations recorded yet.</p>
                            @endforelse
                        </div>
                    </div>

                <!-- ================= TAB 6: GENUINE ANALYTICS ================= -->
                @elseif($tab === 'analytics')
                    <div class="grail-card bg-white p-5 sm:p-6 rounded-3xl border border-[#E6E2E8] space-y-4">
                        <h2 class="text-xl font-medium text-[#1A1018]" style="font-family: var(--font-grail-display);">Listing Performance (Genuine Recorded Activity)</h2>
                        <div class="overflow-x-auto">
                            <table class="w-full text-left text-xs">
                                <thead>
                                    <tr class="border-b border-black/10 text-[11px] uppercase text-[#1A1018]/55">
                                        <th class="py-2.5 pr-4">Listing</th>
                                        <th class="py-2.5 pr-4">Status</th>
                                        <th class="py-2.5 pr-4">Price</th>
                                        <th class="py-2.5 pr-4">Views</th>
                                        <th class="py-2.5 pr-4">Saves</th>
                                        <th class="py-2.5 pr-4">Enquiries</th>
                                        <th class="py-2.5">Open Offers</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-black/5">
                                    @forelse($allMyListings as $aItem)
                                        <tr>
                                            <td class="py-3 pr-4 font-bold">
                                                <a href="{{ route('used.show', $aItem->slug) }}" class="hover:text-[#623562]">{{ $aItem->title }}</a>
                                            </td>
                                            <td class="py-3 pr-4 uppercase font-semibold">{{ $aItem->effectiveUsedStatus() }}</td>
                                            <td class="py-3 pr-4 font-bold">₹{{ number_format($aItem->base_price / 100) }}</td>
                                            <td class="py-3 pr-4">{{ (int) $aItem->views_count }}</td>
                                            <td class="py-3 pr-4">{{ (int) $aItem->saves_count }}</td>
                                            <td class="py-3 pr-4">{{ (int) ($aItem->enquiries_count ?? 0) }}</td>
                                            <td class="py-3">{{ (int) ($aItem->pending_offers_count ?? 0) }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="py-8 text-center text-[#1A1018]/50">Publish a listing to track real views, saves, and buyer enquiries.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                <!-- ================= TAB 7: BUYER SAVED & ALERTS ================= -->
                @elseif($tab === 'buyer')
                    <div class="space-y-6">
                        <div class="grail-card bg-white p-5 sm:p-6 rounded-3xl border border-[#E6E2E8] space-y-4">
                            <h2 class="text-xl font-medium text-[#1A1018]" style="font-family: var(--font-grail-display);">Saved Pre-Owned Items ({{ $savedListings->count() }})</h2>
                            @if($savedListings->isEmpty())
                                <p class="text-xs text-[#1A1018]/55">Save listings using the heart icon on any Used card to monitor price changes and availability.</p>
                            @else
                                <div class="grid grid-cols-2 md:grid-cols-4 gap-3 sm:gap-4">
                                    @foreach($savedListings as $wl)
                                        @if($wl->product)
                                            @include('used.partials.card', ['item' => $wl->product])
                                        @endif
                                    @endforeach
                                </div>
                            @endif
                        </div>

                        <div class="grail-card bg-white p-5 sm:p-6 rounded-3xl border border-[#E6E2E8] space-y-4">
                            <h2 class="text-xl font-medium text-[#1A1018]" style="font-family: var(--font-grail-display);">Saved Search Shortcuts ({{ $savedSearches->count() }})</h2>
                            @forelse($savedSearches as $ss)
                                <div class="p-3.5 rounded-2xl bg-[#FAFAF8] border border-black/10 flex items-center justify-between gap-2 text-xs">
                                    <div>
                                        <div class="font-bold">{{ $ss->name }}</div>
                                        <div class="text-[#1A1018]/60">
                                            @if($ss->query) Keyword: "{{ $ss->query }}" • @endif
                                            @if($ss->city) City: {{ $ss->city }} @endif
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <a href="{{ route('used.index', $ss->toQueryParams()) }}" class="px-3.5 py-1.5 rounded-full bg-[#1A1018] text-[#ECD2F3] font-bold">Run Search</a>
                                        <form action="{{ route('used.saved_searches.destroy', $ss) }}" method="POST">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="px-3 py-1.5 rounded-full bg-rose-50 text-rose-700 font-bold cursor-pointer">Delete</button>
                                        </form>
                                    </div>
                                </div>
                            @empty
                                <p class="text-xs text-[#1A1018]/55">Apply filters on the Used Marketplace page and click "Save Search Shortcut".</p>
                            @endforelse
                        </div>
                    </div>

                <!-- ================= TAB 8: SELLER PROFILE & PRIVACY SETTINGS ================= -->
                @elseif($tab === 'settings')
                    <div class="grail-card bg-white p-5 sm:p-8 rounded-3xl border border-[#E6E2E8] max-w-3xl">
                        <h2 class="text-xl font-medium text-[#1A1018] mb-1" style="font-family: var(--font-grail-display);">Seller Profile &amp; Contact Privacy</h2>
                        <p class="text-xs text-[#1A1018]/60 mb-6">Control your public Used storefront name, handle, general location, and whether phone numbers stay hidden by default.</p>

                        <form action="{{ route('used.seller.update') }}" method="POST" class="space-y-4 text-xs">
                            @csrf
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-[11px] font-bold uppercase tracking-wider text-[#623562] mb-1">Public Display Name *</label>
                                    <input type="text" name="display_name" value="{{ old('display_name', $profile->display_name) }}" required class="grail-input-light w-full px-3.5 py-2.5 text-sm font-semibold">
                                </div>
                                <div>
                                    <label class="block text-[11px] font-bold uppercase tracking-wider text-[#623562] mb-1">Profile Handle *</label>
                                    <input type="text" name="handle" value="{{ old('handle', $profile->handle) }}" required class="grail-input-light w-full px-3.5 py-2.5 text-sm font-mono">
                                </div>
                            </div>

                            <div>
                                <label class="block text-[11px] font-bold uppercase tracking-wider text-[#1A1018]/70 mb-1">Seller Introduction / Bio</label>
                                <textarea name="bio" rows="3" placeholder="Tell buyers about items you typically sell, inspection windows, and meetup preferences..." class="grail-input-light w-full px-3.5 py-2.5 text-xs resize-none">{{ old('bio', $profile->bio) }}</textarea>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-[11px] font-bold uppercase tracking-wider text-[#1A1018]/70 mb-1">City</label>
                                    <input type="text" name="city" value="{{ old('city', $profile->city) }}" class="grail-input-light w-full px-3.5 py-2.5 text-xs">
                                </div>
                                <div>
                                    <label class="block text-[11px] font-bold uppercase tracking-wider text-[#1A1018]/70 mb-1">General Service Area / Locality</label>
                                    <input type="text" name="locality" value="{{ old('locality', $profile->locality) }}" class="grail-input-light w-full px-3.5 py-2.5 text-xs">
                                </div>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 p-4 rounded-2xl bg-[#FAFAF8] border border-black/10">
                                <div class="space-y-2">
                                    <div class="font-bold">Preferred Communication</div>
                                    @foreach(['k71_chat' => 'K71 Enquiry Chat', 'phone' => 'Phone Call', 'whatsapp' => 'WhatsApp'] as $cKey => $cLabel)
                                        <label class="flex items-center gap-2 cursor-pointer">
                                            <input type="checkbox" name="preferred_contact_methods[]" value="{{ $cKey }}" {{ in_array($cKey, $profile->preferred_contact_methods ?? ['k71_chat'], true) ? 'checked' : '' }} class="accent-[#623562]">
                                            <span>{{ $cLabel }}</span>
                                        </label>
                                    @endforeach
                                </div>
                                <div class="space-y-2">
                                    <div class="font-bold">Preferred Handover</div>
                                    @foreach(['local_pickup' => 'Local Pickup', 'public_meetup' => 'Public Meetup', 'seller_shipping' => 'Seller-Arranged Shipping'] as $hKey => $hLabel)
                                        <label class="flex items-center gap-2 cursor-pointer">
                                            <input type="checkbox" name="preferred_handoff_methods[]" value="{{ $hKey }}" {{ in_array($hKey, $profile->preferred_handoff_methods ?? ['local_pickup'], true) ? 'checked' : '' }} class="accent-[#623562]">
                                            <span>{{ $hLabel }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>

                            <div class="space-y-2 p-4 rounded-2xl bg-[#F8EFFA] border border-[#ECD2F3]">
                                <label class="flex items-center gap-2 cursor-pointer font-semibold">
                                    <input type="hidden" name="show_sold_history" value="0">
                                    <input type="checkbox" name="show_sold_history" value="1" {{ $profile->show_sold_history ? 'checked' : '' }} class="accent-[#623562]">
                                    <span>Display my previously sold items on my public seller profile</span>
                                </label>
                                <label class="flex items-center gap-2 cursor-pointer font-semibold">
                                    <input type="hidden" name="show_phone_publicly" value="0">
                                    <input type="checkbox" name="show_phone_publicly" value="1" {{ $profile->show_phone_publicly ? 'checked' : '' }} class="accent-[#623562]">
                                    <span>Reveal my phone number publicly (Keep unchecked for privacy by default)</span>
                                </label>
                            </div>

                            <button type="submit" class="px-6 py-2.5 grail-btn-obsidian font-bold cursor-pointer">Save Profile &amp; Privacy Settings ✓</button>
                        </form>
                    </div>
                @endif

            </div>
        </div>
    </div>
</x-layouts.used>

