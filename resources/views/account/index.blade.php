<x-layouts.public title="My Account" :hideMainHeader="true">
    @php
        $ordersCount = \App\Models\Order::where('user_id', $user->id)->count();
        $wishlistCount = \App\Models\Wishlist::where('user_id', $user->id)->count();
        $recentOrders = \App\Models\Order::where('user_id', $user->id)->with('items.product')->latest()->take(10)->get();
        $wishlistItems = \App\Models\Wishlist::where('user_id', $user->id)->with('product')->latest()->take(12)->get();
    @endphp

    <div x-data="{
        activeTab: window.location.hash ? window.location.hash.replace('#', '') : 'profile',
        scrolledPastSummary: false,
        setTab(tabName) {
            this.activeTab = tabName;
            window.location.hash = tabName;
        }
    }" 
    x-init="
        const observer = new IntersectionObserver(([entry]) => {
            scrolledPastSummary = !entry.isIntersecting;
        }, { threshold: 0.1 });
        if ($refs.profileSummary) observer.observe($refs.profileSummary);
    "
    class="min-h-screen bg-[#F6F7FB] dark:bg-zinc-950 text-slate-900 dark:text-zinc-100 pb-24">

        <!-- ═══════════════════════════════════════════════════════════════════
             1. STICKY TOP HEADER (56px, Z-40, SHADOW ON SCROLL)
             ═══════════════════════════════════════════════════════════════════ -->
        <header class="k71-sticky-head sticky top-0 z-40 h-14 bg-white/95 dark:bg-zinc-900/95 backdrop-blur-md border-b border-slate-200/80 dark:border-zinc-800 px-4 flex items-center justify-between transition-shadow duration-200"
                :class="scrolledPastSummary ? 'shadow-sm' : ''">
            <!-- Back Button Left -->
            <a href="{{ url('/') }}" 
               class="w-10 h-10 -ml-2 rounded-full flex items-center justify-center text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-zinc-800 transition active:scale-95"
               aria-label="Back to home">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
            </a>

            <!-- Title Center ("My Account" or small avatar + user name when scrolled past) -->
            <div class="flex items-center gap-2 truncate">
                <template x-if="scrolledPastSummary">
                    <img src="{{ format_user_avatar($user->avatar, $user->first_name . ' ' . $user->last_name) }}" 
                         alt="{{ $user->username }}" 
                         class="w-7 h-7 rounded-full object-cover border border-slate-200 shadow-2xs">
                </template>
                <h1 class="text-sm sm:text-base font-bold text-slate-900 dark:text-white truncate"
                    x-text="scrolledPastSummary ? '{{ $user->first_name }} {{ $user->last_name }}' : 'My Account'">
                    My Account
                </h1>
            </div>

            <!-- Settings Gear Right -->
            <div class="flex items-center gap-1">
                <button type="button" 
                        @click="setTab('privacy')"
                        class="w-10 h-10 -mr-2 rounded-full flex items-center justify-center text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-zinc-800 transition active:scale-95 cursor-pointer"
                        aria-label="Account Settings">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                </button>
            </div>
        </header>

        <!-- ═══════════════════════════════════════════════════════════════════
             2. PROFILE SUMMARY (AVATAR 64px MOBILE, 80px DESKTOP, SCROLLS UNDER HEADER)
             ═══════════════════════════════════════════════════════════════════ -->
        <div x-ref="profileSummary" class="max-w-4xl mx-auto px-4 pt-5 pb-3">
            <div class="flex items-center gap-4 sm:gap-5">
                <!-- Avatar with photo upload trigger -->
                <div class="relative shrink-0 cursor-pointer group" 
                     onclick="document.getElementById('accountAvatarFileInput').click()" 
                     title="Click to change profile photo">
                    <img id="accountHeaderAvatar" 
                         src="{{ format_user_avatar($user->avatar, $user->first_name . ' ' . $user->last_name) }}" 
                         alt="{{ $user->username }}" 
                         class="w-16 h-16 sm:w-20 sm:h-20 rounded-full object-cover border-2 border-white dark:border-zinc-800 shadow-md">
                    <div class="absolute bottom-0 right-0 w-6 h-6 rounded-full bg-slate-900 text-white flex items-center justify-center shadow-xs border border-white">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    </div>
                </div>

                <!-- Name (18px weight 700), phone/email (13px muted), edit-profile button -->
                <div class="min-w-0 flex-1">
                    <div class="flex items-center gap-2 flex-wrap">
                        <h2 class="text-[18px] font-bold text-slate-900 dark:text-white leading-tight truncate">
                            {{ $user->first_name }} {{ $user->last_name }}
                        </h2>
                        @if($user->hasRole('vendor'))
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-50 text-amber-800 border border-amber-200">
                                Seller
                            </span>
                        @endif
                    </div>
                    <p class="text-[13px] text-slate-500 dark:text-slate-400 truncate mt-0.5 font-medium">
                        {{ $user->phone ?? $user->email }}
                    </p>
                    <div class="mt-2 flex items-center gap-2 flex-wrap">
                        <button type="button" 
                                @click="setTab('profile')"
                                class="px-3 py-1 rounded-lg border border-slate-300 dark:border-zinc-700 hover:bg-white dark:hover:bg-zinc-800 text-xs font-semibold text-slate-700 dark:text-slate-200 transition cursor-pointer">
                            Edit Profile
                        </button>
                        @if($user->hasRole('vendor'))
                            <a href="{{ route('vendor.dashboard') }}" 
                               class="px-3 py-1 rounded-lg bg-indigo-50 text-indigo-700 hover:bg-indigo-100 text-xs font-semibold transition">
                                Seller Portal &rarr;
                            </a>
                        @endif
                        <form method="POST" action="{{ route('logout') }}" class="inline">
                            @csrf
                            <button type="submit" class="px-3 py-1 rounded-lg text-rose-600 hover:bg-rose-50 text-xs font-semibold transition cursor-pointer">
                                Logout
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Hidden input for avatar upload -->
        <input type="file" id="accountAvatarFileInput" accept="image/jpeg,image/png,image/webp,image/jpg" class="hidden">

        <!-- ═══════════════════════════════════════════════════════════════════
             3. STICKY TABS (DIRECTLY BELOW 56px HEADER: top-14, SOLID BG, NEVER FLOAT)
             ═══════════════════════════════════════════════════════════════════ -->
        <div class="k71-sticky-tabs sticky top-14 z-30 bg-white dark:bg-zinc-900 border-y border-slate-200/90 dark:border-zinc-800 shadow-2xs">
            <div class="max-w-4xl mx-auto px-4 flex items-center gap-6 overflow-x-auto no-scrollbar">
                <button type="button" 
                        @click="setTab('profile')"
                        :class="activeTab === 'profile' ? 'border-b-2 font-bold text-indigo-600 border-indigo-600' : 'text-slate-500 hover:text-slate-900 font-medium border-b-2 border-transparent'"
                        class="py-3 text-xs sm:text-sm whitespace-nowrap transition cursor-pointer">
                    Profile Details
                </button>
                <button type="button" 
                        @click="setTab('orders')"
                        :class="activeTab === 'orders' ? 'border-b-2 font-bold text-indigo-600 border-indigo-600' : 'text-slate-500 hover:text-slate-900 font-medium border-b-2 border-transparent'"
                        class="py-3 text-xs sm:text-sm whitespace-nowrap transition cursor-pointer">
                    My Orders ({{ $ordersCount }})
                </button>
                <button type="button" 
                        @click="setTab('wishlist')"
                        :class="activeTab === 'wishlist' ? 'border-b-2 font-bold text-indigo-600 border-indigo-600' : 'text-slate-500 hover:text-slate-900 font-medium border-b-2 border-transparent'"
                        class="py-3 text-xs sm:text-sm whitespace-nowrap transition cursor-pointer">
                    Wishlist ({{ $wishlistCount }})
                </button>
                <button type="button" 
                        @click="setTab('privacy')"
                        :class="activeTab === 'privacy' ? 'border-b-2 font-bold text-indigo-600 border-indigo-600' : 'text-slate-500 hover:text-slate-900 font-medium border-b-2 border-transparent'"
                        class="py-3 text-xs sm:text-sm whitespace-nowrap transition cursor-pointer">
                    Privacy &amp; DPDP
                </button>
            </div>
        </div>

        <!-- ═══════════════════════════════════════════════════════════════════
             4. TAB CONTENT (CLEAN SOLID CARDS WITH 12px GAP)
             ═══════════════════════════════════════════════════════════════════ -->
        <main class="max-w-4xl mx-auto px-4 pt-5">
            
            <!-- A. PROFILE DETAILS TAB -->
            <section x-show="activeTab === 'profile'" class="space-y-3">
                <div class="k71-card p-5 sm:p-6 bg-white dark:bg-zinc-900 rounded-2xl border border-slate-200 dark:border-zinc-800 shadow-xs space-y-4">
                    <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-zinc-800">
                        <h3 class="text-base font-bold text-slate-900 dark:text-white">Personal Information</h3>
                        <span class="text-xs text-slate-400 font-medium">Synced with verified account</span>
                    </div>

                    <div class="space-y-3 text-xs sm:text-sm">
                        <div class="flex items-center justify-between py-2.5 border-b border-slate-100 dark:border-zinc-800">
                            <span class="text-slate-500 font-medium">Avatar</span>
                            <div class="flex items-center gap-2">
                                <img id="accountProfileDetailAvatar" 
                                     src="{{ format_user_avatar($user->avatar, $user->first_name . ' ' . $user->last_name) }}" 
                                     alt="{{ $user->username }}" 
                                     class="w-9 h-9 rounded-full object-cover border border-slate-200 shadow-2xs">
                                <button type="button" 
                                        onclick="document.getElementById('accountAvatarFileInput').click()"
                                        class="px-2.5 py-1 text-xs font-semibold text-indigo-600 hover:text-indigo-800 border border-slate-200 rounded-lg cursor-pointer">
                                    Change
                                </button>
                            </div>
                        </div>

                        <div class="flex items-center justify-between py-2.5 border-b border-slate-100 dark:border-zinc-800">
                            <span class="text-slate-500 font-medium">Full Name</span>
                            <span class="font-bold text-slate-900 dark:text-white">{{ $user->first_name }} {{ $user->last_name }}</span>
                        </div>

                        <div class="flex items-center justify-between py-2.5 border-b border-slate-100 dark:border-zinc-800">
                            <span class="text-slate-500 font-medium">Email Address</span>
                            <span class="font-bold text-slate-900 dark:text-white">{{ $user->email }}</span>
                        </div>

                        <div class="flex items-center justify-between py-2.5 border-b border-slate-100 dark:border-zinc-800">
                            <span class="text-slate-500 font-medium">Username</span>
                            <span class="font-mono text-slate-800 dark:text-zinc-200">{{ '@' . $user->username }}</span>
                        </div>

                        <div class="flex items-center justify-between py-2.5">
                            <span class="text-slate-500 font-medium">Account Created</span>
                            <span class="text-slate-700 dark:text-zinc-300">{{ $user->created_at->format('M d, Y') }}</span>
                        </div>
                    </div>
                </div>
            </section>

            <!-- B. ORDERS TAB (BRAND-SAFE STATUS CHIPS, 12px GAP) -->
            <section x-show="activeTab === 'orders'" class="space-y-3">
                @forelse($recentOrders as $order)
                    @php
                        $firstItem = $order->items->first();
                        $totalQty = $order->items->sum('quantity');
                        $isDelivered = ($order->status === 'delivered') || ($order->shipment && $order->shipment->status === 'delivered');
                    @endphp
                    <div class="k71-card p-4 sm:p-5 bg-white dark:bg-zinc-900 rounded-2xl border border-slate-200 dark:border-zinc-800 shadow-xs flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                        <div class="flex items-center gap-3.5 min-w-0 flex-1">
                            <!-- Thumbnail -->
                            <div class="w-14 h-14 sm:w-16 sm:h-16 rounded-xl bg-slate-100 border border-slate-200 overflow-hidden shrink-0">
                                @if($firstItem && $firstItem->product)
                                    <img src="{{ $firstItem->product->primary_image_url }}" 
                                         alt="{{ $firstItem->product->title }}" 
                                         class="w-full h-full object-cover">
                                @else
                                    <div class="w-full h-full flex items-center justify-center text-xl">📦</div>
                                @endif
                            </div>

                            <div class="min-w-0 flex-1">
                                <div class="flex items-center gap-2 flex-wrap mb-1">
                                    <span class="font-mono text-[11px] font-bold text-slate-700 dark:text-zinc-300 bg-slate-100 dark:bg-zinc-800 px-2 py-0.5 rounded">
                                        {{ $order->order_number }}
                                    </span>
                                    <span class="text-[11px] text-slate-400">• {{ $order->created_at->format('M d, Y') }}</span>
                                    
                                    <!-- Brand-Safe Status Chip (High Contrast, No Unreadable Yellow-on-White) -->
                                    @if($isDelivered)
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-900 border border-emerald-300">
                                            ✓ Delivered
                                        </span>
                                    @elseif($order->status === 'shipped')
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-blue-100 text-blue-900 border border-blue-300">
                                            🚚 In Transit
                                        </span>
                                    @elseif($order->status === 'cancelled')
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-rose-100 text-rose-900 border border-rose-300">
                                            ✕ Cancelled
                                        </span>
                                    @else
                                        <!-- Brand-safe dark amber for pending/processing -->
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-950 border border-amber-300">
                                            ⏳ {{ ucfirst($order->status) }}
                                        </span>
                                    @endif
                                </div>

                                <h4 class="text-xs sm:text-sm font-semibold text-slate-900 dark:text-white truncate">
                                    {{ $firstItem ? ($firstItem->product->title ?? 'Store Item') : 'Order Item' }}
                                </h4>
                                <div class="text-[11px] text-slate-500 mt-0.5">
                                    {{ $totalQty }} {{ Str::plural('item', $totalQty) }} • Total: <strong class="text-slate-900 dark:text-white">₹{{ number_format($order->total_amount / 100, 2) }}</strong>
                                </div>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="flex items-center gap-2 shrink-0 self-end sm:self-auto">
                            <a href="{{ route('orders.show', $order->id) }}" 
                               class="px-3 py-1.5 rounded-lg border border-slate-200 dark:border-zinc-700 hover:bg-slate-50 dark:hover:bg-zinc-800 text-xs font-semibold text-slate-700 dark:text-slate-200 transition">
                                View Order
                            </a>
                            <a href="{{ route('orders.index') }}" 
                               class="px-3.5 py-1.5 rounded-lg bg-slate-900 hover:bg-black text-white text-xs font-bold transition shadow-2xs">
                                Track
                            </a>
                        </div>
                    </div>
                @empty
                    <div class="k71-card p-10 bg-white dark:bg-zinc-900 rounded-2xl border border-slate-200 dark:border-zinc-800 text-center space-y-2">
                        <div class="text-3xl">📦</div>
                        <h4 class="text-sm font-bold text-slate-900 dark:text-white">No orders placed yet</h4>
                        <p class="text-xs text-slate-500">Your purchases and delivery progress will appear here.</p>
                        <a href="{{ route('catalog.index') }}" class="inline-block mt-2 px-4 py-2 rounded-xl bg-slate-900 text-white text-xs font-bold">
                            Explore Catalog
                        </a>
                    </div>
                @endforelse
            </section>

            <!-- C. WISHLIST TAB (2-COLUMN GRID MATCHING MAIN PRODUCT GRID) -->
            <section x-show="activeTab === 'wishlist'">
                @if($wishlistItems->isNotEmpty())
                    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3">
                        @foreach($wishlistItems as $item)
                            @if($item->product)
                                <div class="k71-card bg-white dark:bg-zinc-900 rounded-xl border border-slate-200 dark:border-zinc-800 overflow-hidden group shadow-2xs flex flex-col justify-between">
                                    <div>
                                        <div class="relative aspect-square bg-slate-100 overflow-hidden">
                                            <a href="{{ route('catalog.product', $item->product->slug) }}">
                                                <img src="{{ $item->product->primary_image_url }}" 
                                                     alt="{{ $item->product->title }}" 
                                                     class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-200">
                                            </a>
                                            <button type="button" 
                                                    onclick="window.toggleProductWishlist({{ $item->product_id }}); this.closest('.k71-card').remove();"
                                                    class="absolute top-2 right-2 w-7 h-7 rounded-full bg-white/90 text-rose-500 flex items-center justify-center shadow-xs cursor-pointer"
                                                    aria-label="Remove from wishlist">
                                                ✕
                                            </button>
                                        </div>
                                        <div class="p-2.5">
                                            <a href="{{ route('catalog.product', $item->product->slug) }}" class="block">
                                                <h4 class="text-xs font-semibold text-slate-900 dark:text-white line-clamp-2 min-h-[2rem]">
                                                    {{ $item->product->title }}
                                                </h4>
                                            </a>
                                            <div class="mt-1 font-bold text-xs text-slate-950 dark:text-white">
                                                ₹{{ number_format($item->product->base_price / 100, 2) }}
                                            </div>
                                        </div>
                                    </div>
                                    <div class="p-2.5 pt-0">
                                        <a href="{{ route('catalog.product', $item->product->slug) }}" 
                                           class="w-full block py-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-800 text-[11px] font-semibold text-center transition">
                                            View Item
                                        </a>
                                    </div>
                                </div>
                            @endif
                        @endforeach
                    </div>
                @else
                    <div class="k71-card p-10 bg-white dark:bg-zinc-900 rounded-2xl border border-slate-200 dark:border-zinc-800 text-center space-y-2">
                        <div class="text-3xl">❤️</div>
                        <h4 class="text-sm font-bold text-slate-900 dark:text-white">Your wishlist is empty</h4>
                        <p class="text-xs text-slate-500">Save items you love to revisit or purchase later.</p>
                        <a href="{{ route('catalog.index') }}" class="inline-block mt-2 px-4 py-2 rounded-xl bg-slate-900 text-white text-xs font-bold">
                            Explore Products
                        </a>
                    </div>
                @endif
            </section>

            <!-- D. PRIVACY & DPDP TAB -->
            <section x-show="activeTab === 'privacy'" class="space-y-3">
                <div class="k71-card p-5 sm:p-6 bg-white dark:bg-zinc-900 rounded-2xl border border-slate-200 dark:border-zinc-800 shadow-xs space-y-4">
                    <div>
                        <h3 class="text-base font-bold text-slate-900 dark:text-white">Digital Personal Data Protection (DPDP) Act</h3>
                        <p class="text-xs text-slate-500 mt-0.5">Manage your personal data archive and exercise statutory rights.</p>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-2">
                        <!-- Export Data -->
                        <div class="p-4 rounded-xl bg-slate-50 dark:bg-zinc-800/60 border border-slate-200 dark:border-zinc-700 flex flex-col justify-between gap-3">
                            <div>
                                <h4 class="text-xs font-bold text-slate-900 dark:text-white">📦 Download Data Package</h4>
                                <p class="text-[11px] text-slate-500 mt-1">Export all your account profile, orders, bids, and enquiry records in JSON.</p>
                            </div>
                            <a href="{{ route('account.export') }}" class="w-full py-2 rounded-lg bg-slate-900 hover:bg-black text-white text-xs font-bold text-center transition">
                                Download JSON Archive
                            </a>
                        </div>

                        <!-- Right to Erasure -->
                        <div class="p-4 rounded-xl bg-rose-50/60 dark:bg-rose-950/20 border border-rose-200 dark:border-rose-900/40 flex flex-col justify-between gap-3" x-data="{ confirming: false }">
                            <div>
                                <h4 class="text-xs font-bold text-rose-900 dark:text-rose-200">🗑️ Right to Permanent Erasure</h4>
                                <p class="text-[11px] text-rose-700/80 dark:text-rose-300 mt-1">Permanently purge your account, data, and sessions from K71.</p>
                            </div>
                            <div>
                                <button type="button" 
                                        x-show="!confirming" 
                                        @click="confirming = true"
                                        class="w-full py-2 rounded-lg border border-rose-300 text-rose-700 hover:bg-rose-100 text-xs font-bold transition cursor-pointer">
                                    Request Account Erasure
                                </button>
                                <div x-show="confirming" class="space-y-2" x-cloak>
                                    <p class="text-[10px] text-rose-800 font-bold">This action cannot be undone.</p>
                                    <div class="flex items-center gap-2">
                                        <form method="POST" action="{{ route('account.delete') }}" class="flex-1">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="w-full py-1.5 rounded-lg bg-rose-600 text-white text-xs font-bold hover:bg-rose-700">
                                                Confirm Delete
                                            </button>
                                        </form>
                                        <button type="button" @click="confirming = false" class="px-3 py-1.5 rounded-lg border border-slate-300 text-xs font-semibold">
                                            Cancel
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

        </main>
    </div>

    <!-- Avatar Upload Script -->
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        const fileInput = document.getElementById('accountAvatarFileInput');
        const headerAvatar = document.getElementById('accountHeaderAvatar');
        const profileDetailAvatar = document.getElementById('accountProfileDetailAvatar');

        if (fileInput) {
            fileInput.addEventListener('change', function () {
                const file = fileInput.files[0];
                if (!file) return;

                const reader = new FileReader();
                reader.onload = function (e) {
                    if (headerAvatar) headerAvatar.src = e.target.result;
                    if (profileDetailAvatar) profileDetailAvatar.src = e.target.result;
                };
                reader.readAsDataURL(file);

                const formData = new FormData();
                formData.append('avatar', file);

                const tokenEl = document.querySelector('meta[name="csrf-token"]');
                const csrfToken = tokenEl ? tokenEl.getAttribute('content') : '{{ csrf_token() }}';

                fetch('{{ route("account.avatar") }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: formData
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success && data.avatar_url) {
                        if (headerAvatar) headerAvatar.src = data.avatar_url;
                        if (profileDetailAvatar) profileDetailAvatar.src = data.avatar_url;
                    } else {
                        alert(data.error || 'Failed to update profile picture.');
                    }
                })
                .catch(err => {
                    console.error('Avatar upload error:', err);
                });
            });
        }
    });
    </script>
</x-layouts.public>
