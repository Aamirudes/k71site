<x-layouts.services
    :title="$project->title . ' — K71 Project Brief'"
    :metaDescription="Str::limit(strip_tags($project->description), 155)"
>
    <div class="min-h-screen w-full selection:bg-[#ECD2F3] selection:text-[#1A1018]">

        <section class="grail-hero-band pt-8 pb-10 px-4 sm:px-6 lg:px-8 border-b border-white/10">
            <div class="max-w-7xl mx-auto space-y-4">
                <div class="flex flex-wrap items-center justify-between gap-3 text-xs text-[#EFE9F2]/70">
                    <div class="flex items-center gap-2">
                        <a href="{{ route('services.projects.index') }}" class="text-[#ECD2F3] hover:underline font-semibold">← Open Project Briefs</a>
                        <span>/</span>
                        <span class="font-mono">{{ $project->project_number }}</span>
                    </div>
                    <span class="px-3 py-1 rounded-full bg-white/10 border border-white/20 text-[11px] font-bold uppercase text-white">
                        {{ str_replace('_', ' ', $project->status) }}
                    </span>
                </div>

                <div class="flex flex-col lg:flex-row lg:items-end justify-between gap-6">
                    <div class="space-y-2 max-w-3xl">
                        <h1 class="text-2xl sm:text-4xl font-light text-white leading-tight" style="font-family: var(--font-grail-display);">
                            {{ $project->title }}
                        </h1>
                        <div class="flex flex-wrap items-center gap-4 text-xs text-[#EFE9F2]/75">
                            @if($project->category)
                                <span>{{ $project->category->name }}</span>
                            @endif
                            <span>{{ ucfirst($project->work_mode) }} ({{ $project->city ?: 'Remote' }})</span>
                            <span>Posted by {{ $project->buyer?->name ?? 'Client' }}</span>
                            <span>{{ $project->proposals_count }} Proposal(s)</span>
                        </div>
                    </div>

                    <div class="px-5 py-3.5 rounded-2xl bg-white/10 border border-white/15 backdrop-blur-md text-left lg:text-right shrink-0">
                        <div class="text-[10px] uppercase tracking-wider text-[#C9A24A] font-bold">
                            {{ $project->budget_type === 'hourly' ? 'Hourly Budget' : 'Project Budget' }}
                        </div>
                        <div class="text-2xl font-bold text-white mt-0.5">{{ $project->formatted_budget }}</div>
                        @if($project->expected_duration)
                            <div class="text-[11px] text-[#EFE9F2]/70 mt-0.5">Est. {{ $project->expected_duration }}</div>
                        @endif
                    </div>
                </div>
            </div>
        </section>

        <div class="grail-sheet bg-[#F6F6F6] text-[#1A1018] rounded-t-[28px] sm:rounded-t-[36px] py-8 sm:py-12 pb-24 shadow-[0_-18px_48px_rgba(0,0,0,0.45)]">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">

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
                        <div class="font-bold">Validation issue:</div>
                        <ul class="list-disc pl-5">
                            @foreach($errors->all() as $err)
                                <li>{{ $err }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">

                    <!-- LEFT: BRIEF DETAILS & DELIVERABLES -->
                    <div class="lg:col-span-5 space-y-6">
                        <div class="grail-card p-6 rounded-3xl bg-white border border-[#E6E2E8] space-y-4">
                            <h2 class="grail-eyebrow !text-[#623562]">Project Brief</h2>
                            <div class="text-xs sm:text-sm text-[#1A1018]/80 leading-relaxed whitespace-pre-line">
                                {{ $project->description }}
                            </div>

                            @if($project->deadline_at)
                                <div class="pt-3 border-t border-[#E6E2E8] text-xs">
                                    <span class="font-bold text-[#1A1018]">Target Deadline:</span>
                                    <span class="text-[#1A1018]/75">{{ $project->deadline_at->format('M d, Y') }}</span>
                                </div>
                            @endif
                        </div>

                        @if(!empty($project->deliverables_json) && is_array($project->deliverables_json))
                            <div class="grail-card p-6 rounded-3xl bg-white border border-[#E6E2E8] space-y-3">
                                <h3 class="grail-eyebrow !text-[#623562]">Expected Deliverables</h3>
                                <ul class="space-y-2 text-xs text-[#1A1018]/80">
                                    @foreach($project->deliverables_json as $del)
                                        <li class="flex items-start gap-2">
                                            <span class="text-[#623562] font-bold">✓</span>
                                            <span>{{ $del }}</span>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        @if(!empty($project->skills_json) && is_array($project->skills_json))
                            <div class="grail-card p-6 rounded-3xl bg-white border border-[#E6E2E8] space-y-3">
                                <h3 class="grail-eyebrow !text-[#623562]">Required Skills</h3>
                                <div class="flex flex-wrap gap-1.5">
                                    @foreach($project->skills_json as $skill)
                                        <span class="px-3 py-1 rounded-full bg-[#F8EFFA] text-[#623562] border border-[#ECD2F3] text-xs font-semibold">
                                            {{ $skill }}
                                        </span>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>

                    <!-- RIGHT: BUYER PROPOSALS REVIEW OR PROVIDER PITCH / CONTRACT WORKSPACE -->
                    <div class="lg:col-span-7 space-y-6">

                        @if($project->contract && ($isBuyer || ($vendor && (int) $project->contract->vendor_id === (int) $vendor->id)))
                            <div class="p-5 rounded-3xl bg-emerald-50 border border-emerald-200 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                                <div>
                                    <div class="text-[11px] font-bold uppercase tracking-wider text-emerald-800">Active Contract</div>
                                    <div class="text-sm font-bold text-emerald-950 mt-0.5">
                                        Contract {{ $project->contract->contract_number }} · {{ $project->hiredVendor?->shop_name }}
                                    </div>
                                </div>
                                <a href="{{ route('services.contracts.show', $project->contract) }}"
                                   class="px-5 py-2.5 rounded-full bg-emerald-700 hover:bg-emerald-800 text-white text-xs font-bold shrink-0 text-center">
                                    Open Contract Workspace →
                                </a>
                            </div>
                        @endif

                        @if($isBuyer)
                            <div class="grail-card p-6 rounded-3xl bg-white border border-[#E6E2E8] space-y-5">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <h2 class="text-xl font-light text-[#1A1018]" style="font-family: var(--font-grail-display);">Submitted Proposals ({{ $proposals->count() }})</h2>
                                        <p class="text-xs text-[#1A1018]/60">Inspect case studies, shortlist candidates, interview via chat, or hire one provider into an escrow contract.</p>
                                    </div>
                                </div>

                                @if($proposals->isEmpty())
                                    <div class="p-8 rounded-2xl bg-[#FAFAF8] border border-[#E6E2E8] text-center text-xs text-[#1A1018]/60">
                                        No proposals submitted yet. Eligible K71 service providers will appear here as they pitch.
                                    </div>
                                @else
                                    <div class="space-y-4">
                                        @foreach($proposals as $prop)
                                            @php
                                                $samples = $prop->attachedPortfolioProjects();
                                                $propMilestones = is_array($prop->proposed_milestones_json) ? $prop->proposed_milestones_json : [];
                                                $canDecide = !$project->contract && in_array($project->status, ['open', 'shortlisting'], true) && in_array($prop->status, ['pending', 'shortlisted'], true);
                                            @endphp
                                            <div class="p-5 rounded-2xl border {{ $prop->status === 'hired' ? 'border-emerald-300 bg-emerald-50/40' : ($prop->status === 'shortlisted' ? 'border-[#623562]/50 bg-[#F8EFFA]/40' : 'border-[#E6E2E8] bg-[#FAFAF8]') }} space-y-4">
                                                <div class="flex flex-wrap items-start justify-between gap-3">
                                                    <div>
                                                        <div class="flex flex-wrap items-center gap-2">
                                                            <a href="{{ route('services.provider', $prop->vendor->slug) }}" class="font-bold text-sm text-[#1A1018] hover:text-[#623562]">
                                                                {{ $prop->vendor->shop_name }}
                                                            </a>
                                                            @if($prop->vendor->isWebsitePublished())
                                                                <a href="{{ $prop->vendor->storefront_url }}" target="_blank" class="text-[11px] font-bold text-[#623562] hover:underline">
                                                                    Website ↗
                                                                </a>
                                                            @endif
                                                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase
                                                                {{ $prop->status === 'hired' ? 'bg-emerald-100 text-emerald-800' : ($prop->status === 'shortlisted' ? 'bg-[#ECD2F3] text-[#1A1018]' : ($prop->status === 'rejected' || $prop->status === 'withdrawn' ? 'bg-zinc-200 text-zinc-700' : 'bg-amber-100 text-amber-800')) }}">
                                                                {{ ucfirst($prop->status) }}
                                                            </span>
                                                        </div>
                                                        <div class="text-[11px] text-[#1A1018]/60 mt-0.5">
                                                            {{ $prop->vendor->headline ?: $prop->vendor->tagline }}
                                                        </div>
                                                    </div>

                                                    <div class="text-right">
                                                        <div class="text-base font-bold text-[#1A1018]">{{ $prop->formatted_bid }}</div>
                                                        <div class="text-[11px] text-[#1A1018]/60">{{ $prop->delivery_days }} days · {{ ucfirst($prop->pricing_type) }}</div>
                                                    </div>
                                                </div>

                                                <p class="text-xs text-[#1A1018]/80 leading-relaxed whitespace-pre-line">{{ $prop->cover_letter }}</p>

                                                @if($samples->isNotEmpty())
                                                    <div class="space-y-2 pt-2 border-t border-[#E6E2E8]">
                                                        <div class="text-[11px] font-bold text-[#623562] uppercase tracking-wider">Attached Portfolio Case Studies</div>
                                                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                                                            @foreach($samples as $sample)
                                                                <div class="p-3 rounded-xl bg-white border border-[#E6E2E8] flex items-center justify-between gap-2">
                                                                    <div class="min-w-0">
                                                                        <div class="font-bold text-xs text-[#1A1018] truncate">{{ $sample->title }}</div>
                                                                        <div class="text-[10px] text-[#1A1018]/55 truncate">{{ $sample->work_category ?: 'Case Study' }}</div>
                                                                    </div>
                                                                    @if($prop->vendor->isWebsitePublished())
                                                                        <a href="{{ route('storefront.portfolio.show', [$prop->vendor->slug, $sample->slug]) }}" target="_blank" class="text-[11px] font-bold text-[#623562] shrink-0 hover:underline">
                                                                            Inspect ↗
                                                                        </a>
                                                                    @endif
                                                                </div>
                                                            @endforeach
                                                        </div>
                                                    </div>
                                                @endif

                                                @if(!empty($propMilestones))
                                                    <div class="space-y-1.5 pt-2 border-t border-[#E6E2E8]">
                                                        <div class="text-[11px] font-bold text-[#1A1018]/70 uppercase tracking-wider">Proposed Milestones</div>
                                                        @foreach($propMilestones as $pm)
                                                            <div class="flex items-center justify-between text-xs bg-white px-3 py-1.5 rounded-lg border border-[#E6E2E8]">
                                                                <span class="font-medium">{{ $pm['title'] ?? 'Milestone' }}</span>
                                                                <span class="font-bold">₹{{ number_format(((int) ($pm['amount_paise'] ?? 0)) / 100, 0) }}</span>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                @endif

                                                @if($canDecide)
                                                    <div class="pt-3 border-t border-[#E6E2E8] flex flex-wrap items-center gap-2">
                                                        @if($prop->status === 'pending')
                                                            <form action="{{ route('services.proposals.shortlist', $prop) }}" method="POST">
                                                                @csrf
                                                                <button type="submit" class="px-3.5 py-2 rounded-xl bg-[#F8EFFA] hover:bg-[#ECD2F3] text-[#1A1018] text-xs font-bold border border-[#ECD2F3] cursor-pointer">
                                                                    Shortlist
                                                                </button>
                                                            </form>
                                                        @endif

                                                        <form action="{{ route('services.proposals.interview', $prop) }}" method="POST">
                                                            @csrf
                                                            <button type="submit" class="px-3.5 py-2 rounded-xl bg-white hover:bg-[#EFE9F2] text-[#1A1018] text-xs font-bold border border-[#E6E2E8] cursor-pointer">
                                                                Message / Interview
                                                            </button>
                                                        </form>

                                                        <form action="{{ route('services.proposals.reject', $prop) }}" method="POST">
                                                            @csrf
                                                            <button type="submit" class="px-3.5 py-2 rounded-xl bg-white hover:bg-red-50 text-red-700 text-xs font-bold border border-[#E6E2E8] cursor-pointer">
                                                                Decline
                                                            </button>
                                                        </form>

                                                        <form action="{{ route('services.proposals.hire', $prop) }}" method="POST" class="ml-auto">
                                                            @csrf
                                                            <button type="submit" class="grail-btn-obsidian px-5 py-2 text-xs font-bold cursor-pointer">
                                                                Hire &amp; Start Contract →
                                                            </button>
                                                        </form>
                                                    </div>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </div>

                        @elseif($isEligibleProvider)
                            @if($myProposal)
                                <div class="grail-card p-6 rounded-3xl bg-white border border-[#E6E2E8] space-y-4">
                                    <div class="flex items-center justify-between">
                                        <h2 class="text-xl font-light text-[#1A1018]" style="font-family: var(--font-grail-display);">Your Submitted Proposal</h2>
                                        <span class="px-3 py-1 rounded-full bg-[#F8EFFA] text-[#623562] text-xs font-bold uppercase">{{ ucfirst($myProposal->status) }}</span>
                                    </div>
                                    <div class="grid grid-cols-2 gap-3 text-xs">
                                        <div class="p-3 rounded-2xl bg-[#FAFAF8] border border-[#E6E2E8]">
                                            <div class="text-[#1A1018]/55">Your Bid</div>
                                            <div class="text-base font-bold text-[#1A1018]">{{ $myProposal->formatted_bid }}</div>
                                        </div>
                                        <div class="p-3 rounded-2xl bg-[#FAFAF8] border border-[#E6E2E8]">
                                            <div class="text-[#1A1018]/55">Delivery Timeline</div>
                                            <div class="text-base font-bold text-[#1A1018]">{{ $myProposal->delivery_days }} days</div>
                                        </div>
                                    </div>
                                    <p class="text-xs text-[#1A1018]/80 whitespace-pre-line">{{ $myProposal->cover_letter }}</p>

                                    @if(in_array($myProposal->status, ['pending', 'shortlisted'], true))
                                        <form action="{{ route('services.proposals.withdraw', $myProposal) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="px-4 py-2 rounded-xl bg-white hover:bg-red-50 text-red-700 border border-[#E6E2E8] text-xs font-bold cursor-pointer">
                                                Withdraw Proposal
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            @elseif(in_array($project->status, ['open', 'shortlisting'], true))
                                <div class="grail-card p-6 rounded-3xl bg-white border border-[#E6E2E8] space-y-5" x-data="{ milestoneCount: 0 }">
                                    <div>
                                        <h2 class="text-xl font-light text-[#1A1018]" style="font-family: var(--font-grail-display);">Submit a Proposal</h2>
                                        <p class="text-xs text-[#1A1018]/60">Pitch your approach, quote your bid, and attach relevant case studies from your studio.</p>
                                    </div>

                                    <form action="{{ route('services.proposals.store', $project) }}" method="POST" class="space-y-4">
                                        @csrf

                                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                                            <div class="space-y-1">
                                                <label class="block text-xs font-bold text-[#1A1018]">Your Bid (₹ INR) *</label>
                                                <input type="number" name="bid_amount" value="{{ old('bid_amount') }}" min="100" required
                                                       placeholder="15000"
                                                       class="grail-input-light w-full px-3.5 py-2.5 text-xs">
                                            </div>
                                            <div class="space-y-1">
                                                <label class="block text-xs font-bold text-[#1A1018]">Delivery Days *</label>
                                                <input type="number" name="delivery_days" value="{{ old('delivery_days', 7) }}" min="1" max="365" required
                                                       class="grail-input-light w-full px-3.5 py-2.5 text-xs">
                                            </div>
                                            <div class="space-y-1">
                                                <label class="block text-xs font-bold text-[#1A1018]">Pricing Structure</label>
                                                <select name="pricing_type" class="grail-input-light w-full px-3 py-2.5 text-xs">
                                                    <option value="fixed">Fixed Price</option>
                                                    <option value="milestone">Milestones</option>
                                                    <option value="hourly">Hourly</option>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="space-y-1">
                                            <label class="block text-xs font-bold text-[#1A1018]">Cover Letter &amp; Execution Plan *</label>
                                            <textarea name="cover_letter" rows="5" required
                                                      placeholder="Explain why you're the right specialist/agency, your planned execution steps, and how you will deliver quality results (min 25 characters)..."
                                                      class="grail-input-light w-full px-3.5 py-2.5 text-xs">{{ old('cover_letter') }}</textarea>
                                        </div>

                                        @if($myPortfolioProjects->isNotEmpty())
                                            <div class="space-y-2 pt-2 border-t border-[#E6E2E8]">
                                                <label class="block text-xs font-bold text-[#1A1018]">Attach Portfolio Case Studies (Optional)</label>
                                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                                    @foreach($myPortfolioProjects as $mp)
                                                        <label class="flex items-start gap-2.5 p-3 rounded-xl bg-[#FAFAF8] border border-[#E6E2E8] hover:border-[#623562] cursor-pointer text-xs">
                                                            <input type="checkbox" name="portfolio_project_ids[]" value="{{ $mp->id }}" class="mt-0.5 rounded border-zinc-300 text-[#623562]">
                                                            <span class="min-w-0">
                                                                <strong class="block text-[#1A1018] truncate">{{ $mp->title }}</strong>
                                                                <span class="text-[10px] text-[#1A1018]/55">{{ $mp->work_category ?: 'Case Study' }}</span>
                                                            </span>
                                                        </label>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @endif

                                        <!-- Optional Milestones -->
                                        <div class="space-y-2 pt-2 border-t border-[#E6E2E8]">
                                            <div class="flex items-center justify-between">
                                                <label class="text-xs font-bold text-[#1A1018]">Optional Milestone Schedule</label>
                                                <button type="button" @click="if (milestoneCount < 4) milestoneCount++"
                                                        class="text-xs font-bold text-[#623562] hover:underline cursor-pointer">
                                                    + Add Milestone
                                                </button>
                                            </div>

                                            <template x-for="idx in milestoneCount" :key="idx">
                                                <div class="grid grid-cols-1 sm:grid-cols-12 gap-2 p-3 rounded-xl bg-[#FAFAF8] border border-[#E6E2E8]">
                                                    <input type="text" :name="`milestones[${idx - 1}][title]`" placeholder="Milestone title"
                                                           class="grail-input-light sm:col-span-5 px-3 py-2 text-xs">
                                                    <input type="number" :name="`milestones[${idx - 1}][amount]`" min="1" placeholder="Amount (₹)"
                                                           class="grail-input-light sm:col-span-3 px-3 py-2 text-xs">
                                                    <input type="text" :name="`milestones[${idx - 1}][description]`" placeholder="Short deliverables note"
                                                           class="grail-input-light sm:col-span-4 px-3 py-2 text-xs">
                                                </div>
                                            </template>
                                        </div>

                                        <button type="submit" class="grail-btn-obsidian w-full py-3 text-xs font-bold cursor-pointer">
                                            Submit Proposal →
                                        </button>
                                    </form>
                                </div>
                            @endif

                        @else
                            <div class="grail-card p-8 rounded-3xl bg-white border border-[#E6E2E8] text-center space-y-3">
                                <h3 class="text-lg font-bold text-[#1A1018]">Want to bid on this project?</h3>
                                <p class="text-xs text-[#1A1018]/65 max-w-md mx-auto">
                                    Create your K71 Service Provider profile and personal website to submit tailored proposals with portfolio case studies.
                                </p>
                                <div class="pt-1">
                                    <a href="{{ route('services.become_seller') }}" class="grail-btn-obsidian inline-block px-6 py-2.5 text-xs font-bold">
                                        Launch Provider Website →
                                    </a>
                                </div>
                            </div>
                        @endif

                    </div>

                </div>
            </div>
        </div>
    </div>
</x-layouts.services>

