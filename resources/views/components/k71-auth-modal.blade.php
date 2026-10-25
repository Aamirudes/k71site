<!-- Global K71 Apple-Grade Glassmorphism Authentication Modal (Non-Negotiable B2B & Retail) -->
<div x-data="k71AuthModal()" 
     x-cloak
     @open-auth-modal.window="open($event.detail)"
     @keydown.escape.window="close()"
     class="relative z-[110]">

    <!-- Frosted Blur Backdrop -->
    <div x-show="isOpen" 
         style="display: none;"
         x-transition:enter="transition-opacity ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition-opacity ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         @click="close()"
         class="fixed inset-0 bg-slate-950/50 backdrop-blur-md"></div>

    <!-- Modal Dialog Window -->
    <div x-show="isOpen"
         style="display: none;"
         x-transition:enter="transition ease-out duration-300 transform"
         x-transition:enter-start="opacity-0 translate-y-6 sm:translate-y-0 sm:scale-95"
         x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
         x-transition:leave="transition ease-in duration-200 transform"
         x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
         x-transition:leave-end="opacity-0 translate-y-6 sm:translate-y-0 sm:scale-95"
         class="fixed inset-0 z-10 overflow-y-auto flex items-center justify-center p-4 sm:p-6">

        <div @click.outside="close()"
             class="w-full max-w-md bg-white/95 backdrop-blur-2xl rounded-3xl border border-white/60 shadow-[0_25px_60px_-15px_rgba(0,0,0,0.3)] overflow-hidden relative">

            <!-- Header & Close Button -->
            <div class="px-6 sm:px-8 pt-6 pb-4 flex items-center justify-between relative z-10 border-b border-slate-100">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl bg-slate-900 text-white flex items-center justify-center font-black tracking-tight text-sm shadow-xs">
                        K71
                    </div>
                    <div>
                        <h3 class="text-base sm:text-lg font-bold text-slate-900 tracking-tight leading-none" 
                            x-text="tab === 'login' ? 'Sign In to K71' : (tab === 'register' ? 'Create Your Account' : 'Reset Password')"></h3>
                        <p class="text-[11px] text-slate-500 font-medium mt-1">Unified access for Mart, Services, Foods, Used & RFQs</p>
                    </div>
                </div>

                <button type="button" 
                        @click="close()" 
                        class="w-8 h-8 rounded-full bg-slate-100 hover:bg-slate-200 text-slate-500 hover:text-slate-900 flex items-center justify-center transition-colors cursor-pointer"
                        aria-label="Close dialog">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <!-- Tab Switcher (Login vs Register) -->
            <div x-show="tab !== 'forgot'" class="px-6 sm:px-8 pt-4 pb-1">
                <div class="flex p-1 rounded-2xl bg-slate-100 border border-slate-200/80">
                    <button type="button" 
                            @click="tab = 'login'; errorMessage = '';" 
                            :class="tab === 'login' ? 'bg-white text-slate-900 shadow-sm font-bold' : 'text-slate-600 hover:text-slate-900 font-semibold'"
                            class="flex-1 py-2 rounded-xl text-xs transition-all cursor-pointer">
                        Sign In
                    </button>
                    <button type="button" 
                            @click="tab = 'register'; errorMessage = '';" 
                            :class="tab === 'register' ? 'bg-white text-slate-900 shadow-sm font-bold' : 'text-slate-600 hover:text-slate-900 font-semibold'"
                            class="flex-1 py-2 rounded-xl text-xs transition-all cursor-pointer">
                        Create Account
                    </button>
                </div>
            </div>

            <!-- Form Error Message Banner -->
            <div x-show="errorMessage" 
                 x-cloak
                 class="mx-6 sm:mx-8 mt-3 p-3 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-xs font-semibold flex items-start gap-2">
                <span class="text-rose-500 text-base leading-none">⚠️</span>
                <span x-text="errorMessage" class="flex-1"></span>
            </div>

            <!-- Success Message Banner -->
            <div x-show="successMessage" 
                 x-cloak
                 class="mx-6 sm:mx-8 mt-3 p-3 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-semibold flex items-center gap-2">
                <span class="text-emerald-600 text-base leading-none">✓</span>
                <span x-text="successMessage" class="flex-1"></span>
            </div>

            <!-- TAB 1: LOGIN -->
            <form x-show="tab === 'login'" @submit.prevent="submitLogin()" class="p-6 sm:p-8 space-y-4 pt-3">
                <!-- Role Selector (Customer / Buyer vs Vendor / Supplier) -->
                <div class="space-y-1.5">
                    <label class="block text-[11px] font-bold text-slate-700 uppercase tracking-wider">Signing In As</label>
                    <div class="grid grid-cols-2 gap-2">
                        <label class="flex items-center justify-center gap-2 p-2.5 rounded-xl border text-xs font-bold cursor-pointer transition-all"
                               :class="loginRole === 'customer' ? 'border-slate-900 bg-slate-900 text-white shadow-xs' : 'border-slate-200 text-slate-700 bg-slate-50/70 hover:bg-slate-100'">
                            <input type="radio" name="modal_login_role" value="customer" x-model="loginRole" class="sr-only">
                            <span>🛍️ Buyer / Client</span>
                        </label>
                        <label class="flex items-center justify-center gap-2 p-2.5 rounded-xl border text-xs font-bold cursor-pointer transition-all"
                               :class="loginRole === 'vendor' ? 'border-slate-900 bg-slate-900 text-white shadow-xs' : 'border-slate-200 text-slate-700 bg-slate-50/70 hover:bg-slate-100'">
                            <input type="radio" name="modal_login_role" value="vendor" x-model="loginRole" class="sr-only">
                            <span>🏭 Supplier / Seller</span>
                        </label>
                    </div>
                </div>

                <!-- Username / Email / Mobile Input -->
                <div class="space-y-1.5">
                    <label class="block text-[11px] font-bold text-slate-700 uppercase tracking-wider">Email, Mobile, or Username</label>
                    <div class="relative">
                        <input type="text" 
                               x-model="loginInput" 
                               placeholder="e.g. buyer@company.com or 9876543210"
                               required
                               class="w-full px-4 py-3 rounded-2xl border border-slate-200 bg-slate-50/60 focus:bg-white text-xs sm:text-sm font-medium text-slate-900 focus:border-slate-900 focus:ring-4 focus:ring-slate-100 outline-none transition-all placeholder:text-slate-400">
                    </div>
                </div>

                <!-- Password Input with Toggle -->
                <div class="space-y-1.5">
                    <div class="flex items-center justify-between">
                        <label class="block text-[11px] font-bold text-slate-700 uppercase tracking-wider">Password</label>
                        <button type="button" @click="tab = 'forgot'; errorMessage = '';" class="text-[11px] font-bold text-slate-600 hover:text-slate-900 transition underline underline-offset-2">Forgot password?</button>
                    </div>
                    <div class="relative">
                        <input :type="showPassword ? 'text' : 'password'" 
                               x-model="passwordInput" 
                               placeholder="Enter your password"
                               required
                               class="w-full px-4 py-3 rounded-2xl border border-slate-200 bg-slate-50/60 focus:bg-white text-xs sm:text-sm font-medium text-slate-900 focus:border-slate-900 focus:ring-4 focus:ring-slate-100 outline-none transition-all pr-12">
                        <button type="button" 
                                @click="showPassword = !showPassword" 
                                class="absolute right-3.5 top-3.5 text-slate-400 hover:text-slate-700 focus:outline-none text-xs font-semibold">
                            <span x-text="showPassword ? 'Hide' : 'Show'"></span>
                        </button>
                    </div>
                </div>

                <!-- Submit Button -->
                <button type="submit" 
                        :disabled="isLoading" 
                        class="w-full py-3.5 rounded-2xl bg-slate-900 hover:bg-black active:scale-[0.99] text-white text-xs sm:text-sm font-bold tracking-wide shadow-xs transition-all flex items-center justify-center gap-2 disabled:opacity-50 cursor-pointer">
                    <template x-if="isLoading">
                        <svg class="animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                    </template>
                    <span x-text="isLoading ? 'Authenticating...' : 'Sign In & Continue'"></span>
                </button>
            </form>

            <!-- TAB 2: REGISTER -->
            <form x-show="tab === 'register'" @submit.prevent="submitRegister()" class="p-6 sm:p-8 space-y-3.5 pt-3">
                <div class="space-y-1">
                    <label class="block text-[11px] font-bold text-slate-700 uppercase tracking-wider">Full Name or Company Name *</label>
                    <input type="text" 
                           x-model="regName" 
                           placeholder="e.g. Ramesh Sharma or Marble Impex Ltd"
                           required
                           class="w-full px-4 py-2.5 rounded-2xl border border-slate-200 bg-slate-50/60 focus:bg-white text-xs sm:text-sm font-medium text-slate-900 focus:border-slate-900 focus:ring-4 focus:ring-slate-100 outline-none transition-all placeholder:text-slate-400">
                </div>

                <div class="space-y-1">
                    <label class="block text-[11px] font-bold text-slate-700 uppercase tracking-wider">Work Email Address *</label>
                    <input type="email" 
                           x-model="regEmail" 
                           placeholder="e.g. procurement@company.com"
                           required
                           class="w-full px-4 py-2.5 rounded-2xl border border-slate-200 bg-slate-50/60 focus:bg-white text-xs sm:text-sm font-medium text-slate-900 focus:border-slate-900 focus:ring-4 focus:ring-slate-100 outline-none transition-all placeholder:text-slate-400">
                </div>

                <div class="space-y-1">
                    <label class="block text-[11px] font-bold text-slate-700 uppercase tracking-wider">Mobile Number (For WhatsApp Updates)</label>
                    <div class="flex items-center">
                        <span class="px-3 py-2.5 bg-slate-100 border border-r-0 border-slate-200 rounded-l-2xl text-xs font-bold text-slate-600">+91</span>
                        <input type="tel" 
                               x-model="regPhone" 
                               placeholder="10-digit mobile number"
                               class="w-full px-4 py-2.5 rounded-r-2xl border border-slate-200 bg-slate-50/60 focus:bg-white text-xs sm:text-sm font-medium text-slate-900 focus:border-slate-900 focus:ring-4 focus:ring-slate-100 outline-none transition-all">
                    </div>
                </div>

                <div class="space-y-1">
                    <label class="block text-[11px] font-bold text-slate-700 uppercase tracking-wider">Create Password (Min 8 Characters) *</label>
                    <div class="relative">
                        <input :type="showPassword ? 'text' : 'password'" 
                               x-model="regPassword" 
                               placeholder="••••••••"
                               required
                               minlength="8"
                               class="w-full px-4 py-2.5 rounded-2xl border border-slate-200 bg-slate-50/60 focus:bg-white text-xs sm:text-sm font-medium text-slate-900 focus:border-slate-900 focus:ring-4 focus:ring-slate-100 outline-none transition-all pr-12">
                        <button type="button" 
                                @click="showPassword = !showPassword" 
                                class="absolute right-3.5 top-3 text-slate-400 hover:text-slate-700 focus:outline-none text-xs font-semibold">
                            <span x-text="showPassword ? 'Hide' : 'Show'"></span>
                        </button>
                    </div>
                </div>

                <div class="pt-2">
                    <button type="submit" 
                            :disabled="isLoading" 
                            class="w-full py-3.5 rounded-2xl bg-slate-900 hover:bg-black active:scale-[0.99] text-white text-xs sm:text-sm font-bold tracking-wide shadow-xs transition-all flex items-center justify-center gap-2 disabled:opacity-50 cursor-pointer">
                        <template x-if="isLoading">
                            <svg class="animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                        </template>
                        <span x-text="isLoading ? 'Creating Account...' : 'Create Account & Continue'"></span>
                    </button>
                </div>
            </form>

            <!-- TAB 3: FORGOT PASSWORD -->
            <form x-show="tab === 'forgot'" @submit.prevent="submitForgot()" class="p-6 sm:p-8 space-y-4 pt-4">
                <div class="space-y-1.5">
                    <label class="block text-[11px] font-bold text-slate-700 uppercase tracking-wider">Registered Email</label>
                    <input type="email" 
                           x-model="forgotEmail" 
                           placeholder="name@company.com"
                           required
                           class="w-full px-4 py-3 rounded-2xl border border-slate-200 bg-slate-50/60 focus:bg-white text-xs sm:text-sm font-medium text-slate-900 focus:border-slate-900 focus:ring-4 focus:ring-slate-100 outline-none transition-all placeholder:text-slate-400">
                </div>

                <div class="flex items-center gap-2">
                    <button type="submit" 
                            :disabled="isLoading" 
                            class="flex-1 py-3.5 rounded-2xl bg-slate-900 hover:bg-black text-white text-xs sm:text-sm font-bold shadow-xs transition flex items-center justify-center gap-2 cursor-pointer">
                        <span x-text="isLoading ? 'Sending...' : 'Send Recovery Link'"></span>
                    </button>
                    <button type="button" 
                            @click="tab = 'login'; errorMessage = '';" 
                            class="px-4 py-3.5 rounded-2xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition">
                        Back
                    </button>
                </div>
            </form>

            <!-- Footer: Security & Terms -->
            <div class="px-6 sm:px-8 py-3.5 bg-slate-50/80 border-t border-slate-100 text-[10px] text-slate-400 text-center flex items-center justify-center gap-2">
                <span>🔒 256-Bit SSL Encrypted</span>
                <span>•</span>
                <a href="{{ route('pages.privacy') }}" class="hover:underline text-slate-500">Privacy</a>
                <span>•</span>
                <a href="{{ route('pages.terms') }}" class="hover:underline text-slate-500">Terms</a>
            </div>

        </div>
    </div>
</div>

<script>
function k71AuthModal() {
    return {
        isOpen: false,
        tab: 'login', // 'login', 'register', 'forgot'
        loginRole: 'customer',
        loginInput: '',
        passwordInput: '',
        showPassword: false,
        regName: '',
        regEmail: '',
        regPhone: '',
        regPassword: '',
        forgotEmail: '',
        isLoading: false,
        errorMessage: '',
        successMessage: '',
        pendingCallback: null,

        open(options = {}) {
            this.tab = options.tab || 'login';
            this.loginRole = options.role || 'customer';
            this.pendingCallback = options.callback || null;
            this.errorMessage = '';
            this.successMessage = '';
            this.isOpen = true;
        },

        close() {
            this.isOpen = false;
        },

        getCsrfToken() {
            return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
        },

        async submitLogin() {
            this.isLoading = true;
            this.errorMessage = '';
            this.successMessage = '';

            try {
                const res = await fetch('/login', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': this.getCsrfToken(),
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    credentials: 'same-origin',
                    body: JSON.stringify({
                        login: this.loginInput,
                        password: this.passwordInput,
                        role: this.loginRole
                    })
                });

                const data = await res.json();
                if (res.ok && data.success) {
                    this.successMessage = 'Signed in successfully!';
                    if (data.csrf_token) {
                        const m = document.querySelector('meta[name="csrf-token"]');
                        if (m) m.content = data.csrf_token;
                    }

                    // Dispatch authenticated event
                    window.dispatchEvent(new CustomEvent('user-authenticated', { detail: data.user }));
                    if (window.showToast) window.showToast('Welcome back, ' + (data.user?.name || 'User') + '! 👋');

                    setTimeout(() => {
                        this.close();
                        if (typeof this.pendingCallback === 'function') {
                            this.pendingCallback(data.user);
                            this.pendingCallback = null;
                        } else if (data.redirect) {
                            window.location.href = data.redirect;
                        } else {
                            // Smooth reload or state update
                            window.location.reload();
                        }
                    }, 500);
                } else {
                    this.errorMessage = data.message || (data.errors ? Object.values(data.errors)[0][0] : 'Invalid credentials. Please verify your details.');
                }
            } catch(e) {
                console.error(e);
                this.errorMessage = 'Network error during login. Please try again.';
            } finally {
                this.isLoading = false;
            }
        },

        async submitRegister() {
            this.isLoading = true;
            this.errorMessage = '';
            this.successMessage = '';

            try {
                const res = await fetch('/register', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': this.getCsrfToken(),
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    credentials: 'same-origin',
                    body: JSON.stringify({
                        name: this.regName,
                        email: this.regEmail,
                        phone: this.regPhone,
                        password: this.regPassword
                    })
                });

                const data = await res.json();
                if (res.ok && data.success) {
                    this.successMessage = 'Account created successfully!';
                    if (data.csrf_token) {
                        const m = document.querySelector('meta[name="csrf-token"]');
                        if (m) m.content = data.csrf_token;
                    }

                    window.dispatchEvent(new CustomEvent('user-authenticated', { detail: data.user }));
                    if (window.showToast) window.showToast('Welcome to K71, ' + (data.user?.name || '') + '! 🎉');

                    setTimeout(() => {
                        this.close();
                        if (typeof this.pendingCallback === 'function') {
                            this.pendingCallback(data.user);
                            this.pendingCallback = null;
                        } else {
                            window.location.reload();
                        }
                    }, 600);
                } else {
                    this.errorMessage = data.message || (data.errors ? Object.values(data.errors)[0][0] : 'Could not create account. Please check your inputs.');
                }
            } catch(e) {
                console.error(e);
                this.errorMessage = 'Network error during registration. Please try again.';
            } finally {
                this.isLoading = false;
            }
        },

        async submitForgot() {
            this.isLoading = true;
            this.errorMessage = '';
            this.successMessage = '';

            try {
                const res = await fetch('/forgot-password', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': this.getCsrfToken()
                    },
                    credentials: 'same-origin',
                    body: JSON.stringify({ email: this.forgotEmail })
                });
                this.successMessage = 'If an account exists, a recovery link has been sent to your email!';
                setTimeout(() => {
                    this.tab = 'login';
                }, 3000);
            } catch(e) {
                this.errorMessage = 'Could not request password reset right now. Please try again.';
            } finally {
                this.isLoading = false;
            }
        }
    };
}

// Global window trigger helper
window.openAuthModal = function(callback, tab = 'login', role = 'customer') {
    window.dispatchEvent(new CustomEvent('open-auth-modal', {
        detail: { callback, tab, role }
    }));
};
</script>
