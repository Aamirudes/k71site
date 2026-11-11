<x-layouts.public 
    title="My Freelance Service Orders — K71 Services"
    metaDescription="Manage your active and completed freelance projects and track deliverables on K71 Services."
    :fullWidth="true"
>
    <div class="w-full selection:bg-[#ECD2F3] selection:text-[#1A1018]">

        <!-- OBSIDIAN PROJECTS HEADER -->
        <section class="grail-hero-band py-8 sm:py-10 px-4 sm:px-6 lg:px-8 border-b border-white/10">
            <div class="max-w-6xl mx-auto flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <span class="grail-eyebrow block mb-1.5">Services Workspace Hub</span>
                    <h1 class="text-3xl sm:text-4xl lg:text-5xl font-light text-white tracking-tight leading-[1.06]" style="font-family: var(--font-grail-display);">
                        My Freelance Projects
                    </h1>
                    <p class="text-xs sm:text-sm text-[#EFE9F2]/70 mt-2">
                        Track active milestones, review deliverables, and collaborate with providers.
                    </p>
                </div>
                <a href="{{ route('services.index') }}" 
                   class="grail-cta-pill px-6 py-3 text-xs uppercase tracking-wider shrink-0 text-center">
                    Browse Services
                </a>
            </div>
        </section>

        <!-- CURVED STONE EDITORIAL SHEET -->
        <div class="grail-sheet bg-[#F6F6F6] text-[#1A1018] rounded-t-[28px] sm:rounded-t-[36px] py-8 sm:py-10 pb-20 px-4 sm:px-6 lg:px-8 shadow-[0_-18px_48px_rgba(0,0,0,0.45)] min-h-[65vh]">
            <div class="max-w-6xl mx-auto space-y-6">

                <!-- STATUS TABS -->
                <div class="flex items-center gap-2 overflow-x-auto no-scrollbar pb-1 text-xs font-bold">
                    <a href="{{ route('services.orders.index') }}" 
                       class="px-4 py-2 rounded-full border transition whitespace-nowrap {{ empty($status) ? 'bg-[#1A1018] text-[#ECD2F3] border-[#1A1018] shadow-xs' : 'bg-white text-[#1A1018]/70 hover:bg-[#F8EFFA] hover:text-[#1A1018] border-[#E6E2E8]' }}">
                        All Projects
                    </a>
                    <a href="{{ route('services.orders.index', ['status' => 'in_progress']) }}" 
                       class="px-4 py-2 rounded-full border transition whitespace-nowrap {{ $status === 'in_progress' ? 'bg-[#1A1018] text-[#ECD2F3] border-[#1A1018] shadow-xs' : 'bg-white text-[#1A1018]/70 hover:bg-[#F8EFFA] hover:text-[#1A1018] border-[#E6E2E8]' }}">
                        In Progress
                    </a>
                    <a href="{{ route('services.orders.index', ['status' => 'delivered']) }}" 
                       class="px-4 py-2 rounded-full border transition whitespace-nowrap {{ $status === 'delivered' ? 'bg-[#1A1018] text-[#ECD2F3] border-[#1A1018] shadow-xs' : 'bg-white text-[#1A1018]/70 hover:bg-[#F8EFFA] hover:text-[#1A1018] border-[#E6E2E8]' }}">
                        Delivered (Needs Review)
                    </a>
                    <a href="{{ route('services.orders.index', ['status' => 'completed']) }}" 
                       class="px-4 py-2 rounded-full border transition whitespace-nowrap {{ $status === 'completed' ? 'bg-[#1A1018] text-[#ECD2F3] border-[#1A1018] shadow-xs' : 'bg-white text-[#1A1018]/70 hover:bg-[#F8EFFA] hover:text-[#1A1018] border-[#E6E2E8]' }}">
                        Completed
                    </a>
                </div>

                <!-- ORDERS LIST -->
                @if($orders->isEmpty())
                    <div class="grail-card p-12 text-center rounded-3xl bg-white border border-[#E6E2E8] space-y-4">
                        <div class="w-16 h-16 rounded-full bg-[#F8EFFA] border border-[#ECD2F3] text-[#623562] flex items-center justify-center text-3xl mx-auto">
                            📂
                        </div>
                        <h3 class="text-base font-bold text-[#1A1018]">No Projects Found</h3>
                        <p class="text-xs text-[#1A1018]/60 max-w-sm mx-auto">You don't have any freelance orders in this category yet. Hire a vetted specialist today!</p>
                        <a href="{{ route('services.index') }}" class="grail-btn-obsidian inline-flex px-6 py-2.5 text-xs uppercase tracking-wider">
                            Explore Freelancers
                        </a>
                    </div>
                @else
                    <div class="space-y-4">
                        @foreach($orders as $ord)
                            @php
                                $badge = $ord->status_badge;
                                $primaryImg = $ord->product?->primary_image_url ?? asset('images/product-placeholder.svg');
                            @endphp
                            <div class="grail-card p-5 sm:p-6 rounded-3xl bg-white border border-[#E6E2E8] hover:border-[#623562]/40 transition-all duration-300 flex flex-col md:flex-row items-start md:items-center justify-between gap-5" data-grail-reveal>
                                <div class="flex items-start sm:items-center gap-4">
                                    <img src="{{ $primaryImg }}" alt="{{ $ord->product?->title }}" class="w-16 h-16 sm:w-20 sm:h-20 rounded-2xl object-cover border border-[#E6E2E8] shrink-0">
                                    <div>
                                        <div class="flex items-center gap-2 mb-1">
                                            <span class="text-[10px] font-bold uppercase tracking-wider text-[#623562] bg-[#F8EFFA] border border-[#ECD2F3] px-2 py-0.5 rounded-md">
                                                #{{ $ord->order_number }}
                                            </span>
                                            <span class="text-xs text-[#1A1018]/30">•</span>
                                            <span class="text-xs text-[#1A1018]/55">{{ $ord->created_at->format('M d, Y') }}</span>
                                        </div>

                                        <h3 class="font-bold text-sm sm:text-base text-[#1A1018] leading-snug">
                                            {{ $ord->product?->title ?? 'Service Project' }}
                                        </h3>

                                        <div class="text-xs text-[#1A1018]/60 mt-1 flex flex-wrap items-center gap-3">
                                            <span>Provider: <strong class="text-[#1A1018]">{{ $ord->vendor?->shop_name }}</strong></span>
                                            <span>Tier: <strong class="text-[#623562] uppercase">{{ $ord->tier }}</strong></span>
                                            <span>Due: <strong class="text-[#1A1018]">{{ $ord->due_at ? $ord->due_at->format('M d, Y') : 'In 3 Days' }}</strong></span>
                                        </div>
                                    </div>
                                </div>

                                <div class="flex flex-row md:flex-col items-center md:items-end justify-between w-full md:w-auto gap-3 pt-3 md:pt-0 border-t md:border-t-0 border-[#E6E2E8]">
                                    <div class="text-left md:text-right">
                                        <span class="text-base sm:text-lg font-bold text-[#1A1018] block">₹{{ number_format($ord->total_price) }}</span>
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider border {{ $badge['bg'] }} {{ $badge['text'] }} {{ $badge['border'] }}">
                                            {{ $badge['label'] }}
                                        </span>
                                    </div>

                                    <a href="{{ route('services.workspace', $ord) }}"
                                       class="grail-btn-obsidian px-5 py-2.5 text-xs uppercase tracking-wider shrink-0">
                                        Open Workspace →
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="mt-8">
                        {{ $orders->links() }}
                    </div>
                @endif

            </div>
        </div>

    </div>
</x-layouts.public>
