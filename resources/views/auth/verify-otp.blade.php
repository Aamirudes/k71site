<x-layouts.app title="Verify Email">
    <div class="min-h-[calc(100vh-14rem)] flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full space-y-6 bg-[#FAFAF8] text-[#1A1018] p-8 rounded-[28px] border border-white/15 shadow-[0_32px_90px_rgba(0,0,0,0.65)]" x-data="otpComponent()">
            <div class="text-center">
                <span class="grail-eyebrow block mb-2">Email Verification</span>
                <h2 class="grail-display text-3xl font-bold text-[#1A1018]">
                    Check your email
                </h2>
                <p class="mt-2 text-sm text-[#7A6B78]">
                    We've sent a 6-digit verification code to<br>
                    <span class="font-semibold text-[#1A1018]">{{ $email }}</span>
                </p>
            </div>

            @if(session('info'))
                <div class="bg-[#F8EFFA] border-l-4 border-[#623562] p-4 rounded-xl">
                    <p class="text-sm text-[#1A1018]">{{ session('info') }}</p>
                </div>
            @endif

            @if(session('error'))
                <div class="bg-rose-50 border-l-4 border-rose-500 p-4 mt-4 rounded-xl">
                    <p class="text-sm text-rose-700">{{ session('error') }}</p>
                </div>
            @endif

            @if(session('test_otp_code'))
                <div class="bg-emerald-50 border-l-4 border-emerald-500 p-4 mt-4 rounded-xl">
                    <p class="text-sm font-bold text-emerald-700">TESTING MODE CODE: {{ session('test_otp_code') }}</p>
                    <p class="text-xs text-emerald-600">Enter this code below to proceed.</p>
                </div>
            @endif

            @if(app()->environment('local'))
                <div id="dev-otp-container" class="bg-[#161616] text-[#EFE9F2] border-l-4 border-[#ECD2F3] p-4 mt-4 rounded-xl" x-show="devOtpCode" style="{{ session('dev_otp_code') ? '' : 'display: none;' }}">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs font-bold text-[#ECD2F3]">DEV MODE OTP CODE: <span id="dev-otp-span" class="text-xl font-mono tracking-widest text-white ml-2" x-text="devOtpCode">{{ session('dev_otp_code') }}</span></p>
                            <p class="text-[11px] text-white/60 mt-1">This code is displayed automatically in local dev mode.</p>
                        </div>
                        <button type="button" @click="fillCode(devOtpCode)" class="text-xs font-bold bg-[#ECD2F3] text-[#1A1018] px-3 py-1.5 rounded-full hover:bg-white transition cursor-pointer">
                            Fill Code
                        </button>
                    </div>
                </div>
            @endif

            <form class="mt-6 space-y-5" action="{{ route('otp.verify') }}" method="POST">
                @csrf
                <div>
                    <label for="code" class="sr-only">Verification Code</label>
                    <input id="code" name="code" type="text" required maxlength="6" pattern="[0-9]{6}" class="grail-input-light mt-1 appearance-none relative block w-full px-3 py-3 rounded-xl text-center text-2xl font-mono tracking-[0.5em]" placeholder="------" value="{{ old('code') }}" oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                    @error('code')
                        <p class="mt-2 text-sm text-center text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <button type="submit" class="grail-btn-obsidian w-full py-3.5 px-4 text-sm cursor-pointer">
                        Verify Email
                    </button>
                </div>
            </form>
            
            <div class="text-center mt-4">
                <button type="button" @click="resendCode" :disabled="countdown > 0 || isResending" class="text-sm font-semibold text-[#623562] hover:text-[#1A1018] disabled:text-gray-400 disabled:cursor-not-allowed transition-colors cursor-pointer">
                    <span x-show="countdown > 0">Resend code in <span x-text="countdown"></span>s</span>
                    <span x-show="countdown === 0 && !isResending">Didn't receive the code? Resend</span>
                    <span x-show="isResending">Sending...</span>
                </button>
                <p x-show="resendMessage" x-text="resendMessage" class="mt-2 text-xs text-emerald-700" style="display: none;"></p>
                <p x-show="resendError" x-text="resendError" class="mt-2 text-xs text-rose-600" style="display: none;"></p>
            </div>
            
            <div class="text-center mt-2">
                <a href="{{ route('login') }}" class="text-sm text-[#7A6B78] hover:text-[#1A1018] transition-colors">Back to login</a>
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
