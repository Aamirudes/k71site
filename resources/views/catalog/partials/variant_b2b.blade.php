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
        <div class="rounded-2xl border border-black/10 bg-[#FAFAF8] p-4 shadow-xs">
            <div class="flex items-center justify-between mb-3">
                <h3 class="grail-eyebrow !text-[#623562] flex items-center gap-1.5">
                    <span>📦 Volume Wholesale Pricing (MOQ)</span>
                </h3>
                <span class="text-[10px] text-[#1A1018] font-bold bg-[#ECD2F3]/70 px-2.5 py-0.5 rounded-full border border-[#623562]/20">Tier Discounts</span>
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-3 gap-2.5">
                @foreach($b2bTiers as $tier)
                    <div class="bg-white rounded-xl p-3 border border-black/8 text-center shadow-2xs hover:border-[#623562]/30 transition">
                        <span class="text-[10px] uppercase font-bold tracking-wider text-[#623562] block">
                            {{ $tier['min_qty'] }}{{ !empty($tier['max_qty']) ? ' - ' . $tier['max_qty'] : '+' }} Units
                        </span>
                        <span class="text-sm font-black text-[#1A1018] block mt-0.5 font-mono">
                            ₹{{ number_format((float)($tier['unit_price'] ?? 0), 2) }}
                        </span>
                        <span class="text-[10px] text-emerald-700 font-semibold block">/ unit</span>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <!-- 2. Technical Machine Parameters Grid -->
    @if(!empty($specs))
        <div class="rounded-2xl border border-black/10 bg-white p-4 space-y-3 shadow-xs">
            <h3 class="grail-eyebrow !text-[#623562] flex items-center gap-1.5">
                <span>⚙️ Technical Machine Parameters</span>
            </h3>

            <div class="grid grid-cols-2 sm:grid-cols-3 gap-2.5 text-xs">
                @if(!empty($specs['motor_hp']))
                    <div class="p-2.5 rounded-xl bg-[#FAFAF8] border border-black/8">
                        <span class="text-[10px] text-[#1A1018]/55 block">Motor Power</span>
                        <span class="font-bold text-[#1A1018]">{{ $specs['motor_hp'] }}</span>
                    </div>
                @endif
                @if(!empty($specs['cutting_thickness']))
                    <div class="p-2.5 rounded-xl bg-[#FAFAF8] border border-black/8">
                        <span class="text-[10px] text-[#1A1018]/55 block">Working Thickness</span>
                        <span class="font-bold text-[#1A1018]">{{ $specs['cutting_thickness'] }}</span>
                    </div>
                @endif
                @if(!empty($specs['voltage']))
                    <div class="p-2.5 rounded-xl bg-[#FAFAF8] border border-black/8">
                        <span class="text-[10px] text-[#1A1018]/55 block">Operating Voltage</span>
                        <span class="font-bold text-[#1A1018]">{{ $specs['voltage'] }}</span>
                    </div>
                @endif
                @if(!empty($specs['rpm']))
                    <div class="p-2.5 rounded-xl bg-[#FAFAF8] border border-black/8">
                        <span class="text-[10px] text-[#1A1018]/55 block">Operating Speed</span>
                        <span class="font-bold text-[#1A1018]">{{ $specs['rpm'] }}</span>
                    </div>
                @endif
                @if(!empty($specs['automation_grade']))
                    <div class="p-2.5 rounded-xl bg-[#FAFAF8] border border-black/8">
                        <span class="text-[10px] text-[#1A1018]/55 block">Automation Grade</span>
                        <span class="font-bold text-[#1A1018]">{{ $specs['automation_grade'] }}</span>
                    </div>
                @endif
                @if(!empty($specs['weight_kg']))
                    <div class="p-2.5 rounded-xl bg-[#FAFAF8] border border-black/8">
                        <span class="text-[10px] text-[#1A1018]/55 block">Machine Weight</span>
                        <span class="font-bold text-[#1A1018]">{{ $specs['weight_kg'] }}</span>
                    </div>
                @endif
            </div>
        </div>
    @endif

    <!-- 3. Machine Manufacturer Quick Direct Contact -->
    @if(!empty($sellerPhone))
        <div class="pt-1">
            <a href="https://wa.me/{{ $sellerPhone }}?text={{ rawurlencode('Hello! I would like to inquire about industrial machinery: ' . $product->title . ' (Ref: #' . $product->id . ')') }}" 
               target="_blank"
               class="w-full py-3 px-5 rounded-full grail-btn-ghost-light text-xs font-bold flex items-center justify-center gap-2">
                <span>💬 WhatsApp Machine Manufacturer</span>
            </a>
        </div>
    @endif
</div>

