@props(['title' => 'Admin Console'])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-[#050811]">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title }} • Master Admin Console • {{ config('marketplace.name') }}</title>

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
    <style>
        [x-cloak] { display: none !important; }
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .font-display { font-family: 'Outfit', sans-serif; }
    </style>
    <script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/collapse@3.x.x/dist/cdn.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="h-full text-slate-100 bg-[#050811] antialiased overflow-hidden flex relative" x-data="{ mobileSidebarOpen: false }">

    <!-- Ambient Liquid Glass Refraction Shapes -->
    <div class="fixed inset-0 pointer-events-none overflow-hidden z-0">
        <div class="absolute -top-32 -left-32 w-96 sm:w-[500px] h-96 sm:h-[500px] rounded-full bg-indigo-600/15 blur-[120px]"></div>
        <div class="absolute -bottom-32 -right-32 w-96 sm:w-[500px] h-96 sm:h-[500px] rounded-full bg-cyan-600/15 blur-[130px]"></div>
        <div class="absolute inset-0 bg-[radial-gradient(rgba(255,255,255,0.06)_1px,transparent_1px)] [background-size:24px_24px] opacity-30"></div>
    </div>

    <!-- Mobile Drawer Backdrop -->
    <div 
        x-show="mobileSidebarOpen" 
        x-cloak 
        @click="mobileSidebarOpen = false" 
        class="fixed inset-0 z-40 bg-slate-950/80 backdrop-blur-md lg:hidden"
        x-transition:enter="transition-opacity ease-linear duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition-opacity ease-linear duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
    ></div>

    <!-- SIDEBAR (Desktop Fixed & Mobile Drawer) -->
    <aside 
        class="fixed inset-y-0 left-0 z-50 w-72 bg-slate-950/80 backdrop-blur-2xl text-slate-100 flex flex-col justify-between transition-transform duration-300 ease-in-out lg:static lg:translate-x-0 border-r border-white/10"
        :class="mobileSidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
    >
        <!-- Brand Header -->
        <div>
            <div class="h-20 flex items-center justify-between px-6 border-b border-white/10 bg-white/[0.02]">
                <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3 group">
                    <img src="{{ asset('images/icon.svg') }}" alt="K71" class="w-10 h-10 rounded-xl shadow-lg border border-white/15">
                    <div>
                        <span class="block text-base font-bold text-white tracking-tight leading-none font-display">
                            K71<span class="text-indigo-400 text-xs font-mono ml-1">ADMIN</span>
                        </span>
                        <div class="flex items-center gap-1.5 mt-1">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                            <span class="text-[10px] font-mono uppercase tracking-wider text-amber-400 font-semibold">Master Admin</span>
                        </div>
                    </div>
                </a>

                <button @click="mobileSidebarOpen = false" class="lg:hidden text-slate-400 hover:text-white p-1">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <!-- Navigation Sections -->
            <nav class="px-4 py-6 space-y-6 overflow-y-auto max-h-[calc(100vh-14rem)]">
                
                <!-- Section 1: Core -->
                <div>
                    <p class="px-3 text-[11px] font-mono uppercase tracking-widest text-slate-400 font-semibold mb-2">Core Overview</p>
                    <div class="space-y-1">
                        <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all {{ request()->routeIs('admin.dashboard') ? 'bg-amber-500 text-slate-950 font-semibold shadow-md shadow-amber-500/20' : 'text-slate-300 hover:bg-slate-800/60 hover:text-white' }}">
                            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                            <span>Platform Dashboard</span>
                        </a>
                    </div>
                </div>

                <!-- Section 2: Storefront Merchandising & SEO -->
                <div>
                    <p class="px-3 text-[11px] font-mono uppercase tracking-widest text-slate-400 font-semibold mb-2">Storefront &amp; Growth</p>
                    <div class="space-y-1">
                        <a href="{{ route('admin.homepage') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all {{ request()->routeIs('admin.homepage*') ? 'bg-amber-500 text-slate-950 font-semibold shadow-md shadow-amber-500/20' : 'text-slate-300 hover:bg-slate-800/60 hover:text-white' }}">
                            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z"/></svg>
                            <span>Homepage &amp; SEO Studio</span>
                            <span class="ml-auto text-[10px] uppercase font-bold tracking-wider px-1.5 py-0.5 rounded {{ request()->routeIs('admin.homepage*') ? 'bg-slate-900 text-amber-300' : 'bg-amber-500/20 text-amber-300' }}">Live</span>
                        </a>

                        <a href="{{ route('admin.navigation.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all {{ request()->routeIs('admin.navigation*') ? 'bg-amber-500 text-slate-950 font-semibold shadow-md shadow-amber-500/20' : 'text-slate-300 hover:bg-slate-800/60 hover:text-white' }}">
                            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                            <span>Mobile Panel &amp; Nav</span>
                            <span class="ml-auto text-[9px] uppercase font-bold tracking-wider px-1.5 py-0.5 rounded {{ request()->routeIs('admin.navigation*') ? 'bg-slate-900 text-amber-300' : 'bg-emerald-500/20 text-emerald-300' }}">Power</span>
                        </a>
                    </div>
                </div>

                <!-- Section 3: Marketplace Operations -->
                <div>
                    <p class="px-3 text-[11px] font-mono uppercase tracking-widest text-slate-400 font-semibold mb-2">Commerce Operations</p>
                    <div class="space-y-1">
                        <a href="{{ route('admin.users.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all {{ request()->routeIs('admin.users*') ? 'bg-amber-500 text-slate-950 font-semibold shadow-md shadow-amber-500/20' : 'text-slate-300 hover:bg-slate-800/60 hover:text-white' }}">
                            <span class="text-base">👥</span>
                            <span>Users &amp; Accounts</span>
                            <span class="ml-auto text-[9px] uppercase font-bold tracking-wider px-1.5 py-0.5 rounded {{ request()->routeIs('admin.users*') ? 'bg-slate-900 text-amber-300' : 'bg-purple-500/20 text-purple-300' }}">Super</span>
                        </a>

                        <a href="{{ route('admin.orders') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all {{ request()->routeIs('admin.orders*') ? 'bg-amber-500 text-slate-950 font-semibold shadow-md shadow-amber-500/20' : 'text-slate-300 hover:bg-slate-800/60 hover:text-white' }}">
                            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                            <span>All Orders</span>
                        </a>

                        <a href="{{ route('admin.settlements') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all {{ request()->routeIs('admin.settlements*') ? 'bg-amber-500 text-slate-950 font-semibold shadow-md shadow-amber-500/20' : 'text-slate-300 hover:bg-slate-800/60 hover:text-white' }}">
                            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <span>Settlements</span>
                            <span class="ml-auto text-[9px] uppercase font-bold font-mono tracking-wider px-1.5 py-0.5 rounded {{ request()->routeIs('admin.settlements*') ? 'bg-slate-900 text-amber-300' : 'bg-emerald-500/20 text-emerald-300' }}">PhonePe</span>
                        </a>

                        <a href="{{ route('admin.products') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all {{ request()->routeIs('admin.products*') ? 'bg-amber-500 text-slate-950 font-semibold shadow-md shadow-amber-500/20' : 'text-slate-300 hover:bg-slate-800/60 hover:text-white' }}">
                            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
                            <span>All Products</span>
                        </a>

                        <a href="{{ route('admin.services.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all {{ request()->routeIs('admin.services*') ? 'bg-amber-500 text-slate-950 font-semibold shadow-md shadow-amber-500/20' : 'text-slate-300 hover:bg-slate-800/60 hover:text-white' }}">
                            <span class="text-base">⚡</span>
                            <span>Services &amp; Gigs</span>
                            <span class="ml-auto text-[9px] uppercase font-bold tracking-wider px-1.5 py-0.5 rounded {{ request()->routeIs('admin.services*') ? 'bg-slate-900 text-amber-300' : 'bg-purple-500/20 text-purple-300' }}">Pro</span>
                        </a>

                        <a href="{{ route('admin.vendors') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all {{ request()->routeIs('admin.vendors*') ? 'bg-amber-500 text-slate-950 font-semibold shadow-md shadow-amber-500/20' : 'text-slate-300 hover:bg-slate-800/60 hover:text-white' }}">
                            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                            <span>Manage Vendors</span>
                        </a>

                        <a href="{{ route('admin.shorts.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all {{ request()->routeIs('admin.shorts*') ? 'bg-amber-500 text-slate-950 font-semibold shadow-md shadow-amber-500/20' : 'text-slate-300 hover:bg-slate-800/60 hover:text-white' }}">
                            <span class="text-base">🎬</span>
                            <span>Shorts Moderation</span>
                        </a>

                        <a href="{{ route('admin.reports.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all {{ request()->routeIs('admin.reports*') ? 'bg-amber-500 text-slate-950 font-semibold shadow-md shadow-amber-500/20' : 'text-slate-300 hover:bg-slate-800/60 hover:text-white' }}">
                            <span class="text-base">🛡️</span>
                            <span>Trust &amp; Reports</span>
                        </a>

                        <a href="{{ route('admin.promotions.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all {{ request()->routeIs('admin.promotions*') ? 'bg-amber-500 text-slate-950 font-semibold shadow-md shadow-amber-500/20' : 'text-slate-300 hover:bg-slate-800/60 hover:text-white' }}">
                            <span class="text-base">⚡</span>
                            <span>Promotions &amp; Flash</span>
                        </a>

                        <a href="{{ route('admin.audit_logs.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all {{ request()->routeIs('admin.audit_logs*') ? 'bg-amber-500 text-slate-950 font-semibold shadow-md shadow-amber-500/20' : 'text-slate-300 hover:bg-slate-800/60 hover:text-white' }}">
                            <span class="text-base">📜</span>
                            <span>Audit Logs</span>
                        </a>
                    </div>
                </div>

                <!-- Section 4: External Storefront Preview Link -->
                <div class="pt-4 border-t border-slate-800/80">
                    <a href="{{ route('home') }}" target="_blank" class="flex items-center justify-between px-3 py-2.5 rounded-xl text-xs font-semibold text-slate-300 bg-slate-800/40 hover:bg-slate-800 hover:text-white transition-all border border-slate-800">
                        <span class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                            <span>View Live Storefront</span>
                        </span>
                        <span class="text-[10px] text-slate-400 font-mono">External ↗</span>
                    </a>
                </div>
            </nav>
        </div>

        <!-- Sidebar Footer / Admin Identity -->
        <div class="p-4 border-t border-slate-800/80 bg-slate-950/50">
            <div class="p-3 rounded-2xl bg-slate-900 border border-slate-800 space-y-3">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl bg-amber-500/20 text-amber-400 flex items-center justify-center font-bold text-sm border border-amber-500/30 flex-shrink-0">
                        SA
                    </div>
                    <div class="overflow-hidden">
                        <p class="text-xs font-bold text-white truncate">Master Administrator</p>
                        <p class="text-[10px] font-mono text-slate-400 truncate">{{ auth()->user()->email ?? 'crab.hashir@gmail.com' }}</p>
                    </div>
                </div>

                <form action="{{ route('admin.logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="w-full flex items-center justify-center gap-2 py-2 px-3 rounded-xl text-xs font-semibold bg-red-500/10 hover:bg-red-500/20 text-red-400 border border-red-500/20 transition-colors">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                        <span>Secure Sign Out</span>
                    </button>
                </form>
            </div>
        </div>
    </aside>

    <!-- MAIN WRAPPER (Topbar + Page Content) -->
    <div class="flex-1 flex flex-col min-w-0 h-full overflow-hidden">
        
        <!-- TOPBAR (Dedicated Executive Header) -->
        <header class="h-20 bg-slate-950/60 backdrop-blur-2xl border-b border-white/10 px-4 sm:px-8 flex items-center justify-between z-10 flex-shrink-0 shadow-lg shadow-black/20">
            <!-- Left: Toggle & Title -->
            <div class="flex items-center gap-4">
                <button @click="mobileSidebarOpen = true" class="lg:hidden p-2 rounded-xl text-slate-300 hover:text-white hover:bg-white/10 transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                </button>

                <div>
                    <h1 class="text-lg sm:text-xl font-display font-extrabold text-white tracking-tight flex items-center gap-2">
                        <span>{{ $title }}</span>
                    </h1>
                    <div class="flex items-center gap-2 text-xs text-slate-400">
                        <span>K71 Platform Core</span>
                        <span>•</span>
                        <span class="flex items-center gap-1 text-emerald-400 font-medium">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                            Restricted Root Console
                        </span>
                    </div>
                </div>
            </div>

            <!-- Right: Live Storefront Action & Admin Session Pill -->
            <div class="flex items-center gap-3">
                <a href="{{ route('account') }}" class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl text-xs font-semibold text-slate-200 bg-white/5 hover:bg-white/10 transition-colors border border-white/10 shadow-inner">
                    <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                    <span>Customer Account</span>
                </a>

                <a href="{{ route('home') }}" target="_blank" class="hidden sm:inline-flex items-center gap-2 px-3.5 py-2 rounded-xl text-xs font-semibold text-white bg-indigo-600/30 hover:bg-indigo-600/50 transition-colors border border-indigo-500/30 shadow-lg shadow-indigo-500/10">
                    <svg class="w-4 h-4 text-cyan-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                    <span>Storefront Preview</span>
                </a>

                <div class="flex items-center gap-2.5 pl-3 border-l border-white/10">
                    <div class="w-8 h-8 rounded-full bg-gradient-to-tr from-indigo-500 to-cyan-500 text-white flex items-center justify-center font-bold text-xs shadow-md">
                        {{ strtoupper(substr(auth()->user()?->first_name ?? 'MA', 0, 2)) }}
                    </div>
                    <div class="hidden md:block text-left">
                        <p class="text-xs font-bold text-white leading-none">{{ auth()->user()?->first_name ?? 'Master' }} Admin</p>
                        <p class="text-[10px] text-cyan-400 font-mono mt-0.5">Root Access</p>
                    </div>
                </div>
            </div>
        </header>

        <!-- MAIN SCROLLABLE CONTENT BODY -->
        <main class="flex-1 overflow-y-auto bg-transparent p-4 sm:p-8 lg:p-10 relative z-10">
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
