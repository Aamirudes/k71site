{{-- OBJECT GALLERY BESPOKE FOOTER --}}
@php
    $storeName = $storeName ?? ($vendor->shop_name ?? 'Object Gallery');
    $storeHomeUrl = route('storefront.show', ['slug' => $vendor->slug]);
    $storeCollectionUrl = route('storefront.collection', ['slug' => $vendor->slug]);
@endphp

<footer class="w-full bg-[#fbfbfa] text-[#111111] border-t border-black/[0.08] pt-14 pb-12">
    <div class="max-w-[1560px] mx-auto px-5 sm:px-10 space-y-10">
        <div class="grid grid-cols-1 md:grid-cols-12 gap-8 pb-10 border-b border-black/[0.08]">
            <div class="md:col-span-6 space-y-3">
                <a href="{{ $storeHomeUrl }}" class="text-2xl sm:text-3xl font-serif text-[#111111]">
                    {{ $storeName }}
                </a>
                <p class="text-[10px] font-mono uppercase tracking-[0.22em] text-[#767676]">
                    {{ $vendor->city ? ($vendor->city . ' · ') : '' }}PERMANENT ARCHIVE
                </p>
                <p class="text-xs text-[#767676] max-w-md leading-relaxed font-sans">
                    {{ strip_tags((string)($vendor->description ?: 'Dedicated to utilitarian forms, tactile craftsmanship, and restrained architectural beauty.')) }}
                </p>
            </div>

            <div class="md:col-span-3 space-y-2 text-xs font-mono">
                <p class="text-[10px] uppercase tracking-widest text-black font-bold">HOURS &amp; ACCESS</p>
                <p class="text-[#767676]">TUE – SUN: 10:00 – 18:00</p>
                <p class="text-[#767676]">MONDAYS CLOSED</p>
                <p class="text-[#767676]">CURATORIAL INQUIRIES ONLY</p>
            </div>

            <div class="md:col-span-3 space-y-2 text-xs font-mono">
                <p class="text-[10px] uppercase tracking-widest text-black font-bold">DIRECTORY</p>
                <ul class="space-y-1 text-[#767676]">
                    <li><a href="{{ $storeHomeUrl }}" class="hover:text-black">Pavilion Index</a></li>
                    <li><a href="{{ $storeCollectionUrl }}" class="hover:text-black">Accession Catalogue</a></li>
                    <li><a href="{{ route('cart.index') }}" class="hover:text-black">Acquisition Dossier</a></li>
                </ul>
            </div>
        </div>

        <div class="flex flex-col sm:flex-row items-center justify-between gap-4 text-[10px] font-mono uppercase tracking-[0.2em] text-[#767676]">
            <span>© {{ date('Y') }} {{ $storeName }} · ARCHIVAL RECORD</span>
            <span>POWERED BY K71 BESPOKE ENGINE</span>
        </div>
    </div>
</footer>
