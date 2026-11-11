<x-layouts.public title="Set New Password" :fullWidth="true">
    <div class="grail-hero-band min-h-[calc(100vh-5rem)] flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8 relative overflow-hidden">
        <div class="max-w-md w-full space-y-6 bg-[#FAFAF8] text-[#1A1018] p-8 rounded-[28px] shadow-[0_32px_90px_rgba(0,0,0,0.65)] border border-white/15 relative z-10">
            <div class="text-center">
                <span class="grail-eyebrow block mb-2">Security Update</span>
                <h2 class="grail-display text-3xl font-bold text-[#1A1018]">
                    Set New Password
                </h2>
                <p class="mt-2 text-sm text-[#7A6B78]">
                    Enter your email and your new password below.
                </p>
            </div>

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

            <form class="mt-6 space-y-5" action="{{ route('password.update') }}" method="POST">
                @csrf
                <input type="hidden" name="token" value="{{ $token }}">

                <div class="space-y-1.5">
                    <label for="email" class="block text-xs font-bold text-[#1A1018] uppercase tracking-wider">
                        Email Address
                    </label>
                    <input id="email" 
                           name="email" 
                           type="email" 
                           autocomplete="email" 
                           required 
                           value="{{ old('email', $email) }}"
                           class="grail-input-light w-full px-4 py-3 rounded-xl text-sm font-medium" 
                           placeholder="you@example.com">
                </div>

                <div class="space-y-1.5">
                    <label for="password" class="block text-xs font-bold text-[#1A1018] uppercase tracking-wider">
                        New Password
                    </label>
                    <input id="password" 
                           name="password" 
                           type="password" 
                           autocomplete="new-password" 
                           required 
                           minlength="8"
                           class="grail-input-light w-full px-4 py-3 rounded-xl text-sm font-medium" 
                           placeholder="At least 8 characters">
                </div>

                <div class="space-y-1.5">
                    <label for="password_confirmation" class="block text-xs font-bold text-[#1A1018] uppercase tracking-wider">
                        Confirm New Password
                    </label>
                    <input id="password_confirmation" 
                           name="password_confirmation" 
                           type="password" 
                           autocomplete="new-password" 
                           required 
                           minlength="8"
                           class="grail-input-light w-full px-4 py-3 rounded-xl text-sm font-medium" 
                           placeholder="Confirm new password">
                </div>

                <div class="pt-2">
                    <button type="submit" class="grail-btn-obsidian w-full py-3.5 px-4 text-sm cursor-pointer">
                        Update Password & Log In
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
