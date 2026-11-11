<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Notifications & Alerts — K71</title>

    <link rel="preconnect" href="https://api.fontshare.com" crossorigin>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://api.fontshare.com/v2/css?f[]=switzer@300,400,500,600,700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,500;0,600;0,700;1,400&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="grail-marketplace-body bg-[#000000] text-[#EFE9F2] min-h-screen font-sans pb-24 antialiased selection:bg-[#ECD2F3] selection:text-[#1A1018]">

    <!-- Top Navigation -->
    <x-k71-nav />

    <div class="max-w-4xl mx-auto px-4 sm:px-6 py-8">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4 pb-6 border-b border-white/10 mb-6">
            <div>
                <span class="grail-eyebrow block mb-1.5">Activity Center</span>
                <h1 class="grail-display text-2xl sm:text-4xl font-bold text-white flex items-center gap-3">
                    <span>Notifications &amp; Activity</span>
                </h1>
                <p class="text-sm text-white/60 mt-1">Auction updates, offer alerts, booking status, and system notices.</p>
            </div>
            @if(auth()->user()->unreadNotifications->count() > 0)
                <form action="{{ route('notifications.read-all') }}" method="POST">
                    @csrf
                    <button type="submit" class="px-4 py-2 rounded-full bg-[#ECD2F3] hover:bg-white text-xs font-bold text-[#1A1018] transition cursor-pointer">
                        ✓ Mark All Read
                    </button>
                </form>
            @endif
        </div>

        @if(session('success'))
            <div class="mb-6 p-4 rounded-2xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-300 text-sm">
                {{ session('success') }}
            </div>
        @endif

        @if($notifications->isEmpty())
            <div class="rounded-[28px] bg-[#161616] border border-white/10 p-12 text-center">
                <div class="w-16 h-16 rounded-full bg-[#ECD2F3]/12 text-[#ECD2F3] flex items-center justify-center text-3xl mx-auto mb-4">🔔</div>
                <h2 class="grail-display text-2xl font-bold text-white mb-2">All caught up!</h2>
                <p class="text-sm text-white/60 max-w-md mx-auto">
                    You have no notifications right now. Activity regarding your auctions, bookings, and inquiries will appear here.
                </p>
            </div>
        @else
            <div class="space-y-3">
                @foreach($notifications as $notification)
                    @php
                        $data = $notification->data ?? [];
                        $isUnread = $notification->read_at === null;
                    @endphp
                    <div class="rounded-2xl {{ $isUnread ? 'bg-[#ECD2F3]/10 border-[#ECD2F3]/35' : 'bg-[#161616] border-white/10' }} border p-4 sm:p-5 flex items-start justify-between gap-4 transition">
                        <div class="flex items-start gap-3.5">
                            <div class="w-10 h-10 rounded-xl {{ $isUnread ? 'bg-[#ECD2F3] text-[#1A1018]' : 'bg-white/6 text-white/70' }} flex items-center justify-center font-bold shrink-0 text-base">
                                {{ $data['icon'] ?? '📣' }}
                            </div>
                            <div>
                                <h2 class="text-sm font-bold text-white">
                                    {{ $data['title'] ?? 'Marketplace Update' }}
                                </h2>
                                <p class="text-xs text-white/75 mt-0.5">
                                    {{ $data['message'] ?? ($data['body'] ?? 'You have a new update.') }}
                                </p>
                                <span class="text-[10px] text-white/45 mt-1.5 block font-mono">
                                    {{ $notification->created_at->diffForHumans() }}
                                </span>
                            </div>
                        </div>

                        @if($isUnread)
                            <form action="{{ route('notifications.read', $notification->id) }}" method="POST" class="shrink-0">
                                @csrf
                                <button type="submit" class="text-[11px] text-[#1A1018] bg-[#ECD2F3] hover:bg-white font-bold px-3 py-1 rounded-full transition cursor-pointer">
                                    Mark Read
                                </button>
                            </form>
                        @endif
                    </div>
                @endforeach
            </div>

            <div class="mt-6">
                {{ $notifications->links() }}
            </div>
        @endif
    </div>

    <x-k71-hamburger />
    <x-k71-mini-cart />
    <x-k71-mini-wishlist />
    <x-k71-auth-modal />
</body>
</html>
