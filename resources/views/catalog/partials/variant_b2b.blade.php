{{-- B2B Machinery & Tiered MOQ Volume Pricing Partial (IndiaMART / Alibaba Pattern) --}}
@php
    $b2bTiers = $product->b2b_tiers_json ?? [];
    $specs = $product->specifications_json ?? [];
    $sellerPhone = preg_replace('/[^0-9]/', '', $product->vendor->contact_phone ?? '');
    if (strlen($sellerPhone) === 10) { $sellerPhone = '91' . $sellerPhone; }
@endphp

<div class="space-y-6" x-data="{ showRfqModal: false, rfqQty: 10, rfqMessage: '' }">
    <!-- 1. Tiered Volume MOQ Slabs Card -->
    @if(!empty($b2bTiers))
        <div class="rounded-2xl border border-stone-200/80 bg-stone-50/70 p-4 shadow-xs">
            <div class="flex items-center justify-between mb-2.5">
                <h3 class="text-xs font-bold uppercase tracking-wider text-stone-800 flex items-center gap-1.5">
                    <span>📦 Volume Wholesale Pricing (MOQ)</span>
                </h3>
                <span class="text-[10px] text-blue-700 font-bold bg-blue-50 px-2 py-0.5 rounded-full border border-blue-200">Tier Discounts</span>
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-3 gap-2">
                @foreach($b2bTiers as $tier)
                    <div class="bg-white rounded-xl p-2.5 border border-stone-200 text-center shadow-2xs">
                        <span class="text-[10px] uppercase font-bold text-stone-500 block">
                            {{ $tier['min_qty'] }}{{ !empty($tier['max_qty']) ? ' - ' . $tier['max_qty'] : '+' }} Units
                        </span>
                        <span class="text-sm font-black text-stone-900 block mt-0.5 font-mono">
                            ₹{{ number_format((float)($tier['unit_price'] ?? 0), 2) }}
                        </span>
                        <span class="text-[9px] text-emerald-600 font-semibold block">/ unit</span>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <!-- 2. Technical Machine Parameters Grid -->
    @if(!empty($specs))
        <div class="rounded-2xl border border-stone-200 bg-white p-4 space-y-3">
            <h3 class="text-xs font-bold uppercase tracking-wider text-stone-800 flex items-center gap-1.5">
                <span>⚙️ Technical Machine Parameters</span>
            </h3>

            <div class="grid grid-cols-2 sm:grid-cols-3 gap-2.5 text-xs">
                @if(!empty($specs['motor_hp']))
                    <div class="p-2.5 rounded-xl bg-stone-50 border border-stone-100">
                        <span class="text-[10px] text-stone-500 block">Motor Power</span>
                        <span class="font-bold text-stone-800">{{ $specs['motor_hp'] }}</span>
                    </div>
                @endif
                @if(!empty($specs['cutting_thickness']))
                    <div class="p-2.5 rounded-xl bg-stone-50 border border-stone-100">
                        <span class="text-[10px] text-stone-500 block">Working Thickness</span>
                        <span class="font-bold text-stone-800">{{ $specs['cutting_thickness'] }}</span>
                    </div>
                @endif
                @if(!empty($specs['voltage']))
                    <div class="p-2.5 rounded-xl bg-stone-50 border border-stone-100">
                        <span class="text-[10px] text-stone-500 block">Operating Voltage</span>
                        <span class="font-bold text-stone-800">{{ $specs['voltage'] }}</span>
                    </div>
                @endif
                @if(!empty($specs['rpm']))
                    <div class="p-2.5 rounded-xl bg-stone-50 border border-stone-100">
                        <span class="text-[10px] text-stone-500 block">Operating Speed</span>
                        <span class="font-bold text-stone-800">{{ $specs['rpm'] }}</span>
                    </div>
                @endif
                @if(!empty($specs['automation_grade']))
                    <div class="p-2.5 rounded-xl bg-stone-50 border border-stone-100">
                        <span class="text-[10px] text-stone-500 block">Automation Grade</span>
                        <span class="font-bold text-stone-800">{{ $specs['automation_grade'] }}</span>
                    </div>
                @endif
                @if(!empty($specs['weight_kg']))
                    <div class="p-2.5 rounded-xl bg-stone-50 border border-stone-100">
                        <span class="text-[10px] text-stone-500 block">Machine Weight</span>
                        <span class="font-bold text-stone-800">{{ $specs['weight_kg'] }}</span>
                    </div>
                @endif
            </div>
        </div>
    @endif

    <!-- 3. Dual Action CTAs: Direct Purchase + Request Custom RFQ -->
    <div class="space-y-2.5">
        <button type="button" 
                @click="showRfqModal = true"
                class="w-full py-3.5 px-6 rounded-full bg-blue-700 hover:bg-blue-800 text-white text-xs sm:text-sm font-bold tracking-wide transition-all shadow-md flex items-center justify-center gap-2 cursor-pointer active:scale-95">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
            <span>Request Official B2B RFQ / Quotation</span>
        </button>

        @if(!empty($sellerPhone))
            <a href="https://wa.me/{{ $sellerPhone }}?text={{ rawurlencode('Hello! I would like to inquire about industrial machinery: ' . $product->title . ' (Ref: #' . $product->id . ')') }}" 
               target="_blank"
               class="w-full py-2.5 px-5 rounded-full bg-stone-100 hover:bg-stone-200 text-stone-800 text-xs font-bold transition flex items-center justify-center gap-2">
                <span>💬 WhatsApp Machine Manufacturer</span>
            </a>
        @endif
    </div>

    <!-- 4. Direct RFQ Modal Popup -->
    <div x-show="showRfqModal" 
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-sm"
         style="display: none;"
         @keydown.escape.window="showRfqModal = false">
        
        <div @click.away="showRfqModal = false"
             class="w-full max-w-lg bg-white rounded-3xl p-6 sm:p-7 shadow-2xl text-left text-stone-900 relative">
            
            <button type="button" @click="showRfqModal = false" 
                    class="absolute top-4 right-4 text-stone-400 hover:text-stone-700 p-2 rounded-full hover:bg-stone-100 transition">
                ✕
            </button>

            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 rounded-2xl bg-blue-50 text-blue-700 flex items-center justify-center text-xl font-bold border border-blue-200">
                    📋
                </div>
                <div>
                    <h3 class="text-base font-bold text-stone-900">Request Commercial RFQ</h3>
                    <p class="text-xs text-stone-500">{{ $product->title }}</p>
                </div>
            </div>

            <form action="{{ route('rfq.submit', $product->id) }}" method="POST" class="space-y-4">
                @csrf
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-stone-700 mb-1">Required Quantity (Units) *</label>
                        <input type="number" min="1" name="quantity" x-model="rfqQty" required class="w-full px-3 py-2 rounded-xl border border-stone-300 text-xs font-bold">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-stone-700 mb-1">Destination Pincode / Port *</label>
                        <input type="text" name="destination" placeholder="e.g. 560001 or Mundra Port" required class="w-full px-3 py-2 rounded-xl border border-stone-300 text-xs font-bold">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-stone-700 mb-1">Company / Buyer Name *</label>
                        <input type="text" name="company_name" required value="{{ auth()->user()?->name ?? '' }}" placeholder="Business name" class="w-full px-3 py-2 rounded-xl border border-stone-300 text-xs">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-stone-700 mb-1">Phone Number *</label>
                        <input type="tel" name="phone" required value="{{ auth()->user()?->phone ?? '' }}" placeholder="Contact phone" class="w-full px-3 py-2 rounded-xl border border-stone-300 text-xs">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-stone-700 mb-1">Technical Requirements & Customizations (Optional)</label>
                    <textarea name="requirements" rows="3" placeholder="Specify voltage customization, spare parts package, or installation assistance..." class="w-full px-3 py-2 rounded-xl border border-stone-300 text-xs"></textarea>
                </div>

                <div class="pt-3 border-t border-stone-100 flex items-center justify-between">
                    <span class="text-xs text-stone-500">Quotation sent within 24 business hours</span>
                    <button type="submit" class="px-6 py-2.5 rounded-full bg-blue-700 hover:bg-blue-800 text-white text-xs font-bold transition shadow-md active:scale-95 cursor-pointer">
                        Submit RFQ Inquiry
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
