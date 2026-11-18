<x-layouts.vendor title="Storefront Theme Marketplace">
<div class="py-6 sm:py-8 px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto space-y-8">

    {{-- Header Banner --}}
    <div class="flex flex-col lg:flex-row lg:items-end justify-between gap-4">
        <div>
            <div class="inline-flex items-center gap-2 px-2.5 py-1 rounded-full bg-zinc-900 text-white text-[10px] font-bold uppercase tracking-wider mb-2">
                <span>61 Storefront Themes</span>
                <span class="opacity-60">·</span>
                <span>11 Classic + 50 Studio Editions</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-slate-900">Storefront Theme Marketplace</h1>
            <p class="text-xs sm:text-sm text-slate-500 mt-1 max-w-2xl">
                Preview any theme safely across desktop, tablet, and mobile without changing your live store. Paid themes are unlocked by K71 Admin after WhatsApp payment coordination and verification.
            </p>
        </div>
        <div class="flex flex-wrap items-center gap-2.5">
            <a href="{{ route('vendor.design.index') }}" class="px-4 py-2.5 rounded-xl border border-slate-200 bg-white text-xs font-bold text-slate-700 hover:bg-slate-50 transition">
                Classic 11 Studio
            </a>
            @if($vendor->storefront_url)
                <a href="{{ $vendor->storefront_url }}" target="_blank" class="px-4 py-2.5 rounded-xl bg-zinc-900 text-white text-xs font-bold hover:bg-zinc-800 transition">
                    Open Live Storefront &rarr;
                </a>
            @endif
        </div>
    </div>

    {{-- Flash Notifications & Instant WhatsApp Link Banner --}}
    @if(session('success'))
        <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-900 text-xs sm:text-sm space-y-2.5">
            <p class="font-semibold">{{ session('success') }}</p>
            @if(session('recent_whatsapp_url'))
                <div class="flex flex-wrap items-center gap-3 pt-1">
                    <a href="{{ session('recent_whatsapp_url') }}" target="_blank" rel="noopener"
                       class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-emerald-600 text-white text-xs font-bold hover:bg-emerald-700 transition">
                        Open WhatsApp to Coordinate Payment &rarr;
                    </a>
                    <span class="text-[11px] text-emerald-700">Note: Sending a WhatsApp message does not automatically activate the theme; K71 Admin unlocks it after verifying payment.</span>
                </div>
            @elseif(!$adminWhatsAppConfigured)
                <p class="text-[11px] text-amber-800 font-medium">Official admin WhatsApp number is not yet configured in Admin Theme Settings. Your request reference is saved in the admin queue.</p>
            @endif
        </div>
    @endif

    @if(session('error'))
        <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-xs sm:text-sm font-semibold">
            {{ session('error') }}
        </div>
    @endif

    {{-- Active / Recent Vendor Requests Strip --}}
    @if($vendorRequests->isNotEmpty())
        <div class="rounded-2xl border border-slate-200 bg-white p-4 sm:p-5 space-y-3">
            <div class="flex items-center justify-between">
                <h2 class="text-sm font-bold text-slate-900">Your Theme Access Requests</h2>
                <span class="text-[11px] text-slate-500">Payment verification &amp; access grants are managed separately by K71 Admin</span>
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
                            <th class="py-2 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($vendorRequests as $req)
                            @php $waHref = $service->buildWhatsAppRequestUrl($req); @endphp
                            <tr>
                                <td class="py-2.5 pr-3 font-mono font-bold text-slate-800">{{ $req->reference_code }}</td>
                                <td class="py-2.5 pr-3 font-semibold text-slate-900">{{ $req->theme?->name ?? $req->theme_id }}</td>
                                <td class="py-2.5 pr-3 font-semibold text-slate-700">{{ $req->formatted_price_snapshot }}</td>
                                <td class="py-2.5 pr-3">
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ $req->isPaymentConfirmed() ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                                        {{ ucwords(str_replace('_', ' ', $req->payment_verification_status)) }}
                                    </span>
                                </td>
                                <td class="py-2.5 pr-3">
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-800">
                                        {{ $req->status_label }}
                                    </span>
                                </td>
                                <td class="py-2.5 text-right whitespace-nowrap space-x-1.5">
                                    @if($req->isPending() && $waHref)
                                        <a href="{{ $waHref }}" target="_blank" rel="noopener" class="inline-flex items-center px-2.5 py-1 rounded-lg bg-emerald-600 text-white text-[11px] font-bold hover:bg-emerald-700">
                                            WhatsApp Admin
                                        </a>
                                    @endif
                                    @if(in_array($req->status, ['requested', 'pending_review', 'payment_awaiting_confirmation'], true))
                                        <form action="{{ route('vendor.themes.request.cancel', $req) }}" method="POST" class="inline" onsubmit="return confirm('Cancel this pending theme request?');">
                                            @csrf
                                            <button type="submit" class="px-2.5 py-1 rounded-lg border border-slate-200 text-[11px] font-semibold text-slate-600 hover:bg-slate-50">Cancel</button>
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

    {{-- Search, Category, Access & Price Filters --}}
    <form action="{{ route('vendor.themes.index') }}" method="GET" class="rounded-2xl border border-slate-200 bg-white p-4 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-3 items-end">
        <div class="lg:col-span-4">
            <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1">Search Themes</label>
            <input type="search" name="q" value="{{ $search }}" placeholder="Search by name, style, or keyword..."
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
            <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1">Access Status</label>
            <select name="access" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-xs font-medium text-slate-900">
                <option value="all" {{ $accessFilter === 'all' ? 'selected' : '' }}>All Themes</option>
                <option value="entitled" {{ $accessFilter === 'entitled' ? 'selected' : '' }}>Unlocked / Included</option>
                <option value="available" {{ $accessFilter === 'available' ? 'selected' : '' }}>Available to Request</option>
                <option value="pending" {{ $accessFilter === 'pending' ? 'selected' : '' }}>Request Pending</option>
                <option value="live" {{ $accessFilter === 'live' ? 'selected' : '' }}>Currently Live</option>
            </select>
        </div>
        <div class="lg:col-span-2">
            <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1">Sort By</label>
            <select name="sort" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-xs font-medium text-slate-900">
                <option value="featured" {{ $sort === 'featured' ? 'selected' : '' }}>Featured Order</option>
                <option value="name" {{ $sort === 'name' ? 'selected' : '' }}>Name (A–Z)</option>
                <option value="price_low" {{ $sort === 'price_low' ? 'selected' : '' }}>Price: Low to High</option>
                <option value="price_high" {{ $sort === 'price_high' ? 'selected' : '' }}>Price: High to Low</option>
            </select>
        </div>
        <div class="lg:col-span-1 flex gap-1.5">
            <button type="submit" class="w-full px-3 py-2 rounded-xl bg-zinc-900 text-white text-xs font-bold hover:bg-zinc-800 transition">Filter</button>
        </div>
    </form>

    {{-- Theme Catalogue Grid (Lightweight Art-Directed Miniature Previews) --}}
    @if($items->isEmpty())
        <div class="rounded-2xl border border-slate-200 bg-white p-12 text-center space-y-2">
            <p class="text-sm font-bold text-slate-900">No themes matched your filter</p>
            <a href="{{ route('vendor.themes.index') }}" class="inline-block text-xs font-bold text-indigo-600 hover:underline">Reset filters</a>
        </div>
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-5">
            @foreach($items as $entry)
                @php
                    $t = $entry['theme'];
                    $m = $entry['manifest'];
                    $pal = $m['palette'] ?? ['bg' => '#fafaf9', 'surface' => '#ffffff', 'text' => '#18181b', 'accent' => '#18181b'];
                    $state = $entry['state'];
                    $req = $entry['request'];
                @endphp
                <article class="rounded-2xl border transition-all overflow-hidden flex flex-col justify-between bg-white {{ $entry['is_live'] ? 'border-emerald-500 ring-2 ring-emerald-500/20' : 'border-slate-200 hover:border-slate-300 hover:shadow-md' }}">

                    {{-- Lightweight Visual Miniature Thumbnail (No heavy iframe instantiation) --}}
                    <div class="relative p-4 h-44 flex flex-col justify-between overflow-hidden"
                         style="background-color: {{ $pal['bg'] }}; color: {{ $pal['text'] }}; border-bottom: 1px solid rgba(0,0,0,0.06);">
                        <div class="flex items-center justify-between gap-2 text-[10px] font-bold">
                            <span class="px-2 py-0.5 rounded-full" style="background-color: {{ $pal['surface'] }}; color: {{ $pal['text'] }}; border: 1px solid {{ $pal['accent'] }}44;">
                                {{ $t->is_legacy ? 'Classic 11' : ('#' . str_pad((string)($m['number'] ?? ''), 2, '0', STR_PAD_LEFT) . ' · ' . ucfirst($t->category)) }}
                            </span>
                            @if($entry['is_live'])
                                <span class="px-2 py-0.5 rounded-full bg-emerald-600 text-white uppercase tracking-wider">Live Now</span>
                            @elseif($state === 'granted')
                                <span class="px-2 py-0.5 rounded-full bg-indigo-600 text-white uppercase tracking-wider">Access Granted</span>
                            @elseif($state === 'pending')
                                <span class="px-2 py-0.5 rounded-full bg-amber-500 text-black uppercase tracking-wider">Pending Review</span>
                            @elseif($state === 'revoked')
                                <span class="px-2 py-0.5 rounded-full bg-rose-600 text-white uppercase tracking-wider">Revoked</span>
                            @elseif($state === 'unavailable')
                                <span class="px-2 py-0.5 rounded-full bg-zinc-700 text-white uppercase tracking-wider">Unavailable</span>
                            @endif
                        </div>

                        {{-- Art-Directed Mini Layout Wireframe Preview --}}
                        <div class="my-auto py-2 grid grid-cols-12 gap-2 items-center">
                            <div class="col-span-7 space-y-1.5">
                                <div class="h-1.5 w-10 rounded-full" style="background-color: {{ $pal['accent'] }};"></div>
                                <div class="text-base font-extrabold leading-tight truncate">{{ $t->name }}</div>
                                <div class="text-[10px] opacity-75 line-clamp-1">{{ $m['typography']['heading_font'] ?? 'Editorial' }} · {{ $m['layout']['hero_variant'] ?? 'Storefront' }}</div>
                            </div>
                            <div class="col-span-5 grid grid-cols-2 gap-1.5">
                                <div class="h-14 rounded-lg" style="background-color: {{ $pal['surface'] }}; border: 1px solid {{ $pal['accent'] }}33;"></div>
                                <div class="h-14 rounded-lg" style="background-color: {{ $pal['accent'] }}22; border: 1px solid {{ $pal['accent'] }}44;"></div>
                            </div>
                        </div>

                        <div class="flex items-center justify-between text-[10px] opacity-80">
                            <span>v{{ $t->version }}</span>
                            <span class="font-bold px-2 py-0.5 rounded" style="background-color: {{ $pal['accent'] }}; color: {{ $pal['accent_contrast'] ?? '#ffffff' }};">
                                {{ $t->formatted_price }}
                            </span>
                        </div>
                    </div>

                    {{-- Card Metadata & Strict State-Driven Actions --}}
                    <div class="p-4 flex-1 flex flex-col justify-between gap-4">
                        <div class="space-y-1.5">
                            <div class="flex items-center justify-between gap-2">
                                <h3 class="text-sm font-extrabold text-slate-900">{{ $t->name }}</h3>
                                <span class="text-xs font-extrabold text-slate-900">{{ $t->formatted_price }}</span>
                            </div>
                            <p class="text-xs text-slate-500 leading-relaxed line-clamp-2">{{ $t->description ?: $t->tagline }}</p>
                            @if($entry['has_draft'] && !$entry['is_live'])
                                <p class="text-[11px] font-semibold text-indigo-600">Unpublished draft saved</p>
                            @endif
                        </div>

                        <div class="flex flex-wrap items-center gap-2 pt-2 border-t border-slate-100">
                            <a href="{{ route('vendor.themes.preview', $t->theme_id) }}"
                               class="flex-1 text-center px-3 py-2 rounded-xl border border-slate-200 text-xs font-bold text-slate-700 hover:bg-slate-50 transition">
                                Live Preview
                            </a>

                            @if($entry['is_entitled'])
                                @if($t->is_legacy)
                                    <a href="{{ route('vendor.design.index') }}"
                                       class="flex-1 text-center px-3 py-2 rounded-xl bg-zinc-900 text-white text-xs font-bold hover:bg-zinc-800 transition">
                                        Open Classic Studio
                                    </a>
                                @else
                                    <a href="{{ route('vendor.themes.customize', $t->theme_id) }}"
                                       class="flex-1 text-center px-3 py-2 rounded-xl bg-zinc-900 text-white text-xs font-bold hover:bg-zinc-800 transition">
                                        {{ $entry['is_live'] ? 'Customize Live' : 'Customize & Publish' }}
                                    </a>
                                @endif
                            @elseif($state === 'pending' && $req)
                                @if($entry['whatsapp_url'])
                                    <a href="{{ $entry['whatsapp_url'] }}" target="_blank" rel="noopener"
                                       class="flex-1 text-center px-3 py-2 rounded-xl bg-emerald-600 text-white text-xs font-bold hover:bg-emerald-700 transition">
                                        WhatsApp ({{ $req->reference_code }})
                                    </a>
                                @else
                                    <span class="flex-1 text-center px-3 py-2 rounded-xl bg-amber-50 border border-amber-200 text-amber-800 text-[11px] font-bold">
                                        Pending: {{ $req->reference_code }}
                                    </span>
                                @endif
                            @elseif($state === 'revoked')
                                <span class="flex-1 text-center px-3 py-2 rounded-xl bg-rose-50 border border-rose-200 text-rose-700 text-[11px] font-bold">
                                    Access Revoked
                                </span>
                            @elseif($state === 'unavailable')
                                <span class="flex-1 text-center px-3 py-2 rounded-xl bg-slate-100 text-slate-500 text-[11px] font-bold">
                                    Unavailable
                                </span>
                            @else
                                <form action="{{ route('vendor.themes.request', $t->theme_id) }}" method="POST" class="flex-1">
                                    @csrf
                                    <button type="submit"
                                            class="w-full px-3 py-2 rounded-xl bg-emerald-600 text-white text-xs font-bold hover:bg-emerald-700 transition cursor-pointer">
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
