<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $conversation->subject ?? 'Conversation' }} — K71 Messages</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-[#0b0f19] text-slate-100 min-h-screen font-sans pb-24 antialiased selection:bg-violet-500 selection:text-white">

    <!-- Top Navigation -->
    <x-k71-nav />

    <div class="max-w-4xl mx-auto px-4 sm:px-6 py-6">
        <!-- Back Link & Header Card -->
        <div class="mb-4">
            <a href="{{ route('messages.index') }}" class="inline-flex items-center gap-2 text-xs font-semibold text-slate-400 hover:text-white transition mb-3">
                <span>←</span>
                <span>Back to All Conversations</span>
            </a>

            <div class="rounded-2xl bg-white/[0.04] border border-white/10 p-4 sm:p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div class="flex items-center gap-3.5">
                    <div class="w-12 h-12 rounded-xl bg-violet-500/10 border border-violet-500/30 flex items-center justify-center text-violet-400 font-bold shrink-0 text-lg">
                        {{ substr($conversation->vendor->shop_name ?? 'Vendor', 0, 1) }}
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <h1 class="text-base font-bold text-white">
                                {{ $conversation->vendor->shop_name ?? 'Vendor' }}
                            </h1>
                            <span class="px-2 py-0.5 rounded-full bg-emerald-500/10 text-emerald-400 border border-emerald-500/30 text-[10px] font-bold">
                                Verified Seller
                            </span>
                        </div>
                        <p class="text-xs text-slate-300 mt-0.5 font-medium">{{ $conversation->subject }}</p>
                    </div>
                </div>

                @if($conversation->product)
                    <div class="shrink-0">
                        <a href="{{ route('catalog.product', $conversation->product->slug) }}" class="inline-flex items-center gap-2 px-3 py-1.5 rounded-xl bg-white/5 hover:bg-white/10 border border-white/10 text-xs text-slate-200 transition">
                            <span>📦</span>
                            <span class="font-medium truncate max-w-[200px]">{{ $conversation->product->title }}</span>
                            <span class="text-[10px] text-violet-400 font-bold">View →</span>
                        </a>
                    </div>
                @endif
            </div>
        </div>

        @if(session('success'))
            <div class="mb-4 p-3 rounded-xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 text-xs font-semibold">
                {{ session('success') }}
            </div>
        @endif

        <!-- Message Thread Box -->
        <div class="rounded-3xl bg-white/[0.02] border border-white/10 overflow-hidden shadow-2xl flex flex-col h-[550px]">
            <!-- Messages Scroll Area -->
            <div class="flex-1 overflow-y-auto p-4 sm:p-6 space-y-4">
                @foreach($conversation->messages as $msg)
                    @if($msg->isSenderUser())
                        <!-- User's Message (Right) -->
                        <div class="flex justify-end">
                            <div class="max-w-[80%] sm:max-w-[70%]">
                                <div class="rounded-2xl rounded-tr-xs bg-violet-600 text-white p-3.5 text-xs sm:text-sm leading-relaxed shadow-md">
                                    {{ $msg->body }}
                                </div>
                                <div class="text-[10px] text-slate-500 text-right mt-1 font-mono">
                                    You • {{ $msg->created_at->format('h:i A') }}
                                </div>
                            </div>
                        </div>
                    @else
                        <!-- Vendor's Message (Left) -->
                        <div class="flex justify-start items-start gap-2.5">
                            <div class="w-7 h-7 rounded-lg bg-amber-500/10 border border-amber-500/30 flex items-center justify-center text-amber-400 font-bold text-[10px] shrink-0 mt-1">
                                {{ substr($conversation->vendor->shop_name ?? 'S', 0, 1) }}
                            </div>
                            <div class="max-w-[80%] sm:max-w-[70%]">
                                <div class="rounded-2xl rounded-tl-xs bg-slate-800 text-slate-100 p-3.5 text-xs sm:text-sm leading-relaxed border border-slate-700/80 shadow-md">
                                    {{ $msg->body }}
                                </div>
                                <div class="text-[10px] text-slate-500 mt-1 font-mono">
                                    {{ $conversation->vendor->shop_name ?? 'Seller' }} • {{ $msg->created_at->format('h:i A') }}
                                </div>
                            </div>
                        </div>
                    @endif
                @endforeach
            </div>

            <!-- Reply Box -->
            <div class="p-3 sm:p-4 bg-slate-900/90 border-t border-white/10">
                <form action="{{ route('messages.reply', $conversation->id) }}" method="POST" class="flex items-center gap-2">
                    @csrf
                    <input type="text" name="body" required placeholder="Type your reply to {{ $conversation->vendor->shop_name ?? 'seller' }}..."
                           class="flex-1 px-4 py-3 rounded-2xl bg-white/5 border border-white/10 text-white text-xs sm:text-sm focus:ring-2 focus:ring-violet-500 focus:outline-none placeholder:text-slate-500">
                    <button type="submit" 
                            class="px-5 py-3 rounded-2xl bg-violet-600 hover:bg-violet-500 text-white text-xs font-bold transition shadow-lg shrink-0 flex items-center gap-1.5 active:scale-95">
                        <span>Send</span>
                        <span>↗</span>
                    </button>
                </form>
            </div>
        </div>
    </div>

</body>
</html>
