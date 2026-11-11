@php
    $unreadChatCount = 0;
    if (auth()->check()) {
        try {
            $uid = (int) auth()->id();
            if (\Illuminate\Support\Facades\Schema::hasTable('chat_messages') && \Illuminate\Support\Facades\Schema::hasTable('chat_conversations')) {
                $unreadChatCount = (int) \Illuminate\Support\Facades\DB::table('chat_messages')
                    ->join('chat_conversations', 'chat_messages.conversation_id', '=', 'chat_conversations.id')
                    ->where(function ($q) use ($uid) {
                        $q->where('chat_conversations.user_one_id', $uid)
                          ->orWhere('chat_conversations.user_two_id', $uid);
                    })
                    ->where('chat_messages.sender_id', '!=', $uid)
                    ->where('chat_messages.is_read', 0)
                    ->count();
            }
            if (\Illuminate\Support\Facades\Schema::hasTable('messages')) {
                $unreadChatCount += (int) \App\Models\Message::where('recipient_id', $uid)->whereNull('read_at')->count();
            }
        } catch (\Throwable $e) {
            $unreadChatCount = 0;
        }
    }
    $currentVendor = app()->bound('current_vendor') ? app('current_vendor') : null;
    $isDetailPage = request()->routeIs('services.show') 
        || request()->routeIs('catalog.show') 
        || request()->routeIs('product.show')
        || request()->routeIs('food.restaurant') 
        || request()->routeIs('cart.*') 
        || request()->routeIs('checkout.*') 
        || request()->routeIs('services.order.*');
@endphp

<div x-data="{ 
        chatOpen: false, 
        unread: {{ (int)$unreadChatCount }} 
     }" 
     class="{{ $isDetailPage ? 'hidden md:block' : '' }} fixed bottom-20 md:bottom-6 right-4 sm:right-6 z-40 select-none"
     @keydown.escape.window="chatOpen = false">

    <!-- Expandable Grail Obsidian Quick-Chat Flyout -->
    <div x-show="chatOpen" 
         x-cloak
         @click.outside="chatOpen = false"
         x-transition:enter="transition cubic-bezier(0.16, 1, 0.3, 1) duration-250 transform"
         x-transition:enter-start="opacity-0 translate-y-4 scale-95"
         x-transition:enter-end="opacity-100 translate-y-0 scale-100"
         x-transition:leave="transition cubic-bezier(0.16, 1, 0.3, 1) duration-180 transform"
         x-transition:leave-start="opacity-100 translate-y-0 scale-100"
         x-transition:leave-end="opacity-0 translate-y-4 scale-95"
         class="absolute bottom-16 right-0 w-[320px] sm:w-[360px] rounded-3xl p-5 bg-[#0D0D0D]/95 border border-white/15 shadow-[0_24px_60px_rgba(0,0,0,0.65)] backdrop-blur-2xl text-[#EFE9F2]">
        
        <!-- Header -->
        <div class="flex items-center justify-between pb-3.5 border-b border-white/10">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-xl bg-[#ECD2F3]/15 border border-[#ECD2F3]/30 text-[#ECD2F3] flex items-center justify-center text-sm shadow-sm">
                    💬
                </div>
                <div>
                    <h3 class="text-sm font-bold tracking-tight leading-none text-white">Direct Messages</h3>
                    <p class="text-[11px] text-[#EFE9F2]/60 mt-0.5">Real-time buyer &amp; seller chats</p>
                </div>
            </div>
            <button @click="chatOpen = false" 
                    type="button" 
                    class="w-7 h-7 rounded-full bg-white/10 text-[#EFE9F2]/70 hover:text-white hover:bg-white/20 flex items-center justify-center transition-colors cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <!-- Body Options -->
        <div class="py-3.5 space-y-2">
            @auth
                <!-- Main Inbox Quick Link -->
                <a href="{{ route('messages.index') }}" 
                   class="flex items-center justify-between p-3 rounded-2xl bg-white/5 hover:bg-[#ECD2F3]/10 border border-white/10 hover:border-[#ECD2F3]/35 transition-all group">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-xl bg-[#ECD2F3]/15 text-[#ECD2F3] flex items-center justify-center text-sm font-bold group-hover:scale-105 transition-transform">
                            📥
                        </div>
                        <div class="text-left">
                            <div class="text-xs font-bold text-white group-hover:text-[#ECD2F3] transition-colors">
                                Open Full Chat Inbox
                            </div>
                            <div class="text-[10.5px] text-[#EFE9F2]/55">View all conversations &amp; requests</div>
                        </div>
                    </div>
                    @if($unreadChatCount > 0)
                        <span class="px-2 py-0.5 rounded-full bg-[#ECD2F3] text-[#1A1018] text-[10px] font-bold animate-pulse">
                            {{ $unreadChatCount }} new
                        </span>
                    @else
                        <svg class="w-4 h-4 text-[#EFE9F2]/50 group-hover:text-[#ECD2F3] group-hover:translate-x-0.5 transition-all" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    @endif
                </a>

                @if($currentVendor)
                    <!-- Fast Vendor Contact Button -->
                    <a href="{{ route('messages.index') }}?chat_with={{ $currentVendor->user_id ?? $currentVendor->id }}" 
                       class="flex items-center justify-between p-3 rounded-2xl bg-white/5 hover:bg-emerald-500/10 border border-white/10 hover:border-emerald-400/35 transition-all group">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-xl bg-emerald-500/15 text-emerald-300 flex items-center justify-center text-sm font-bold group-hover:scale-105 transition-transform">
                                🏪
                            </div>
                            <div class="text-left">
                                <div class="text-xs font-bold text-white group-hover:text-emerald-300 transition-colors">
                                    Message {{ $currentVendor->shop_name }}
                                </div>
                                <div class="text-[10.5px] text-[#EFE9F2]/55">Ask seller about availability / delivery</div>
                            </div>
                        </div>
                        <svg class="w-4 h-4 text-[#EFE9F2]/50 group-hover:text-emerald-300 group-hover:translate-x-0.5 transition-all" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </a>
                @endif

                <!-- Support Concierge -->
                <a href="{{ route('messages.index') }}?support=1" 
                   class="flex items-center justify-between p-3 rounded-2xl bg-white/5 hover:bg-[#C9A24A]/10 border border-white/10 hover:border-[#C9A24A]/35 transition-all group">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-xl bg-[#C9A24A]/15 text-[#C9A24A] flex items-center justify-center text-sm font-bold group-hover:scale-105 transition-transform">
                            🛡️
                        </div>
                        <div class="text-left">
                            <div class="text-xs font-bold text-white group-hover:text-[#C9A24A] transition-colors">
                                Support &amp; Escrow Help
                            </div>
                            <div class="text-[10.5px] text-[#EFE9F2]/55">Instant resolution for orders &amp; disputes</div>
                        </div>
                    </div>
                    <svg class="w-4 h-4 text-[#EFE9F2]/50 group-hover:text-[#C9A24A] group-hover:translate-x-0.5 transition-all" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </a>
            @else
                <!-- Guest View -->
                <div class="p-4 rounded-2xl bg-white/5 border border-white/10 text-center">
                    <div class="text-3xl mb-2">💬</div>
                    <h4 class="text-xs font-bold text-white">Sign In to Chat</h4>
                    <p class="text-[11px] text-[#EFE9F2]/60 mt-1 mb-3.5 leading-snug">
                        Contact verified vendors, bargain on pre-loved items, or get help with your orders.
                    </p>
                    <a href="{{ route('login') }}" 
                       class="grail-cta-pill block w-full py-2.5 px-4 text-xs text-center">
                        Sign In Now
                    </a>
                </div>
            @endauth
        </div>

        <div class="pt-2 text-center text-[10px] text-[#EFE9F2]/45 border-t border-white/10">
            Encrypted direct messaging • K71 Platform
        </div>
    </div>

    <!-- Floating Trigger Capsule / Orb -->
    <button @click="chatOpen = !chatOpen" 
            type="button" 
            class="bg-[#0D0D0D]/92 backdrop-blur-xl border border-white/15 hover:border-[#ECD2F3]/50 shadow-[0_14px_34px_rgba(0,0,0,0.55)] flex items-center gap-2.5 px-4 py-3 rounded-full cursor-pointer group focus:outline-none transition-all duration-200 hover:-translate-y-0.5"
            :class="chatOpen ? 'ring-2 ring-[#ECD2F3]' : ''"
            aria-label="Open Direct Messages">
        
        <div class="relative flex items-center justify-center">
            <!-- Sleek Obsidian/Orchid Message Icon -->
            <svg class="w-5 h-5 text-[#ECD2F3] group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.625 12a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H8.25m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H12m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0h-.375M21 12c0 4.556-4.03 8.25-9 8.25a9.764 9.764 0 01-2.555-.337A5.972 5.972 0 015.41 20.97a.75.75 0 01-.874-.91 4.54 4.54 0 00.91-2.122A7.95 7.95 0 013 12c0-4.556 4.03-8.25 9-8.25s9 3.694 9 8.25z" />
            </svg>

        </div>

        <span class="text-xs font-bold tracking-tight text-[#EFE9F2] hidden sm:inline">
            Messages
        </span>

        @if($unreadChatCount > 0)
            <span class="px-1.5 py-0.2 rounded-full bg-[#ECD2F3] text-[#1A1018] text-[10px] font-bold shadow-xs">
                {{ $unreadChatCount > 9 ? '9+' : $unreadChatCount }}
            </span>
        @endif
    </button>
</div>
