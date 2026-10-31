@props(['title' => 'Admin Console'])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-[#FAFAFA]">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title }} • K71 Master Console</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=Outfit:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">

    <!-- Favicon & Brand Icons -->
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32x32.png') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('favicon-16x16.png') }}">
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('apple-touch-icon.png') }}">
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <x-k71-design-system />
    <style>
        [x-cloak] { display: none !important; }
        body { font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; }
        .font-display { font-family: 'Outfit', sans-serif; }
    </style>
    <script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/collapse@3.x.x/dist/cdn.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="h-full text-zinc-900 bg-[#FAFAFA] antialiased overflow-hidden flex relative" x-data="{ mobileSidebarOpen: false }">

    <!-- Mobile Drawer Backdrop -->
    <div 
        x-show="mobileSidebarOpen" 
        x-cloak 
        @click="mobileSidebarOpen = false" 
        class="fixed inset-0 z-40 bg-zinc-950/40 backdrop-blur-xs lg:hidden"
        x-transition:enter="transition-opacity ease-linear duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition-opacity ease-linear duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
    ></div>

    <!-- SIDEBAR (Desktop Fixed & Mobile Drawer) - Minimal White & Black -->
    <aside 
        class="fixed inset-y-0 left-0 z-50 w-64 sm:w-72 bg-white text-zinc-900 flex flex-col justify-between transition-transform duration-200 ease-in-out lg:static lg:translate-x-0 border-r border-zinc-200/80 shadow-xs"
        :class="mobileSidebarOpen ? 'translate-x-0 shadow-2xl' : '-translate-x-full lg:translate-x-0'"
    >
        <!-- Brand Header -->
        <div>
            <div class="h-16 flex items-center justify-between px-6 border-b border-zinc-100 bg-white">
                <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-2.5 group">
                    <div class="w-8 h-8 rounded-lg bg-zinc-950 text-white flex items-center justify-center font-black text-sm tracking-tight shadow-xs">
                        K71
                    </div>
                    <div>
                        <span class="block text-sm font-bold text-zinc-950 tracking-tight leading-none">
                            Admin <span class="text-zinc-400 font-mono text-[10px] uppercase font-normal">Console</span>
                        </span>
                        <div class="flex items-center gap-1.5 mt-0.5">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                            <span class="text-[10px] font-mono uppercase tracking-wider text-zinc-500 font-medium">Production</span>
                        </div>
                    </div>
                </a>

                <button @click="mobileSidebarOpen = false" class="lg:hidden text-zinc-400 hover:text-zinc-900 p-1.5 rounded-lg hover:bg-zinc-100 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <!-- Navigation Sections -->
            <nav class="px-3 py-4 space-y-5 overflow-y-auto max-h-[calc(100vh-12rem)] no-scrollbar">
                
                <!-- Section 1: Overview -->
                <div>
                    <p class="px-3 text-[10px] font-mono uppercase tracking-widest text-zinc-400 font-bold mb-1.5">Overview</p>
                    <div class="space-y-0.5">
                        <a href="{{ route('admin.dashboard') }}" 
                           class="flex items-center gap-3 px-3 py-2 rounded-lg text-xs font-semibold transition-colors {{ request()->routeIs('admin.dashboard') ? 'bg-zinc-950 text-white shadow-xs' : 'text-zinc-600 hover:bg-zinc-100 hover:text-zinc-950' }}">
                            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                            <span>Dashboard</span>
                        </a>
                    </div>
                </div>

                <!-- Section 2: Storefront & SEO -->
                <div>
                    <p class="px-3 text-[10px] font-mono uppercase tracking-widest text-zinc-400 font-bold mb-1.5">Storefront</p>
                    <div class="space-y-0.5">
                        <a href="{{ route('admin.homepage') }}" 
                           class="flex items-center justify-between px-3 py-2 rounded-lg text-xs font-semibold transition-colors {{ request()->routeIs('admin.homepage*') ? 'bg-zinc-950 text-white shadow-xs' : 'text-zinc-600 hover:bg-zinc-100 hover:text-zinc-950' }}">
                            <div class="flex items-center gap-3">
                                <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z"/></svg>
                                <span>Homepage &amp; SEO</span>
                            </div>
                            <span class="text-[9px] uppercase font-mono px-1.5 py-0.5 rounded {{ request()->routeIs('admin.homepage*') ? 'bg-zinc-800 text-zinc-300' : 'bg-zinc-100 text-zinc-500' }}">Live</span>
                        </a>

                        <a href="{{ route('admin.navigation.index') }}" 
                           class="flex items-center justify-between px-3 py-2 rounded-lg text-xs font-semibold transition-colors {{ request()->routeIs('admin.navigation*') ? 'bg-zinc-950 text-white shadow-xs' : 'text-zinc-600 hover:bg-zinc-100 hover:text-zinc-950' }}">
                            <div class="flex items-center gap-3">
                                <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                                <span>Navigation &amp; Mobile</span>
                            </div>
                        </a>

                        <a href="{{ route('admin.floating_launcher.index') }}" 
                           class="flex items-center justify-between px-3 py-2 rounded-lg text-xs font-semibold transition-colors {{ request()->routeIs('admin.floating_launcher*') ? 'bg-zinc-950 text-white shadow-xs' : 'text-zinc-600 hover:bg-zinc-100 hover:text-zinc-950' }}">
                            <div class="flex items-center gap-3">
                                <span class="text-sm font-black text-amber-500">✱</span>
                                <span>Floating 6-in-1 Launcher</span>
                            </div>
                            <span class="text-[9px] uppercase font-mono px-1.5 py-0.5 rounded {{ request()->routeIs('admin.floating_launcher*') ? 'bg-zinc-800 text-zinc-300' : 'bg-zinc-100 text-zinc-500' }}">Radial</span>
                        </a>
                    </div>
                </div>

                <!-- Section 3: Operations -->
                <div>
                    <p class="px-3 text-[10px] font-mono uppercase tracking-widest text-zinc-400 font-bold mb-1.5">Operations</p>
                    <div class="space-y-0.5">
                        <a href="{{ route('admin.users.index') }}" 
                           class="flex items-center justify-between px-3 py-2 rounded-lg text-xs font-semibold transition-colors {{ request()->routeIs('admin.users*') ? 'bg-zinc-950 text-white shadow-xs' : 'text-zinc-600 hover:bg-zinc-100 hover:text-zinc-950' }}">
                            <div class="flex items-center gap-3">
                                <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                                <span>Users &amp; Roles</span>
                            </div>
                        </a>

                        <a href="{{ route('admin.orders') }}" 
                           class="flex items-center justify-between px-3 py-2 rounded-lg text-xs font-semibold transition-colors {{ request()->routeIs('admin.orders*') ? 'bg-zinc-950 text-white shadow-xs' : 'text-zinc-600 hover:bg-zinc-100 hover:text-zinc-950' }}">
                            <div class="flex items-center gap-3">
                                <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                                <span>Orders</span>
                            </div>
                        </a>

                        <a href="{{ route('admin.settlements') }}" 
                           class="flex items-center justify-between px-3 py-2 rounded-lg text-xs font-semibold transition-colors {{ request()->routeIs('admin.settlements*') ? 'bg-zinc-950 text-white shadow-xs' : 'text-zinc-600 hover:bg-zinc-100 hover:text-zinc-950' }}">
                            <div class="flex items-center gap-3">
                                <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                <span>Settlements</span>
                            </div>
                            <span class="text-[9px] font-mono px-1.5 py-0.5 rounded {{ request()->routeIs('admin.settlements*') ? 'bg-zinc-800 text-zinc-300' : 'bg-zinc-100 text-zinc-500' }}">Escrow</span>
                        </a>

                        <a href="{{ route('admin.products') }}" 
                           class="flex items-center justify-between px-3 py-2 rounded-lg text-xs font-semibold transition-colors {{ request()->routeIs('admin.products*') ? 'bg-zinc-950 text-white shadow-xs' : 'text-zinc-600 hover:bg-zinc-100 hover:text-zinc-950' }}">
                            <div class="flex items-center gap-3">
                                <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
                                <span>Products</span>
                            </div>
                        </a>

                        <a href="{{ route('admin.services.index') }}" 
                           class="flex items-center justify-between px-3 py-2 rounded-lg text-xs font-semibold transition-colors {{ request()->routeIs('admin.services*') ? 'bg-zinc-950 text-white shadow-xs' : 'text-zinc-600 hover:bg-zinc-100 hover:text-zinc-950' }}">
                            <div class="flex items-center gap-3">
                                <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                                <span>Services &amp; Gigs</span>
                            </div>
                        </a>

                        <a href="{{ route('admin.vendors') }}" 
                           class="flex items-center justify-between px-3 py-2 rounded-lg text-xs font-semibold transition-colors {{ request()->routeIs('admin.vendors*') ? 'bg-zinc-950 text-white shadow-xs' : 'text-zinc-600 hover:bg-zinc-100 hover:text-zinc-950' }}">
                            <div class="flex items-center gap-3">
                                <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                                <span>Vendors</span>
                            </div>
                        </a>

                        <a href="{{ route('admin.shorts.index') }}" 
                           class="flex items-center justify-between px-3 py-2 rounded-lg text-xs font-semibold transition-colors {{ request()->routeIs('admin.shorts*') ? 'bg-zinc-950 text-white shadow-xs' : 'text-zinc-600 hover:bg-zinc-100 hover:text-zinc-950' }}">
                            <div class="flex items-center gap-3">
                                <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                <span>Shorts</span>
                            </div>
                        </a>

                        <a href="{{ route('admin.reports.index') }}" 
                           class="flex items-center justify-between px-3 py-2 rounded-lg text-xs font-semibold transition-colors {{ request()->routeIs('admin.reports*') ? 'bg-zinc-950 text-white shadow-xs' : 'text-zinc-600 hover:bg-zinc-100 hover:text-zinc-950' }}">
                            <div class="flex items-center gap-3">
                                <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                                <span>Trust &amp; Reports</span>
                            </div>
                        </a>

                        <a href="{{ route('admin.promotions.index') }}" 
                           class="flex items-center justify-between px-3 py-2 rounded-lg text-xs font-semibold transition-colors {{ request()->routeIs('admin.promotions*') ? 'bg-zinc-950 text-white shadow-xs' : 'text-zinc-600 hover:bg-zinc-100 hover:text-zinc-950' }}">
                            <div class="flex items-center gap-3">
                                <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/></svg>
                                <span>Promotions</span>
                            </div>
                        </a>

                        <a href="{{ route('admin.audit_logs.index') }}" 
                           class="flex items-center justify-between px-3 py-2 rounded-lg text-xs font-semibold transition-colors {{ request()->routeIs('admin.audit_logs*') ? 'bg-zinc-950 text-white shadow-xs' : 'text-zinc-600 hover:bg-zinc-100 hover:text-zinc-950' }}">
                            <div class="flex items-center gap-3">
                                <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                                <span>Audit Logs</span>
                            </div>
                        </a>
                    </div>
                </div>

                <!-- Section 4: Live Storefront -->
                <div class="pt-2">
                    <a href="{{ route('home') }}" target="_blank" 
                       class="flex items-center justify-between px-3 py-2 rounded-lg text-xs font-semibold text-zinc-700 bg-zinc-50 hover:bg-zinc-100 border border-zinc-200/80 transition-colors">
                        <span class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-zinc-900" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                            <span>Live Storefront</span>
                        </span>
                        <span class="text-[10px] text-zinc-400 font-mono">↗</span>
                    </a>
                </div>
            </nav>
        </div>

        <!-- Sidebar Footer / Minimal Administrator Identity -->
        <div class="p-4 border-t border-zinc-100 bg-zinc-50/50">
            <div class="flex items-center justify-between gap-3 mb-3">
                <div class="flex items-center gap-2.5 overflow-hidden">
                    <div class="w-8 h-8 rounded-lg bg-zinc-900 text-white flex items-center justify-center font-bold text-xs shrink-0">
                        {{ strtoupper(substr(auth()->user()?->name ?: 'A', 0, 1)) }}
                    </div>
                    <div class="overflow-hidden">
                        <p class="text-xs font-bold text-zinc-950 truncate">{{ auth()->user()?->name ?: 'Admin' }}</p>
                        <p class="text-[10px] text-zinc-500 font-mono truncate">{{ auth()->user()?->email ?: 'crab.hashir@gmail.com' }}</p>
                    </div>
                </div>
            </div>

            <form action="{{ route('admin.logout') }}" method="POST">
                @csrf
                <button type="submit" class="w-full flex items-center justify-center gap-2 py-1.5 px-3 rounded-lg text-xs font-semibold text-zinc-600 hover:text-rose-600 bg-white hover:bg-rose-50/60 border border-zinc-200/80 transition">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                    <span>Sign Out</span>
                </button>
            </form>
        </div>
    </aside>

    <!-- MAIN CONTENT AREA -->
    <div class="flex-1 flex flex-col min-w-0 h-full overflow-hidden">
        
        <!-- Minimal Monochrome Top Header -->
        <header class="h-16 bg-white border-b border-zinc-200/80 px-4 sm:px-8 flex items-center justify-between z-10 flex-shrink-0 shadow-2xs">
            <!-- Left: Toggle & Title -->
            <div class="flex items-center gap-3">
                <button @click="mobileSidebarOpen = true" class="lg:hidden p-2 -ml-1 text-zinc-600 hover:text-zinc-950 hover:bg-zinc-100 rounded-lg transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                </button>

                <div>
                    <h1 class="text-sm sm:text-base font-bold text-zinc-950 tracking-tight flex items-center gap-2">
                        <span>{{ $title }}</span>
                    </h1>
                </div>
            </div>

            <!-- Right: Direct Storefront Action & Admin Profile -->
            <div class="flex items-center gap-2.5">
                <a href="{{ route('account') }}" class="hidden sm:inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold text-zinc-700 hover:text-zinc-950 hover:bg-zinc-100 border border-zinc-200/80 transition">
                    <span>Account</span>
                </a>

                <a href="{{ route('home') }}" target="_blank" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold text-white bg-zinc-950 hover:bg-zinc-800 transition shadow-xs">
                    <span>View Storefront</span>
                    <span class="text-xs">↗</span>
                </a>
            </div>
        </header>

        <!-- MAIN SCROLLABLE CONTENT BODY -->
        <main class="flex-1 overflow-y-auto bg-[#FAFAFA] p-4 sm:p-6 lg:p-8 relative z-10">
            <div class="max-w-7xl mx-auto space-y-6">
                {{ $slot }}
            </div>
        </main>
    </div>

    <!-- Active CSRF Keep-Alive Heartbeat for Unbreakable Admin Session -->
    <script>
        (function() {
            setInterval(function() {
                fetch('/up', { credentials: 'same-origin' })
                    .catch(function(err) { console.debug('Keepalive ping', err); });
            }, 60000);
        })();
    </script>
</body>
</html>
