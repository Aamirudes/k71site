<x-layouts.public 
    :title="'Project Workspace #' . $order->order_number . ' — K71 Services'"
    :metaDescription="'Collaborative project workspace for order #' . $order->order_number . ' on K71 Services.'"
    :fullWidth="true"
>
    @php
        $vendor = $order->vendor;
        $buyer = $order->buyer;
        $product = $order->product;
        $package = $order->package;
        $isBuyer = $order->buyer_id === auth()->id();
        $isVendor = $vendor && $vendor->user_id === auth()->id();
        $statusBadge = $order->status_badge;
    @endphp

    <div x-data="{ 
            showDeliveryModal: false, 
            showRevisionModal: false, 
            showApproveModal: false,
            showDisputeModal: false,
            rating: 5,
            reviewText: ''
         }"
         class="w-full selection:bg-[#ECD2F3] selection:text-[#1A1018]">

        <!-- OBSIDIAN WORKSPACE HEADER STAGE -->
        <section class="grail-hero-band py-6 sm:py-8 px-4 sm:px-6 lg:px-8 border-b border-white/10">
            <div class="max-w-7xl mx-auto space-y-5">

                <!-- NOTIFICATIONS -->
                @if(session('success'))
                    <div class="p-4 rounded-2xl bg-emerald-500/15 border border-emerald-400/30 text-emerald-200 text-xs font-semibold flex items-center gap-2">
                        <svg class="w-4 h-4 text-emerald-300 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg>
                        <span>{{ session('success') }}</span>
                    </div>
                @endif

                @if(session('info'))
                    <div class="p-4 rounded-2xl bg-[#ECD2F3]/15 border border-[#ECD2F3]/30 text-[#ECD2F3] text-xs font-semibold flex items-center gap-2">
                        <svg class="w-4 h-4 text-[#ECD2F3] shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path></svg>
                        <span>{{ session('info') }}</span>
                    </div>
                @endif

                @if(session('error'))
                    <div class="p-4 rounded-2xl bg-rose-500/15 border border-rose-400/30 text-rose-200 text-xs font-semibold flex items-center gap-2">
                        <span>{{ session('error') }}</span>
                    </div>
                @endif

                <!-- TOP STATUS BAR & ORDER HEADER -->
                <div class="grail-dark-card p-6 sm:p-8 rounded-3xl flex flex-col md:flex-row items-start md:items-center justify-between gap-6">
                    <div>
                        <div class="flex items-center gap-3 mb-2">
                            <span class="grail-eyebrow">Project Workspace</span>
                            <span class="text-white/25">•</span>
                            <span class="text-xs text-[#EFE9F2]/65 font-medium">Order #{{ $order->order_number }}</span>
                        </div>
                        <h1 class="text-2xl sm:text-3xl font-light text-white tracking-tight leading-snug" style="font-family: var(--font-grail-display);">
                            {{ $product?->title ?? 'Freelance Service Project' }}
                        </h1>
                        <div class="flex flex-wrap items-center gap-4 text-xs text-[#EFE9F2]/70 mt-2 font-medium">
                            <span>Buyer: <strong class="text-white">{{ $buyer?->first_name }} {{ $buyer?->last_name }}</strong></span>
                            <span class="text-white/25">•</span>
                            <span>Provider: <strong class="text-white">{{ $vendor?->shop_name }}</strong></span>
                            <span class="text-white/25">•</span>
                            <span>Due: <strong class="text-[#ECD2F3]">{{ $order->due_at ? $order->due_at->format('M d, Y') : 'In 3 Days' }}</strong></span>
                        </div>
                    </div>

                    <div class="flex flex-wrap items-center gap-3">
                        <!-- Current Status Pill -->
                        <span class="inline-flex items-center gap-1.5 px-4 py-2 rounded-full border text-xs font-bold uppercase tracking-wide {{ $statusBadge['bg'] }} {{ $statusBadge['text'] }} {{ $statusBadge['border'] }}">
                            <span class="w-2 h-2 rounded-full bg-current animate-pulse"></span>
                            <span>{{ $statusBadge['label'] }}</span>
                        </span>

                        <!-- ACTION BUTTONS BASED ON ROLE & ORDER STATUS -->
                        @if($isVendor && in_array($order->status, ['in_progress', 'revision_requested']))
                            <button type="button" @click="showDeliveryModal = true"
                                    class="grail-cta-pill px-5 py-2.5 text-xs uppercase tracking-wider cursor-pointer">
                                Submit Delivery
                            </button>
                        @endif

                        @if($isBuyer && $order->status === 'delivered')
                            <button type="button" @click="showApproveModal = true"
                                    class="px-5 py-2.5 rounded-full bg-emerald-500 hover:bg-emerald-400 text-[#060606] text-xs font-bold uppercase tracking-wider transition shadow-md active:scale-95 cursor-pointer">
                                Approve &amp; Complete
                            </button>
                            @if($order->revisions_used < $order->revisions_allowed)
                                <button type="button" @click="showRevisionModal = true"
                                        class="px-5 py-2.5 rounded-full bg-amber-400 hover:bg-amber-300 text-[#1A1018] text-xs font-bold uppercase tracking-wider transition active:scale-95 cursor-pointer">
                                    Request Revision ({{ $order->revisions_used }}/{{ $order->revisions_allowed }})
                                </button>
                            @endif
                        @endif

                        @if(!in_array($order->status, ['completed', 'cancelled', 'disputed']))
                            <button type="button" @click="showDisputeModal = true"
                                    class="grail-btn-ghost-dark px-4 py-2.5 text-xs hover:!bg-rose-500/20 hover:!border-rose-400/50 hover:!text-rose-200 cursor-pointer">
                                Raise Dispute
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        </section>

        <!-- CURVED STONE EDITORIAL SHEET -->
        <div class="grail-sheet bg-[#F6F6F6] text-[#1A1018] rounded-t-[28px] sm:rounded-t-[36px] py-8 sm:py-10 pb-20 px-4 sm:px-6 lg:px-8 shadow-[0_-18px_48px_rgba(0,0,0,0.45)]">
            <div class="max-w-7xl mx-auto">

                <!-- WORKSPACE GRID: CONVERSATION (8 COLS) + PROJECT DETAILS (4 COLS) -->
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">

                    <!-- LEFT COLUMN (8 COLS): COLLABORATIVE CHAT & DELIVERABLES -->
                    <div class="lg:col-span-8 space-y-6">

                        <!-- DELIVERABLES SECTION (If delivered) -->
                        @if(!empty($order->deliveries) && is_array($order->deliveries))
                            <div class="p-6 rounded-3xl bg-[#F8EFFA] border border-[#ECD2F3] shadow-xs space-y-4">
                                <div class="flex items-center justify-between">
                                    <h3 class="font-bold text-sm text-[#1A1018] uppercase tracking-wider flex items-center gap-2">
                                        <span>🎁 Submitted Deliverables</span>
                                        <span class="px-2.5 py-0.5 rounded-full bg-[#1A1018] text-[#ECD2F3] text-[10px]">
                                            {{ count($order->deliveries) }} Submission(s)
                                        </span>
                                    </h3>
                                </div>

                                @foreach($order->deliveries as $idx => $deliv)
                                    <div class="p-4 rounded-2xl bg-white border border-[#E6E2E8] space-y-3">
                                        <div class="flex items-center justify-between text-xs">
                                            <span class="font-bold text-[#1A1018]">Delivery #{{ $idx + 1 }}</span>
                                            <span class="text-[#1A1018]/50">{{ $deliv['submitted_at'] ?? 'Recently' }}</span>
                                        </div>
                                        <p class="text-xs text-[#1A1018]/75 whitespace-pre-line leading-relaxed">{{ $deliv['notes'] ?? '' }}</p>

                                        @if(!empty($deliv['files']) && is_array($deliv['files']))
                                            <div class="pt-2 border-t border-[#E6E2E8] flex flex-wrap gap-2">
                                                @foreach($deliv['files'] as $f)
                                                    <a href="{{ asset('storage/' . $f['path']) }}" target="_blank" download
                                                       class="px-3 py-1.5 rounded-xl bg-[#FAFAF8] border border-[#E6E2E8] hover:border-[#623562]/40 hover:bg-[#F8EFFA] text-[#623562] text-xs font-bold flex items-center gap-1.5 transition">
                                                        <span>📎 {{ $f['name'] }}</span>
                                                        <span class="text-[10px] text-[#1A1018]/45">({{ round(($f['size'] ?? 1024)/1024) }} KB)</span>
                                                    </a>
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        @endif

                        <!-- CHAT FEED CONTAINER -->
                        <div class="grail-card p-6 sm:p-8 rounded-3xl bg-white border border-[#E6E2E8] space-y-6">
                            <div class="pb-4 border-b border-[#E6E2E8] flex items-center justify-between">
                                <h3 class="font-bold text-xs text-[#1A1018] uppercase tracking-[0.14em]">
                                    Project Discussion &amp; Work Updates
                                </h3>
                                <span class="text-[11px] text-[#1A1018]/50">Direct Workspace Stream</span>
                            </div>

                            <!-- Messages List -->
                            <div class="space-y-4 max-h-[500px] overflow-y-auto pr-1">
                                @foreach($order->messages as $msg)
                                    @php
                                        $isMine = $msg->sender_id === auth()->id();
                                    @endphp
                                    <div class="flex {{ $isMine ? 'justify-end' : 'justify-start' }}">
                                        <div class="max-w-md sm:max-w-lg p-4 rounded-3xl {{ $isMine ? 'bg-[#1A1018] text-[#EFE9F2] rounded-br-xs shadow-sm' : 'bg-[#F8EFFA] text-[#1A1018] border border-[#ECD2F3] rounded-bl-xs' }} space-y-2">
                                            <div class="flex items-center justify-between gap-4 text-[10px] {{ $isMine ? 'text-[#ECD2F3]/80' : 'text-[#623562]/80' }}">
                                                <span class="font-bold">{{ $msg->sender?->first_name ?? 'System' }}</span>
                                                <span>{{ $msg->created_at->format('M d, H:i') }}</span>
                                            </div>
                                            <p class="text-xs sm:text-sm whitespace-pre-line leading-relaxed">{{ $msg->message }}</p>

                                            @if(!empty($msg->attachments) && is_array($msg->attachments))
                                                <div class="pt-2 border-t {{ $isMine ? 'border-white/15' : 'border-[#ECD2F3]' }} flex flex-wrap gap-2">
                                                    @foreach($msg->attachments as $att)
                                                        <a href="{{ asset('storage/' . $att['path']) }}" target="_blank" download
                                                           class="px-2.5 py-1 rounded-xl text-xs font-bold flex items-center gap-1 transition {{ $isMine ? 'bg-white/10 text-[#ECD2F3] hover:bg-white/20' : 'bg-white text-[#623562] border border-[#ECD2F3] hover:bg-[#F6E3FB]' }}">
                                                            <span>📎 {{ $att['name'] }}</span>
                                                        </a>
                                                    @endforeach
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            <!-- Post Message Form -->
                            @if(!in_array($order->status, ['completed', 'cancelled']))
                                <form action="{{ route('services.workspace.message', $order) }}" method="POST" enctype="multipart/form-data" 
                                      class="pt-4 border-t border-[#E6E2E8] space-y-3">
                                    @csrf
                                    <textarea name="message" rows="3" 
                                              placeholder="Type a message, ask a question, or share an update..."
                                              class="grail-input-light w-full p-4 rounded-2xl text-xs sm:text-sm font-medium"></textarea>

                                    <div class="flex items-center justify-between gap-3">
                                        <label class="grail-btn-ghost-light px-4 py-2 text-xs cursor-pointer flex items-center gap-1.5">
                                            <input type="file" name="attachments[]" multiple class="hidden">
                                            <span>📎 Attach File(s)</span>
                                        </label>

                                        <button type="submit" 
                                                class="grail-btn-obsidian px-6 py-2.5 text-xs uppercase tracking-wider cursor-pointer">
                                            Send Message
                                        </button>
                                    </div>
                                </form>
                            @endif
                        </div>

                    </div>

                    <!-- RIGHT COLUMN (4 COLS): ORDER INFO, REQUIREMENTS, DETAILS -->
                    <div class="lg:col-span-4 space-y-6">

                        <!-- PROJECT SPECIFICATIONS CARD -->
                        <div class="grail-card p-6 rounded-3xl bg-white border border-[#E6E2E8] space-y-4">
                            <h3 class="text-xs font-bold text-[#623562] uppercase tracking-[0.14em]">Project Scope</h3>

                            <div class="space-y-3 text-xs">
                                <div class="flex items-center justify-between text-[#1A1018]/65">
                                    <span>Package Tier:</span>
                                    <span class="font-bold text-[#1A1018] uppercase">{{ $order->tier }}</span>
                                </div>
                                <div class="flex items-center justify-between text-[#1A1018]/65">
                                    <span>Agreed Price:</span>
                                    <span class="font-bold text-[#623562] text-sm">₹{{ number_format($order->total_price) }}</span>
                                </div>
                                <div class="flex items-center justify-between text-[#1A1018]/65">
                                    <span>Revisions Allowed:</span>
                                    <span class="font-bold text-[#1A1018]">{{ $order->revisions_allowed }}</span>
                                </div>
                                <div class="flex items-center justify-between text-[#1A1018]/65">
                                    <span>Revisions Used:</span>
                                    <span class="font-bold text-[#1A1018]">{{ $order->revisions_used }}</span>
                                </div>
                                <div class="flex items-center justify-between text-[#1A1018]/65">
                                    <span>Ordered On:</span>
                                    <span class="font-bold text-[#1A1018]">{{ $order->created_at->format('M d, Y') }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- SUBMITTED REQUIREMENTS -->
                        <div class="grail-card p-6 rounded-3xl bg-white border border-[#E6E2E8] space-y-3">
                            <h3 class="text-xs font-bold text-[#623562] uppercase tracking-[0.14em]">Client Requirements</h3>
                            <div class="text-xs text-[#1A1018]/75 leading-relaxed whitespace-pre-line bg-[#FAFAF8] p-4 rounded-2xl border border-[#E6E2E8]">
                                {{ $order->requirements_text }}
                            </div>

                            @if(!empty($order->requirements_files) && is_array($order->requirements_files))
                                <div class="space-y-1.5 pt-2">
                                    <div class="text-[11px] font-bold text-[#1A1018]/55">Attached Reference Files:</div>
                                    @foreach($order->requirements_files as $rf)
                                        <a href="{{ asset('storage/' . $rf['path']) }}" target="_blank" download
                                           class="p-2.5 rounded-xl bg-[#FAFAF8] border border-[#E6E2E8] hover:border-[#623562]/40 hover:bg-[#F8EFFA] text-xs font-semibold text-[#623562] flex items-center justify-between transition">
                                            <span class="truncate">{{ $rf['name'] }}</span>
                                            <span class="text-[10px] text-[#1A1018]/45">Download</span>
                                        </a>
                                    @endforeach
                                </div>
                            @endif
                        </div>

                    </div>

                </div>

            </div>
        </div>

        <!-- MODAL 1: SUBMIT DELIVERY (Vendor Only) -->
        <div x-show="showDeliveryModal" x-cloak 
             class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/75 backdrop-blur-sm">
            <div class="w-full max-w-lg rounded-3xl overflow-hidden bg-[#FAFAF8] border border-[#E6E2E8] shadow-2xl"
                 @click.outside="showDeliveryModal = false">
                <div class="px-6 py-4 bg-[#0D0D0D] text-white border-b border-white/10 flex items-center justify-between">
                    <div>
                        <span class="grail-eyebrow block">Provider Delivery</span>
                        <h3 class="text-lg font-medium text-white" style="font-family: var(--font-grail-display);">Submit Project Deliverables</h3>
                    </div>
                    <button type="button" @click="showDeliveryModal = false" class="w-8 h-8 rounded-full bg-white/10 hover:bg-white/20 text-white/80 hover:text-white flex items-center justify-center text-sm transition">✕</button>
                </div>

                <form action="{{ route('services.workspace.deliver', $order) }}" method="POST" enctype="multipart/form-data" class="p-6 sm:p-7 space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-bold text-[#1A1018]/75 mb-1.5">Delivery Notes &amp; Summary</label>
                        <textarea name="notes" rows="4" required
                                  placeholder="Describe the completed work, link to online previews (Figma/GitHub), or explain the files..."
                                  class="grail-input-light w-full p-3.5 rounded-2xl text-xs"></textarea>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-[#1A1018]/75 mb-1.5">Upload Deliverable Files (ZIP, PDF, Master Files)</label>
                        <input type="file" name="delivery_files[]" multiple required
                               class="w-full text-xs text-[#1A1018]/70 file:mr-4 file:py-2.5 file:px-4 file:rounded-full file:border file:border-[#ECD2F3] file:text-xs file:font-bold file:bg-[#F8EFFA] file:text-[#623562] hover:file:bg-[#F6E3FB]">
                    </div>

                    <div class="pt-4 flex items-center justify-end gap-3">
                        <button type="button" @click="showDeliveryModal = false" class="grail-btn-ghost-light px-5 py-2.5 text-xs">Cancel</button>
                        <button type="submit" class="grail-btn-obsidian px-6 py-2.5 text-xs uppercase tracking-wider cursor-pointer">Confirm Delivery</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- MODAL 2: REQUEST REVISION (Buyer Only) -->
        <div x-show="showRevisionModal" x-cloak 
             class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/75 backdrop-blur-sm">
            <div class="w-full max-w-lg rounded-3xl overflow-hidden bg-[#FAFAF8] border border-[#E6E2E8] shadow-2xl"
                 @click.outside="showRevisionModal = false">
                <div class="px-6 py-4 bg-[#0D0D0D] text-white border-b border-white/10 flex items-center justify-between">
                    <div>
                        <span class="grail-eyebrow block">Client Feedback</span>
                        <h3 class="text-lg font-medium text-white" style="font-family: var(--font-grail-display);">Request Project Revision</h3>
                    </div>
                    <button type="button" @click="showRevisionModal = false" class="w-8 h-8 rounded-full bg-white/10 hover:bg-white/20 text-white/80 hover:text-white flex items-center justify-center text-sm transition">✕</button>
                </div>

                <form action="{{ route('services.workspace.revision', $order) }}" method="POST" class="p-6 sm:p-7 space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-bold text-[#1A1018]/75 mb-1.5">What changes or improvements are needed?</label>
                        <textarea name="revision_notes" rows="4" required
                                  placeholder="Be specific about the changes you want made to the submitted work..."
                                  class="grail-input-light w-full p-3.5 rounded-2xl text-xs"></textarea>
                    </div>

                    <div class="pt-4 flex items-center justify-end gap-3">
                        <button type="button" @click="showRevisionModal = false" class="grail-btn-ghost-light px-5 py-2.5 text-xs">Cancel</button>
                        <button type="submit" class="px-6 py-2.5 rounded-full bg-amber-400 hover:bg-amber-300 text-[#1A1018] text-xs font-bold uppercase tracking-wider transition cursor-pointer">Submit Revision Request</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- MODAL 3: APPROVE & COMPLETE WITH RATING (Buyer Only) -->
        <div x-show="showApproveModal" x-cloak 
             class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/75 backdrop-blur-sm">
            <div class="w-full max-w-lg rounded-3xl overflow-hidden bg-[#FAFAF8] border border-[#E6E2E8] shadow-2xl"
                 @click.outside="showApproveModal = false">
                <div class="px-6 py-4 bg-[#0D0D0D] text-white border-b border-white/10 flex items-center justify-between">
                    <div>
                        <span class="grail-eyebrow block">Escrow Release</span>
                        <h3 class="text-lg font-medium text-white" style="font-family: var(--font-grail-display);">Approve &amp; Complete Project</h3>
                    </div>
                    <button type="button" @click="showApproveModal = false" class="w-8 h-8 rounded-full bg-white/10 hover:bg-white/20 text-white/80 hover:text-white flex items-center justify-center text-sm transition">✕</button>
                </div>

                <form action="{{ route('services.workspace.approve', $order) }}" method="POST" class="p-6 sm:p-7 space-y-4">
                    @csrf
                    <div class="p-4 rounded-2xl bg-[#F8EFFA] border border-[#ECD2F3] text-xs text-[#1A1018] leading-relaxed">
                        By approving this delivery, you confirm that the project scope has been met and release the payment to the provider.
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-[#1A1018]/75 mb-1.5">Rate Your Experience</label>
                        <div class="flex items-center gap-2">
                            <template x-for="star in [1, 2, 3, 4, 5]" :key="star">
                                <button type="button" @click="rating = star" class="text-2xl transition cursor-pointer"
                                        :class="rating >= star ? 'text-amber-400' : 'text-[#1A1018]/15'">
                                    ★
                                </button>
                            </template>
                            <input type="hidden" name="rating" :value="rating">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-[#1A1018]/75 mb-1.5">Leave a Review (Optional)</label>
                        <textarea name="review_text" rows="3" x-model="reviewText"
                                  placeholder="Share your feedback about the communication, quality, and delivery speed..."
                                  class="grail-input-light w-full p-3.5 rounded-2xl text-xs"></textarea>
                    </div>

                    <div class="pt-4 flex items-center justify-end gap-3">
                        <button type="button" @click="showApproveModal = false" class="grail-btn-ghost-light px-5 py-2.5 text-xs">Cancel</button>
                        <button type="submit" class="px-6 py-2.5 rounded-full bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold uppercase tracking-wider transition cursor-pointer">Finalize &amp; Release Payment</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- MODAL 4: RAISE DISPUTE -->
        <div x-show="showDisputeModal" x-cloak 
             class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/75 backdrop-blur-sm">
            <div class="w-full max-w-lg rounded-3xl overflow-hidden bg-[#FAFAF8] border border-[#E6E2E8] shadow-2xl"
                 @click.outside="showDisputeModal = false">
                <div class="px-6 py-4 bg-[#0D0D0D] text-white border-b border-white/10 flex items-center justify-between">
                    <div>
                        <span class="grail-eyebrow !text-rose-300 block">Trust &amp; Mediation</span>
                        <h3 class="text-lg font-medium text-white" style="font-family: var(--font-grail-display);">Raise Project Dispute</h3>
                    </div>
                    <button type="button" @click="showDisputeModal = false" class="w-8 h-8 rounded-full bg-white/10 hover:bg-white/20 text-white/80 hover:text-white flex items-center justify-center text-sm transition">✕</button>
                </div>

                <form action="{{ route('services.workspace.dispute', $order) }}" method="POST" class="p-6 sm:p-7 space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-bold text-[#1A1018]/75 mb-1.5">Reason for Dispute</label>
                        <textarea name="dispute_reason" rows="4" required
                                  placeholder="Explain the issue with this order. A K71 support mediation officer will review the chat logs and deliverables..."
                                  class="grail-input-light w-full p-3.5 rounded-2xl text-xs !border-rose-200 focus:!border-rose-400"></textarea>
                    </div>

                    <div class="pt-4 flex items-center justify-end gap-3">
                        <button type="button" @click="showDisputeModal = false" class="grail-btn-ghost-light px-5 py-2.5 text-xs">Cancel</button>
                        <button type="submit" class="px-6 py-2.5 rounded-full bg-rose-600 hover:bg-rose-500 text-white text-xs font-bold uppercase tracking-wider transition cursor-pointer">Submit Dispute Ticket</button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</x-layouts.public>
