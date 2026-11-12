<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Plan Status • {{ $vendor->shop_name }} | K71</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-zinc-100 text-zinc-950 antialiased">
    @php
        $state = $lockState['state'] ?? 'expired';
        $isExpired = in_array($state, ['expired', 'grace_locked'], true);
        $ribbonOneHeadline = match ($state) {
            'expired', 'grace_locked' => 'PLAN EXPIRED',
            'pending_activation' => 'PENDING ADMIN ACTIVATION',
            'suspended' => 'SUBSCRIPTION PAUSED',
            'verification_pending' => 'VERIFICATION IN PROGRESS',
            default => 'SUBSCRIPTION REQUIRED',
        };
        $statusPill = match ($state) {
            'expired' => 'Expired',
            'grace_locked' => 'Grace Period Locked',
            'pending_activation' => 'Awaiting Activation',
            'suspended' => 'Suspended',
            'verification_pending' => 'Pending KYC',
            default => 'Inactive',
        };
        $expiryLabel = $lockState['expired_at'] ? $lockState['expired_at']->format('d M Y') : 'Not active';
        $defaultMsg = "Hello K71 Admin, my {$lockState['sector_label']} plan ({$lockState['plan_name']}) for {$vendor->shop_name} expired"
            . ($lockState['expired_at'] ? " on {$lockState['expired_at']->format('d M Y')}" : '')
            . ". Please renew / reactivate my subscription.";
        $adminWhatsapp = preg_replace('/\D+/', '', (string) config('k71.admin_whatsapp', ''));
    @endphp

    <!-- Minimal Monochrome Header -->
    <header class="bg-white border-b border-zinc-200">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 h-16 flex items-center justify-between gap-4">
            <div class="flex items-center gap-3 min-w-0">
                <div class="w-9 h-9 rounded-xl bg-zinc-950 text-white flex items-center justify-center font-black text-xs tracking-wider shrink-0">K71</div>
                <div class="min-w-0">
                    <p class="text-xs font-black tracking-tight text-zinc-950 truncate">{{ $vendor->shop_name }}</p>
                    <p class="text-[11px] font-mono text-zinc-500 truncate">{{ $lockState['sector_label'] }} • Restricted Access</p>
                </div>
            </div>

            <div class="flex items-center gap-2 shrink-0">
                <a href="{{ route('vendor.subscription.index') }}"
                   class="px-3 py-1.5 rounded-lg border border-zinc-300 bg-white text-zinc-800 text-xs font-semibold hover:bg-zinc-100 transition">
                    Plan Details
                </a>
                <a href="{{ route('account') }}"
                   class="hidden sm:inline-flex px-3 py-1.5 rounded-lg border border-zinc-300 bg-white text-zinc-800 text-xs font-semibold hover:bg-zinc-100 transition">
                    Customer Account
                </a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit"
                            class="px-3 py-1.5 rounded-lg bg-zinc-950 text-white text-xs font-semibold hover:bg-zinc-800 transition">
                        Sign Out
                    </button>
                </form>
            </div>
        </div>
    </header>

    <main class="max-w-5xl mx-auto px-4 sm:px-6 py-8 space-y-4">
        @if(session('success'))
            <div class="rounded-xl border border-zinc-900 bg-white px-5 py-4 text-xs text-zinc-950 flex items-start justify-between gap-4 shadow-xs">
                <div>
                    <p class="font-black uppercase tracking-wider text-zinc-950">Renewal Request Delivered</p>
                    <p class="text-zinc-700 mt-1">{{ session('success') }}</p>
                </div>
                <span class="px-2.5 py-1 rounded-full bg-zinc-950 text-white text-[10px] font-mono uppercase shrink-0">Sent to Admin</span>
            </div>
        @endif

        @if(session('error'))
            <div class="rounded-xl border border-zinc-400 bg-zinc-200 px-5 py-3.5 text-xs font-semibold text-zinc-900">
                {{ session('error') }}
            </div>
        @endif

        @if($errors->any())
            <div class="rounded-xl border border-zinc-400 bg-white px-5 py-3.5 text-xs text-zinc-900">
                <ul class="list-disc pl-4 space-y-1">
                    @foreach($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- RIBBON 1: Full-width Prominent PLAN EXPIRED Banner -->
        <section class="w-full rounded-2xl bg-zinc-950 text-white border border-zinc-800 px-6 py-6 shadow-sm">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div class="space-y-2">
                    <div class="flex flex-wrap items-center gap-2.5">
                        <span class="inline-flex items-center px-3 py-1 rounded-full bg-white text-zinc-950 text-[11px] font-black uppercase tracking-widest">
                            {{ $ribbonOneHeadline }}
                        </span>
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full border border-zinc-700 bg-zinc-900 text-zinc-200 text-[11px] font-mono uppercase">
                            Status: {{ $statusPill }}
                        </span>
                    </div>
                    <h1 class="text-2xl sm:text-3xl font-black tracking-tight text-white">
                        {{ $lockState['sector_label'] }} Access Locked
                    </h1>
                    <p class="text-sm text-zinc-300 max-w-2xl leading-relaxed">
                        {{ $lockState['reason'] }}
                    </p>
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-1 gap-2 md:min-w-[230px] shrink-0">
                    <div class="rounded-xl bg-zinc-900 border border-zinc-800 px-3.5 py-2.5">
                        <span class="block text-[10px] font-mono uppercase tracking-wider text-zinc-400">Sector</span>
                        <span class="text-xs font-bold text-white">{{ $lockState['sector_label'] }}</span>
                    </div>
                    <div class="rounded-xl bg-zinc-900 border border-zinc-800 px-3.5 py-2.5">
                        <span class="block text-[10px] font-mono uppercase tracking-wider text-zinc-400">Plan</span>
                        <span class="text-xs font-bold text-white">{{ $lockState['plan_name'] }}</span>
                    </div>
                    <div class="rounded-xl bg-zinc-900 border border-zinc-800 px-3.5 py-2.5 col-span-2 sm:col-span-1">
                        <span class="block text-[10px] font-mono uppercase tracking-wider text-zinc-400">{{ $isExpired ? 'Expired On' : 'Term Date' }}</span>
                        <span class="text-xs font-bold text-white">{{ $expiryLabel }}</span>
                    </div>
                </div>
            </div>
        </section>

        <!-- RIBBON 2: Full-width Prominent Contact Admin for Renewal Banner -->
        <section class="w-full rounded-2xl bg-white text-zinc-950 border border-zinc-300 px-6 py-5 shadow-xs">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div class="space-y-1">
                    <p class="text-[11px] font-mono uppercase tracking-widest text-zinc-500 font-bold">Manual Administrator Renewal</p>
                    <h2 class="text-lg sm:text-xl font-black text-zinc-950">Contact Admin for Renewal</h2>
                    <p class="text-xs sm:text-sm text-zinc-600 max-w-2xl">
                        Send a renewal request directly to the K71 administrator below and complete offline confirmation on WhatsApp. Access restores automatically as soon as your plan is renewed.
                    </p>
                </div>
                <div class="flex flex-wrap items-center gap-2.5 shrink-0">
                    <a href="#renewal-message-form"
                       onclick="setTimeout(() => document.getElementById('renewal-message-input')?.focus(), 50)"
                       class="px-5 py-3 rounded-xl bg-zinc-950 text-white text-xs font-black uppercase tracking-wider hover:bg-zinc-800 transition shadow-xs">
                        Message Admin for Renewal
                    </a>
                    @if(strlen($adminWhatsapp) >= 10)
                        <a href="https://wa.me/{{ $adminWhatsapp }}?text={{ urlencode($defaultMsg) }}"
                           target="_blank"
                           class="px-4 py-3 rounded-xl border border-zinc-300 bg-zinc-50 text-zinc-900 text-xs font-bold hover:bg-zinc-100 transition">
                            WhatsApp Admin ↗
                        </a>
                    @endif
                </div>
            </div>
        </section>

        @if(!empty($lockState['active_other_sectors']))
            <!-- Multi-Sector Switcher: Other sectors remain unlocked -->
            <section class="rounded-2xl bg-white border border-zinc-200 px-5 py-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div>
                    <p class="text-xs font-black text-zinc-950">Other Active Sectors on Your Store</p>
                    <p class="text-[11px] text-zinc-500">Only {{ $lockState['sector_label'] }} is locked. Your other active sector(s) remain fully accessible.</p>
                </div>
                <div class="flex flex-wrap gap-2">
                    @foreach($lockState['active_other_sectors'] as $otherSector)
                        <a href="{{ route('vendor.dashboard', ['sector' => $otherSector]) }}"
                           class="px-3.5 py-2 rounded-xl bg-zinc-950 text-white text-xs font-bold hover:bg-zinc-800 transition">
                            Open {{ ucfirst($otherSector) }} Workspace →
                        </a>
                    @endforeach
                </div>
            </section>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
            <!-- Renewal Request Form -->
            <div id="renewal-message-form" class="lg:col-span-7 bg-white border border-zinc-200 rounded-2xl p-6 space-y-4">
                <div class="border-b border-zinc-100 pb-3">
                    <h3 class="text-base font-black text-zinc-950">Message Admin for Renewal</h3>
                    <p class="text-xs text-zinc-500 mt-0.5">Your store identity, affected sector, plan name, and expiry date are pre-filled for fast processing.</p>
                </div>

                <form method="POST" action="{{ route('vendor.subscription.request_renewal') }}" class="space-y-4">
                    @csrf
                    <input type="hidden" name="sector_key" value="{{ $lockState['sector_key'] }}">
                    @if($lockState['latest_sub']?->plan_id)
                        <input type="hidden" name="requested_plan_id" value="{{ $lockState['latest_sub']->plan_id }}">
                    @endif

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
                        <div class="rounded-xl bg-zinc-50 border border-zinc-200 p-3">
                            <span class="block text-[10px] font-mono uppercase text-zinc-400">Vendor Account</span>
                            <strong class="text-zinc-950">{{ $vendor->shop_name }} ({{ auth()->user()?->name ?? $vendor->contact_person }})</strong>
                        </div>
                        <div class="rounded-xl bg-zinc-50 border border-zinc-200 p-3">
                            <span class="block text-[10px] font-mono uppercase text-zinc-400">Affected Sector &amp; Plan</span>
                            <strong class="text-zinc-950">{{ $lockState['sector_label'] }} • {{ $lockState['plan_name'] }}</strong>
                        </div>
                        <div class="rounded-xl bg-zinc-50 border border-zinc-200 p-3 sm:col-span-2 flex items-center justify-between">
                            <div>
                                <span class="block text-[10px] font-mono uppercase text-zinc-400">Expiry / Term Reference</span>
                                <strong class="text-zinc-950">{{ $expiryLabel }}</strong>
                            </div>
                            <span class="px-2.5 py-0.5 rounded-full bg-zinc-200 text-zinc-800 text-[10px] font-mono uppercase">{{ $statusPill }}</span>
                        </div>
                    </div>

                    <div>
                        <label for="renewal-message-input" class="block text-xs font-bold text-zinc-800 mb-1.5">
                            Message to K71 Admin
                        </label>
                        <textarea id="renewal-message-input"
                                  name="message"
                                  rows="4"
                                  required
                                  placeholder="Share preferred duration, WhatsApp contact, or payment confirmation details..."
                                  class="w-full rounded-xl border border-zinc-300 bg-white px-3.5 py-2.5 text-xs text-zinc-950 focus:border-zinc-950 focus:outline-none">{{ old('message', $defaultMsg) }}</textarea>
                    </div>

                    <button type="submit"
                            class="w-full py-3 rounded-xl bg-zinc-950 text-white text-xs font-black uppercase tracking-wider hover:bg-zinc-800 transition">
                        Send Renewal Request to Admin
                    </button>
                </form>

                <div class="rounded-xl border border-zinc-200 bg-zinc-50 px-4 py-3 text-[11px] text-zinc-600 leading-relaxed">
                    <strong class="text-zinc-900">Your store data is safe.</strong> All your catalog items, orders, customer history, designs, and configurations remain preserved and will become immediately usable once your plan is renewed.
                </div>
            </div>

            <!-- Submitted Requests & Admin Replies -->
            <div class="lg:col-span-5 bg-white border border-zinc-200 rounded-2xl p-6 space-y-4">
                <div class="flex items-center justify-between border-b border-zinc-100 pb-3">
                    <div>
                        <h3 class="text-sm font-black text-zinc-950">Your Renewal Requests</h3>
                        <p class="text-[11px] text-zinc-500">Track admin replies &amp; approval status.</p>
                    </div>
                    <span class="text-[10px] font-mono uppercase px-2 py-0.5 rounded bg-zinc-100 text-zinc-600">
                        {{ $renewalRequests->count() }} Total
                    </span>
                </div>

                @if($renewalRequests->isEmpty())
                    <div class="rounded-xl border border-dashed border-zinc-300 p-6 text-center text-xs text-zinc-500">
                        No renewal requests sent yet. Use the form to message the K71 administrator.
                    </div>
                @else
                    <div class="space-y-3">
                        @foreach($renewalRequests as $req)
                            <div class="rounded-xl border border-zinc-200 bg-zinc-50/70 p-3.5 space-y-2 text-xs">
                                <div class="flex items-center justify-between gap-2">
                                    <span class="font-mono text-[10px] uppercase font-bold text-zinc-700">
                                        {{ strtoupper($req->sector_key) }} • {{ $req->created_at?->format('d M Y H:i') }}
                                    </span>
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase {{ in_array($req->status, ['fulfilled', 'approved'], true) ? 'bg-zinc-950 text-white' : ($req->status === 'rejected' || $req->status === 'dismissed' ? 'bg-zinc-300 text-zinc-800' : 'bg-zinc-200 text-zinc-900') }}">
                                        {{ ucfirst($req->status) }}
                                    </span>
                                </div>
                                <p class="text-zinc-700 leading-relaxed">{{ $req->message }}</p>
                                @if($req->admin_reply || $req->rejection_reason)
                                    <div class="rounded-lg bg-white border border-zinc-300 p-2.5 text-[11px]">
                                        <span class="block font-black uppercase text-[10px] text-zinc-500 mb-0.5">Admin Response</span>
                                        <p class="text-zinc-950">{{ $req->admin_reply ?: $req->rejection_reason }}</p>
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </main>
</body>
</html>

