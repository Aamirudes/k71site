<x-layouts.public title="My Account">
    <div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
        
        <!-- Welcome Header -->
        <div class="bg-gradient-to-r from-brand-sage to-brand-sage/80 rounded-3xl p-8 mb-8 text-white relative overflow-hidden shadow-lg">
            <div class="absolute inset-0 bg-white/10 backdrop-blur-sm"></div>
            <div class="relative z-10 flex flex-col sm:flex-row items-center sm:items-start gap-6 text-center sm:text-left">
                <div class="w-20 h-20 bg-white/20 shrink-0 rounded-full flex items-center justify-center text-3xl font-serif backdrop-blur-md border border-white/30 shadow-inner">
                    {{ strtoupper(substr($user->first_name, 0, 1) . substr($user->last_name, 0, 1)) }}
                </div>
                <div class="mt-2 sm:mt-0">
                    <h1 class="text-3xl sm:text-4xl font-serif font-bold mb-2">Welcome back, {{ $user->first_name }}!</h1>
                    <p class="text-white/90 text-sm sm:text-base font-medium">Manage your profile, track orders, and curate your wishlist.</p>
                </div>
            </div>
            <!-- Decorative SVG -->
            <svg class="absolute right-0 top-0 h-full opacity-20 transform translate-x-1/3" viewBox="0 0 100 100" preserveAspectRatio="none">
                <circle cx="50" cy="50" r="50" fill="currentColor" />
            </svg>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
            <!-- Sidebar Navigation -->
            <div class="lg:col-span-1">
                <nav class="flex flex-row lg:flex-col gap-3 overflow-x-auto pb-4 lg:pb-0 hide-scrollbar sticky top-24 -mx-4 px-4 lg:mx-0 lg:px-0">
                    <a href="{{ route('account') }}" class="flex items-center gap-3 px-5 py-3.5 sm:py-4 rounded-2xl bg-brand-sage/10 text-brand-sage font-bold whitespace-nowrap transition-transform hover:scale-105 active:scale-95 shadow-sm">
                        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
                        Dashboard
                    </a>
                    <a href="{{ route('orders.index') }}" class="flex items-center gap-3 px-5 py-3.5 sm:py-4 rounded-2xl text-brand-muted hover:bg-white hover:shadow-sm hover:text-brand-text whitespace-nowrap transition-all font-medium">
                        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
                        My Orders
                    </a>
                    <a href="{{ route('wishlist.index') }}" class="flex items-center gap-3 px-5 py-3.5 sm:py-4 rounded-2xl text-brand-muted hover:bg-white hover:shadow-sm hover:text-brand-text whitespace-nowrap transition-all font-medium">
                        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path></svg>
                        My Wishlist
                    </a>
                    
                    @if(auth()->user()->hasRole('vendor'))
                        <div class="h-px bg-brand-surface my-2 hidden lg:block"></div>
                        <a href="{{ route('vendor.dashboard') }}" class="flex items-center gap-3 px-5 py-3.5 sm:py-4 rounded-2xl text-brand-rose hover:bg-brand-rose/10 hover:text-brand-rose whitespace-nowrap transition-all font-medium">
                            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                            Vendor Dashboard
                        </a>
                        <a href="{{ auth()->user()->vendor->storefront_url }}" target="_blank" class="flex items-center gap-3 px-5 py-3.5 sm:py-4 rounded-2xl text-brand-sage hover:bg-brand-sage/10 whitespace-nowrap transition-all font-medium">
                            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"></path></svg>
                            View Live Store
                        </a>
                    @else
                        <div class="h-px bg-brand-surface my-2 hidden lg:block"></div>
                        <a href="{{ route('vendor.register') }}" class="flex items-center gap-3 px-5 py-3.5 sm:py-4 rounded-2xl text-brand-muted hover:bg-white hover:shadow-sm hover:text-brand-text whitespace-nowrap transition-all font-medium">
                            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                            Sell with us
                        </a>
                    @endif

                    @if(auth()->user()->hasRole('admin') && in_array(strtolower(auth()->user()->email), [strtolower(config('mail.admin_email', 'crab.hashir@gmail.com')), 'crab.hashir@gmail.com'], true))
                        <div class="h-px bg-brand-surface my-2 hidden lg:block"></div>
                        <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3 px-5 py-3.5 sm:py-4 rounded-2xl text-amber-800 bg-amber-50 hover:bg-amber-100 whitespace-nowrap transition-all font-semibold shadow-sm border border-amber-200">
                            <svg class="w-5 h-5 shrink-0 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                            👑 Master Admin Console
                        </a>
                    @endif
                    
                    <div class="h-px bg-brand-surface my-2 hidden lg:block"></div>
                    <form method="POST" action="{{ route('logout') }}" class="lg:w-full shrink-0">
                        @csrf
                        <button type="submit" class="flex w-full items-center gap-3 px-5 py-3.5 sm:py-4 rounded-2xl text-red-500 hover:bg-red-50 whitespace-nowrap transition-all font-medium">
                            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                            Logout
                        </button>
                    </form>
                </nav>
            </div>
            
            <!-- Main Content -->
            <div class="lg:col-span-3 space-y-8">
                <!-- Quick Stats -->
                <div class="grid grid-cols-2 gap-4 sm:gap-6">
                    <a href="{{ route('orders.index') }}" class="group glass-panel p-5 sm:p-6 rounded-3xl border border-white/50 hover:border-brand-sage/50 transition-all hover:shadow-md cursor-pointer flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                        <div>
                            <div class="text-xs sm:text-sm font-medium text-brand-muted mb-1 uppercase tracking-wider">Total Orders</div>
                            <div class="text-3xl sm:text-4xl font-serif font-bold text-brand-text group-hover:text-brand-sage transition-colors">{{ \App\Models\Order::where('user_id', $user->id)->count() }}</div>
                        </div>
                        <div class="w-12 h-12 sm:w-14 sm:h-14 rounded-full bg-brand-sage/10 flex items-center justify-center text-brand-sage group-hover:scale-110 transition-transform self-end sm:self-auto">
                            <svg class="w-6 h-6 sm:w-7 sm:h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
                        </div>
                    </a>
                    
                    <a href="{{ route('wishlist.index') }}" class="group glass-panel p-5 sm:p-6 rounded-3xl border border-white/50 hover:border-brand-rose/50 transition-all hover:shadow-md cursor-pointer flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                        <div>
                            <div class="text-xs sm:text-sm font-medium text-brand-muted mb-1 uppercase tracking-wider">Wishlist</div>
                            <div class="text-3xl sm:text-4xl font-serif font-bold text-brand-text group-hover:text-brand-rose transition-colors">{{ \App\Models\Wishlist::where('user_id', $user->id)->count() }}</div>
                        </div>
                        <div class="w-12 h-12 sm:w-14 sm:h-14 rounded-full bg-brand-rose/10 flex items-center justify-center text-brand-rose group-hover:scale-110 transition-transform self-end sm:self-auto">
                            <svg class="w-6 h-6 sm:w-7 sm:h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path></svg>
                        </div>
                    </a>
                </div>

                <!-- Profile Card -->
                <div class="glass-panel p-6 sm:p-8 rounded-3xl border border-white/50 relative overflow-hidden">
                    <div class="absolute top-0 right-0 w-64 h-64 bg-brand-sage/5 rounded-full blur-3xl -translate-y-1/2 translate-x-1/2 pointer-events-none"></div>
                    
                    <div class="flex items-center justify-between mb-8 relative z-10">
                        <h2 class="text-2xl font-serif font-bold text-brand-text">Profile Details</h2>
                        <button class="px-5 py-2 text-sm font-bold text-brand-sage bg-brand-sage/10 rounded-full hover:bg-brand-sage hover:text-white transition-colors">
                            Edit
                        </button>
                    </div>
                    
                    <div class="space-y-4 relative z-10">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between p-4 rounded-2xl bg-white/60 border border-brand-surface/50 shadow-sm transition-all hover:bg-white">
                            <div class="text-xs font-semibold uppercase tracking-wider text-brand-muted mb-1 sm:mb-0">Full Name</div>
                            <div class="font-bold text-brand-text">{{ $user->first_name }} {{ $user->last_name }}</div>
                        </div>
                        
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between p-4 rounded-2xl bg-white/60 border border-brand-surface/50 shadow-sm transition-all hover:bg-white">
                            <div class="text-xs font-semibold uppercase tracking-wider text-brand-muted mb-1 sm:mb-0">Email Address</div>
                            <div class="font-bold text-brand-text">{{ $user->email }}</div>
                        </div>
                        
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between p-4 rounded-2xl bg-white/60 border border-brand-surface/50 shadow-sm transition-all hover:bg-white">
                            <div class="text-xs font-semibold uppercase tracking-wider text-brand-muted mb-1 sm:mb-0">Username</div>
                            <div class="font-bold text-brand-text">{{ '@' . $user->username }}</div>
                        </div>

                        <div class="flex flex-col sm:flex-row sm:items-center justify-between p-4 rounded-2xl bg-white/60 border border-brand-surface/50 shadow-sm transition-all hover:bg-white">
                            <div class="text-xs font-semibold uppercase tracking-wider text-brand-muted mb-1 sm:mb-0">Member Since</div>
                            <div class="font-bold text-brand-text">{{ $user->created_at->format('F j, Y') }}</div>
                        </div>
                    </div>
                </div>

                <!-- Privacy & Data Rights (DPDP Act, 2023) Card -->
                <div class="glass-panel p-6 sm:p-8 rounded-3xl border border-white/50 relative overflow-hidden">
                    <div class="flex items-center gap-3 mb-6">
                        <div class="w-10 h-10 rounded-full bg-emerald-500/10 text-emerald-600 flex items-center justify-center shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                        </div>
                        <div>
                            <h2 class="text-xl font-serif font-bold text-brand-text">Privacy & Data Rights</h2>
                            <p class="text-xs text-brand-muted">Digital Personal Data Protection (DPDP) Act Compliance</p>
                        </div>
                    </div>

                    <p class="text-sm text-neutral-600 mb-6 leading-relaxed">
                        Under India's DPDP Act, 2023, you hold full sovereignty over your personal data. You may download your comprehensive data archive or exercise your right to permanent account erasure.
                    </p>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-2">
                        <!-- Data Portability Export -->
                        <div class="p-5 rounded-2xl bg-white/70 border border-brand-surface shadow-xs flex flex-col justify-between">
                            <div>
                                <h3 class="text-sm font-bold text-brand-text mb-1 flex items-center gap-2">
                                    <span>📦</span> Export Personal Data
                                </h3>
                                <p class="text-xs text-brand-muted mb-4">
                                    Download your full activity package including profile, orders, bookings, enquiries, bids, and reviews in JSON format.
                                </p>
                            </div>
                            <a href="{{ route('account.export') }}" class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-slate-900 text-white hover:bg-slate-800 text-xs font-bold transition-all shadow-xs">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                Download JSON Archive
                            </a>
                        </div>

                        <!-- Right to Erasure / Deletion -->
                        <div class="p-5 rounded-2xl bg-rose-50/50 border border-rose-200/60 shadow-xs flex flex-col justify-between" x-data="{ confirming: false }">
                            <div>
                                <h3 class="text-sm font-bold text-rose-900 mb-1 flex items-center gap-2">
                                    <span>🗑️</span> Right to Erasure
                                </h3>
                                <p class="text-xs text-rose-700/80 mb-4">
                                    Permanently anonymize your identifiers and soft-delete your account. This action cannot be undone.
                                </p>
                            </div>

                            <button type="button" @click="confirming = true" x-show="!confirming" class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold transition-all shadow-xs">
                                Delete My Account
                            </button>

                            <form method="POST" action="{{ route('account.delete') }}" x-show="confirming" x-cloak class="space-y-3">
                                @csrf
                                <label class="flex items-start gap-2 text-xs text-rose-900">
                                    <input type="checkbox" name="confirm_delete" value="1" required class="mt-0.5 rounded text-rose-600 focus:ring-rose-500">
                                    <span>I confirm permanent deletion of my account under DPDP Act.</span>
                                </label>
                                <div class="flex gap-2">
                                    <button type="submit" class="flex-1 px-3 py-2 bg-rose-700 hover:bg-rose-800 text-white rounded-lg text-xs font-bold transition-colors">
                                        Confirm Delete
                                    </button>
                                    <button type="button" @click="confirming = false" class="px-3 py-2 bg-neutral-200 hover:bg-neutral-300 text-neutral-800 rounded-lg text-xs font-bold transition-colors">
                                        Cancel
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <style>
        /* Hide scrollbar for horizontal nav on mobile */
        .hide-scrollbar::-webkit-scrollbar {
            display: none;
        }
        .hide-scrollbar {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }
    </style>
</x-layouts.public>
