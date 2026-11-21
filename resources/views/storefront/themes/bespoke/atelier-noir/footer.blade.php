{{-- ATELIER NOIR BESPOKE FOOTER --}}
@php
    $storeName = $storeName ?? ($vendor->shop_name ?? 'Atelier Noir');
    $storeHomeUrl = route('storefront.show', ['slug' => $vendor->slug]);
    $storeCollectionUrl = route('storefront.collection', ['slug' => $vendor->slug]);
@endphp

<footer class="w-full bg-[#050507] text-[#f5f2eb] border-t border-white/10 pt-16 pb-12">
    <div class="max-w-[1560px] mx-auto px-5 sm:px-10 space-y-12">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-10 pb-12 border-b border-white/10">
            <div class="lg:col-span-6 space-y-4">
                <a href="{{ $storeHomeUrl }}" class="text-3xl sm:text-5xl font-serif italic tracking-tight text-[#f5f2eb]">
                    {{ $storeName }}
                </a>
                <p class="text-xs font-mono uppercase tracking-[0.22em] text-[#8a857e]">
                    {{ $vendor->city ? ($vendor->city . ' · ') : '' }}HAUTE ATELIER DE CRÉATION
                </p>
                <p class="text-xs text-[#8a857e] max-w-md leading-relaxed font-sans">
                    {{ strip_tags((string)($vendor->description ?: 'Bespoke garments created with severe architectural restraint and tactile materiality.')) }}
                </p>
            </div>

            <div class="lg:col-span-3 space-y-3 font-mono text-xs">
                <p class="text-[10px] uppercase tracking-[0.28em] text-[#8a857e] font-bold">EDITIONS</p>
                <ul class="space-y-2 text-[#f5f2eb]">
                    <li><a href="{{ $storeHomeUrl }}" class="hover:text-white transition">I · Atelier Overview</a></li>
                    <li><a href="{{ $storeCollectionUrl }}" class="hover:text-white transition">II · Complete Lookbook</a></li>
                    <li><a href="{{ route('cart.index') }}" class="hover:text-white transition">III · Private Acquisition Bag</a></li>
                </ul>
            </div>

            <div class="lg:col-span-3 space-y-3 font-mono text-xs">
                <p class="text-[10px] uppercase tracking-[0.28em] text-[#8a857e] font-bold">CONCIERGE</p>
                <ul class="space-y-1.5 text-[#8a857e]">
                    @if($vendor->contact_email)<li>{{ $vendor->contact_email }}</li>@endif
                    @if($vendor->contact_phone)<li>{{ $vendor->contact_phone }}</li>@endif
                    <li>PARIS / TOKYO / MILAN</li>
                </ul>
            </div>
        </div>

        <div class="flex flex-col sm:flex-row items-center justify-between gap-4 text-[10px] font-mono uppercase tracking-[0.2em] text-[#8a857e]">
            <span>© {{ date('Y') }} {{ $storeName }} · ALL RIGHTS RESERVED</span>
            <span>POWERED BY K71 BESPOKE ENGINE</span>
        </div>
    </div>
</footer>
