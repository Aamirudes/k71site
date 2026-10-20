<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-slate-950">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Master Admin 2FA Verification | Restricted Access</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=Cinzel:wght@600;700&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        [x-cloak] { display: none !important; }
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
    </style>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="min-h-full flex items-center justify-center p-4 sm:p-6 bg-slate-950 text-slate-100 relative overflow-hidden">

    <!-- Ambient Mesh Glow Background -->
    <div class="absolute inset-0 pointer-events-none overflow-hidden">
        <div class="absolute -top-[20%] -left-[10%] w-[50%] h-[50%] bg-amber-500/10 rounded-full blur-[120px]"></div>
        <div class="absolute -bottom-[20%] -right-[10%] w-[50%] h-[50%] bg-emerald-500/10 rounded-full blur-[140px]"></div>
        <div class="absolute inset-0 bg-[radial-gradient(#334155_1px,transparent_1px)] [background-size:24px_24px] opacity-25"></div>
    </div>

    <div class="max-w-md w-full relative z-10 py-8">
        
        <div class="bg-slate-900/90 backdrop-blur-xl rounded-3xl p-8 sm:p-10 border border-slate-800 shadow-2xl relative overflow-hidden" 
             x-data="{
                cooldown: 60,
                timer: null,
                sending: false,
                startCooldown() {
                    this.cooldown = 60;
                    if (this.timer) clearInterval(this.timer);
                    this.timer = setInterval(() => {
                        if (this.cooldown > 0) {
                            this.cooldown--;
                        } else {
                            clearInterval(this.timer);
                        }
                    }, 1000);
                },
                resend() {
                    if (this.cooldown > 0 || this.sending) return;
                    this.sending = true;
                    fetch('{{ route('admin.otp.resend') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        }
                    })
                    .then(r => r.json())
                    .then(data => {
                        this.sending = false;
                        if (data.error) {
                            alert(data.error);
                        } else {
                            alert(data.message || 'Verification code resent!');
                            this.startCooldown();
                        }
                    })
                    .catch(err => {
                        this.sending = false;
                        alert('Failed to resend verification code. Please try again.');
                    });
                }
             }"
             x-init="startCooldown()"
        >
            <!-- Subtle Top Amber Accent Line -->
            <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-amber-500 via-amber-400 to-amber-600"></div>

            <!-- Security Header -->
            <div class="text-center mb-8">
                <div class="inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-slate-950 text-amber-400 mb-4 shadow-inner border border-slate-800">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                </div>

                <div class="flex items-center justify-center gap-1.5 text-[11px] font-mono uppercase tracking-widest text-amber-400 mb-1 font-semibold">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                    <span>Step 2 of 2 • Multi-Factor Verification</span>
                </div>

                <h1 class="text-2xl sm:text-3xl font-serif font-bold text-white tracking-tight">
                    Enter Security Code
                </h1>
                <p class="text-xs text-slate-400 mt-2 leading-relaxed">
                    A 6-digit One-Time Password (OTP) has been dispatched to your authorized administrator inbox.
                </p>
            </div>

            @if(session('info'))
                <div class="mb-6 p-3.5 rounded-xl bg-slate-800/90 border border-slate-700 text-xs text-slate-200 flex items-start gap-2.5">
                    <svg class="w-4 h-4 text-amber-400 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>{{ session('info') }}</span>
                </div>
            @endif

            @if(session('error'))
                <div class="mb-6 p-3.5 rounded-xl bg-red-950/80 border border-red-800 text-xs text-red-200 flex items-start gap-2.5">
                    <svg class="w-4 h-4 text-red-400 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>{{ session('error') }}</span>
                </div>
            @endif

            @if(session('test_otp_code'))
                <div class="mb-6 p-4 rounded-xl bg-amber-500/10 border border-amber-500/40 text-center">
                    <p class="text-[11px] font-mono uppercase tracking-wider text-amber-400 font-semibold mb-1">Testing Mode Verification Code</p>
                    <p class="text-3xl font-mono font-extrabold text-amber-400 tracking-[0.25em]">{{ session('test_otp_code') }}</p>
                </div>
            @endif

            <!-- OTP Verification Form -->
            <form action="{{ route('admin.otp.verify') }}" method="POST" class="space-y-6">
                @csrf

                <div>
                    <label for="code" class="block text-center text-xs font-bold uppercase tracking-wider text-slate-300 mb-2">
                        6-Digit Security Code
                    </label>
                    <input 
                        id="code" 
                        name="code" 
                        type="text" 
                        inputmode="numeric" 
                        pattern="[0-9]{6}" 
                        maxlength="6" 
                        required 
                        autofocus 
                        placeholder="••••••" 
                        class="w-full text-center tracking-[0.5em] text-2xl font-mono py-3.5 px-4 rounded-xl border border-slate-700 bg-slate-950/70 text-white placeholder-slate-600 focus:ring-2 focus:ring-amber-500 focus:border-amber-500 shadow-xs"
                    >
                    @error('code')
                        <p class="text-xs text-red-400 text-center mt-2 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                <button 
                    type="submit" 
                    class="w-full py-3.5 px-4 rounded-xl bg-amber-500 hover:bg-amber-400 text-slate-950 font-bold text-sm active:scale-[0.99] transition-all shadow-lg shadow-amber-500/20 flex items-center justify-center gap-2"
                >
                    <svg class="w-4 h-4 text-slate-950" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                    <span>Verify &amp; Enter Master Console</span>
                </button>
            </form>

            <!-- Resend & Back Actions -->
            <div class="mt-6 pt-5 border-t border-slate-800 flex items-center justify-between text-xs">
                <a href="{{ route('admin.login') }}" class="text-slate-400 hover:text-white transition-colors">
                    &larr; Re-enter credentials
                </a>

                <button 
                    type="button" 
                    @click="resend()" 
                    :disabled="cooldown > 0 || sending" 
                    :class="cooldown > 0 || sending ? 'text-slate-600 cursor-not-allowed' : 'text-amber-400 hover:text-amber-300 font-bold cursor-pointer'"
                    class="transition-colors"
                >
                    <span x-show="cooldown > 0">Resend in <span x-text="cooldown"></span>s</span>
                    <span x-show="cooldown === 0 && !sending">Resend Code &rarr;</span>
                    <span x-show="sending">Dispatching...</span>
                </button>
            </div>
        </div>

        <!-- Security Footer Note -->
        <p class="text-center text-[11px] text-slate-500 mt-6 font-mono">
            Two-Factor Authentication Enforced on Every Admin Session
        </p>
    </div>

</body>
</html>
