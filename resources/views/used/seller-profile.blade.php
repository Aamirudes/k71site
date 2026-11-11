<x-layouts.used
    :title="$profile->display_name . ' (@' . $profile->handle . ') — Seller Profile | K71 Used'"
    :metaDescription="$profile->bio ? \Illuminate\Support\Str::limit($profile->bio, 155) : 'Browse pre-owned listings from ' . $profile->display_name . ' on K71 Used.'"
>
    @php
        $memberSince = $sellerUser->created_at ? $sellerUser->created_at->format('M Y') : 'Member';
        $accountAge = $sellerUser->created_at ? $sellerUser->created_at->diffForHumans(null, true) : '';
        $contactLabels = [
            'k71_chat' => 'K71 Enquiry Chat',
            'phone' => 'Phone / Call',
            'whatsapp' => 'WhatsApp',
        ];
        $handoffLabels = config('used_marketplace.fulfillment_modes', []);
        $reportReasons = config('used_marketplace.report_reasons', []);
        $isOwnProfile = auth()->check() && (int) auth()->id() === (int) $sellerUser->id;
    @endphp

    <div x-data="{ activeTab: 'active', reportSellerModal: false, revealPhone: false }" class="bg-black text-[#EFE9F2]">

        <!-- Seller Header Obsidian Band -->
        <section class="grail-hero-band py-8 sm:py-10 px-3 sm:px-6 lg:px-8 border-b border-white/10">
            <div class="max-w-7xl mx-auto">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
                    <div class="flex items-start gap-4">
                        <div class="w-16 h-16 sm:w-20 sm:h-20 rounded-2xl bg-[#ECD2F3] text-[#1A1018] flex items-center justify-center font-black text-2xl shrink-0 overflow-hidden border border-white/20">
                            @if($sellerUser->avatar)
                                <img src="{{ format_user_avatar($sellerUser->avatar, $profile->display_name) }}" alt="{{ $profile->display_name }}" class="w-full h-full object-cover">
                            @else
                                {{ strtoupper(substr($profile->display_name, 0, 1)) }}
                            @endif
                        </div>

                        <div class="space-y-1.5">
                            <span class="grail-eyebrow block">Used Marketplace Seller</span>
                            <h1 class="text-2xl sm:text-4xl font-light text-white tracking-tight" style="font-family: var(--font-grail-display);">
                                {{ $profile->display_name }}
                            </h1>
                            <div class="flex flex-wrap items-center gap-x-3 gap-y-1 text-xs text-[#EFE9F2]/70">
                                <span class="text-[#ECD2F3] font-mono">{{ '@' . $profile->handle }}</span>
                                <span>• 📍 {{ $profile->location_label }}</span>
                                <span>• Member since {{ $memberSince }} ({{ $accountAge }})</span>
                                @if($profile->reviews_count > 0)
                                    <span class="text-amber-300 font-bold">• ★ {{ number_format($profile->avg_rating, 1) }} ({{ $profile->reviews_count }} reviews)</span>
                                @endif
                            </div>
                            @if($profile->bio)
                                <p class="text-xs sm:text-sm text-[#EFE9F2]/75 max-w-2xl pt-1">{{ $profile->bio }}</p>
                            @endif
                        </div>
                    </div>

                    <!-- Stats & Profile Actions -->
                    <div class="flex flex-col items-start md:items-end gap-3 shrink-0">
                        <div class="grid grid-cols-3 gap-2.5 text-center">
                            <div class="px-3.5 py-2 rounded-2xl bg-white/5 border border-white/10">
                                <div class="text-base font-bold text-white">{{ $activeListings->count() }}</div>
                                <div class="text-[10px] text-[#EFE9F2]/55 uppercase">Active</div>
                            </div>
                            <div class="px-3.5 py-2 rounded-2xl bg-white/5 border border-white/10">
                                <div class="text-base font-bold text-[#ECD2F3]">{{ (int) $profile->sold_count }}</div>
                                <div class="text-[10px] text-[#EFE9F2]/55 uppercase">Deals Sold</div>
                            </div>
                            <div class="px-3.5 py-2 rounded-2xl bg-white/5 border border-white/10">
                                <div class="text-base font-bold text-white">{{ $profile->reviews_count > 0 ? number_format($profile->avg_rating, 1) : '—' }}</div>
                                <div class="text-[10px] text-[#EFE9F2]/55 uppercase">Rating</div>
                            </div>
                        </div>

                        <div class="flex flex-wrap items-center gap-2">
                            @if($isOwnProfile)
                                <a href="{{ route('used.dashboard', ['tab' => 'settings']) }}" class="px-4 py-2 rounded-full bg-[#ECD2F3] text-[#1A1018] text-xs font-bold">
                                    ✎ Edit My Seller Profile
                                </a>
                            @else
                                @if($profile->show_phone_publicly && $sellerUser->phone)
                                    <button type="button" @click="revealPhone = !revealPhone" class="px-4 py-2 rounded-full bg-white/10 hover:bg-white/15 border border-white/15 text-xs font-semibold text-white cursor-pointer">
                                        <span x-text="revealPhone ? '📞 {{ $sellerUser->phone }}' : '📞 Show Public Phone'"></span>
                                    </button>
                                @endif
                                <button type="button" @click="reportSellerModal = true" class="px-3.5 py-2 rounded-full bg-white/5 hover:bg-rose-500/20 border border-white/15 text-xs font-semibold text-white/75 hover:text-rose-200 cursor-pointer">
                                    🚩 Report Seller
                                </button>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Preferences Bar -->
                <div class="mt-5 pt-4 border-t border-white/10 flex flex-wrap items-center gap-4 text-[11px] text-[#EFE9F2]/65">
                    <div>
                        <span class="text-[#C9A24A] font-semibold uppercase tracking-wider mr-1.5">Preferred Contact:</span>
                        {{ collect($profile->preferred_contact_methods ?? ['k71_chat'])->map(fn ($m) => $contactLabels[$m] ?? $m)->implode(', ') }}
                    </div>
                    <div>
                        <span class="text-[#C9A24A] font-semibold uppercase tracking-wider mr-1.5">Handover Options:</span>
                        {{ collect($profile->preferred_handoff_methods ?? ['local_pickup'])->map(fn ($m) => is_array($handoffLabels[$m] ?? null) ? ($handoffLabels[$m]['label'] ?? $m) : ($handoffLabels[$m] ?? $m))->implode(', ') }}
                    </div>
                </div>
            </div>
        </section>

        <!-- Sheet -->
        <div class="grail-sheet bg-[#F6F6F6] text-[#1A1018] rounded-t-[28px] sm:rounded-t-[36px] py-7 px-3 sm:px-6 lg:px-8 min-h-screen shadow-[0_-18px_50px_rgba(0,0,0,0.45)]">
            <div class="max-w-7xl mx-auto space-y-6">

                @if(session('success'))
                    <div class="p-3.5 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-900 text-xs font-semibold">{{ session('success') }}</div>
                @endif
                @if(session('error'))
                    <div class="p-3.5 rounded-2xl bg-rose-50 border border-rose-200 text-rose-900 text-xs font-semibold">{{ session('error') }}</div>
                @endif

                <!-- Tabs -->
                <div class="flex items-center gap-5 border-b border-black/10 pb-2 text-sm">
                    <button type="button" @click="activeTab = 'active'"
                            :class="activeTab === 'active' ? 'border-b-2 border-[#1A1018] font-bold text-[#1A1018]' : 'text-[#1A1018]/55'"
                            class="pb-2 cursor-pointer">
                        Active Listings ({{ $activeListings->count() }})
                    </button>
                    @if($profile->show_sold_history)
                        <button type="button" @click="activeTab = 'sold'"
                                :class="activeTab === 'sold' ? 'border-b-2 border-[#1A1018] font-bold text-[#1A1018]' : 'text-[#1A1018]/55'"
                                class="pb-2 cursor-pointer">
                            Sold History ({{ $soldListings->count() }})
                        </button>
                    @endif
                    <button type="button" @click="activeTab = 'reviews'"
                            :class="activeTab === 'reviews' ? 'border-b-2 border-[#1A1018] font-bold text-[#1A1018]' : 'text-[#1A1018]/55'"
                            class="pb-2 cursor-pointer">
                        Reviews ({{ $reviews->count() }})
                    </button>
                </div>

                <!-- Active Listings -->
                <div x-show="activeTab === 'active'">
                    @if($activeListings->isEmpty())
                        <div class="grail-card bg-white p-10 text-center rounded-3xl text-xs text-[#1A1018]/60">
                            No active listings from {{ $profile->display_name }} right now.
                        </div>
                    @else
                        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3 sm:gap-5">
                            @foreach($activeListings as $item)
                                @include('used.partials.card', ['item' => $item])
                            @endforeach
                        </div>
                    @endif
                </div>

                <!-- Sold Archive (if seller allows) -->
                @if($profile->show_sold_history)
                    <div x-show="activeTab === 'sold'" x-cloak>
                        @if($soldListings->isEmpty())
                            <div class="grail-card bg-white p-10 text-center rounded-3xl text-xs text-[#1A1018]/60">
                                No sold items in archive yet.
                            </div>
                        @else
                            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3 sm:gap-5">
                                @foreach($soldListings as $sItem)
                                    @include('used.partials.card', ['item' => $sItem])
                                @endforeach
                            </div>
                        @endif
                    </div>
                @endif

                <!-- Transaction-Gated Seller Reviews -->
                <div x-show="activeTab === 'reviews'" x-cloak>
                    @if($reviews->isEmpty())
                        <div class="grail-card bg-white p-10 text-center rounded-3xl text-xs text-[#1A1018]/60">
                            No buyer reviews published yet. Reviews can only be submitted after completed handovers.
                        </div>
                    @else
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            @foreach($reviews as $rev)
                                <div class="grail-card bg-white p-5 rounded-2xl border border-[#E6E2E8] space-y-2 text-xs">
                                    <div class="flex items-center justify-between">
                                        <span class="font-bold text-amber-600">{{ str_repeat('★', $rev->rating) }} ({{ $rev->rating }}/5)</span>
                                        <span class="text-[#1A1018]/50">{{ $rev->created_at?->diffForHumans() }}</span>
                                    </div>
                                    @if($rev->title)
                                        <div class="font-bold text-sm text-[#1A1018]">{{ $rev->title }}</div>
                                    @endif
                                    <p class="text-[#1A1018]/80 leading-relaxed">{{ $rev->body }}</p>
                                    <div class="text-[11px] text-[#1A1018]/55 pt-2 border-t border-black/5">
                                        Verified Buyer: <strong>{{ $rev->user?->name ?? 'Buyer' }}</strong>
                                        @if($rev->product) • For {{ $rev->product->title }} @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

            </div>
        </div>

        <!-- Report Seller Modal -->
        <div x-show="reportSellerModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/75 backdrop-blur-xs">
            <div class="bg-[#FAFAF8] text-[#1A1018] rounded-[28px] max-w-md w-full shadow-2xl border border-[#E6E2E8] overflow-hidden" @click.outside="reportSellerModal = false">
                <div class="bg-[#0D0D0D] text-[#EFE9F2] px-6 py-4 flex items-center justify-between">
                    <h3 class="text-base font-bold text-white">🚩 Report Seller Profile</h3>
                    <button @click="reportSellerModal = false" class="text-white/70 hover:text-white cursor-pointer">✕</button>
                </div>
                <div class="p-6">
                    @auth
                        <form action="{{ route('reports.store') }}" method="POST" class="space-y-3 text-xs">
                            @csrf
                            <input type="hidden" name="reportable_type" value="used_seller">
                            <input type="hidden" name="reportable_id" value="{{ $profile->id }}">
                            <div>
                                <label class="block font-bold uppercase tracking-wider text-[11px] text-[#1A1018]/70 mb-1">Reason *</label>
                                <select name="reason" required class="grail-input-light w-full px-3 py-2 text-xs">
                                    @foreach($reportReasons as $rKey => $rLabel)
                                        <option value="{{ $rKey }}">{{ $rLabel }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block font-bold uppercase tracking-wider text-[11px] text-[#1A1018]/70 mb-1">Details</label>
                                <textarea name="details" rows="3" class="grail-input-light w-full px-3 py-2 text-xs resize-none" placeholder="Describe the issue..."></textarea>
                            </div>
                            <button type="submit" class="w-full py-2.5 rounded-full bg-rose-600 text-white font-bold cursor-pointer">Submit Seller Report</button>
                        </form>
                    @else
                        <div class="text-center space-y-3 py-3 text-xs">
                            <p>Please sign in to submit a report against this seller profile.</p>
                            <a href="{{ route('login') }}" class="inline-block px-5 py-2 grail-btn-obsidian font-bold">Sign In →</a>
                        </div>
                    @endauth
                </div>
            </div>
        </div>
    </div>
</x-layouts.used>
