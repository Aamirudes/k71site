<x-layouts.services
    :title="'Contract ' . $contract->contract_number . ' — K71 Services'"
    metaDescription="Escrow-protected project contract workspace on K71 Services."
>
    <div class="min-h-screen w-full selection:bg-[#ECD2F3] selection:text-[#1A1018]">

        <section class="grail-hero-band pt-8 pb-10 px-4 sm:px-6 lg:px-8 border-b border-white/10">
            <div class="max-w-6xl mx-auto space-y-4">
                <div class="flex flex-wrap items-center justify-between gap-3 text-xs text-[#EFE9F2]/75">
                    <div class="flex items-center gap-2">
                        <a href="{{ route('services.projects.show', $contract->project_id) }}" class="text-[#ECD2F3] hover:underline font-semibold">
                            ← Project Brief
                        </a>
                        <span>/</span>
                        <span class="font-mono">{{ $contract->contract_number }}</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="px-3 py-1 rounded-full bg-white/10 border border-white/20 text-[11px] font-bold uppercase text-white">
                            Contract: {{ ucfirst($contract->status) }}
                        </span>
                        <span class="px-3 py-1 rounded-full bg-emerald-500/20 border border-emerald-400/30 text-[11px] font-bold uppercase text-emerald-200">
                            {{ str_replace('_', ' ', $contract->payment_status) }}
                        </span>
                    </div>
                </div>

                <div class="flex flex-col md:flex-row md:items-end justify-between gap-4">
                    <div class="space-y-1.5">
                        <h1 class="text-2xl sm:text-3xl font-light text-white" style="font-family: var(--font-grail-display);">
                            {{ $contract->project?->title ?? 'Project Contract Workspace' }}
                        </h1>
                        <div class="text-xs text-[#EFE9F2]/75 flex flex-wrap items-center gap-3">
                            <span>Client: <strong class="text-white">{{ $contract->buyer?->name }}</strong></span>
                            <span>•</span>
                            <span>
                                Provider: <strong class="text-white">{{ $contract->vendor?->shop_name }}</strong>
                                @if($contract->vendor?->isWebsitePublished())
                                    <a href="{{ $contract->vendor->storefront_url }}" target="_blank" class="text-[#ECD2F3] hover:underline ml-1">Website ↗</a>
                                @endif
                            </span>
                        </div>
                    </div>

                    <div class="px-5 py-3 rounded-2xl bg-white/10 border border-white/15 shrink-0">
                        <div class="text-[10px] uppercase tracking-wider text-[#C9A24A] font-bold">Agreed Contract Value</div>
                        <div class="text-2xl font-bold text-white">{{ $contract->formatted_amount }}</div>
                    </div>
                </div>
            </div>
        </section>

        <div class="grail-sheet bg-[#F6F6F6] text-[#1A1018] rounded-t-[28px] sm:rounded-t-[36px] py-8 sm:py-12 pb-24 shadow-[0_-18px_48px_rgba(0,0,0,0.45)]">
            <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">

                @if(session('success'))
                    <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-900 text-xs font-semibold">{{ session('success') }}</div>
                @endif
                @if(session('error'))
                    <div class="p-4 rounded-2xl bg-red-50 border border-red-200 text-red-900 text-xs font-semibold">{{ session('error') }}</div>
                @endif
                @if(session('info'))
                    <div class="p-4 rounded-2xl bg-amber-50 border border-amber-200 text-amber-900 text-xs font-semibold">{{ session('info') }}</div>
                @endif
                @if($errors->any())
                    <div class="p-4 rounded-2xl bg-red-50 border border-red-200 text-red-900 text-xs space-y-1">
                        @foreach($errors->all() as $err)
                            <div>• {{ $err }}</div>
                        @endforeach
                    </div>
                @endif

                <!-- MILESTONES -->
                <div class="space-y-4">
                    <h2 class="text-2xl font-light text-[#1A1018]" style="font-family: var(--font-grail-display);">Project Milestones &amp; Deliverables</h2>

                    @foreach($contract->milestones as $milestone)
                        <div class="grail-card p-6 rounded-3xl bg-white border border-[#E6E2E8] space-y-4">
                            <div class="flex flex-wrap items-start justify-between gap-3">
                                <div>
                                    <div class="flex items-center gap-2">
                                        <span class="px-2.5 py-0.5 rounded-full bg-[#F8EFFA] text-[#623562] text-[10px] font-bold uppercase">
                                            Milestone {{ $milestone->sort_order }}
                                        </span>
                                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase
                                            {{ $milestone->status === 'approved' ? 'bg-emerald-100 text-emerald-800' : ($milestone->status === 'submitted' ? 'bg-blue-100 text-blue-800' : ($milestone->status === 'revision_requested' ? 'bg-amber-100 text-amber-800' : 'bg-zinc-100 text-zinc-700')) }}">
                                            {{ str_replace('_', ' ', $milestone->status) }}
                                        </span>
                                    </div>
                                    <h3 class="text-base font-bold text-[#1A1018] mt-1.5">{{ $milestone->title }}</h3>
                                    @if($milestone->description)
                                        <p class="text-xs text-[#1A1018]/70 mt-1">{{ $milestone->description }}</p>
                                    @endif
                                </div>
                                <div class="text-right">
                                    <div class="text-lg font-bold text-[#1A1018]">{{ $milestone->formatted_amount }}</div>
                                    @if($milestone->due_at)
                                        <div class="text-[11px] text-[#1A1018]/55">Due {{ $milestone->due_at->format('M d, Y') }}</div>
                                    @endif
                                </div>
                            </div>

                            @if($milestone->submitted_notes)
                                <div class="p-4 rounded-2xl bg-[#FAFAF8] border border-[#E6E2E8] space-y-2 text-xs">
                                    <div class="font-bold text-[#1A1018]">Submitted Delivery Notes:</div>
                                    <p class="text-[#1A1018]/80 whitespace-pre-line">{{ $milestone->submitted_notes }}</p>

                                    @if(!empty($milestone->deliverables_json) && is_array($milestone->deliverables_json))
                                        <div class="flex flex-wrap gap-2 pt-1">
                                            @foreach($milestone->deliverables_json as $file)
                                                <a href="{{ asset('storage/' . ($file['path'] ?? '')) }}" target="_blank"
                                                   class="px-3 py-1.5 rounded-xl bg-white border border-[#E6E2E8] text-[#623562] font-bold hover:border-[#623562]">
                                                    Download: {{ $file['name'] ?? 'Deliverable' }} ↓
                                                </a>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                            @endif

                            @if($milestone->revision_notes)
                                <div class="p-4 rounded-2xl bg-amber-50 border border-amber-200 text-xs text-amber-950 space-y-1">
                                    <div class="font-bold">Client Revision Feedback:</div>
                                    <p>{{ $milestone->revision_notes }}</p>
                                </div>
                            @endif

                            <!-- PROVIDER SUBMIT ACTION -->
                            @if($isVendor && $contract->status === 'active' && in_array($milestone->status, ['pending', 'revision_requested'], true))
                                <form action="{{ route('services.milestones.submit', [$contract, $milestone]) }}" method="POST" enctype="multipart/form-data" class="pt-3 border-t border-[#E6E2E8] space-y-3">
                                    @csrf
                                    <div class="space-y-1">
                                        <label class="block text-xs font-bold text-[#1A1018]">Submit Deliverable Notes *</label>
                                        <textarea name="submitted_notes" rows="3" required
                                                  placeholder="Describe completed deliverables, links, or handover instructions (min 10 chars)..."
                                                  class="grail-input-light w-full px-3.5 py-2.5 text-xs"></textarea>
                                    </div>
                                    <div class="flex flex-wrap items-center justify-between gap-3">
                                        <input type="file" name="deliverables[]" multiple class="text-xs text-[#1A1018]/70">
                                        <button type="submit" class="grail-btn-obsidian px-5 py-2.5 text-xs font-bold cursor-pointer">
                                            Submit Milestone for Review →
                                        </button>
                                    </div>
                                </form>
                            @endif

                            <!-- BUYER APPROVE / REVISION ACTIONS -->
                            @if($isBuyer && $contract->status === 'active' && $milestone->status === 'submitted')
                                <div class="pt-3 border-t border-[#E6E2E8] grid grid-cols-1 md:grid-cols-2 gap-4 items-end">
                                    <form action="{{ route('services.milestones.revision', [$contract, $milestone]) }}" method="POST" class="flex flex-col sm:flex-row gap-2">
                                        @csrf
                                        <input type="text" name="revision_notes" required placeholder="Explain what changes are needed (min 10 chars)..."
                                               class="grail-input-light flex-1 px-3.5 py-2.5 text-xs">
                                        <button type="submit" class="px-4 py-2.5 rounded-xl bg-amber-100 hover:bg-amber-200 text-amber-950 text-xs font-bold cursor-pointer shrink-0">
                                            Request Revision
                                        </button>
                                    </form>

                                    <form action="{{ route('services.milestones.approve', [$contract, $milestone]) }}" method="POST" class="md:text-right">
                                        @csrf
                                        <button type="submit" class="px-6 py-2.5 rounded-full bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold cursor-pointer">
                                            ✓ Approve Milestone
                                        </button>
                                    </form>
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>

                <!-- CONTRACT COMPLETION & VERIFIED REVIEW -->
                <div class="grail-card p-6 sm:p-8 rounded-3xl bg-white border border-[#E6E2E8] space-y-5">
                    @if($contract->status === 'completed')
                        <div class="flex flex-wrap items-center justify-between gap-3">
                            <div>
                                <div class="text-xs font-bold uppercase tracking-wider text-emerald-700">Contract Completed</div>
                                <h2 class="text-xl font-light text-[#1A1018]" style="font-family: var(--font-grail-display);">Verified Client Review &amp; Outcome</h2>
                            </div>
                            @if($contract->completed_at)
                                <span class="text-xs text-[#1A1018]/60">Completed {{ $contract->completed_at->format('M d, Y') }}</span>
                            @endif
                        </div>

                        @if($contract->buyer_rating)
                            <div class="p-5 rounded-2xl bg-[#FAFAF8] border border-[#E6E2E8] space-y-3">
                                <div class="text-sm font-bold text-[#C9A24A]">★ {{ $contract->buyer_rating }} / 5 Verified Contract Rating</div>
                                <p class="text-xs sm:text-sm text-[#1A1018]/80">{{ $contract->buyer_review }}</p>

                                @if($contract->vendor_reply)
                                    <div class="p-3.5 rounded-xl bg-[#F8EFFA] border border-[#ECD2F3] text-xs space-y-1">
                                        <div class="font-bold text-[#623562]">Official Reply from {{ $contract->vendor?->shop_name }}:</div>
                                        <p class="text-[#1A1018]/80">{{ $contract->vendor_reply }}</p>
                                    </div>
                                @endif
                            </div>
                        @endif
                    @elseif($isBuyer && $contract->status === 'active')
                        <div>
                            <h2 class="text-xl font-light text-[#1A1018]" style="font-family: var(--font-grail-display);">Complete Contract &amp; Release Final Escrow</h2>
                            <p class="text-xs text-[#1A1018]/60 mt-1">Once all deliverables satisfy your project goals, complete the contract and submit a verified review for {{ $contract->vendor?->shop_name }}.</p>
                        </div>

                        <form action="{{ route('services.contracts.complete', $contract) }}" method="POST" class="space-y-4">
                            @csrf
                            <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
                                <div class="space-y-1">
                                    <label class="block text-xs font-bold text-[#1A1018]">Rating (1–5)</label>
                                    <select name="rating" class="grail-input-light w-full px-3.5 py-2.5 text-xs">
                                        <option value="5">5 ★ — Exceptional</option>
                                        <option value="4">4 ★ — Strong Delivery</option>
                                        <option value="3">3 ★ — Good</option>
                                        <option value="2">2 ★ — Fair</option>
                                        <option value="1">1 ★ — Needs Improvement</option>
                                    </select>
                                </div>
                                <div class="sm:col-span-3 space-y-1">
                                    <label class="block text-xs font-bold text-[#1A1018]">Verified Client Review</label>
                                    <input type="text" name="review_text" placeholder="Share feedback on quality, timeline, and communication..."
                                           class="grail-input-light w-full px-3.5 py-2.5 text-xs">
                                </div>
                            </div>

                            <button type="submit" class="grail-btn-obsidian px-7 py-3 text-xs font-bold cursor-pointer">
                                Complete Contract &amp; Release Payment →
                            </button>
                        </form>
                    @else
                        <div class="text-xs text-[#1A1018]/65">
                            Waiting for the client to approve remaining milestones and complete the contract.
                        </div>
                    @endif
                </div>

            </div>
        </div>
    </div>
</x-layouts.services>

