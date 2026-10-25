<x-layouts.public 
    title="Client Project Orders — Freelance Studio | K71"
    metaDescription="Manage active client project orders, deliveries, and milestone reviews on K71 Services."
>
    <div class="min-h-screen bg-gradient-to-b from-[#faf5ff] via-white to-[#faf5ff] text-zinc-900 pb-20 pt-8">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            <!-- HEADER -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-6 border-b border-purple-100">
                <div>
                    <a href="{{ route('vendor.services.index') }}" class="text-xs text-purple-700 hover:text-purple-900 font-bold flex items-center gap-1 mb-1">
                        ← Back to Services Studio
                    </a>
                    <h1 class="text-2xl sm:text-3xl font-black text-zinc-950 tracking-tight">Client Project Orders</h1>
                    <p class="text-xs text-zinc-500 mt-0.5">Fulfill milestone deliverables, respond to client revisions, and finalize projects.</p>
                </div>
            </div>

            <!-- STATUS TABS -->
            <div class="flex items-center gap-2 overflow-x-auto no-scrollbar pb-1 text-xs font-bold">
                <a href="{{ route('vendor.service_orders.index') }}" 
                   class="px-4 py-2 rounded-xl transition {{ empty($status) ? 'bg-purple-600 text-white shadow-xs' : 'bg-white text-zinc-600 hover:bg-purple-50 border border-purple-100' }}">
                    All Orders
                </a>
                <a href="{{ route('vendor.service_orders.index', ['status' => 'in_progress']) }}" 
                   class="px-4 py-2 rounded-xl transition {{ $status === 'in_progress' ? 'bg-purple-600 text-white shadow-xs' : 'bg-white text-zinc-600 hover:bg-purple-50 border border-purple-100' }}">
                    In Progress
                </a>
                <a href="{{ route('vendor.service_orders.index', ['status' => 'delivered']) }}" 
                   class="px-4 py-2 rounded-xl transition {{ $status === 'delivered' ? 'bg-purple-600 text-white shadow-xs' : 'bg-white text-zinc-600 hover:bg-purple-50 border border-purple-100' }}">
                    Delivered (Awaiting Client Approval)
                </a>
                <a href="{{ route('vendor.service_orders.index', ['status' => 'revision_requested']) }}" 
                   class="px-4 py-2 rounded-xl transition {{ $status === 'revision_requested' ? 'bg-purple-600 text-white shadow-xs' : 'bg-white text-zinc-600 hover:bg-purple-50 border border-purple-100' }}">
                    Revision Requested
                </a>
                <a href="{{ route('vendor.service_orders.index', ['status' => 'completed']) }}" 
                   class="px-4 py-2 rounded-xl transition {{ $status === 'completed' ? 'bg-purple-600 text-white shadow-xs' : 'bg-white text-zinc-600 hover:bg-purple-50 border border-purple-100' }}">
                    Completed
                </a>
            </div>

            <!-- ORDERS LIST -->
            @if($orders->isEmpty())
                <div class="p-12 text-center rounded-3xl bg-white/80 backdrop-blur-md border border-purple-100 space-y-4">
                    <div class="w-16 h-16 rounded-full bg-purple-100 text-purple-600 flex items-center justify-center text-3xl mx-auto">
                        📦
                    </div>
                    <h3 class="text-base font-bold text-zinc-900">No Client Orders Found</h3>
                    <p class="text-xs text-zinc-500 max-w-sm mx-auto">When clients purchase your service packages, their project requirements and workspaces will appear here.</p>
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
                                        <span>Client: <strong class="text-zinc-800">{{ $ord->buyer?->first_name }} {{ $ord->buyer?->last_name }}</strong></span>
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
