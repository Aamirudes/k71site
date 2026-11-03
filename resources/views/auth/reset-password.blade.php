<x-layouts.public title="Set New Password">
    <div class="min-h-[calc(100vh-16rem)] flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full space-y-8 glass-panel p-8 bg-white/80 backdrop-blur-md rounded-2xl shadow-xl border border-slate-200/60">
            <div>
                <h2 class="mt-2 text-center text-3xl font-extrabold text-brand-text font-serif">
                    Set New Password
                </h2>
                <p class="mt-2 text-center text-sm text-brand-muted">
                    Enter your email and your new password below.
                </p>
            </div>

            @if($errors->any())
                <div class="bg-red-50 border-l-4 border-red-500 p-4 rounded-md">
                    <div class="flex">
                        <div class="ml-3">
                            @foreach($errors->all() as $error)
                                <p class="text-sm font-medium text-red-800">{{ $error }}</p>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif

            <form class="mt-6 space-y-5" action="{{ route('password.update') }}" method="POST">
                @csrf
                <input type="hidden" name="token" value="{{ $token }}">

                <div class="space-y-1.5">
                    <label for="email" class="block text-xs font-bold text-slate-700 uppercase tracking-wider">
                        Email Address
                    </label>
                    <input id="email" 
                           name="email" 
                           type="email" 
                           autocomplete="email" 
                           required 
                           value="{{ old('email', $email) }}"
                           class="w-full px-4 py-3 rounded-xl border border-slate-200 bg-white text-sm font-medium text-slate-900 focus:border-slate-900 focus:ring-2 focus:ring-slate-900/10 outline-none transition-all placeholder:text-slate-400" 
                           placeholder="you@example.com">
                </div>

                <div class="space-y-1.5">
                    <label for="password" class="block text-xs font-bold text-slate-700 uppercase tracking-wider">
                        New Password
                    </label>
                    <input id="password" 
                           name="password" 
                           type="password" 
                           autocomplete="new-password" 
                           required 
                           minlength="8"
                           class="w-full px-4 py-3 rounded-xl border border-slate-200 bg-white text-sm font-medium text-slate-900 focus:border-slate-900 focus:ring-2 focus:ring-slate-900/10 outline-none transition-all placeholder:text-slate-400" 
                           placeholder="At least 8 characters">
                </div>

                <div class="space-y-1.5">
                    <label for="password_confirmation" class="block text-xs font-bold text-slate-700 uppercase tracking-wider">
                        Confirm New Password
                    </label>
                    <input id="password_confirmation" 
                           name="password_confirmation" 
                           type="password" 
                           autocomplete="new-password" 
                           required 
                           minlength="8"
                           class="w-full px-4 py-3 rounded-xl border border-slate-200 bg-white text-sm font-medium text-slate-900 focus:border-slate-900 focus:ring-2 focus:ring-slate-900/10 outline-none transition-all placeholder:text-slate-400" 
                           placeholder="Confirm new password">
                </div>

                <div class="pt-2">
                    <button type="submit" class="w-full flex justify-center py-3.5 px-4 border border-transparent rounded-xl shadow-sm text-sm font-bold text-white bg-slate-900 hover:bg-black focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-slate-900 transition-all cursor-pointer">
                        Update Password & Log In
                    </button>
                </div>
            </form>

            <div class="text-center pt-2">
                <a href="{{ route('login') }}" class="text-xs font-semibold text-slate-600 hover:text-slate-900 transition-colors">
                    ← Back to Login
                </a>
            </div>
        </div>
    </div>
</x-layouts.public>
