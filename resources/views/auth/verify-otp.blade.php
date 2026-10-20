<x-layouts.app title="Verify Email">
    <div class="min-h-[calc(100vh-16rem)] flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full space-y-8 glass-panel p-8" x-data="otpComponent()">
            <div>
                <h2 class="mt-2 text-center text-3xl font-extrabold text-brand-text font-serif">
                    Check your email
                </h2>
                <p class="mt-2 text-center text-sm text-brand-muted">
                    We've sent a 6-digit verification code to<br>
                    <span class="font-medium text-brand-text">{{ $email }}</span>
                </p>
            </div>

            @if(session('info'))
                <div class="bg-blue-50 border-l-4 border-blue-400 p-4">
                    <p class="text-sm text-blue-700">{{ session('info') }}</p>
                </div>
            @endif

            @if(session('error'))
                <div class="bg-red-50 border-l-4 border-red-400 p-4 mt-4">
                    <p class="text-sm text-red-700">{{ session('error') }}</p>
                </div>
            @endif

            @if(session('test_otp_code'))
                <div class="bg-green-50 border-l-4 border-green-400 p-4 mt-4">
                    <p class="text-sm font-bold text-green-700">TESTING MODE CODE: {{ session('test_otp_code') }}</p>
                    <p class="text-xs text-green-600">Enter this code below to proceed.</p>
                </div>
            @endif

            @if(app()->environment('local'))
                <div id="dev-otp-container" class="bg-indigo-50 border-l-4 border-indigo-400 p-4 mt-4" x-show="devOtpCode" style="{{ session('dev_otp_code') ? '' : 'display: none;' }}">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-bold text-indigo-700">DEV MODE OTP CODE: <span id="dev-otp-span" class="text-xl font-mono tracking-widest text-indigo-900 ml-2" x-text="devOtpCode">{{ session('dev_otp_code') }}</span></p>
                            <p class="text-xs text-indigo-600 mt-1">This code is displayed automatically in local dev mode.</p>
                        </div>
                        <button type="button" @click="fillCode(devOtpCode)" class="text-xs font-semibold bg-indigo-600 text-white px-3 py-1.5 rounded-lg hover:bg-indigo-700 transition">
                            Fill Code
                        </button>
                    </div>
                </div>
            @endif

            <form class="mt-8 space-y-6" action="{{ route('otp.verify') }}" method="POST">
                @csrf
                <div>
                    <label for="code" class="sr-only">Verification Code</label>
                    <input id="code" name="code" type="text" required maxlength="6" pattern="[0-9]{6}" class="mt-1 appearance-none relative block w-full px-3 py-3 border border-brand-surface placeholder-gray-400 text-brand-text rounded-md focus:outline-none focus:ring-brand-sage focus:border-brand-sage focus:z-10 text-center text-2xl tracking-[0.5em] bg-white/50" placeholder="------" value="{{ old('code') }}" oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                    @error('code')
                        <p class="mt-2 text-sm text-center text-brand-error">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <button type="submit" class="w-full flex justify-center py-3 px-4 border border-transparent rounded-full shadow-sm text-sm font-medium text-white bg-brand-sage hover:bg-brand-sage/90 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-brand-sage transition-all">
                        Verify Email
                    </button>
                </div>
            </form>
            
            <div class="text-center mt-4">
                <button type="button" @click="resendCode" :disabled="countdown > 0 || isResending" class="text-sm font-medium text-brand-sage hover:text-brand-sage/80 disabled:text-gray-400 disabled:cursor-not-allowed transition-colors">
                    <span x-show="countdown > 0">Resend code in <span x-text="countdown"></span>s</span>
                    <span x-show="countdown === 0 && !isResending">Didn't receive the code? Resend</span>
                    <span x-show="isResending">Sending...</span>
                </button>
                <p x-show="resendMessage" x-text="resendMessage" class="mt-2 text-xs text-green-600" style="display: none;"></p>
                <p x-show="resendError" x-text="resendError" class="mt-2 text-xs text-brand-error" style="display: none;"></p>
            </div>
            
            <div class="text-center mt-2">
                <a href="{{ route('login') }}" class="text-sm text-brand-muted hover:text-brand-text transition-colors">Back to login</a>
            </div>
        </div>
    </div>
    
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('otpComponent', () => ({
                countdown: 0,
                isResending: false,
                resendMessage: '',
                resendError: '',
                devOtpCode: '{{ session("dev_otp_code") }}',
                
                fillCode(val) {
                    if (!val) return;
                    const input = document.getElementById('code');
                    if (input) {
                        input.value = val;
                        input.focus();
                    }
                },

                startCountdown() {
                    this.countdown = {{ app()->environment('local') ? 5 : config('marketplace.otp.resend_cooldown_seconds', 60) }};
                    const timer = setInterval(() => {
                        this.countdown--;
                        if (this.countdown <= 0) {
                            clearInterval(timer);
                        }
                    }, 1000);
                },
                
                async resendCode() {
                    if (this.countdown > 0 || this.isResending) return;
                    
                    this.isResending = true;
                    this.resendMessage = '';
                    this.resendError = '';
                    
                    try {
                        const response = await fetch('{{ route('otp.resend') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            }
                        });
                        
                        const data = await response.json();
                        
                        if (response.ok) {
                            this.resendMessage = 'Code sent successfully!';
                            this.startCountdown();
                            if (data.dev_otp_code) {
                                this.devOtpCode = data.dev_otp_code;
                            }
                        } else {
                            this.resendError = data.error || 'Failed to resend code.';
                        }
                    } catch (error) {
                        this.resendError = 'An error occurred. Please try again.';
                    } finally {
                        this.isResending = false;
                    }
                }
            }));
        });
    </script>
</x-layouts.app>
