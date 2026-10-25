<x-layouts.public 
    :title="'Project Workspace #' . $order->order_number . ' — K71 Services'"
    :metaDescription="'Collaborative project workspace for order #' . $order->order_number . ' on K71 Services.'"
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
         class="min-h-screen bg-gradient-to-b from-[#faf5ff] via-white to-[#faf5ff] text-zinc-900 pb-20 pt-6">

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            <!-- NOTIFICATIONS -->
            @if(session('success'))
                <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-semibold flex items-center gap-2">
                    <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            @if(session('info'))
                <div class="p-4 rounded-2xl bg-indigo-50 border border-indigo-200 text-indigo-800 text-xs font-semibold flex items-center gap-2">
                    <svg class="w-4 h-4 text-indigo-600 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path></svg>
                    <span>{{ session('info') }}</span>
                </div>
            @endif

            @if(session('error'))
                <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-xs font-semibold flex items-center gap-2">
                    <span>{{ session('error') }}</span>
                </div>
            @endif

            <!-- TOP STATUS BAR & ORDER HEADER (Glass Container) -->
            <div class="p-6 sm:p-8 rounded-3xl bg-white/90 backdrop-blur-md border border-purple-200/80 shadow-[0_10px_35px_rgba(124,58,237,0.06)] flex flex-col md:flex-row items-start md:items-center justify-between gap-6">
                <div>
                    <div class="flex items-center gap-3 mb-2">
                        <span class="text-xs uppercase font-bold tracking-wider text-purple-700">Project Workspace</span>
                        <span class="text-zinc-300">•</span>
                        <span class="text-xs text-zinc-500 font-medium">Order #{{ $order->order_number }}</span>
                    </div>
                    <h1 class="text-xl sm:text-2xl font-black text-zinc-950 tracking-tight leading-snug">
                        {{ $product?->title ?? 'Freelance Service Project' }}
                    </h1>
                    <div class="flex flex-wrap items-center gap-4 text-xs text-zinc-500 mt-2 font-medium">
                        <span>Buyer: <strong class="text-zinc-800">{{ $buyer?->first_name }} {{ $buyer?->last_name }}</strong></span>
                        <span>•</span>
                        <span>Provider: <strong class="text-zinc-800">{{ $vendor?->shop_name }}</strong></span>
                        <span>•</span>
                        <span>Due: <strong class="text-purple-700">{{ $order->due_at ? $order->due_at->format('M d, Y') : 'In 3 Days' }}</strong></span>
                    </div>
                </div>

                <div class="flex flex-wrap items-center gap-3">
                    <!-- Current Status Pill -->
                    <span class="inline-flex items-center gap-1.5 px-4 py-2 rounded-2xl border text-xs font-bold uppercase tracking-wide {{ $statusBadge['bg'] }} {{ $statusBadge['text'] }} {{ $statusBadge['border'] }}">
                        <span class="w-2 h-2 rounded-full bg-current animate-pulse"></span>
                        <span>{{ $statusBadge['label'] }}</span>
                    </span>

                    <!-- ACTION BUTTONS BASED ON ROLE & ORDER STATUS -->
                    @if($isVendor && in_array($order->status, ['in_progress', 'revision_requested']))
                        <button type="button" @click="showDeliveryModal = true"
                                class="px-5 py-2.5 rounded-2xl bg-purple-600 hover:bg-purple-700 text-white text-xs font-bold uppercase tracking-wider transition shadow-md shadow-purple-500/25 active:scale-95">
                            Submit Delivery
                        </button>
                    @endif

                    @if($isBuyer && $order->status === 'delivered')
                        <button type="button" @click="showApproveModal = true"
                                class="px-5 py-2.5 rounded-2xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold uppercase tracking-wider transition shadow-md shadow-emerald-500/25 active:scale-95">
                            Approve &amp; Complete
                        </button>
                        @if($order->revisions_used < $order->revisions_allowed)
                            <button type="button" @click="showRevisionModal = true"
                                    class="px-5 py-2.5 rounded-2xl bg-amber-500 hover:bg-amber-600 text-white text-xs font-bold uppercase tracking-wider transition active:scale-95">
                                Request Revision ({{ $order->revisions_used }}/{{ $order->revisions_allowed }})
                            </button>
                        @endif
                    @endif

                    @if(!in_array($order->status, ['completed', 'cancelled', 'disputed']))
                        <button type="button" @click="showDisputeModal = true"
                                class="px-3.5 py-2.5 rounded-2xl bg-zinc-100 hover:bg-rose-50 text-zinc-600 hover:text-rose-700 text-xs font-bold transition">
                            Raise Dispute
                        </button>
                    @endif
                </div>
            </div>

            <!-- WORKSPACE GRID: CONVERSATION (8 COLS) + PROJECT DETAILS (4 COLS) -->
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">

                <!-- LEFT COLUMN (8 COLS): COLLABORATIVE CHAT & DELIVERABLES -->
                <div class="lg:col-span-8 space-y-6">

                    <!-- DELIVERABLES SECTION (If delivered) -->
                    @if(!empty($order->deliveries) && is_array($order->deliveries))
                        <div class="p-6 rounded-3xl bg-purple-50/70 border border-purple-200/80 shadow-xs space-y-4">
                            <div class="flex items-center justify-between">
                                <h3 class="font-black text-sm text-purple-950 uppercase tracking-wider flex items-center gap-2">
                                    <span>🎁 Submitted Deliverables</span>
                                    <span class="px-2 py-0.5 rounded-md bg-purple-200 text-purple-800 text-[10px]">
                                        {{ count($order->deliveries) }} Submission(s)
                                    </span>
                                </h3>
                            </div>

                            @foreach($order->deliveries as $idx => $deliv)
                                <div class="p-4 rounded-2xl bg-white border border-purple-100 space-y-3">
                                    <div class="flex items-center justify-between text-xs">
                                        <span class="font-bold text-zinc-900">Delivery #{{ $idx + 1 }}</span>
                                        <span class="text-zinc-400">{{ $deliv['submitted_at'] ?? 'Recently' }}</span>
                                    </div>
                                    <p class="text-xs text-zinc-700 whitespace-pre-line leading-relaxed">{{ $deliv['notes'] ?? '' }}</p>

                                    @if(!empty($deliv['files']) && is_array($deliv['files']))
                                        <div class="pt-2 border-t border-purple-50 flex flex-wrap gap-2">
                                            @foreach($deliv['files'] as $f)
                                                <a href="{{ asset('storage/' . $f['path']) }}" target="_blank" download
                                                   class="px-3 py-1.5 rounded-xl bg-purple-50 border border-purple-200 hover:bg-purple-100 text-purple-800 text-xs font-bold flex items-center gap-1.5 transition">
                                                    <span>📎 {{ $f['name'] }}</span>
                                                    <span class="text-[10px] text-zinc-400">({{ round(($f['size'] ?? 1024)/1024) }} KB)</span>
                                                </a>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @endif

                    <!-- CHAT FEED CONTAINER -->
                    <div class="p-6 sm:p-8 rounded-3xl bg-white/80 backdrop-blur-md border border-purple-100 shadow-sm space-y-6">
                        <div class="pb-4 border-b border-purple-50 flex items-center justify-between">
                            <h3 class="font-black text-sm text-zinc-950 uppercase tracking-wider">
                                Project Discussion &amp; Work Updates
                            </h3>
                            <span class="text-[11px] text-zinc-400">Direct Workspace Stream</span>
                        </div>

                        <!-- Messages List -->
                        <div class="space-y-4 max-h-[500px] overflow-y-auto pr-1">
                            @foreach($order->messages as $msg)
                                @php
                                    $isMine = $msg->sender_id === auth()->id();
                                @endphp
                                <div class="flex {{ $isMine ? 'justify-end' : 'justify-start' }}">
                                    <div class="max-w-md sm:max-w-lg p-4 rounded-3xl {{ $isMine ? 'bg-purple-600 text-white rounded-br-xs' : 'bg-purple-50/80 text-zinc-900 border border-purple-100 rounded-bl-xs' }} space-y-2">
                                        <div class="flex items-center justify-between gap-4 text-[10px] {{ $isMine ? 'text-purple-200' : 'text-zinc-400' }}">
                                            <span class="font-bold">{{ $msg->sender?->first_name ?? 'System' }}</span>
                                            <span>{{ $msg->created_at->format('M d, H:i') }}</span>
                                        </div>
                                        <p class="text-xs sm:text-sm whitespace-pre-line leading-relaxed">{{ $msg->message }}</p>

                                        @if(!empty($msg->attachments) && is_array($msg->attachments))
                                            <div class="pt-2 border-t {{ $isMine ? 'border-purple-500' : 'border-purple-200' }} flex flex-wrap gap-2">
                                                @foreach($msg->attachments as $att)
                                                    <a href="{{ asset('storage/' . $att['path']) }}" target="_blank" download
                                                       class="px-2.5 py-1 rounded-xl text-xs font-bold flex items-center gap-1 {{ $isMine ? 'bg-white/20 text-white hover:bg-white/30' : 'bg-white text-purple-700 border border-purple-200 hover:bg-purple-100' }}">
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
                                  class="pt-4 border-t border-purple-50 space-y-3">
                                @csrf
                                <textarea name="message" rows="3" 
                                          placeholder="Type a message, ask a question, or share an update..."
                                          class="w-full p-4 rounded-2xl border border-purple-200 bg-purple-50/30 text-xs sm:text-sm font-medium focus:ring-2 focus:ring-purple-500 focus:outline-none text-zinc-900"></textarea>

                                <div class="flex items-center justify-between gap-3">
                                    <label class="px-4 py-2 rounded-xl bg-purple-50 border border-purple-200 text-purple-800 text-xs font-bold hover:bg-purple-100 transition cursor-pointer flex items-center gap-1.5">
                                        <input type="file" name="attachments[]" multiple class="hidden">
                                        <span>📎 Attach File(s)</span>
                                    </label>

                                    <button type="submit" 
                                            class="px-6 py-2.5 rounded-xl bg-purple-600 hover:bg-purple-700 text-white text-xs font-bold uppercase tracking-wider transition shadow-sm active:scale-95">
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
                    <div class="p-6 rounded-3xl bg-white/80 backdrop-blur-md border border-purple-100 shadow-sm space-y-4">
                        <h3 class="font-black text-sm text-purple-900 uppercase tracking-wider">Project Scope</h3>

                        <div class="space-y-3 text-xs">
                            <div class="flex items-center justify-between text-zinc-600">
                                <span>Package Tier:</span>
                                <span class="font-bold text-zinc-900 uppercase">{{ $order->tier }}</span>
                            </div>
                            <div class="flex items-center justify-between text-zinc-600">
                                <span>Agreed Price:</span>
                                <span class="font-bold text-purple-700 text-sm">₹{{ number_format($order->total_price) }}</span>
                            </div>
                            <div class="flex items-center justify-between text-zinc-600">
                                <span>Revisions Allowed:</span>
                                <span class="font-bold text-zinc-900">{{ $order->revisions_allowed }}</span>
                            </div>
                            <div class="flex items-center justify-between text-zinc-600">
                                <span>Revisions Used:</span>
                                <span class="font-bold text-zinc-900">{{ $order->revisions_used }}</span>
                            </div>
                            <div class="flex items-center justify-between text-zinc-600">
                                <span>Ordered On:</span>
                                <span class="font-bold text-zinc-900">{{ $order->created_at->format('M d, Y') }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- SUBMITTED REQUIREMENTS -->
                    <div class="p-6 rounded-3xl bg-white/80 backdrop-blur-md border border-purple-100 shadow-sm space-y-3">
                        <h3 class="font-black text-sm text-purple-900 uppercase tracking-wider">Client Requirements</h3>
                        <div class="text-xs text-zinc-700 leading-relaxed whitespace-pre-line bg-purple-50/40 p-4 rounded-2xl border border-purple-100/60">
                            {{ $order->requirements_text }}
                        </div>

                        @if(!empty($order->requirements_files) && is_array($order->requirements_files))
                            <div class="space-y-1.5 pt-2">
                                <div class="text-[11px] font-bold text-zinc-500">Attached Reference Files:</div>
                                @foreach($order->requirements_files as $rf)
                                    <a href="{{ asset('storage/' . $rf['path']) }}" target="_blank" download
                                       class="p-2 rounded-xl bg-white border border-purple-100 hover:border-purple-300 text-xs font-semibold text-purple-700 flex items-center justify-between transition">
                                        <span class="truncate">{{ $rf['name'] }}</span>
                                        <span class="text-[10px] text-zinc-400">Download</span>
                                    </a>
                                @endforeach
                            </div>
                        @endif
                    </div>

                </div>

            </div>

        </div>

        <!-- MODAL 1: SUBMIT DELIVERY (Vendor Only) -->
        <div x-show="showDeliveryModal" x-cloak 
             class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-zinc-950/60 backdrop-blur-xs">
            <div class="w-full max-w-lg p-6 sm:p-8 rounded-3xl bg-white border border-purple-200 shadow-2xl space-y-5"
                 @click.outside="showDeliveryModal = false">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-black text-zinc-950">Submit Project Deliverables</h3>
                    <button type="button" @click="showDeliveryModal = false" class="text-zinc-400 hover:text-zinc-600 font-black text-lg">✕</button>
                </div>

                <form action="{{ route('services.workspace.deliver', $order) }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-bold text-zinc-700 mb-1.5">Delivery Notes &amp; Summary</label>
                        <textarea name="notes" rows="4" required
                                  placeholder="Describe the completed work, link to online previews (Figma/GitHub), or explain the files..."
                                  class="w-full p-3.5 rounded-2xl border border-purple-200 text-xs text-zinc-900 focus:ring-2 focus:ring-purple-500 focus:outline-none"></textarea>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-zinc-700 mb-1.5">Upload Deliverable Files (ZIP, PDF, Master Files)</label>
                        <input type="file" name="delivery_files[]" multiple required
                               class="w-full text-xs text-zinc-600 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-purple-50 file:text-purple-700 hover:file:bg-purple-100">
                    </div>

                    <div class="pt-4 flex items-center justify-end gap-3">
                        <button type="button" @click="showDeliveryModal = false" class="px-5 py-2.5 rounded-xl border border-zinc-200 text-xs font-bold text-zinc-700">Cancel</button>
                        <button type="submit" class="px-6 py-2.5 rounded-xl bg-purple-600 hover:bg-purple-700 text-white text-xs font-bold uppercase tracking-wider">Confirm Delivery</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- MODAL 2: REQUEST REVISION (Buyer Only) -->
        <div x-show="showRevisionModal" x-cloak 
             class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-zinc-950/60 backdrop-blur-xs">
            <div class="w-full max-w-lg p-6 sm:p-8 rounded-3xl bg-white border border-purple-200 shadow-2xl space-y-5"
                 @click.outside="showRevisionModal = false">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-black text-zinc-950">Request Project Revision</h3>
                    <button type="button" @click="showRevisionModal = false" class="text-zinc-400 hover:text-zinc-600 font-black text-lg">✕</button>
                </div>

                <form action="{{ route('services.workspace.revision', $order) }}" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-bold text-zinc-700 mb-1.5">What changes or improvements are needed?</label>
                        <textarea name="revision_notes" rows="4" required
                                  placeholder="Be specific about the changes you want made to the submitted work..."
                                  class="w-full p-3.5 rounded-2xl border border-purple-200 text-xs text-zinc-900 focus:ring-2 focus:ring-purple-500 focus:outline-none"></textarea>
                    </div>

                    <div class="pt-4 flex items-center justify-end gap-3">
                        <button type="button" @click="showRevisionModal = false" class="px-5 py-2.5 rounded-xl border border-zinc-200 text-xs font-bold text-zinc-700">Cancel</button>
                        <button type="submit" class="px-6 py-2.5 rounded-xl bg-amber-500 hover:bg-amber-600 text-white text-xs font-bold uppercase tracking-wider">Submit Revision Request</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- MODAL 3: APPROVE & COMPLETE WITH RATING (Buyer Only) -->
        <div x-show="showApproveModal" x-cloak 
             class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-zinc-950/60 backdrop-blur-xs">
            <div class="w-full max-w-lg p-6 sm:p-8 rounded-3xl bg-white border border-purple-200 shadow-2xl space-y-5"
                 @click.outside="showApproveModal = false">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-black text-zinc-950">Approve &amp; Complete Project</h3>
                    <button type="button" @click="showApproveModal = false" class="text-zinc-400 hover:text-zinc-600 font-black text-lg">✕</button>
                </div>

                <form action="{{ route('services.workspace.approve', $order) }}" method="POST" class="space-y-4">
                    @csrf
                    <div class="p-4 rounded-2xl bg-purple-50 text-xs text-purple-900 leading-relaxed">
                        By approving this delivery, you confirm that the project scope has been met and release the payment to the provider.
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-zinc-700 mb-1.5">Rate Your Experience</label>
                        <div class="flex items-center gap-2">
                            <template x-for="star in [1, 2, 3, 4, 5]" :key="star">
                                <button type="button" @click="rating = star" class="text-2xl transition"
                                        :class="rating >= star ? 'text-amber-400' : 'text-zinc-200'">
                                    ★
                                </button>
                            </template>
                            <input type="hidden" name="rating" :value="rating">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-zinc-700 mb-1.5">Leave a Review (Optional)</label>
                        <textarea name="review_text" rows="3" x-model="reviewText"
                                  placeholder="Share your feedback about the communication, quality, and delivery speed..."
                                  class="w-full p-3.5 rounded-2xl border border-purple-200 text-xs text-zinc-900 focus:ring-2 focus:ring-purple-500 focus:outline-none"></textarea>
                    </div>

                    <div class="pt-4 flex items-center justify-end gap-3">
                        <button type="button" @click="showApproveModal = false" class="px-5 py-2.5 rounded-xl border border-zinc-200 text-xs font-bold text-zinc-700">Cancel</button>
                        <button type="submit" class="px-6 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold uppercase tracking-wider">Finalize &amp; Release Payment</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- MODAL 4: RAISE DISPUTE -->
        <div x-show="showDisputeModal" x-cloak 
             class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-zinc-950/60 backdrop-blur-xs">
            <div class="w-full max-w-lg p-6 sm:p-8 rounded-3xl bg-white border border-rose-200 shadow-2xl space-y-5"
                 @click.outside="showDisputeModal = false">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-black text-rose-950">Raise Project Dispute</h3>
                    <button type="button" @click="showDisputeModal = false" class="text-zinc-400 hover:text-zinc-600 font-black text-lg">✕</button>
                </div>

                <form action="{{ route('services.workspace.dispute', $order) }}" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-bold text-zinc-700 mb-1.5">Reason for Dispute</label>
                        <textarea name="dispute_reason" rows="4" required
                                  placeholder="Explain the issue with this order. A K71 support mediation officer will review the chat logs and deliverables..."
                                  class="w-full p-3.5 rounded-2xl border border-rose-200 text-xs text-zinc-900 focus:ring-2 focus:ring-rose-500 focus:outline-none"></textarea>
                    </div>

                    <div class="pt-4 flex items-center justify-end gap-3">
                        <button type="button" @click="showDisputeModal = false" class="px-5 py-2.5 rounded-xl border border-zinc-200 text-xs font-bold text-zinc-700">Cancel</button>
                        <button type="submit" class="px-6 py-2.5 rounded-xl bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold uppercase tracking-wider">Submit Dispute Ticket</button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</x-layouts.public>
