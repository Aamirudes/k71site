<x-layouts.services
    title="Open Project Briefs — K71 Services Marketplace"
    metaDescription="Browse custom client project briefs on K71 Services. Submit tailored proposals, attach verified portfolio case studies, and get hired with milestone protection."
>
    <div class="min-h-screen w-full selection:bg-[#ECD2F3] selection:text-[#1A1018]">

        <!-- OBSIDIAN HEADER -->
        <section class="grail-hero-band pt-8 sm:pt-12 pb-10 px-4 sm:px-6 lg:px-8 border-b border-white/10">
            <div class="max-w-7xl mx-auto flex flex-col lg:flex-row lg:items-end justify-between gap-6">
                <div class="space-y-2.5 max-w-2xl">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/10 border border-white/15 text-[11px] font-semibold text-[#ECD2F3]">
                        <span>UPWORK-STYLE CUSTOM PROJECTS</span>
                        <span class="text-white/40">•</span>
                        <span>ESCROW MILESTONES</span>
                    </div>
                    <h1 class="text-3xl sm:text-4xl lg:text-5xl font-light text-white tracking-tight" style="font-family: var(--font-grail-display);">
                        Open Client Project Briefs
                    </h1>
                    <p class="text-xs sm:text-sm text-[#EFE9F2]/75">
                        Clients post custom project briefs with budget ranges and deliverables. Independent providers and agencies submit tailored bids with real case studies.
                    </p>
                </div>

                <div class="flex flex-wrap items-center gap-3 shrink-0">
                    <a href="{{ route('services.index') }}" class="px-4 py-2.5 rounded-full bg-white/10 hover:bg-white/20 text-white text-xs font-semibold border border-white/15 transition">
                        ← Browse Service Packages
                    </a>
                    <a href="{{ route('services.projects.create') }}" class="grail-cta-pill px-6 py-2.5 text-xs font-bold transition shadow-sm">
                        + Post a Project Brief
                    </a>
                </div>
            </div>
        </section>

        <!-- CURVED EDITORIAL SHEET -->
        <div class="grail-sheet bg-[#F6F6F6] text-[#1A1018] rounded-t-[28px] sm:rounded-t-[36px] py-8 sm:py-12 pb-24 shadow-[0_-18px_48px_rgba(0,0,0,0.45)]">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">

                @if(session('success'))
                    <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-900 text-xs font-semibold">
                        {{ session('success') }}
                    </div>
                @endif
                @if(session('error'))
                    <div class="p-4 rounded-2xl bg-red-50 border border-red-200 text-red-900 text-xs font-semibold">
                        {{ session('error') }}
                    </div>
                @endif

                <!-- MY POSTED PROJECT BRIEFS -->
                @if(isset($myProjects) && $myProjects->isNotEmpty())
                    <div class="grail-card p-5 rounded-3xl bg-white border border-[#E6E2E8] space-y-3">
                        <div class="flex items-center justify-between">
                            <h2 class="text-sm font-bold text-[#1A1018] uppercase tracking-wider">Your Posted Project Briefs</h2>
                            <a href="{{ route('services.projects.create') }}" class="text-xs font-bold text-[#623562] hover:underline">+ Post Another</a>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                            @foreach($myProjects as $mp)
                                <div class="p-3.5 rounded-2xl bg-[#FAFAF8] border border-[#E6E2E8] flex flex-col justify-between gap-2">
                                    <div>
                                        <div class="flex items-center justify-between text-[10px] font-bold uppercase tracking-wider text-[#623562]">
                                            <span>{{ $mp->project_number }}</span>
                                            <span>{{ str_replace('_', ' ', $mp->status) }}</span>
                                        </div>
                                        <a href="{{ route('services.projects.show', $mp) }}" class="font-bold text-xs text-[#1A1018] hover:text-[#623562] line-clamp-1 mt-1 block">
                                            {{ $mp->title }}
                                        </a>
                                    </div>
                                    <div class="flex items-center justify-between text-[11px] pt-2 border-t border-[#E6E2E8]">
                                        <span class="text-[#1A1018]/65">{{ $mp->proposals_count }} proposal(s)</span>
                                        @if($mp->contract)
                                            <a href="{{ route('services.contracts.show', $mp->contract) }}" class="font-bold text-emerald-700 hover:underline">Open Contract →</a>
                                        @else
                                            <a href="{{ route('services.projects.show', $mp) }}" class="font-bold text-[#623562] hover:underline">Review Bids →</a>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                <!-- SEARCH & FILTERS -->
                <form action="{{ route('services.projects.index') }}" method="GET" class="grail-card p-4 sm:p-5 rounded-3xl bg-white border border-[#E6E2E8] grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3 items-center">
                    <div class="lg:col-span-2">
                        <input type="text" name="search" value="{{ $search }}" placeholder="Search briefs by title, skill, city..."
                               class="grail-input-light w-full px-4 py-2.5 text-xs">
                    </div>
                    <div>
                        <select name="cat" class="grail-input-light w-full px-3.5 py-2.5 text-xs">
                            <option value="">All Categories</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->slug }}" @selected($catSlug === $cat->slug)>{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <select name="work_mode" class="grail-input-light w-full px-3.5 py-2.5 text-xs">
                            <option value="">Any Work Mode</option>
                            <option value="remote" @selected($workMode === 'remote')>Remote</option>
                            <option value="onsite" @selected($workMode === 'onsite')>On-Site / Local</option>
                            <option value="hybrid" @selected($workMode === 'hybrid')>Hybrid</option>
                        </select>
                    </div>
                    <div class="flex items-center gap-2">
                        <select name="budget_type" class="grail-input-light flex-1 px-3 py-2.5 text-xs">
                            <option value="">Any Budget</option>
                            <option value="fixed" @selected($budgetType === 'fixed')>Fixed</option>
                            <option value="hourly" @selected($budgetType === 'hourly')>Hourly</option>
                        </select>
                        <button type="submit" class="grail-btn-obsidian px-4 py-2.5 text-xs cursor-pointer shrink-0">
                            Filter
                        </button>
                    </div>
                </form>

                <!-- OPEN PROJECTS FEED -->
                @if($projects->isEmpty())
                    <div class="grail-card p-12 rounded-3xl bg-white border border-[#E6E2E8] text-center space-y-3">
                        <h3 class="text-xl font-light text-[#1A1018]" style="font-family: var(--font-grail-display);">No open project briefs match your filters</h3>
                        <p class="text-xs text-[#1A1018]/60 max-w-md mx-auto">Be the first client to post a custom project brief or clear your filters to inspect all open work.</p>
                        <div class="pt-2 flex items-center justify-center gap-3">
                            <a href="{{ route('services.projects.index') }}" class="px-4 py-2 rounded-full bg-[#EFE9F2] text-[#1A1018] text-xs font-bold">Reset Filters</a>
                            <a href="{{ route('services.projects.create') }}" class="grail-btn-obsidian px-5 py-2 text-xs">Post Project Brief</a>
                        </div>
                    </div>
                @else
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        @foreach($projects as $proj)
                            <div class="grail-card p-6 rounded-3xl bg-white border border-[#E6E2E8] hover:border-[#623562]/45 hover:shadow-md transition-all flex flex-col justify-between space-y-4">
                                <div class="space-y-3">
                                    <div class="flex flex-wrap items-center justify-between gap-2 text-[11px]">
                                        <div class="flex items-center gap-2">
                                            <span class="px-2.5 py-0.5 rounded-full bg-[#F8EFFA] text-[#623562] border border-[#ECD2F3] font-bold uppercase">
                                                {{ $proj->budget_type === 'hourly' ? 'Hourly Rate' : 'Fixed Scope' }}
                                            </span>
                                            @if($proj->category)
                                                <span class="text-[#1A1018]/65 font-semibold">{{ $proj->category->name }}</span>
                                            @endif
                                        </div>
                                        <span class="text-[#1A1018]/50 font-mono">{{ $proj->project_number }}</span>
                                    </div>

                                    <div>
                                        <h2 class="text-lg font-bold text-[#1A1018] leading-snug">
                                            <a href="{{ route('services.projects.show', $proj) }}" class="hover:text-[#623562] transition">
                                                {{ $proj->title }}
                                            </a>
                                        </h2>
                                        <p class="text-xs text-[#1A1018]/70 mt-1.5 line-clamp-3 leading-relaxed">
                                            {{ $proj->description }}
                                        </p>
                                    </div>

                                    @if(!empty($proj->skills_json) && is_array($proj->skills_json))
                                        <div class="flex flex-wrap gap-1.5">
                                            @foreach(array_slice($proj->skills_json, 0, 5) as $skill)
                                                <span class="px-2.5 py-0.5 rounded-full bg-[#FAFAF8] border border-[#E6E2E8] text-[11px] font-medium text-[#1A1018]/80">
                                                    {{ $skill }}
                                                </span>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>

                                <div class="pt-4 border-t border-[#E6E2E8] flex flex-wrap items-center justify-between gap-3">
                                    <div>
                                        <div class="text-base font-bold text-[#1A1018]">{{ $proj->formatted_budget }}</div>
                                        <div class="text-[11px] text-[#1A1018]/55">
                                            {{ ucfirst($proj->work_mode) }} · {{ $proj->city ?: 'Remote' }} · {{ $proj->proposals_count }} proposal(s)
                                        </div>
                                    </div>
                                    <a href="{{ route('services.projects.show', $proj) }}"
                                       class="grail-btn-obsidian px-5 py-2.5 text-xs font-bold transition">
                                        View Brief &amp; Pitch →
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="pt-4">
                        {{ $projects->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-layouts.services>

