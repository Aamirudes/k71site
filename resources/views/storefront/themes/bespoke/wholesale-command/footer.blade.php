{{-- WHOLESALE COMMAND BESPOKE FOOTER --}}
@php
    $storeName = $storeName ?? ($vendor->shop_name ?? 'Wholesale Command');
    $storeHomeUrl = route('storefront.show', ['slug' => $vendor->slug]);
    $storeCollectionUrl = route('storefront.collection', ['slug' => $vendor->slug]);
@endphp

<footer class="w-full bg-[#070a10] text-[#f9fafb] border-t border-[#1f2937] pt-12 pb-10 font-mono">
    <div class="max-w-[1560px] mx-auto px-4 sm:px-8 space-y-8">
        <div class="grid grid-cols-1 md:grid-cols-12 gap-8 pb-8 border-b border-[#1f2937]">
            <div class="md:col-span-5 space-y-3">
                <div class="flex items-center gap-2">
                    <span class="px-2 py-0.5 rounded bg-sky-600 text-white text-[10px] font-bold">TERMINAL B2B</span>
                    <a href="{{ $storeHomeUrl }}" class="text-xl font-bold text-white">{{ $storeName }}</a>
                </div>
                <p class="text-xs text-zinc-400 leading-relaxed font-sans max-w-md">
                    {{ strip_tags((string)($vendor->description ?: 'Verified supplier of industrial and commercial lot goods. Tiered pricing and direct depot fulfillment.')) }}
                </p>
                <div class="text-[11px] text-zinc-500">
                    DEPOT: {{ $vendor->city ? ($vendor->city . ', ' . $vendor->state) : 'CENTRAL LOGISTICS HUB' }}
                </div>
            </div>

            <div class="md:col-span-4 space-y-2 text-xs">
                <span class="text-sky-400 font-bold uppercase tracking-wider block">COMMERCIAL POLICY &amp; TERMS</span>
                <ul class="space-y-1.5 text-zinc-400">
                    <li>● Minimum Order: 10 Units / Case Pack</li>
                    <li>● NET-30 Terms Subject to Credit Verification</li>
                    <li>● Direct Bill of Lading &amp; Commercial Invoice Included</li>
                    <li>● FOB Warehouse Dock Dispatch</li>
                </ul>
            </div>

            <div class="md:col-span-3 space-y-2 text-xs">
                <span class="text-sky-400 font-bold uppercase tracking-wider block">B2B TRADE DESK</span>
                <ul class="space-y-1 text-zinc-400">
                    @if($vendor->contact_email)<li>EMAIL: {{ $vendor->contact_email }}</li>@endif
                    @if($vendor->contact_phone)<li>PHONE: {{ $vendor->contact_phone }}</li>@endif
                    <li>DESK HOURS: 08:00 – 18:00 IST</li>
                </ul>
            </div>
        </div>

        <div class="flex flex-col sm:flex-row items-center justify-between gap-4 text-[10px] text-zinc-500 tracking-wider">
            <span>© {{ date('Y') }} {{ $storeName }} · B2B COMMERCE TERMINAL</span>
            <span>POWERED BY K71 WHOLESALE ENGINE</span>
        </div>
    </div>
</footer>
