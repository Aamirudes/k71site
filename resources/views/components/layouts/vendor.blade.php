@props(['title' => null])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ? $title . ' | Vendor Portal | ' . config('marketplace.name') : 'Vendor Portal | ' . config('marketplace.name') }}</title>
    
    <!-- Favicon & Brand Icons -->
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32x32.png') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('favicon-16x16.png') }}">
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('apple-touch-icon.png') }}">
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}">

    <!-- Google Fonts: Plus Jakarta Sans & Cormorant Garamond -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=Space+Grotesk:wght@500;700&display=swap" rel="stylesheet">

    <!-- Anti-FOUC Theme Initializer -->
    <script>
        (function() {
            const savedTheme = localStorage.getItem('k71_theme');
            if (savedTheme === 'light') {
                document.documentElement.classList.remove('dark');
            } else {
                document.documentElement.classList.add('dark');
            }
        })();
    </script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <style>
        body { font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, sans-serif; }
        .font-display { font-family: 'Space Grotesk', sans-serif; }
    </style>
</head>
<body class="bg-[#f8fafc] dark:bg-[#07080d] text-slate-900 dark:text-slate-100 antialiased flex flex-col md:flex-row min-h-screen md:h-screen md:overflow-hidden transition-colors duration-300"
      x-data="{
          sidebarOpen: false,
          darkMode: document.documentElement.classList.contains('dark'),
          toggleTheme() {
              this.darkMode = !this.darkMode;
              if (this.darkMode) {
                  document.documentElement.classList.add('dark');
                  localStorage.setItem('k71_theme', 'dark');
              } else {
                  document.documentElement.classList.remove('dark');
                  localStorage.setItem('k71_theme', 'light');
              }
          }
      }">

    @php
        $curVendor = auth()->user()?->vendor;
        $vType = $curVendor?->vendor_type ?? 'clothing';
        
        $vConfigs = [
            'clothing' => ['icon' => '👗', 'label' => 'Fashion & Apparel', 'color' => 'from-rose-500 to-pink-600', 'badge' => 'bg-rose-500/10 text-rose-600 dark:text-rose-400 border-rose-500/20'],
            'food' => ['icon' => '🍽️', 'label' => 'Cloud Kitchen & Menu', 'color' => 'from-amber-500 to-orange-600', 'badge' => 'bg-orange-500/10 text-orange-600 dark:text-orange-400 border-orange-500/20'],
            'services' => ['icon' => '🛠️', 'label' => 'Doorstep & Pro Services', 'color' => 'from-blue-500 to-indigo-600', 'badge' => 'bg-blue-500/10 text-blue-600 dark:text-blue-400 border-blue-500/20'],
            'used' => ['icon' => '📱', 'label' => 'Pre-Owned & Refurbished', 'color' => 'from-emerald-500 to-teal-600', 'badge' => 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border-emerald-500/20'],
            'b2b' => ['icon' => '🏭', 'label' => 'Heavy Industrial & RFQ', 'color' => 'from-purple-500 to-violet-600', 'badge' => 'bg-purple-500/10 text-purple-600 dark:text-purple-400 border-purple-500/20'],
        ];
        $currentVInfo = $vConfigs[$vType] ?? $vConfigs['clothing'];
    @endphp

    <!-- Mobile sidebar backdrop -->
    <div x-show="sidebarOpen" 
         x-cloak
         class="fixed inset-0 z-40 bg-black/60 backdrop-blur-md md:hidden" 
         x-transition:enter="transition-opacity ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition-opacity ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         @click="sidebarOpen = false"></div>

    <!-- Mobile sidebar drawer -->
    <div x-show="sidebarOpen" 
         x-cloak
         class="fixed inset-y-0 left-0 z-50 w-72 bg-white dark:bg-[#0d0f17] border-r border-slate-200 dark:border-white/10 flex flex-col md:hidden shadow-2xl"
         x-transition:enter="transition ease-in-out duration-300 transform"
         x-transition:enter-start="-translate-x-full"
         x-transition:enter-end="translate-x-0"
         x-transition:leave="transition ease-in-out duration-300 transform"
         x-transition:leave-start="translate-x-0"
         x-transition:leave-end="-translate-x-full">
        
        <!-- Mobile Sidebar Header -->
        <div class="h-20 flex items-center justify-between px-6 border-b border-slate-200 dark:border-white/10 bg-slate-50/50 dark:bg-white/[0.02]">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-br {{ $currentVInfo['color'] }} flex items-center justify-center text-white text-lg font-bold shadow-md shadow-violet-500/10">
                    {{ $currentVInfo['icon'] }}
                </div>
                <div class="min-w-0">
                    <h2 class="text-sm font-bold text-slate-900 dark:text-white truncate max-w-[140px]">{{ $curVendor?->shop_name ?? 'My Merchant Portal' }}</h2>
                    <span class="inline-flex items-center text-[10px] font-bold px-1.5 py-0.5 rounded border {{ $currentVInfo['badge'] }}">
                        {{ $currentVInfo['label'] }}
                    </span>
                </div>
            </div>
            <button @click="sidebarOpen = false" class="p-2 rounded-lg text-slate-400 hover:text-slate-600 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-white/10 transition-colors">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <!-- Mobile Navigation Links -->
        <nav class="flex-1 px-4 py-4 space-y-1 overflow-y-auto no-scrollbar">
            @include('vendor.partials.nav_links')
        </nav>

        <!-- Mobile Sidebar Footer -->
        <div class="p-4 border-t border-slate-200 dark:border-white/10 space-y-2.5 bg-slate-50/50 dark:bg-white/[0.02]">
            <button type="button" @click="toggleTheme()" class="w-full flex items-center justify-between px-3 py-2 rounded-xl text-xs font-semibold text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-white/5 transition-colors">
                <span class="flex items-center gap-2">
                    <span x-show="!darkMode">☀️ Light Mode</span>
                    <span x-show="darkMode">🌙 Dark Mode</span>
                </span>
                <span class="text-[10px] px-2 py-0.5 rounded bg-slate-200 dark:bg-white/10 text-slate-700 dark:text-slate-300">Toggle</span>
            </button>

            <a href="{{ route('account') }}" class="flex items-center px-3 py-2 text-xs font-medium text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white rounded-xl hover:bg-slate-100 dark:hover:bg-white/5 transition-colors">
                <svg class="mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
                Customer Account
            </a>
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="w-full flex items-center px-3 py-2 text-xs font-medium text-rose-500 hover:text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-500/10 rounded-xl transition-colors">
                    <svg class="mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" /></svg>
                    Logout
                </button>
            </form>
        </div>
    </div>

    <!-- Desktop Sidebar -->
    <aside class="w-72 bg-white dark:bg-[#0c0d14] border-r border-slate-200 dark:border-white/[0.08] hidden md:flex flex-col shrink-0 relative z-30 transition-colors duration-300">
        
        <!-- Desktop Brand Header -->
        <div class="h-20 flex items-center px-6 border-b border-slate-200 dark:border-white/[0.08] bg-slate-50/50 dark:bg-white/[0.01]">
            <div class="flex items-center gap-3.5 min-w-0">
                <div class="w-11 h-11 rounded-2xl bg-gradient-to-br {{ $currentVInfo['color'] }} p-[2px] shadow-lg shadow-violet-500/10">
                    <div class="w-full h-full bg-white dark:bg-zinc-900 rounded-[14px] flex items-center justify-center text-xl">
                        {{ $currentVInfo['icon'] }}
                    </div>
                </div>
                <div class="min-w-0">
                    <div class="flex items-center gap-1.5">
                        <a href="{{ route('vendor.dashboard') }}" class="font-display text-sm font-bold text-slate-900 dark:text-white truncate block max-w-[150px] tracking-tight hover:text-purple-600 dark:hover:text-purple-400 transition-colors">
                            {{ $curVendor?->shop_name ?? 'Merchant Portal' }}
                        </a>
                    </div>
                    <div class="flex items-center gap-1.5 mt-0.5">
                        <span class="inline-flex items-center text-[10px] font-bold px-2 py-0.5 rounded-full border {{ $currentVInfo['badge'] }}">
                            {{ $currentVInfo['label'] }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Desktop Navigation Items -->
        <nav class="flex-1 px-4 py-5 space-y-1.5 overflow-y-auto no-scrollbar">
            @include('vendor.partials.nav_links')
        </nav>

        <!-- Desktop Sidebar Footer -->
        <div class="p-4 border-t border-slate-200 dark:border-white/[0.08] space-y-2 bg-slate-50/40 dark:bg-white/[0.01]">
            <!-- Live Store Link -->
            @if($curVendor?->storefront_url)
            <a href="{{ $curVendor->storefront_url }}" target="_blank" class="group flex items-center justify-between p-2.5 rounded-xl border border-slate-200 dark:border-white/[0.08] bg-white dark:bg-white/[0.02] hover:border-purple-500/50 hover:bg-purple-50/50 dark:hover:bg-purple-500/5 transition-all">
                <div class="flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                    <span class="text-xs font-semibold text-slate-700 dark:text-slate-200 group-hover:text-purple-600 dark:group-hover:text-purple-300">Live Storefront</span>
                </div>
                <svg class="w-3.5 h-3.5 text-slate-400 group-hover:text-purple-500 group-hover:translate-x-0.5 transition-all" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
            </a>
            @endif

            <div class="flex items-center justify-between pt-1">
                <a href="{{ route('account') }}" class="text-xs font-medium text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white transition-colors flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
                    Buyer Mode
                </a>

                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="text-xs font-semibold text-rose-500 hover:text-rose-600 dark:hover:text-rose-400 transition-colors">
                        Sign Out
                    </button>
                </form>
            </div>
        </div>
    </aside>

    <!-- Main Viewport -->
    <div class="flex-1 flex flex-col min-h-screen md:h-screen md:overflow-hidden bg-[#f8fafc] dark:bg-[#07080d] transition-colors duration-300">
        
        <!-- Award-Winning Top Bar -->
        <header class="h-20 bg-white/80 dark:bg-[#0c0d14]/80 backdrop-blur-xl border-b border-slate-200 dark:border-white/[0.08] flex items-center justify-between px-4 sm:px-8 shrink-0 z-20">
            <!-- Mobile Menu Toggle -->
            <div class="flex items-center gap-3 md:hidden">
                <button @click="sidebarOpen = true" type="button" class="p-2 rounded-xl text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-white/10 transition-colors">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" /></svg>
                </button>
                <div class="flex items-center gap-2">
                    <span class="text-lg">{{ $currentVInfo['icon'] }}</span>
                    <span class="font-display font-bold text-sm text-slate-900 dark:text-white truncate max-w-[150px]">{{ $curVendor?->shop_name }}</span>
                </div>
            </div>

            <!-- Left Desktop Breadcrumbs / Status -->
            <div class="hidden md:flex items-center gap-4">
                <div class="flex items-center gap-2 text-xs font-medium text-slate-500 dark:text-slate-400">
                    <span class="px-2.5 py-1 rounded-lg bg-slate-100 dark:bg-white/[0.06] text-slate-700 dark:text-slate-300 font-bold">K71 Enterprise</span>
                    <span>/</span>
                    <span class="text-slate-900 dark:text-white font-semibold">{{ $title ?? 'Merchant Operations' }}</span>
                </div>
                <div class="hidden lg:inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                    Subdomain Wildcard Engine Active
                </div>
            </div>

            <!-- Right Controls: Theme Toggle, Quick Action, Live Store -->
            <div class="flex items-center gap-3">
                <!-- Theme Toggle Button -->
                <button type="button" 
                        @click="toggleTheme()" 
                        class="p-2.5 rounded-xl border border-slate-200 dark:border-white/10 bg-white dark:bg-white/[0.04] text-slate-600 dark:text-slate-300 hover:border-purple-500/40 hover:text-purple-600 dark:hover:text-purple-400 shadow-xs transition-all active:scale-95"
                        :title="darkMode ? 'Switch to Light Mode' : 'Switch to Dark Mode'">
                    <span x-show="!darkMode" class="flex items-center gap-1 text-xs font-bold">
                        <svg class="w-4 h-4 text-amber-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4 8a4 4 0 11-8 0 4 4 0 018 0zm-.464 4.95l.707.707a1 1 0 001.414-1.414l-.707-.707a1 1 0 00-1.414 1.414zm2.12-10.607a1 1 0 010 1.414l-.706.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0zM17 11a1 1 0 100-2h-1a1 1 0 100 2h1zm-7 4a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zM5.05 6.464A1 1 0 106.465 5.05l-.708-.707a1 1 0 00-1.414 1.414l.707.707zm1.414 8.486l-.707.707a1 1 0 01-1.414-1.414l.707-.707a1 1 0 011.414 1.414zM4 11a1 1 0 100-2H3a1 1 0 000 2h1z" clip-rule="evenodd" /></svg>
                    </span>
                    <span x-show="darkMode" class="flex items-center gap-1 text-xs font-bold">
                        <svg class="w-4 h-4 text-purple-400" fill="currentColor" viewBox="0 0 20 20"><path d="M17.293 13.293A8 8 0 016.707 2.707a8.001 8.001 0 1010.586 10.586z" /></svg>
                    </span>
                </button>

                <!-- Quick Action New Product -->
                <a href="{{ route('vendor.products.create') }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-gradient-to-r from-purple-600 to-indigo-600 hover:from-purple-500 hover:to-indigo-500 text-white text-xs font-bold shadow-md shadow-purple-600/20 active:scale-95 transition-all">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                    <span class="hidden sm:inline">Add {{ $vType === 'food' ? 'Dish' : ($vType === 'services' ? 'Service' : 'Product') }}</span>
                    <span class="sm:hidden">Add</span>
                </a>

                <!-- View Live Store -->
                @if($curVendor?->storefront_url)
                <a href="{{ $curVendor->storefront_url }}" target="_blank" class="hidden sm:inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl border border-slate-200 dark:border-white/10 bg-white dark:bg-white/[0.04] text-xs font-bold text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-white/[0.08] transition-colors">
                    <svg class="w-3.5 h-3.5 text-purple-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                    <span>Store</span>
                </a>
                @endif
            </div>
        </header>

        <!-- Main Scrollable Dashboard Content Area -->
        <main class="flex-1 overflow-y-auto p-4 sm:p-6 lg:p-8 pb-32 sm:pb-24">
            @if(session('success'))
                <div class="mb-6 p-4 rounded-2xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-800 dark:text-emerald-300 flex items-center justify-between text-xs font-semibold backdrop-blur-md">
                    <div class="flex items-center gap-2">
                        <span>✓</span>
                        <span>{{ session('success') }}</span>
                    </div>
                </div>
            @endif

            @if(session('error'))
                <div class="mb-6 p-4 rounded-2xl bg-rose-500/10 border border-rose-500/20 text-rose-800 dark:text-rose-300 flex items-center justify-between text-xs font-semibold backdrop-blur-md">
                    <div class="flex items-center gap-2">
                        <span>⚠</span>
                        <span>{{ session('error') }}</span>
                    </div>
                </div>
            @endif

            {{ $slot }}
        </main>
    </div>
</body>
</html>
