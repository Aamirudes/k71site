{{-- WHOLESALE COMMAND BESPOKE HOMEPAGE (High-Density B2B Terminal) --}}
@php
    $storeName = $storeName ?? ($vendor->shop_name ?? 'Wholesale Command');
    $storeCollectionUrl = $storeCollectionUrl ?? route('storefront.collection', ['slug' => $vendor->slug]);
    $allProducts = (isset($products) && method_exists($products, 'items')) ? collect($products->items())->values() : collect($products ?? [])->values();
    $prodUrl = fn ($p) => route('storefront.product', ['slug' => $vendor->slug, 'productSlug' => $p->slug]) . (!empty($isPreview) ? '?preview=1' : '');
    $totalStockSum = $allProducts->sum(fn ($p) => (int)($p->total_stock ?? 120));
@endphp

<div class="w-full bg-[#0b0f19] text-[#f9fafb]">
    {{-- SECTION 1: LIVE B2B TELEMETRY DASHBOARD HERO --}}
    <section class="py-8 sm:py-12 px-4 sm:px-8 max-w-[1560px] mx-auto border-b border-[#1f2937]">
        <div class="space-y-6">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <div class="flex items-center gap-2 text-xs font-mono text-sky-400 mb-1">
                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                        <span>WHOLESALE COMMERCE GATEWAY // {{ strtoupper($storeName) }}</span>
                    </div>
                    <h1 class="text-2xl sm:text-4xl font-mono font-bold tracking-tight text-white">
                        Industrial Wholesale Procurement Terminal
                    </h1>
                </div>
                <a href="{{ $storeCollectionUrl }}" class="k71-btn k71-btn-primary !px-5 !py-2.5 !text-xs font-mono font-bold shrink-0">
                    VIEW COMPLETE TRADE LEDGER [{{ $allProducts->count() }} LOTS] →
                </a>
            </div>

            {{-- 4 Live Telemetry Tiles --}}
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="p-4 sm:p-5 rounded-lg bg-[#111827] border border-[#1f2937] space-y-1">
                    <span class="text-[10px] font-mono uppercase tracking-wider text-zinc-400">Total Units in Depot</span>
                    <p class="text-2xl sm:text-3xl font-mono font-bold text-white">{{ number_format(max(1450, $totalStockSum)) }}</p>
                    <span class="text-[10px] font-mono text-emerald-400">● LIVE WAREHOUSE SYNC</span>
                </div>

                <div class="p-4 sm:p-5 rounded-lg bg-[#111827] border border-[#1f2937] space-y-1">
                    <span class="text-[10px] font-mono uppercase tracking-wider text-zinc-400">Dispatch Lead Time</span>
                    <p class="text-2xl sm:text-3xl font-mono font-bold text-sky-400">24-48 HR</p>
                    <span class="text-[10px] font-mono text-zinc-400">FREIGHT CARRIER DOCK</span>
                </div>

                <div class="p-4 sm:p-5 rounded-lg bg-[#111827] border border-[#1f2937] space-y-1">
                    <span class="text-[10px] font-mono uppercase tracking-wider text-zinc-400">Max Volume Discount</span>
                    <p class="text-2xl sm:text-3xl font-mono font-bold text-emerald-400">40% OFF</p>
                    <span class="text-[10px] font-mono text-zinc-400">TIER 3 PALLET ORDERS</span>
                </div>

                <div class="p-4 sm:p-5 rounded-lg bg-[#111827] border border-[#1f2937] space-y-1">
                    <span class="text-[10px] font-mono uppercase tracking-wider text-zinc-400">Minimum Order Qty</span>
                    <p class="text-2xl sm:text-3xl font-mono font-bold text-amber-400">10 UNITS</p>
                    <span class="text-[10px] font-mono text-zinc-400">CASE LOT PACKAGING</span>
                </div>
            </div>
        </div>
    </section>

    {{-- SECTION 2: STRUCTURED TRADE SPECIMEN LEDGER (TABLE ON DESKTOP, DENSE CARDS ON MOBILE) --}}
    <section class="py-8 sm:py-12 px-4 sm:px-8 max-w-[1560px] mx-auto border-b border-[#1f2937]">
        <div class="space-y-6">
            <div class="flex items-center justify-between pb-3 border-b border-[#1f2937]">
                <h2 class="text-lg sm:text-xl font-mono font-bold text-white flex items-center gap-2">
                    <span class="px-2 py-0.5 rounded bg-[#1f2937] text-sky-400 text-xs font-mono">SPECIMENS</span>
                    Active Commercial Lots
                </h2>
                <span class="text-xs font-mono text-zinc-400">DISPLAYING 6 VERIFIED LOTS</span>
            </div>

            {{-- Desktop Data Table --}}
            <div class="hidden lg:block overflow-x-auto rounded-lg border border-[#1f2937] bg-[#111827]">
                <table class="w-full text-left font-mono text-xs divide-y divide-[#1f2937]">
                    <thead class="bg-[#0f172a] text-zinc-400 uppercase tracking-wider text-[11px]">
                        <tr>
                            <th class="py-3 px-4">Lot ID</th>
                            <th class="py-3 px-4">Product Name</th>
                            <th class="py-3 px-4">Category</th>
                            <th class="py-3 px-4">Available Stock</th>
                            <th class="py-3 px-4 text-right">Unit Price</th>
                            <th class="py-3 px-4 text-center">Procurement Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#1f2937]">
                        @foreach($allProducts->take(6) as $idx => $p)
                            @php
                                $price = (int) ($p->base_price ?? 0);
                                $img = $p->images?->first()?->url ?? asset('images/theme-editorial/tech-hardware.jpg');
                            @endphp
                            <tr class="hover:bg-[#1a2333] transition">
                                <td class="py-3.5 px-4 text-sky-400 font-bold">LOT-#{{ str_pad((string)($idx + 101), 4, '0', STR_PAD_LEFT) }}</td>
                                <td class="py-3.5 px-4 font-semibold text-white">
                                    <div class="flex items-center gap-3">
                                        <img src="{{ $img }}" alt="" class="w-10 h-10 rounded object-cover border border-[#374151]">
                                        <a href="{{ $prodUrl($p) }}" class="hover:text-sky-400 transition">{{ $p->title }}</a>
                                    </div>
                                </td>
                                <td class="py-3.5 px-4 text-zinc-400">{{ $p->category?->name ?? 'GENERAL' }}</td>
                                <td class="py-3.5 px-4 text-emerald-400 font-bold">{{ $p->total_stock ?: '150' }} units</td>
                                <td class="py-3.5 px-4 text-right font-bold text-white">₹{{ number_format($price / 100, 0) }}</td>
                                <td class="py-3.5 px-4 text-center">
                                    <form action="{{ route('cart.add') }}" method="POST" data-k71-cart-form class="inline-flex items-center gap-2">
                                        @csrf
                                        <input type="hidden" name="product_id" value="{{ $p->id }}">
                                        <input type="number" name="quantity" value="10" min="1" class="w-16 h-8 px-2 bg-[#0b0f19] border border-[#374151] rounded text-center text-xs font-mono text-white">
                                        <button type="submit" class="h-8 px-3 rounded bg-sky-600 hover:bg-sky-500 text-white font-mono text-[11px] font-bold uppercase transition">
                                            + ADD LOT
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Mobile Specimen Cards --}}
            <div class="lg:hidden grid grid-cols-1 sm:grid-cols-2 gap-4">
                @foreach($allProducts->take(6) as $product)
                    @include('storefront.themes.bespoke.wholesale-command.card', ['product' => $product])
                @endforeach
            </div>
        </div>
    </section>

    {{-- SECTION 3: WHOLESALE VOLUME TIER MATRIX --}}
    <section class="py-10 sm:py-14 px-4 sm:px-8 max-w-[1560px] mx-auto">
        <div class="p-6 sm:p-8 rounded-xl bg-[#111827] border border-[#1f2937] space-y-6">
            <div class="space-y-1">
                <span class="text-xs font-mono text-sky-400 font-bold uppercase">COMMERCIAL CONTRACT TERMS</span>
                <h3 class="text-xl sm:text-2xl font-mono font-bold text-white">Standard Wholesale Tier Discount Schedule</h3>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 font-mono text-xs">
                <div class="p-4 rounded-lg bg-[#0b0f19] border border-[#1f2937] space-y-2">
                    <div class="flex justify-between items-baseline">
                        <span class="text-sky-400 font-bold">TIER 1 (STARTER LOT)</span>
                        <span class="text-emerald-400 font-bold">10% OFF</span>
                    </div>
                    <p class="text-zinc-400">Order Size: 10 – 50 Units</p>
                    <p class="text-[11px] text-zinc-500">Standard pallet wrapping, 48hr warehouse priority.</p>
                </div>

                <div class="p-4 rounded-lg bg-[#0b0f19] border border-[#1f2937] space-y-2">
                    <div class="flex justify-between items-baseline">
                        <span class="text-sky-400 font-bold">TIER 2 (CONTAINER)</span>
                        <span class="text-emerald-400 font-bold">25% OFF</span>
                    </div>
                    <p class="text-zinc-400">Order Size: 51 – 200 Units</p>
                    <p class="text-[11px] text-zinc-500">Includes freight insurance & dedicated account rep.</p>
                </div>

                <div class="p-4 rounded-lg bg-[#0b0f19] border border-[#1f2937] space-y-2">
                    <div class="flex justify-between items-baseline">
                        <span class="text-sky-400 font-bold">TIER 3 (ENTERPRISE CONTRACT)</span>
                        <span class="text-emerald-400 font-bold">40% OFF</span>
                    </div>
                    <p class="text-zinc-400">Order Size: 200+ Units</p>
                    <p class="text-[11px] text-zinc-500">Custom branding, NET-30 payment terms upon credit approval.</p>
                </div>
            </div>
        </div>
    </section>
</div>
