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

    <!-- Grail Typography -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="preconnect" href="https://api.fontshare.com" crossorigin>
    <link rel="stylesheet" href="https://api.fontshare.com/v2/css?f[]=switzer@300,400,500,600,700,800&display=swap">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,600;0,700;1,300&family=Outfit:wght@300;400;500;600;700&display=swap">
    <meta name="theme-color" content="#000000">
    
    <!-- Favicon & Brand Icons -->
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32x32.png') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('favicon-16x16.png') }}">
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('apple-touch-icon.png') }}">
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}">

    <!-- Vite Scripts & Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <!-- Alpine.js (Defer) -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <!-- Synchronous K71 Dynamic Theme Bootstrapper (Zero Flash) -->
    <script>
        (function() {
            try {
                var stored = localStorage.getItem('k71_theme');
                var theme = stored === 'light' ? 'light' : 'dark';
                var doc = document.documentElement;
                if (theme === 'light') {
                    doc.classList.add('k71-light-mode');
                    doc.classList.remove('k71-dark-mode');
                    doc.setAttribute('data-theme', 'light');
                } else {
                    doc.classList.add('k71-dark-mode');
                    doc.classList.remove('k71-light-mode');
                    doc.setAttribute('data-theme', 'dark');
                }
            } catch (e) {}
        })();
    </script>
</head>
<body class="grail-marketplace-body bg-black text-[#EFE9F2] selection:bg-[#ECD2F3] selection:text-[#1A1018] font-sans antialiased flex flex-col min-h-screen overflow-x-clip max-w-full w-full" style="font-family: var(--font-grail-sans);">

    <!-- Header / Nav -->
    <header class="sticky top-0 z-50 bg-black/82 backdrop-blur-2xl border-b border-white/10 text-[#EFE9F2]">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16 items-center">
                <div class="flex-shrink-0 flex items-center">
                    <a href="{{ route('home') }}" class="flex items-center gap-2.5 group">
                        <div class="w-8 h-8 rounded-xl bg-[#ECD2F3] p-1 flex items-center justify-center shadow-[0_0_20px_rgba(236,210,243,0.22)]">
                            <img src="{{ asset('images/icon.svg') }}" alt="K71" class="w-full h-full object-contain">
                        </div>
                        <div class="flex flex-col">
                            <span class="font-bold text-xl text-white leading-none tracking-tight">
                                K71<span class="text-[#ECD2F3] text-sm font-medium">.IN</span>
                            </span>
                            <span class="text-[8px] font-semibold uppercase tracking-[0.2em] text-[#C9A24A] mt-0.5">
                                Curated Marketplace
                            </span>
                        </div>
                    </a>
                </div>
                
                <!-- Desktop Nav -->
                <nav class="hidden md:flex space-x-6">
                    <a href="{{ route('catalog.index') }}" class="text-[#EFE9F2]/75 hover:text-[#FFCDFF] px-3 py-2 text-xs font-semibold uppercase tracking-wider transition-colors">Shop</a>
                    <a href="{{ route('stores.index') }}" class="text-[#EFE9F2]/75 hover:text-[#FFCDFF] px-3 py-2 text-xs font-semibold uppercase tracking-wider transition-colors">Stores</a>
                </nav>

                <div class="flex items-center space-x-4">
                    <x-theme-toggle :grailTheme="true" />
                    <a href="{{ route('catalog.index') }}" class="text-[#EFE9F2]/75 hover:text-[#FFCDFF] transition-colors">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                        </svg>
                    </a>
                    @auth
                        <a href="{{ route('account') }}" class="text-[#EFE9F2]/75 hover:text-[#FFCDFF] transition-colors">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
                            </svg>
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="grail-cta-pill px-4 py-1.5 text-xs">Sign In</a>
                    @endauth
                    <a href="{{ route('cart.index') }}" class="text-[#EFE9F2]/75 hover:text-[#FFCDFF] relative transition-colors">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 01-1.12-1.243l1.264-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119 1.007zM8.625 10.5a.375.375 0 11-.75 0 .375.375 0 01.75 0zm7.5 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
                        </svg>
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
    <footer class="bg-[#060606] border-t border-white/10 mt-12 py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center text-xs text-[#EFE9F2]/55">
            &copy; {{ date('Y') }} {{ config('marketplace.name') }}. All rights reserved.
        </div>
    </footer>

    <!-- Mobile Bottom Tab Navigation (Section F) -->
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

    <!-- 6-in-1 Draggable Floating Radial Nav -->
    <x-floating-six-nav />
</body>
</html>
