@props(['title' => null, 'seo' => null])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Enforce Pure Clean Light Mode on Marketplace -->
    <script>
        (function() {
            document.documentElement.classList.remove('dark');
            localStorage.setItem('theme', 'light');
        })();
    </script>

    @if($seo)
        <x-seo-meta :model="$seo" />
    @else
        <title>{{ $title ? $title . ' | ' . config('marketplace.name') : config('marketplace.name') }}</title>
        <meta name="description" content="{{ config('marketplace.seo.default_description') }}">
    @endif

    <!-- Fonts are preloaded in CSS via app.css -->
    
    <!-- Favicon & Brand Icons -->
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32x32.png') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('favicon-16x16.png') }}">
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('apple-touch-icon.png') }}">
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}">

    <!-- Vite Scripts & Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <x-k71-design-system />

    <!-- Alpine.js (Defer) -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="bg-brand-background text-brand-text font-sans antialiased flex flex-col min-h-screen">

    <!-- Header / Nav -->
    <header class="sticky top-0 z-50 glass-panel rounded-none border-t-0 border-x-0">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16 items-center">
                <div class="flex-shrink-0 flex items-center">
                    <a href="{{ route('home') }}" class="flex items-center gap-2.5 group">
                        <img src="{{ asset('images/icon.svg') }}" alt="K71" class="w-8 h-8 transition-transform duration-300 group-hover:scale-105 drop-shadow-sm">
                        <div class="flex flex-col">
                            <span class="font-sans text-2xl font-black text-zinc-950 leading-none tracking-tight">
                                K71<span class="text-zinc-500 text-lg">.IN</span>
                            </span>
                            <span class="text-[8px] font-sans font-semibold uppercase tracking-[0.2em] text-zinc-500 -mt-0.5">
                                Curated Marketplace
                            </span>
                        </div>
                    </a>
                </div>
                
                <!-- Desktop Nav -->
                <nav class="hidden md:flex space-x-8">
                    <a href="{{ route('catalog.index') }}" class="text-brand-muted hover:text-brand-text px-3 py-2 text-sm font-medium transition-colors">Shop</a>
                    <a href="{{ route('catalog.index') }}" class="text-brand-muted hover:text-brand-text px-3 py-2 text-sm font-medium transition-colors">Stores</a>
                </nav>

                <div class="flex items-center space-x-4">
                    <a href="{{ route('catalog.index') }}" class="text-brand-muted hover:text-brand-text">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                        </svg>
                    </a>
                    @auth
                        <a href="{{ route('account') }}" class="text-brand-muted hover:text-brand-text">
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
                            </svg>
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="text-sm font-medium text-brand-text hover:text-brand-sage transition-colors">Login</a>
                    @endauth
                    <a href="{{ route('cart.index') }}" class="text-brand-muted hover:text-brand-text relative">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 01-1.12-1.243l1.264-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119 1.007zM8.625 10.5a.375.375 0 11-.75 0 .375.375 0 01.75 0zm7.5 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
                        </svg>
                        <!-- Cart count badge could go here -->
                    </a>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="flex-grow">
        {{ $slot }}
    </main>

    <!-- Footer -->
    <footer class="bg-white border-t border-brand-surface mt-12 py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center text-sm text-brand-muted">
            &copy; {{ date('Y') }} {{ config('marketplace.name') }}. All rights reserved.
        </div>
    </footer>

    <!-- Mobile Bottom Nav Placeholder -->
    <div class="md:hidden fixed bottom-0 left-0 right-0 glass-panel rounded-none border-b-0 border-x-0 h-16 z-50 flex justify-around items-center pb-safe">
        <a href="{{ route('home') }}" class="flex flex-col items-center text-brand-sage">
            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" /></svg>
            <span class="text-[10px] mt-1 font-medium">Home</span>
        </a>
        <a href="{{ route('catalog.index') }}" class="flex flex-col items-center text-brand-muted hover:text-brand-text">
            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" /></svg>
            <span class="text-[10px] mt-1 font-medium">Shop</span>
        </a>
        <a href="{{ route('cart.index') }}" class="flex flex-col items-center text-brand-muted hover:text-brand-text">
            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 01-1.12-1.243l1.264-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119 1.007zM8.625 10.5a.375.375 0 11-.75 0 .375.375 0 01.75 0zm7.5 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" /></svg>
            <span class="text-[10px] mt-1 font-medium">Cart</span>
        </a>
        @auth
            <a href="{{ auth()->user()->hasRole('admin') ? route('admin.dashboard') : (auth()->user()->hasRole('vendor') ? route('vendor.dashboard') : route('account')) }}" class="flex flex-col items-center text-brand-muted hover:text-brand-text">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" /></svg>
                <span class="text-[10px] mt-1 font-medium">Account</span>
            </a>
        @else
            <a href="{{ route('login') }}" class="flex flex-col items-center text-brand-muted hover:text-brand-text">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" /></svg>
                <span class="text-[10px] mt-1 font-medium">Login</span>
            </a>
        @endauth
    </div>

    <!-- CSRF Auto-Refresh & Session Heartbeat (Guarantees zero 419 Page Expired errors) -->
    <script>
        (function() {
            function refreshCsrfToken() {
                return fetch('/api/heartbeat', {
                    method: 'GET',
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                })
                .then(r => r.json())
                .then(data => {
                    if (data && data.csrf_token) {
                        const csrfMeta = document.querySelector('meta[name="csrf-token"]');
                        if (csrfMeta) csrfMeta.content = data.csrf_token;
                        document.querySelectorAll('input[name="_token"]').forEach(inp => {
                            inp.value = data.csrf_token;
                        });
                    }
                    return data?.csrf_token;
                })
                .catch(() => null);
            }
            setInterval(refreshCsrfToken, 60000);
            document.addEventListener('submit', function(e) {
                const form = e.target;
                if (form && form.tagName === 'FORM') {
                    const csrfMeta = document.querySelector('meta[name="csrf-token"]');
                    const tokenInput = form.querySelector('input[name="_token"]');
                    if (csrfMeta && tokenInput && csrfMeta.content) {
                        tokenInput.value = csrfMeta.content;
                    }
                }
            }, true);
        })();
    </script>
</body>
</html>
