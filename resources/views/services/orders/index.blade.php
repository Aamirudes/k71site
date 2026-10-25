<x-layouts.public 
    title="My Freelance Service Orders — K71 Services"
    metaDescription="Manage your active and completed freelance projects and track deliverables on K71 Services."
>
    <div class="min-h-screen bg-gradient-to-b from-[#faf5ff] via-white to-[#faf5ff] text-zinc-900 pb-20 pt-8">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            <!-- HEADER -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h1 class="text-2xl sm:text-3xl font-black text-zinc-950 tracking-tight">My Freelance Projects</h1>
                    <p class="text-xs sm:text-sm text-zinc-500 mt-1">Track active milestones, review deliverables, and collaborate with providers.</p>
                </div>
                <a href="{{ route('services.index') }}" 
                   class="px-5 py-2.5 rounded-2xl bg-purple-600 hover:bg-purple-700 text-white text-xs font-bold uppercase tracking-wider transition shadow-md shadow-purple-500/25 shrink-0 text-center">
                    Browse Services
                </a>
            </div>

            <!-- STATUS TABS -->
            <div class="flex items-center gap-2 overflow-x-auto no-scrollbar pb-1 text-xs font-bold">
                <a href="{{ route('services.orders.index') }}" 
                   class="px-4 py-2 rounded-xl transition {{ empty($status) ? 'bg-purple-600 text-white shadow-xs' : 'bg-white text-zinc-600 hover:bg-purple-50 border border-purple-100' }}">
                    All Projects
                </a>
                <a href="{{ route('services.orders.index', ['status' => 'in_progress']) }}" 
                   class="px-4 py-2 rounded-xl transition {{ $status === 'in_progress' ? 'bg-purple-600 text-white shadow-xs' : 'bg-white text-zinc-600 hover:bg-purple-50 border border-purple-100' }}">
                    In Progress
                </a>
                <a href="{{ route('services.orders.index', ['status' => 'delivered']) }}" 
                   class="px-4 py-2 rounded-xl transition {{ $status === 'delivered' ? 'bg-purple-600 text-white shadow-xs' : 'bg-white text-zinc-600 hover:bg-purple-50 border border-purple-100' }}">
                    Delivered (Needs Review)
                </a>
                <a href="{{ route('services.orders.index', ['status' => 'completed']) }}" 
                   class="px-4 py-2 rounded-xl transition {{ $status === 'completed' ? 'bg-purple-600 text-white shadow-xs' : 'bg-white text-zinc-600 hover:bg-purple-50 border border-purple-100' }}">
                    Completed
                </a>
            </div>

            <!-- ORDERS LIST -->
            @if($orders->isEmpty())
                <div class="p-12 text-center rounded-3xl bg-white/80 backdrop-blur-md border border-purple-100 space-y-4">
                    <div class="w-16 h-16 rounded-full bg-purple-100 text-purple-600 flex items-center justify-center text-3xl mx-auto">
                        📂
                    </div>
                    <h3 class="text-base font-bold text-zinc-900">No Projects Found</h3>
                    <p class="text-xs text-zinc-500 max-w-sm mx-auto">You don't have any freelance orders in this category yet. Hire a vetted specialist today!</p>
                    <a href="{{ route('services.index') }}" class="inline-block px-6 py-2.5 rounded-2xl bg-purple-600 text-white text-xs font-bold uppercase tracking-wider shadow-sm hover:bg-purple-700 transition">
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
                        <div class="p-5 sm:p-6 rounded-3xl bg-white/90 backdrop-blur-md border border-purple-100 hover:border-purple-300 hover:shadow-[0_8px_30px_rgba(124,58,237,0.06)] transition-all duration-300 flex flex-col md:flex-row items-start md:items-center justify-between gap-5">
                            <div class="flex items-start sm:items-center gap-4">
                                <img src="{{ $primaryImg }}" alt="{{ $ord->product?->title }}" class="w-16 h-16 sm:w-20 sm:h-20 rounded-2xl object-cover border border-purple-100 shrink-0">
                                <div>
                                    <div class="flex items-center gap-2 mb-1">
                                        <span class="text-[10px] font-bold uppercase tracking-wider text-purple-700 bg-purple-50 px-2 py-0.5 rounded-md">
                                            #{{ $ord->order_number }}
                                        </span>
                                        <span class="text-xs text-zinc-400">•</span>
                                        <span class="text-xs text-zinc-500">{{ $ord->created_at->format('M d, Y') }}</span>
                                    </div>

                                    <h3 class="font-bold text-sm sm:text-base text-zinc-950 leading-snug">
                                        {{ $ord->product?->title ?? 'Service Project' }}
                                    </h3>

                                    <div class="text-xs text-zinc-500 mt-1 flex flex-wrap items-center gap-3">
                                        <span>Provider: <strong class="text-zinc-800">{{ $ord->vendor?->shop_name }}</strong></span>
                                        <span>Tier: <strong class="text-purple-700 uppercase">{{ $ord->tier }}</strong></span>
                                        <span>Due: <strong class="text-zinc-800">{{ $ord->due_at ? $ord->due_at->format('M d, Y') : 'In 3 Days' }}</strong></span>
                                    </div>
                                </div>
                            </div>

                            <div class="flex flex-row md:flex-col items-center md:items-end justify-between w-full md:w-auto gap-3 pt-3 md:pt-0 border-t md:border-t-0 border-purple-50">
                                <div class="text-left md:text-right">
                                    <span class="text-base sm:text-lg font-black text-purple-700 block">₹{{ number_format($ord->total_price) }}</span>
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider border {{ $badge['bg'] }} {{ $badge['text'] }} {{ $badge['border'] }}">
                                        {{ $badge['label'] }}
                                    </span>
                                </div>

                                <a href="{{ route('services.workspace', $ord) }}"
                                   class="px-5 py-2.5 rounded-xl bg-purple-600 hover:bg-purple-700 text-white text-xs font-bold uppercase tracking-wider transition shadow-sm active:scale-95 shrink-0">
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
</x-layouts.public>
