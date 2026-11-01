<x-layouts.public title="Login">
    <div class="min-h-[calc(100vh-16rem)] flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full space-y-8 glass-panel p-8">
            <div>
                <h2 class="mt-2 text-center text-3xl font-extrabold text-brand-text font-serif">
                    Welcome back
                </h2>
                <p class="mt-2 text-center text-sm text-brand-muted">
                    Or
                    <a href="{{ route('register') }}" class="font-medium text-brand-sage hover:text-brand-sage/80 transition-colors">
                        create a new account
                    </a>
                </p>
            </div>
            
            @if(session('info'))
                <div class="bg-blue-50 border-l-4 border-blue-400 p-4">
                    <div class="flex">
                        <div class="ml-3">
                            <p class="text-sm text-blue-700">
                                {{ session('info') }}
                            </p>
                        </div>
                    </div>
                </div>
            @endif

            <form class="mt-8 space-y-6" action="{{ route('login') }}" method="POST">
                @csrf
                <div class="space-y-4">
                    <div>
                        <label for="login" class="block text-sm font-medium text-brand-text">Email address, Mobile or Username</label>
                        <input id="login" name="login" type="text" autocomplete="username" required class="mt-1 appearance-none relative block w-full px-3 py-2 border border-brand-surface placeholder-gray-400 text-brand-text rounded-md focus:outline-none focus:ring-brand-sage focus:border-brand-sage focus:z-10 sm:text-sm bg-white/50" placeholder="Enter email, 10-digit mobile or username" value="{{ old('login') }}">
                        @error('login')
                            <p class="mt-1 text-sm text-brand-error">{{ $message }}</p>
                        @enderror
                    </div>
                    <div x-data="{ showPass: false }">
                        <div class="flex items-center justify-between">
                            <label for="password" class="block text-sm font-medium text-brand-text">Password</label>
                            <button type="button" @click="showPass = !showPass" class="text-xs text-brand-sage hover:text-brand-sage/80 font-medium focus:outline-none">
                                <span x-text="showPass ? 'Hide' : 'Show'"></span>
                            </button>
                        </div>
                        <div class="relative mt-1">
                            <input id="password" name="password" :type="showPass ? 'text' : 'password'" autocomplete="current-password" required class="appearance-none block w-full px-3 py-2 pr-10 border border-brand-surface placeholder-gray-400 text-brand-text rounded-md focus:outline-none focus:ring-brand-sage focus:border-brand-sage sm:text-sm bg-white/50" placeholder="Password">
                            <button type="button" @click="showPass = !showPass" aria-label="Toggle password visibility" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600 focus:outline-none" tabindex="-1">
                                <!-- Eye (Show) -->
                                <svg x-show="!showPass" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                <!-- Eye Slash (Hide) -->
                                <svg x-show="showPass" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="display: none;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18"/></svg>
                            </button>
                        </div>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-brand-text mb-2">Login as</label>
                        <div class="flex space-x-4">
                            <label class="flex items-center">
                                <input type="radio" name="role" value="customer" class="form-radio h-4 w-4 text-brand-sage border-gray-300 focus:ring-brand-sage" {{ old('role', 'customer') === 'customer' ? 'checked' : '' }}>
                                <span class="ml-2 text-sm text-brand-text">Customer</span>
                            </label>
                            <label class="flex items-center">
                                <input type="radio" name="role" value="vendor" class="form-radio h-4 w-4 text-brand-sage border-gray-300 focus:ring-brand-sage" {{ old('role') === 'vendor' ? 'checked' : '' }}>
                                <span class="ml-2 text-sm text-brand-text">Vendor</span>
                            </label>
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <input id="remember" name="remember" type="checkbox" checked class="h-4 w-4 text-brand-sage focus:ring-brand-sage border-gray-300 rounded">
                        <label for="remember" class="ml-2 block text-sm text-brand-muted">
                            Remember me
                        </label>
                    </div>

                    <div class="text-sm">
                        <a href="#" class="font-medium text-brand-sage hover:text-brand-sage/80 transition-colors">
                            Forgot password?
                        </a>
                    </div>
                </div>

                <div>
                    <button type="submit" class="w-full flex justify-center py-3 px-4 border border-transparent rounded-full shadow-sm text-sm font-medium text-white bg-brand-sage hover:bg-brand-sage/90 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-brand-sage transition-all">
                        Sign in
                    </button>
                </div>
            </form>
            
            <div class="mt-6">
                <div class="relative">
                    <div class="absolute inset-0 flex items-center">
                        <div class="w-full border-t border-gray-300"></div>
                    </div>
                    <div class="relative flex justify-center text-sm">
                        <span class="px-2 bg-transparent text-brand-muted">Or continue with</span>
                    </div>
                </div>

                <div class="mt-6">
                    <a href="{{ route('auth.google') }}" class="w-full inline-flex justify-center py-2 px-4 border border-gray-300 rounded-full shadow-sm bg-white text-sm font-medium text-gray-500 hover:bg-gray-50 transition-colors">
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
        </div>
    </div>
</x-layouts.public>
