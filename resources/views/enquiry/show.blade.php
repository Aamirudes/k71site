<x-layouts.public 
    :title="$enquiry->title . ' — RFQ #' . $enquiry->enquiry_number . ' | K71'"
    :metaDescription="'Requirement details and vendor quotations for RFQ #' . $enquiry->enquiry_number . ' on K71 Enquiry Marketplace.'"
>
    <div class="max-w-4xl mx-auto py-6" x-data="{ 
        showQuoteModal: false,
        openQuoteWorkflow() {
            @guest
                if (window.openAuthModal) {
                    window.openAuthModal(() => { this.showQuoteModal = true; }, 'login', 'seller');
                } else {
                    window.location.href = '{{ route('login') }}';
                }
            @else
                @if(auth()->user()->isVendor())
                    this.showQuoteModal = true;
                @else
                    alert('Only registered supplier/vendor accounts can submit formal quotations. Please register your business as a supplier on K71.');
                    window.location.href = '{{ route('vendor.register') }}';
                @endif
            @endguest
        }
    }">

        @if(session('success'))
            <div class="mb-6 p-4 rounded-2xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-900 text-sm flex items-center gap-3 shadow-xs">
                <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        @if(session('error'))
            <div class="mb-6 p-4 rounded-2xl bg-rose-500/10 border border-rose-500/20 text-rose-900 text-sm flex items-center gap-3 shadow-xs">
                <svg class="w-5 h-5 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        <div class="rounded-3xl bg-white/80 backdrop-blur-xl border border-zinc-200/80 p-6 sm:p-8 shadow-xs space-y-6">
            
            <!-- Header & Status Pill -->
            <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-4 pb-6 border-b border-zinc-100">
                <div class="space-y-1">
                    <div class="flex items-center gap-2">
                        <span class="text-xs uppercase font-mono font-bold text-amber-800">#{{ $enquiry->enquiry_number }}</span>
                        <span class="text-xs text-zinc-500">• Posted {{ $enquiry->created_at->diffForHumans() }}</span>
                    </div>
                    <h1 class="text-2xl sm:text-3xl font-bold text-zinc-950 leading-tight">
                        {{ $enquiry->title }}
                    </h1>
                </div>

                <div class="shrink-0 flex items-center gap-2">
                    @if($enquiry->isOpen())
                        <span class="px-3.5 py-1.5 rounded-full bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-semibold uppercase tracking-wide">
                            ● Open for Quotes
                        </span>
                    @elseif($enquiry->isQuoted())
                        <span class="px-3.5 py-1.5 rounded-full bg-amber-50 border border-amber-200 text-amber-800 text-xs font-semibold uppercase tracking-wide">
                            ● Quotes Received
                        </span>
                    @elseif($enquiry->isAccepted())
                        <span class="px-3.5 py-1.5 rounded-full bg-indigo-50 border border-indigo-200 text-indigo-800 text-xs font-semibold uppercase tracking-wide">
                            ✓ Deal Finalized
                        </span>
                    @elseif($enquiry->isClosed())
                        <span class="px-3.5 py-1.5 rounded-full bg-zinc-100 border border-zinc-200 text-zinc-600 text-xs font-semibold uppercase tracking-wide">
                            Closed
                        </span>
                    @endif
                </div>
            </div>

            <!-- Key Specifications Grid -->
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                <div class="p-3.5 rounded-2xl bg-zinc-50/80 border border-zinc-200/70">
                    <div class="text-[10px] text-zinc-500 uppercase font-semibold">Quantity</div>
                    <div class="text-base font-bold text-zinc-900 mt-0.5">{{ $enquiry->quantity }}</div>
                </div>

                <div class="p-3.5 rounded-2xl bg-zinc-50/80 border border-zinc-200/70">
                    <div class="text-[10px] text-zinc-500 uppercase font-semibold">Target Budget</div>
                    <div class="text-base font-bold text-amber-800 mt-0.5">{{ $enquiry->formatted_budget }}</div>
                </div>

                <div class="p-3.5 rounded-2xl bg-zinc-50/80 border border-zinc-200/70">
                    <div class="text-[10px] text-zinc-500 uppercase font-semibold">Delivery City</div>
                    <div class="text-base font-bold text-zinc-900 mt-0.5">{{ $enquiry->city }}</div>
                </div>

                <div class="p-3.5 rounded-2xl bg-zinc-50/80 border border-zinc-200/70">
                    <div class="text-[10px] text-zinc-500 uppercase font-semibold">Timeline</div>
                    <div class="text-sm font-bold text-zinc-900 mt-0.5 uppercase">{{ str_replace('_', ' ', $enquiry->urgency) }}</div>
                </div>
            </div>

            <!-- Specifications Body -->
            <div class="space-y-2">
                <h3 class="text-xs font-bold text-zinc-500 uppercase tracking-wider">Requirement Details</h3>
                <div class="p-5 rounded-2xl bg-zinc-50/80 border border-zinc-200/70 text-sm text-zinc-800 leading-relaxed whitespace-pre-line">
                    {{ $enquiry->description }}
                </div>
            </div>

            <!-- Buyer Details & Direct Action -->
            <div class="p-5 rounded-2xl bg-amber-50/40 border border-amber-200/60 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                <div>
                    <div class="text-[11px] text-amber-800 uppercase font-bold tracking-wider">Buyer Information</div>
                    <div class="text-base font-bold text-zinc-950">{{ $enquiry->buyer_name }}</div>
                    <div class="text-xs text-zinc-600 mt-0.5">
                        <span>Delivery Location: {{ $enquiry->city }}</span>
                        @if($enquiry->pincode)
                            <span>({{ $enquiry->pincode }})</span>
                        @endif
                    </div>
                </div>

                <div class="flex items-center gap-2 w-full sm:w-auto">
                    @php
                        $bPhone = preg_replace('/[^0-9]/', '', $enquiry->buyer_phone);
                        if (strlen($bPhone) === 10) { $bPhone = '91' . $bPhone; }
                        $waUrl = "https://wa.me/{$bPhone}?text=" . urlencode("Hi {$enquiry->buyer_name}, I am contacting you regarding your requirement on K71: '{$enquiry->title}' (#{$enquiry->enquiry_number}).");
                    @endphp
                    <a href="{{ $waUrl }}" target="_blank"
                       class="flex-1 sm:flex-initial px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-semibold transition shadow-xs flex items-center justify-center gap-2">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/></svg>
                        <span>WhatsApp Buyer</span>
                    </a>

                    @if(!$enquiry->isClosed())
                        <button type="button" @click="openQuoteWorkflow()"
                                class="flex-1 sm:flex-initial px-5 py-2.5 rounded-xl bg-zinc-950 hover:bg-zinc-800 text-white text-xs font-semibold transition shadow-xs active:scale-95">
                            + Submit Quotation
                        </button>
                    @endif
                </div>
            </div>

            <!-- QUOTATIONS SECTION -->
            <div class="pt-6 border-t border-zinc-100 space-y-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-base font-bold text-zinc-950">Vendor Quotations</h3>
                        <p class="text-xs text-zinc-500">{{ $enquiry->quotes->count() }} {{ Str::plural('proposal', $enquiry->quotes->count()) }} received</p>
                    </div>

                    @if(!$enquiry->isClosed())
                        <button type="button" @click="openQuoteWorkflow()"
                                class="text-xs text-amber-800 font-semibold hover:underline">
                            + Submit Quote
                        </button>
                    @endif
                </div>

                @if($enquiry->quotes->isEmpty())
                    <div class="p-8 text-center rounded-2xl bg-zinc-50/80 border border-zinc-200/70">
                        <div class="w-10 h-10 rounded-full bg-zinc-100 text-zinc-400 flex items-center justify-center mx-auto mb-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        </div>
                        <h4 class="text-sm font-bold text-zinc-900 mb-1">No quotations submitted yet</h4>
                        <p class="text-xs text-zinc-500">Verified suppliers are reviewing this requirement.</p>
                    </div>
                @else
                    <div class="space-y-3">
                        @foreach($enquiry->quotes as $quote)
                            @php
                                $vPhone = preg_replace('/[^0-9]/', '', $quote->vendor->phone ?? $quote->vendor->contact_phone ?? '919876543210');
                                if (strlen($vPhone) === 10) { $vPhone = '91' . $vPhone; }
                                $vWa = "https://wa.me/{$vPhone}?text=" . urlencode("Hi {$quote->vendor->shop_name}, I saw your quote of {$quote->formatted_amount} on K71 for requirement #{$enquiry->enquiry_number}.");
                            @endphp
                            <div class="p-4 rounded-2xl bg-zinc-50/80 border border-zinc-200/70 flex flex-col sm:flex-row sm:items-center justify-between gap-4 {{ $quote->isAccepted() ? 'ring-2 ring-emerald-500 bg-emerald-50/50' : '' }}">
                                <div class="space-y-1">
                                    <div class="flex items-center gap-2">
                                        <span class="text-sm font-bold text-zinc-900">{{ $quote->vendor->shop_name }}</span>
                                        <span class="text-xs text-zinc-500 font-medium">📍 {{ $quote->vendor->city ?? 'Supplier' }}</span>
                                        @if($quote->isAccepted())
                                            <span class="px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-800 text-[10px] font-bold uppercase">
                                                ✓ Accepted Quote
                                            </span>
                                        @endif
                                    </div>

                                    <div class="text-xs text-zinc-600 flex items-center gap-3">
                                        <span>Lead Time: <strong>{{ $quote->lead_time_days }} Days</strong></span>
                                        @if($quote->notes)
                                            <span>•</span>
                                            <span class="text-zinc-500 italic">"{{ $quote->notes }}"</span>
                                        @endif
                                    </div>
                                </div>

                                <div class="flex items-center gap-4 shrink-0 justify-between sm:justify-end">
                                    <div class="text-right">
                                        <div class="text-[10px] text-zinc-400 uppercase font-semibold">Quoted Price</div>
                                        <div class="text-lg font-bold text-amber-800">{{ $quote->formatted_amount }}</div>
                                    </div>

                                    <div class="flex items-center gap-2">
                                        <a href="{{ $vWa }}" target="_blank"
                                           class="p-2.5 rounded-xl bg-white border border-zinc-200 text-zinc-700 hover:text-zinc-900 text-xs font-semibold transition shadow-xs" title="Chat on WhatsApp">
                                            <svg class="w-4 h-4 text-emerald-600" fill="currentColor" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/></svg>
                                        </a>

                                        @if($quote->isSent() && !$enquiry->isClosed())
                                            <form action="{{ route('quotes.accept', $quote->id) }}" method="POST">
                                                @csrf
                                                <button type="submit" 
                                                        class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-semibold transition shadow-xs active:scale-95">
                                                    Accept
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

        </div>

        <div class="mt-8 text-center">
            <a href="{{ route('enquiry.index') }}" class="inline-flex items-center gap-1.5 text-xs text-zinc-500 hover:text-zinc-800 transition font-medium">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                <span>Back to All Requirements</span>
            </a>
        </div>

        <!-- VENDOR QUOTE SUBMISSION MODAL -->
        <div x-show="showQuoteModal" 
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs"
             style="display: none;"
             @keydown.escape.window="showQuoteModal = false">
            
            <div @click.away="showQuoteModal = false"
                 class="w-full max-w-md bg-white/95 backdrop-blur-xl border border-zinc-200/80 rounded-3xl p-6 sm:p-7 shadow-2xl text-left text-zinc-900 relative max-h-[90vh] overflow-y-auto">
                
                <button type="button" @click="showQuoteModal = false" 
                        class="absolute top-4 right-4 text-zinc-400 hover:text-zinc-700 p-2 rounded-full hover:bg-zinc-100 transition">
                    ✕
                </button>

                <div class="flex items-center gap-3 mb-5 pb-4 border-b border-zinc-100">
                    <div class="w-10 h-10 rounded-2xl bg-amber-50 text-amber-800 border border-amber-200 flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-zinc-950">Submit Quotation</h3>
                        <p class="text-xs text-zinc-500 truncate max-w-[240px]">{{ $enquiry->title }}</p>
                    </div>
                </div>

                <form action="{{ route('vendor.enquiries.quote', $enquiry->id) }}" method="POST" class="space-y-4">
                    @csrf

                    <div>
                        <label class="block text-xs font-bold text-zinc-700 mb-1">Your Total Quote Amount (₹) *</label>
                        <input type="number" step="0.01" min="1" name="quote_amount" required placeholder="e.g. 45000"
                               class="w-full px-3.5 py-2.5 rounded-xl bg-zinc-50 border border-zinc-200 text-zinc-900 text-sm focus:ring-2 focus:ring-amber-500 focus:bg-white focus:outline-none transition">
                        <span class="text-[10px] text-zinc-500">Total inclusive of taxes &amp; freight if applicable</span>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-zinc-700 mb-1">Lead Time (Delivery in Days) *</label>
                        <input type="number" min="1" max="365" name="lead_time_days" required value="7"
                               class="w-full px-3.5 py-2.5 rounded-xl bg-zinc-50 border border-zinc-200 text-zinc-900 text-sm focus:ring-2 focus:ring-amber-500 focus:bg-white focus:outline-none transition">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-zinc-700 mb-1">Quote Validity (Optional)</label>
                        <input type="date" name="valid_until" min="{{ date('Y-m-d', strtotime('+1 day')) }}"
                               class="w-full px-3.5 py-2.5 rounded-xl bg-zinc-50 border border-zinc-200 text-zinc-900 text-xs focus:ring-2 focus:ring-amber-500 focus:bg-white focus:outline-none transition">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-zinc-700 mb-1">Notes / Terms / Sample Details (Optional)</label>
                        <textarea name="notes" rows="3" placeholder="State payment terms, material brand/grade, packaging, or sample delivery..."
                                  class="w-full px-3.5 py-2.5 rounded-xl bg-zinc-50 border border-zinc-200 text-zinc-900 text-xs focus:ring-2 focus:ring-amber-500 focus:bg-white focus:outline-none transition"></textarea>
                    </div>

                    <div class="pt-3 border-t border-zinc-100 flex items-center justify-between">
                        <span class="text-[11px] text-zinc-500">Sent directly to buyer</span>
                        <button type="submit" 
                                class="px-6 py-2.5 rounded-full bg-zinc-900 hover:bg-zinc-800 text-white font-semibold text-xs transition shadow-xs active:scale-95">
                            Submit Quote
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</x-layouts.public>
