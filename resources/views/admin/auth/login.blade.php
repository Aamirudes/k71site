<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-[#050811]">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Master Admin Access • Liquid Portal</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=Outfit:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        [x-cloak] { display: none !important; }
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .font-display { font-family: 'Outfit', sans-serif; }
        
        /* Ultra Liquid Glassmorphism Tokens */
        .liquid-glass {
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.08) 0%, rgba(255, 255, 255, 0.02) 100%);
            backdrop-filter: blur(28px) saturate(190%);
            -webkit-backdrop-filter: blur(28px) saturate(190%);
            border: 1px solid rgba(255, 255, 255, 0.14);
            box-shadow: 0 35px 80px -15px rgba(0, 0, 0, 0.7),
                        inset 0 1px 1px 0 rgba(255, 255, 255, 0.35),
                        inset 0 -1px 1px 0 rgba(255, 255, 255, 0.05);
        }
        .liquid-pill {
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.12) 0%, rgba(255, 255, 255, 0.03) 100%);
            backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.16);
            box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.3);
        }
        .liquid-input {
            background: rgba(10, 16, 32, 0.65);
            backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.12);
            transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
        }
        .liquid-input:focus {
            background: rgba(15, 23, 42, 0.85);
            border-color: rgba(99, 102, 241, 0.8);
            box-shadow: 0 0 25px rgba(99, 102, 241, 0.35), inset 0 1px 1px rgba(255, 255, 255, 0.2);
        }
        @keyframes liquidFloat {
            0%, 100% { transform: translateY(0px) scale(1) rotate(0deg); }
            50% { transform: translateY(-18px) scale(1.05) rotate(4deg); }
        }
        @keyframes liquidFloatRev {
            0%, 100% { transform: translateY(0px) scale(1) rotate(0deg); }
            50% { transform: translateY(18px) scale(0.96) rotate(-5deg); }
        }
        .animate-liquid-blob-1 { animation: liquidFloat 14s ease-in-out infinite; }
        .animate-liquid-blob-2 { animation: liquidFloatRev 18s ease-in-out infinite; }
    </style>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="min-h-full flex items-center justify-center p-4 sm:p-6 bg-[#050811] text-slate-100 relative overflow-hidden antialiased select-none">

    <!-- Ambient Liquid Glass Refraction Shapes & Blobs -->
    <div class="fixed inset-0 pointer-events-none overflow-hidden z-0">
        <!-- Floating Fluid Shape 1 -->
        <div class="absolute -top-32 -left-32 w-96 sm:w-[520px] h-96 sm:h-[520px] rounded-[42%_58%_70%_30%/45%_45%_55%_55%] bg-gradient-to-tr from-indigo-600/35 via-violet-600/25 to-pink-500/20 blur-[90px] animate-liquid-blob-1"></div>
        <!-- Floating Fluid Shape 2 -->
        <div class="absolute -bottom-40 -right-40 w-96 sm:w-[600px] h-96 sm:h-[600px] rounded-[60%_40%_30%_70%/60%_30%_70%_40%] bg-gradient-to-br from-cyan-500/25 via-blue-600/30 to-purple-600/25 blur-[100px] animate-liquid-blob-2"></div>
        <!-- Floating Fluid Center Accent -->
        <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-80 h-80 rounded-full bg-emerald-500/10 blur-[110px] pointer-events-none"></div>
        <!-- Glass Shimmer Grid Overlay -->
        <div class="absolute inset-0 bg-[radial-gradient(rgba(255,255,255,0.08)_1px,transparent_1px)] [background-size:28px_28px] opacity-40"></div>
    </div>

    <div class="max-w-[460px] w-full relative z-10 py-6">
        
        <!-- Main Liquid Glass Portal Container -->
        <div class="liquid-glass rounded-3xl p-7 sm:p-9 relative overflow-hidden">
            <!-- Organic Top Specular Glow -->
            <div class="absolute -top-24 left-1/2 -translate-x-1/2 w-64 h-24 bg-gradient-to-b from-indigo-400/40 via-cyan-400/20 to-transparent blur-2xl pointer-events-none"></div>
            
            <!-- Header Section -->
            <div class="text-center mb-8 relative">
                <!-- Liquid Badge / Avatar -->
                <div class="inline-flex items-center justify-center p-3.5 rounded-2xl liquid-pill text-indigo-400 mb-4 shadow-xl relative group">
                    <div class="absolute inset-0 rounded-2xl bg-gradient-to-r from-indigo-500 to-cyan-500 opacity-20 blur-md group-hover:opacity-40 transition-opacity"></div>
                    <svg class="w-8 h-8 relative z-10 text-cyan-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                    </svg>
                </div>

                <div class="flex items-center justify-center gap-2 text-[10px] font-mono uppercase tracking-[0.2em] text-cyan-300/90 mb-1.5 font-bold">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 shadow-[0_0_8px_#34d399] animate-pulse"></span>
                    <span>Direct Root Authentication</span>
                </div>

                <h1 class="text-2xl sm:text-3xl font-display font-extrabold text-white tracking-tight">
                    Master Administrator
                </h1>
                <p class="text-xs text-slate-300/80 mt-2 leading-relaxed">
                    Unrestricted ecosystem control &amp; live marketplace management.
                </p>
            </div>

            @if(session('error'))
                <div class="mb-5 p-3.5 rounded-2xl bg-rose-500/10 border border-rose-500/30 text-xs text-rose-200 flex items-start gap-2.5 backdrop-blur-md">
                    <svg class="w-4 h-4 text-rose-400 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>{{ session('error') }}</span>
                </div>
            @endif

            @if(session('info'))
                <div class="mb-5 p-3.5 rounded-2xl bg-indigo-500/10 border border-indigo-500/30 text-xs text-indigo-200 flex items-start gap-2.5 backdrop-blur-md">
                    <svg class="w-4 h-4 text-cyan-400 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>{{ session('info') }}</span>
                </div>
            @endif

            <!-- Credentials Form -->
            <form action="{{ route('admin.login.submit') }}" method="POST" class="space-y-4" x-data="{ showPass: false, submitting: false }" @submit="submitting = true">
                @csrf

                <div>
                    <label for="admin-email" class="block text-[11px] font-bold uppercase tracking-wider text-slate-300 mb-1.5">
                        Admin Identifier (Email / Username)
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.206"/></svg>
                        </div>
                        <input 
                            id="admin-email" 
                            name="email" 
                            type="text" 
                            required 
                            autofocus 
                            value="{{ old('email', 'crab.hashir@gmail.com') }}" 
                            placeholder="crab.hashir@gmail.com" 
                            class="liquid-input w-full pl-10 pr-4 py-3 rounded-xl text-white placeholder-slate-500 text-sm shadow-inner"
                        >
                    </div>
                    @error('email')
                        <p class="text-xs text-rose-400 mt-1.5 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <div class="flex justify-between items-center mb-1.5">
                        <label for="admin-password" class="block text-[11px] font-bold uppercase tracking-wider text-slate-300">
                            Master Key / Password
                        </label>
                        <button type="button" @click="showPass = !showPass" class="text-[11px] text-cyan-400 hover:text-cyan-300 font-medium transition-colors">
                            <span x-text="showPass ? 'Hide' : 'Reveal'"></span>
                        </button>
                    </div>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                        </div>
                        <input 
                            id="admin-password" 
                            name="password" 
                            :type="showPass ? 'text' : 'password'" 
                            required 
                            placeholder="Master password" 
                            class="liquid-input w-full pl-10 pr-10 py-3 rounded-xl text-white placeholder-slate-500 text-sm shadow-inner font-mono"
                        >
                    </div>
                    @error('password')
                        <p class="text-xs text-rose-400 mt-1.5 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                <!-- One-click bypass / Instant Root Access Indicator -->
                <div class="p-3 rounded-xl liquid-pill flex items-center gap-2.5">
                    <div class="w-2 h-2 rounded-full bg-cyan-400 animate-ping"></div>
                    <p class="text-[11px] text-slate-300 leading-snug">
                        Immediate session activation enabled for verified master administrator credentials.
                    </p>
                </div>

                <button 
                    type="submit" 
                    :disabled="submitting"
                    class="w-full mt-2 py-3.5 px-5 rounded-xl bg-gradient-to-r from-indigo-500 via-purple-600 to-cyan-500 hover:from-indigo-400 hover:via-purple-500 hover:to-cyan-400 text-white font-bold text-sm tracking-wide shadow-lg shadow-indigo-500/30 hover:shadow-indigo-500/50 active:scale-[0.98] transition-all flex items-center justify-center gap-2 relative overflow-hidden"
                >
                    <span x-show="!submitting" class="flex items-center gap-2 font-display">
                        <span>Unlock Console</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                    </span>
                    <span x-show="submitting" x-cloak class="flex items-center gap-2">
                        <svg class="animate-spin w-4 h-4 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                        <span>Authenticating...</span>
                    </span>
                </button>
            </form>

            <!-- Back to Public Website -->
            <div class="mt-6 pt-5 border-t border-white/10 text-center">
                <a href="{{ route('home') }}" class="text-xs text-slate-400 hover:text-white transition-colors inline-flex items-center gap-1.5">
                    <span>&larr;</span>
                    <span>Return to Marketplace Storefront</span>
                </a>
            </div>
        </div>

        <!-- Security Footer Note -->
        <p class="text-center text-[11px] text-slate-400/70 mt-6 font-mono tracking-wide">
            MarbleMandi Liquid Architecture • Master Security Guard
        </p>
    </div>

    <!-- Active CSRF Keep-Alive Heartbeat -->
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
