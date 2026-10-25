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

    <!-- 3. Machine Manufacturer Quick Direct Contact -->
    @if(!empty($sellerPhone))
        <div class="pt-1">
            <a href="https://wa.me/{{ $sellerPhone }}?text={{ rawurlencode('Hello! I would like to inquire about industrial machinery: ' . $product->title . ' (Ref: #' . $product->id . ')') }}" 
               target="_blank"
               class="w-full py-2.5 px-5 rounded-2xl bg-stone-100 hover:bg-stone-200 text-stone-800 text-xs font-bold transition flex items-center justify-center gap-2">
                <span>💬 WhatsApp Machine Manufacturer</span>
            </a>
        </div>
    @endif
</div>

