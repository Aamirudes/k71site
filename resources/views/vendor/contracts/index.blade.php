<x-layouts.vendor title="Project Contracts">
    <div class="max-w-7xl mx-auto space-y-6 pb-12">

        <div class="bg-white rounded-2xl border border-zinc-200 p-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <div class="text-[11px] font-mono uppercase tracking-wider text-zinc-500 font-bold">Upwork Contract Execution</div>
                <h1 class="text-2xl font-black text-zinc-950 tracking-tight mt-0.5">Project Contracts &amp; Milestones</h1>
                <p class="text-xs text-zinc-500 mt-1">Manage awarded client contracts, submit milestone deliverables, and receive verified project reviews.</p>
            </div>

            <div class="flex items-center gap-2 text-xs font-bold">
                <a href="{{ route('vendor.contracts.index') }}" class="px-3.5 py-2 rounded-xl border {{ empty($status) ? 'bg-black text-white border-black' : 'bg-white text-zinc-700 border-zinc-300' }}">All</a>
                <a href="{{ route('vendor.contracts.index', ['status' => 'active']) }}" class="px-3.5 py-2 rounded-xl border {{ $status === 'active' ? 'bg-black text-white border-black' : 'bg-white text-zinc-700 border-zinc-300' }}">Active</a>
                <a href="{{ route('vendor.contracts.index', ['status' => 'completed']) }}" class="px-3.5 py-2 rounded-xl border {{ $status === 'completed' ? 'bg-black text-white border-black' : 'bg-white text-zinc-700 border-zinc-300' }}">Completed</a>
            </div>
        </div>

        @if($contracts->isEmpty())
            <div class="p-12 text-center rounded-2xl bg-white border border-zinc-200 space-y-3">
                <h3 class="text-base font-bold text-zinc-900">No Contracts Yet</h3>
                <p class="text-xs text-zinc-500 max-w-md mx-auto">When clients hire your proposal on an open brief, your milestone contract workspace appears here.</p>
                <a href="{{ route('vendor.projects.index') }}" class="inline-block px-5 py-2.5 rounded-xl bg-black text-white text-xs font-bold">Explore Open Project Briefs</a>
            </div>
        @else
            <div class="space-y-4">
                @foreach($contracts as $contract)
                    @php
                        $totalM = $contract->milestones->count();
                        $doneM = $contract->milestones->where('status', 'approved')->count();
                        $pct = $totalM > 0 ? (int) round(($doneM / $totalM) * 100) : ($contract->status === 'completed' ? 100 : 0);
                    @endphp
                    <div class="bg-white rounded-2xl border border-zinc-200 p-5 flex flex-col md:flex-row md:items-center justify-between gap-4">
                        <div class="space-y-1.5 flex-1">
                            <div class="flex flex-wrap items-center gap-2 text-xs">
                                <span class="font-mono font-bold text-zinc-500">{{ $contract->contract_number }}</span>
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase {{ $contract->status === 'completed' ? 'bg-emerald-100 text-emerald-800' : 'bg-zinc-100 text-zinc-800' }}">
                                    {{ $contract->status }}
                                </span>
                                @if($contract->buyer_rating)
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800">★ {{ $contract->buyer_rating }}/5 Verified Review</span>
                                @endif
                            </div>

                            <h3 class="font-bold text-base text-zinc-950">{{ $contract->project?->title ?? 'Project Contract' }}</h3>
                            <p class="text-xs text-zinc-500">Client: <strong class="text-zinc-800">{{ $contract->buyer?->name ?? 'Client' }}</strong> · Value: <strong class="text-black font-mono">{{ $contract->formatted_amount }}</strong></p>

                            <div class="pt-1 max-w-md space-y-1">
                                <div class="flex justify-between text-[11px] font-mono text-zinc-500">
                                    <span>Milestones Approved</span>
                                    <span>{{ $doneM }} / {{ $totalM }} ({{ $pct }}%)</span>
                                </div>
                                <div class="w-full h-1.5 rounded-full bg-zinc-100 overflow-hidden">
                                    <div class="h-full bg-black" style="width: {{ $pct }}%;"></div>
                                </div>
                            </div>
                        </div>

                        <a href="{{ route('services.contracts.show', $contract) }}"
                           class="px-5 py-2.5 rounded-xl bg-black hover:bg-zinc-800 text-white text-xs font-bold shrink-0 text-center">
                            Open Contract Workspace →
                        </a>
                    </div>
                @endforeach
            </div>

            <div>{{ $contracts->links() }}</div>
        @endif
    </div>
</x-layouts.vendor>
