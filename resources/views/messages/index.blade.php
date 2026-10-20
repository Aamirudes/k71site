<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>My Messages — K71 Marketplace</title>
    <meta name="description" content="View and respond to direct inquiries and messages from sellers on K71.">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-[#0b0f19] text-slate-100 min-h-screen font-sans pb-24 antialiased selection:bg-violet-500 selection:text-white">

    <!-- Top Navigation -->
    <x-k71-nav />

    <div class="max-w-5xl mx-auto px-4 sm:px-6 py-8">
        <!-- Header -->
        <div class="flex items-center justify-between pb-6 border-b border-white/10 mb-6">
            <div>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-white flex items-center gap-3">
                    <span>💬</span>
                    <span>Direct Messages</span>
                </h1>
                <p class="text-sm text-slate-400 mt-1">Communicate directly with sellers, service providers, and makers on K71.</p>
            </div>
            <a href="{{ route('notifications.index') }}" class="px-4 py-2 rounded-xl bg-white/5 hover:bg-white/10 border border-white/10 text-xs font-semibold text-slate-300 transition flex items-center gap-2">
                <span>🔔</span>
                <span>Notifications</span>
            </a>
        </div>

        @if(session('success'))
            <div class="mb-6 p-4 rounded-2xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 text-sm">
                {{ session('success') }}
            </div>
        @endif

        @if($conversations->isEmpty())
            <div class="rounded-3xl bg-white/[0.02] border border-white/10 p-12 text-center">
                <div class="text-5xl mb-4">💬</div>
                <h2 class="text-lg font-bold text-white mb-2">No messages yet</h2>
                <p class="text-sm text-slate-400 max-w-md mx-auto mb-6">
                    You haven't started any conversations with sellers. Browse products, services, or auctions and click "Send Direct Message" to ask questions.
                </p>
                <a href="{{ route('home') }}" class="inline-flex items-center gap-2 px-6 py-3 rounded-full bg-violet-600 hover:bg-violet-700 text-white text-xs font-bold transition">
                    Explore Marketplace
                </a>
            </div>
        @else
            <div class="space-y-3">
                @foreach($conversations as $conversation)
                    @php
                        $unread = $conversation->unreadMessagesFor('user', auth()->id());
                        $latest = $conversation->latestMessage;
                    @endphp
                    <a href="{{ route('messages.show', $conversation->id) }}"
                       class="block rounded-2xl bg-white/[0.03] hover:bg-white/[0.06] border border-white/10 p-4 sm:p-5 transition group relative">
                        <div class="flex items-start justify-between gap-4">
                            <div class="flex items-start gap-3.5">
                                <div class="w-11 h-11 rounded-xl bg-violet-500/10 border border-violet-500/30 flex items-center justify-center text-violet-400 font-bold shrink-0 text-base">
                                    {{ substr($conversation->vendor->shop_name ?? 'Vendor', 0, 1) }}
                                </div>
                                <div>
                                    <div class="flex items-center gap-2">
                                        <h2 class="text-sm font-bold text-white group-hover:text-violet-400 transition">
                                            {{ $conversation->vendor->shop_name ?? 'Vendor' }}
                                        </h2>
                                        @if($unread > 0)
                                            <span class="px-2 py-0.5 rounded-full bg-violet-500 text-white text-[10px] font-black">
                                                {{ $unread }} new
                                            </span>
                                        @endif
                                    </div>
                                    <p class="text-xs font-medium text-slate-300 mt-0.5">
                                        {{ $conversation->subject ?? 'Direct Inquiry' }}
                                    </p>
                                    @if($conversation->product)
                                        <span class="inline-block mt-1 text-[11px] text-slate-400 bg-white/5 px-2 py-0.5 rounded-md border border-white/5">
                                            📦 {{ \Illuminate\Support\Str::limit($conversation->product->title, 40) }}
                                        </span>
                                    @endif
                                    @if($latest)
                                        <p class="text-xs text-slate-400 mt-2 line-clamp-1">
                                            <span class="text-slate-500">{{ $latest->sender_type === 'user' ? 'You: ' : 'Seller: ' }}</span>
                                            {{ $latest->body }}
                                        </p>
                                    @endif
                                </div>
                            </div>
                            <div class="text-right shrink-0">
                                <span class="text-[11px] text-slate-500">
                                    {{ $conversation->last_message_at ? $conversation->last_message_at->diffForHumans() : '' }}
                                </span>
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>

            <div class="mt-6">
                {{ $conversations->links() }}
            </div>
        @endif
    </div>

</body>
</html>
