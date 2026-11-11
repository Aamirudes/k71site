<x-layouts.public title="Forgot Password" :fullWidth="true">
    <div class="grail-hero-band min-h-[calc(100vh-5rem)] flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8 relative overflow-hidden">
        <div class="max-w-md w-full space-y-6 bg-[#FAFAF8] text-[#1A1018] p-8 rounded-[28px] shadow-[0_32px_90px_rgba(0,0,0,0.65)] border border-white/15 relative z-10">
            <div class="text-center">
                <span class="grail-eyebrow block mb-2">Account Recovery</span>
                <h2 class="grail-display text-3xl font-bold text-[#1A1018]">
                    Reset Password
                </h2>
                <p class="mt-2 text-sm text-[#7A6B78]">
                    Enter your account's verified email and we'll send you a secure recovery link.
                </p>
            </div>
            
            @if(session('status'))
                <div class="bg-emerald-50 border-l-4 border-emerald-500 p-4 rounded-xl">
                    <div class="flex">
                        <div class="ml-1">
                            <p class="text-sm font-medium text-emerald-800">
                                {{ session('status') }}
                            </p>
                        </div>
                    </div>
                </div>
            @endif

            @if($errors->any())
                <div class="bg-rose-50 border-l-4 border-rose-500 p-4 rounded-xl">
                    <div class="flex">
                        <div class="ml-1">
                            @foreach($errors->all() as $error)
                                <p class="text-sm font-medium text-rose-800">{{ $error }}</p>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif

            <form class="mt-6 space-y-5" action="{{ route('password.email') }}" method="POST">
                @csrf
                <div class="space-y-2">
                    <label for="email" class="block text-xs font-bold text-[#1A1018] uppercase tracking-wider">
                        Registered Email Address
                    </label>
                    <input id="email" 
                           name="email" 
                           type="email" 
                           autocomplete="email" 
                           required 
                           value="{{ old('email') }}"
                           class="grail-input-light w-full px-4 py-3 rounded-xl text-sm font-medium" 
                           placeholder="you@example.com">
                </div>

                <div>
                    <button type="submit" class="grail-btn-obsidian w-full py-3.5 px-4 text-sm cursor-pointer">
                        Send Recovery Link
                    </button>
                </div>
            </form>

            <div class="text-center pt-2">
                <a href="{{ route('login') }}" class="text-xs font-semibold text-[#623562] hover:text-[#1A1018] transition-colors">
                    ← Back to Login
                </a>
            </div>
        </div>
    </div>
</x-layouts.public>
