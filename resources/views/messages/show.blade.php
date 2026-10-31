<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="light" data-brand="{{ $currentBrand ?? 'main' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover, interactive-widget=resizes-content">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $conversation->subject ?? 'Conversation' }} — K71 Direct</title>
    <meta name="description" content="Real-time direct chat on K71.">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <x-k71-design-system />

    <style>
        [x-cloak] { display: none !important; }
        html, body {
            height: 100%;
            height: 100dvh;
            margin: 0;
            padding: 0;
            overflow: hidden;
            background-color: var(--bg, #F6F7FB);
            color: var(--text, #0F1222);
            font-family: var(--font-body, 'Plus Jakarta Sans', system-ui, sans-serif);
        }

        .chat-app-container {
            height: 100dvh;
            max-height: 100dvh;
            width: 100%;
            overflow: hidden;
            display: grid;
            grid-template-rows: clamp(52px, 6dvh, 60px) minmax(0, 1fr) auto;
            background-color: var(--bg, #F6F7FB);
        }

        .chat-messages-viewport {
            min-height: 0;
            overflow-y: auto;
            overscroll-behavior: contain;
            -webkit-overflow-scrolling: touch;
        }

        .bubble-sent {
            background-color: var(--primary, #4338CA);
            color: var(--on-primary, #FFFFFF);
            border-radius: 16px 16px 2px 16px;
        }

        .bubble-received {
            background-color: var(--surface, #FFFFFF);
            color: var(--text, #0F1222);
            border: 1px solid var(--border, #E2E8F0);
            border-radius: 16px 16px 16px 2px;
        }
    </style>
</head>
<body class="antialiased selection:bg-indigo-600 selection:text-white">

    <div class="chat-app-container max-w-4xl mx-auto border-x border-slate-200/80 dark:border-slate-800 bg-white dark:bg-slate-900 shadow-sm"
         x-data="{
             init() {
                 this.scrollToBottom();
                 window.addEventListener('resize', () => this.scrollToBottom());
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
        <header class="h-[clamp(52px,6dvh,60px)] px-3 sm:px-4 bg-white dark:bg-slate-900 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between gap-3 shrink-0 z-30 select-none">
            <div class="flex items-center gap-2.5 min-w-0">
                <!-- Back Button -->
                <a href="{{ route('messages.index') }}" 
                   class="w-10 h-10 -ml-1 rounded-full flex items-center justify-center text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 active:scale-95 transition"
                   title="Back to Messages"
                   aria-label="Back to Messages">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
                </a>

                <!-- Recipient Avatar with Online Badge -->
                <div class="relative shrink-0">
                    @if($conversation->vendor && $conversation->vendor->logo_url)
                        <img src="{{ $conversation->vendor->logo_url }}" alt="{{ $conversation->vendor->shop_name }}" class="w-9 h-9 sm:w-10 sm:h-10 rounded-full object-cover border border-slate-200 dark:border-slate-700 shadow-2xs">
                    @else
                        <div class="w-9 h-9 sm:w-10 sm:h-10 rounded-full bg-slate-900 text-white flex items-center justify-center font-bold text-xs shadow-2xs">
                            {{ substr($conversation->vendor->shop_name ?? 'S', 0, 1) }}
                        </div>
                    @endif
                    <span class="absolute bottom-0 right-0 w-2.5 h-2.5 rounded-full bg-emerald-500 border-2 border-white dark:border-slate-900"></span>
                </div>

                <!-- Recipient Name & Online Status -->
                <div class="min-w-0">
                    <div class="flex items-center gap-1.5 truncate">
                        <h1 class="text-sm sm:text-base font-bold text-slate-900 dark:text-white truncate leading-tight">
                            {{ $conversation->vendor->shop_name ?? 'Verified Seller' }}
                        </h1>
                        @if($conversation->vendor)
                            <span class="hidden sm:inline-flex px-1.5 py-0.2 rounded text-[9.5px] font-bold bg-emerald-50 text-emerald-800 border border-emerald-200 shrink-0">
                                ✓ Verified
                            </span>
                        @endif
                    </div>
                    <div class="text-[11px] text-slate-500 dark:text-slate-400 flex items-center gap-1 leading-tight">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                        <span>Active now</span>
                        @if($conversation->subject)
                            <span class="hidden md:inline text-slate-400 truncate">• {{ Str::limit($conversation->subject, 30) }}</span>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Action Overflow Menu & Product Attachment -->
            <div class="flex items-center gap-1.5 shrink-0">
                @if($conversation->product)
                    <a href="{{ route('catalog.product', $conversation->product->slug) }}" 
                       class="hidden sm:inline-flex items-center gap-1.5 px-2.5 py-1.5 rounded-lg bg-slate-100 dark:bg-slate-800 text-xs text-slate-700 dark:text-slate-200 hover:bg-slate-200 transition font-medium">
                        <span>📦</span>
                        <span class="truncate max-w-[120px]">{{ $conversation->product->title }}</span>
                    </a>
                @endif

                <!-- Storefront link or more options -->
                @if($conversation->vendor)
                    <a href="{{ route('storefront.show', $conversation->vendor->slug) }}" 
                       class="px-2.5 py-1.5 rounded-lg border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 text-xs font-semibold transition"
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
              class="chat-messages-viewport px-3 sm:px-4 py-3 space-y-3 bg-[#F8FAFC] dark:bg-slate-950/40">

            @if(session('success'))
                <div class="p-2.5 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-medium flex items-center gap-2">
                    <span>✓</span>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            <!-- Attached Product Banner (if conversation initiated on product) -->
            @if($conversation->product)
                <div class="p-2.5 rounded-xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 flex items-center gap-3 shadow-2xs">
                    <img src="{{ $conversation->product->primary_image_url ?? asset('images/product-placeholder.svg') }}" 
                         alt="{{ $conversation->product->title }}" 
                         class="w-12 h-12 rounded-lg object-cover bg-slate-100 shrink-0">
                    <div class="min-w-0 flex-1">
                        <span class="text-[10px] text-slate-400 font-semibold uppercase block">Inquiring about</span>
                        <h4 class="text-xs font-bold text-slate-900 dark:text-white truncate">{{ $conversation->product->title }}</h4>
                        <span class="text-xs font-black text-indigo-700 dark:text-indigo-400">₹{{ number_format($conversation->product->base_price / 100, 2) }}</span>
                    </div>
                    <a href="{{ route('catalog.product', $conversation->product->slug) }}" 
                       class="px-3 py-1.5 rounded-lg bg-slate-100 dark:bg-slate-700 hover:bg-slate-200 text-xs font-semibold text-slate-800 dark:text-white shrink-0">
                        View Item
                    </a>
                </div>
            @endif

            <!-- Conversation Messages -->
            @forelse($conversation->messages as $msg)
                @if($msg->isSenderUser())
                    <!-- Sent Message (Right Aligned, Brand Primary) -->
                    <div class="flex justify-end">
                        <div class="max-w-[78%]">
                            <div class="bubble-sent px-3.5 py-2.5 text-[14px] leading-relaxed break-words shadow-xs">
                                {{ $msg->body }}
                            </div>
                            <div class="text-[10.5px] text-slate-400 text-right mt-1 font-medium pr-1">
                                {{ $msg->created_at->format('h:i A') }}
                            </div>
                        </div>
                    </div>
                @else
                    <!-- Received Message (Left Aligned, Solid Surface with Border) -->
                    <div class="flex justify-start items-end gap-2">
                        <div class="w-6 h-6 rounded-full bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-300 flex items-center justify-center font-bold text-[10px] shrink-0 mb-4 select-none">
                            {{ substr($conversation->vendor->shop_name ?? 'S', 0, 1) }}
                        </div>
                        <div class="max-w-[78%]">
                            <div class="bubble-received px-3.5 py-2.5 text-[14px] leading-relaxed break-words shadow-xs">
                                {{ $msg->body }}
                            </div>
                            <div class="text-[10.5px] text-slate-400 mt-1 font-medium pl-1">
                                {{ $conversation->vendor->shop_name ?? 'Seller' }} • {{ $msg->created_at->format('h:i A') }}
                            </div>
                        </div>
                    </div>
                @endif
            @empty
                <div class="py-16 text-center text-slate-400 text-xs">
                    <p class="font-semibold text-slate-600 dark:text-slate-400">No messages yet</p>
                    <p class="mt-1">Send a message below to start chatting with {{ $conversation->vendor->shop_name ?? 'the seller' }}.</p>
                </div>
            @endforelse

        </main>

        <!-- ═══════════════════════════════════════════════════════════════════
             4. COMPOSER BAR (PINNED BOTTOM, STAYS ABOVE KEYBOARD)
             ═══════════════════════════════════════════════════════════════════ -->
        <footer class="p-2 sm:p-3 bg-white dark:bg-slate-900 border-t border-slate-200 dark:border-slate-800 shrink-0 z-30 pb-[max(0.6rem,env(safe-area-inset-bottom))]">
            <form action="{{ route('messages.reply', $conversation->id) }}" 
                  method="POST" 
                  class="flex items-center gap-2"
                  @submit="scrollToBottom()">
                @csrf
                <div class="flex-1 flex items-center bg-slate-100 dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 px-3.5 focus-within:border-indigo-600 focus-within:bg-white dark:focus-within:bg-slate-800 transition">
                    <input type="text" 
                           name="body" 
                           required 
                           autocomplete="off"
                           placeholder="Type a message..."
                           class="w-full bg-transparent border-none text-slate-900 dark:text-white text-[16px] leading-normal min-h-[44px] focus:outline-none placeholder:text-slate-400">
                </div>
                <button type="submit" 
                        class="w-11 h-11 rounded-xl bg-slate-900 hover:bg-black text-white active:scale-95 transition flex items-center justify-center shrink-0 cursor-pointer shadow-xs"
                        style="background-color: var(--primary); color: var(--on-primary);"
                        aria-label="Send message">
                    <svg class="w-5 h-5 ml-0.5" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 12L3.269 3.126A59.768 59.768 0 0121.485 12 59.77 59.77 0 013.27 20.876L5.999 12zm0 0h7.5"/></svg>
                </button>
            </form>
        </footer>

    </div>

</body>
</html>
