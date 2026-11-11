<x-layouts.used
    :title="'Enquiry Thread: ' . ($thread->product?->title ?? 'Used Listing') . ' | K71 Used'"
    robotsContent="noindex,nofollow"
>
    @php
        $product = $thread->product;
        $peer = $isSeller ? $thread->buyer : $thread->seller;
        $peerProfile = !$isSeller ? $thread->seller?->usedSellerProfile : $thread->buyer?->usedSellerProfile;
        $peerName = $peerProfile?->display_name ?? $peer?->name ?? 'User';
        $isLocked = $thread->isLocked();
        $reportReasons = config('used_marketplace.report_reasons', []);
    @endphp

    <div x-data="{ reportMsgId: null }" class="bg-black text-[#EFE9F2]">

        <section class="grail-hero-band py-6 px-3 sm:px-6 lg:px-8 border-b border-white/10">
            <div class="max-w-4xl mx-auto flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <a href="{{ route('used.dashboard', ['tab' => 'messages']) }}" class="text-xs text-[#ECD2F3] hover:text-white font-semibold inline-flex items-center gap-1 mb-2">
                        ← Back to All Enquiries
                    </a>
                    <span class="grail-eyebrow block mb-1">Listing Enquiry Thread</span>
                    <h1 class="text-xl sm:text-2xl font-light text-white" style="font-family: var(--font-grail-display);">
                        Conversation with <span class="text-[#ECD2F3]">{{ $peerName }}</span>
                    </h1>
                </div>
                @if($product)
                    <a href="{{ route('used.show', $product->slug) }}" class="px-4 py-2 rounded-full bg-white/10 hover:bg-white/15 border border-white/15 text-xs font-bold text-white shrink-0">
                        View Listing ↗
                    </a>
                @endif
            </div>
        </section>

        <div class="grail-sheet bg-[#F6F6F6] text-[#1A1018] rounded-t-[28px] sm:rounded-t-[36px] py-7 px-3 sm:px-6 min-h-screen shadow-[0_-18px_50px_rgba(0,0,0,0.45)]">
            <div class="max-w-4xl mx-auto space-y-5">

                @if(session('success'))
                    <div class="p-3.5 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-900 text-xs font-semibold">{{ session('success') }}</div>
                @endif
                @if(session('error'))
                    <div class="p-3.5 rounded-2xl bg-rose-50 border border-rose-200 text-rose-900 text-xs font-semibold">{{ session('error') }}</div>
                @endif

                <!-- Attached Listing Banner -->
                @if($product)
                    <div class="grail-card bg-white rounded-2xl border border-[#E6E2E8] p-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                        <div class="flex items-center gap-3 min-w-0">
                            <div class="w-14 h-14 rounded-xl bg-[#141115] overflow-hidden shrink-0 flex items-center justify-center">
                                @if($product->hasUploadedImages())
                                    <img src="{{ $product->images->first()->url }}" alt="{{ $product->title }}" class="w-full h-full object-cover">
                                @else
                                    <span class="text-base opacity-60">📷</span>
                                @endif
                            </div>
                            <div class="min-w-0">
                                <div class="text-xs font-bold text-[#1A1018] truncate">{{ $product->title }}</div>
                                <div class="text-sm font-black text-[#623562]">₹ {{ number_format($product->base_price / 100) }}</div>
                                <div class="text-[11px] text-[#1A1018]/60">Status: <strong>{{ strtoupper($product->effectiveUsedStatus()) }}</strong> • 📍 {{ $product->city_label }}</div>
                            </div>
                        </div>
                        <div class="text-xs shrink-0">
                            @if($peerProfile?->handle)
                                <a href="{{ route('used.seller.profile', $peerProfile->handle) }}" class="px-3.5 py-2 rounded-full bg-[#F8EFFA] text-[#1A1018] font-bold">Seller Profile →</a>
                            @endif
                        </div>
                    </div>
                @endif

                <!-- Shared Private Pickup / Meetup Spot Card -->
                @if($thread->shared_pickup_location || $isSeller)
                    <div class="grail-card bg-white rounded-2xl border border-[#ECD2F3] p-4 space-y-3 text-xs">
                        @if($thread->shared_pickup_location)
                            <div class="p-3 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-950">
                                <div class="font-bold">📍 Shared Private Pickup / Meetup Location:</div>
                                <div class="mt-0.5 font-semibold">{{ $thread->shared_pickup_location }}</div>
                                @if($thread->pickup_shared_at)
                                    <div class="text-[10px] text-emerald-800/75 mt-1">Shared {{ $thread->pickup_shared_at->diffForHumans() }}</div>
                                @endif
                            </div>
                        @endif

                        @if($isSeller && !$isLocked)
                            <form action="{{ route('used.threads.share_pickup', $thread) }}" method="POST" class="flex flex-col sm:flex-row gap-2">
                                @csrf
                                <input type="text" name="pickup_location"
                                       value="{{ old('pickup_location', $thread->shared_pickup_location ?: ($product?->private_pickup_area ?? '')) }}"
                                       placeholder="Enter exact pickup spot or safe public meetup landmark..."
                                       class="grail-input-light flex-1 px-3.5 py-2 text-xs">
                                <button type="submit" class="px-4 py-2 grail-btn-obsidian text-xs font-bold shrink-0 cursor-pointer">
                                    {{ $thread->shared_pickup_location ? 'Update Shared Pickup Spot' : 'Share Pickup Area With Buyer' }}
                                </button>
                            </form>
                        @endif
                    </div>
                @endif

                <!-- Conversation Messages Stream -->
                <div class="grail-card bg-white rounded-3xl border border-[#E6E2E8] p-4 sm:p-6 space-y-4">
                    <div class="space-y-3 max-h-[55vh] overflow-y-auto pr-1">
                        @forelse($thread->messages as $msg)
                            @php
                                $isMine = (int) $msg->sender_id === (int) $user->id;
                                $isEvent = in_array($msg->message_type, ['offer_event', 'location_share', 'system'], true);
                            @endphp
                            <div class="flex {{ $isMine ? 'justify-end' : 'justify-start' }}">
                                <div class="max-w-[85%] sm:max-w-[75%] space-y-1">
                                    <div class="px-4 py-3 rounded-2xl text-xs leading-relaxed {{ $isEvent ? 'bg-[#F8EFFA] border border-[#ECD2F3] text-[#1A1018]' : ($isMine ? 'bg-[#1A1018] text-[#EFE9F2]' : 'bg-[#FAFAF8] border border-black/10 text-[#1A1018]') }}">
                                        <div class="text-[10px] font-bold opacity-65 mb-1">
                                            {{ $isMine ? 'You' : ($msg->sender?->name ?? $peerName) }}
                                            @if($msg->message_type === 'offer_event') • Offer Activity @elseif($msg->message_type === 'location_share') • Pickup Shared @endif
                                        </div>
                                        <div class="whitespace-pre-line">{{ $msg->body }}</div>
                                    </div>
                                    <div class="flex items-center {{ $isMine ? 'justify-end' : 'justify-between' }} gap-3 px-1 text-[10px] text-[#1A1018]/45">
                                        <span>{{ $msg->created_at?->format('d M, g:i A') }}</span>
                                        @if(!$isMine)
                                            <button type="button" @click="reportMsgId = {{ $msg->id }}" class="hover:text-rose-600 font-semibold cursor-pointer">
                                                Report
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @empty
                            <p class="text-xs text-[#1A1018]/50 text-center py-6">No messages in this thread yet.</p>
                        @endforelse
                    </div>

                    @if($isLocked)
                        <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-900 text-xs font-semibold text-center">
                            🔒 This listing has been sold; new enquiry messages are closed.
                        </div>
                    @else
                        <form action="{{ route('used.threads.reply', $thread) }}" method="POST" class="pt-3 border-t border-[#E6E2E8] flex flex-col sm:flex-row gap-2">
                            @csrf
                            <textarea name="body" rows="2" required placeholder="Type your message regarding condition, pickup timing, or inspection..."
                                      class="grail-input-light flex-1 px-4 py-2.5 text-xs resize-none"></textarea>
                            <button type="submit" class="px-6 py-2.5 grail-btn-obsidian text-xs font-bold shrink-0 cursor-pointer">
                                Send Reply →
                            </button>
                        </form>
                    @endif
                </div>

            </div>
        </div>

        <!-- Report Thread Message Modal -->
        <div x-show="reportMsgId !== null" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/75 backdrop-blur-xs">
            <div class="bg-[#FAFAF8] text-[#1A1018] rounded-[28px] max-w-sm w-full shadow-2xl border border-[#E6E2E8] overflow-hidden" @click.outside="reportMsgId = null">
                <div class="bg-[#0D0D0D] text-[#EFE9F2] px-5 py-4 flex items-center justify-between">
                    <h3 class="text-sm font-bold text-white">🚩 Report Message</h3>
                    <button @click="reportMsgId = null" class="text-white/70 hover:text-white cursor-pointer">✕</button>
                </div>
                <form action="{{ route('reports.store') }}" method="POST" class="p-5 space-y-3 text-xs">
                    @csrf
                    <input type="hidden" name="reportable_type" value="used_thread_message">
                    <input type="hidden" name="reportable_id" :value="reportMsgId">
                    <div>
                        <label class="block font-bold text-[11px] uppercase text-[#1A1018]/70 mb-1">Reason *</label>
                        <select name="reason" required class="grail-input-light w-full px-3 py-2 text-xs">
                            @foreach($reportReasons as $rKey => $rLabel)
                                <option value="{{ $rKey }}">{{ $rLabel }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block font-bold text-[11px] uppercase text-[#1A1018]/70 mb-1">Details</label>
                        <textarea name="details" rows="2" class="grail-input-light w-full px-3 py-2 text-xs resize-none"></textarea>
                    </div>
                    <button type="submit" class="w-full py-2.5 rounded-full bg-rose-600 text-white font-bold cursor-pointer">Submit Message Report</button>
                </form>
            </div>
        </div>

    </div>
</x-layouts.used>

