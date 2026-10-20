<x-layouts.vendor title="Buyer Enquiries & RFQ Leads">
    <div class="mb-8 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h1 class="text-2xl font-serif font-bold text-gray-900">Buyer Enquiries & RFQ Leads</h1>
            <p class="text-sm text-gray-500 mt-1">Review custom buying requirements, bulk wholesale requests, and submit competitive quotations.</p>
        </div>
        <div>
            <a href="{{ route('enquiry.index') }}" target="_blank" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-amber-500 hover:bg-amber-400 text-slate-950 text-xs font-bold transition shadow-sm">
                <span>🔍 Browse Public Feed</span>
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
        @foreach(['all' => 'All Open Leads', 'direct' => 'Direct Inquiries to You', 'my_quotes' => 'My Quoted Leads', 'closed' => 'Closed Leads'] as $tabKey => $tabLabel)
            <a href="{{ route('vendor.enquiries.index', ['tab' => $tabKey]) }}" 
               class="px-4 py-2 rounded-xl text-xs font-bold transition whitespace-nowrap flex items-center gap-1.5 {{ $tab === $tabKey ? 'bg-amber-500 text-slate-950 font-black shadow-sm' : 'bg-white hover:bg-gray-50 text-gray-700 border border-gray-200' }}">
                <span>{{ $tabLabel }}</span>
                <span class="px-1.5 py-0.2 rounded-full text-[10px] {{ $tab === $tabKey ? 'bg-black/10 text-slate-950 font-black' : 'bg-gray-100 text-gray-600' }}">
                    {{ $counts[$tabKey] ?? 0 }}
                </span>
            </a>
        @endforeach
    </div>

    @if($enquiries->isEmpty())
        <div class="p-12 text-center rounded-2xl bg-white border border-gray-200">
            <div class="text-4xl mb-3">📋</div>
            <h3 class="text-base font-bold text-gray-900 mb-1">No enquiries found in this view</h3>
            <p class="text-xs text-gray-500">When buyers post bulk or custom requirements matching your category, they will appear here.</p>
        </div>
    @else
        <div class="space-y-4">
            @foreach($enquiries as $enquiry)
                @php
                    $myQuote = $enquiry->quotes->first();
                    $cleanPhone = preg_replace('/[^0-9]/', '', $enquiry->buyer_phone);
                    if (strlen($cleanPhone) === 10) { $cleanPhone = '91' . $cleanPhone; }
                    $msg = "Hi {$enquiry->buyer_name}, this is {$vendor->shop_name} on K71. Regarding your requirement (#{$enquiry->enquiry_number}: {$enquiry->title}), we are ready to supply.";
                    $waLink = "https://wa.me/{$cleanPhone}?text=" . urlencode($msg);
                @endphp
                <div class="bg-white rounded-2xl border border-gray-200 p-5 shadow-xs transition hover:border-gray-300"
                     x-data="{ quoteOpen: false }">
                    <div class="flex flex-col lg:flex-row lg:items-start justify-between gap-5">
                        
                        <!-- Left: Enquiry Info -->
                        <div class="space-y-2 flex-1">
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="text-xs font-mono font-bold text-amber-700">#{{ $enquiry->enquiry_number }}</span>
                                <span class="text-xs text-gray-400">• {{ $enquiry->created_at->diffForHumans() }}</span>
                                
                                @if($enquiry->vendor_id === $vendor->id)
                                    <span class="px-2 py-0.5 rounded-full bg-amber-100 text-amber-800 text-[10px] font-bold uppercase">
                                        ⭐ Direct To You
                                    </span>
                                @endif

                                @if($enquiry->isClosed())
                                    <span class="px-2 py-0.5 rounded-full bg-gray-100 text-gray-700 text-[10px] font-bold uppercase">
                                        Closed
                                    </span>
                                @else
                                    <span class="px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-800 text-[10px] font-bold uppercase">
                                        Open for Bids
                                    </span>
                                @endif
                            </div>

                            <h3 class="text-base font-bold text-gray-900 leading-snug">
                                <a href="{{ route('enquiry.show', $enquiry->enquiry_number) }}" target="_blank" class="hover:text-amber-600 transition">
                                    {{ $enquiry->title }}
                                </a>
                            </h3>

                            <p class="text-xs text-gray-600 line-clamp-2 leading-relaxed">
                                {{ $enquiry->description }}
                            </p>

                            <!-- Metrics row -->
                            <div class="flex flex-wrap items-center gap-4 text-xs text-gray-700 pt-1">
                                <div><span class="text-gray-400">Qty:</span> <strong class="text-gray-900">{{ $enquiry->quantity }}</strong></div>
                                <div><span class="text-gray-400">Target:</span> <strong class="text-amber-600">{{ $enquiry->formatted_budget }}</strong></div>
                                <div><span class="text-gray-400">Location:</span> <strong class="text-gray-900">📍 {{ $enquiry->city }}</strong></div>
                                <div><span class="text-gray-400">Urgency:</span> <strong class="capitalize">{{ str_replace('_', ' ', $enquiry->urgency) }}</strong></div>
                            </div>

                            @if($myQuote)
                                <div class="mt-2 p-3 rounded-xl bg-amber-50/70 border border-amber-200 text-xs flex items-center justify-between">
                                    <div class="flex items-center gap-2">
                                        <span class="font-bold text-amber-900">Your Current Quote:</span>
                                        <span class="font-black text-amber-700 text-sm">{{ $myQuote->formatted_amount }}</span>
                                        <span class="text-gray-500">({{ $myQuote->lead_time_days }} days lead time)</span>
                                    </div>
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase {{ $myQuote->isAccepted() ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                                        {{ ucfirst($myQuote->status) }}
                                    </span>
                                </div>
                            @endif
                        </div>

                        <!-- Right: Actions & WhatsApp -->
                        <div class="lg:w-72 shrink-0 space-y-3 pt-3 lg:pt-0 lg:border-l lg:border-gray-100 lg:pl-5">
                            <div class="text-xs space-y-1">
                                <div class="font-bold text-gray-900">{{ $enquiry->buyer_name }}</div>
                                <div class="text-gray-600">📞 {{ $enquiry->buyer_phone }}</div>
                            </div>

                            <div class="flex flex-col gap-2">
                                <a href="{{ $waLink }}" target="_blank"
                                   class="w-full py-2 px-3 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold transition flex items-center justify-center gap-1.5 shadow-xs">
                                    <span>💬</span>
                                    <span>WhatsApp Buyer</span>
                                </a>

                                @if(!$enquiry->isClosed())
                                    <button type="button" @click="quoteOpen = !quoteOpen"
                                            class="w-full py-2 px-3 rounded-xl bg-amber-500 hover:bg-amber-400 text-slate-950 text-xs font-black transition shadow-xs">
                                        {{ $myQuote ? '✏️ Revise Quotation' : '+ Submit Quotation' }}
                                    </button>
                                @endif
                            </div>
                        </div>

                    </div>

                    <!-- Inline Quote Form Drawer -->
                    <div x-show="quoteOpen" class="mt-4 pt-4 border-t border-gray-200" style="display: none;">
                        <form action="{{ route('vendor.enquiries.quote', $enquiry->id) }}" method="POST" class="p-4 rounded-xl bg-gray-50 border border-gray-200 space-y-3">
                            @csrf
                            <h4 class="text-xs font-bold text-gray-900 uppercase tracking-wider">
                                {{ $myQuote ? 'Revise Your Quotation' : 'Submit Quotation to Buyer' }}
                            </h4>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-[11px] font-semibold text-gray-700 mb-1">Total Quote Amount (₹) *</label>
                                    <input type="number" step="0.01" min="1" name="quote_amount" required value="{{ $myQuote ? $myQuote->quote_amount / 100 : '' }}" placeholder="e.g. 45000"
                                           class="w-full px-3 py-1.5 rounded-lg border border-gray-300 text-xs text-gray-900 focus:ring-1 focus:ring-amber-500 focus:outline-none">
                                </div>

                                <div>
                                    <label class="block text-[11px] font-semibold text-gray-700 mb-1">Lead Time (Days) *</label>
                                    <input type="number" min="1" max="365" name="lead_time_days" required value="{{ $myQuote ? $myQuote->lead_time_days : 7 }}"
                                           class="w-full px-3 py-1.5 rounded-lg border border-gray-300 text-xs text-gray-900 focus:ring-1 focus:ring-amber-500 focus:outline-none">
                                </div>
                            </div>

                            <div>
                                <label class="block text-[11px] font-semibold text-gray-700 mb-1">Terms / Notes (Optional)</label>
                                <textarea name="notes" rows="2" placeholder="Payment terms, quality specifications, packaging details..."
                                          class="w-full px-3 py-1.5 rounded-lg border border-gray-300 text-xs text-gray-900 focus:ring-1 focus:ring-amber-500 focus:outline-none">{{ $myQuote?->notes }}</textarea>
                            </div>

                            <div class="flex items-center justify-end gap-2 pt-1">
                                <button type="button" @click="quoteOpen = false" class="px-3 py-1.5 rounded-lg text-xs font-semibold text-gray-600 hover:bg-gray-200">
                                    Cancel
                                </button>
                                <button type="submit" class="px-4 py-1.5 rounded-lg bg-amber-500 hover:bg-amber-400 text-slate-950 text-xs font-bold transition shadow-xs">
                                    Send Quote
                                </button>
                            </div>
                        </form>
                    </div>

                </div>
            @endforeach
        </div>

        <div class="mt-6">
            {{ $enquiries->links() }}
        </div>
    @endif
</x-layouts.vendor>
