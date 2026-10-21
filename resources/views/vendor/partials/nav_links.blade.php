@php
    $curVendor = auth()->user()?->vendor;
    $vType = $curVendor?->vendor_type ?? 'clothing';
    $sec = $curVendor ? $curVendor->getSectorConfig() : null;
@endphp

<div class="space-y-6">
    <!-- Core Management -->
    <div>
        <div class="px-3 mb-2 text-[10px] font-black uppercase tracking-wider text-slate-400 dark:text-zinc-500">
            Core Operations
        </div>
        <div class="space-y-1">
            <a href="{{ route('vendor.dashboard') }}" 
               class="group flex items-center justify-between px-3 py-2.5 rounded-xl text-xs font-semibold transition-all duration-200 {{ request()->routeIs('vendor.dashboard') ? 'bg-gradient-to-r from-purple-600 to-indigo-600 text-white shadow-md shadow-purple-600/20 font-bold' : 'text-slate-600 dark:text-zinc-300 hover:bg-slate-100 dark:hover:bg-white/[0.05] hover:text-slate-900 dark:hover:text-white' }}">
                <span class="flex items-center gap-2.5">
                    <span class="text-base">📊</span>
                    <span>Dashboard & Analytics</span>
                </span>
                @if(request()->routeIs('vendor.dashboard'))
                    <span class="w-1.5 h-1.5 rounded-full bg-white animate-pulse"></span>
                @endif
            </a>

            <a href="{{ route('vendor.products.index') }}" 
               class="group flex items-center justify-between px-3 py-2.5 rounded-xl text-xs font-semibold transition-all duration-200 {{ request()->routeIs('vendor.products.*') ? 'bg-gradient-to-r from-purple-600 to-indigo-600 text-white shadow-md shadow-purple-600/20 font-bold' : 'text-slate-600 dark:text-zinc-300 hover:bg-slate-100 dark:hover:bg-white/[0.05] hover:text-slate-900 dark:hover:text-white' }}">
                <span class="flex items-center gap-2.5">
                    <span class="text-base">{{ $sec['icon'] ?? '🛍️' }}</span>
                    <span>{{ $sec['catalog_name'] ?? 'Product Catalog' }}</span>
                </span>
                <span class="text-[10px] font-bold px-1.5 py-0.5 rounded {{ request()->routeIs('vendor.products.*') ? 'bg-white/20 text-white' : 'bg-slate-100 dark:bg-white/10 text-slate-500 dark:text-zinc-400' }}">
                    Items
                </span>
            </a>

            @if(!in_array($vType, ['services', 'digital_services', 'real_estate']))
            <a href="{{ route('vendor.orders.index') }}" 
               class="group flex items-center justify-between px-3 py-2.5 rounded-xl text-xs font-semibold transition-all duration-200 {{ request()->routeIs('vendor.orders.*') ? 'bg-gradient-to-r from-purple-600 to-indigo-600 text-white shadow-md shadow-purple-600/20 font-bold' : 'text-slate-600 dark:text-zinc-300 hover:bg-slate-100 dark:hover:bg-white/[0.05] hover:text-slate-900 dark:hover:text-white' }}">
                <span class="flex items-center gap-2.5">
                    <span class="text-base">{{ $vType === 'food' ? '🛵' : ($vType === 'electronics' ? '📦' : ($vType === 'automotive' ? '🏎️' : '📦')) }}</span>
                    <span>{{ $vType === 'food' ? 'Kitchen Orders' : ($vType === 'electronics' ? 'Customer Orders & Shipments' : 'Customer Orders') }}</span>
                </span>
            </a>
            @endif

            @if(in_array($vType, ['services']))
            <a href="{{ route('vendor.bookings.index') }}" 
               class="group flex items-center justify-between px-3 py-2.5 rounded-xl text-xs font-semibold transition-all duration-200 {{ request()->routeIs('vendor.bookings.*') ? 'bg-gradient-to-r from-blue-600 to-indigo-600 text-white shadow-md shadow-blue-600/20 font-bold' : 'text-slate-600 dark:text-zinc-300 hover:bg-slate-100 dark:hover:bg-white/[0.05] hover:text-slate-900 dark:hover:text-white' }}">
                <span class="flex items-center gap-2.5">
                    <span class="text-base">📅</span>
                    <span>Client Bookings</span>
                </span>
            </a>
            @endif

            @if($vType === 'used')
            <a href="{{ route('vendor.offers.index') }}" 
               class="group flex items-center justify-between px-3 py-2.5 rounded-xl text-xs font-semibold transition-all duration-200 {{ request()->routeIs('vendor.offers.*') ? 'bg-gradient-to-r from-emerald-600 to-teal-600 text-white shadow-md shadow-emerald-600/20 font-bold' : 'text-slate-600 dark:text-zinc-300 hover:bg-slate-100 dark:hover:bg-white/[0.05] hover:text-slate-900 dark:hover:text-white' }}">
                <span class="flex items-center gap-2.5">
                    <span class="text-base">🤝</span>
                    <span>Buyer Offers & Deals</span>
                </span>
            </a>
            @endif

            @if(in_array($vType, ['b2b', 'machinery', 'real_estate', 'digital_services']))
            <a href="{{ route('vendor.enquiries.index') }}" 
               class="group flex items-center justify-between px-3 py-2.5 rounded-xl text-xs font-semibold transition-all duration-200 {{ request()->routeIs('vendor.enquiries.*') ? 'bg-gradient-to-r from-purple-600 to-pink-600 text-white shadow-md shadow-purple-600/20 font-bold' : 'text-slate-600 dark:text-zinc-300 hover:bg-slate-100 dark:hover:bg-white/[0.05] hover:text-slate-900 dark:hover:text-white' }}">
                <span class="flex items-center gap-2.5">
                    <span class="text-base">📋</span>
                    <span>{{ $vType === 'real_estate' ? 'Property Enquiries' : ($vType === 'digital_services' ? 'Client Project Briefs' : 'RFQ Enquiries') }}</span>
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
               class="group flex items-center justify-between px-3 py-2.5 rounded-xl text-xs font-semibold transition-all duration-200 {{ request()->routeIs('vendor.shorts.*') ? 'bg-gradient-to-r from-rose-500 to-red-600 text-white shadow-md shadow-rose-500/20 font-bold' : 'text-slate-600 dark:text-zinc-300 hover:bg-slate-100 dark:hover:bg-white/[0.05] hover:text-slate-900 dark:hover:text-white' }}">
                <span class="flex items-center gap-2.5">
                    <span class="text-base">🎬</span>
                    <span>Shorts Video Studio</span>
                </span>
                <span class="text-[9px] uppercase font-black px-1.5 py-0.5 rounded bg-rose-500/20 text-rose-500 dark:text-rose-300 border border-rose-500/30">
                    Live
                </span>
            </a>

            <a href="{{ route('messages.index') }}" 
               class="group flex items-center justify-between px-3 py-2.5 rounded-xl text-xs font-semibold transition-all duration-200 {{ request()->routeIs('messages.*') ? 'bg-gradient-to-r from-purple-600 to-indigo-600 text-white shadow-md shadow-purple-600/20 font-bold' : 'text-slate-600 dark:text-zinc-300 hover:bg-slate-100 dark:hover:bg-white/[0.05] hover:text-slate-900 dark:hover:text-white' }}">
                <span class="flex items-center gap-2.5">
                    <span class="text-base">💬</span>
                    <span>Direct Messages</span>
                </span>
                <span class="text-[9px] uppercase font-black px-1.5 py-0.5 rounded bg-blue-500/10 text-blue-600 dark:text-blue-400 border border-blue-500/20">
                    Realtime
                </span>
            </a>

            <a href="{{ route('vendor.coupons.index') }}" 
               class="group flex items-center justify-between px-3 py-2.5 rounded-xl text-xs font-semibold transition-all duration-200 {{ request()->routeIs('vendor.coupons.*') ? 'bg-gradient-to-r from-purple-600 to-indigo-600 text-white shadow-md shadow-purple-600/20 font-bold' : 'text-slate-600 dark:text-zinc-300 hover:bg-slate-100 dark:hover:bg-white/[0.05] hover:text-slate-900 dark:hover:text-white' }}">
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
            <a href="{{ route('vendor.profile') }}" 
               class="group flex items-center justify-between px-3 py-2.5 rounded-xl text-xs font-semibold transition-all duration-200 {{ request()->routeIs('vendor.profile') ? 'bg-gradient-to-r from-purple-600 to-indigo-600 text-white shadow-md shadow-purple-600/20 font-bold' : 'text-slate-600 dark:text-zinc-300 hover:bg-slate-100 dark:hover:bg-white/[0.05] hover:text-slate-900 dark:hover:text-white' }}">
                <span class="flex items-center gap-2.5">
                    <span class="text-base">🏬</span>
                    <span>Store Identity & Logo</span>
                </span>
            </a>

            <a href="{{ route('vendor.settings') }}" 
               class="group flex items-center justify-between px-3 py-2.5 rounded-xl text-xs font-semibold transition-all duration-200 {{ request()->routeIs('vendor.settings') ? 'bg-gradient-to-r from-purple-600 to-indigo-600 text-white shadow-md shadow-purple-600/20 font-bold' : 'text-slate-600 dark:text-zinc-300 hover:bg-slate-100 dark:hover:bg-white/[0.05] hover:text-slate-900 dark:hover:text-white' }}">
                <span class="flex items-center gap-2.5">
                    <span class="text-base">⚙️</span>
                    <span>{{ $vType === 'food' ? 'Kitchen & FSSAI' : ($vType === 'services' ? 'Service Areas & Slates' : 'Logistics & Payouts') }}</span>
                </span>
            </a>
        </div>
    </div>
</div>
