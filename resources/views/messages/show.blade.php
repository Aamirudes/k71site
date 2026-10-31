<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $conversation->subject ?? 'Conversation' }} — K71 Direct</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        .direct-chat-shell {
            max-width: 900px;
            margin: 0 auto;
            min-height: calc(100vh - 80px);
            display: flex;
            flex-direction: column;
            padding: 16px;
        }
        @media (max-width: 640px) {
            .direct-chat-shell {
                padding: 8px;
                min-height: calc(100vh - 64px);
            }
        }
    </style>
</head>
<body class="bg-slate-50 dark:bg-[#060a17] text-slate-900 dark:text-slate-100 min-h-screen antialiased selection:bg-indigo-500 selection:text-white font-sans">

    <!-- Dedicated Minimalist Header: No Search Bar, No Category Slider, No Mobile Panel -->
    <header class="bg-black text-white px-4 sm:px-6 py-2.5 border-b border-white/10 flex items-center justify-between z-30 relative select-none">
        <div class="flex items-center gap-3">
            <a href="{{ url('/') }}" class="inline-flex items-center gap-1.5 text-zinc-300 hover:text-white text-xs font-semibold transition group">
                <svg class="w-4 h-4 group-hover:-translate-x-0.5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                <span>Back to Marketplace</span>
            </a>
            <span class="text-zinc-600">|</span>
            <a href="{{ route('messages.index') }}" class="text-xs font-bold text-white hover:text-emerald-400 transition flex items-center gap-1.5">
                <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                <span>All Messages</span>
            </a>
        </div>

        <div class="flex items-center gap-3 text-xs">
            <span class="text-[11px] text-zinc-400 hidden sm:inline font-mono">End-to-End Private Chat</span>
            <a href="{{ route('account') }}" class="font-semibold text-zinc-300 hover:text-white transition flex items-center gap-1.5">
                <span>Account</span>
                <span class="text-zinc-500">›</span>
            </a>
        </div>
    </header>

    <div class="direct-chat-shell">
        
        <!-- Instagram-Style Individual Chat Frame -->
        <div class="flex-1 flex flex-col bg-white/80 dark:bg-slate-900/80 backdrop-blur-2xl rounded-3xl border border-slate-200/80 dark:border-white/10 shadow-2xl overflow-hidden">
            
            <!-- Top Instagram Direct Header -->
            <div class="h-18 px-4 sm:px-6 border-b border-slate-200/80 dark:border-white/10 flex items-center justify-between bg-white/60 dark:bg-slate-900/60 backdrop-blur-xl shrink-0 gap-3">
                <div class="flex items-center gap-3 min-w-0">
                    <!-- Instagram Back Arrow Button -->
                    <a href="{{ route('messages.index') }}" 
                       class="w-10 h-10 rounded-full bg-slate-100 hover:bg-slate-200 dark:bg-white/10 dark:hover:bg-white/20 text-slate-800 dark:text-white flex items-center justify-center transition-all duration-150 hover:scale-105 shrink-0 shadow-xs" 
                       title="Back to All Conversations" 
                       aria-label="Back to All Conversations">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="19" y1="12" x2="5" y2="12"></line>
                            <polyline points="12 19 5 12 12 5"></polyline>
                        </svg>
                    </a>

                    <!-- Seller Avatar with Online Badge -->
                    <div class="relative shrink-0">
                        @if($conversation->vendor && $conversation->vendor->logo_url)
                            <img src="{{ $conversation->vendor->logo_url }}" alt="{{ $conversation->vendor->shop_name }}" class="w-11 h-11 rounded-full object-cover border-2 border-white dark:border-slate-800 shadow-md">
                        @else
                            <div class="w-11 h-11 rounded-full bg-gradient-to-tr from-indigo-600 to-purple-600 flex items-center justify-center text-white font-black text-sm shadow-md">
                                {{ substr($conversation->vendor->shop_name ?? 'S', 0, 1) }}
                            </div>
                        @endif
                        <span class="absolute bottom-0 right-0 w-3.5 h-3.5 rounded-full bg-emerald-500 border-2 border-white dark:border-slate-900 shadow-xs"></span>
                    </div>

                    <!-- Vendor Name & Status -->
                    <div class="min-w-0">
                        <div class="flex items-center gap-2">
                            <h1 class="text-sm sm:text-base font-extrabold text-slate-900 dark:text-white truncate tracking-tight">
                                {{ $conversation->vendor->shop_name ?? 'Verified Seller' }}
                            </h1>
                            <span class="hidden sm:inline-flex px-2 py-0.5 rounded-full bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 text-[10px] font-extrabold border border-emerald-500/20">
                                Verified Seller
                            </span>
                        </div>
                        <p class="text-xs text-slate-500 dark:text-slate-400 truncate flex items-center gap-1.5 font-medium">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                            <span>Active now</span>
                            @if($conversation->subject)
                                <span class="hidden md:inline">• {{ Str::limit($conversation->subject, 35) }}</span>
                            @endif
                        </p>
                    </div>
                </div>

                <!-- Product Pill if attached -->
                @if($conversation->product)
                    <div class="shrink-0 hidden sm:block">
                        <a href="{{ route('catalog.product', $conversation->product->slug) }}" 
                           class="inline-flex items-center gap-2 px-3 py-1.5 rounded-xl bg-slate-100 dark:bg-white/5 hover:bg-slate-200 dark:hover:bg-white/10 border border-slate-200 dark:border-white/10 text-xs text-slate-700 dark:text-slate-200 transition shadow-xs">
                            <span>📦</span>
                            <span class="font-bold truncate max-w-[150px]">{{ $conversation->product->title }}</span>
                            <span class="text-indigo-600 dark:text-indigo-400 font-extrabold">View →</span>
                        </a>
                    </div>
                @endif
            </div>

            @if(session('success'))
                <div class="m-3 p-3 rounded-2xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-700 dark:text-emerald-300 text-xs font-bold flex items-center gap-2">
                    <span>✓</span>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            <!-- Scrollable Messages Feed -->
            <div id="messagesFeed" class="flex-1 overflow-y-auto p-4 sm:p-6 space-y-3.5 max-h-[calc(100vh-270px)]">
                @forelse($conversation->messages as $msg)
                    @if($msg->isSenderUser())
                        <!-- User's Message (Right) -->
                        <div class="flex justify-end">
                            <div class="max-w-[82%] sm:max-w-[70%]">
                                <div class="rounded-2xl rounded-tr-xs bg-gradient-to-r from-indigo-600 to-indigo-700 text-white p-3.5 text-xs sm:text-sm leading-relaxed shadow-md shadow-indigo-600/15 break-words">
                                    {{ $msg->body }}
                                </div>
                                <div class="text-[10px] text-slate-400 dark:text-slate-500 text-right mt-1 font-semibold pr-1">
                                    You • {{ $msg->created_at->format('h:i A') }}
                                </div>
                            </div>
                        </div>
                    @else
                        <!-- Vendor's Message (Left) -->
                        <div class="flex justify-start items-end gap-2.5">
                            <div class="w-7 h-7 rounded-full bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-200 flex items-center justify-center font-bold text-[11px] shrink-0 mb-4">
                                {{ substr($conversation->vendor->shop_name ?? 'S', 0, 1) }}
                            </div>
                            <div class="max-w-[82%] sm:max-w-[70%]">
                                <div class="rounded-2xl rounded-tl-xs bg-slate-100 dark:bg-slate-800 text-slate-900 dark:text-slate-100 p-3.5 text-xs sm:text-sm leading-relaxed border border-slate-200/60 dark:border-slate-700/80 shadow-xs break-words">
                                    {{ $msg->body }}
                                </div>
                                <div class="text-[10px] text-slate-400 dark:text-slate-500 mt-1 font-semibold pl-1">
                                    {{ $conversation->vendor->shop_name ?? 'Seller' }} • {{ $msg->created_at->format('h:i A') }}
                                </div>
                            </div>
                        </div>
                    @endif
                @empty
                    <div class="py-16 text-center text-slate-400 text-xs">
                        No messages yet in this conversation. Send a message below to start chatting!
                    </div>
                @endforelse
            </div>

            <!-- Sticky Bottom Composer Bar -->
            <div class="p-3 sm:p-4 bg-white/90 dark:bg-slate-900/90 border-t border-slate-200/80 dark:border-white/10 shrink-0">
                <form action="{{ route('messages.reply', $conversation->id) }}" method="POST" class="flex items-center gap-2">
                    @csrf
                    <div class="flex-1 flex items-center bg-slate-100 dark:bg-slate-800/80 rounded-2xl border border-slate-200 dark:border-slate-700/80 px-4 py-1.5 focus-within:border-indigo-600 focus-within:ring-2 focus-within:ring-indigo-600/20 transition-all">
                        <input type="text" 
                               name="body" 
                               required 
                               autocomplete="off"
                               placeholder="Message {{ $conversation->vendor->shop_name ?? 'seller' }}..."
                               class="w-full bg-transparent border-none text-slate-900 dark:text-white text-xs sm:text-sm focus:outline-none placeholder:text-slate-400 dark:placeholder:text-slate-500 py-1.5">
                    </div>
                    <button type="submit" 
                            class="px-5 py-3 rounded-2xl bg-gradient-to-r from-indigo-600 to-indigo-700 hover:from-indigo-500 hover:to-indigo-600 text-white text-xs font-extrabold transition shadow-lg shadow-indigo-600/25 shrink-0 flex items-center gap-1.5 active:scale-95 cursor-pointer">
                        <span>Send</span>
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="22" y1="2" x2="11" y2="13"></line>
                            <polygon points="22 2 15 22 11 13 2 9 22 2"></polygon>
                        </svg>
                    </button>
                </form>
            </div>

        </div>

    </div>

    <script>
        // Auto-scroll feed to bottom
        (function() {
            const feed = document.getElementById('messagesFeed');
            if (feed) {
                feed.scrollTop = feed.scrollHeight;
            }
        })();
    </script>
</body>
</html>
