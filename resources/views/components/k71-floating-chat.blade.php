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

    <!-- Expandable Liquid Glass Quick-Chat Flyout -->
    <div x-show="chatOpen" 
         x-cloak
         @click.outside="chatOpen = false"
         x-transition:enter="transition cubic-bezier(0.16, 1, 0.3, 1) duration-250 transform"
         x-transition:enter-start="opacity-0 translate-y-4 scale-95"
         x-transition:enter-end="opacity-100 translate-y-0 scale-100"
         x-transition:leave="transition cubic-bezier(0.16, 1, 0.3, 1) duration-180 transform"
         x-transition:leave-start="opacity-100 translate-y-0 scale-100"
         x-transition:leave-end="opacity-0 translate-y-4 scale-95"
         class="absolute bottom-16 right-0 w-[320px] sm:w-[360px] rounded-3xl p-5 liquid-glass-tile border border-white/60 dark:border-white/15 shadow-2xl backdrop-blur-2xl text-zinc-900 dark:text-zinc-100">
        
        <!-- Header -->
        <div class="flex items-center justify-between pb-3.5 border-b border-zinc-200/60 dark:border-zinc-800/80">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-xl bg-gradient-to-tr from-violet-600 to-indigo-500 text-white flex items-center justify-center text-sm shadow-md shadow-violet-500/20">
                    💬
                </div>
                <div>
                    <h3 class="text-sm font-black tracking-tight leading-none text-zinc-950 dark:text-white">Direct Messages</h3>
                    <p class="text-[11px] text-zinc-500 dark:text-zinc-400 mt-0.5">Real-time buyer & seller chats</p>
                </div>
            </div>
            <button @click="chatOpen = false" 
                    type="button" 
                    class="w-7 h-7 rounded-full bg-zinc-200/60 dark:bg-zinc-800/60 text-zinc-500 hover:text-zinc-950 dark:hover:text-white flex items-center justify-center transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <!-- Body Options -->
        <div class="py-3.5 space-y-2">
            @auth
                <!-- Main Inbox Quick Link -->
                <a href="{{ route('messages.index') }}" 
                   class="flex items-center justify-between p-3 rounded-2xl bg-white/70 dark:bg-zinc-800/60 hover:bg-violet-50 dark:hover:bg-violet-950/40 border border-zinc-200/60 dark:border-zinc-700/60 transition-all group">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-xl bg-indigo-50 dark:bg-indigo-950/50 text-indigo-600 dark:text-indigo-400 flex items-center justify-center text-sm font-bold group-hover:scale-105 transition-transform">
                            📥
                        </div>
                        <div class="text-left">
                            <div class="text-xs font-bold text-zinc-900 dark:text-white group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors">
                                Open Full Chat Inbox
                            </div>
                            <div class="text-[10.5px] text-zinc-500 dark:text-zinc-400">View all conversations & requests</div>
                        </div>
                    </div>
                    @if($unreadChatCount > 0)
                        <span class="px-2 py-0.5 rounded-full bg-violet-600 text-white text-[10px] font-black animate-pulse">
                            {{ $unreadChatCount }} new
                        </span>
                    @else
                        <svg class="w-4 h-4 text-zinc-400 group-hover:translate-x-0.5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    @endif
                </a>

                @if($currentVendor)
                    <!-- Fast Vendor Contact Button -->
                    <a href="{{ route('messages.index') }}?chat_with={{ $currentVendor->user_id ?? $currentVendor->id }}" 
                       class="flex items-center justify-between p-3 rounded-2xl bg-white/70 dark:bg-zinc-800/60 hover:bg-emerald-50 dark:hover:bg-emerald-950/40 border border-zinc-200/60 dark:border-zinc-700/60 transition-all group">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-xl bg-emerald-50 dark:bg-emerald-950/50 text-emerald-600 dark:text-emerald-400 flex items-center justify-center text-sm font-bold group-hover:scale-105 transition-transform">
                                🏪
                            </div>
                            <div class="text-left">
                                <div class="text-xs font-bold text-zinc-900 dark:text-white group-hover:text-emerald-600 dark:group-hover:text-emerald-400 transition-colors">
                                    Message {{ $currentVendor->shop_name }}
                                </div>
                                <div class="text-[10.5px] text-zinc-500 dark:text-zinc-400">Ask seller about availability / delivery</div>
                            </div>
                        </div>
                        <svg class="w-4 h-4 text-zinc-400 group-hover:translate-x-0.5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </a>
                @endif

                <!-- Support Concierge -->
                <a href="{{ route('messages.index') }}?support=1" 
                   class="flex items-center justify-between p-3 rounded-2xl bg-white/70 dark:bg-zinc-800/60 hover:bg-amber-50 dark:hover:bg-amber-950/40 border border-zinc-200/60 dark:border-zinc-700/60 transition-all group">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-xl bg-amber-50 dark:bg-amber-950/50 text-amber-600 dark:text-amber-400 flex items-center justify-center text-sm font-bold group-hover:scale-105 transition-transform">
                            🛡️
                        </div>
                        <div class="text-left">
                            <div class="text-xs font-bold text-zinc-900 dark:text-white group-hover:text-amber-600 dark:group-hover:text-amber-400 transition-colors">
                                Support & Escrow Help
                            </div>
                            <div class="text-[10.5px] text-zinc-500 dark:text-zinc-400">Instant resolution for orders & disputes</div>
                        </div>
                    </div>
                    <svg class="w-4 h-4 text-zinc-400 group-hover:translate-x-0.5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </a>
            @else
                <!-- Guest View -->
                <div class="p-4 rounded-2xl bg-white/70 dark:bg-zinc-800/60 border border-zinc-200/60 dark:border-zinc-700/60 text-center">
                    <div class="text-3xl mb-2">💬</div>
                    <h4 class="text-xs font-bold text-zinc-900 dark:text-white">Sign In to Chat</h4>
                    <p class="text-[11px] text-zinc-500 dark:text-zinc-400 mt-1 mb-3.5 leading-snug">
                        Contact verified vendors, bargain on pre-loved items, or get help with your orders.
                    </p>
                    <a href="{{ route('login') }}" 
                       class="block w-full py-2.5 px-4 rounded-xl bg-gradient-to-r from-violet-600 to-indigo-600 text-white text-xs font-bold shadow-md shadow-violet-500/25 hover:brightness-110 active:scale-95 transition-all text-center">
                        Sign In Now
                    </a>
                </div>
            @endauth
        </div>

        <div class="pt-2 text-center text-[10px] text-zinc-400 dark:text-zinc-500 border-t border-zinc-200/50 dark:border-zinc-800/50">
            Encrypted direct messaging • K71 Platform
        </div>
    </div>

    <!-- Floating Trigger Capsule / Orb -->
    <button @click="chatOpen = !chatOpen" 
            type="button" 
            class="liquid-glass-launcher flex items-center gap-2.5 px-4 py-3 rounded-full cursor-pointer group focus:outline-none focus:ring-2 focus:ring-violet-500"
            :class="chatOpen ? 'ring-2 ring-violet-500' : ''"
            aria-label="Open Direct Messages">
        
        <div class="relative flex items-center justify-center">
            <!-- Sleek Glass Message Icon -->
            <svg class="w-5 h-5 text-violet-600 dark:text-violet-400 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.625 12a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H8.25m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H12m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0h-.375M21 12c0 4.556-4.03 8.25-9 8.25a9.764 9.764 0 01-2.555-.337A5.972 5.972 0 015.41 20.97a.75.75 0 01-.874-.91 4.54 4.54 0 00.91-2.122A7.95 7.95 0 013 12c0-4.556 4.03-8.25 9-8.25s9 3.694 9 8.25z" />
            </svg>

        </div>

        <span class="text-xs font-black tracking-tight text-zinc-900 dark:text-white hidden sm:inline">
            Messages
        </span>

        @if($unreadChatCount > 0)
            <span class="px-1.5 py-0.2 rounded-full bg-[#EF4444] text-white text-[10px] font-black shadow-xs">
                {{ $unreadChatCount > 9 ? '9+' : $unreadChatCount }}
            </span>
        @endif
    </button>
</div>
