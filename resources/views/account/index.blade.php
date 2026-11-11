<x-layouts.public title="My Account" :hideMainHeader="true" :fullWidth="true">
    @php
        $ordersCount = \App\Models\Order::where('user_id', $user->id)->count();
        $wishlistCount = \App\Models\Wishlist::where('user_id', $user->id)->count();
        $recentOrders = \App\Models\Order::where('user_id', $user->id)->with('items.product')->latest()->take(10)->get();
        $wishlistItems = \App\Models\Wishlist::where('user_id', $user->id)->with('product')->latest()->take(12)->get();
    @endphp

    <div x-data="{
        activeTab: (window.location.hash && ['profile', 'orders', 'wishlist'].includes(window.location.hash.replace('#', ''))) ? window.location.hash.replace('#', '') : 'profile',
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
    class="min-h-screen bg-[#000000] text-[#EFE9F2]">

        <!-- ═══════════════════════════════════════════════════════════════════
             1. STICKY TOP HEADER (56px, Z-40, OBSIDIAN SHADOW ON SCROLL)
             ═══════════════════════════════════════════════════════════════════ -->
        <header class="k71-sticky-head sticky top-0 z-40 h-14 bg-[#060606]/95 backdrop-blur-md border-b border-white/10 px-4 flex items-center justify-between transition-shadow duration-200"
                :class="scrolledPastSummary ? 'shadow-[0_12px_30px_rgba(0,0,0,0.45)]' : ''">
            <!-- Back Button Left -->
            <a href="{{ url('/') }}" 
               class="w-10 h-10 -ml-2 rounded-full flex items-center justify-center text-white/80 hover:text-[#ECD2F3] hover:bg-white/8 transition active:scale-95"
               aria-label="Back to home">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
            </a>

            <!-- Title Center ("My Account" or small avatar + user name when scrolled past) -->
            <div class="flex items-center gap-2 truncate">
                <template x-if="scrolledPastSummary">
                    <img src="{{ format_user_avatar($user->avatar, $user->first_name . ' ' . $user->last_name) }}" 
                         alt="{{ $user->username }}" 
                         class="w-7 h-7 rounded-full object-cover border border-[#ECD2F3] shadow-2xs">
                </template>
                <h1 class="text-sm sm:text-base font-bold text-white truncate"
                    x-text="scrolledPastSummary ? '{{ $user->first_name }} {{ $user->last_name }}' : 'My Account'">
                    My Account
                </h1>
            </div>

            <!-- Settings Gear Right -->
            <div class="flex items-center gap-1">
                <button type="button" 
                        @click="setTab('profile'); document.getElementById('account-privacy-section')?.scrollIntoView({ behavior: 'smooth' });"
                        class="w-10 h-10 -mr-2 rounded-full flex items-center justify-center text-white/80 hover:text-[#ECD2F3] hover:bg-white/8 transition active:scale-95 cursor-pointer"
                        aria-label="Account Settings">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                </button>
            </div>
        </header>

        <!-- ═══════════════════════════════════════════════════════════════════
             2. PROFILE SUMMARY (GRAIL OBSIDIAN HERO BAND)
             ═══════════════════════════════════════════════════════════════════ -->
        <div x-ref="profileSummary" class="grail-hero-band">
            <div class="max-w-4xl mx-auto px-4 pt-6 pb-6">
                <div class="flex items-center gap-4 sm:gap-5">
                    <!-- Avatar with photo upload trigger -->
                    <div class="relative shrink-0 cursor-pointer group" 
                         onclick="document.getElementById('accountAvatarFileInput').click()" 
                         title="Click to change profile photo">
                        <img id="accountHeaderAvatar" 
                             src="{{ format_user_avatar($user->avatar, $user->first_name . ' ' . $user->last_name) }}" 
                             alt="{{ $user->username }}" 
                             class="w-16 h-16 sm:w-20 sm:h-20 rounded-full object-cover border-2 border-[#ECD2F3] shadow-[0_8px_24px_rgba(0,0,0,0.5)]">
                        <div class="absolute bottom-0 right-0 w-6 h-6 rounded-full bg-[#ECD2F3] text-[#1A1018] flex items-center justify-center shadow-xs border border-[#060606]">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        </div>
                    </div>

                    <!-- Name & quick links -->
                    <div class="min-w-0 flex-1">
                        <div class="flex items-center gap-2 flex-wrap">
                            <h2 class="grail-display text-2xl sm:text-3xl font-bold text-white leading-tight truncate">
                                {{ $user->first_name }} {{ $user->last_name }}
                            </h2>
                            @if($user->hasRole('vendor'))
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-400/15 text-amber-300 border border-amber-400/25">
                                    Seller
                                </span>
                            @endif
                            @if($user->isFleet())
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-400/15 text-emerald-300 border border-emerald-400/25">
                                    Fleet Partner
                                </span>
                            @endif
                        </div>
                        <p class="text-[13px] text-white/65 truncate mt-0.5 font-medium">
                            {{ $user->phone ?? $user->email }}
                        </p>
                        <div class="mt-3 flex items-center gap-2 flex-wrap">
                            <button type="button" 
                                    @click="setTab('profile')"
                                    class="px-3.5 py-1.5 rounded-full border border-white/20 bg-white/8 hover:bg-white/15 text-xs font-semibold text-white transition cursor-pointer">
                                Edit Profile
                            </button>
                            @if($user->hasRole('vendor'))
                                <a href="{{ route('vendor.dashboard') }}" 
                                   class="px-3.5 py-1.5 rounded-full bg-[#ECD2F3] text-[#1A1018] hover:bg-white text-xs font-bold transition">
                                    {{ $user->vendor?->isServices() ? 'Provider Studio' : 'Seller Portal' }} &rarr;
                                </a>
                            @else
                                <a href="{{ route('services.become_seller') }}"
                                   class="px-3.5 py-1.5 rounded-full bg-[#ECD2F3] text-[#1A1018] hover:bg-white text-xs font-bold transition">
                                    Launch Provider Website &rarr;
                                </a>
                            @endif
                            <a href="{{ route('addresses.index') }}"
                               class="px-3.5 py-1.5 rounded-full border border-white/20 bg-white/8 hover:bg-white/15 text-xs font-semibold text-white transition">
                                Saved Addresses
                            </a>
                            <a href="{{ route('services.projects.index') }}"
                               class="px-3.5 py-1.5 rounded-full border border-white/20 bg-white/8 hover:bg-white/15 text-xs font-semibold text-white transition">
                                My Project Briefs
                            </a>
                            <a href="{{ route('services.orders.index') }}"
                               class="px-3.5 py-1.5 rounded-full border border-white/20 bg-white/8 hover:bg-white/15 text-xs font-semibold text-white transition">
                                Service Orders
                            </a>
                            <a href="{{ route('used.dashboard') }}"
                               class="px-3.5 py-1.5 rounded-full border border-white/20 bg-white/8 hover:bg-white/15 text-xs font-semibold text-white transition">
                                Used Marketplace Hub
                            </a>
                            <a href="{{ route('used.sell') }}"
                               class="px-3.5 py-1.5 rounded-full border border-[#ECD2F3]/40 bg-[#ECD2F3]/15 hover:bg-[#ECD2F3]/25 text-xs font-semibold text-[#ECD2F3] transition">
                                + Sell Used Item
                            </a>
                            @if($user->isFleet())
                                <a href="{{ route('delivery.dashboard') }}" 
                                   class="px-3.5 py-1.5 rounded-full bg-emerald-500/20 text-emerald-200 border border-emerald-400/30 hover:bg-emerald-500/30 text-xs font-semibold transition">
                                    Fleet Portal &rarr;
                                </a>
                            @endif
                            <form method="POST" action="{{ route('logout') }}" class="inline">
                                @csrf
                                <button type="submit" class="px-3 py-1.5 rounded-full text-rose-300 hover:bg-rose-500/15 text-xs font-semibold transition cursor-pointer">
                                    Logout
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Hidden input for avatar upload -->
        <input type="file" id="accountAvatarFileInput" accept="image/jpeg,image/png,image/webp,image/jpg" class="hidden">

        <!-- ═══════════════════════════════════════════════════════════════════
             3. STICKY TABS (DIRECTLY BELOW 56px HEADER: top-14, OBSIDIAN BG)
             ═══════════════════════════════════════════════════════════════════ -->
        <div class="k71-sticky-tabs sticky top-14 z-30 bg-[#0D0D0D] border-y border-white/10 shadow-sm">
            <div class="max-w-4xl mx-auto px-4 flex items-center gap-6 overflow-x-auto no-scrollbar">
                <button type="button" 
                        @click="setTab('profile')"
                        :class="activeTab === 'profile' ? 'border-b-2 font-bold text-[#ECD2F3] border-[#ECD2F3]' : 'text-white/60 hover:text-white font-medium border-b-2 border-transparent'"
                        class="py-3.5 text-xs sm:text-sm whitespace-nowrap transition cursor-pointer">
                    Profile Details
                </button>
                <button type="button" 
                        @click="setTab('orders')"
                        :class="activeTab === 'orders' ? 'border-b-2 font-bold text-[#ECD2F3] border-[#ECD2F3]' : 'text-white/60 hover:text-white font-medium border-b-2 border-transparent'"
                        class="py-3.5 text-xs sm:text-sm whitespace-nowrap transition cursor-pointer">
                    My Orders ({{ $ordersCount }})
                </button>
                <button type="button" 
                        @click="setTab('wishlist')"
                        :class="activeTab === 'wishlist' ? 'border-b-2 font-bold text-[#ECD2F3] border-[#ECD2F3]' : 'text-white/60 hover:text-white font-medium border-b-2 border-transparent'"
                        class="py-3.5 text-xs sm:text-sm whitespace-nowrap transition cursor-pointer">
                    Wishlist ({{ $wishlistCount }})
                </button>
            </div>
        </div>

        <!-- ═══════════════════════════════════════════════════════════════════
             4. TAB CONTENT INSIDE GRAIL STONE SHEET
             ═══════════════════════════════════════════════════════════════════ -->
        <div class="grail-sheet rounded-t-[28px] sm:rounded-t-[36px] min-h-[70vh] pb-24">
            <main class="max-w-4xl mx-auto px-4 pt-6 sm:pt-8">
                
                <!-- A. PROFILE DETAILS TAB -->
                <section x-show="activeTab === 'profile'" class="space-y-4">
                    <div class="k71-card p-5 sm:p-6 bg-white rounded-[24px] border border-black/8 shadow-[0_8px_28px_rgba(26,16,24,0.05)] space-y-4">
                        <div class="flex items-center justify-between pb-3 border-b border-black/8">
                            <h3 class="grail-display text-lg font-bold text-[#1A1018]">Personal Information</h3>
                            <span class="text-xs text-[#7A6B78] font-medium">Synced with verified account</span>
                        </div>

                        <div class="space-y-3 text-xs sm:text-sm">
                            <div class="flex items-center justify-between py-2.5 border-b border-black/6">
                                <span class="text-[#7A6B78] font-medium">Avatar</span>
                                <div class="flex items-center gap-2.5">
                                    <img id="accountProfileDetailAvatar" 
                                         src="{{ format_user_avatar($user->avatar, $user->first_name . ' ' . $user->last_name) }}" 
                                         alt="{{ $user->username }}" 
                                         class="w-9 h-9 rounded-full object-cover border border-black/15 shadow-2xs">
                                    <button type="button" 
                                            onclick="document.getElementById('accountAvatarFileInput').click()"
                                            class="px-3 py-1 text-xs font-bold text-[#623562] hover:bg-[#FAF8F5] border border-black/12 rounded-full cursor-pointer transition">
                                        Change
                                    </button>
                                </div>
                            </div>

                            <div class="flex items-center justify-between py-2.5 border-b border-black/6">
                                <span class="text-[#7A6B78] font-medium">Full Name</span>
                                <span class="font-bold text-[#1A1018]">{{ $user->first_name }} {{ $user->last_name }}</span>
                            </div>

                            <div class="flex items-center justify-between py-2.5 border-b border-black/6">
                                <span class="text-[#7A6B78] font-medium">Email Address</span>
                                <span class="font-bold text-[#1A1018]">{{ $user->email }}</span>
                            </div>

                            <div class="flex items-center justify-between py-2.5 border-b border-black/6">
                                <span class="text-[#7A6B78] font-medium">Username</span>
                                <span class="font-mono text-[#1A1018]">{{ '@' . $user->username }}</span>
                            </div>

                            <div class="flex items-center justify-between py-2.5">
                                <span class="text-[#7A6B78] font-medium">Account Created</span>
                                <span class="text-[#1A1018] font-medium">{{ $user->created_at->format('M d, Y') }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="k71-card p-5 sm:p-6 bg-white rounded-[24px] border border-black/8 shadow-[0_8px_28px_rgba(26,16,24,0.05)] flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                        <div>
                            <h3 class="grail-display text-lg font-bold text-[#1A1018]">Saved Delivery Addresses</h3>
                            <p class="text-xs text-[#7A6B78] mt-0.5">Save Home, Work, or other delivery locations for 1-tap K71 Food and Marketplace checkout.</p>
                        </div>
                        <a href="{{ route('addresses.index') }}" class="px-5 py-2.5 rounded-full bg-[#1A1018] hover:bg-black text-[#ECD2F3] text-xs font-bold text-center transition whitespace-nowrap">
                            Manage Addresses &rarr;
                        </a>
                    </div>

                    <!-- Privacy & Data Controls (Integrated discreetly into Profile) -->
                    <div id="account-privacy-section" class="k71-card p-5 sm:p-6 bg-white rounded-[24px] border border-black/8 shadow-[0_8px_28px_rgba(26,16,24,0.05)] space-y-4">
                        <div>
                            <h3 class="grail-display text-lg font-bold text-[#1A1018]">Account Security &amp; Data Privacy</h3>
                            <p class="text-xs text-[#7A6B78] mt-0.5">Manage your personal data archive and privacy settings.</p>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5 pt-1">
                            <!-- Export Data -->
                            <div class="p-4 rounded-2xl bg-[#FAF8F5] border border-black/8 flex flex-col justify-between gap-3">
                                <div>
                                    <h4 class="text-xs font-bold text-[#1A1018]">Download Account Data</h4>
                                    <p class="text-[11px] text-[#7A6B78] mt-0.5">Export profile and order records in JSON.</p>
                                </div>
                                <a href="{{ route('account.export') }}" class="w-full py-2 rounded-xl bg-[#1A1018] hover:bg-black text-[#ECD2F3] text-xs font-bold text-center transition">
                                    Download Archive
                                </a>
                            </div>

                            <!-- Right to Erasure -->
                            <div class="p-4 rounded-2xl bg-rose-50/50 border border-rose-200 flex flex-col justify-between gap-3" x-data="{ confirming: false }">
                                <div>
                                    <h4 class="text-xs font-bold text-rose-900">Delete Account</h4>
                                    <p class="text-[11px] text-rose-700/80 mt-0.5">Permanently remove your account and data.</p>
                                </div>
                                <div>
                                    <button type="button" 
                                            x-show="!confirming" 
                                            @click="confirming = true"
                                            class="w-full py-2 rounded-xl border border-rose-300 text-rose-700 hover:bg-rose-100 text-xs font-semibold transition cursor-pointer">
                                        Request Deletion
                                    </button>
                                    <div x-show="confirming" class="space-y-2" x-cloak>
                                        <p class="text-[10px] text-rose-800 font-bold">This action cannot be undone.</p>
                                        <div class="flex items-center gap-2">
                                            <form method="POST" action="{{ route('account.delete') }}" class="flex-1">
                                                @csrf
                                                <input type="hidden" name="confirm_delete" value="1">
                                                <button type="submit" class="w-full py-1.5 rounded-lg bg-rose-600 text-white text-xs font-bold hover:bg-rose-700 cursor-pointer">
                                                    Confirm Delete
                                                </button>
                                            </form>
                                            <button type="button" @click="confirming = false" class="px-3 py-1.5 rounded-lg border border-black/15 text-xs font-semibold text-[#1A1018] cursor-pointer">
                                                Cancel
                                            </button>
                                        </div>
                                    </div>
                                </div>
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
                        <div class="k71-card p-4 sm:p-5 bg-white rounded-[24px] border border-black/8 shadow-[0_8px_24px_rgba(26,16,24,0.04)] flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                            <div class="flex items-center gap-3.5 min-w-0 flex-1">
                                <!-- Thumbnail -->
                                <div class="w-14 h-14 sm:w-16 sm:h-16 rounded-2xl bg-[#F4F1EC] border border-black/8 overflow-hidden shrink-0">
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
                                        <span class="font-mono text-[11px] font-bold text-[#1A1018] bg-[#F4F1EC] px-2 py-0.5 rounded-md border border-black/8">
                                            {{ $order->order_number }}
                                        </span>
                                        <span class="text-[11px] text-[#7A6B78]">• {{ $order->created_at->format('M d, Y') }}</span>
                                        
                                        <!-- Brand-Safe Status Chip -->
                                        @if($isDelivered)
                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-900 border border-emerald-300">
                                                ✓ Delivered
                                            </span>
                                        @elseif($order->status === 'shipped')
                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-[#F8EFFA] text-[#623562] border border-[#ECD2F3]">
                                                🚚 In Transit
                                            </span>
                                        @elseif($order->status === 'cancelled')
                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 text-rose-900 border border-rose-300">
                                                ✕ Cancelled
                                            </span>
                                        @else
                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-950 border border-amber-300">
                                                ⏳ {{ ucfirst($order->status) }}
                                            </span>
                                        @endif
                                    </div>

                                    <h4 class="text-xs sm:text-sm font-semibold text-[#1A1018] truncate">
                                        {{ $firstItem ? ($firstItem->product?->title ?? 'Store Item') : 'Order Item' }}
                                    </h4>
                                    <div class="text-[11px] text-[#7A6B78] mt-0.5">
                                        {{ $totalQty }} {{ Str::plural('item', $totalQty) }} • Total: <strong class="text-[#1A1018] tabular-nums">₹{{ number_format($order->total_amount / 100, 2) }}</strong>
                                    </div>
                                </div>
                            </div>

                            <!-- Action Buttons -->
                            <div class="flex items-center gap-2 shrink-0 self-end sm:self-auto">
                                <a href="{{ route('orders.show', $order->id) }}" 
                                   class="px-3.5 py-1.5 rounded-full border border-black/15 hover:bg-[#FAF8F5] text-xs font-semibold text-[#1A1018] transition">
                                    View Order
                                </a>
                                <a href="{{ route('orders.index') }}" 
                                   class="px-4 py-1.5 rounded-full bg-[#1A1018] hover:bg-black text-[#ECD2F3] text-xs font-bold transition shadow-2xs">
                                    Track
                                </a>
                            </div>
                        </div>
                    @empty
                        <div class="k71-card p-10 bg-white rounded-[24px] border border-black/8 text-center space-y-2 shadow-xs">
                            <div class="text-3xl">📦</div>
                            <h4 class="grail-display text-lg font-bold text-[#1A1018]">No orders placed yet</h4>
                            <p class="text-xs text-[#7A6B78]">Your purchases and delivery progress will appear here.</p>
                            <a href="{{ route('catalog.index') }}" class="grail-btn-obsidian inline-block mt-2 text-xs">
                                Explore Catalog
                            </a>
                        </div>
                    @endforelse
                </section>

                <!-- C. WISHLIST TAB (2-COLUMN GRID MATCHING MAIN PRODUCT GRID) -->
                <section x-show="activeTab === 'wishlist'">
                    @if($wishlistItems->isNotEmpty())
                        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3.5">
                            @foreach($wishlistItems as $item)
                                @if($item->product)
                                    <div class="k71-card grail-card overflow-hidden group flex flex-col justify-between">
                                        <div>
                                            <div class="relative aspect-square bg-[#F4F1EC] overflow-hidden">
                                                <a href="{{ route('catalog.product', $item->product->slug) }}">
                                                    <img src="{{ $item->product->primary_image_url }}" 
                                                         alt="{{ $item->product->title }}" 
                                                         class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                                                </a>
                                                <button type="button" 
                                                        onclick="window.toggleProductWishlist({{ $item->product_id }}); this.closest('.k71-card').remove();"
                                                        class="absolute top-2.5 right-2.5 w-7 h-7 rounded-full bg-white/95 text-rose-500 hover:bg-rose-500 hover:text-white flex items-center justify-center shadow-xs cursor-pointer transition-colors"
                                                        aria-label="Remove from wishlist">
                                                    ✕
                                                </button>
                                            </div>
                                            <div class="p-3">
                                                <a href="{{ route('catalog.product', $item->product->slug) }}" class="block">
                                                    <h4 class="text-xs font-semibold text-[#1A1018] line-clamp-2 min-h-[2rem]">
                                                        {{ $item->product->title }}
                                                    </h4>
                                                </a>
                                                <div class="mt-1 font-bold text-xs text-[#1A1018] tabular-nums">
                                                    ₹{{ number_format($item->product->base_price / 100, 2) }}
                                                </div>
                                            </div>
                                        </div>
                                        <div class="p-3 pt-0">
                                            <a href="{{ route('catalog.product', $item->product->slug) }}" 
                                               class="w-full block py-2 rounded-full bg-[#1A1018] hover:bg-black text-[#ECD2F3] text-[11px] font-bold text-center transition">
                                                View Item
                                            </a>
                                        </div>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    @else
                        <div class="k71-card p-10 bg-white rounded-[24px] border border-black/8 text-center space-y-2 shadow-xs">
                            <div class="text-3xl">❤️</div>
                            <h4 class="grail-display text-lg font-bold text-[#1A1018]">Your wishlist is empty</h4>
                            <p class="text-xs text-[#7A6B78]">Save items you love to revisit or purchase later.</p>
                            <a href="{{ route('catalog.index') }}" class="grail-btn-obsidian inline-block mt-2 text-xs">
                                Explore Products
                            </a>
                        </div>
                    @endif
                </section>
            </main>
        </div>
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
