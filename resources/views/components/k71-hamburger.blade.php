@php
    $verticalsList = [
        [
            'key' => 'main',
            'name' => 'Marketplace',
            'desc' => 'Curated shops & fashion collections',
            'url' => k71_vertical_url('main'),
            'icon_path' => 'M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 01-1.12-1.243l1.264-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119 1.007z',
        ],
        [
            'key' => 'food',
            'name' => 'Food & Dining',
            'desc' => 'Local kitchens & direct food orders',
            'url' => k71_vertical_url('food'),
            'icon_path' => 'M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25',
        ],
        [
            'key' => 'services',
            'name' => 'Services & Bookings',
            'desc' => 'Verified local service providers',
            'url' => k71_vertical_url('services'),
            'icon_path' => 'M11.42 15.17L17.25 21A2.652 2.652 0 0021 17.25l-5.877-5.877M11.42 15.17l2.496-3.03c.317-.384.74-.626 1.208-.766M11.42 15.17l-4.655 5.653a2.548 2.548 0 11-3.586-3.586l6.837-5.63m5.108-.233c.55-.164 1.163-.188 1.743-.07a4.5 4.5 0 004.486-6.32l-3.27 3.27-2.959-.592-.592-2.96 3.27-3.27a4.5 4.5 0 00-6.32 4.486c.118.58.094 1.193-.07 1.743',
        ],
        [
            'key' => 'used',
            'name' => 'Pre-Loved',
            'desc' => 'Verified items & direct offers',
            'url' => k71_vertical_url('used'),
            'icon_path' => 'M9.568 3H5.25A2.25 2.25 0 003 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.386a9.92 9.92 0 002.503-2.072 9.92 9.92 0 002.072-2.503c.486-.827.313-1.908-.386-2.607L10.455 3.659A2.25 2.25 0 008.864 3z',
        ],
        [
            'key' => 'enquiry',
            'name' => 'Quotes & B2B',
            'desc' => 'Wholesale pricing & custom requests',
            'url' => k71_vertical_url('enquiry'),
            'icon_path' => 'M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z',
        ],
        [
            'key' => 'shorts',
            'name' => 'Video Feed',
            'desc' => 'Watch shorts & discover products',
            'url' => k71_vertical_url('shorts'),
            'badge' => 'LIVE',
            'icon_path' => 'M5.25 5.653c0-.856.917-1.398 1.667-.986l11.54 6.348a1.125 1.125 0 010 1.971l-11.54 6.347a1.125 1.125 0 01-1.667-.985V5.653z',
        ],
    ];

    $currentKey = $k71_vertical_key ?? 'main';
    $customDrawerItems = \App\Models\NavigationItem::getMobileDrawerItems();
@endphp

<div x-data="{ menuOpen: false }" 
     x-cloak
     @open-hamburger.window="menuOpen = true"
     @keydown.escape.window="menuOpen = false"
     class="relative z-[90]">

    <!-- Backdrop -->
    <div x-show="menuOpen"
         x-transition:enter="transition-opacity ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition-opacity ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         @click="menuOpen = false"
         class="fixed inset-0 bg-black/60 backdrop-blur-sm"></div>

    <!-- Drawer Panel -->
    <div x-show="menuOpen"
         x-transition:enter="transition ease-out duration-300 transform"
         x-transition:enter-start="-translate-x-full"
         x-transition:enter-end="translate-x-0"
         x-transition:leave="transition ease-in duration-200 transform"
         x-transition:leave-start="translate-x-0"
         x-transition:leave-end="-translate-x-full"
         class="fixed inset-y-0 left-0 w-[85%] max-w-sm bg-white/95 dark:bg-zinc-950/95 backdrop-blur-2xl shadow-2xl flex flex-col overflow-hidden border-r border-zinc-200/60 dark:border-zinc-800/80 text-zinc-900 dark:text-zinc-100">

        <!-- Drawer Header -->
        <div class="px-5 py-4 border-b border-zinc-100 dark:border-zinc-800/80 flex items-center justify-between bg-white/80 dark:bg-zinc-950/80 backdrop-blur-md">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-xl bg-indigo-600 p-1 flex items-center justify-center shadow-md shadow-indigo-600/20 shrink-0 border border-indigo-700">
                    <img src="{{ asset('images/k71-logo.png') }}" alt="K71" class="w-full h-full object-contain">
                </div>
                <div class="flex items-baseline gap-1.5 leading-none">
                    <span class="font-sans text-xl font-black tracking-tight text-zinc-950 dark:text-white">
                        K71
                    </span>
                    <span class="text-[9px] uppercase tracking-wider font-mono font-bold text-indigo-600 dark:text-indigo-400">
                        MARKETPLACE
                    </span>
                </div>
            </div>
            <button @click="menuOpen = false" 
                    class="w-8 h-8 rounded-full hover:bg-zinc-100 dark:hover:bg-zinc-800 text-zinc-500 hover:text-zinc-900 dark:hover:text-white flex items-center justify-center transition-colors">
                <svg width="20" height="20" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>

        <!-- Scrollable Drawer Content -->
        <div class="flex-1 overflow-y-auto px-5 py-5 space-y-6">

            <!-- User Status Card -->
            <div class="p-4 rounded-2xl bg-zinc-50/80 dark:bg-zinc-900/80 backdrop-blur-md border border-zinc-200/60 dark:border-zinc-800/80">
                @auth
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-violet-600 text-white font-medium flex items-center justify-center text-sm shadow-xs">
                            {{ strtoupper(substr(auth()->user()->first_name ?? 'U', 0, 1)) }}
                        </div>
                        <div class="flex-1 min-w-0">
                            <h4 class="text-sm font-semibold text-zinc-900 dark:text-white truncate">
                                {{ auth()->user()->first_name }} {{ auth()->user()->last_name }}
                            </h4>
                            <p class="text-xs text-zinc-500 dark:text-zinc-400 truncate">{{ auth()->user()->email }}</p>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-2 mt-3 pt-3 border-t border-zinc-200/50 dark:border-zinc-800/80">
                        <a href="{{ route('account') }}" class="text-center py-2 rounded-xl bg-white dark:bg-zinc-800 text-xs font-semibold text-zinc-800 dark:text-zinc-200 border border-zinc-200/80 dark:border-zinc-700 shadow-xs hover:bg-zinc-50 dark:hover:bg-zinc-700 transition-colors">
                            Account
                        </a>
                        <a href="{{ route('orders.index') }}" class="text-center py-2 rounded-xl bg-white dark:bg-zinc-800 text-xs font-semibold text-zinc-800 dark:text-zinc-200 border border-zinc-200/80 dark:border-zinc-700 shadow-xs hover:bg-zinc-50 dark:hover:bg-zinc-700 transition-colors">
                            Orders
                        </a>
                    </div>
                @else
                    <div class="text-center py-2">
                        <p class="text-sm font-semibold text-zinc-900 dark:text-white">Welcome to K71</p>
                        <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-0.5">Sign in for fast checkout &amp; order tracking</p>
                        <div class="grid grid-cols-2 gap-2 mt-3">
                            <button type="button" 
                                    @click="closeDrawer(); window.openAuthModal ? window.openAuthModal(null, 'login') : $dispatch('open-auth-modal', { tab: 'login' })" 
                                    class="py-2.5 rounded-xl bg-violet-600 hover:bg-violet-700 text-white text-xs font-semibold shadow-xs text-center transition-colors cursor-pointer">
                                Sign In
                            </button>
                            <button type="button" 
                                    @click="closeDrawer(); window.openAuthModal ? window.openAuthModal(null, 'register') : $dispatch('open-auth-modal', { tab: 'register' })" 
                                    class="py-2.5 rounded-xl bg-white dark:bg-zinc-800 text-zinc-900 dark:text-white border border-zinc-200 dark:border-zinc-700 text-xs font-semibold hover:bg-zinc-50 dark:hover:bg-zinc-700 text-center transition-colors cursor-pointer">
                                Register
                            </button>
                        </div>
                    </div>
                @endauth
            </div>

            <!-- Verticals Menu -->
            <div>
                <h5 class="text-xs uppercase font-semibold tracking-wider text-zinc-400 dark:text-zinc-500 mb-2">Ecosystem Verticals</h5>
                <div class="space-y-1.5">
                    @foreach($verticalsList as $vert)
                        @php $isActive = $currentKey === $vert['key']; @endphp
                        <a href="{{ $vert['url'] }}" 
                           class="flex items-center justify-between p-3 rounded-2xl transition-all duration-200 {{ $isActive ? 'bg-violet-600 text-white shadow-md' : 'hover:bg-zinc-100 dark:hover:bg-zinc-900 text-zinc-800 dark:text-zinc-200' }}">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-lg flex items-center justify-center {{ $isActive ? 'bg-white/20 text-white' : 'bg-white dark:bg-zinc-800 text-zinc-700 dark:text-zinc-300 border border-zinc-200/60 dark:border-zinc-700/60 shadow-xs' }}">
                                    <svg width="18" height="18" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="{{ $vert['icon_path'] }}"></path>
                                    </svg>
                                </div>
                                <div>
                                    <div class="flex items-center gap-1.5">
                                        <span class="text-xs font-semibold">{{ $vert['name'] }}</span>
                                        @if(isset($vert['badge']))
                                            <span class="text-[8px] font-bold px-1.5 py-0.2 rounded-full bg-red-500 text-white">
                                                {{ $vert['badge'] }}
                                            </span>
                                        @endif
                                    </div>
                                    <p class="text-[10px] {{ $isActive ? 'text-violet-100' : 'text-zinc-500 dark:text-zinc-400' }}">{{ $vert['desc'] }}</p>
                                </div>
                            </div>
                            <svg class="w-4 h-4 {{ $isActive ? 'text-white' : 'text-zinc-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8.25 4.5l7.5 7.5-7.5 7.5"></path>
                            </svg>
                        </a>
                    @endforeach
                </div>
            </div>

            @if($customDrawerItems->isNotEmpty())
                <!-- Dynamic Admin Configured Panel Shortcuts -->
                <div>
                    <h5 class="text-xs uppercase font-bold tracking-wider text-indigo-600 mb-2">Featured Highlights</h5>
                    <div class="space-y-2">
                        @foreach($customDrawerItems as $cItem)
                            <a href="{{ $cItem->url }}" target="{{ $cItem->target ?? '_self' }}"
                               class="flex items-center justify-between p-3 rounded-2xl bg-slate-50 border border-slate-200 hover:border-indigo-500/50 hover:bg-slate-100/80 transition group shadow-2xs">
                                <div class="flex items-center gap-3">
                                    <!-- 4x Large HD Icon Box -->
                                    <div class="w-14 h-14 rounded-2xl bg-white border border-slate-200 flex items-center justify-center p-1.5 shadow-xs shrink-0 overflow-hidden group-hover:scale-105 transition-transform">
                                        @if($cItem->icon_type === 'image' && $cItem->image_url)
                                            <img src="{{ $cItem->image_url }}" alt="{{ $cItem->title }}" class="w-full h-full object-contain drop-shadow">
                                        @elseif($cItem->icon_type === 'emoji' && $cItem->icon)
                                            <span class="text-2xl">{{ $cItem->icon }}</span>
                                        @elseif($cItem->icon)
                                            <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                {!! $cItem->icon !!}
                                            </svg>
                                        @else
                                            <span class="text-slate-500 font-bold">★</span>
                                        @endif
                                    </div>
                                    <div>
                                        <div class="flex items-center gap-1.5">
                                            <span class="text-sm font-bold text-slate-900 group-hover:text-indigo-600 transition-colors">{{ $cItem->title }}</span>
                                            @if($cItem->badge)
                                                <span class="text-[8px] font-black px-1.5 py-0.2 rounded-full bg-{{ $cItem->badge_color ?? 'indigo' }}-600 text-white shadow-2xs">
                                                    {{ $cItem->badge }}
                                                </span>
                                            @endif
                                        </div>
                                        @if($cItem->subtitle)
                                            <p class="text-[11px] text-slate-500 mt-0.5">{{ $cItem->subtitle }}</p>
                                        @endif
                                    </div>
                                </div>
                                <svg class="w-4 h-4 text-slate-400 group-hover:text-indigo-600 group-hover:translate-x-0.5 transition-all" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.25 4.5l7.5 7.5-7.5 7.5"></path></svg>
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Navigation Links -->
            <div>
                <h5 class="text-xs uppercase font-semibold tracking-wider text-zinc-400 dark:text-zinc-500 mb-2">Explore</h5>
                <div class="space-y-1">
                    <a href="{{ route('catalog.index') }}" class="flex items-center justify-between py-2 text-sm text-zinc-700 dark:text-zinc-300 hover:text-black dark:hover:text-white font-medium border-b border-zinc-100 dark:border-zinc-800">
                        <span>All Categories &amp; Products</span>
                        <svg class="w-3.5 h-3.5 text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8.25 4.5l7.5 7.5-7.5 7.5"></path></svg>
                    </a>
                    <a href="{{ route('stores.index') }}" class="flex items-center justify-between py-2 text-sm text-zinc-700 dark:text-zinc-300 hover:text-black dark:hover:text-white font-medium border-b border-zinc-100 dark:border-zinc-800">
                        <span>🏪 Verified Stores Directory</span>
                        <svg class="w-3.5 h-3.5 text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8.25 4.5l7.5 7.5-7.5 7.5"></path></svg>
                    </a>
                    <a href="{{ route('wishlist.index') }}" class="flex items-center justify-between py-2 text-sm text-zinc-700 dark:text-zinc-300 hover:text-black dark:hover:text-white font-medium">
                        <span>Saved Wishlist</span>
                        <svg width="16" height="16" class="w-4 h-4 text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z"></path>
                        </svg>
                    </a>
                </div>
            </div>

            <!-- Seller Hub -->
            <div class="p-4 rounded-2xl bg-zinc-50 dark:bg-zinc-900 border border-zinc-200/60 dark:border-zinc-800/80">
                <h5 class="text-xs uppercase font-semibold tracking-wider text-zinc-900 dark:text-white">Seller Hub</h5>
                <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-1">Open your verified store across all 13 marketplace sectors.</p>
                <div class="mt-3">
                    @auth
                        @if(auth()->user()->hasRole('vendor'))
                            <a href="{{ route('vendor.dashboard') }}" class="block w-full text-center py-2.5 rounded-xl bg-violet-600 hover:bg-violet-700 text-white text-xs font-semibold transition-colors shadow-xs">
                                Open Vendor Dashboard
                            </a>
                        @else
                            <a href="{{ route('vendor.register') }}" class="block w-full text-center py-2.5 rounded-xl bg-zinc-950 dark:bg-white text-white dark:text-zinc-950 text-xs font-semibold hover:bg-black transition-colors shadow-xs">
                                Become a Seller
                            </a>
                        @endif
                    @else
                        <a href="{{ route('vendor.register') }}" class="block w-full text-center py-2.5 rounded-xl bg-zinc-950 dark:bg-white text-white dark:text-zinc-950 text-xs font-semibold hover:bg-black transition-colors shadow-xs">
                            Start Selling
                        </a>
                    @endauth
                </div>
            </div>

            @auth
                @if(auth()->user()->hasRole('admin'))
                    <div class="pt-1">
                        <a href="{{ route('admin.dashboard') }}" class="flex items-center justify-between p-3 rounded-xl bg-zinc-950 dark:bg-zinc-900 text-white text-xs font-semibold shadow-xs border border-zinc-800">
                            <span>Master Admin Console</span>
                            <span class="text-[10px] bg-white/20 text-white px-2 py-0.5 rounded-full font-medium">Admin</span>
                        </a>
                    </div>
                @endif
            @endauth

        </div>

        <!-- Drawer Footer -->
        <div class="p-4 border-t border-zinc-100 dark:border-zinc-800/80 bg-white/80 dark:bg-zinc-950/80 flex items-center justify-between text-xs text-zinc-500">
            <span>&copy; {{ date('Y') }} K71 Creative Agency</span>
            @auth
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="text-red-600 dark:text-red-400 font-medium hover:underline">
                        Sign Out
                    </button>
                </form>
            @endauth
        </div>

    </div>
</div>
