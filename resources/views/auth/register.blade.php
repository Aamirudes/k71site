<x-layouts.public title="Create Account" :fullWidth="true">
    <div class="grail-hero-band min-h-[calc(100vh-5rem)] flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8 relative overflow-hidden">
        <!-- Ambient Obsidian Orchid & Gold Glow -->
        <div class="absolute inset-0 pointer-events-none">
            <div class="absolute -top-24 -right-24 w-96 h-96 bg-[#ECD2F3]/12 rounded-full blur-3xl"></div>
            <div class="absolute -bottom-24 -left-24 w-96 h-96 bg-[#C9A24A]/10 rounded-full blur-3xl"></div>
        </div>

        <div class="max-w-md w-full space-y-6 bg-[#FAFAF8] text-[#1A1018] p-7 sm:p-9 rounded-[28px] border border-white/15 shadow-[0_32px_90px_rgba(0,0,0,0.65)] relative z-10">
            <div class="text-center">
                <span class="grail-eyebrow block mb-2">Join K71 Marketplace</span>
                <h2 class="grail-display text-3xl sm:text-4xl font-bold text-[#1A1018] tracking-tight">
                    Create an account
                </h2>
                <p class="mt-2 text-sm text-[#7A6B78]">
                    Already have an account?
                    <a href="{{ route('login') }}" class="font-semibold text-[#623562] hover:text-[#1A1018] transition-colors underline decoration-[#ECD2F3] underline-offset-4">
                        Sign in
                    </a>
                </p>
            </div>
            
            <form class="mt-6 space-y-5" action="{{ route('register') }}" method="POST">
                @csrf
                <div class="space-y-4">
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label for="first_name" class="block text-xs font-bold uppercase tracking-wider text-[#1A1018] mb-1">First Name</label>
                            <input id="first_name" name="first_name" type="text" required class="grail-input-light appearance-none relative block w-full px-3.5 py-2.5 rounded-xl sm:text-sm" value="{{ old('first_name') }}">
                            @error('first_name')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label for="last_name" class="block text-xs font-bold uppercase tracking-wider text-[#1A1018] mb-1">Last Name</label>
                            <input id="last_name" name="last_name" type="text" required class="grail-input-light appearance-none relative block w-full px-3.5 py-2.5 rounded-xl sm:text-sm" value="{{ old('last_name') }}">
                            @error('last_name')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                        </div>
                    </div>
                    
                    <div>
                        <label for="username" class="block text-xs font-bold uppercase tracking-wider text-[#1A1018] mb-1">Username</label>
                        <input id="username" name="username" type="text" required class="grail-input-light appearance-none relative block w-full px-3.5 py-2.5 rounded-xl sm:text-sm" value="{{ old('username') }}">
                        @error('username')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="email" class="block text-xs font-bold uppercase tracking-wider text-[#1A1018] mb-1">Email address</label>
                        <input id="email" name="email" type="email" autocomplete="email" required class="grail-input-light appearance-none relative block w-full px-3.5 py-2.5 rounded-xl sm:text-sm" value="{{ old('email') }}">
                        @error('email')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                    </div>

                    <div x-data="{ showPass: false }">
                        <div class="flex items-center justify-between mb-1">
                            <label for="password" class="block text-xs font-bold uppercase tracking-wider text-[#1A1018]">Password</label>
                            <button type="button" @click="showPass = !showPass" class="text-xs text-[#623562] hover:text-[#1A1018] font-semibold focus:outline-none cursor-pointer">
                                <span x-text="showPass ? 'Hide' : 'Show'"></span>
                            </button>
                        </div>
                        <div class="relative">
                            <input id="password" name="password" :type="showPass ? 'text' : 'password'" required class="grail-input-light appearance-none block w-full px-3.5 py-2.5 pr-10 rounded-xl sm:text-sm" placeholder="••••••••">
                            <button type="button" @click="showPass = !showPass" aria-label="Toggle password visibility" class="absolute inset-y-0 right-0 pr-3 flex items-center text-[#7A6B78] hover:text-[#1A1018] focus:outline-none cursor-pointer" tabindex="-1">
                                <svg x-show="!showPass" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                <svg x-show="showPass" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="display: none;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18"/></svg>
                            </button>
                        </div>
                        @error('password')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                    </div>
                </div>

                <div>
                    <button type="submit" class="grail-btn-obsidian w-full py-3.5 px-4 text-sm cursor-pointer">
                        Create Account
                    </button>
                </div>
            </form>
            
            <div class="mt-6">
                <div class="relative">
                    <div class="absolute inset-0 flex items-center">
                        <div class="w-full border-t border-black/10"></div>
                    </div>
                    <div class="relative flex justify-center text-xs uppercase tracking-wider font-semibold">
                        <span class="px-3 bg-[#FAFAF8] text-[#7A6B78]">Or sign up with</span>
                    </div>
                </div>

                <div class="mt-5">
                    <a href="{{ route('auth.google') }}" class="w-full inline-flex items-center justify-center py-2.5 px-4 border border-black/15 rounded-full shadow-2xs bg-white text-sm font-semibold text-[#1A1018] hover:bg-[#F4F1EC] transition-colors">
                        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/>
                            <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/>
                            <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#FBBC05"/>
                            <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/>
                        </svg>
                        <span class="ml-2">Google</span>
                    </a>
                </div>
            </div>
            
            <p class="mt-4 text-center text-xs text-[#7A6B78]">
                Want to sell on {{ config('marketplace.name') }}? <br/>
                <a href="{{ route('vendor.register') }}" class="font-semibold text-[#623562] hover:underline">Apply to be a vendor</a>
            </p>
        </div>
    </div>
</x-layouts.public>
