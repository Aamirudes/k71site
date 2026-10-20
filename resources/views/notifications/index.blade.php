<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Notifications & Alerts — K71</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-[#0b0f19] text-slate-100 min-h-screen font-sans pb-24 antialiased selection:bg-violet-500 selection:text-white">

    <!-- Top Navigation -->
    <x-k71-nav />

    <div class="max-w-4xl mx-auto px-4 sm:px-6 py-8">
        <!-- Header -->
        <div class="flex items-center justify-between pb-6 border-b border-white/10 mb-6">
            <div>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-white flex items-center gap-3">
                    <span>🔔</span>
                    <span>Notifications & Activity</span>
                </h1>
                <p class="text-sm text-slate-400 mt-1">Auction updates, offer alerts, booking status, and system notices.</p>
            </div>
            @if(auth()->user()->unreadNotifications->count() > 0)
                <form action="{{ route('notifications.read-all') }}" method="POST">
                    @csrf
                    <button type="submit" class="px-4 py-2 rounded-xl bg-white/5 hover:bg-white/10 border border-white/10 text-xs font-semibold text-slate-300 transition">
                        ✓ Mark All Read
                    </button>
                </form>
            @endif
        </div>

        @if(session('success'))
            <div class="mb-6 p-4 rounded-2xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 text-sm">
                {{ session('success') }}
            </div>
        @endif

        @if($notifications->isEmpty())
            <div class="rounded-3xl bg-white/[0.02] border border-white/10 p-12 text-center">
                <div class="text-5xl mb-4">🔔</div>
                <h2 class="text-lg font-bold text-white mb-2">All caught up!</h2>
                <p class="text-sm text-slate-400 max-w-md mx-auto">
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
                    <div class="rounded-2xl {{ $isUnread ? 'bg-violet-500/10 border-violet-500/30' : 'bg-white/[0.02] border-white/10' }} border p-4 sm:p-5 flex items-start justify-between gap-4 transition">
                        <div class="flex items-start gap-3.5">
                            <div class="w-10 h-10 rounded-xl {{ $isUnread ? 'bg-violet-500/20 text-violet-300' : 'bg-white/5 text-slate-400' }} flex items-center justify-center font-bold shrink-0 text-base">
                                {{ $data['icon'] ?? '📣' }}
                            </div>
                            <div>
                                <h2 class="text-sm font-bold text-white">
                                    {{ $data['title'] ?? 'Marketplace Update' }}
                                </h2>
                                <p class="text-xs text-slate-300 mt-0.5">
                                    {{ $data['message'] ?? ($data['body'] ?? 'You have a new update.') }}
                                </p>
                                <span class="text-[10px] text-slate-500 mt-1.5 block font-mono">
                                    {{ $notification->created_at->diffForHumans() }}
                                </span>
                            </div>
                        </div>

                        @if($isUnread)
                            <form action="{{ route('notifications.read', $notification->id) }}" method="POST" class="shrink-0">
                                @csrf
                                <button type="submit" class="text-[11px] text-violet-400 hover:text-violet-300 font-semibold px-2.5 py-1 rounded-lg bg-violet-500/10 hover:bg-violet-500/20 transition">
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

</body>
</html>
