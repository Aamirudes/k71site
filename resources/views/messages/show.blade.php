<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover, interactive-widget=resizes-content">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $conversation->subject ?? 'Conversation' }} — K71 Direct</title>
    <meta name="description" content="Real-time direct chat on K71.">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="preconnect" href="https://api.fontshare.com" crossorigin>
    <link href="https://api.fontshare.com/v2/css?f[]=switzer@300,400,500,600,700,800&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,600;0,700;1,400&family=Outfit:wght@400;500;600;700&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        [x-cloak] { display: none !important; }
        html, body {
            height: 100%;
            height: 100dvh;
            margin: 0;
            padding: 0;
            overflow: hidden;
            background-color: #000000;
            color: #EFE9F2;
            font-family: var(--font-grail-sans, 'Switzer', system-ui, sans-serif);
        }

        .chat-app-container {
            height: 100dvh;
            max-height: 100dvh;
            width: 100%;
            overflow: hidden;
            display: grid;
            grid-template-rows: clamp(56px, 7dvh, 66px) minmax(0, 1fr) auto;
            background-color: #0D0D0D;
        }

        .chat-messages-viewport {
            min-height: 0;
            overflow-y: auto;
            overscroll-behavior: contain;
            -webkit-overflow-scrolling: touch;
        }

        .bubble-sent {
            background-color: #ECD2F3;
            color: #000000;
            border-radius: 18px 18px 4px 18px;
        }

        .bubble-received {
            background-color: #1B1B1B;
            color: #EFE9F2;
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 18px 18px 18px 4px;
        }
    </style>
</head>
<body class="k71-chat-page grail-marketplace-body antialiased selection:bg-[#ECD2F3] selection:text-[#1A1018]">

    <div class="k71-chat chat-app-container max-w-4xl mx-auto border-x border-white/10 bg-[#0D0D0D] shadow-2xl"
         x-data="{
             init() {
                 this.scrollToBottom();
                 window.addEventListener('resize', () => this.scrollToBottom());
                 const feed = this.$refs.messagesFeed;
                 if (feed && 'MutationObserver' in window) {
                     const observer = new MutationObserver(() => this.scrollToBottom());
                     observer.observe(feed, { childList: true, subtree: true });
                 }
             },
             scrollToBottom() {
                 this.$nextTick(() => {
                     const feed = this.$refs.messagesFeed;
                     if (feed) {
                         feed.scrollTop = feed.scrollHeight;
                     }
                 });
             }
         }">

        <!-- ═══════════════════════════════════════════════════════════════════
             2. FIXED HEADER (NEVER SCROLLS)
             ═══════════════════════════════════════════════════════════════════ -->
        <header class="k71-chat__head h-[clamp(56px,7dvh,66px)] px-3 sm:px-5 bg-[#161616]/95 backdrop-blur-xl border-b border-white/10 flex items-center justify-between gap-3 shrink-0 z-30 select-none">
            <div class="flex items-center gap-2.5 min-w-0">
                <!-- Back Button -->
                <a href="{{ route('messages.index') }}" 
                   class="w-10 h-10 -ml-1 rounded-full flex items-center justify-center text-white/80 hover:text-white hover:bg-white/10 active:scale-95 transition"
                   title="Back to Messages"
                   aria-label="Back to Messages">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
                </a>

                <!-- Recipient Avatar with Online Badge -->
                <div class="relative shrink-0">
                    @if($conversation->vendor && $conversation->vendor->logo_url)
                        <img src="{{ $conversation->vendor->logo_url }}" alt="{{ $conversation->vendor->shop_name }}" class="w-9 h-9 sm:w-10 sm:h-10 rounded-full object-cover border border-white/15 shadow-2xs">
                    @else
                        <div class="w-9 h-9 sm:w-10 sm:h-10 rounded-full bg-[#ECD2F3] text-[#1A1018] flex items-center justify-center font-bold text-xs shadow-2xs">
                            {{ substr($conversation->vendor?->shop_name ?? 'S', 0, 1) }}
                        </div>
                    @endif
                    <span class="absolute bottom-0 right-0 w-2.5 h-2.5 rounded-full bg-emerald-400 border-2 border-[#161616]"></span>
                </div>

                <!-- Recipient Name & Online Status -->
                <div class="min-w-0">
                    <div class="flex items-center gap-1.5 truncate">
                        <h1 class="text-sm sm:text-base font-semibold text-white truncate leading-tight">
                            {{ $conversation->vendor?->shop_name ?? 'Verified Seller' }}
                        </h1>
                        @if($conversation->vendor)
                            <span class="hidden sm:inline-flex px-2 py-0.5 rounded-full text-[9.5px] font-semibold bg-emerald-500/15 text-emerald-300 border border-emerald-400/25 shrink-0">
                                ✓ Verified
                            </span>
                        @endif
                    </div>
                    <div class="text-[11px] text-[#EFE9F2]/55 flex items-center gap-1 leading-tight">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                        <span>Active now</span>
                        @if($conversation->subject)
                            <span class="hidden md:inline text-[#EFE9F2]/40 truncate">• {{ Str::limit($conversation->subject, 30) }}</span>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Action Overflow Menu & Product Attachment -->
            <div class="flex items-center gap-1.5 shrink-0">
                @if($conversation->product)
                    <a href="{{ route('catalog.product', $conversation->product->slug) }}" 
                       class="hidden sm:inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-white/10 text-xs text-[#EFE9F2] hover:bg-white/15 transition font-medium border border-white/10">
                        <span>📦</span>
                        <span class="truncate max-w-[120px]">{{ $conversation->product->title }}</span>
                    </a>
                @endif

                <!-- Storefront link or more options -->
                @if($conversation->vendor)
                    <a href="{{ $conversation->vendor->storefront_url }}" 
                       class="px-3.5 py-1.5 rounded-full border border-white/15 bg-white/5 text-[#ECD2F3] hover:bg-white/10 text-xs font-semibold transition"
                       title="View Storefront">
                        Store
                    </a>
                @endif
            </div>
        </header>

        <!-- ═══════════════════════════════════════════════════════════════════
             3. MESSAGE LIST (THE ONLY SCROLLING ELEMENT)
             ═══════════════════════════════════════════════════════════════════ -->
        <main x-ref="messagesFeed" 
              class="k71-chat__body k71-chat__list chat-messages-viewport px-3 sm:px-5 py-4 space-y-3 bg-[#090909]">

            @if(session('success'))
                <div class="p-3 rounded-2xl bg-emerald-500/15 border border-emerald-400/25 text-emerald-300 text-xs font-medium flex items-center gap-2">
                    <span>✓</span>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            <!-- Attached Product Banner (if conversation initiated on product) -->
            @if($conversation->product)
                <div class="p-3 rounded-[18px] bg-[#161616] border border-white/10 flex items-center gap-3 shadow-sm">
                    <img src="{{ $conversation->product->primary_image_url ?? asset('images/product-placeholder.svg') }}" 
                         alt="{{ $conversation->product->title }}" 
                         class="w-12 h-12 rounded-xl object-cover bg-white/5 shrink-0">
                    <div class="min-w-0 flex-1">
                        <span class="text-[10px] text-[#C9A24A] font-semibold uppercase tracking-[0.14em] block">Inquiring about</span>
                        <h4 class="text-xs font-semibold text-white truncate">{{ $conversation->product->title }}</h4>
                        <span class="text-xs font-bold text-[#ECD2F3]">₹{{ number_format($conversation->product->base_price / 100, 2) }}</span>
                    </div>
                    <a href="{{ route('catalog.product', $conversation->product->slug) }}" 
                       class="px-3.5 py-1.5 rounded-full bg-[#ECD2F3] hover:bg-[#FFCDFF] text-xs font-semibold text-[#1A1018] shrink-0 transition">
                        View Item
                    </a>
                </div>
            @endif

            <!-- Conversation Messages -->
            @forelse($conversation->messages as $msg)
                @if($msg->isSenderUser())
                    <!-- Sent Message (Right Aligned, Grail Orchid) -->
                    <div class="flex justify-end">
                        <div class="max-w-[78%]">
                            <div class="bubble-sent px-4 py-2.5 text-[14px] leading-relaxed break-words shadow-xs font-medium">
                                {{ $msg->body }}
                            </div>
                            <div class="text-[10.5px] text-[#EFE9F2]/45 text-right mt-1 font-medium pr-1">
                                {{ $msg->created_at->format('h:i A') }}
                            </div>
                        </div>
                    </div>
                @else
                    <!-- Received Message (Left Aligned, Dark Surface with Subtle Border) -->
                    <div class="flex justify-start items-end gap-2">
                        <div class="w-6 h-6 rounded-full bg-white/10 text-[#ECD2F3] flex items-center justify-center font-bold text-[10px] shrink-0 mb-4 select-none border border-white/10">
                            {{ substr($conversation->vendor->shop_name ?? 'S', 0, 1) }}
                        </div>
                        <div class="max-w-[78%]">
                            <div class="bubble-received px-4 py-2.5 text-[14px] leading-relaxed break-words shadow-xs">
                                {{ $msg->body }}
                            </div>
                            <div class="text-[10.5px] text-[#EFE9F2]/45 mt-1 font-medium pl-1">
                                {{ $conversation->vendor->shop_name ?? 'Seller' }} • {{ $msg->created_at->format('h:i A') }}
                            </div>
                        </div>
                    </div>
                @endif
            @empty
                <div class="py-16 text-center text-[#EFE9F2]/50 text-xs">
                    <p class="font-semibold text-white">No messages yet</p>
                    <p class="mt-1">Send a message below to start chatting with {{ $conversation->vendor->shop_name ?? 'the seller' }}.</p>
                </div>
            @endforelse

        </main>

        <!-- ═══════════════════════════════════════════════════════════════════
             4. COMPOSER BAR (PINNED BOTTOM, STAYS ABOVE KEYBOARD)
             ═══════════════════════════════════════════════════════════════════ -->
        <footer class="k71-chat__composer p-2.5 sm:p-3.5 bg-[#161616] border-t border-white/10 shrink-0 z-30 pb-[max(0.6rem,env(safe-area-inset-bottom))]">
            <form action="{{ route('messages.reply', $conversation->id) }}" 
                  method="POST" 
                  class="flex items-center gap-2"
                  @submit="scrollToBottom()">
                @csrf
                <div class="flex-1 flex items-center bg-white/5 rounded-full border border-white/15 px-4 focus-within:border-[#ECD2F3] focus-within:bg-white/10 transition">
                    <input type="text" 
                           name="body" 
                           required 
                           autocomplete="off"
                           placeholder="Type a message..."
                           class="w-full bg-transparent border-none text-white text-[15px] leading-normal min-h-[44px] focus:outline-none placeholder:text-white/40">
                </div>
                <button type="submit" 
                        class="w-11 h-11 rounded-full bg-[#ECD2F3] hover:bg-[#FFCDFF] text-[#1A1018] active:scale-95 transition flex items-center justify-center shrink-0 cursor-pointer shadow-xs"
                        aria-label="Send message">
                    <svg class="w-5 h-5 ml-0.5" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 12L3.269 3.126A59.768 59.768 0 0121.485 12 59.77 59.77 0 013.27 20.876L5.999 12zm0 0h7.5"/></svg>
                </button>
            </form>
        </footer>

    </div>

</body>
</html>
