@php
    $curVendor = auth()->user()?->vendor;
    $vType = $curVendor?->vendor_type ?? 'clothing';
    $sec = $curVendor ? $curVendor->getSectorConfig() : null;
    $isServiceProvider = $curVendor?->isServices() ?? false;
@endphp

@if($isServiceProvider)
<div class="space-y-6">
    <div>
        <div class="px-3 mb-2 text-[10px] font-black uppercase tracking-wider text-slate-400">
            Personal Website
        </div>
        <div class="space-y-1">
            <a href="{{ route('vendor.dashboard') }}"
               class="group flex items-center justify-between px-3 py-2.5 rounded-xl text-xs font-semibold transition {{ request()->routeIs('vendor.dashboard') ? 'bg-stone-900 text-white shadow-sm font-bold' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
                <span class="flex items-center gap-2.5"><span class="text-base">📊</span><span>Overview &amp; Hub</span></span>
            </a>

            <a href="{{ route('vendor.website.index') }}"
               class="group flex items-center justify-between px-3 py-2.5 rounded-xl text-xs font-semibold transition {{ request()->routeIs('vendor.website.*') ? 'bg-stone-900 text-white shadow-sm font-bold' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
                <span class="flex items-center gap-2.5"><span class="text-base">🌐</span><span>My Website</span></span>
                <span class="text-[9px] uppercase font-black px-1.5 py-0.5 rounded {{ $curVendor->isWebsitePublished() ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                    {{ $curVendor->isWebsitePublished() ? 'Live' : 'Draft' }}
                </span>
            </a>

            <a href="{{ route('vendor.design.index') }}"
               class="group flex items-center justify-between px-3 py-2.5 rounded-xl text-xs font-semibold transition {{ request()->routeIs('vendor.design.*') ? 'bg-stone-900 text-white shadow-sm font-bold' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
                <span class="flex items-center gap-2.5"><span class="text-base">🎨</span><span>Design &amp; Presets</span></span>
                <span class="text-[9px] uppercase font-black px-1.5 py-0.5 rounded bg-amber-500/15 text-amber-700">10</span>
            </a>

            <a href="{{ route('vendor.portfolio.index') }}"
               class="group flex items-center justify-between px-3 py-2.5 rounded-xl text-xs font-semibold transition {{ request()->routeIs('vendor.portfolio.*') ? 'bg-stone-900 text-white shadow-sm font-bold' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
                <span class="flex items-center gap-2.5"><span class="text-base">🗂️</span><span>Portfolio Case Studies</span></span>
            </a>
        </div>
    </div>

    <div>
        <div class="px-3 mb-2 text-[10px] font-black uppercase tracking-wider text-slate-400">
            Services &amp; Contracts
        </div>
        <div class="space-y-1">
            <a href="{{ route('vendor.services.index') }}"
               class="group flex items-center justify-between px-3 py-2.5 rounded-xl text-xs font-semibold transition {{ request()->routeIs('vendor.services.*') ? 'bg-stone-900 text-white shadow-sm font-bold' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
                <span class="flex items-center gap-2.5"><span class="text-base">🛠️</span><span>Services Studio</span></span>
            </a>

            <a href="{{ route('vendor.service_orders.index') }}"
               class="group flex items-center justify-between px-3 py-2.5 rounded-xl text-xs font-semibold transition {{ request()->routeIs('vendor.service_orders.*') ? 'bg-stone-900 text-white shadow-sm font-bold' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
                <span class="flex items-center gap-2.5"><span class="text-base">📦</span><span>Service Orders</span></span>
            </a>

            <a href="{{ route('vendor.projects.index') }}"
               class="group flex items-center justify-between px-3 py-2.5 rounded-xl text-xs font-semibold transition {{ request()->routeIs('vendor.projects.*') ? 'bg-stone-900 text-white shadow-sm font-bold' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
                <span class="flex items-center gap-2.5"><span class="text-base">💼</span><span>Open Projects &amp; Bids</span></span>
            </a>

            <a href="{{ route('vendor.contracts.index') }}"
               class="group flex items-center justify-between px-3 py-2.5 rounded-xl text-xs font-semibold transition {{ request()->routeIs('vendor.contracts.*') ? 'bg-stone-900 text-white shadow-sm font-bold' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
                <span class="flex items-center gap-2.5"><span class="text-base">📑</span><span>Active Contracts</span></span>
            </a>

            <a href="{{ route('vendor.bookings.index') }}"
               class="group flex items-center justify-between px-3 py-2.5 rounded-xl text-xs font-semibold transition {{ request()->routeIs('vendor.bookings.*') ? 'bg-stone-900 text-white shadow-sm font-bold' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
                <span class="flex items-center gap-2.5"><span class="text-base">📅</span><span>Appointments &amp; Slots</span></span>
            </a>
        </div>
    </div>

    <div>
        <div class="px-3 mb-2 text-[10px] font-black uppercase tracking-wider text-slate-400">
            Clients &amp; Insights
        </div>
        <div class="space-y-1">
            <a href="{{ route('vendor.enquiries.index') }}"
               class="group flex items-center justify-between px-3 py-2.5 rounded-xl text-xs font-semibold transition {{ request()->routeIs('vendor.enquiries.*') ? 'bg-stone-900 text-white shadow-sm font-bold' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
                <span class="flex items-center gap-2.5"><span class="text-base">✉️</span><span>Website Enquiries</span></span>
            </a>

            <a href="{{ route('messages.index') }}"
               class="group flex items-center justify-between px-3 py-2.5 rounded-xl text-xs font-semibold transition {{ request()->routeIs('messages.*') ? 'bg-stone-900 text-white shadow-sm font-bold' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
                <span class="flex items-center gap-2.5"><span class="text-base">💬</span><span>Direct Messages</span></span>
            </a>

            <a href="{{ route('vendor.reviews.index') }}"
               class="group flex items-center justify-between px-3 py-2.5 rounded-xl text-xs font-semibold transition {{ request()->routeIs('vendor.reviews.*') ? 'bg-stone-900 text-white shadow-sm font-bold' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
                <span class="flex items-center gap-2.5"><span class="text-base">★</span><span>Verified Reviews</span></span>
            </a>

            <a href="{{ route('vendor.analytics.index') }}"
               class="group flex items-center justify-between px-3 py-2.5 rounded-xl text-xs font-semibold transition {{ request()->routeIs('vendor.analytics.*') ? 'bg-stone-900 text-white shadow-sm font-bold' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
                <span class="flex items-center gap-2.5"><span class="text-base">📈</span><span>Website Analytics</span></span>
            </a>

            <a href="{{ route('vendor.subscription.index') }}"
               class="group flex items-center justify-between px-3 py-2.5 rounded-xl text-xs font-semibold transition {{ request()->routeIs('vendor.subscription.*') ? 'bg-stone-900 text-white shadow-sm font-bold' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
                <span class="flex items-center gap-2.5"><span class="text-base">🛡️</span><span>Plan &amp; Subscription</span></span>
            </a>

            <a href="{{ route('vendor.settings') }}"
               class="group flex items-center justify-between px-3 py-2.5 rounded-xl text-xs font-semibold transition {{ request()->routeIs('vendor.settings') ? 'bg-stone-900 text-white shadow-sm font-bold' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
                <span class="flex items-center gap-2.5"><span class="text-base">⚙️</span><span>Availability &amp; Settings</span></span>
            </a>
        </div>
    </div>
</div>
@else
<div class="space-y-6">
    <!-- Core Management -->
    <div>
        <div class="px-3 mb-2 text-[10px] font-black uppercase tracking-wider text-slate-400 dark:text-zinc-500">
            Core Operations
        </div>
        <div class="space-y-1">
            <a href="{{ route('vendor.dashboard') }}" 
               class="group flex items-center justify-between px-3 py-2.5 rounded-xl text-xs font-semibold transition-all duration-200 {{ request()->routeIs('vendor.dashboard') ? 'bg-stone-900 text-white dark:bg-white dark:text-stone-900 shadow-sm font-bold' : 'text-slate-600 dark:text-zinc-300 hover:bg-slate-100 dark:hover:bg-white/[0.05] hover:text-slate-900 dark:hover:text-white' }}">
                <span class="flex items-center gap-2.5">
                    <span class="text-base">📊</span>
                    <span>Dashboard & Analytics</span>
                </span>
                @if(request()->routeIs('vendor.dashboard'))
                    <span class="w-1.5 h-1.5 rounded-full bg-white dark:bg-stone-900 animate-pulse"></span>
                @endif
            </a>

            <a href="{{ route('vendor.products.index') }}" 
               class="group flex items-center justify-between px-3 py-2.5 rounded-xl text-xs font-semibold transition-all duration-200 {{ request()->routeIs('vendor.products.*') ? 'bg-stone-900 text-white dark:bg-white dark:text-stone-900 shadow-sm font-bold' : 'text-slate-600 dark:text-zinc-300 hover:bg-slate-100 dark:hover:bg-white/[0.05] hover:text-slate-900 dark:hover:text-white' }}">
                <span class="flex items-center gap-2.5">
                    <span class="text-base">{{ $sec['icon'] ?? '🛍️' }}</span>
                    <span>{{ $sec['catalog_name'] ?? 'Product Catalog' }}</span>
                </span>
                <span class="text-[10px] font-bold px-1.5 py-0.5 rounded {{ request()->routeIs('vendor.products.*') ? 'bg-white/20 text-white dark:bg-stone-900/10 dark:text-stone-900' : 'bg-slate-100 dark:bg-white/10 text-slate-500 dark:text-zinc-400' }}">
                    Items
                </span>
            </a>

            @if($vType === 'food' || ($curVendor && $curVendor->isFood()))
            <a href="{{ route('vendor.food.menu') }}"
               class="group flex items-center justify-between px-3 py-2.5 rounded-xl text-xs font-semibold transition-all duration-200 {{ request()->routeIs('vendor.food.*') ? 'bg-stone-900 text-white dark:bg-white dark:text-stone-900 shadow-sm font-bold' : 'text-slate-600 dark:text-zinc-300 hover:bg-slate-100 dark:hover:bg-white/[0.05] hover:text-slate-900 dark:hover:text-white' }}">
                <span class="flex items-center gap-2.5">
                    <span class="text-base">🍽️</span>
                    <span>Menu Builder &amp; Sections</span>
                </span>
                <span class="text-[9px] uppercase font-black px-1.5 py-0.5 rounded {{ request()->routeIs('vendor.food.*') ? 'bg-white/20 text-white dark:bg-stone-900/10 dark:text-stone-900' : 'bg-orange-100 text-orange-800 border border-orange-200' }}">
                    Kitchen
                </span>
            </a>
            @endif

            @if(!in_array($vType, ['services', 'digital_services', 'real_estate']))
            <a href="{{ route('vendor.orders.index') }}" 
               class="group flex items-center justify-between px-3 py-2.5 rounded-xl text-xs font-semibold transition-all duration-200 {{ request()->routeIs('vendor.orders.*') ? 'bg-stone-900 text-white dark:bg-white dark:text-stone-900 shadow-sm font-bold' : 'text-slate-600 dark:text-zinc-300 hover:bg-slate-100 dark:hover:bg-white/[0.05] hover:text-slate-900 dark:hover:text-white' }}">
                <span class="flex items-center gap-2.5">
                    <span class="text-base">{{ $vType === 'food' ? '🛵' : ($vType === 'electronics' ? '📦' : ($vType === 'automotive' ? '🏎️' : '📦')) }}</span>
                    <span>{{ $vType === 'food' ? 'Kitchen Orders' : ($vType === 'electronics' ? 'Customer Orders & Shipments' : 'Customer Orders') }}</span>
                </span>
            </a>
            @endif

            @if($vType === 'food' || $vType === 'feasto' || $vType === 'groceries' || ($curVendor && ($curVendor->isRestaurant() || in_array($curVendor->vendor_type, ['food', 'feasto', 'groceries']))))
            <a href="{{ route('vendor.delivery_partners.index') }}" 
               class="group flex items-center justify-between px-3 py-2.5 rounded-xl text-xs font-semibold transition-all duration-200 {{ request()->routeIs('vendor.delivery_partners.*') ? 'bg-stone-900 text-white dark:bg-white dark:text-stone-900 shadow-sm font-bold' : 'text-slate-600 dark:text-zinc-300 hover:bg-slate-100 dark:hover:bg-white/[0.05] hover:text-slate-900 dark:hover:text-white' }}">
                <span class="flex items-center gap-2.5">
                    <span class="text-base">⚡</span>
                    <span>Delivery Boys &amp; Fleet</span>
                </span>
                <span class="text-[9px] uppercase font-black px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-800 border border-emerald-300">
                    Live
                </span>
            </a>
            @endif

            @if(in_array($vType, ['services']))
            <a href="{{ route('vendor.bookings.index') }}" 
               class="group flex items-center justify-between px-3 py-2.5 rounded-xl text-xs font-semibold transition-all duration-200 {{ request()->routeIs('vendor.bookings.*') ? 'bg-stone-900 text-white dark:bg-white dark:text-stone-900 shadow-sm font-bold' : 'text-slate-600 dark:text-zinc-300 hover:bg-slate-100 dark:hover:bg-white/[0.05] hover:text-slate-900 dark:hover:text-white' }}">
                <span class="flex items-center gap-2.5">
                    <span class="text-base">📅</span>
                    <span>Client Bookings</span>
                </span>
            </a>
            @endif

            @if($vType === 'used')
            <a href="{{ route('vendor.offers.index') }}" 
               class="group flex items-center justify-between px-3 py-2.5 rounded-xl text-xs font-semibold transition-all duration-200 {{ request()->routeIs('vendor.offers.*') ? 'bg-stone-900 text-white dark:bg-white dark:text-stone-900 shadow-sm font-bold' : 'text-slate-600 dark:text-zinc-300 hover:bg-slate-100 dark:hover:bg-white/[0.05] hover:text-slate-900 dark:hover:text-white' }}">
                <span class="flex items-center gap-2.5">
                    <span class="text-base">🤝</span>
                    <span>Buyer Offers & Deals</span>
                </span>
            </a>
            @endif

            @if(in_array($vType, ['b2b', 'machinery', 'real_estate', 'digital_services', 'marble']))
            <a href="{{ route('vendor.enquiries.index') }}" 
               class="group flex items-center justify-between px-3 py-2.5 rounded-xl text-xs font-semibold transition-all duration-200 {{ request()->routeIs('vendor.enquiries.*') ? 'bg-stone-900 text-white dark:bg-white dark:text-stone-900 shadow-sm font-bold' : 'text-slate-600 dark:text-zinc-300 hover:bg-slate-100 dark:hover:bg-white/[0.05] hover:text-slate-900 dark:hover:text-white' }}">
                <span class="flex items-center gap-2.5">
                    <span class="text-base">📋</span>
                    <span>{{ $vType === 'real_estate' ? 'Property Enquiries' : ($vType === 'digital_services' ? 'Client Project Briefs' : ($vType === 'marble' ? 'Marble RFQs & Leads' : 'RFQ Enquiries')) }}</span>
                </span>
            </a>
            @endif
        </div>
    </div>

    <!-- Engagement & Growth -->
    <div>
        <div class="px-3 mb-2 text-[10px] font-black uppercase tracking-wider text-slate-400 dark:text-zinc-500">
            Growth & Channels
        </div>
        <div class="space-y-1">
            <a href="{{ route('vendor.shorts.index') }}" 
               class="group flex items-center justify-between px-3 py-2.5 rounded-xl text-xs font-semibold transition-all duration-200 {{ request()->routeIs('vendor.shorts.*') ? 'bg-stone-900 text-white dark:bg-white dark:text-stone-900 shadow-sm font-bold' : 'text-slate-600 dark:text-zinc-300 hover:bg-slate-100 dark:hover:bg-white/[0.05] hover:text-slate-900 dark:hover:text-white' }}">
                <span class="flex items-center gap-2.5">
                    <span class="text-base">🎬</span>
                    <span>Shorts Video Studio</span>
                </span>
                <span class="text-[9px] uppercase font-black px-1.5 py-0.5 rounded bg-stone-100 text-stone-700 dark:bg-white/10 dark:text-stone-200 border border-stone-200 dark:border-white/15">
                    Live
                </span>
            </a>

            <a href="{{ route('messages.index') }}" 
               class="group flex items-center justify-between px-3 py-2.5 rounded-xl text-xs font-semibold transition-all duration-200 {{ request()->routeIs('messages.*') ? 'bg-stone-900 text-white dark:bg-white dark:text-stone-900 shadow-sm font-bold' : 'text-slate-600 dark:text-zinc-300 hover:bg-slate-100 dark:hover:bg-white/[0.05] hover:text-slate-900 dark:hover:text-white' }}">
                <span class="flex items-center gap-2.5 relative">
                    <span class="text-base relative inline-flex items-center">
                        💬
                        <span class="bottom-nav-messages-dot absolute -top-1 -right-1.5 flex h-2.5 w-2.5 hidden">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-rose-500 opacity-80"></span>
                            <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-[#EF4444] ring-1.5 ring-white shadow-sm"></span>
                        </span>
                    </span>
                    <span>Direct Messages</span>
                </span>
                <span class="text-[9px] uppercase font-black px-1.5 py-0.5 rounded bg-stone-100 text-stone-700 dark:bg-white/10 dark:text-stone-200 border border-stone-200 dark:border-white/15">
                    Realtime
                </span>
            </a>

            <a href="{{ route('vendor.coupons.index') }}" 
               class="group flex items-center justify-between px-3 py-2.5 rounded-xl text-xs font-semibold transition-all duration-200 {{ request()->routeIs('vendor.coupons.*') ? 'bg-stone-900 text-white dark:bg-white dark:text-stone-900 shadow-sm font-bold' : 'text-slate-600 dark:text-zinc-300 hover:bg-slate-100 dark:hover:bg-white/[0.05] hover:text-slate-900 dark:hover:text-white' }}">
                <span class="flex items-center gap-2.5">
                    <span class="text-base">🏷️</span>
                    <span>{{ $vType === 'food' ? 'Dish Promotions' : 'Coupons & Deals' }}</span>
                </span>
            </a>
        </div>
    </div>

    <!-- Store Profile & Setup -->
    <div>
        <div class="px-3 mb-2 text-[10px] font-black uppercase tracking-wider text-slate-400 dark:text-zinc-500">
            Storefront Settings
        </div>
        <div class="space-y-1">
            <a href="{{ route('vendor.design.index') }}" 
               class="group flex items-center justify-between px-3 py-2.5 rounded-xl text-xs font-semibold transition-all duration-200 {{ request()->routeIs('vendor.design.*') ? 'bg-stone-900 text-white dark:bg-white dark:text-stone-900 shadow-sm font-bold' : 'text-slate-600 dark:text-zinc-300 hover:bg-slate-100 dark:hover:bg-white/[0.05] hover:text-slate-900 dark:hover:text-white' }}">
                <span class="flex items-center gap-2.5">
                    <span class="text-base">🎨</span>
                    <span>Store Design &amp; Presets</span>
                </span>
                <span class="text-[9px] uppercase font-black px-1.5 py-0.5 rounded bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/20">
                    2.0
                </span>
            </a>

            <a href="{{ route('vendor.profile') }}" 
               class="group flex items-center justify-between px-3 py-2.5 rounded-xl text-xs font-semibold transition-all duration-200 {{ request()->routeIs('vendor.profile') ? 'bg-stone-900 text-white dark:bg-white dark:text-stone-900 shadow-sm font-bold' : 'text-slate-600 dark:text-zinc-300 hover:bg-slate-100 dark:hover:bg-white/[0.05] hover:text-slate-900 dark:hover:text-white' }}">
                <span class="flex items-center gap-2.5">
                    <span class="text-base">🏬</span>
                    <span>Store Identity & Logo</span>
                </span>
            </a>

            <a href="{{ route('vendor.subscription.index') }}" 
               class="group flex items-center justify-between px-3 py-2.5 rounded-xl text-xs font-semibold transition-all duration-200 {{ request()->routeIs('vendor.subscription.*') ? 'bg-stone-900 text-white dark:bg-white dark:text-stone-900 shadow-sm font-bold' : 'text-slate-600 dark:text-zinc-300 hover:bg-slate-100 dark:hover:bg-white/[0.05] hover:text-slate-900 dark:hover:text-white' }}">
                <span class="flex items-center gap-2.5">
                    <span class="text-base">🛡️</span>
                    <span>My Plan &amp; Subscription</span>
                </span>
            </a>

            <a href="{{ route('vendor.settings') }}" 
               class="group flex items-center justify-between px-3 py-2.5 rounded-xl text-xs font-semibold transition-all duration-200 {{ request()->routeIs('vendor.settings') ? 'bg-stone-900 text-white dark:bg-white dark:text-stone-900 shadow-sm font-bold' : 'text-slate-600 dark:text-zinc-300 hover:bg-slate-100 dark:hover:bg-white/[0.05] hover:text-slate-900 dark:hover:text-white' }}">
                <span class="flex items-center gap-2.5">
                    <span class="text-base">⚙️</span>
                    <span>{{ $vType === 'food' ? 'Kitchen & FSSAI' : ($vType === 'services' ? 'Service Areas & Slates' : 'Logistics & Payouts') }}</span>
                </span>
            </a>
        </div>
    </div>
</div>
@endif
