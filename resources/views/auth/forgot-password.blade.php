<x-layouts.public title="Forgot Password">
    <div class="min-h-[calc(100vh-16rem)] flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full space-y-8 glass-panel p-8 bg-white/80 backdrop-blur-md rounded-2xl shadow-xl border border-slate-200/60">
            <div>
                <h2 class="mt-2 text-center text-3xl font-extrabold text-brand-text font-serif">
                    Reset Password
                </h2>
                <p class="mt-2 text-center text-sm text-brand-muted">
                    Enter your account's verified email and we'll send you a secure recovery link.
                </p>
            </div>
            
            @if(session('status'))
                <div class="bg-emerald-50 border-l-4 border-emerald-500 p-4 rounded-md">
                    <div class="flex">
                        <div class="ml-3">
                            <p class="text-sm font-medium text-emerald-800">
                                {{ session('status') }}
                            </p>
                        </div>
                    </div>
                </div>
            @endif

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

            <form class="mt-6 space-y-6" action="{{ route('password.email') }}" method="POST">
                @csrf
                <div class="space-y-2">
                    <label for="email" class="block text-xs font-bold text-slate-700 uppercase tracking-wider">
                        Registered Email Address
                    </label>
                    <input id="email" 
                           name="email" 
                           type="email" 
                           autocomplete="email" 
                           required 
                           value="{{ old('email') }}"
                           class="w-full px-4 py-3 rounded-xl border border-slate-200 bg-white text-sm font-medium text-slate-900 focus:border-slate-900 focus:ring-2 focus:ring-slate-900/10 outline-none transition-all placeholder:text-slate-400" 
                           placeholder="you@example.com">
                </div>

                <div>
                    <button type="submit" class="w-full flex justify-center py-3.5 px-4 border border-transparent rounded-xl shadow-sm text-sm font-bold text-white bg-slate-900 hover:bg-black focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-slate-900 transition-all cursor-pointer">
                        Send Recovery Link
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
