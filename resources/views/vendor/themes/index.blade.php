<x-layouts.vendor title="Theme Marketplace">
<div class="py-6 sm:py-8 px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto space-y-8">

    {{-- Header --}}
    <div class="flex flex-col lg:flex-row lg:items-end justify-between gap-4">
        <div>
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-zinc-950 text-white text-[10px] font-bold uppercase tracking-widest mb-2.5">
                <span>K71 Storefront Theme Marketplace</span>
                <span class="opacity-50">|</span>
                <span>61 Themes · 50 Bespoke Studio Editions</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-slate-900">Theme Marketplace</h1>
            <p class="text-xs sm:text-sm text-slate-500 mt-1 max-w-2xl">
                Every studio theme features its own layout architecture, typography system, product cards, and product detail page. Click <strong>Preview</strong> on any theme to inspect your actual store in a new tab without altering your live storefront.
            </p>
        </div>
        <div class="flex flex-wrap items-center gap-2.5">
            <a href="{{ route('vendor.design.index') }}" class="px-4 py-2.5 rounded-xl border border-slate-200 bg-white text-xs font-bold text-slate-700 hover:bg-slate-50 transition">
                Classic 11 Studio
            </a>
            @if($vendor->storefront_url)
                <a href="{{ $vendor->storefront_url }}" target="_blank" rel="noopener" class="px-4 py-2.5 rounded-xl bg-zinc-950 text-white text-xs font-bold hover:bg-zinc-800 transition inline-flex items-center gap-1.5">
                    <span>Live Storefront</span>
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M14 4h6v6M10 14L20 4M18 14v5a1 1 0 01-1 1H5a1 1 0 01-1-1V7a1 1 0 011-1h5"/></svg>
                </a>
            @endif
        </div>
    </div>

    {{-- Flash Feedback + WhatsApp Coordination Banner --}}
    @if(session('success'))
        <div class="p-4 sm:p-5 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-950 text-xs sm:text-sm space-y-2.5">
            <p class="font-semibold">{{ session('success') }}</p>
            @if(session('recent_whatsapp_url'))
                <div class="flex flex-wrap items-center gap-3 pt-1">
                    <a href="{{ session('recent_whatsapp_url') }}" target="_blank" rel="noopener"
                       class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-emerald-600 text-white text-xs font-bold hover:bg-emerald-700 transition">
                        <span>Continue on WhatsApp</span>
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14m-6-6l6 6-6 6"/></svg>
                    </a>
                    <span class="text-[11px] text-emerald-800">Manual verification required: clicking WhatsApp does not mark payment confirmed or unlock the theme until K71 Admin grants access.</span>
                </div>
            @elseif(!$adminWhatsAppConfigured)
                <p class="text-[11px] text-amber-800 font-medium">Business WhatsApp number is not yet configured in Admin Theme Settings. Your purchase request reference is safely recorded in the admin queue.</p>
            @endif
        </div>
    @endif

    @if(session('error'))
        <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-xs sm:text-sm font-semibold">
            {{ session('error') }}
        </div>
    @endif

    {{-- Vendor's Active / Recent Purchase Requests --}}
    @if($vendorRequests->isNotEmpty())
        <div class="rounded-2xl border border-slate-200 bg-white p-4 sm:p-5 space-y-3">
            <div class="flex flex-wrap items-center justify-between gap-2">
                <h2 class="text-sm font-extrabold text-slate-900">Your Theme Access Requests</h2>
                <span class="text-[11px] text-slate-500">Payment verification &amp; entitlement grants are performed manually by K71 Admin</span>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="border-b border-slate-100 text-[10px] uppercase tracking-wider text-slate-400">
                            <th class="py-2 pr-3">Reference</th>
                            <th class="py-2 pr-3">Theme</th>
                            <th class="py-2 pr-3">Price Snapshot</th>
                            <th class="py-2 pr-3">Payment Status</th>
                            <th class="py-2 pr-3">Request Status</th>
                            <th class="py-2 text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($vendorRequests as $req)
                            @php $waHref = $service->buildWhatsAppRequestUrl($req); @endphp
                            <tr>
                                <td class="py-2.5 pr-3 font-mono font-bold text-slate-900">{{ $req->reference_code }}</td>
                                <td class="py-2.5 pr-3 font-semibold text-slate-900">{{ $req->theme?->name ?? $req->theme_id }}</td>
                                <td class="py-2.5 pr-3 font-semibold text-slate-700">{{ $req->formatted_price_snapshot }}</td>
                                <td class="py-2.5 pr-3">
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ $req->isPaymentConfirmed() ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                                        {{ ucwords(str_replace('_', ' ', $req->payment_verification_status)) }}
                                    </span>
                                </td>
                                <td class="py-2.5 pr-3">
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-800">{{ $req->status_label }}</span>
                                </td>
                                <td class="py-2.5 text-right whitespace-nowrap space-x-1.5">
                                    @if($req->isPending() && $waHref)
                                        <a href="{{ $waHref }}" target="_blank" rel="noopener" class="inline-flex items-center px-2.5 py-1 rounded-lg bg-emerald-600 text-white text-[11px] font-bold hover:bg-emerald-700">Continue on WhatsApp</a>
                                    @endif
                                    @if(in_array($req->status, ['requested', 'pending_review', 'payment_awaiting_confirmation'], true))
                                        <form action="{{ route('vendor.themes.request.cancel', $req) }}" method="POST" class="inline" onsubmit="return confirm('Cancel this pending request?');">
                                            @csrf
                                            <button type="submit" class="px-2.5 py-1 rounded-lg border border-slate-200 text-[11px] font-semibold text-slate-600 hover:bg-slate-50 cursor-pointer">Cancel</button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    {{-- Search, Category, Price Range, Access & Sort Controls --}}
    <form action="{{ route('vendor.themes.index') }}" method="GET" class="rounded-2xl border border-slate-200 bg-white p-4 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-3 items-end">
        <div class="lg:col-span-3">
            <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1">Search</label>
            <input type="search" name="q" value="{{ $search }}" placeholder="Theme name or design style..."
                   class="w-full rounded-xl border border-slate-200 px-3.5 py-2 text-xs font-medium text-slate-900 focus:border-zinc-900 focus:outline-none">
        </div>
        <div class="lg:col-span-3">
            <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1">Category</label>
            <select name="category" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-xs font-medium text-slate-900">
                <option value="all">All Categories (61)</option>
                @foreach($categories as $catKey => $catLabel)
                    <option value="{{ $catKey }}" {{ $category === $catKey ? 'selected' : '' }}>{{ $catLabel }}</option>
                @endforeach
            </select>
        </div>
        <div class="lg:col-span-2">
            <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1">Price Range</label>
            <select name="price" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-xs font-medium text-slate-900">
                <option value="all" {{ $priceFilter === 'all' ? 'selected' : '' }}>Any Price</option>
                <option value="free" {{ $priceFilter === 'free' ? 'selected' : '' }}>Included / Free</option>
                <option value="under_2500" {{ $priceFilter === 'under_2500' ? 'selected' : '' }}>Up to ₹2,500</option>
                <option value="2500_7500" {{ $priceFilter === '2500_7500' ? 'selected' : '' }}>₹2,500 – ₹7,500</option>
                <option value="above_7500" {{ $priceFilter === 'above_7500' ? 'selected' : '' }}>₹7,500+</option>
            </select>
        </div>
        <div class="lg:col-span-2">
            <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1">Access</label>
            <select name="access" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-xs font-medium text-slate-900">
                <option value="all" {{ $accessFilter === 'all' ? 'selected' : '' }}>All Themes</option>
                <option value="entitled" {{ $accessFilter === 'entitled' ? 'selected' : '' }}>Accessible / Owned</option>
                <option value="available" {{ $accessFilter === 'available' ? 'selected' : '' }}>Requires Approval</option>
                <option value="pending" {{ $accessFilter === 'pending' ? 'selected' : '' }}>Pending Request</option>
                <option value="live" {{ $accessFilter === 'live' ? 'selected' : '' }}>Currently Live</option>
            </select>
        </div>
        <div class="lg:col-span-2 flex gap-2">
            <select name="sort" aria-label="Sort themes" class="flex-1 rounded-xl border border-slate-200 px-2.5 py-2 text-xs font-medium text-slate-900">
                <option value="featured" {{ $sort === 'featured' ? 'selected' : '' }}>Featured</option>
                <option value="newest" {{ $sort === 'newest' ? 'selected' : '' }}>Newest</option>
                <option value="name" {{ $sort === 'name' ? 'selected' : '' }}>Name A–Z</option>
                <option value="price_low" {{ $sort === 'price_low' ? 'selected' : '' }}>Price Low</option>
                <option value="price_high" {{ $sort === 'price_high' ? 'selected' : '' }}>Price High</option>
            </select>
            <button type="submit" class="px-3.5 py-2 rounded-xl bg-zinc-950 text-white text-xs font-bold hover:bg-zinc-800 cursor-pointer shrink-0">Go</button>
        </div>
    </form>

    {{-- Theme Marketplace Cards --}}
    @if($items->isEmpty())
        <div class="rounded-2xl border border-slate-200 bg-white p-14 text-center space-y-2.5">
            <p class="text-base font-extrabold text-slate-900">No themes match your current filters</p>
            <p class="text-xs text-slate-500">Try clearing your search keyword or switching category/price filters.</p>
            <a href="{{ route('vendor.themes.index') }}" class="inline-block mt-2 px-4 py-2 rounded-xl bg-zinc-950 text-white text-xs font-bold">Reset All Filters</a>
        </div>
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
            @foreach($items as $entry)
                @php
                    $t = $entry['theme'];
                    $m = $entry['manifest'];
                    $pal = $m['palette'] ?? ['bg' => '#fafaf9', 'surface' => '#ffffff', 'surface_alt' => '#ececec', 'text' => '#18181b', 'accent' => '#18181b', 'accent_contrast' => '#ffffff'];
                    $lay = $m['layout'] ?? ['hero_template' => 'asymmetric-campaign', 'header_template' => 'couture-centered', 'page_architecture' => 'editorial-runway'];
                    $arch = $lay['page_architecture'] ?? 'editorial-runway';
                    $pdpArch = $lay['pdp_architecture'] ?? 'editorial-fullbleed';
                    $thumbPhoto = !$t->is_legacy
                        ? asset('images/theme-editorial/' . ($lay['sample_art'] ?? 'fashion-editorial') . '.jpg')
                        : null;
                    $choreo = $m['motion']['choreography'] ?? ($m['motion']['profile'] ?? 'quiet-editorial');
                    $bands = $lay['band_colors'] ?? [$pal['bg'], $pal['accent'], $pal['surface_alt']];
                    $state = $entry['state'];
                    $req = $entry['request'];
                    $attrs = is_array($t->design_attributes) && !empty($t->design_attributes) ? $t->design_attributes : ($m['design_attributes'] ?? []);
                    $compat = is_array($t->compatible_categories) && !empty($t->compatible_categories) ? $t->compatible_categories : ($m['compatible_categories'] ?? []);
                @endphp
                <article class="rounded-2xl border transition-all overflow-hidden flex flex-col justify-between bg-white {{ $entry['is_live'] ? 'border-emerald-500 ring-2 ring-emerald-500/20 shadow-md' : 'border-slate-200 hover:border-slate-300 hover:shadow-lg' }}">

                    {{-- Real Live-Captured Studio Preview Showcase (All 50 Bespoke Themes) --}}
                    @php
                        $realPreviewPath = public_path('images/theme-previews/' . $t->id . '.jpg');
                        $hasRealPreview = !$t->is_legacy && file_exists($realPreviewPath);
                        $realPreviewUrl = $hasRealPreview ? asset('images/theme-previews/' . $t->id . '.jpg') . '?v=4' : null;
                    @endphp
                    @if($hasRealPreview)
                        <div class="relative aspect-[16/10] w-full overflow-hidden group bg-zinc-950" style="border-bottom: 1px solid rgba(0,0,0,0.08);">
                            <img src="{{ $realPreviewUrl }}" alt="{{ $t->name }} live storefront preview" loading="lazy"
                                 class="w-full h-full object-cover object-top transition-transform duration-700 ease-out group-hover:scale-[1.04]">
                            <div class="pointer-events-none absolute inset-x-0 top-0 h-16 bg-gradient-to-b from-black/55 to-transparent"></div>
                            <div class="pointer-events-none absolute inset-x-0 bottom-0 h-20 bg-gradient-to-t from-black/70 via-black/25 to-transparent opacity-85 group-hover:opacity-100 transition-opacity"></div>

                            {{-- Top Floating Status & Edition Pills --}}
                            <div class="absolute top-3 left-3 right-3 z-10 flex items-center justify-between gap-2">
                                <div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-black/70 backdrop-blur-md text-white text-[10px] font-mono uppercase tracking-wider border border-white/15">
                                    <span class="w-2 h-2 rounded-full" style="background-color: {{ $pal['accent'] }};"></span>
                                    <span>#{{ str_pad((string)($m['number'] ?? ''), 2, '0', STR_PAD_LEFT) }}</span>
                                    @if($t->is_featured)
                                        <span class="text-amber-300 font-sans font-bold">★</span>
                                    @endif
                                </div>
                                <div class="flex items-center gap-1.5">
                                    @if($entry['is_live'])
                                        <span class="px-2.5 py-1 rounded-full bg-emerald-500 text-white text-[9px] font-black uppercase tracking-wider shadow">Live</span>
                                    @elseif($state === 'granted')
                                        <span class="px-2.5 py-1 rounded-full bg-indigo-600 text-white text-[9px] font-black uppercase tracking-wider shadow">Owned</span>
                                    @elseif($state === 'pending')
                                        <span class="px-2.5 py-1 rounded-full bg-amber-400 text-zinc-950 text-[9px] font-black uppercase tracking-wider shadow">Pending</span>
                                    @elseif($state === 'revoked')
                                        <span class="px-2.5 py-1 rounded-full bg-rose-600 text-white text-[9px] font-black uppercase tracking-wider shadow">Revoked</span>
                                    @endif
                                </div>
                            </div>

                            {{-- Floating Hover Fullscreen Preview CTA --}}
                            <div class="absolute inset-0 z-10 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity duration-300 bg-black/25 backdrop-blur-[2px]">
                                <a href="{{ route('vendor.themes.preview', $t->id) }}" target="_blank" rel="noopener"
                                   class="px-4 py-2 rounded-full bg-white text-zinc-950 text-xs font-extrabold shadow-xl inline-flex items-center gap-1.5 hover:scale-105 transition-transform">
                                    <span>Live Fullscreen Preview</span>
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M14 4h6v6M10 14L20 4"/></svg>
                                </a>
                            </div>

                            {{-- Bottom Palette & Price Strip --}}
                            <div class="absolute bottom-2.5 left-3 right-3 z-10 flex items-center justify-between text-[10px] text-white">
                                <span class="px-2 py-0.5 rounded bg-black/60 backdrop-blur-md font-medium truncate">{{ $categories[$t->category] ?? ucfirst($t->category) }} · {{ strtoupper($lay['structural_variant'] ?? 'A') }}</span>
                                <span class="font-extrabold px-2.5 py-0.5 rounded-full shadow shrink-0" style="background-color: {{ $pal['accent'] }}; color: {{ $pal['accent_contrast'] ?? '#fff' }};">
                                    {{ $t->formatted_price }}
                                </span>
                            </div>
                        </div>
                    @else
                    {{-- Classic 11 Fallback Miniature Thumbnail --}}
                    <div class="relative p-3.5 h-56 flex flex-col justify-between overflow-hidden group"
                         style="background-color: {{ $pal['bg'] }}; color: {{ $pal['text'] }}; border-bottom: 1px solid rgba(0,0,0,0.08);">
                        @if($thumbPhoto)
                            <img src="{{ $thumbPhoto }}" alt="{{ $t->name }} preview" loading="lazy"
                                 class="pointer-events-none absolute inset-0 w-full h-full object-cover opacity-25 group-hover:scale-105 transition-transform duration-700">
                            <div class="pointer-events-none absolute inset-0" style="background: linear-gradient(180deg, {{ $pal['bg'] }}cc 0%, {{ $pal['bg'] }}99 45%, {{ $pal['bg'] }}ee 100%);"></div>
                        @endif
                        {{-- Mini Header Bar reflecting theme header architecture --}}
                        <div class="relative z-10 flex items-center justify-between gap-2 pb-2" style="border-bottom: 1px solid {{ $pal['text'] }}22;">
                            <div class="flex items-center gap-1.5 text-[9px] font-mono uppercase tracking-wider opacity-85">
                                <span class="w-2 h-2 rounded-full" style="background-color: {{ $pal['accent'] }};"></span>
                                <span>{{ $t->is_legacy ? 'Classic 11' : ('#' . str_pad((string)($m['number'] ?? ''), 2, '0', STR_PAD_LEFT)) }}</span>
                                @if($t->is_featured)
                                    <span class="px-1.5 py-0.5 rounded font-sans font-bold" style="background-color: {{ $pal['accent'] }}; color: {{ $pal['accent_contrast'] ?? '#fff' }};">Featured</span>
                                @endif
                            </div>
                            <div class="flex items-center gap-1.5">
                                @if($entry['is_live'])
                                    <span class="px-2 py-0.5 rounded-full bg-emerald-600 text-white text-[9px] font-black uppercase">Live</span>
                                @elseif($state === 'granted')
                                    <span class="px-2 py-0.5 rounded-full bg-indigo-600 text-white text-[9px] font-black uppercase">Owned</span>
                                @elseif($state === 'pending')
                                    <span class="px-2 py-0.5 rounded-full bg-amber-400 text-zinc-950 text-[9px] font-black uppercase">Pending</span>
                                @elseif($state === 'revoked')
                                    <span class="px-2 py-0.5 rounded-full bg-rose-600 text-white text-[9px] font-black uppercase">Revoked</span>
                                @endif
                            </div>
                        </div>

                        {{-- Mini Architectural Wireframe reflecting THIS theme's exact Page Architecture --}}
                        <div class="relative z-10 flex-1 flex flex-col">
                        @if($arch === 'left-rail-gallery')
                            <div class="grid grid-cols-12 gap-2 my-2 flex-1">
                                <div class="col-span-4 rounded p-2 flex flex-col justify-between" style="background-color: {{ $pal['surface'] }}; border-right: 1px solid {{ $pal['text'] }}24;">
                                    <span class="text-[8px] font-mono uppercase opacity-65">Left Rail</span>
                                    <div class="space-y-1">
                                        <div class="h-1 w-full rounded" style="background-color: {{ $pal['accent'] }};"></div>
                                        <div class="h-1 w-3/4 rounded opacity-35" style="background-color: {{ $pal['text'] }};"></div>
                                        <div class="h-1 w-2/3 rounded opacity-25" style="background-color: {{ $pal['text'] }};"></div>
                                    </div>
                                </div>
                                <div class="col-span-8 flex flex-col justify-between p-2 rounded" style="background-color: {{ $pal['surface'] }}; border: 1px solid {{ $pal['text'] }}16;">
                                    <span class="text-xs font-bold leading-tight">{{ $t->name }}</span>
                                    <div class="grid grid-cols-3 gap-1 mt-1.5 flex-1">
                                        <div class="rounded-xs" style="background-color: {{ $pal['surface_alt'] }};"></div>
                                        <div class="rounded-xs" style="background-color: {{ $pal['surface_alt'] }};"></div>
                                        <div class="rounded-xs" style="background-color: {{ $pal['accent'] }}33;"></div>
                                    </div>
                                </div>
                            </div>
                        @elseif($arch === 'split-screen-showroom')
                            <div class="grid grid-cols-12 rounded-lg overflow-hidden my-2 flex-1" style="border: 1px solid {{ $pal['text'] }}18;">
                                <div class="col-span-6 p-2.5 flex flex-col justify-between" style="background-color: {{ $pal['surface'] }}; border-right: 1px solid {{ $pal['text'] }}14;">
                                    <span class="text-[8px] uppercase tracking-wider opacity-60">50/50 Sticky</span>
                                    <span class="text-sm font-bold leading-tight">{{ $t->name }}</span>
                                    <span class="w-10 h-1.5 rounded-full" style="background-color: {{ $pal['accent'] }};"></span>
                                </div>
                                <div class="col-span-6 p-1.5 flex flex-col gap-1" style="background-color: {{ $pal['surface_alt'] }};">
                                    <div class="flex-1 rounded-xs" style="background-color: {{ $pal['text'] }}18;"></div>
                                    <div class="h-5 rounded-xs" style="background-color: {{ $pal['accent'] }}33;"></div>
                                </div>
                            </div>
                        @elseif($arch === 'brutalist-poster')
                            <div class="my-2 flex-1 flex flex-col justify-between gap-1.5">
                                <div class="grid grid-cols-12 flex-1" style="border: 2px solid {{ $pal['text'] }}; background-color: {{ $pal['surface'] }};">
                                    <div class="col-span-7 p-2 flex flex-col justify-between" style="border-right: 2px solid {{ $pal['text'] }};">
                                        <span class="text-[7px] font-black uppercase px-1 py-0.5 w-fit" style="background-color: {{ $pal['accent'] }}; color: {{ $pal['accent_contrast'] ?? '#fff' }};">BRUTALIST</span>
                                        <span class="text-sm font-black uppercase leading-none">{{ $t->name }}</span>
                                    </div>
                                    <div class="col-span-5" style="background-color: {{ $pal['surface_alt'] }};"></div>
                                </div>
                                <div class="px-2 py-0.5 text-[7px] font-mono font-black uppercase truncate" style="background-color: {{ $pal['text'] }}; color: {{ $pal['bg'] }};">/// MARQUEE TICKER /// {{ strtoupper($t->name) }} ///</div>
                            </div>
                        @elseif($arch === 'swiss-manifesto')
                            <div class="my-2 flex-1 flex flex-col" style="border: 1px solid {{ $pal['text'] }}30;">
                                <div class="px-2 py-0.5 flex justify-between text-[7px] font-mono uppercase" style="border-bottom: 1px solid {{ $pal['text'] }}25;">
                                    <span>[00/SWISS]</span>
                                    <span>GRID MATRIX</span>
                                </div>
                                <div class="grid grid-cols-12 flex-1">
                                    <div class="col-span-4 p-1.5 space-y-1 text-[7px] font-mono" style="border-right: 1px solid {{ $pal['text'] }}25;">
                                        <div class="font-bold">01 ALL</div>
                                        <div class="opacity-60">02 INDEX</div>
                                    </div>
                                    <div class="col-span-8 p-2 flex flex-col justify-between">
                                        <span class="text-xs font-extrabold leading-tight">{{ $t->name }}</span>
                                        <div class="grid grid-cols-3 gap-1 h-7">
                                            <div style="border: 1px solid {{ $pal['text'] }}22; background: {{ $pal['surface_alt'] }};"></div>
                                            <div style="border: 1px solid {{ $pal['text'] }}22; background: {{ $pal['surface_alt'] }};"></div>
                                            <div style="border: 1px solid {{ $pal['text'] }}22; background: {{ $pal['accent'] }}33;"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @elseif($arch === 'cinematic-dark-stage')
                            <div class="my-2 flex-1 rounded-lg p-2.5 flex flex-col justify-between" style="background: radial-gradient(circle at 75% 25%, {{ $pal['accent'] }}33, #070709 70%); border: 1px solid rgba(255,255,255,0.14); color: #fff;">
                                <div class="flex items-center justify-between">
                                    <span class="text-[8px] font-mono uppercase" style="color: {{ $pal['accent'] }};">Cinema Stage</span>
                                    <span class="text-[8px] font-mono opacity-60">3D Reel &rarr;</span>
                                </div>
                                <span class="text-sm font-extrabold leading-tight">{{ $t->name }}</span>
                                <div class="flex gap-1.5 h-7">
                                    <div class="flex-1 rounded-xs bg-white/10 border border-white/15"></div>
                                    <div class="flex-1 rounded-xs bg-white/10 border border-white/15"></div>
                                    <div class="w-8 rounded-xs" style="background-color: {{ $pal['accent'] }}44;"></div>
                                </div>
                            </div>
                        @elseif($arch === 'spatial-glass-studio')
                            <div class="my-2 flex-1 rounded-xl p-2.5 flex flex-col justify-between" style="background: linear-gradient(135deg, {{ $pal['accent'] }}22, {{ $pal['surface'] }}); border: 1px solid {{ $pal['text'] }}1c;">
                                <div class="flex items-center justify-between">
                                    <span class="px-2 py-0.5 rounded-full text-[7px] font-bold" style="background-color: {{ $pal['surface'] }}; border: 1px solid {{ $pal['text'] }}18;">Glass 3D</span>
                                    <span class="w-6 h-1.5 rounded-full" style="background-color: {{ $pal['accent'] }};"></span>
                                </div>
                                <span class="text-sm font-bold leading-tight">{{ $t->name }}</span>
                                <div class="flex gap-1.5">
                                    <span class="px-2 py-0.5 rounded-full text-[7px]" style="background-color: {{ $pal['surface'] }};">Capsule</span>
                                    <span class="px-2 py-0.5 rounded-full text-[7px]" style="background-color: {{ $pal['accent'] }}; color: {{ $pal['accent_contrast'] ?? '#fff' }};">Spatial</span>
                                </div>
                            </div>
                        @elseif($arch === 'bento-modular-flagship')
                            <div class="grid grid-cols-12 gap-1.5 my-2 flex-1">
                                <div class="col-span-7 rounded-lg p-2.5 flex flex-col justify-between" style="background-color: {{ $pal['surface'] }}; border: 1px solid {{ $pal['text'] }}14;">
                                    <span class="text-[8px] font-mono uppercase opacity-60">Bento 12-Col</span>
                                    <span class="text-sm font-extrabold leading-tight">{{ $t->name }}</span>
                                    <span class="inline-block w-12 h-2 rounded-full" style="background-color: {{ $pal['accent'] }};"></span>
                                </div>
                                <div class="col-span-5 grid grid-cols-2 gap-1.5">
                                    <div class="col-span-2 rounded-lg" style="background-color: {{ $pal['surface_alt'] }};"></div>
                                    <div class="rounded-lg" style="background-color: {{ $pal['accent'] }}33;"></div>
                                    <div class="rounded-lg" style="background-color: {{ $pal['surface'] }}; border: 1px solid {{ $pal['text'] }}15;"></div>
                                </div>
                            </div>
                        @elseif($arch === 'color-block-editorial')
                            <div class="grid grid-cols-12 rounded-lg overflow-hidden my-2 flex-1" style="border: 1.5px solid {{ $pal['text'] }}44;">
                                <div class="col-span-7 p-2.5 flex flex-col justify-between" style="background-color: {{ $bands[1] ?? $pal['accent'] }}; color: #ffffff;">
                                    <span class="text-[8px] uppercase font-black opacity-85">Color-Block</span>
                                    <span class="text-sm font-black leading-tight">{{ $t->name }}</span>
                                </div>
                                <div class="col-span-5 p-2 flex flex-col justify-between" style="background-color: {{ $bands[2] ?? $pal['surface_alt'] }};">
                                    <div class="h-2 w-8 bg-black/80"></div>
                                    <div class="h-10 rounded-xs bg-white/90"></div>
                                </div>
                            </div>
                        @elseif($arch === 'pro-commerce-catalog')
                            <div class="my-2 flex-1 flex flex-col justify-between gap-1.5">
                                <div class="rounded px-2.5 py-1 flex items-center justify-between" style="background-color: {{ $pal['surface'] }}; border: 1px solid {{ $pal['text'] }}18;">
                                    <span class="text-xs font-extrabold">{{ $t->name }}</span>
                                    <span class="px-2 py-0.5 rounded text-[8px] font-bold" style="background-color: {{ $pal['accent'] }}; color: {{ $pal['accent_contrast'] ?? '#fff' }};">Search</span>
                                </div>
                                <div class="grid grid-cols-12 gap-1.5 flex-1">
                                    <div class="col-span-3 rounded p-1 space-y-1" style="background-color: {{ $pal['surface'] }}; border: 1px solid {{ $pal['text'] }}14;">
                                        <div class="h-1.5 rounded" style="background-color: {{ $pal['accent'] }};"></div>
                                        <div class="h-1 rounded opacity-30" style="background-color: {{ $pal['text'] }};"></div>
                                    </div>
                                    <div class="col-span-9 grid grid-cols-3 gap-1">
                                        @for($k = 0; $k < 3; $k++)
                                            <div class="rounded p-1 flex flex-col justify-between" style="background-color: {{ $pal['surface'] }}; border: 1px solid {{ $pal['text'] }}12;">
                                                <div class="h-6 rounded-xs" style="background-color: {{ $pal['surface_alt'] }};"></div>
                                                <div class="h-1.5 w-3/4 rounded-full" style="background-color: {{ $pal['accent'] }}88;"></div>
                                            </div>
                                        @endfor
                                    </div>
                                </div>
                            </div>
                        @elseif($arch === 'typography-as-architecture')
                            <div class="my-2 flex-1 flex flex-col justify-between p-1">
                                <div class="text-xl font-black uppercase leading-none tracking-tighter truncate">{{ $t->name }}</div>
                                <div class="grid grid-cols-12 gap-1.5 h-14 mt-1">
                                    <div class="col-span-4 flex flex-col justify-end gap-1">
                                        <div class="h-1 w-full opacity-40" style="background-color: {{ $pal['text'] }};"></div>
                                        <div class="h-2.5 w-10" style="background-color: {{ $pal['accent'] }};"></div>
                                    </div>
                                    <div class="col-span-5 rounded-xs" style="background-color: {{ $pal['surface_alt'] }}; border: 1px solid {{ $pal['text'] }}18;"></div>
                                    <div class="col-span-3 rounded-xs mt-2" style="background-color: {{ $pal['accent'] }}33;"></div>
                                </div>
                            </div>
                        @elseif($arch === 'product-first-index')
                            <div class="my-2 flex-1 flex flex-col justify-between gap-1.5">
                                <div class="flex items-center justify-between text-[8px] font-mono uppercase opacity-65 pb-1" style="border-bottom: 1px solid {{ $pal['text'] }}18;">
                                    <span>{{ $t->name }}</span>
                                    <span>0-Hero Product Wall</span>
                                </div>
                                <div class="grid grid-cols-5 gap-1 flex-1">
                                    @for($k = 0; $k < 5; $k++)
                                        <div class="flex flex-col gap-0.5">
                                            <div class="flex-1 rounded-xs" style="background-color: {{ $pal['surface_alt'] }};"></div>
                                            <div class="h-1 w-3/4 opacity-50" style="background-color: {{ $pal['text'] }};"></div>
                                        </div>
                                    @endfor
                                </div>
                            </div>
                        @elseif($arch === 'b2b-specimen-ledger')
                            <div class="my-2 flex-1 flex flex-col justify-between gap-1">
                                <div class="text-[8px] font-mono uppercase opacity-70">{{ $t->name }} · Spec Rows</div>
                                @for($k = 0; $k < 3; $k++)
                                    <div class="flex items-center justify-between gap-1.5 px-1.5 py-1 rounded-xs" style="background-color: {{ $pal['surface'] }}; border: 1px solid {{ $pal['text'] }}18;">
                                        <div class="w-4 h-3 rounded-xs" style="background-color: {{ $pal['surface_alt'] }};"></div>
                                        <div class="flex-1 h-1.5 rounded opacity-35" style="background-color: {{ $pal['text'] }};"></div>
                                        <div class="w-5 h-2 rounded-xs" style="background-color: {{ $pal['accent'] }};"></div>
                                    </div>
                                @endfor
                            </div>
                        @elseif($arch === 'horizontal-gallery-wall')
                            <div class="my-2 flex-1 grid grid-cols-4 gap-1" style="border: 1px solid {{ $pal['text'] }}20;">
                                @for($k = 1; $k <= 4; $k++)
                                    <div class="p-1 flex flex-col justify-between" style="border-right: 1px solid {{ $pal['text'] }}14;">
                                        <span class="text-[8px] font-serif opacity-40">0{{ $k }}</span>
                                        <div class="h-12 rounded-xs" style="background-color: {{ $k === 2 ? ($pal['accent'] . '33') : $pal['surface_alt'] }};"></div>
                                        <div class="h-1 w-2/3 opacity-50" style="background-color: {{ $pal['text'] }};"></div>
                                    </div>
                                @endfor
                            </div>
                        @elseif($arch === 'sculptural-arch-sanctuary')
                            <div class="my-2 flex-1 flex flex-col justify-between items-center">
                                <span class="text-xs font-serif font-semibold">{{ $t->name }}</span>
                                <div class="grid grid-cols-3 gap-2 w-full px-3 flex-1 items-end mt-1">
                                    <div class="h-12 rounded-t-full" style="background-color: {{ $pal['surface_alt'] }}; border: 1px solid {{ $pal['text'] }}18;"></div>
                                    <div class="h-16 rounded-t-full" style="background-color: {{ $pal['accent'] }}33; border: 1px solid {{ $pal['accent'] }}66;"></div>
                                    <div class="h-12 rounded-t-full" style="background-color: {{ $pal['surface_alt'] }}; border: 1px solid {{ $pal['text'] }}18;"></div>
                                </div>
                            </div>
                        @else
                            {{-- Editorial Runway Offset Pair Miniature --}}
                            <div class="grid grid-cols-12 gap-2 items-center my-2 flex-1">
                                <div class="col-span-6 space-y-1.5">
                                    <span class="text-[8px] uppercase tracking-[0.2em] opacity-65">Editorial Runway</span>
                                    <div class="text-base font-bold leading-tight">{{ $t->name }}</div>
                                    <div class="text-[9px] opacity-70 truncate">{{ $m['typography']['heading_font'] ?? 'Editorial' }}</div>
                                </div>
                                <div class="col-span-6 grid grid-cols-3 gap-1.5 h-20">
                                    <div class="col-span-2 rounded" style="background-color: {{ $pal['surface'] }}; border: 1px solid {{ $pal['text'] }}1a;"></div>
                                    <div class="rounded mt-3" style="background-color: {{ $pal['accent'] }}33; border: 1px solid {{ $pal['accent'] }}55;"></div>
                                </div>
                            </div>
                        @endif
                        </div>

                        <div class="relative z-10 flex items-center justify-between text-[10px] pt-1 opacity-90">
                            <span class="truncate">{{ $categories[$t->category] ?? ucfirst($t->category) }}</span>
                            <span class="font-extrabold px-2 py-0.5 rounded shrink-0" style="background-color: {{ $pal['accent'] }}; color: {{ $pal['accent_contrast'] ?? '#fff' }};">
                                {{ $t->formatted_price }}
                            </span>
                        </div>
                    </div>
                    @endif

                    {{-- Metadata, Attributes & Direct Actions --}}
                    <div class="p-4 sm:p-5 flex-1 flex flex-col justify-between gap-4">
                        <div class="space-y-2.5">
                            <div class="flex items-baseline justify-between gap-2">
                                <h3 class="text-base font-extrabold text-slate-900">{{ $t->name }}</h3>
                                <span class="text-xs font-extrabold text-slate-900 shrink-0">{{ $t->formatted_price }}</span>
                            </div>
                            <p class="text-xs text-slate-500 leading-relaxed line-clamp-2">{{ $t->description ?: $t->tagline }}</p>

                            @if(!empty($attrs))
                                <div class="flex flex-wrap gap-1.5">
                                    @if(!$t->is_legacy)
                                        <span class="px-2 py-0.5 rounded-md bg-zinc-900 text-white text-[10px] font-bold">{{ ucwords(str_replace('-', ' ', $arch)) }}</span>
                                        <span class="px-2 py-0.5 rounded-md bg-indigo-50 text-indigo-700 text-[10px] font-bold">GSAP: {{ ucwords(str_replace('-', ' ', $choreo)) }}</span>
                                    @endif
                                    @foreach(array_slice($attrs, 0, 2) as $attr)
                                        <span class="px-2 py-0.5 rounded-md bg-slate-100 text-slate-700 text-[10px] font-semibold">{{ $attr }}</span>
                                    @endforeach
                                </div>
                            @endif

                            @if(!empty($compat))
                                <p class="text-[10px] text-slate-400">
                                    Best for: <span class="text-slate-600 font-medium">{{ implode(', ', array_map(fn ($c) => ucwords(str_replace('_', ' ', (string) $c)), array_slice($compat, 0, 4))) }}</span>
                                </p>
                            @endif
                        </div>

                        <div class="flex flex-wrap items-center gap-2 pt-3 border-t border-slate-100">
                            @if($entry['preview_url'])
                                <a href="{{ $entry['preview_url'] }}" target="_blank" rel="noopener"
                                   class="flex-1 text-center px-3 py-2.5 rounded-xl border border-slate-300 text-xs font-bold text-slate-800 hover:bg-slate-50 transition inline-flex items-center justify-center gap-1.5">
                                    <span>Preview</span>
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M14 4h6v6M10 14L20 4M18 14v5a1 1 0 01-1 1H5a1 1 0 01-1-1V7a1 1 0 011-1h5"/></svg>
                                </a>
                            @else
                                <span class="flex-1 text-center px-3 py-2.5 rounded-xl bg-slate-100 text-slate-400 text-xs font-semibold">Preview Off</span>
                            @endif

                            @if($entry['is_entitled'])
                                @if($t->is_legacy)
                                    <a href="{{ route('vendor.design.index') }}" class="flex-1 text-center px-3 py-2.5 rounded-xl bg-zinc-950 text-white text-xs font-bold hover:bg-zinc-800 transition">Classic Studio</a>
                                @else
                                    <a href="{{ route('vendor.themes.customize', $t->theme_id) }}" class="flex-1 text-center px-3 py-2.5 rounded-xl bg-zinc-950 text-white text-xs font-bold hover:bg-zinc-800 transition">
                                        {{ $entry['is_live'] ? 'Customize Live' : 'Select & Draft' }}
                                    </a>
                                @endif
                            @elseif($state === 'pending' && $req)
                                @if($entry['whatsapp_url'])
                                    <a href="{{ $entry['whatsapp_url'] }}" target="_blank" rel="noopener" class="flex-1 text-center px-3 py-2.5 rounded-xl bg-emerald-600 text-white text-xs font-bold hover:bg-emerald-700 transition">
                                        WhatsApp · {{ $req->reference_code }}
                                    </a>
                                @else
                                    <span class="flex-1 text-center px-3 py-2.5 rounded-xl bg-amber-50 border border-amber-200 text-amber-800 text-[11px] font-bold">Pending: {{ $req->reference_code }}</span>
                                @endif
                            @elseif($state === 'revoked')
                                <span class="flex-1 text-center px-3 py-2.5 rounded-xl bg-rose-50 border border-rose-200 text-rose-700 text-[11px] font-bold">Access Revoked</span>
                            @elseif($state === 'unavailable')
                                <span class="flex-1 text-center px-3 py-2.5 rounded-xl bg-slate-100 text-slate-500 text-[11px] font-bold">Unavailable</span>
                            @else
                                <form action="{{ route('vendor.themes.request', $t->theme_id) }}" method="POST" class="flex-1">
                                    @csrf
                                    <button type="submit" class="w-full px-3 py-2.5 rounded-xl bg-emerald-600 text-white text-xs font-bold hover:bg-emerald-700 transition cursor-pointer">
                                        Request Access
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                </article>
            @endforeach
        </div>
    @endif
</div>
</x-layouts.vendor>
